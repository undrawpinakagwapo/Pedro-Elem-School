<?php 

require_once __DIR__ . '../../ComponentHelper/ComponentHelper.php';
// Make sure helper.php is autoloaded globally (or require it here)

class UserController {

    protected $db;
    protected $componentHelper;

    public function __construct($db) {
        $this->db = $db;
        $this->componentHelper = new ComponentHelper($db);
    }

    public function index() {
        $data =  $this->db->Select("SELECT * FROM `system_info` limit 1");

        $data = [
            'title' => $data[0]["title"] ?? 'Login',
            'content' => 'This is the homepage.',
        ];

        echo loadView('components/UserController/pages/login_form', $data);
    }

    public function forgot_password(){
        ensureSessionStarted();
        $data = getRequestAll();
        extract($data);

        if (isset($email)) {
            // NOTE: LIKE with interpolation is okay for quick use, but parameterize fully if possible
            $rows = $this->db->Select("select * from users where email = ? OR contact_no LIKE '%".$email."%'", array($email));
            if (count($rows) > 0) {
                $token =  $this->componentHelper->generateRandomString(6);
                $this->db->Update("update users SET code = ? WHERE user_id = ? ", array($token, $rows[0]["user_id"]));

                $BodySms = ' Code : ' . $token;
                $this->componentHelper->sentSMS($rows[0]["contact_no"], $BodySms);

                $Body = '<a href="'.$_ENV["URL_HOST"].'otp?email='.$rows[0]["email"].'"><button style="padding:12px; background-color:blue;color:white;border:none;">CLICK TO CHANGE PASSWORD</button></a><br><h2>CODE: '.$token.'</h2>';
                $this->componentHelper->sentToEmail($rows[0]["email"], "Forgot Password", $Body);

                header('Location: /otp?email='.$rows[0]["email"]);
                exit();
            }
        }

        $sys =  $this->db->Select("SELECT * FROM `system_info` limit 1");

        $viewData = [
            'title' => $sys[0]["title"] ?? 'Forgot Password',
            'content' => 'This is the homepage.',
        ];

        echo loadView('components/UserController/pages/forgot_password', $viewData);
    }

    public function otp(){
        ensureSessionStarted();
        $data = getRequestAll();
        extract($data);
        
        if (isset($pin)) {
            $code = implode('', $pin);
            $rows = $this->db->Select("select * from users where email = ? and code = ?", array($email, $code));
           
            if (count($rows) > 0) {
                if (isset($type) && $type == "verify") {
                    header('Location: /');
                } else {
                    header('Location: /changepassword?email='.$email);
                }
                exit();
            }
        }

        $sys =  $this->db->Select("SELECT * FROM `system_info` limit 1");

        $viewData = [
            'title' => $sys[0]["title"] ?? 'OTP',
            'email' => $email ?? '',
            'content' => 'This is the homepage.',
        ];

        echo loadView('components/UserController/pages/otp', $viewData);
    }

    public function changepassword(){
    ensureSessionStarted();
    $data = getRequestAll();
    extract($data);
    
    if (isset($password)) {
        $rows = $this->db->Select("SELECT * FROM users WHERE email = ?", [$email]);
        if (count($rows) > 0) {
            
            // MODIFIED: Hash the new password before updating
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            $this->db->Update("UPDATE users SET password = ? WHERE user_id = ?", [$hashedPassword, $rows[0]["user_id"]]);
            
            header('Location: /auth?type=success&message=Password changed successfully. Please log in.');
            exit();
        }
    }

    $sys =  $this->db->Select("SELECT * FROM `system_info` limit 1");

    $viewData = [
        'title' => $sys[0]["title"] ?? 'Change Password',
        'email' => $email ?? '',
        'content' => 'This is the homepage.',
    ];

    echo loadView('components/UserController/pages/changepassword', $viewData);
}

    public function userLogin() {
    ensureSessionStarted();
    $data = getRequestAll();
    extract($data);

    // --- Start of changes ---

    // Step 1: Find the user by email or username first.
    // Notice we DO NOT check the password in the SQL query anymore.
    $rows = $this->db->Select(
        "SELECT * FROM users 
         WHERE status = 1 AND deleted = 0 
         AND (email = ? OR username = ?)",
        [$email, $email]
    );

    // Step 2: Verify the password if a user was found.
    if (count($rows) > 0) {
        $user = $rows[0];
        $hashedPasswordFromDB = $user["password"];

        if (password_verify($password, $hashedPasswordFromDB)) {
            // Password is correct! Proceed with login.
            $token = generateToken();
            $this->db->Update("UPDATE users SET token = ? WHERE user_id = ?", [$token, $user["user_id"]]);

            // Refresh user data after token update
            $updatedUser = $this->db->Select("SELECT * FROM users WHERE user_id = ?", [$user["user_id"]]);

            ensureSessionStarted();
            foreach ($updatedUser[0] as $key => $value) {
                $_SESSION[$key] = $value;
            }
            $_SESSION["user_active"] = true;

            // logs
            $logs = [
                'user_id' => $_SESSION["user_id"] ?? null,
                'action'  => 'User Login Successful'
            ];
            $this->db->insertRequestBatchRquest($logs, 'logs');

            redirectToRoleHomeAndExit();
        }
    }
    
    // --- End of changes ---

    // If we reach here, it means either the user was not found or the password was wrong.
    session_destroy();
    header('Location: /auth?type=warning&message=Invalid Credentials!. Please try again.');
    exit();
}

    public function authRegister(){
    ensureSessionStarted();
    $data = getRequestAll();
    extract($data);

    $rows = $this->db->Select("SELECT * FROM users WHERE email = ? AND deleted = 0", [$email]);
    if (count($rows) > 0) {
        session_destroy();
        header('Location: /auth?type=warning&message=Email already exist!.Please user another email!');
        exit();
    }

    $token = generateToken();

    // MODIFIED: Hash the password before inserting
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $this->db->Insert(
        "INSERT INTO users (`email`,`username`,`password`, `token`, `user_type`) VALUES (?,?,?,?,?)",
        [
            $email,
            $username,
            $hashedPassword, // Use the hashed password here
            $token,
            $user_type,
        ]
    );

    session_destroy();
    header('Location: /auth?type=success&message=Successfully Registered!');
    exit();
}

    public function userLogout() {
        ensureSessionStarted();

        $logs = [
            'user_id' => $_SESSION["user_id"] ?? null,
            'action'  => 'User Logout'
        ];
        $this->db->insertRequestBatchRquest($logs, 'logs');

        session_destroy();

        // Everyone returns to /auth
        header('Location: /auth');
        exit();
    }

    public function test() {
       echo "123";
    }
}
