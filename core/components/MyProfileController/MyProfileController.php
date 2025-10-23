<?php

class MyProfileController {

    protected $db;
    protected $view = "MyProfileController";

    public function __construct($db) {
        $this->db = $db;
    }

    public function index() {
        $d = [];
        $result = $this->db->Select("SELECT * FROM users WHERE user_id = ?", [$_SESSION["user_id"]])[0] ?? [];
        $d["details"] = $result;
        return ["content" => loadView('components/'.$this->view.'/views/custom', $d)];
    }

    public function js(){ return [ $this->view.'/js/custom.js' ]; }
    public function css(){ return []; }

    public function source() {
        $data = getRequestAll();
        extract($data);
        $d["details"] = false;

        $d["departmentlist"] = $this->db->Select("SELECT id, name FROM brand WHERE deleted = 0 AND module = 'brand-type'", []);

        if ($action == "edit" && ($id != '' && $id != 'undefined')) {
            $result = $this->db->Select("SELECT * FROM users WHERE user_id = ?", [$id])[0] ?? null;
            $d["details"] = $result;
        }

        $res = [
            'header'=> ($action ?? '') === "add" ? "Add User" : 'Edit User',
            "html" => loadView('components/'.$this->view.'/views/modal_details', $d),
            'button' => '<button class="btn btn-primary" type="submit">Submit form</button>',
            'action' => 'afterSubmit'
        ];

        echo json_encode($res);
    }

    public function afterSubmit(){
        $post = getRequestAll();
        extract($post);

        $folder = 'src/images/products/uploads/';

        if (isset($user_id)) {
            // edit
            $table = "users";
            $update = $post;

            unset($update['image']);
            $uploadedPaths = $this->db->handleMultipleFileUpload($image ?? null, $folder);
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
            $data = $this->db->Select("SELECT email FROM users WHERE email = ? AND deleted = 0", [$email] );
            if (count($data) > 0){
                header('Location: index?type=warning&message=Email already exist!.Please use another email!');
                exit();
            } else {
                $token = generateToken();
                $post["token"] = $token;

                unset($post['image']);
                $uploadedPaths = $this->db->handleMultipleFileUpload($image ?? null, $folder);
                $uploadedNew = implode('|', $uploadedPaths);
                if(count($uploadedPaths) > 0) {
                    $post["image"] = $uploadedNew;
                }
                $this->db->insertRequestBatchRquest($post,'users','src/images/products/uploads/');

                header('Location: index?type=success&message=Successfully Registered!');
                exit();
            }
        }
    }

    public function delete(){
        $data = getRequestAll();
        extract($data);
        $this->db->Update("UPDATE users SET deleted = 1 WHERE user_id = ?", [ $id ]);
        $res = ['status'=> true, 'msg' => 'Successfully deleted!'];
        echo json_encode($res);
    }
}
