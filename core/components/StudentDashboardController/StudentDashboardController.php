<?php

class StudentDashboardController
{
    protected $db;
    protected $view;

    public function __construct($db)
    {
        $this->db = $db;
        $this->view = "StudentDashboardController";
    }

    /** Simple guard to allow only logged-in Students (user_type = 5) */
    private function requireStudent(): void
    {
        ensureSessionStarted();
        $role = (int)($_SESSION['user_type'] ?? 0);
        if ($role !== 5) {
            http_response_code(403);
            echo 'Forbidden';
            exit();
        }
    }

    /** Route: /component/student-dashboard/index */
    public function index()
    {
        $this->requireStudent();

        ensureSessionStarted();
        $userId   = (int)($_SESSION['user_id'] ?? 0);

        // Fetch a few useful bits to show on the dashboard (customize to your schema)
        $user = $this->db->Select(
            "SELECT 
                account_first_name, account_last_name, account_middle_name, 
                LRN, gender, dateof_birth, email, username
             FROM users
             WHERE user_id = ? AND deleted = 0
             LIMIT 1",
            [$userId]
        );

        $data = [
            'student' => $user[0] ?? null,
        ];

        // Render dashboard view
        return ["content" => loadView('components/'.$this->view.'/views/index', $data)];
    }

    /** If your asset loader calls these to include per-page JS/CSS */
    public function js()
    {
        return [
            $this->view.'/js/custom.js',
        ];
    }

    public function css()
    {
        return [];
    }
}
