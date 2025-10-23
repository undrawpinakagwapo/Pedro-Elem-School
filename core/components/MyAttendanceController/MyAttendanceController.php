<?php

class MyAttendanceController
{
    protected $db;
    protected $view = 'MyAttendanceController';

    // Prefer the exact School Year tied to the student's active registrar row for the selected section.
    // Fallbacks: date-inferred SY, then most-recent curriculum.
    private bool $USE_SECTION_EXACT_SY = true;

    public function __construct($db) {
        // If you need auth, enable this:
        // if (empty($_SESSION['user_id']) || empty($_SESSION['verify']) || (int)($_SESSION['status'] ?? 0) !== 1) {
        //     header('Location: /auth'); exit;
        // }
        $this->db = $db;
    }

    /* ===== tiny utils ===== */
    private function one($sql,$p=[]){ $r=$this->db->Select($sql,$p); return $r? $r[0]:null; }
    private function many($sql,$p=[]){ return $this->db->Select($sql,$p); }
    private function uid(){   return $_SESSION['user_id']   ?? null; }
    private function utype(){ return $_SESSION['user_type'] ?? null; }

    private function resolveStudentId(array $req) : ?int {
        if (!empty($req['student_id']) && (int)$req['student_id'] > 0) return (int)$req['student_id'];
        $ut  = (int)($_SESSION['user_type'] ?? 0);
        $uid = (int)($_SESSION['user_id']   ?? 0);
        if ($ut === 3 && $uid > 0) return $uid; // student
        if (!empty($_SESSION['student_id']))        return (int)$_SESSION['student_id'];
        if (!empty($_SESSION['child_id']))          return (int)$_SESSION['child_id'];
        if (!empty($_SESSION['linked_student_id'])) return (int)$_SESSION['linked_student_id'];
        if ($uid > 0) {
            $row = $this->one("SELECT user_id FROM users WHERE user_id=? AND deleted=0 LIMIT 1", [$uid]);
            if ($row) return (int)$row['user_id'];
        }
        return null;
    }

    /* ===== student-scoped refs ===== */
    private function sectionsForStudent($studentId){
        return $this->many(
            "SELECT DISTINCT s.id, s.name, gl.name AS grade_name
               FROM registrar_student rs
               JOIN section s      ON s.id = rs.section_id
               JOIN grade_level gl ON gl.id = s.grade_id
              WHERE rs.deleted=0 AND rs.status=1 AND rs.student_id=?
           ORDER BY gl.name, s.name",
           [$studentId]
        );
    }

    private function curriculaForStudent($studentId, $sectionId){
        return $this->many(
            "SELECT DISTINCT c.id, c.school_year
               FROM registrar_student rs
               JOIN curriculum c ON c.id = rs.curriculum_id
              WHERE rs.deleted=0 AND rs.status=1
                AND rs.student_id=? AND rs.section_id=?
           ORDER BY c.school_year DESC",
           [$studentId, $sectionId]
        );
    }

    /** Prefer curriculum bound to the active registrar row for this section (exact SY). */
    private function activeCurriculumForStudentSection($studentId, $sectionId){
        return $this->one(
            "SELECT rs.curriculum_id, c.school_year
               FROM registrar_student rs
               JOIN curriculum c ON c.id = rs.curriculum_id
              WHERE rs.deleted=0 AND rs.status=1
                AND rs.student_id=? AND rs.section_id=?
           ORDER BY rs.id DESC
              LIMIT 1",
            [$studentId, $sectionId]
        );
    }

    /** Parse "YYYY-YYYY" (with optional labels) -> [start,end] or null */
    private function parseSY($text){
        if (!is_string($text)) return null;
        if (preg_match('/(\d{4})\D+(\d{4})/', $text, $m)) {
            return [ (int)$m[1], (int)$m[2] ];
        }
        return null;
    }

    /** Given a date, infer school year (Jun–Dec => Y–Y+1; Jan–May => Y-1–Y). */
    private function expectedSYRangeForDate(DateTime $d){
        $y = (int)$d->format('Y');
        $m = (int)$d->format('n');
        if ($m >= 6) return [$y, $y+1];
        return [$y-1, $y];
    }

    /** Pick curriculum by priority:
     * 1) Exact registrar row for section,
     * 2) Match inferred SY from date,
     * 3) Most recent.
     */
    private function pickCurriculumForDate($studentId, $sectionId, DateTime $date){
        if ($this->USE_SECTION_EXACT_SY) {
            $active = $this->activeCurriculumForStudentSection($studentId, $sectionId);
            if ($active && !empty($active['curriculum_id'])) {
                return [ (int)$active['curriculum_id'], (string)$active['school_year'] ];
            }
        }

        $list = $this->curriculaForStudent($studentId, $sectionId);
        if (!$list) return [null, ''];

        [$ey1, $ey2] = $this->expectedSYRangeForDate($date);
        foreach ($list as $c) {
            $p = $this->parseSY($c['school_year'] ?? '');
            if ($p && $p[0] === $ey1 && $p[1] === $ey2) {
                return [ (int)$c['id'], (string)$c['school_year'] ];
            }
        }
        // fallback to first entry (already DESC by year)
        return [ (int)$list[0]['id'], (string)$list[0]['school_year'] ];
    }

    /** Map of Y-m-d => {am_status, pm_status, remarks} for all entries in the month */
    private function monthlyAttendance($studentId, $sectionId, $curriculumId, DateTime $firstDay, DateTime $lastDay){
        $rows = $this->many(
            "SELECT attendance_date, am_status, pm_status, remarks
               FROM student_attendance
              WHERE student_id=? AND section_id=? AND curriculum_id=?
                AND attendance_date BETWEEN ? AND ?
           ORDER BY attendance_date",
            [
                $studentId, $sectionId, $curriculumId,
                $firstDay->format('Y-m-d'), $lastDay->format('Y-m-d')
            ]
        );
        $map = [];
        foreach ($rows as $r){
            $k = (string)$r['attendance_date'];
            $map[$k] = [
                'am_status' => ($r['am_status'] ?? null),
                'pm_status' => ($r['pm_status'] ?? null),
                'remarks'   => ($r['remarks']   ?? null),
            ];
        }
        return $map;
    }

    /** Holiday set for the month (central table + any attendance row with remarks like 'HOLIDAY'). */
    private function holidaysForMonth($sectionId, $curriculumId, DateTime $firstDay, DateTime $lastDay){
        $set = [];

        // 1) Central table (if present)
        try {
            $rows = $this->many(
                "SELECT date FROM attendance_holidays
                  WHERE section_id=? AND curriculum_id=?
                    AND date BETWEEN ? AND ?",
                [$sectionId,$curriculumId,$firstDay->format('Y-m-d'),$lastDay->format('Y-m-d')]
            );
            foreach ($rows as $r) { $set[(string)$r['date']] = true; }
        } catch (\Throwable $e) {
            // table may not exist — ignore
        }

        // 2) Any student_attendance where remarks = 'HOLIDAY' (case-insensitive)
        try {
            $r2 = $this->many(
                "SELECT DISTINCT attendance_date AS date
                   FROM student_attendance
                  WHERE section_id=? AND curriculum_id=?
                    AND UPPER(TRIM(COALESCE(remarks,'')))='HOLIDAY'
                    AND attendance_date BETWEEN ? AND ?",
                [$sectionId,$curriculumId,$firstDay->format('Y-m-d'),$lastDay->format('Y-m-d')]
            );
            foreach ($r2 as $r) { $set[(string)$r['date']] = true; }
        } catch (\Throwable $e) {}

        return $set; // keys: 'YYYY-MM-DD'
    }

    private function monthFrameFromDate(DateTime $d){
        $firstDay = new DateTime($d->format('Y-m-01'));
        $lastDay  = new DateTime($d->format('Y-m-t'));
        return [$firstDay,$lastDay];
    }

    /** Build UI context with monthly rows (Mon–Fri only), including 'is_holiday'. */
    private function buildContext($req){
        $sid = $this->resolveStudentId($req);
        $dateStr = isset($req['date']) && preg_match('/^\d{4}-\d{2}-\d{2}$/',(string)$req['date'])
                ? (string)$req['date'] : date('Y-m-d');
        $d = new DateTime($dateStr);

        if (!$sid) {
            return [
                'student_id'=>null,
                'sections'=>[], 'section_id'=>null,
                'school_year'=>'',
                'curriculum_id'=>null,
                'date'=>$dateStr, 'days'=>[]
            ];
        }

        $sections   = $this->sectionsForStudent($sid);
        $section_id = $sections ? (int)($req['section_id'] ?? $sections[0]['id']) : null;

        $school_year = ''; $curriculum_id = null;
        if ($section_id) {
            [$curriculum_id, $school_year] = $this->pickCurriculumForDate($sid, $section_id, $d);
        }

        $daysOut = [];
        if ($section_id && $curriculum_id) {
            [$first,$last] = $this->monthFrameFromDate($d);

            // Build map of per-day attendance + a holiday set
            $map = $this->monthlyAttendance($sid,$section_id,$curriculum_id,$first,$last);
            $hol = $this->holidaysForMonth($section_id,$curriculum_id,$first,$last);

            // Generate Mon–Fri rows for the month
            for ($cur = clone $first; $cur <= $last; $cur->modify('+1 day')) {
                $dow = (int)$cur->format('N'); // 1..7 (Mon=1)
                if ($dow < 1 || $dow > 5) continue; // only Mon–Fri

                $ymd = $cur->format('Y-m-d');
                $isHoliday = !empty($hol[$ymd]);

                $rec = $map[$ymd] ?? null;
                $am  = $isHoliday ? null : ($rec['am_status'] ?? null);
                $pm  = $isHoliday ? null : ($rec['pm_status'] ?? null);
                // If server marks a holiday via remarks, normalize that too
                $remarksRaw = $rec['remarks'] ?? '';
                $isRemHol   = (is_string($remarksRaw) && strtoupper(trim($remarksRaw)) === 'HOLIDAY');
                if ($isRemHol) $isHoliday = true;

                $rem = $isHoliday ? '' : (($remarksRaw ?? '') ?: '');

                $daysOut[] = [
                    'date'      => $ymd,
                    'weekday'   => $cur->format('D'), // Mon, Tue...
                    'am_status' => $am,
                    'pm_status' => $pm,
                    'remarks'   => $rem,
                    'is_holiday'=> $isHoliday ? 1 : 0,
                ];
            }
        }

        return [
            'student_id'    => $sid,
            'sections'      => $sections,
            'section_id'    => $section_id,
            'school_year'   => $school_year,
            'curriculum_id' => $curriculum_id,
            'date'          => $dateStr,
            'days'          => $daysOut,
        ];
    }

    /* ===== endpoints ===== */
    public function index(){
        $data = $this->buildContext($_GET ?? []);
        return [
            'header'  => 'My Attendance',
            'content' => loadView('components/'.$this->view.'/views/custom', $data),
        ];
    }

    public function fetch(){
        $req = getRequestAll();
        $sid = $this->resolveStudentId($req);
        if (!$sid) { echo json_encode(['status'=>false,'message'=>'Missing student_id.']); return; }
        $ctx = $this->buildContext($req + ['student_id'=>$sid]);
        echo json_encode(['status'=>true] + $ctx);
    }

    public function js(){  return [$this->view.'/js/custom.js']; }
    public function css(){ return []; } // CSS inline in the view
}
