<?php 


class SettingsController {

    protected $db;

    public function __construct($db) {
        $this->db = $db;
        $this->view = "SettingsController";
    }


    public function index() {

        $data = [];
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




    public function text() {


        echo json_encode(["test"]);

    }

  

}
