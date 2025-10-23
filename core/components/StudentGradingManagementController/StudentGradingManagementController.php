<?php

class StudentGradingManagementController
{
    protected $db;
    protected $view;
    // Change to 'subjects' if your DB table is plural
    protected $subjectTable = 'subjects';

    public function __construct($db) {
        $this->db  = $db;
        $this->view = 'StudentGradingManagementController';
    }

    /* ------------ tiny utils ------------ */
    private function one($sql,$p=[]){ $r=$this->db->Select($sql,$p); return $r? $r[0]:null; }
    private function many($sql,$p=[]){ return $this->db->Select($sql,$p); }
    private function uid(){   return $_SESSION['user_id']   ?? null; }
    private function utype(){ return $_SESSION['user_type'] ?? null; } // 1=admin,2=teacher,...

    /* ------------ access helpers ------------ */

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

    /**
     * Sections visible to a teacher:
     *  - adviser of the section
     *  - adviser of any curriculum in the section
     *  - assigned to any subject (curriculum_child) in the section
     */
    private function sectionsForTeacherStrict($teacherId){
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

    /**
     * Curricula visible to a teacher for a section:
     *  - if section adviser → all curricula of that section
     *  - else curricula where teacher is curriculum adviser OR assigned to ≥1 subject
     */
    private function curriculaForTeacherStrict($teacherId, $sectionId){
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

    /**
     * Subjects visible to a teacher for a curriculum:
     *  - if section adviser OR curriculum adviser → all subjects under that curriculum
     *  - else only subjects assigned to that teacher
     */
    private function subjectsForTeacherStrict($teacherId, $curriculumId){
        $cur = $this->one(
            "SELECT grade_id AS section_id FROM curriculum WHERE id=? AND deleted=0 LIMIT 1",
            [$curriculumId]
        );
        if (!$cur) return [];

        $sectionId = (int)$cur['section_id'];
        $st = $this->subjectTable;

        if ($this->isAdviserOfSection($teacherId,$sectionId)
            || $this->isAdviserOfCurriculum($teacherId,$curriculumId)) {
            return $this->many(
                "SELECT cc.subject_id AS id, s.code, s.name
                   FROM curriculum_child cc
                   JOIN {$st} s ON s.id = cc.subject_id
                  WHERE cc.deleted=0 AND cc.curriculum_id=?
               ORDER BY s.name",
               [$curriculumId]
            );
        }

        return $this->many(
            "SELECT cc.subject_id AS id, s.code, s.name
               FROM curriculum_child cc
               JOIN {$st} s ON s.id = cc.subject_id
              WHERE cc.deleted=0 AND cc.curriculum_id=? AND cc.adviser_id=?
           ORDER BY s.name",
           [$curriculumId,$teacherId]
        );
    }

    /**
     * Hard check the teacher owns this section/curriculum/subject triple.
     */
    private function assertTeacherAccessStrict($teacherId, $sectionId, $curriculumId, $subjectId){
        $cur = $this->one(
            "SELECT id, grade_id AS section_id FROM curriculum WHERE id=? AND deleted=0 LIMIT 1",
            [$curriculumId]
        );
        if (!$cur || (int)$cur['section_id'] !== (int)$sectionId) return false;

        if ($this->isAdviserOfSection($teacherId,$sectionId)
            || $this->isAdviserOfCurriculum($teacherId,$curriculumId)) {
            return true;
        }

        $own = $this->one(
            "SELECT 1 FROM curriculum_child
              WHERE deleted=0 AND curriculum_id=? AND subject_id=? AND adviser_id=? LIMIT 1",
            [$curriculumId,$subjectId,$teacherId]
        );
        return (bool)$own;
    }

    /**
     * Option B: Show ALL enrolled students even if they have no grade row yet.
     * Driving table: registrar_student (rs) → LEFT JOIN student_subject_grades (ssg).
     */
    private function gradeRowsStrict($filters, $userId, $userType){
        if (empty($filters['subject_id'])) return [];

        $where = ["rs.deleted=0", "rs.status=1"];
        $p = [];

        if (!empty($filters['section_id']))    { $where[]="rs.section_id=?";    $p[]=(int)$filters['section_id']; }
        if (!empty($filters['curriculum_id'])) { $where[]="rs.curriculum_id=?"; $p[]=(int)$filters['curriculum_id']; }

        if (!empty($filters['q'])) {
            $where[]="(u.account_last_name LIKE ? OR u.account_first_name LIKE ? OR u.LRN LIKE ?)";
            $q='%'.$filters['q'].'%'; array_push($p,$q,$q,$q);
        }

        if ($userType == 2) {
            $where[] = "(sec.adviser_id = ?
                        OR cur.adviser_id = ?
                        OR EXISTS (
                             SELECT 1 FROM curriculum_child cc
                              WHERE cc.curriculum_id = cur.id
                                AND cc.subject_id    = subj.id
                                AND cc.deleted       = 0
                                AND cc.adviser_id    = ?
                        ))";
            array_push($p,$userId,$userId,$userId);
        }

        $st = $this->subjectTable;

        $sql = "SELECT
                    ssg.id,
                    u.user_id,
                    CONCAT(u.account_last_name, ', ', u.account_first_name, ' ', u.account_middle_name) AS full_name,
                    u.LRN,
                    subj.id   AS subject_id,
                    subj.code AS subject_code,
                    subj.name AS subject_name,
                    ssg.q1, ssg.q2, ssg.q3, ssg.q4, ssg.final_average,
                    cur.id AS curriculum_id, cur.school_year,
                    sec.id AS section_id, sec.name AS section_name,
                    gl.name AS grade_name
                FROM registrar_student rs
                JOIN users u         ON u.user_id     = rs.student_id
                JOIN curriculum cur  ON cur.id        = rs.curriculum_id
                JOIN section sec     ON sec.id        = rs.section_id
                LEFT JOIN grade_level gl ON gl.id     = sec.grade_id
                /* Subject is chosen by the filter (required) */
                JOIN {$st} subj      ON subj.id       = ?
                /* Bring in grades if they exist; otherwise fields are NULL */
                LEFT JOIN student_subject_grades ssg
                       ON  ssg.student_id    = rs.student_id
                       AND ssg.section_id    = rs.section_id
                       AND ssg.curriculum_id = rs.curriculum_id
                       AND ssg.subject_id    = subj.id
                       AND ssg.deleted       = 0
                WHERE ".implode(' AND ', $where)."
                ORDER BY gl.name, sec.name, subj.name, u.account_last_name, u.account_first_name";

        array_unshift($p, (int)$filters['subject_id']); // first ? is subj.id
        return $this->many($sql,$p);
    }

    /* ------------ page build (strict) ------------ */

    private function buildContextStrict($req){
        $uid = $this->uid(); $ut = $this->utype();
        $q   = isset($req['q']) ? trim((string)$req['q']) : '';

        // Admin/Principal
        if ($ut != 2) {
            $sections = $this->many(
                "SELECT s.id, s.name, gl.name AS grade_name
                   FROM section s
                   JOIN grade_level gl ON gl.id = s.grade_id
                  WHERE s.deleted=0
               ORDER BY gl.name, s.name"
            );
            $section_id = $sections ? (int)($req['section_id'] ?? $sections[0]['id']) : null;

            $curricula = $section_id ? $this->many(
                "SELECT c.id, c.school_year
                   FROM curriculum c
                  WHERE c.deleted=0 AND c.grade_id=?
               ORDER BY c.school_year DESC", [$section_id]
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

            $rows = ($section_id && $curriculum_id && $subject_id)
                ? $this->gradeRowsStrict(compact('section_id','curriculum_id','subject_id','q'), $uid, $ut)
                : [];

            return compact('sections','section_id','curricula','curriculum_id','subjects','subject_id','rows');
        }

        // Teacher
        $sections = $this->sectionsForTeacherStrict($uid);
        $section_id = $sections ? (int)($req['section_id'] ?? $sections[0]['id']) : null;

        $curricula = $section_id ? $this->curriculaForTeacherStrict($uid, $section_id) : [];
        $curriculum_id = $curricula ? (int)($req['curriculum_id'] ?? $curricula[0]['id']) : null;

        $subjects = $curriculum_id ? $this->subjectsForTeacherStrict($uid, $curriculum_id) : [];
        $subject_id = $subjects ? (int)($req['subject_id'] ?? $subjects[0]['id']) : null;

        $rows = [];
        if ($section_id && $curriculum_id && $subject_id &&
            $this->assertTeacherAccessStrict($uid,$section_id,$curriculum_id,$subject_id)) {
            $rows = $this->gradeRowsStrict(compact('section_id','curriculum_id','subject_id','q'), $uid, 2);
        }
        return compact('sections','section_id','curricula','curriculum_id','subjects','subject_id','rows');
    }

    /* ------------ endpoints ------------ */

    public function index() {
        $data = $this->buildContextStrict($_GET ?? []);
        return ["content" => loadView('components/'.$this->view.'/views/custom', $data)];
    }

    public function fetch() {
        $req = getRequestAll();
        $uid = $this->uid(); $ut = $this->utype();

        $ctx = $this->buildContextStrict($req);

        if ($ut == 2) {
            $sid = isset($req['section_id'])    ? (int)$req['section_id']    : ($ctx['section_id'] ?? null);
            $cid = isset($req['curriculum_id']) ? (int)$req['curriculum_id'] : ($ctx['curriculum_id'] ?? null);
            $sub = isset($req['subject_id'])    ? (int)$req['subject_id']    : ($ctx['subject_id'] ?? null);

            if ($sid && $cid && $sub && !$this->assertTeacherAccessStrict($uid,$sid,$cid,$sub)) {
                echo json_encode(['status'=>false,'message'=>'You are not assigned to this section/subject.']);
                return;
            }
        }

        echo json_encode(['status'=>true] + $ctx);
    }

    public function js(){  return [$this->view.'/js/custom.js']; }
    public function css(){ return []; }
}
