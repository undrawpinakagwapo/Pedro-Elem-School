<?php

class SfReportController {

    protected $db;
    protected $Name;
    protected $view;

    public function __construct($db) {
        $this->db   = $db;
        $this->Name = 'sf-report';           // maps to /component/sf-report/*
        $this->view = 'SfReportController';  // folder name under /components
    }

    public function index() {
        $listsection = [
            ['id' => 1, 'school_year' => '2023-2024', 'gs_name' => 'Grade 6 - A'],
            ['id' => 2, 'school_year' => '2024-2025', 'gs_name' => 'Grade 6 - B'],
        ];

        return [
            "content" => loadView('components/'.$this->view.'/views/custom', [
                'listsection' => $listsection
            ])
        ];
    }



    public function js()  { return []; }
    public function css() { return []; }
}
