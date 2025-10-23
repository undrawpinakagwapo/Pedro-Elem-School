<?php 

class UserManagementController {

    protected $db;
    protected $view;
    protected $currentRole;

    public function __construct($db) {
        $this->db = $db;
        $this->view = "UserManagementController";

        // Ensure session and capture the current user's role
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        // Adjust the session key if your auth uses a different one
        $this->currentRole = isset($_SESSION['user_type']) ? (int)$_SESSION['user_type'] : 0;
    }

    /** ===== Helpers ===== */

    /** Roles gate: allow only Admin (1) and Principal (3) by default */
    private function ensureAllowed(array $allowedRoles = [1, 3]) {
        if (!in_array($this->currentRole, $allowedRoles, true)) {
            $this->deny();
        }
    }

    /** Minimal AJAX detection */
    private function isAjaxRequest(): bool {
        return (
            (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || (isset($_SERVER['HTTP_ACCEPT']) && stripos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
        );
    }

    /** Return 403 either as HTML page (normal) or JSON (AJAX) */
    private function deny(): void {
        if ($this->isAjaxRequest()) {
            http_response_code(403);
            echo json_encode([
                'status' => false,
                'error'  => 'forbidden',
                'message'=> 'You do not have permission to perform this action.'
            ]);
            exit();
        }

        http_response_code(403);
        ?>
        <style>
          body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif; background:#f8fafc; margin:0; }
          .forbidden-wrap { min-height: 100vh; display:flex; align-items:center; justify-content:center; }
          .forbidden-card {
            max-width: 560px; background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:28px; box-shadow:0 10px 24px rgba(2,6,23,.06);
            text-align:center;
          }
          .forbidden-card h1 { margin:0 0 6px; font-size:22px; color:#0f172a; }
          .forbidden-card p { margin:0; color:#475569; }
        </style>
        <div class="forbidden-wrap">
          <div class="forbidden-card">
            <h1>403 – Access Restricted</h1>
            <p>You don’t have permission to view this page.</p>
          </div>
        </div>
        <?php
        exit();
    }

    /** ===== Routes ===== */

    public function index() {
        // Page view must be limited to Admin + Principal
        $this->ensureAllowed([1, 3]);

        $data = [];

        // Only fetch Admin (1) + Principal (3)
        $data["list"] = $this->db->Select(
            "SELECT *
               FROM users
              WHERE deleted = 0
                AND user_id != 1
                AND user_type IN ('1','3')
           ORDER BY account_last_name, account_first_name",
            []
        );

        return ["content" => loadView('components/'.$this->view.'/views/custom', $data)];
    }

    public function js(){
        return [
            $this->view.'/js/custom.js',
        ];
    }

    public function css(){
        return [];
    }

    public function source() {
        // Modal source is opened via AJAX → protect it too
        $this->ensureAllowed([1, 3]);

        $data = getRequestAll();
        extract($data);

        $d["details"] = false;

        if (isset($action) && $action === "edit" && !empty($id) && $id !== 'undefined') {
            // Only allow fetching Admin(1)+Principal(3) records for editing
            $result = $this->db->Select(
                "SELECT * FROM users WHERE user_id = ? AND deleted = 0 AND user_type IN ('1','3')",
                [ $id ]
            );
            if (!empty($result)) {
                $d["details"] = $result[0];
            } else {
                http_response_code(404);
                echo json_encode([
                    'status'  => false,
                    'error'   => 'not_found',
                    'message' => 'Record not found or not allowed.'
                ]);
                exit();
            }
        }

        $res = [
            'header'=> (isset($action) && $action === "add") ? "Add User" : 'Edit User',
            "html"  => loadView('components/'.$this->view.'/views/modal_details', $d),
            'button'=> '<button class="btn btn-primary" type="submit">Submit form</button>',
            'action'=> 'afterSubmit'
        ];

        echo json_encode($res);
    }

    public function afterSubmit(){
    // Create/Edit must be limited as well
    $this->ensureAllowed([1, 3]);

    $post = getRequestAll();
    extract($post);

    $folder = 'src/images/products/uploads/';

    $roleMap = [
        '1' => 1, // Admin
        '2' => 2, // Teacher
        '3' => 3, // Principal
        '5' => 5, // Student
    ];

    // Hard-stop if trying to save anything other than Admin or Principal
    $desiredRole = isset($post['user_type']) ? (string)$post['user_type'] : '';
    if (!in_array($desiredRole, ['1','3'], true)) {
        header('Location: index?type=warning&message=This page can only create/update Admin or Principal accounts.');
        exit();
    }

    if (isset($user_id)) {
        // ===================== EDIT =====================

        // Ensure the target row itself is Admin/Principal
        $target = $this->db->Select(
            "SELECT user_id FROM users WHERE user_id = ? AND deleted = 0 AND user_type IN ('1','3') LIMIT 1",
            [ $user_id ]
        );
        if (count($target) === 0) {
            header('Location: index?type=warning&message=Record not found or not allowed.');
            exit();
        }

        $update = $post;

        // Handle password update. Only hash and update if a new password is provided.
        if (!empty($update['password'])) {
            // Hash the new password
            $update['password'] = password_hash($update['password'], PASSWORD_DEFAULT);
        } else {
            // If password field is empty, remove it from the update array to keep the old one.
            unset($update['password']);
        }

        // Enforce single active Principal when changing role to Principal
        if (isset($update['user_type']) && (string)$update['user_type'] === '3') {
            $exists = $this->db->Select(
                "SELECT user_id
                    FROM users
                    WHERE deleted = 0
                    AND user_type = '3'
                    AND user_id != ? LIMIT 1",
                [ $user_id ]
            );
            if (count($exists) > 0) {
                header('Location: index?type=warning&message=Another Principal already exists!');
                exit();
            }
        }

        // Keep role_id in sync with user_type (if you keep this column)
        if (isset($update['user_type'])) {
            $update['role_id'] = $roleMap[(string)$update['user_type']] ?? null;
        }

        // File handling (Image upload)
        unset($update['image']);
        $uploadedPaths = $this->db->handleMultipleFileUpload($image ?? [], $folder);
        $uploadedNew   = implode('|', $uploadedPaths);
        if (count($uploadedPaths) > 0) {
            $update["image"] = $uploadedNew;
        }

        unset($update['user_id']);
        $where = ['user_id' => $user_id];
        $this->db->updateField('users', $update, $where);

        header('Location: index?type=success&message=Successfully Updated!');
        exit();

    } else {
        // ===================== CREATE =====================

        // Ensure Principal account does not already exist
        if ($desiredRole === '3') {
            $principalExists = $this->db->Select(
                "SELECT user_id FROM users WHERE deleted = 0 AND user_type = '3' LIMIT 1",
                []
            );
            if (count($principalExists) > 0) {
                header('Location: index?type=warning&message=Principal account already exists!');
                exit();
            }
        }

        // Check for duplicate email among active users
        $dupe = $this->db->Select(
            "SELECT email FROM users WHERE email = ? AND deleted = 0",
            [ $email ]
        );
        if (count($dupe) > 0) {
            header('Location: index?type=warning&message=Email already exist. Please use another email!');
            exit();
        }

        // Handle password - hash if provided
        if (isset($post['password']) && !empty($post['password'])) {
             $post['password'] = password_hash($post['password'], PASSWORD_DEFAULT);
        } else {
            // Default password handling if none is provided
            header('Location: index?type=warning&message=Password is required for new users!');
            exit();
        }

        // Set a default token
        $token = generateToken();
        $post["token"] = $token;

        // Assign role_id based on the user_type
        $post['role_id'] = $roleMap[$desiredRole] ?? null;

        // File handling (Image upload)
        unset($post['image']);
        $uploadedPaths = $this->db->handleMultipleFileUpload($image ?? [], $folder);
        $uploadedNew   = implode('|', $uploadedPaths);
        if (count($uploadedPaths) > 0) {
            $post["image"] = $uploadedNew;
        }

        // Insert the new user data
        $this->db->insertRequestBatchRquest($post, 'users', $folder);

        header('Location: index?type=success&message=Successfully Registered!');
        exit();
    }
}


    public function delete(){
        // Delete must be limited as well
        $this->ensureAllowed([1, 3]);

        $data = getRequestAll();
        extract($data);

        // Only allow deletion of Admin/Principal from this page
        $target = $this->db->Select(
            "SELECT user_id FROM users WHERE user_id = ? AND deleted = 0 AND user_type IN ('1','3') LIMIT 1",
            [ $id ]
        );
        if (count($target) === 0) {
            echo json_encode(['status'=> false, 'msg'=> 'Not allowed for this record.']);
            return;
        }

        $this->db->Update("UPDATE users SET deleted = 1 WHERE user_id = ? ", [ $id ]);
        echo json_encode(['status'=> true, 'msg'=> 'Successfully deleted!']);
    }
}
