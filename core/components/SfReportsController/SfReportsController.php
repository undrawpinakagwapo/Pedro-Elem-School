<?php

class SfReportsController
{
    protected $db;
    protected $view;

    public function __construct($db)
    {
        $this->db   = $db;
        $this->view = 'SfReportsController';
    }

    /** Page */
    public function index()
    {
        $data = [];
        return ["content" => loadView('components/'.$this->view.'/views/custom', $data)];
    }

    /** Assets */
    public function js(){  return [$this->view.'/js/custom.js']; }
    public function css(){ return []; }

    /* =================== DATA HELPERS =================== */
    private function gradeLevels(){
        return $this->db->Select("SELECT id, name AS grade_name FROM grade_level WHERE deleted=0 ORDER BY name");
    }
    private function sectionsByGrade($gradeId){
        return $this->db->Select(
            "SELECT s.id, s.name
               FROM section s
              WHERE s.deleted=0 AND s.grade_id=?
           ORDER BY s.name", [(int)$gradeId]
        );
    }
    private function curriculaBySection($sectionId){
        return $this->db->Select(
            "SELECT c.id, c.school_year
               FROM curriculum c
              WHERE c.deleted=0 AND c.grade_id=?
           ORDER BY c.school_year DESC", [(int)$sectionId]
        );
    }

    /* =================== MODAL SOURCE (AJAX) =================== */
    public function source()
    {
        $req  = getRequestAll();
        $form = strtolower(trim((string)($req['form'] ?? '')));
        if (!in_array($form, ['sf2','sf9','sf10'], true)) {
            echo json_encode(['status'=>false,'message'=>'Unknown form type.']); return;
        }

        $grades     = $this->gradeLevels();
        $gradeId    = isset($req['grade_id']) ? (int)$req['grade_id'] : ( $grades ? (int)$grades[0]['id'] : 0 );
        $sections   = $gradeId ? $this->sectionsByGrade($gradeId) : [];
        $sectionId  = isset($req['section_id']) ? (int)$req['section_id'] : ( $sections ? (int)$sections[0]['id'] : 0 );
        $curricula  = $sectionId ? $this->curriculaBySection($sectionId) : [];
        $currId     = isset($req['curriculum_id']) ? (int)$req['curriculum_id'] : ( $curricula ? (int)$curricula[0]['id'] : 0 );

        $months = [];
        for ($m=1; $m<=12; $m++) {
            $months[] = ['value'=>sprintf('%02d',$m), 'label'=>date('F', mktime(0,0,0,$m,1))];
        }

        $data = [
            'form'          => $form,
            'grades'        => $grades,
            'grade_id'      => $gradeId,
            'sections'      => $sections,
            'section_id'    => $sectionId,
            'curricula'     => $curricula,
            'curriculum_id' => $currId,
            'months'        => $months,
            'year'          => (int)date('Y'),
            'month'         => date('m'),
        ];

        $html = loadView('components/'.$this->view.'/views/modal_export', $data);

        // Titles are still returned (even though the no-bootstrap modal in JS ignores them)
        $titles = [
            'sf2'  => 'Export • School Form 2 (SF2) – Daily Attendance',
            'sf9'  => 'Export • School Form 9 (SF9) – Progress Report',
            'sf10' => 'Export • School Form 10 (SF10) – Permanent Record',
        ];

        echo json_encode([
            'status' => true,
            'header' => $titles[$form],
            'html'   => $html,
            'button' => '<button class="btn btn-primary" type="button">Export</button>',
            'action' => 'sfReportsDoExport'
        ]);
    }

    /* =================== Cascading select helpers =================== */
    public function listSections()
    {
        $req = getRequestAll();
        $gid = (int)($req['grade_id'] ?? 0);
        echo json_encode(['status'=>true, 'sections'=>$gid ? $this->sectionsByGrade($gid) : []]);
    }

    public function listCurricula()
    {
        $req = getRequestAll();
        $sid = (int)($req['section_id'] ?? 0);
        echo json_encode(['status'=>true, 'curricula'=>$sid ? $this->curriculaBySection($sid) : []]);
    }

    /**
     * =============== NEW: Students list for SF9 ===============
     * POST: section_id, curriculum_id
     * Returns: {status:true, students:[{id, name}]}
     * name is "Lastname, Firstname M." with optional LRN suffix.
     */
    public function listStudents()
    {
        $req = getRequestAll();
        $sid = (int)($req['section_id'] ?? 0);
        $cid = (int)($req['curriculum_id'] ?? 0);

        if (!$sid || !$cid) {
            echo json_encode(['status'=>false,'message'=>'Missing section_id or curriculum_id.']); return;
        }

        $rows = $this->db->Select(
            "SELECT u.user_id AS id,
                    u.account_last_name   AS last,
                    u.account_first_name  AS first,
                    u.account_middle_name AS middle,
                    u.LRN
               FROM registrar_student rs
               JOIN users u ON u.user_id = rs.student_id
              WHERE rs.deleted=0 AND rs.status=1
                AND rs.section_id=? AND rs.curriculum_id=?
           ORDER BY u.account_last_name, u.account_first_name",
            [$sid,$cid]
        );

        $students = [];
        foreach ($rows ?: [] as $r) {
            $mid = trim((string)($r['middle'] ?? ''));
            $mi  = $mid !== '' ? ' ' . mb_substr($mid,0,1,'UTF-8').'.' : '';
            $label = trim(($r['last'] ?? '') . ', ' . ($r['first'] ?? '') . $mi);
            if (!empty($r['LRN'])) $label .= ' — LRN: ' . $r['LRN'];
            $students[] = ['id'=>(int)$r['id'], 'name'=>$label];
        }

        echo json_encode(['status'=>true,'students'=>$students]);
    }
}
