<?php
/**
 * PrincipalDashboardController.php
 *
 * Adds:
 *  - Proper quarter filtering using q1..q4 columns
 *  - school_year filter
 *  - subject filter + subject preview
 *  - fetchSubjectsByGrade endpoint
 *  - fetchAbsences (NEW) for in-card refresh of Absent list
 */

if (!function_exists('ensureSessionStarted')) {
    function ensureSessionStarted() {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }
    }
}

if (!function_exists('getDefaultSlugByRole')) {
    function getDefaultSlugByRole() {
        ensureSessionStarted();
        $role = (int)($_SESSION['user_type'] ?? 0);
        if ($role === 1) return 'dashboard';
        if ($role === 2) return 'teacher-dashboard';
        if ($role === 3) return 'principal-dashboard';
        return 'dashboard';
    }
}

if (!function_exists('redirectToRoleHomeAndExit')) {
    function redirectToRoleHomeAndExit() {
        $dest = '/component/' . getDefaultSlugByRole() . '/index';
        header('Location: ' . $dest, true, 303);
        exit();
    }
}

class PrincipalDashboardController {

    protected $db;
    protected $view = "PrincipalDashboardController";

    public function __construct($db) {
        $this->db = $db;
    }

    /* ============================ PAGE ============================ */

    public function index() {
        ensureSessionStarted();

        // Allow only Admin(1) & Principal(3)
        $role = (int)($_SESSION['user_type'] ?? 0);
        if (!in_array($role, array(1, 3), true)) {
            redirectToRoleHomeAndExit();
        }

        $reportDate    = $this->resolveDateFromRequest($_GET['date'] ?? null);
        $principalName = $this->resolveDisplayNameFromUsers();

        // ---- Metrics ----
        $totalStudents = $this->scalarCount("SELECT COUNT(*) c FROM users WHERE deleted=0 AND user_type='5'");
        $totalTeachers = $this->scalarCount("SELECT COUNT(*) c FROM users WHERE deleted=0 AND user_type='2'");
        $totalActive   = $this->scalarCount("SELECT COUNT(*) c FROM users WHERE deleted=0 AND status=1");
        $totalInactive = $this->scalarCount("SELECT COUNT(*) c FROM users WHERE deleted=0 AND status=0");

        $absentToday  = $this->absentStudentsForDate($reportDate);
        $gradeLevels  = $this->gradeLevels();
        $sections     = $this->sections();

        // Optional: list of distinct school years (desc)
        $schoolYears = $this->schoolYears();

        $data = [
            'counts' => [
                'totalStudents' => $totalStudents,
                'totalTeachers' => $totalTeachers,
                'totalActive'   => $totalActive,
                'totalInactive' => $totalInactive,
            ],
            'absentToday'   => $absentToday,
            'reportDate'    => $reportDate,
            'principalName' => $principalName,
            'gradeLevels'   => $gradeLevels,
            'sections'      => $sections,
            'schoolYears'   => $schoolYears,
        ];

        return [
            "content" => loadView('components/'.$this->view.'/views/custom', $data)
        ];
    }

    /* ============================ AJAX ============================ */

    /**
     * GET /component/principal-dashboard/fetchTopStudents
     * Params: grade_id?, section_id?, subject_id?, quarter?, school_year?
     */
    public function fetchTopStudents() {
        ensureSessionStarted();

        $role = (int)($_SESSION['user_type'] ?? 0);
        if (!in_array($role, [1, 3], true)) {
            echo json_encode(['status'=>false,'message'=>'Not allowed']);
            return;
        }

        $gradeId    = isset($_GET['grade_id'])    && $_GET['grade_id']    !== '' ? (int)$_GET['grade_id']    : null;
        $sectionId  = isset($_GET['section_id'])  && $_GET['section_id']  !== '' ? (int)$_GET['section_id']  : null;
        $subjectId  = isset($_GET['subject_id'])  && $_GET['subject_id']  !== '' ? (int)$_GET['subject_id']  : null;
        $quarterRaw = isset($_GET['quarter'])     && $_GET['quarter']     !== '' ? (int)$_GET['quarter']     : null;
        $schoolYear = isset($_GET['school_year']) && trim($_GET['school_year']) !== '' ? trim($_GET['school_year']) : null;

        $quarter = (in_array($quarterRaw, [1,2,3,4], true)) ? $quarterRaw : null;

        try {
            $rows = $this->topStudents($gradeId, $sectionId, $subjectId, $quarter, $schoolYear, 10);
            echo json_encode(['status'=>true, 'rows'=>$rows], JSON_UNESCAPED_UNICODE|JSON_PARTIAL_OUTPUT_ON_ERROR);
        } catch (Throwable $e) {
            error_log('fetchTopStudents error: '.$e->getMessage());
            echo json_encode(['status'=>false, 'message'=>'Failed to load top students.']);
        }
    }

    /**
     * GET /component/principal-dashboard/fetchSectionsByGrade?grade_id=
     */
    public function fetchSectionsByGrade() {
        ensureSessionStarted();

        $gradeId = isset($_GET['grade_id']) && $_GET['grade_id'] !== '' ? (int)$_GET['grade_id'] : null;
        if ($gradeId === null) {
            echo json_encode(['status'=>true, 'rows'=>[]]);
            return;
        }

        try {
            $rows = $this->db->Select(
                "SELECT id, name
                   FROM section
                  WHERE deleted = 0 AND grade_id = ?
               ORDER BY name",
                [$gradeId]
            );
            echo json_encode(['status'=>true, 'rows'=>$rows]);
        } catch (Throwable $e) {
            echo json_encode(['status'=>false, 'message'=>$e->getMessage()]);
        }
    }

    /**
     * GET /component/principal-dashboard/fetchSubjectsByGrade?grade_id=...&school_year=...
     */
    public function fetchSubjectsByGrade() {
        ensureSessionStarted();

        $gradeId    = isset($_GET['grade_id'])    && $_GET['grade_id']    !== '' ? (int)$_GET['grade_id']    : null;
        $schoolYear = isset($_GET['school_year']) && trim($_GET['school_year']) !== '' ? trim($_GET['school_year']) : null;

        if ($gradeId === null) {
            echo json_encode(['status'=>true, 'rows'=>[]]);
            return;
        }

        try {
            // curriculum.grade_id references section.id (based on your other controller)
            $params = [$gradeId];
            $where  = ["s.deleted = 0", "gl.id = ?", "c.deleted = 0", "cc.deleted = 0"];
            $sql = "
                SELECT DISTINCT subj.id, subj.code, subj.name
                  FROM grade_level gl
                  JOIN section s          ON s.grade_id = gl.id AND s.deleted=0
                  JOIN curriculum c       ON c.grade_id = s.id AND c.deleted=0
                  JOIN curriculum_child cc ON cc.curriculum_id = c.id AND cc.deleted=0
                  JOIN subjects subj      ON subj.id = cc.subject_id
                 WHERE ".implode(' AND ', $where);

            if ($schoolYear !== null) {
                $sql   .= " AND c.school_year = ? ";
                $params[] = $schoolYear;
            }

            $sql .= " ORDER BY subj.name";
            $rows = $this->db->Select($sql, $params);

            echo json_encode(['status'=>true, 'rows'=>$rows], JSON_UNESCAPED_UNICODE|JSON_PARTIAL_OUTPUT_ON_ERROR);
        } catch (Throwable $e) {
            error_log('fetchSubjectsByGrade error: '.$e->getMessage());
            echo json_encode(['status'=>false, 'message'=>'Failed to load subjects.']);
        }
    }

    /**
     * GET /component/principal-dashboard/fetchAbsences?date=YYYY-MM-DD
     * Returns same fields as initial server render, so the card can refresh in place (no page reload).
     */
    public function fetchAbsences() {
        ensureSessionStarted();

        $role = (int)($_SESSION['user_type'] ?? 0);
        if (!in_array($role, [1,3], true)) {
            echo json_encode(['status'=>false,'message'=>'Not allowed']);
            return;
        }

        $date = $this->resolveDateFromRequest($_GET['date'] ?? null);
        try {
            $rows = $this->absentStudentsForDate($date);
            echo json_encode(['status'=>true, 'rows'=>$rows, 'date'=>$date], JSON_UNESCAPED_UNICODE|JSON_PARTIAL_OUTPUT_ON_ERROR);
        } catch (Throwable $e) {
            error_log('fetchAbsences error: '.$e->getMessage());
            echo json_encode(['status'=>false,'message'=>'Failed to load absences.']);
        }
    }

    public function js()  { return []; }
    public function css() { return []; }

    /* ========================== HELPERS ========================== */

    private function scalarCount($sql, $params = []) {
        $res = $this->db->Select($sql, $params);
        return (int)($res[0]['c'] ?? 0);
    }

    private function resolveDateFromRequest($raw) {
        $raw = is_string($raw) ? trim($raw) : '';
        if ($raw !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $raw)) return $raw;
        return date('Y-m-d');
    }

    private function resolveDisplayNameFromUsers() {
        $uid = (int)($_SESSION['user_id'] ?? 0);
        if ($uid > 0) {
            $row = $this->db->Select(
                "SELECT account_first_name, account_middle_name, account_last_name
                   FROM users
                  WHERE deleted = 0 AND user_id = ? LIMIT 1",
                [$uid]
            );
            if (!empty($row)) {
                $first  = trim($row[0]['account_first_name'] ?? '');
                $middle = trim($row[0]['account_middle_name'] ?? '');
                $last   = trim($row[0]['account_last_name'] ?? '');
                if ($first !== '' || $last !== '') {
                    $mi = $middle !== '' ? mb_substr($middle, 0, 1) . '.' : '';
                    $name = trim("$first $mi $last");
                    if ($name !== '') return $name;
                }
            }
        }
        if (!empty($_SESSION['account_display_name'])) return $_SESSION['account_display_name'];
        if (!empty($_SESSION['username'])) return $_SESSION['username'];
        return 'Principal';
    }

    private function gradeLevels() {
        return $this->db->Select("SELECT id, name FROM grade_level WHERE deleted=0 ORDER BY name", []);
    }

    private function sections() {
        return $this->db->Select("SELECT id, name, grade_id FROM section WHERE deleted=0 ORDER BY name", []);
    }

    private function schoolYears() {
        $rows = $this->db->Select(
            "SELECT DISTINCT c.school_year
               FROM curriculum c
              WHERE c.deleted=0 AND c.school_year IS NOT NULL AND c.school_year <> ''
           ORDER BY c.school_year DESC",
            []
        );
        return array_values(array_filter(array_map(function($r){ return $r['school_year'] ?? null; }, $rows)));
    }

    /**
     * Build Top Students list with optional filters.
     */
    private function topStudents($gradeId = null, $sectionId = null, $subjectId = null, $quarter = null, $schoolYear = null, $limit = 10) {
        $where = [
            "rs.deleted = 0",
            "rs.status  = 1",
            "u.deleted  = 0",
            "s.deleted  = 0",
            "ssg.deleted = 0",
            "ssg.student_id = rs.student_id",
            "ssg.section_id = rs.section_id",
            "ssg.curriculum_id = rs.curriculum_id"
        ];
        $params = [];

        if ($gradeId !== null) { $where[] = "s.grade_id = ?"; $params[] = $gradeId; }
        if ($sectionId !== null) { $where[] = "s.id = ?"; $params[] = $sectionId; }
        if ($subjectId !== null) { $where[] = "ssg.subject_id = ?"; $params[] = $subjectId; }
        if ($schoolYear !== null && $schoolYear !== '') { $where[] = "ssg.school_year = ?"; $params[] = $schoolYear; }

        if ($quarter !== null) {
            $qcol = "ssg.q".$quarter;
            $avgExpr = "ROUND(AVG($qcol), 2)";
            $where[] = "$qcol IS NOT NULL";
        } else {
            $avgExpr = "ROUND(AVG(ssg.final_average), 2)";
            $where[] = "ssg.final_average IS NOT NULL";
        }

        $subjectSelect = ($subjectId !== null) ? ", subj.name AS subject_name" : ", NULL AS subject_name";
        $subjectJoin   = ($subjectId !== null) ? "JOIN subjects subj ON subj.id = ssg.subject_id" : "";

        $sql = "
            SELECT
                u.user_id AS student_id,
                CONCAT(
                    u.account_last_name, ', ', u.account_first_name,
                    IF(u.account_middle_name IS NULL OR u.account_middle_name='','',
                       CONCAT(' ', LEFT(u.account_middle_name,1),'.'))
                ) AS full_name,
                u.LRN,
                gl.name AS grade_name,
                s.name  AS section_name,
                {$avgExpr} AS average
                {$subjectSelect}
            FROM registrar_student rs
            JOIN users u        ON u.user_id = rs.student_id
            JOIN section s      ON s.id = rs.section_id
            JOIN grade_level gl ON gl.id = s.grade_id
            JOIN student_subject_grades ssg
                 ON ssg.student_id = rs.student_id
                AND ssg.section_id = rs.section_id
                AND ssg.curriculum_id = rs.curriculum_id
            {$subjectJoin}
            WHERE ".implode(' AND ', $where)."
            GROUP BY u.user_id, gl.name, s.name, u.account_last_name, u.account_first_name, u.account_middle_name, u.LRN
            ORDER BY average DESC, u.account_last_name, u.account_first_name
            LIMIT ".(int)$limit;

        $rows = $this->db->Select($sql, $params);

        return array_map(function($r){
            return [
                'student_id'   => (int)$r['student_id'],
                'full_name'    => $r['full_name'],
                'LRN'          => $r['LRN'],
                'grade_name'   => $r['grade_name'],
                'section_name' => $r['section_name'],
                'average'      => ($r['average'] === null || $r['average'] === '') ? null : (float)$r['average'],
                'subject_name' => $r['subject_name'] ?? null,
            ];
        }, $rows);
    }

    private function absentStudentsForDate($date) {
        $sql = "
            SELECT
                u.user_id AS student_id,
                CONCAT(u.account_last_name, ', ', u.account_first_name,
                       IF(u.account_middle_name IS NULL OR u.account_middle_name='', '', CONCAT(' ', LEFT(u.account_middle_name,1),'.'))
                ) AS full_name,
                u.LRN,
                u.gender,
                s.id   AS section_id,
                s.name AS section_name,
                gl.name AS grade_name,
                sa.am_status,
                sa.pm_status,
                COALESCE(sa.remarks,'') AS remarks,
                sa.attendance_date
            FROM student_attendance sa
            JOIN users u
              ON u.user_id = sa.student_id AND u.deleted = 0
            JOIN registrar_student rs
              ON rs.deleted = 0
             AND rs.status  = 1
             AND rs.student_id = sa.student_id
             AND rs.section_id = sa.section_id
             AND rs.curriculum_id = sa.curriculum_id
            JOIN section s
              ON s.deleted = 0 AND s.id = rs.section_id
            JOIN grade_level gl
              ON gl.id = s.grade_id
            WHERE sa.attendance_date = ?
              AND sa.am_status = 'A'
              AND sa.pm_status = 'A'
            ORDER BY gl.name, s.name, u.account_last_name, u.account_first_name
        ";
        return $this->db->Select($sql, [$date]);
    }
}