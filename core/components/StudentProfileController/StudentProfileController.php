<?php

class StudentProfileController
{
    protected $db;
    protected $view;

    public function __construct($db) {
        $this->db  = $db;
        $this->view = "StudentProfileController";
    }

    /**
     * GET /component/student-profile/index
     * - Student (user_type = 5): sees own profile (from session).
     * - Admin/Principal (1/3): can view any student via ?id=...
     */
    public function index() {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $currentUserId   = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
        $currentUserType = isset($_SESSION['user_type']) ? (int)$_SESSION['user_type'] : 0; // 1=Admin,3=Principal,5=Student

        $requestedId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($currentUserType === 5) {
            // Student may only see own profile
            $studentIdToShow = $currentUserId;
            if ($studentIdToShow <= 0) {
                return $this->forbidden("No session user found.");
            }
        } elseif ($currentUserType === 1 || $currentUserType === 3) {
            // Admin / Principal must pass a student id
            if ($requestedId <= 0) {
                return $this->forbidden("Student id is required for admins/principals.");
            }
            $studentIdToShow = $requestedId;
        } else {
            return $this->forbidden("Access restricted.");
        }

        // Fetch raw row
        $row = $this->getStudentById($studentIdToShow);
        if (!$row) {
            return $this->forbidden("Student record not found or inactive.");
        }

        // ---- Normalize fields for the view ----
        $row = $this->normalizeStudentRow($row);

        $data = [
            'details' => $row,
        ];

        return [
            "content" => loadView("components/{$this->view}/views/custom", $data)
        ];
    }

    /** JS assets */
    public function js() {
        return [
            $this->view . '/js/custom.js',
        ];
    }

    /** CSS assets (optional) */
    public function css() {
        return [];
    }

    /* ======================= Helpers ======================= */

    /**
     * Fetch a student (users table) by id.
     * Must be user_type = 5 (Student) and not deleted.
     */
    private function getStudentById(int $userId) {
        $res = $this->db->Select(
            "SELECT *
               FROM users
              WHERE deleted = 0
                AND user_type = 5
                AND user_id = ?
              LIMIT 1",
            [ $userId ]
        );
        return !empty($res) ? $res[0] : null;
    }

    /**
     * Make sure the view always gets consistent keys:
     * - Ensure 'lrn' exists even if DB column is 'LRN' / 'Lrn'
     * - Ensure 'dateof_birth' exists (fallbacks: 'dob', 'birthdate')
     * - Compute 'age' from 'dateof_birth' if missing/empty
     * - Build 'full_name' if missing
     */
    private function normalizeStudentRow(array $row): array {
        // LRN normalization (accept LRN/Lrn/lrn)
        if (!isset($row['lrn'])) {
            if (isset($row['LRN']))       $row['lrn'] = $row['LRN'];
            elseif (isset($row['Lrn']))   $row['lrn'] = $row['Lrn'];
            else                          $row['lrn'] = null;
        }

        // date of birth normalization
        if (!isset($row['dateof_birth']) || $row['dateof_birth'] === '' || $row['dateof_birth'] === null) {
            if (isset($row['dob']) && $row['dob'] !== '') {
                $row['dateof_birth'] = $row['dob'];
            } elseif (isset($row['birthdate']) && $row['birthdate'] !== '') {
                $row['dateof_birth'] = $row['birthdate'];
            }
        }

        // Compute age if missing
        if (!isset($row['age']) || $row['age'] === '' || $row['age'] === null) {
            $row['age'] = $this->computeAgeSafe($row['dateof_birth'] ?? null);
        }

        // Full name fallback
        if (!isset($row['full_name']) || trim((string)$row['full_name']) === '' || $row['full_name'] === ',') {
            $last   = trim((string)($row['account_last_name']   ?? ''));
            $first  = trim((string)($row['account_first_name']  ?? ''));
            $middle = trim((string)($row['account_middle_name'] ?? ''));
            $parts = array_filter([$last, $first], fn($v) => $v !== '');
            $row['full_name'] = $parts ? implode(', ', $parts) . ($middle ? ' ' . $middle : '') : '';
        }

        return $row;
    }

    /**
     * Compute age in years from a Y-m-d (or other parseable) date string safely.
     * Returns '' if date cannot be parsed.
     */
    private function computeAgeSafe($dob) {
        if (!$dob) return '';
        try {
            // Accept common formats
            $dt = new \DateTime($dob);
            $now = new \DateTime('today');
            if ($dt && $now) {
                $diff = $dt->diff($now);
                return (string)$diff->y;
            }
        } catch (\Throwable $e) {
            // ignore and return empty
        }
        return '';
    }

    /**
     * Render a simple 403 card and return as controller content.
     */
    private function forbidden(string $reason = "") {
        ob_start();
        http_response_code(403);
        ?>
        <style>
          body { background:#f8fafc; }
          .forbidden-wrap{
            min-height: 50vh; display:flex; align-items:center; justify-content:center; padding:24px;
          }
          .forbidden-card{
            background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:28px;
            box-shadow:0 10px 24px rgba(2,6,23,.06); max-width:560px; width:100%; text-align:center;
            font-family: system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif;
          }
          .forbidden-card h1{ margin:0 0 8px; font-size:20px; color:#0f172a; }
          .forbidden-card p{ margin:0; color:#475569; }
        </style>
        <div class="forbidden-wrap">
          <div class="forbidden-card">
            <h1>403 – Access Restricted</h1>
            <p><?= htmlspecialchars($reason) ?></p>
          </div>
        </div>
        <?php
        $html = ob_get_clean();

        return ["content" => $html];
    }
}
