<?php 

class CurriculumManagementController {

    protected $db;

    public function __construct($db) {
        $this->db = $db;
        $this->view = "CurriculumManagementController";
    }

    public function list(){
        $data["list"] = $this->db->Select("select s.*, gl.name as gradeName, CONCAT(u.account_first_name, ' ', u.account_last_name) as full_name 
            from section s 
            left join grade_level gl ON gl.id = s.grade_id
            left join users u ON u.user_id = s.adviser_id
            where s.deleted = 0", []);
        echo json_encode($data["list"]);
    }

    public function index() {
        $data = [];

        $data["list"] = $this->db->Select("select 
            c.*, 
            CONCAT(gl.`name`, ' - ', s.`name`) as `gs_name`, 
            CONCAT(u.account_first_name, ' ', u.account_last_name) adviser_name 
            from curriculum c 
            join section s ON s.id = c.grade_id
            join grade_level gl ON gl.id = s.grade_id
            join users u ON u.user_id = c.adviser_id
            where c.deleted = 0", []);
        
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
        $d["child"]   = false;

        $d["adviser"] = $this->db->Select("select user_id id, CONCAT(account_first_name, ' ', account_last_name) name 
            from users 
            where deleted = 0 and status = 1 and user_type = 2", []);

        $d["grade"] = $this->db->Select("select s.id, CONCAT(gl.`name`, ' - ', s.`name`) as `name` 
            from section s 
            join grade_level gl ON gl.id = s.grade_id 
            where s.deleted = 0", []);

        // Get all subjects
        $allSubjects = $this->db->Select("select id, code, name from subjects where deleted = 0", []);
        $selectedSubjects = [];

        // If editing, fetch existing curriculum details + selected subjects
        if ($action == "edit" && ($id != '' && $id != 'undefined')) {
            $result = $this->db->Select("select * from curriculum where id = ?", [$id])[0];
            $d["details"] = $result;

            $result = $this->db->Select("select c.id, 
                        c.subject_id,
                        s.`code`,
                        s.`name`
                    from curriculum_child c 
                    join subjects s on s.id = c.subject_id
                    where c.curriculum_id = ? and c.deleted = 0", [$id]);
            $d["child"] = $result;

            // Collect IDs of already selected subjects
            $selectedSubjects = array_column($result, 'subject_id');
        }

        // Filter available subjects (remove already selected ones)
        if (!empty($selectedSubjects)) {
            $allSubjects = array_filter($allSubjects, function ($subj) use ($selectedSubjects) {
                return !in_array($subj['id'], $selectedSubjects);
            });
        }

        $d["subjects"] = $allSubjects;

        $res = [
            'header'=> (isset($action) && $action == "add") ? "Add" : 'Edit',
            "html"  => loadView('components/'.$this->view.'/views/modal_details', $d),
            'button'=> '<button class="btn btn-primary" type="submit">Submit form</button>',
            'action'=> 'afterSubmit'
        ];

        echo json_encode($res);
    }

    public function afterSubmit(){
        $data = getRequestAll();
        extract($data);

        if (isset($id)) {
            // Update curriculum
            $this->db->Update("update curriculum 
                SET grade_id = ?, adviser_id = ?, school_year = ?, name = ?  
                WHERE id = ?", [
                    $grade_id, 
                    $adviser_id, 
                    $school_year, 
                    $name, 
                    $id
                ]);

            $table = "curriculum_child";
            foreach ($itemlist["data"] as $value) {
                if (isset($value["id"])) {
                    $where = [ 'id' => $value["id"] ];
                    $update = [];
                    $update["deleted"] = isset($value["deleted"]) && $value["deleted"] == 1 ? 1 : 0;
                    $this->db->updateField($table, $update, $where);
                } else {
                    $value["curriculum_id"] = $id;
                    $value["adviser_id"]    = $adviser_id;
                    $this->db->insertRequestBatchRquest($value, $table);
                }
            }

            header('Location: index?type=success&message=Successfully Updated!');
            exit();
        } else {
            // Insert new curriculum
            $id = $this->db->Insert("INSERT INTO curriculum (`grade_id`,`adviser_id`,`school_year`,`name`) VALUES (?,?,?,?)", [
                $grade_id,
                $adviser_id,
                $school_year,
                $name
            ]);
        
            $table = "curriculum_child";
            foreach ($itemlist["data"] as $value) {
                $value["curriculum_id"] = $id;
                $value["adviser_id"]    = $adviser_id;
                $this->db->insertRequestBatchRquest($value, $table);
            }
            
            header('Location: index?type=success&message=Successfully Added!');
            exit();
        }
    }

    public function delete(){
    $data = getRequestAll();
    extract($data);

    // FIX: Changed table from 'section' to 'curriculum'
    $this->db->Update("update curriculum SET deleted = 1 WHERE id = ? ", [ $id ]);

    $res = [
        'status'=> true,
        'msg'   => 'Successfully deleted!'
    ];

    echo json_encode($res);
}
}
