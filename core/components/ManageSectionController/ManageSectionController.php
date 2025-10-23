<?php 

class ManageSectionController {

    protected $db;
    protected $view;

    public function __construct($db) {
        $this->db = $db;
        $this->view = "ManageSectionController";
    }

    public function list(){
        // Return sections with joins, sorted by grade level asc, then section name
        $data["list"] = $this->db->Select(
            "SELECT 
                s.*, 
                gl.name AS gradeName, 
                CONCAT(u.account_first_name, ' ', u.account_last_name) AS full_name
             FROM section s
             LEFT JOIN grade_level gl ON gl.id = s.grade_id
             LEFT JOIN users u       ON u.user_id = s.adviser_id
             WHERE s.deleted = 0
             ORDER BY s.grade_id ASC, s.name ASC",
            array()
        );
        echo json_encode($data["list"]);
    }

    public function index() {
        $data = [];

        // Feed the view with a sorted list too (same ordering)
        $data["list"] = $this->db->Select(
            "SELECT 
                s.*, 
                gl.name AS gradeName
             FROM section s
             LEFT JOIN grade_level gl ON gl.id = s.grade_id
             WHERE s.deleted = 0
             ORDER BY s.grade_id ASC, s.name ASC",
            array()
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
        $data = getRequestAll();
        extract($data);

        $d["details"] = false;

        // Sort adviser list alphabetically
        $d["adviser"] = $this->db->Select(
            "SELECT user_id AS id, CONCAT(account_first_name, ' ', account_last_name) AS name
             FROM users 
             WHERE deleted = 0 AND status = 1
             ORDER BY account_last_name ASC, account_first_name ASC",
            array()
        );

        // Sort grades by id (or change to ORDER BY CAST(name AS UNSIGNED) if you prefer)
        $d["grade"] = $this->db->Select(
            "SELECT id, name 
             FROM grade_level 
             WHERE deleted = 0
             ORDER BY id ASC",
            array()
        );

        if ($action == "edit" && ($id != '' && $id != 'undefined')) {
            $result = $this->db->Select("SELECT * FROM section WHERE id = ?", array($id));
            if ($result) {
                $d["details"] = $result[0];
            }
        }

        $res = [
            'header'=> (isset($action) && $action == "add") ? "Add" : 'Edit',
            "html" => loadView('components/'.$this->view.'/views/modal_details', $d),
            'button' => '<button class="btn btn-primary" type="submit">Submit form</button>',
            'action' => 'afterSubmit'
        ];

        echo json_encode($res);
    }

    public function afterSubmit(){
        $data = getRequestAll();
        extract($data);

        if (isset($id) && $id !== '') {
            // Update existing
            $this->db->Update(
                "UPDATE section 
                 SET name = ?, code = ?, grade_id = ? 
                 WHERE id = ?",
                array($name, $code, $grade_id, $id)
            );

            header('Location: index?type=success&message=Successfully Updated!');
            exit();
        } else {
            // Insert new
            $this->db->insertRequestBatchRquest($data,'section', false);
            header('Location: index?type=success&message=Successfully Registered!');
            exit();
        }
    }

    public function delete(){
        $data = getRequestAll();
        extract($data);

        $this->db->Update("UPDATE section SET deleted = 1 WHERE id = ? ", array($id));

        $res = [
            'status'=> true,
            'msg' => 'Successfully deleted!'
        ];

        echo json_encode($res);
    }
}
