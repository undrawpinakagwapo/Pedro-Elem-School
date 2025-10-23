<?php
class MyGradesController
{
    protected $db;
    protected $view = 'MyGradesController';
    protected $subjectTable = 'subjects'; // change if your table differs

    public function __construct($db) {
        // Optional strict auth
        // if (empty($_SESSION['user_id']) || empty($_SESSION['verify']) || (int)($_SESSION['status'] ?? 0) !== 1) {
        //     header('Location: /auth'); exit;
        // }
        $this->db = $db;
    }

    /* ================== tiny utils ================== */
    private function one($sql,$p=[]){ $r=$this->db->Select($sql,$p); return $r? $r[0]:null; }
    private function many($sql,$p=[]){ return $this->db->Select($sql,$p); }
    private function uid(){   return $_SESSION['user_id']   ?? null; }
    private function utype(){ return $_SESSION['user_type'] ?? null; } // 1=admin,2=teacher,3=student (we also detect student via enrollment)

    /* ================== access helpers ================== */
    private function isAdviserOfSection($teacherId, $sectionId) {
        return (bool)$this->one(
            "SELECT 1 FROM section s WHERE s.deleted=0 AND s.id=? AND s.adviser_id=? LIMIT 1",
            [(int)$sectionId,(int)$teacherId]
        );
    }
    private function isAdviserOfCurriculum($teacherId, $curriculumId) {
        return (bool)$this->one(
            "SELECT 1 FROM curriculum c WHERE c.deleted=0 AND c.id=? AND c.adviser_id=? LIMIT 1",
            [(int)$curriculumId,(int)$teacherId]
        );
    }
    /** Treat account as "student" if they have any active enrollment, regardless of user_type value */
    private function isStudentAccount($userId){
        if (!$userId) return false;
        return (bool)$this->one(
            "SELECT 1 FROM registrar_student rs WHERE rs.deleted=0 AND rs.status=1 AND rs.student_id=? LIMIT 1",
            [(int)$userId]
        );
    }
    /**
     * Who can view?
     * - student: only self
     * - teacher: must be adviser of the section OR of the curriculum for that SY
     * - others (admin/principal): any student
     */
    private function canView($viewerId, $viewerType, $studentId, $sectionId, $curriculumId) {
        if ($this->isStudentAccount($viewerId)) $viewerType = 3; // normalize
        if ((int)$viewerType === 3) { // student
            return ((int)$viewerId === (int)$studentId);
        }
        if ((int)$viewerType === 2) { // teacher
            return $this->isAdviserOfSection($viewerId,$sectionId) || $this->isAdviserOfCurriculum($viewerId,$curriculumId);
        }
        return true; // admins/principals
    }

    /* ================== data helpers ================== */
    /** All active enrollments for a student (School Year dropdown) */
    private function enrollmentsForStudent($studentId){
        return $this->many(
            "SELECT
                c.id          AS curriculum_id,
                c.school_year AS school_year,
                c.grade_id    AS section_id,
                s.name        AS section_name,
                gl.name       AS grade_name
             FROM registrar_student rs
             JOIN curriculum   c  ON c.id = rs.curriculum_id AND c.deleted=0
             JOIN section      s  ON s.id = c.grade_id       AND s.deleted=0
             JOIN grade_level  gl ON gl.id = s.grade_id
            WHERE rs.deleted=0 AND rs.status=1 AND rs.student_id=?
            ORDER BY c.school_year DESC, gl.name DESC, s.name ASC",
            [(int)$studentId]
        );
    }

    /** Student meta (validates that student is enrolled in section/curriculum pair) */
    private function studentMeta($studentId, $sectionId, $curriculumId){
        $stu = $this->one(
            "SELECT u.user_id AS id,
                    CONCAT(u.account_last_name, ', ', u.account_first_name, ' ', u.account_middle_name) AS full_name,
                    u.LRN
               FROM users u
               JOIN registrar_student rs ON rs.student_id = u.user_id
              WHERE u.user_id = ? AND rs.deleted=0 AND rs.status=1
                AND rs.section_id=? AND rs.curriculum_id=? LIMIT 1",
            [(int)$studentId,(int)$sectionId,(int)$curriculumId]
        );
        if (!$stu) return null;

        $sec = $this->one(
            "SELECT s.name AS section_name, gl.name AS grade_name
               FROM section s
               JOIN grade_level gl ON gl.id=s.grade_id
              WHERE s.id=? AND s.deleted=0 LIMIT 1",
            [(int)$sectionId]
        );
        $cur = $this->one(
            "SELECT school_year FROM curriculum WHERE id=? AND deleted=0 LIMIT 1",
            [(int)$curriculumId]
        );

        return [
            'id'           => (int)$stu['id'],
            'full_name'    => $stu['full_name'],
            'LRN'          => $stu['LRN'],
            'grade_name'   => $sec['grade_name'] ?? '',
            'section_name' => $sec['section_name'] ?? '',
            'school_year'  => $cur['school_year'] ?? '',
        ];
    }

    /** Subjects + grades for the selected curriculum (left-join so ungraded subjects appear) */
    private function subjectsWithGrades($studentId, $sectionId, $curriculumId){
        $st = $this->subjectTable;
        return $this->many(
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
           ORDER BY s.name ASC",
            [(int)$studentId,(int)$sectionId,(int)$curriculumId,(int)$curriculumId]
        );
    }

    /** Create a stable revision hash of the current subjects/grades set */
    private function computeRevision($subjects){
        $parts = [];
        foreach ($subjects as $g) {
            $parts[] = implode('|', [
                (int)($g['id'] ?? 0),
                strtoupper((string)($g['code'] ?? '')),
                strtoupper((string)($g['name'] ?? '')),
                ($g['q1']            === null ? '' : number_format((float)$g['q1'], 2, '.', '')),
                ($g['q2']            === null ? '' : number_format((float)$g['q2'], 2, '.', '')),
                ($g['q3']            === null ? '' : number_format((float)$g['q3'], 2, '.', '')),
                ($g['q4']            === null ? '' : number_format((float)$g['q4'], 2, '.', '')),
                ($g['final_average'] === null ? '' : number_format((float)$g['final_average'], 2, '.', '')),
            ]);
        }
        return substr(sha1(json_encode($parts)), 0, 12);
    }

    /** Build initial page context */
    private function buildContext($req){
        $viewerId   = $this->uid();
        $viewerType = (int)($this->utype() ?? 0);
        if ($this->isStudentAccount($viewerId)) $viewerType = 3; // normalize

        // Whose grades?
        if ($viewerType === 3) { // student
            $studentId = (int)$viewerId;
        } else {
            $studentId = (int)($req['student_id'] ?? 0);
        }

        $enrollments  = $studentId ? $this->enrollmentsForStudent($studentId) : [];
        $curriculumId = $enrollments ? (int)($req['curriculum_id'] ?? $enrollments[0]['curriculum_id']) : null;

        // Resolve section for the curriculum
        $sectionId = null;
        if ($enrollments && $curriculumId) {
            foreach ($enrollments as $e) {
                if ((int)$e['curriculum_id'] === (int)$curriculumId) {
                    $sectionId = (int)$e['section_id'];
                    break;
                }
            }
        }

        $student = null; $subjects = [];
        if ($studentId && $sectionId && $curriculumId && $this->canView($viewerId,$viewerType,$studentId,$sectionId,$curriculumId)) {
            $student  = $this->studentMeta($studentId,$sectionId,$curriculumId);
            $subjects = $this->subjectsWithGrades($studentId,$sectionId,$curriculumId);
        }

        $rev = $this->computeRevision($subjects);

        return [
            'student_id'    => $studentId ?: null,
            'student'       => $student,
            'enrollments'   => $enrollments,
            'curriculum_id' => $curriculumId,
            'subjects'      => array_map(function($g){
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
            }, $subjects),
            'rev'           => $rev,
        ];
    }

    /* ================== endpoints ================== */
    public function index() {
        $data = $this->buildContext($_GET ?? []);
        return [
            'header'  => 'My Grades',
            'content' => loadView('components/'.$this->view.'/views/custom', $data),
        ];
    }

    /** XHR: fetch grades for (student_id, curriculum_id), with rev optimization */
    public function fetch() {
        try {
            $req = getRequestAll();

            $viewerId   = $this->uid();
            $viewerType = (int)($this->utype() ?? 0);
            if ($this->isStudentAccount($viewerId)) $viewerType = 3; // normalize

            // Student can omit student_id. Others must provide it.
            if ($viewerType === 3) {
                $studentId = (int)$viewerId;
            } else {
                $studentId = (int)($req['student_id'] ?? 0);
            }
            $curriculumId = (int)($req['curriculum_id'] ?? 0);
            $clientRev    = (string)($req['rev'] ?? '');

            if (!$studentId) {
                echo json_encode(['status'=>false,'message'=>'Missing student_id.']);
                return;
            }

            $enrollments = $this->enrollmentsForStudent($studentId);
            if (!$enrollments) {
                echo json_encode(['status'=>false,'message'=>'No active enrollments found for student.']);
                return;
            }

            if (!$curriculumId) { $curriculumId = (int)$enrollments[0]['curriculum_id']; }

            // find section_id for this curriculum
            $sectionId = null;
            foreach ($enrollments as $e) {
                if ((int)$e['curriculum_id'] === (int)$curriculumId) { $sectionId = (int)$e['section_id']; break; }
            }
            if (!$sectionId) {
                echo json_encode(['status'=>false,'message'=>'Invalid curriculum selection for this student.']);
                return;
            }

            // Access gate
            if (!$this->canView($viewerId,$viewerType,$studentId,$sectionId,$curriculumId)) {
                echo json_encode(['status'=>false,'message'=>'You are not allowed to view these grades.']);
                return;
            }

            $student  = $this->studentMeta($studentId,$sectionId,$curriculumId);
            if (!$student) {
                echo json_encode(['status'=>false,'message'=>'Student not enrolled in selected school year.']);
                return;
            }

            $subjects = $this->subjectsWithGrades($studentId,$sectionId,$curriculumId);
            $rev      = $this->computeRevision($subjects);

            // 304-like optimization
            if ($clientRev !== '' && $clientRev === $rev) {
                echo json_encode([
                    'status'        => true,
                    'not_modified'  => true,
                    'rev'           => $rev,
                    'curriculum_id' => $curriculumId,
                ]);
                return;
            }

            echo json_encode([
                'status'        => true,
                'student_id'    => $studentId,
                'student'       => $student,
                'enrollments'   => $enrollments,
                'curriculum_id' => $curriculumId,
                'subjects'      => array_map(function($g){
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
                }, $subjects),
                'rev'           => $rev,
            ]);
        } catch (Throwable $e) {
            error_log('MyGrades fetch error: '.$e->getMessage());
            echo json_encode(['status'=>false,'message'=>'Fetch failed.']);
        }
    }

    /* static assets */
    public function js(){  return [$this->view.'/js/custom.js']; }
    public function css(){ return []; } // style inline in the view
}
