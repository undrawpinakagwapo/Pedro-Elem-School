<?php 


class FacultyManagementController {

    protected $db;

    public function __construct($db) {
        $this->db = $db;
        $this->view = "FacultyManagementController";
    }


    public function index() {

        $data = [];

        $data["list"] = $this->db->Select("select * from users where deleted = 0 and user_id != 1 and user_type = 2 ", array() );
        
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
        $data = getRequestAll();

        extract($data);

        $d["details"] = false;



        if($action == "edit" && ($id != '' || $id != 'undefined') ) {
            $result = $this->db->Select("select * from users where user_id = ?", array($id) )[0];
            $d["details"] = $result;
        }

        $res = [
            'header'=> (isset($action) && $action == "add") ? "Add User" : 'Edit User',
            "html" => loadView('components/'.$this->view.'/views/modal_details', $d),
            'button' => '<button class="btn btn-primary" type="submit">Submit form</button>',
            'action' => 'afterSubmit'
        ];

        echo json_encode($res);
    }

    // In C:\...\FacultyManagementController\FacultyManagementController.php

public function afterSubmit(){
    $post = getRequestAll();
    extract($post);
    
    $folder = 'src/images/products/uploads/';

    if(isset($user_id)) {
    // ===================== EDIT =====================
    $table = "users";
    $update = $post;

    // NEW: Add a duplicate email check that IGNORES the current user
    $dupe = $this->db->Select(
        "SELECT user_id FROM users WHERE email = ? AND deleted = 0 AND user_id != ?",
        [$update['email'], $user_id]
    );

    if (count($dupe) > 0) {
        // This email belongs to someone else, so it's a real duplicate.
        header('Location: index?type=warning&message=Email already exists! Please use another email!');
        exit();
    }
    // --- End of new check ---


    // Secure password handling for edits
    if (!empty($update['password'])) {
        $update['password'] = password_hash($update['password'], PASSWORD_DEFAULT);
    } else {
        unset($update['password']);
    }

    // Your file upload logic
    unset($update['image']);
    $uploadedPaths = $this->db->handleMultipleFileUpload($image ?? [], $folder);
    $uploadedNew = implode('|', $uploadedPaths);
    if(count($uploadedPaths) > 0) {
        $update["image"] = $uploadedNew;
    }

    unset($update['user_id']);
    
    $where = ['user_id' => $user_id];
    $this->db->updateField($table, $update, $where);

    header('Location: index?type=success&message=Successfully Updated!');
    exit();

    } else {
        // ===================== CREATE =====================
        $data = $this->db->Select("select email from users where email = ? and deleted = 0", array($email) );
        if(count($data) > 0){
            header('Location: index?type=warning&message=Email already exists! Please use another email!');
            exit();
        }

        // NEW: Hash the password on creation
        if (isset($post['password']) && !empty($post['password'])) {
            $post['password'] = password_hash($post['password'], PASSWORD_DEFAULT);
        } else {
            // Require a password for new faculty members
            header('Location: index?type=warning&message=Password is required for new faculty!');
            exit();
        }

        $token = generateToken();
        $post["token"] = $token;
        
        // --- Your existing file upload logic ---
        unset($post['image']);
        $uploadedPaths = $this->db->handleMultipleFileUpload($image ?? [], $folder); // Added fallback for $image
        $uploadedNew = implode('|', $uploadedPaths);
        if(count($uploadedPaths) > 0) {
            $post["image"] = $uploadedNew;
        }

        $this->db->insertRequestBatchRquest($post,'users','src/images/products/uploads/');

        header('Location: index?type=success&message=Successfully Registered!');
        exit();
    }
}

    public function delete(){
        $data = getRequestAll();

        extract($data);

        $this->db->Update("update users SET deleted = 1 WHERE user_id = ? ", array( $id) );


        $res = [
            'status'=> true,
            'msg' => 'Successfully deleted!'
        ];

        echo json_encode($res);
    }






}
