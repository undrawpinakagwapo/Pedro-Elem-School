<?php
/* PhpSpreadsheet imports removed (export features disabled) */

class StudentGradeEntryController
{
    protected $db;
    protected $view = 'StudentGradeEntryController';
    // Change to 'subject' if your DB table is singular
    protected $subjectTable = 'subjects';

    // ✅ new: core-values table name
    protected $coreValuesTable = 'student_subject_core_values';

    // ✅ new: per-behavior rows table
    protected $coreValuesRowsTable = 'student_subject_core_values_rows';


    public function __construct($db) {
        // If you want strict auth like other modules, uncomment:
        // if (empty($_SESSION['user_id']) || empty($_SESSION['verify']) || (int)($_SESSION['status'] ?? 0) !== 1) {
        //     header('Location: /auth'); exit;
        // }
        $this->db = $db;
    }

    /* ================== tiny utils ================== */
    private function one($sql,$p=[]){ $r=$this->db->Select($sql,$p); return $r? $r[0]:null; }
    private function many($sql,$p=[]){ return $this->db->Select($sql,$p); }
    private function uid(){   return $_SESSION['user_id']   ?? null; }
    private function utype(){ return $_SESSION['user_type'] ?? null; } // 1=admin,2=teacher,...

    /* ===== transaction helpers (safe fallbacks/no-ops) ===== */
    private function txBegin(){
        foreach (['Begin','begin','beginTransaction','startTransaction','StartTrans','BeginTrans','beginTrans'] as $m) {
            if (method_exists($this->db, $m)) { return $this->db->{$m}(); }
        }
        return null; // no-op
    }
    private function txCommit(){
        foreach (['Commit','commit','commitTransaction','CompleteTrans','endTransaction','CommitTrans'] as $m) {
            if (method_exists($this->db, $m)) { return $this->db->{$m}(); }
        }
        return null; // no-op
    }
    private function txRollback(){
        foreach (['Rollback','rollback','rollBack','FailTrans','cancelTransaction','RollbackTrans'] as $m) {
            if (method_exists($this->db, $m)) { return $this->db->{$m}(); }
        }
        return null; // no-op
    }

    /* ================== access helpers ================== */
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
    private function subjectsForTeacher($teacherId, $curriculumId){
        $cur = $this->one("SELECT grade_id AS section_id FROM curriculum WHERE id=? AND deleted=0 LIMIT 1", [$curriculumId]);
        if (!$cur) return [];
        $sectionId = (int)$cur['section_id'];
        $st = $this->subjectTable;

        if ($this->isAdviserOfSection($teacherId,$sectionId) || $this->isAdviserOfCurriculum($teacherId,$curriculumId)) {
            return $this->many(
                "SELECT cc.subject_id AS id, s.code, s.name
                   FROM curriculum_child cc
                   JOIN {$st} s ON s.id = cc.subject_id
                  WHERE cc.deleted=0 AND cc.curriculum_id=?
               ORDER BY s.name", [$curriculumId]
            );
        }
        return $this->many(
            "SELECT cc.subject_id AS id, s.code, s.name
               FROM curriculum_child cc
               JOIN {$st} s ON s.id = cc.subject_id
              WHERE cc.deleted=0 AND cc.curriculum_id=? AND cc.adviser_id=?
           ORDER BY s.name", [$curriculumId,$teacherId]
        );
    }
    private function assertTeacherAccess($teacherId, $sectionId, $curriculumId, $subjectId){
        $cur = $this->one("SELECT grade_id AS section_id FROM curriculum WHERE id=? AND deleted=0 LIMIT 1", [$curriculumId]);
        if (!$cur || (int)$cur['section_id'] !== (int)$sectionId) return false;
        if ($this->isAdviserOfSection($teacherId,$sectionId) || $this->isAdviserOfCurriculum($teacherId,$curriculumId)) return true;

        return (bool)$this->one(
            "SELECT 1 FROM curriculum_child
              WHERE deleted=0 AND curriculum_id=? AND subject_id=? AND adviser_id=? LIMIT 1",
            [$curriculumId,$subjectId,$teacherId]
        );
    }

    /** Show ALL enrolled students for (section,curriculum,subject); LEFT JOIN grades */
    private function entryRows($sectionId, $curriculumId, $subjectId, $q = ''){
        $where = ["rs.deleted=0", "rs.status=1", "u.status=1", "rs.section_id=?", "rs.curriculum_id=?"];
        $p = [(int)$sectionId, (int)$curriculumId];
        if ($q !== '') {
            $like = '%'.$q.'%';
            $where[]="(u.account_last_name LIKE ? OR u.account_first_name LIKE ? OR u.LRN LIKE ?)";
            array_push($p,$like,$like,$like);
        }
        $sql = "SELECT
                    u.user_id AS student_id,
                    CONCAT(u.account_last_name, ', ', u.account_first_name, ' ', u.account_middle_name) AS full_name,
                    u.LRN,
                    u.gender AS gender,               -- ✅ include gender
                    ssg.id AS grade_row_id,
                    ssg.q1, ssg.q2, ssg.q3, ssg.q4, ssg.final_average
                FROM registrar_student rs
                JOIN users u ON u.user_id = rs.student_id
                LEFT JOIN student_subject_grades ssg
                       ON  ssg.student_id    = rs.student_id
                       AND ssg.section_id    = rs.section_id
                       AND ssg.curriculum_id = rs.curriculum_id
                       AND ssg.subject_id    = ?
                       AND ssg.deleted       = 0
               WHERE ".implode(' AND ', $where)."
               ORDER BY u.account_last_name, u.account_first_name";
        array_unshift($p, (int)$subjectId);
        return $this->many($sql,$p);
    }

    /* ================== page build ================== */
    private function buildContext($req){
        $uid = $this->uid(); $ut = $this->utype();
        $q   = isset($req['q']) ? trim((string)$req['q']) : '';

        if ($ut != 2) { // admin/principal
            $sections = $this->many(
                "SELECT s.id, s.name, gl.name AS grade_name
                   FROM section s
                   JOIN grade_level gl ON gl.id = s.grade_id
                  WHERE s.deleted=0
               ORDER BY gl.name, s.name");
            $section_id = $sections ? (int)($req['section_id'] ?? $sections[0]['id']) : null;

            $curricula = $section_id ? $this->many(
                "SELECT c.id, c.school_year FROM curriculum c
                  WHERE c.deleted=0 AND c.grade_id=? ORDER BY c.school_year DESC", [$section_id]
            ) : [];
            $curriculum_id = $curricula ? (int)($req['curriculum_id'] ?? $curricula[0]['id']) : null;

            $st = $this->subjectTable;
            $subjects = $curriculum_id ? $this->many(
                "SELECT cc.subject_id AS id, s.code, s.name
                   FROM curriculum_child cc
                   JOIN {$st} s ON s.id = cc.subject_id
                  WHERE cc.deleted=0 AND cc.curriculum_id=?
               ORDER BY s.name", [$curriculum_id]
            ) : [];
            $subject_id = $subjects ? (int)($req['subject_id'] ?? $subjects[0]['id']) : null;

            $rows = ($section_id && $curriculum_id && $subject_id) ? $this->entryRows($section_id,$curriculum_id,$subject_id,$q) : [];

            return compact('sections','section_id','curricula','curriculum_id','subjects','subject_id','rows');
        }

        // teacher
        $sections = $this->sectionsForTeacher($uid);
        $section_id = $sections ? (int)($req['section_id'] ?? $sections[0]['id']) : null;

        $curricula = $section_id ? $this->curriculaForTeacher($uid,$section_id) : [];
        $curriculum_id = $curricula ? (int)($req['curriculum_id'] ?? $curricula[0]['id']) : null;

        $subjects = $curriculum_id ? $this->subjectsForTeacher($uid,$curriculum_id) : [];
        $subject_id = $subjects ? (int)($req['subject_id'] ?? $subjects[0]['id']) : null;

        $rows = [];
        if ($section_id && $curriculum_id && $subject_id &&
            $this->assertTeacherAccess($uid,$section_id,$curriculum_id,$subject_id)) {
            $rows = $this->entryRows($section_id,$curriculum_id,$subject_id,$q);
        }
        return compact('sections','section_id','curricula','curriculum_id','subjects','subject_id','rows');
    }

    /* ============ helpers for Core Values / SSG existence ============ */
    private function getOrCreateSSG($studentId,$subjectId,$sectionId,$curriculumId){
        $row = $this->one(
            "SELECT id, school_year FROM student_subject_grades
              WHERE deleted=0 AND student_id=? AND subject_id=? AND section_id=? AND curriculum_id=?
              LIMIT 1",
            [$studentId,$subjectId,$sectionId,$curriculumId]
        );
        if ($row) return (int)$row['id'];

        // create a shell SSG row to bind core values
        $cur = $this->one("SELECT school_year FROM curriculum WHERE id=? AND deleted=0 LIMIT 1", [$curriculumId]);
        $sy  = $cur['school_year'] ?? null;
        $uid = $this->uid();

        $this->db->Insert(
            "INSERT INTO student_subject_grades
               (student_id, subject_id, section_id, curriculum_id, school_year,
                q1, q2, q3, q4, final_average,
                status, deleted, added_by, latest_edited_by)
             VALUES (?,?,?,?,?,
                     NULL,NULL,NULL,NULL,NULL,
                     1,0,?,?)",
            [$studentId,$subjectId,$sectionId,$curriculumId,$sy,$uid,$uid]
        );
        $new = $this->one(
            "SELECT id FROM student_subject_grades
              WHERE deleted=0 AND student_id=? AND subject_id=? AND section_id=? AND curriculum_id=?
              ORDER BY id DESC LIMIT 1",
            [$studentId,$subjectId,$sectionId,$curriculumId]
        );
        return $new ? (int)$new['id'] : 0;
    }

    /* ================== endpoints ================== */
    public function index() {
        $data = $this->buildContext($_GET ?? []);
        return [
            'header'  => 'Student Grade Entry',
            'content' => loadView('components/'.$this->view.'/views/custom', $data),
        ];
    }

    public function fetch() {
        $req = getRequestAll();
        $uid = $this->uid(); $ut = $this->utype();

        $ctx = $this->buildContext($req);

        if ($ut == 2) {
            $sid = isset($req['section_id'])    ? (int)$req['section_id']    : ($ctx['section_id'] ?? null);
            $cid = isset($req['curriculum_id']) ? (int)$req['curriculum_id'] : ($ctx['curriculum_id'] ?? null);
            $sub = isset($req['subject_id'])    ? (int)$req['subject_id']    : ($ctx['subject_id'] ?? null);

            if ($sid && $cid && $sub && !$this->assertTeacherAccess($uid,$sid,$cid,$sub)) {
                echo json_encode(['status'=>false,'message'=>'You are not assigned to this section/subject.']);
                return;
            }
        }

        echo json_encode(['status'=>true] + $ctx);
    }

    public function save() {
        try {
            $req = getRequestAll();
            $uid = $this->uid(); $ut = $this->utype();

            $sectionId    = (int)($req['section_id']    ?? 0);
            $curriculumId = (int)($req['curriculum_id'] ?? 0);
            $subjectId    = (int)($req['subject_id']    ?? 0);
            $rows         = $req['rows'] ?? [];

            if (is_string($rows)) {
                $tmp = json_decode($rows, true);
                if (json_last_error() === JSON_ERROR_NONE) $rows = $tmp;
            }
            if (!$sectionId || !$curriculumId || !$subjectId || !is_array($rows)) {
                echo json_encode(['status'=>false,'message'=>'Missing required fields.']); return;
            }

            if ($ut == 2 && !$this->assertTeacherAccess($uid,$sectionId,$curriculumId,$subjectId)) {
                echo json_encode(['status'=>false,'message'=>'You are not assigned to this section/subject.']); return;
            }

            // get school year to stamp
            $cur = $this->one("SELECT school_year FROM curriculum WHERE id=? AND deleted=0 LIMIT 1", [$curriculumId]);
            $schoolYear = $cur['school_year'] ?? null;

            $this->txBegin();

            foreach ($rows as $r) {
                $studentId = (int)($r['student_id'] ?? 0);
                if (!$studentId) continue;

                // normalize to 0..100 or NULL
                $q1 = isset($r['q1']) && $r['q1'] !== '' ? max(0, min(100, (float)$r['q1'])) : null;
                $q2 = isset($r['q2']) && $r['q2'] !== '' ? max(0, min(100, (float)$r['q2'])) : null;
                $q3 = isset($r['q3']) && $r['q3'] !== '' ? max(0, min(100, (float)$r['q3'])) : null;
                $q4 = isset($r['q4']) && $r['q4'] !== '' ? max(0, min(100, (float)$r['q4'])) : null;

                $existing = $this->one(
                    "SELECT id, q1, q2, q3, q4, school_year, added_by
                       FROM student_subject_grades
                      WHERE student_id=? AND subject_id=? AND section_id=? AND curriculum_id=? AND deleted=0
                      LIMIT 1",
                    [$studentId,$subjectId,$sectionId,$curriculumId]
                );

                // compute final average
                $vals  = array_values(array_filter([$q1,$q2,$q3,$q4], fn($v)=>$v!==null&&$v!==''));
                $final = $vals ? round(array_sum($vals)/count($vals), 2) : null;

                if ($existing) {
                    // merge with existing (keep old if incoming null)
                    if ($q1 === null) $q1 = $existing['q1'];
                    if ($q2 === null) $q2 = $existing['q2'];
                    if ($q3 === null) $q3 = $existing['q3'];
                    if ($q4 === null) $q4 = $existing['q4'];
                    $vals2  = array_values(array_filter([$q1,$q2,$q3,$q4], fn($v)=>$v!==null&&$v!=='')); 
                    $final2 = $vals2 ? round(array_sum($vals2)/count($vals2), 2) : null;

                    $syToSet = ($schoolYear !== null && $schoolYear !== '') ? $schoolYear : $existing['school_year'];

                    // ✅ added_by gets set only if it is currently NULL
                    $this->db->Update(
                        "UPDATE student_subject_grades
                            SET q1=?, q2=?, q3=?, q4=?, final_average=?, school_year=?,
                                added_by = COALESCE(added_by, ?),
                                latest_edited_by=?
                          WHERE id=?",
                        [$q1,$q2,$q3,$q4,$final2,$syToSet,$uid,$uid,(int)$existing['id']]
                    );
                } else {
                    // fresh insert — stamp both added_by and latest_edited_by
                    $this->db->Insert(
                        "INSERT INTO student_subject_grades
                           (student_id, subject_id, section_id, curriculum_id, school_year,
                            q1, q2, q3, q4, final_average,
                            status, deleted, added_by, latest_edited_by)
                         VALUES (?,?,?,?,?,
                                 ?,?,?,?,?,
                                 1, 0, ?, ?)",
                        [
                            $studentId, $subjectId, $sectionId, $curriculumId, $schoolYear,
                            $q1, $q2, $q3, $q4, $final,
                            $uid, $uid
                        ]
                    );
                }
            }

            $this->txCommit();
            echo json_encode(['status'=>true,'message'=>'Grades saved successfully.']);
        } catch (Throwable $e) {
            $this->txRollback();
            error_log('Grade entry save error: '.$e->getMessage());
            echo json_encode(['status'=>false,'message'=>'Save failed: '.$e->getMessage()]);
        }
    }

    public function gradeSlip() {
        try {
            $req = getRequestAll();
            $uid = $this->uid(); $ut = $this->utype();

            $sectionId    = (int)($req['section_id']    ?? 0);
            $curriculumId = (int)($req['curriculum_id'] ?? 0);
            $studentId    = (int)($req['student_id']    ?? 0);

            if (!$sectionId || !$curriculumId || !$studentId) {
                echo json_encode(['status'=>false,'message'=>'Missing required fields.']); return;
            }

            // Access: teachers must be section/curriculum advisers to view full slip; admins/principals allowed.
            if ($ut == 2) {
                $isSecAdv = $this->isAdviserOfSection($uid,$sectionId);
                $isCurAdv = $this->isAdviserOfCurriculum($uid,$curriculumId);
                if (!($isSecAdv || $isCurAdv)) {
                    echo json_encode(['status'=>false,'message'=>'You are not allowed to view the full grade slip for this section/curriculum.']); return;
                }
            }

            // validate section-curriculum pair & get meta
            $cur = $this->one("SELECT c.school_year, c.grade_id AS section_id FROM curriculum c WHERE c.id=? AND c.deleted=0 LIMIT 1", [$curriculumId]);
            if (!$cur || (int)$cur['section_id'] !== (int)$sectionId) {
                echo json_encode(['status'=>false,'message'=>'Invalid section/curriculum pairing.']); return;
            }
            $sec = $this->one("SELECT s.name AS section_name, gl.name AS grade_name FROM section s JOIN grade_level gl ON gl.id=s.grade_id WHERE s.id=? AND s.deleted=0 LIMIT 1", [$sectionId]);

            // ensure student is enrolled in this section/curriculum
            $stu = $this->one(
                "SELECT u.user_id AS id,
                        CONCAT(u.account_last_name, ', ', u.account_first_name, ' ', u.account_middle_name) AS full_name,
                        u.LRN,
                        u.gender AS gender
                   FROM users u
                   JOIN registrar_student rs ON rs.student_id=u.user_id
                  WHERE u.user_id=? AND rs.deleted=0 AND rs.status=1 AND rs.section_id=? AND rs.curriculum_id=? LIMIT 1",
                [$studentId,$sectionId,$curriculumId]
            );
            if (!$stu) { echo json_encode(['status'=>false,'message'=>'Student not found in this section/curriculum.']); return; }

            // subjects for this curriculum (include all; left join grades)
            $st = $this->subjectTable;
            $grades = $this->many(
                "SELECT cc.subject_id AS id, s.code, s.name,
                        g.q1, g.q2, g.q3, g.q4, g.final_average
                   FROM curriculum_child cc
                   JOIN {$st} s ON s.id=cc.subject_id
              LEFT JOIN student_subject_grades g
                     ON g.deleted=0
                    AND g.student_id=?
                    AND g.section_id=?
                    AND g.curriculum_id=?
                    AND g.subject_id=cc.subject_id
                  WHERE cc.deleted=0 AND cc.curriculum_id=?
               ORDER BY s.name",
                [$studentId,$sectionId,$curriculumId,$curriculumId]
            );

            echo json_encode([
                'status'  => true,
                'student' => [
                    'id'           => (int)$stu['id'],
                    'full_name'    => $stu['full_name'],
                    'LRN'          => $stu['LRN'],
                    'gender'       => $stu['gender'] ?? null, // ✅ expose gender
                    'grade_name'   => $sec['grade_name'] ?? '',
                    'section_name' => $sec['section_name'] ?? '',
                    'school_year'  => $cur['school_year'] ?? '',
                ],
                'subjects' => array_map(function($g){
                    return [
                        'id'            => (int)$g['id'],
                        'code'          => $g['code'],
                        'name'          => $g['name'],
                        'q1'            => ($g['q1'] === null ? null : (float)$g['q1']),
                        'q2'            => ($g['q2'] === null ? null : (float)$g['q2']),
                        'q3'            => ($g['q3'] === null ? null : (float)$g['q3']),
                        'q4'            => ($g['q4'] === null ? null : (float)$g['q4']),
                        'final_average' => ($g['final_average'] === null ? null : (float)$g['final_average']),
                    ];
                }, $grades),
            ]);
        } catch (Throwable $e) {
            error_log('Grade slip error: '.$e->getMessage());
            echo json_encode(['status'=>false,'message'=>'Failed to generate grade slip.']);
        }
    }

    public function exportSf10() {
        try {
            // IMPORTANT: filename is case-sensitive on many servers
            require_once __DIR__ . '/ExportSf10Controller.php';

            // Hand off to the dedicated exporter
            $ctl = new ExportSf10Controller($this->db);
            $ctl->export(); // expects POST: section_id, curriculum_id, student_id
        } catch (Throwable $e) {
            http_response_code(500);
            echo 'Export failed: ' . htmlspecialchars($e->getMessage());
        }
    }

    public function exportSf9(){
        try {
            require_once __DIR__ . '/ExportSf9Controller.php';
            $ctl = new ExportSf9Controller($this->db);
            $ctl->export();
        } catch (Throwable $e) {
            http_response_code(500);
            echo 'Export failed: ' . htmlspecialchars($e->getMessage());
        }
    }

    public function summary(){
        $req = getRequestAll();
        $uid = $this->uid(); $ut = $this->utype();

        $sectionId    = (int)($req['section_id']    ?? 0);
        $curriculumId = (int)($req['curriculum_id'] ?? 0);
        if (!$sectionId || !$curriculumId) {
            echo json_encode(['status'=>false,'message'=>'Missing required fields.']); return;
        }

        // Subjects for the curriculum
        $st = $this->subjectTable;
        $subjects = $this->many(
            "SELECT cc.subject_id AS id, s.code, s.name
               FROM curriculum_child cc
               JOIN {$st} s ON s.id = cc.subject_id
              WHERE cc.deleted=0 AND cc.curriculum_id=?
           ORDER BY s.name", [$curriculumId]
        );

        // Teacher access filtering
        $allowedIds = [];
        if ($ut == 2) {
            $isSecAdv = $this->isAdviserOfSection($uid,$sectionId);
            $isCurAdv = $this->isAdviserOfCurriculum($uid,$curriculumId);
            if ($isSecAdv || $isCurAdv) {
                $allowedIds = array_column($subjects,'id');
            } else {
                $rows = $this->many(
                    "SELECT cc.subject_id AS id
                       FROM curriculum_child cc
                      WHERE cc.deleted=0 AND cc.curriculum_id=? AND cc.adviser_id=?",
                    [$curriculumId,$uid]
                );
                $allowedIds = array_column($rows,'id');
            }
        } else {
            $allowedIds = array_column($subjects,'id'); // admin/principal: all
        }
        $allowedMap = array_flip($allowedIds);

        // Students enrolled in section+curriculum
        $students = $this->many(
    "SELECT u.user_id AS id,
            u.account_last_name   AS last,
            u.account_first_name  AS first,
            u.account_middle_name AS middle,
            u.LRN,
            u.gender AS gender
       FROM registrar_student rs
       JOIN users u ON u.user_id = rs.student_id
      WHERE rs.deleted=0 AND rs.status=1 AND u.status=1
        AND rs.section_id=? AND rs.curriculum_id=?
   ORDER BY u.account_last_name, u.account_first_name",
    [$sectionId,$curriculumId]
);
        $studentCount = count($students);
        $studentMap = [];
        foreach ($students as $s) $studentMap[(int)$s['id']] = $s;

        // Pull all subject grades for this sec/curr
        $grades = $this->many(
            "SELECT ssg.student_id, ssg.subject_id,
                    ssg.q1, ssg.q2, ssg.q3, ssg.q4, ssg.final_average
               FROM student_subject_grades ssg
              WHERE ssg.deleted=0 AND ssg.section_id=? AND ssg.curriculum_id=?",
            [$sectionId,$curriculumId]
        );

        /* ========= Subject-level aggregates (backward-compat fields) ========= */
        $subjMeta = [];
        foreach ($subjects as $sbj) {
            if (!isset($allowedMap[(int)$sbj['id']])) continue;
            $subjMeta[(int)$sbj['id']] = [
                'id'      => (int)$sbj['id'],
                'code'    => $sbj['code'],
                'name'    => $sbj['name'],
                'graded'  => 0,
                'missing' => 0,
                'sum'     => 0.0,
                'min'     => null,
                'max'     => null,
            ];
        }

        // Student aggregates (overall & per-quarter)
        // stuAgg: overall final averages (for legacy top/at_risk/missing)
        // stuQ:   quarter sums/counts (for quarter-based cards)
        $stuAgg = []; // id => ['sum'=>float, 'count'=>int, 'minFinal'=>?float, 'riskSubjects'=>int]
        $stuQ   = []; // id => ['q1_sum'=>, 'q1_cnt'=>, 'q2_sum'=>, 'q2_cnt'=>, 'q3_sum'=>, 'q3_cnt'=>, 'q4_sum'=>, 'q4_cnt'=>]

        $anyQ = ['q1'=>false,'q2'=>false,'q3'=>false,'q4'=>false]; // detect latest_quarter

        foreach ($grades as $g) {
            $sid = (int)$g['student_id'];
            $sub = (int)$g['subject_id'];
            if (!isset($allowedMap[$sub])) continue;
            if (!isset($studentMap[$sid])) continue;

            // Subject-level meta
            $fa = $g['final_average'];
            if ($fa !== null && $fa !== '') {
                $fa = (float)$fa;
                $meta = &$subjMeta[$sub];
                $meta['graded']++;
                $meta['sum'] += $fa;
                $meta['min'] = ($meta['min']===null) ? $fa : min($meta['min'],$fa);
                $meta['max'] = ($meta['max']===null) ? $fa : max($meta['max'],$fa);

                if (!isset($stuAgg[$sid])) $stuAgg[$sid] = ['sum'=>0.0,'count'=>0,'minFinal'=>null,'riskSubjects'=>0];
                $stuAgg[$sid]['sum']   += $fa;
                $stuAgg[$sid]['count'] += 1;
                $stuAgg[$sid]['minFinal'] = ($stuAgg[$sid]['minFinal']===null) ? $fa : min($stuAgg[$sid]['minFinal'],$fa);
                if ($fa < 75) $stuAgg[$sid]['riskSubjects'] += 1;
            }

            // Quarter aggregates per student (q1..q4)
            if (!isset($stuQ[$sid])) $stuQ[$sid] = ['q1_sum'=>0,'q1_cnt'=>0,'q2_sum'=>0,'q2_cnt'=>0,'q3_sum'=>0,'q3_cnt'=>0,'q4_sum'=>0,'q4_cnt'=>0];

            if ($g['q1'] !== null && $g['q1'] !== '') { $stuQ[$sid]['q1_sum'] += (float)$g['q1']; $stuQ[$sid]['q1_cnt']++; $anyQ['q1']=true; }
            if ($g['q2'] !== null && $g['q2'] !== '') { $stuQ[$sid]['q2_sum'] += (float)$g['q2']; $stuQ[$sid]['q2_cnt']++; $anyQ['q2']=true; }
            if ($g['q3'] !== null && $g['q3'] !== '') { $stuQ[$sid]['q3_sum'] += (float)$g['q3']; $stuQ[$sid]['q3_cnt']++; $anyQ['q3']=true; }
            if ($g['q4'] !== null && $g['q4'] !== '') { $stuQ[$sid]['q4_sum'] += (float)$g['q4']; $stuQ[$sid]['q4_cnt']++; $anyQ['q4']=true; }
        }

        // Finish subject outputs (+missing counts)
        $allowedCount = count($allowedIds);
        $subjectsOut = [];
        foreach ($subjMeta as $id => $m) {
            $m['missing'] = max(0, $studentCount - $m['graded']);
            $avg = $m['graded'] ? round($m['sum'] / $m['graded'], 2) : null;
            $subjectsOut[] = [
                'id'      => $m['id'],
                'code'    => $m['code'],
                'name'    => $m['name'],
                'avg'     => $avg,
                'highest' => $m['max'],
                'lowest'  => $m['min'],
                'graded'  => $m['graded'],
                'missing' => $m['missing'],
            ];
        }
        usort($subjectsOut, fn($a,$b)=>strcmp($a['name'],$b['name']));

        // Legacy "top_students" (overall across allowed subjects, using final_average)
        $topOverall = [];
        foreach ($students as $s) {
            $id = (int)$s['id'];
            $agg = $stuAgg[$id] ?? ['sum'=>0,'count'=>0];
            $avg = $agg['count'] ? round($agg['sum']/$agg['count'], 2) : null;
            $topOverall[] = [
                'student_id' => $id,
                'full_name'  => trim($s['last'].', '.$s['first'].' '.($s['middle'] ? mb_substr($s['middle'],0,1).' .' : '')),
                'LRN'        => $s['LRN'],
                'avg'        => $avg,
            ];
        }
        usort($topOverall, function($a,$b){
            if ($a['avg']===null && $b['avg']===null) return 0;
            if ($a['avg']===null) return 1;
            if ($b['avg']===null) return -1;
            if ($a['avg']===$b['avg']) return strcmp($a['full_name'],$b['full_name']);
            return ($a['avg'] > $b['avg']) ? -1 : 1;
        });
        $top10Overall = array_slice($topOverall, 0, 10);

        // Legacy at_risk & missing
        $atRisk = [];
        foreach ($students as $s) {
            $id = (int)$s['id'];
            $agg = $stuAgg[$id] ?? null;
            if (!$agg || $agg['riskSubjects']<=0) continue;
            $atRisk[] = [
                'student_id'   => $id,
                'full_name'    => trim($s['last'].', '.$s['first'].' '.($s['middle'] ? mb_substr($s['middle'],0,1).' .' : '')),
                'LRN'          => $s['LRN'],
                'lowest_final' => $agg['minFinal'],
                'risk_count'   => $agg['riskSubjects'],
            ];
        }
        usort($atRisk, function($a,$b){
            if ($a['lowest_final']===$b['lowest_final']) return strcmp($a['full_name'],$b['full_name']);
            return ($a['lowest_final'] < $b['lowest_final']) ? -1 : 1;
        });

        $missing = [];
        foreach ($students as $s) {
            $id = (int)$s['id'];
            $graded = $stuAgg[$id]['count'] ?? 0;
            $miss = max(0, $allowedCount - $graded);
            if ($miss > 0) {
                $missing[] = [
                    'student_id'    => $id,
                    'full_name'     => trim($s['last'].', '.$s['first'].' '.($s['middle'] ? mb_substr($s['middle'],0,1).' .' : '')),
                    'LRN'           => $s['LRN'],
                    'missing_count' => $miss
                ];
            }
        }
        usort($missing, function($a,$b){
            if ($a['missing_count']===$b['missing_count']) return strcmp($a['full_name'],$b['full_name']);
            return ($a['missing_count'] > $b['missing_count']) ? -1 : 1;
        });

        /* ========= Quarter-based outputs ========= */
        $quarters = ['q1','q2','q3','q4'];
        $top_by_quarter = [];
        $under75_by_quarter = [];

        foreach ($quarters as $qk) {
            $sumKey = $qk.'_sum';
            $cntKey = $qk.'_cnt';
            $rows = [];
            $rowsUnder = [];

            foreach ($students as $s) {
                $id  = (int)$s['id'];
                $agg = $stuQ[$id] ?? ['q1_sum'=>0,'q1_cnt'=>0,'q2_sum'=>0,'q2_cnt'=>0,'q3_sum'=>0,'q3_cnt'=>0,'q4_sum'=>0,'q4_cnt'=>0];
                $sum = (float)($agg[$sumKey] ?? 0);
                $cnt = (int)  ($agg[$cntKey] ?? 0);
                $avg = $cnt ? round($sum/$cnt, 2) : null;

                $row = [
                    'student_id' => $id,
                    'full_name'  => trim($s['last'].', '.$s['first'].' '.($s['middle'] ? mb_substr($s['middle'],0,1).' .' : '')),
                    'LRN'        => $s['LRN'],
                    'avg'        => $avg
                ];
                if ($avg !== null) {
                    $rows[] = $row;
                    if ($avg < 75) $rowsUnder[] = $row;
                }
            }

            // Sort: Top list desc; Under-75 list asc
            usort($rows, function($a,$b){
                if ($a['avg']===$b['avg']) return strcmp($a['full_name'],$b['full_name']);
                return ($a['avg'] > $b['avg']) ? -1 : 1;
            });
            $top_by_quarter[$qk] = array_slice($rows, 0, 10);

            usort($rowsUnder, function($a,$b){
                if ($a['avg']===$b['avg']) return strcmp($a['full_name'],$b['full_name']);
                return ($a['avg'] < $b['avg']) ? -1 : 1;
            });
            $under75_by_quarter[$qk] = $rowsUnder;
        }

        // Guess latest_quarter (highest quarter with any data)
        $latest_quarter = 'q1';
        if     ($anyQ['q4']) $latest_quarter = 'q4';
        elseif ($anyQ['q3']) $latest_quarter = 'q3';
        elseif ($anyQ['q2']) $latest_quarter = 'q2';
        else                 $latest_quarter = 'q1';

        echo json_encode([
            'status'               => true,
            // legacy / backward-compatible fields (still used elsewhere)
            'students'             => $studentCount,
            'subjects'             => array_values($subjectsOut),
            'overall_avg'          => null,
            'top_students'         => $top10Overall,
            'at_risk'              => $atRisk,
            'missing_grades'       => $missing,

            // new quarter-based outputs for dashboard cards
            'top_by_quarter'       => $top_by_quarter,
            'under75_by_quarter'   => $under75_by_quarter,
            'latest_quarter'       => $latest_quarter,
        ], JSON_UNESCAPED_UNICODE|JSON_PARTIAL_OUTPUT_ON_ERROR);
    }

    /* ================== Core Values endpoints (new) ================== */

    /** GET /component/student-grade-entry/fetchCoreValues
     *  POST fields: section_id, curriculum_id, subject_id, student_id
     */
    public function fetchCoreValues() {
    try {
        $req = getRequestAll();
        $uid = $this->uid(); $ut = $this->utype();

        $sectionId    = (int)($req['section_id']    ?? 0);
        $curriculumId = (int)($req['curriculum_id'] ?? 0);
        $subjectId    = (int)($req['subject_id']    ?? 0);
        $studentId    = (int)($req['student_id']    ?? 0);

        if (!$sectionId || !$curriculumId || !$subjectId || !$studentId) {
            echo json_encode(['status'=>false,'message'=>'Missing required fields.']); return;
        }
        if ($ut == 2 && !$this->assertTeacherAccess($uid,$sectionId,$curriculumId,$subjectId)) {
            echo json_encode(['status'=>false,'message'=>'You are not assigned to this section/subject.']); return;
        }

        // find SSG
        $ssg = $this->one(
            "SELECT id FROM student_subject_grades
             WHERE deleted=0 AND student_id=? AND subject_id=? AND section_id=? AND curriculum_id=? LIMIT 1",
            [$studentId,$subjectId,$sectionId,$curriculumId]
        );

        // shape to return to UI:
        // values = { core: [ {behavior_index:1,q1..q4}, {behavior_index:2,q1..q4}, ... ] }
        $values = [
            'maka_diyos'     => [],
            'makatao'        => [],
            'maka_kalikasan' => [],
            'maka_bansa'     => [],
        ];

        if ($ssg) {
            $rows = $this->many(
                "SELECT core_name, behavior_index, q1, q2, q3, q4
                   FROM {$this->coreValuesRowsTable}
                  WHERE deleted=0 AND ssg_id=?
               ORDER BY core_name, behavior_index",
                [(int)$ssg['id']]
            );
            foreach ($rows as $r) {
                $core = $r['core_name'];
                if (!isset($values[$core])) $values[$core] = [];
                $values[$core][] = [
                    'behavior_index' => (int)$r['behavior_index'],
                    'q1' => $r['q1'] ?? '',
                    'q2' => $r['q2'] ?? '',
                    'q3' => $r['q3'] ?? '',
                    'q4' => $r['q4'] ?? '',
                ];
            }

            // Fallback/compat: if no rows yet, try the legacy summary table
            $legacy = $this->one(
                "SELECT
                   md_q1,md_q2,md_q3,md_q4,
                   mt_q1,mt_q2,mt_q3,mt_q4,
                   mk_q1,mk_q2,mk_q3,mk_q4,
                   mb_q1,mb_q2,mb_q3,mb_q4
                 FROM {$this->coreValuesTable}
                WHERE deleted=0 AND ssg_id=? LIMIT 1",
                [(int)$ssg['id']]
            );
            if ($legacy) {
                $inject = function(&$arr,$q1,$q2,$q3,$q4){
                    if (!count($arr)) {
                        $arr[] = ['behavior_index'=>1,'q1'=>$q1??'','q2'=>$q2??'','q3'=>$q3??'','q4'=>$q4??''];
                    }
                };
                $inject($values['maka_diyos'],     $legacy['md_q1']??'', $legacy['md_q2']??'', $legacy['md_q3']??'', $legacy['md_q4']??'');
                $inject($values['makatao'],        $legacy['mt_q1']??'', $legacy['mt_q2']??'', $legacy['mt_q3']??'', $legacy['mt_q4']??'');
                $inject($values['maka_kalikasan'], $legacy['mk_q1']??'', $legacy['mk_q2']??'', $legacy['mk_q3']??'', $legacy['mk_q4']??'');
                $inject($values['maka_bansa'],     $legacy['mb_q1']??'', $legacy['mb_q2']??'', $legacy['mb_q3']??'', $legacy['mb_q4']??'');
            }
        }

        echo json_encode(['status'=>true, 'values'=>$values]);
    } catch (Throwable $e) {
        error_log('fetchCoreValues error: '.$e->getMessage());
        echo json_encode(['status'=>false,'message'=>'Failed to fetch core values.']);
    }
}



    /** POST /component/student-grade-entry/saveCoreValues
     *  POST fields: section_id, curriculum_id, subject_id, student_id, values(JSON)
     *  Values shape:
     *  {
     *    "maka_diyos":{"q1":"AO","q2":"SO","q3":"RO","q4":"NO"},
     *    "makatao":{"q1":"","q2":"","q3":"","q4":""},
     *    "maka_kalikasan":{"q1":"","q2":"","q3":"","q4":""},
     *    "maka_bansa":{"q1":"","q2":"","q3":"","q4":""}
     *  }
     */
    public function saveCoreValues() {
    try {
        $req = getRequestAll();
        $uid = $this->uid(); $ut = $this->utype();

        $sectionId    = (int)($req['section_id']    ?? 0);
        $curriculumId = (int)($req['curriculum_id'] ?? 0);
        $subjectId    = (int)($req['subject_id']    ?? 0);
        $studentId    = (int)($req['student_id']    ?? 0);
        $values       = $req['values'] ?? null;

        if (is_string($values)) {
            $tmp = json_decode($values, true);
            if (json_last_error() === JSON_ERROR_NONE) $values = $tmp;
        }
        if (!$sectionId || !$curriculumId || !$subjectId || !$studentId || !is_array($values)) {
            echo json_encode(['status'=>false,'message'=>'Missing required fields.']); return;
        }
        if ($ut == 2 && !$this->assertTeacherAccess($uid,$sectionId,$curriculumId,$subjectId)) {
            echo json_encode(['status'=>false,'message'=>'You are not assigned to this section/subject.']); return;
        }

        // ensure SSG
        $ssgId = $this->getOrCreateSSG($studentId,$subjectId,$sectionId,$curriculumId);
        if (!$ssgId) { echo json_encode(['status'=>false,'message'=>'Unable to prepare grade row.']); return; }

        $ok = ['AO'=>1,'SO'=>1,'RO'=>1,'NO'=>1,''=>1,null=>1];
        $norm = function($v) use ($ok){ return isset($ok[$v]) ? ($v ?: null) : null; };

        // cores we accept
        $cores = ['maka_diyos','makatao','maka_kalikasan','maka_bansa'];

        // Track what we upsert for optional cleanup
        $seenKeys = [];

        foreach ($cores as $core) {
            $list = $values[$core] ?? [];
            if (!is_array($list)) $list = [];

            // Only keep behavior_index >=1, limit to a sane range
            foreach ($list as $row) {
                $beh = (int)($row['behavior_index'] ?? 1);
                if ($beh < 1 || $beh > 8) $beh = 1;

                $q1 = $norm($row['q1'] ?? null);
                $q2 = $norm($row['q2'] ?? null);
                $q3 = $norm($row['q3'] ?? null);
                $q4 = $norm($row['q4'] ?? null);

                $existing = $this->one(
                    "SELECT id FROM {$this->coreValuesRowsTable}
                      WHERE deleted=0 AND ssg_id=? AND core_name=? AND behavior_index=? LIMIT 1",
                    [$ssgId, $core, $beh]
                );

                if ($existing) {
                    $this->db->Update(
                        "UPDATE {$this->coreValuesRowsTable}
                            SET q1=?, q2=?, q3=?, q4=?, latest_edited_by=?
                          WHERE id=?",
                        [$q1,$q2,$q3,$q4,$uid,(int)$existing['id']]
                    );
                    $seenKeys[] = (int)$existing['id'];
                } else {
                    $this->db->Insert(
                        "INSERT INTO {$this->coreValuesRowsTable}
                           (ssg_id, core_name, behavior_index, q1, q2, q3, q4,
                            deleted, added_by, latest_edited_by)
                         VALUES (?,?,?,?,?,?,?,0,?,?)",
                        [$ssgId,$core,$beh,$q1,$q2,$q3,$q4,$uid,$uid]
                    );
                    $new = $this->one(
                        "SELECT id FROM {$this->coreValuesRowsTable}
                          WHERE deleted=0 AND ssg_id=? AND core_name=? AND behavior_index=? LIMIT 1",
                        [$ssgId,$core,$beh]
                    );
                    if ($new) $seenKeys[] = (int)$new['id'];
                }
            }
        }

        // Optional tidy: mark any other rows for this ssg_id as deleted (not in posted payload)
        // Comment out if you prefer to keep old rows.
        if (!empty($seenKeys)) {
            $in  = implode(',', array_fill(0, count($seenKeys), '?'));
            $par = array_merge([$ssgId], $seenKeys);
            $this->db->Update(
                "UPDATE {$this->coreValuesRowsTable}
                    SET deleted=1, latest_edited_by=?
                  WHERE ssg_id=? AND deleted=0 AND id NOT IN ($in)",
                array_merge([$uid], $par)
            );
        }

        /* ---- Keep legacy summary table in sync (behavior #1 per core) ---- */
        // Build the "first behavior" snapshot
        $first = [
            'maka_diyos'     => ['q1'=>null,'q2'=>null,'q3'=>null,'q4'=>null],
            'makatao'        => ['q1'=>null,'q2'=>null,'q3'=>null,'q4'=>null],
            'maka_kalikasan' => ['q1'=>null,'q2'=>null,'q3'=>null,'q4'=>null],
            'maka_bansa'     => ['q1'=>null,'q2'=>null,'q3'=>null,'q4'=>null],
        ];
        foreach ($cores as $core) {
            $list = $values[$core] ?? [];
            if (is_array($list) && count($list)) {
                // find behavior_index=1, else take first
                $pick = null;
                foreach ($list as $row) {
                    if ((int)($row['behavior_index'] ?? 0) === 1) { $pick = $row; break; }
                }
                if (!$pick) $pick = $list[0];

                $first[$core] = [
                    'q1'=>$norm($pick['q1'] ?? null),
                    'q2'=>$norm($pick['q2'] ?? null),
                    'q3'=>$norm($pick['q3'] ?? null),
                    'q4'=>$norm($pick['q4'] ?? null),
                ];
            }
        }

        $existsLegacy = $this->one(
            "SELECT id FROM {$this->coreValuesTable} WHERE deleted=0 AND ssg_id=? LIMIT 1",
            [$ssgId]
        );
        if ($existsLegacy) {
            $this->db->Update(
                "UPDATE {$this->coreValuesTable}
                    SET md_q1=?,md_q2=?,md_q3=?,md_q4=?,
                        mt_q1=?,mt_q2=?,mt_q3=?,mt_q4=?,
                        mk_q1=?,mk_q2=?,mk_q3=?,mk_q4=?,
                        mb_q1=?,mb_q2=?,mb_q3=?,mb_q4=?,
                        latest_edited_by=?
                  WHERE id=?",
                [
                    $first['maka_diyos']['q1'],$first['maka_diyos']['q2'],$first['maka_diyos']['q3'],$first['maka_diyos']['q4'],
                    $first['makatao']['q1'],$first['makatao']['q2'],$first['makatao']['q3'],$first['makatao']['q4'],
                    $first['maka_kalikasan']['q1'],$first['maka_kalikasan']['q2'],$first['maka_kalikasan']['q3'],$first['maka_kalikasan']['q4'],
                    $first['maka_bansa']['q1'],$first['maka_bansa']['q2'],$first['maka_bansa']['q3'],$first['maka_bansa']['q4'],
                    $uid, (int)$existsLegacy['id']
                ]
            );
        } else {
            $this->db->Insert(
                "INSERT INTO {$this->coreValuesTable}
                  (ssg_id,student_id,subject_id,curriculum_id,section_id,
                   md_q1,md_q2,md_q3,md_q4,
                   mt_q1,mt_q2,mt_q3,mt_q4,
                   mk_q1,mk_q2,mk_q3,mk_q4,
                   mb_q1,mb_q2,mb_q3,mb_q4,
                   status,deleted,added_by,latest_edited_by)
                 VALUES (?,?,?,?,?,
                         ?,?,?,?,
                         ?,?,?,?,
                         ?,?,?,?,
                         ?,?,?,?,
                         1,0,?,?)",
                [
                    $ssgId,$studentId,$subjectId,$curriculumId,$sectionId,
                    $first['maka_diyos']['q1'],$first['maka_diyos']['q2'],$first['maka_diyos']['q3'],$first['maka_diyos']['q4'],
                    $first['makatao']['q1'],$first['makatao']['q2'],$first['makatao']['q3'],$first['makatao']['q4'],
                    $first['maka_kalikasan']['q1'],$first['maka_kalikasan']['q2'],$first['maka_kalikasan']['q3'],$first['maka_kalikasan']['q4'],
                    $first['maka_bansa']['q1'],$first['maka_bansa']['q2'],$first['maka_bansa']['q3'],$first['maka_bansa']['q4'],
                    $uid,$uid
                ]
            );
        }

        echo json_encode(['status'=>true,'message'=>'Core values saved.']);
    } catch (Throwable $e) {
        error_log('saveCoreValues error: '.$e->getMessage());
        echo json_encode(['status'=>false,'message'=>'Failed to save core values.']);
    }
}



    public function js(){  return [$this->view.'/js/custom.js']; }
    public function css(){ return []; } // CSS is inline in the view
}
