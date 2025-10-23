<?php

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Borders;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing;

// Export (kept as in your project)
require_once __DIR__ . '/ExportSf2.php';

class StudentAttendanceController
{
    use ExportSf2Trait;

    protected $db;
    protected $view = 'StudentAttendanceController';

    public function __construct($db) {
        // If you enforce auth, keep your checks here.
        $this->db = $db;
    }

    /* ===== tiny utils ===== */
    private function one($sql,$p=[]){ $r=$this->db->Select($sql,$p); return $r? $r[0]:null; }
    private function many($sql,$p=[]){ return $this->db->Select($sql,$p); }
    private function uid(){   return $_SESSION['user_id']   ?? null; }
    private function utype(){ return $_SESSION['user_type'] ?? null; } // 1=admin,2=teacher,...

    /* ===== transaction helpers (safe fallbacks) ===== */
    private function txBegin(){
        foreach (['Begin','begin','beginTransaction','startTransaction','StartTrans','BeginTrans','beginTrans'] as $m) {
            if (method_exists($this->db, $m)) { return $this->db->{$m}(); }
        }
        return null;
    }
    private function txCommit(){
        foreach (['Commit','commit','commitTransaction','CompleteTrans','endTransaction','CommitTrans'] as $m) {
            if (method_exists($this->db, $m)) { return $this->db->{$m}(); }
        }
        return null;
    }
    private function txRollback(){
        foreach (['Rollback','rollback','rollBack','FailTrans','cancelTransaction','RollbackTrans'] as $m) {
            if (method_exists($this->db, $m)) { return $this->db->{$m}(); }
        }
        return null;
    }

    /* ===== access helpers ===== */
    private function isAdviserOfSection($teacherId, $sectionId) {
        return (bool)$this->one(
            "SELECT 1 FROM section s WHERE s.deleted=0 AND s.id=? AND s.adviser_id=? LIMIT 1",
            [$sectionId,$teacherId]
        );
    }
    private function isAdviserOfCurriculum($teacherId, $curriculumId) {
        return (bool)$this->one(
            "SELECT 1 FROM curriculum c WHERE c.deleted=0 AND c.id=? AND c.adviser_id=? LIMIT 1",
            [$curriculumId,$teacherId]
        );
    }

    /** Sections where teacher is section adviser OR curriculum adviser OR assigned to any subject under that section's curricula. */
    private function sectionsForTeacher($teacherId){
        return $this->many(
            "SELECT DISTINCT s.id, s.name, gl.name AS grade_name
               FROM section s
               JOIN grade_level gl ON gl.id = s.grade_id
          LEFT JOIN curriculum c        ON c.grade_id = s.id AND c.deleted=0
          LEFT JOIN curriculum_child cc ON cc.curriculum_id = c.id AND cc.deleted=0
              WHERE s.deleted=0
                AND (s.adviser_id = ? OR c.adviser_id = ? OR cc.adviser_id = ?)
           ORDER BY gl.name, s.name",
           [$teacherId,$teacherId,$teacherId]
        );
    }

    /** If adviser of section → all curricula; else only curricula where teacher is curriculum adviser or has any subject assignment. */
    private function curriculaForTeacher($teacherId, $sectionId){
        if ($this->isAdviserOfSection($teacherId,$sectionId)) {
            return $this->many(
                "SELECT c.id, c.school_year
                   FROM curriculum c
                  WHERE c.deleted=0 AND c.grade_id=?
               ORDER BY c.school_year DESC",
               [$sectionId]
            );
        }
        return $this->many(
            "SELECT DISTINCT c.id, c.school_year
               FROM curriculum c
          LEFT JOIN curriculum_child cc ON cc.curriculum_id = c.id AND cc.deleted=0
              WHERE c.deleted=0 AND c.grade_id=? AND (c.adviser_id = ? OR cc.adviser_id = ?)
           ORDER BY c.school_year DESC",
           [$sectionId,$teacherId,$teacherId]
        );
    }

    /** Final access check for (section, curriculum) without subject dimension. */
    private function assertTeacherAccess($teacherId, $sectionId, $curriculumId){
        $cur = $this->one("SELECT grade_id AS section_id FROM curriculum WHERE id=? AND deleted=0 LIMIT 1", [$curriculumId]);
        if (!$cur || (int)$cur['section_id'] !== (int)$sectionId) return false;
        if ($this->isAdviserOfSection($teacherId,$sectionId) || $this->isAdviserOfCurriculum($teacherId,$curriculumId)) return true;

        // Any subject assignment under this curriculum is enough
        return (bool)$this->one(
            "SELECT 1 FROM curriculum_child WHERE deleted=0 AND curriculum_id=? AND adviser_id=? LIMIT 1",
            [$curriculumId,$teacherId]
        );
    }

    /** All enrolled students; LEFT JOIN attendance AM/PM for given date (and remarks). */
    private function entryRows($sectionId, $curriculumId, $date, $q=''){
        $where = ["rs.deleted=0","rs.status=1","u.status=1","rs.section_id=?","rs.curriculum_id=?"];
        $p = [(int)$sectionId, (int)$curriculumId];

        if ($q !== '') {
            $like='%'.$q.'%';
            $where[]="(u.account_last_name LIKE ? OR u.account_first_name LIKE ? OR u.LRN LIKE ?)";
            array_push($p,$like,$like,$like);
        }

        $sql = "SELECT
                    u.user_id AS student_id,
                    CONCAT(u.account_last_name, ', ', u.account_first_name, ' ', u.account_middle_name) AS full_name,
                    u.LRN,
                    u.gender AS gender,
                    sa.id AS attendance_id,
                    sa.am_status, sa.pm_status,
                    sa.remarks
                FROM registrar_student rs
                JOIN users u ON u.user_id = rs.student_id
                LEFT JOIN student_attendance sa
                       ON sa.student_id    = rs.student_id
                      AND sa.section_id    = rs.section_id
                      AND sa.curriculum_id = rs.curriculum_id
                      AND sa.attendance_date = ?
               WHERE ".implode(' AND ',$where)."
               ORDER BY u.account_last_name, u.account_first_name";
        array_unshift($p, $date);
        return $this->many($sql,$p);
    }

    /* ===== page build ===== */
    private function buildContext($req){
        $uid = $this->uid(); $ut = $this->utype();
        $q   = isset($req['q']) ? trim((string)$req['q']) : '';
        $date = isset($req['date']) && preg_match('/^\d{4}-\d{2}-\d{2}$/',(string)$req['date'])
              ? (string)$req['date'] : date('Y-m-d');

        if ($ut != 2) {
            $sections=$this->many("SELECT s.id,s.name,gl.name AS grade_name FROM section s JOIN grade_level gl ON gl.id=s.grade_id WHERE s.deleted=0 ORDER BY gl.name,s.name");
            $section_id=$sections ? (int)($req['section_id'] ?? $sections[0]['id']) : null;
            $curricula=$section_id ? $this->many("SELECT c.id,c.school_year FROM curriculum c WHERE c.deleted=0 AND c.grade_id=? ORDER BY c.school_year DESC",[$section_id]) : [];
            $curriculum_id=$curricula ? (int)($req['curriculum_id'] ?? $curricula[0]['id']) : null;

            $rows = ($section_id && $curriculum_id) ? $this->entryRows($section_id,$curriculum_id,$date,$q) : [];
            return compact('sections','section_id','curricula','curriculum_id','date','rows');
        }

        // teacher
        $sections = $this->sectionsForTeacher($uid);
        $section_id = $sections ? (int)($req['section_id'] ?? $sections[0]['id']) : null;

        $curricula = $section_id ? $this->curriculaForTeacher($uid,$section_id) : [];
        $curriculum_id = $curricula ? (int)($req['curriculum_id'] ?? $curricula[0]['id']) : null;

        $rows = [];
        if ($section_id && $curriculum_id && $this->assertTeacherAccess($uid,$section_id,$curriculum_id)) {
            $rows = $this->entryRows($section_id,$curriculum_id,$date,$q);
        }

        return compact('sections','section_id','curricula','curriculum_id','date','rows');
    }

    /* ===== endpoints ===== */
    public function index(){
        $data = $this->buildContext($_GET ?? []);
        return [
            'header'  => 'Student Attendance',
            'content' => loadView('components/'.$this->view.'/views/custom', $data),
        ];
    }

    public function fetch(){
        $req = getRequestAll();
        $uid = $this->uid(); $ut = $this->utype();

        $ctx = $this->buildContext($req);

        if ($ut == 2) {
            $sid = isset($req['section_id'])    ? (int)$req['section_id']    : ($ctx['section_id'] ?? null);
            $cid = isset($req['curriculum_id']) ? (int)$req['curriculum_id'] : ($ctx['curriculum_id'] ?? null);
            if ($sid && $cid && !$this->assertTeacherAccess($uid,$sid,$cid)) {
                echo json_encode(['status'=>false,'message'=>'You are not assigned to this section/curriculum.']);
                return;
            }
        }
        echo json_encode(['status'=>true] + $ctx);
    }

    public function save(){
        try {
            $req = getRequestAll();
            $uid = $this->uid(); $ut = $this->utype();

            $sectionId    = (int)($req['section_id']    ?? 0);
            $curriculumId = (int)($req['curriculum_id'] ?? 0);
            $date         = isset($req['date']) ? trim((string)$req['date']) : '';

            if (!$sectionId || !$curriculumId || !preg_match('/^\d{4}-\d{2}-\d{2}$/',$date)) {
                echo json_encode(['status'=>false,'message'=>'Missing/invalid required fields.']); return;
            }
            if ($ut == 2 && !$this->assertTeacherAccess($uid,$sectionId,$curriculumId)) {
                echo json_encode(['status'=>false,'message'=>'You are not assigned to this section/curriculum.']); return;
            }

            $rows = $req['rows'] ?? [];
            if (is_string($rows)) {
                $tmp = json_decode($rows, true);
                if (json_last_error() === JSON_ERROR_NONE) $rows = $tmp;
            }
            if (!is_array($rows)) $rows = [];

            $valid = ['P','A']; // Only Present or Absent

            $this->txBegin();
            foreach ($rows as $r) {
                $studentId = (int)($r['student_id'] ?? 0);
                if (!$studentId) continue;

                $am = strtoupper(trim((string)($r['am_status'] ?? 'A'))); // default absent if unchecked
                $pm = strtoupper(trim((string)($r['pm_status'] ?? 'A')));
                $am = in_array($am,$valid,true) ? $am : 'A';
                $pm = in_array($pm,$valid,true) ? $pm : 'A';

                $remarks = isset($r['remarks']) ? trim((string)$r['remarks']) : null;

                $existing = $this->one(
                    "SELECT id FROM student_attendance
                      WHERE student_id=? AND section_id=? AND curriculum_id=? AND attendance_date=? LIMIT 1",
                    [$studentId,$sectionId,$curriculumId,$date]
                );

                if ($existing) {
                    $this->db->Update(
                        "UPDATE student_attendance SET am_status=?, pm_status=?, remarks=? WHERE id=?",
                        [$am,$pm,$remarks,(int)$existing['id']]
                    );
                } else {
                    $this->db->Insert(
                        "INSERT INTO student_attendance
                           (student_id, section_id, curriculum_id, attendance_date, am_status, pm_status, remarks)
                         VALUES (?,?,?,?,?,?,?)",
                        [$studentId,$sectionId,$curriculumId,$date,$am,$pm,$remarks]
                    );
                }
            }
            $this->txCommit();
            echo json_encode(['status'=>true,'message'=>'Attendance saved successfully.']);
        } catch (Throwable $e) {
            $this->txRollback();
            error_log('Attendance save error: '.$e->getMessage());
            echo json_encode(['status'=>false,'message'=>'Save failed: '.$e->getMessage()]);
        }
    }

    /** Quick upsert from QR scan by LRN for AM/PM. */
    public function punch(){
        try{
            $req = getRequestAll();
            $uid = $this->uid(); $ut = $this->utype();

            $lrn = preg_replace('/\D+/', '', (string)($req['lrn'] ?? ''));
            $slot = strtoupper(trim((string)($req['slot'] ?? 'AM')));
            $sectionId    = (int)($req['section_id']    ?? 0);
            $curriculumId = (int)($req['curriculum_id'] ?? 0);
            $date         = (string)($req['date'] ?? date('Y-m-d'));

            if (strlen($lrn) !== 12 || !in_array($slot, ['AM','PM'], true) ||
                !$sectionId || !$curriculumId || !preg_match('/^\d{4}-\d{2}-\d{2}$/',$date)) {
                echo json_encode(['status'=>false,'message'=>'Invalid scan payload.']); return;
            }

            // Authorization: teachers must be assigned; admins can punch anywhere
            if ($ut == 2 && !$this->assertTeacherAccess($uid,$sectionId,$curriculumId)) {
                echo json_encode(['status'=>false,'message'=>'Not allowed to punch for this class.']); return;
            }

            // Find student by LRN
            $stu = $this->one("SELECT user_id, account_first_name, account_last_name FROM users WHERE deleted=0 AND LRN=? LIMIT 1", [$lrn]);
            if (!$stu) { echo json_encode(['status'=>false,'message'=>'Student not found for LRN.']); return; }
            $studentId = (int)$stu['user_id'];

            // Ensure enrolled in this section/curriculum
            $enr = $this->one("SELECT 1 FROM registrar_student WHERE deleted=0 AND status=1 AND student_id=? AND section_id=? AND curriculum_id=? LIMIT 1",
                              [$studentId,$sectionId,$curriculumId]);
            if (!$enr) { echo json_encode(['status'=>false,'message'=>'Student not in this class list.']); return; }

            // Upsert attendance, setting only the requested slot to "P"
            $existing = $this->one(
                "SELECT id, am_status, pm_status FROM student_attendance
                  WHERE student_id=? AND section_id=? AND curriculum_id=? AND attendance_date=? LIMIT 1",
                [$studentId,$sectionId,$curriculumId,$date]
            );

            if ($existing){
                $am = $existing['am_status'] ?? 'A';
                $pm = $existing['pm_status'] ?? 'A';
                if ($slot === 'AM') $am = 'P'; else $pm = 'P';
                $this->db->Update(
                    "UPDATE student_attendance SET am_status=?, pm_status=? WHERE id=?",
                    [$am,$pm,(int)$existing['id']]
                );
            } else {
                $am = ($slot === 'AM') ? 'P' : 'A';
                $pm = ($slot === 'PM') ? 'P' : 'A';
                $this->db->Insert(
                    "INSERT INTO student_attendance (student_id, section_id, curriculum_id, attendance_date, am_status, pm_status, remarks)
                     VALUES (?,?,?,?,?,?,NULL)",
                    [$studentId,$sectionId,$curriculumId,$date,$am,$pm]
                );
            }

            $name = trim(($stu['account_last_name'] ?? '').', '.($stu['account_first_name'] ?? ''));
            echo json_encode(['status'=>true,'message'=>"Recorded $slot for $name",'student_id'=>$studentId]);

        } catch (Throwable $e){
            error_log('punch error: '.$e->getMessage());
            echo json_encode(['status'=>false,'message'=>'Punch failed.']);
        }
    }

    public function recent() {
        try {
            $req = getRequestAll();
            $uid = $this->uid();
            if (!$uid) { echo json_encode(['status'=>false,'message'=>'Not logged in.']); return; }

            $limit = (int)($req['limit'] ?? 7);
            if ($limit < 1)  $limit = 7;
            if ($limit > 14) $limit = 14;

            // Latest active enrollment for this student
            $enr = $this->one(
                "SELECT rs.section_id, rs.curriculum_id,
                        s.name AS section_name, gl.name AS grade_name, c.school_year
                   FROM registrar_student rs
                   JOIN curriculum   c  ON c.id = rs.curriculum_id AND c.deleted=0
                   JOIN section      s  ON s.id = rs.section_id AND s.deleted=0
                   JOIN grade_level  gl ON gl.id = s.grade_id
                  WHERE rs.deleted=0 AND rs.status=1 AND rs.student_id=?
               ORDER BY c.school_year DESC
                  LIMIT 1",
                [(int)$uid]
            );
            if (!$enr) { echo json_encode(['status'=>false,'message'=>'No active enrollment found.']); return; }

            $sid = (int)$enr['section_id'];
            $cid = (int)$enr['curriculum_id'];

            // Last N school days (Mon–Fri), newest first
            $days = [];
            $d = new DateTime('today');
            while (count($days) < $limit) {
                $dow = (int)$d->format('N'); // Mon=1..Sun=7
                if ($dow >= 1 && $dow <= 5) $days[] = $d->format('Y-m-d');
                $d->modify('-1 day');
            }

            // Fetch rows for those days
            $in = implode(',', array_fill(0, count($days), '?'));
            $params = array_merge([(int)$uid, $sid, $cid], $days);
            $rows = $this->many(
                "SELECT attendance_date, am_status, pm_status, COALESCE(remarks,'') AS remarks
                   FROM student_attendance
                  WHERE student_id=? AND section_id=? AND curriculum_id=? AND attendance_date IN ($in)",
                $params
            );
            $byDate = [];
            foreach ($rows as $r) { $byDate[$r['attendance_date']] = $r; }

            // Build payload
            $items = [];
            foreach ($days as $ymd) {
                $dt  = DateTime::createFromFormat('Y-m-d', $ymd);
                $row = $byDate[$ymd] ?? null;

                if ($row) {
                    $am = (strtoupper((string)$row['am_status']) === 'P') ? 'Present'
                         : ((strtoupper((string)$row['am_status']) === 'A') ? 'Absent' : null);
                    $pm = (strtoupper((string)$row['pm_status']) === 'P') ? 'Present'
                         : ((strtoupper((string)$row['pm_status']) === 'A') ? 'Absent' : null);

                    $remarks = trim((string)$row['remarks']);
                } else {
                    $am = null;
                    $pm = null;
                    $remarks = '';
                }

                $items[] = [
                    'ymd'     => $ymd,
                    'month'   => $dt->format('F'),
                    'date'    => (int)$dt->format('j'),
                    'day'     => $dt->format('l'),
                    'year'    => (int)$dt->format('Y'),
                    'am'      => $am,       // null if no record
                    'pm'      => $pm,       // null if no record
                    'remarks' => $remarks,  // '' if none
                ];
            }

            echo json_encode([
                'status' => true,
                'meta'   => [
                    'grade_name'   => $enr['grade_name'] ?? '',
                    'section_name' => $enr['section_name'] ?? '',
                    'school_year'  => $enr['school_year'] ?? '',
                ],
                'items'  => $items,
            ]);
        } catch (Throwable $e) {
            error_log('recent attendance error: '.$e->getMessage());
            echo json_encode(['status'=>false,'message'=>'Failed to load recent attendance.']);
        }
    }

    public function js(){  return [$this->view.'/js/custom.js']; }
    public function css(){ return []; } // CSS is inline in the view
}
