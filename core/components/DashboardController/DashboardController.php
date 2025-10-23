<?php

class DashboardController
{
    protected $db;
    protected $view = 'DashboardController';
    protected $subjectTable = 'subjects'; // change to 'subject' if your table is singular

    public function __construct($db) {
        $this->db = $db;
        $this->requireAdmin(); // Admin-only gate
    }

    /* ===== role/auth guard ===== */
    private function requireAdmin(){
        if (empty($_SESSION['user_id']) || (int)($_SESSION['status'] ?? 0) !== 1) {
            header('Location: /auth'); exit;
        }

        $role = (int)($_SESSION['user_type'] ?? 0);
        $ADMIN_ROLE   = 1;
        $TEACHER_ROLE = 2;

        if ($role !== $ADMIN_ROLE) {
            // Send teachers to their dashboard if you have it; otherwise pick a teacher page you do have.
            if ($role === $TEACHER_ROLE) {
                redirect('/component/teacher-dashboard/index');
            }
            http_response_code(403);
            echo 'Forbidden: Admins only.';
            exit;
        }
    }

    /* ----- tiny utils ----- */
    private function one($sql, $p = []) { $r = $this->db->Select($sql, $p); return $r ? $r[0] : null; }
    private function many($sql, $p = []) { return $this->db->Select($sql, $p); }

    public function index()
    {
        // KPIs only
        $students = $this->one("SELECT COUNT(DISTINCT student_id) AS c FROM registrar_student WHERE COALESCE(deleted,0)=0 AND COALESCE(status,1)=1");
        $teachers = $this->one("SELECT COUNT(*) AS c FROM users WHERE COALESCE(user_type,0)=2");
        $sections = $this->one("SELECT COUNT(*) AS c FROM section WHERE COALESCE(deleted,0)=0");
        $subjects = $this->one("SELECT COUNT(*) AS c FROM {$this->subjectTable}");
        $curric   = $this->one("SELECT COUNT(*) AS c FROM curriculum WHERE COALESCE(deleted,0)=0");

        $data = [
            'kpi' => [
                'students'  => (int)($students['c'] ?? 0),
                'teachers'  => (int)($teachers['c'] ?? 0),
                'sections'  => (int)($sections['c'] ?? 0),
                'subjects'  => (int)($subjects['c'] ?? 0),
                'curricula' => (int)($curric['c'] ?? 0),
            ],
        ];
        return ['header' => 'Dashboard', 'content' => loadView('components/'.$this->view.'/views/custom', $data)];
    }

    public function js(){  return [$this->view.'/js/custom.js']; }
    public function css(){ return []; }
}
