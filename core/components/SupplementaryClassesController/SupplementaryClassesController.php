<?php
/* ===========================================================
 * SupplementaryClassesController
 * - Lists summer-eligible (1–2 fails) or retained (3+ fails)
 * - Remedial encode modal visible only to advisers (or admin)
 * =========================================================== */

class SupplementaryClassesController
{
    protected $db;
    protected $view = 'SupplementaryClassesController';
    protected $subjectTable  = 'subjects';
    protected $remedialTable = 'student_remedial_classes';

    public function __construct($db){ $this->db = $db; }

    /* ------------ basic helpers ------------- */
    private function one($sql,$p=[]){ $r=$this->db->Select($sql,$p); return $r? $r[0]:null; }
    private function many($sql,$p=[]){ return $this->db->Select($sql,$p); }
    private function uid(){ return $_SESSION['user_id'] ?? null; }
    private function utype(){ return $_SESSION['user_type'] ?? null; }

    /* ------------ transactions ------------- */
    private function txBegin(){ foreach(['begin','Begin','BeginTrans','beginTransaction'] as $m){ if(method_exists($this->db,$m)) return $this->db->{$m}(); } }
    private function txCommit(){ foreach(['commit','Commit','CompleteTrans'] as $m){ if(method_exists($this->db,$m)) return $this->db->{$m}(); } }
    private function txRollback(){ foreach(['rollback','Rollback','FailTrans'] as $m){ if(method_exists($this->db,$m)) return $this->db->{$m}(); } }

    /* ------------ access helpers ------------- */
    private function isAdviserOfSection($uid,$sid){
        return (bool)$this->one("SELECT 1 FROM section WHERE deleted=0 AND id=? AND adviser_id=? LIMIT 1",[$sid,$uid]);
    }
    private function isAdviserOfCurriculum($uid,$cid){
        return (bool)$this->one("SELECT 1 FROM curriculum WHERE deleted=0 AND id=? AND adviser_id=? LIMIT 1",[$cid,$uid]);
    }

    private function sectionsForTeacher($uid){
        return $this->many("
            SELECT DISTINCT s.id, s.name, gl.name AS grade_name
              FROM section s
              JOIN grade_level gl ON gl.id=s.grade_id
         LEFT JOIN curriculum c        ON c.grade_id=s.id AND c.deleted=0
         LEFT JOIN curriculum_child cc ON cc.curriculum_id=c.id AND cc.deleted=0
             WHERE s.deleted=0 AND (s.adviser_id=? OR c.adviser_id=? OR cc.adviser_id=?)
          ORDER BY gl.name,s.name",[$uid,$uid,$uid]);
    }

    private function curriculaForTeacher($uid,$sid){
        if($this->isAdviserOfSection($uid,$sid)){
            return $this->many("SELECT id,school_year FROM curriculum WHERE deleted=0 AND grade_id=? ORDER BY school_year DESC",[$sid]);
        }
        return $this->many("
            SELECT DISTINCT c.id,c.school_year
              FROM curriculum c
         LEFT JOIN curriculum_child cc ON cc.curriculum_id=c.id AND cc.deleted=0
             WHERE c.deleted=0 AND c.grade_id=? AND (c.adviser_id=? OR cc.adviser_id=?)
          ORDER BY c.school_year DESC",[$sid,$uid,$uid]);
    }

    /* ------------ get/create SSG ------------ */
    private function getOrCreateSSG($student,$subject,$section,$curr){
        $row=$this->one("SELECT id FROM student_subject_grades WHERE deleted=0 AND student_id=? AND subject_id=? AND section_id=? AND curriculum_id=? LIMIT 1",[$student,$subject,$section,$curr]);
        if($row) return (int)$row['id'];
        $sy=$this->one("SELECT school_year FROM curriculum WHERE id=? LIMIT 1",[$curr]);
        $uid=$this->uid();
        $this->db->Insert("
            INSERT INTO student_subject_grades
              (student_id,subject_id,section_id,curriculum_id,school_year,status,deleted,added_by,latest_edited_by)
            VALUES (?,?,?,?,?,1,0,?,?)",[$student,$subject,$section,$curr,$sy['school_year']??null,$uid,$uid]);
        $n=$this->one("SELECT id FROM student_subject_grades WHERE deleted=0 AND student_id=? AND subject_id=? AND section_id=? AND curriculum_id=? ORDER BY id DESC LIMIT 1",[$student,$subject,$section,$curr]);
        return $n?(int)$n['id']:0;
    }

    /* ===========================================================
     * Context Builder (used for index + fetch)
     * =========================================================== */
    private function buildContext($req){
        $uid=$this->uid(); $ut=$this->utype();
        $q=trim($req['q']??'');
        $type=($req['list_type']??'summer')==='retained'?'retained':'summer';

        // ADMIN / PRINCIPAL
        if($ut!=2){
            $sections=$this->many("SELECT s.id,s.name,gl.name AS grade_name FROM section s JOIN grade_level gl ON gl.id=s.grade_id WHERE s.deleted=0 ORDER BY gl.name,s.name");
            $sid=$sections?(int)($req['section_id']??$sections[0]['id']):null;
            $curricula=$sid?$this->many("SELECT id,school_year FROM curriculum WHERE deleted=0 AND grade_id=? ORDER BY school_year DESC",[$sid]):[];
            $cid=$curricula?(int)($req['curriculum_id']??$curricula[0]['id']):null;
            $rows=($sid&&$cid)?$this->listRows($sid,$cid,$q,null,$type):[];
            return ['sections'=>$sections,'section_id'=>$sid,'curricula'=>$curricula,'curriculum_id'=>$cid,'rows'=>$rows,'list_type'=>$type,'can_edit'=>true];
        }

        // TEACHER
        $sections=$this->sectionsForTeacher($uid);
        $sid=$sections?(int)($req['section_id']??$sections[0]['id']):null;
        $curricula=$sid?$this->curriculaForTeacher($uid,$sid):[];
        $cid=$curricula?(int)($req['curriculum_id']??$curricula[0]['id']):null;
        $rows=($sid&&$cid)?$this->listRows($sid,$cid,$q,$uid,$type):[];
        $can_edit=$this->isAdviserOfSection($uid,$sid)||$this->isAdviserOfCurriculum($uid,$cid);
        return ['sections'=>$sections,'section_id'=>$sid,'curricula'=>$curricula,'curriculum_id'=>$cid,'rows'=>$rows,'list_type'=>$type,'can_edit'=>$can_edit];
    }

    /* ------------ Build student rows ------------ */
    private function listRows($section,$curr,$q='',$teacher=null,$type='summer'){
        $p=[$section,$curr];
        $w=["rs.deleted=0","rs.status=1","u.status=1","rs.section_id=?","rs.curriculum_id=?"];
        if($q!==''){ $like="%$q%"; $w[]="(u.account_last_name LIKE ? OR u.account_first_name LIKE ? OR u.LRN LIKE ?)"; array_push($p,$like,$like,$like); }

        $students=$this->many("
            SELECT u.user_id AS id,CONCAT(u.account_last_name,', ',u.account_first_name,' ',u.account_middle_name) AS full_name,u.LRN,s.name AS section_name,gl.name AS grade_name
              FROM registrar_student rs
              JOIN users u ON u.user_id=rs.student_id
              JOIN section s ON s.id=rs.section_id
              JOIN grade_level gl ON gl.id=s.grade_id
             WHERE ".implode(' AND ',$w)."
          ORDER BY u.account_last_name,u.account_first_name",$p);
        if(!$students) return [];

        $ids=array_column($students,'id');
        $in=implode(',',array_fill(0,count($ids),'?'));
        $grades=$this->many("
    SELECT g.student_id, g.subject_id, g.final_average, g.q1, g.q2, g.q3, g.q4, s.code, s.name
      FROM student_subject_grades g
      JOIN {$this->subjectTable} s ON s.id=g.subject_id
     WHERE g.deleted=0 AND g.section_id=? AND g.curriculum_id=? AND g.student_id IN ($in)",
     array_merge([$section,$curr],$ids));

        $fails=[];
foreach($grades as $g){
    // NEW: Check if all four quarters have grades. If not, skip.
    if ($g['q1'] === null || $g['q2'] === null || $g['q3'] === null || $g['q4'] === null) {
        continue;
    }

    // Original logic to check if the final average is a failing grade
    if($g['final_average']===null) continue;
    $fa=(float)$g['final_average'];
    if($fa>=75) continue;

    $sid=(int)$g['student_id'];
    $fails[$sid][]=($g['code']?$g['code'].' - ':'').$g['name'].' ('.number_format($fa,2).')';
}

        $out=[];
        foreach($students as $s){
            $sid=(int)$s['id'];
            if(empty($fails[$sid])) continue;
            $count=count($fails[$sid]);
            if($type==='summer'){
                if($count<1||$count>2) continue;
                $status='eligible';
            }else{
                if($count<3) continue;
                $status='retained';
            }
            $out[]=['id'=>$sid,'full_name'=>$s['full_name'],'LRN'=>$s['LRN'],'grade_name'=>$s['grade_name'],'section_name'=>$s['section_name'],'failed_count'=>$count,'subjects_text'=>implode(', ',$fails[$sid]),'status'=>$status];
        }
        return $out;
    }

    /* ===========================================================
     * ROUTES
     * =========================================================== */
    public function index(){
        $data=$this->buildContext($_GET??[]);
        return ['header'=>'Supplementary / Remedial Classes','content'=>loadView('components/'.$this->view.'/views/custom',$data)];
    }

    public function fetch(){
        $req=getRequestAll();
        $ctx=$this->buildContext($req);
        echo json_encode(['status'=>true]+$ctx);
    }

    /* ------------ fetchRemedial ------------ */
    public function fetchRemedial(){
        try{
            $r=getRequestAll();
            $uid=$this->uid(); $ut=$this->utype();
            $sec=(int)($r['section_id']??0);
            $cur=(int)($r['curriculum_id']??0);
            $stu=(int)($r['student_id']??0);
            if(!$sec||!$cur||!$stu){ echo json_encode(['status'=>false,'message'=>'Missing fields.']); return; }

            if($ut==2 && !$this->isAdviserOfSection($uid,$sec) && !$this->isAdviserOfCurriculum($uid,$cur)){
                echo json_encode(['status'=>false,'message'=>'Not allowed.']); return;
            }

            $fails=$this->many("
                SELECT g.subject_id AS id,s.code,s.name,g.final_average
                  FROM student_subject_grades g
                  JOIN {$this->subjectTable} s ON s.id=g.subject_id
                 WHERE g.deleted=0 AND g.student_id=? AND g.section_id=? AND g.curriculum_id=?
                   AND g.final_average IS NOT NULL AND g.final_average<75
              ORDER BY s.name",[$stu,$sec,$cur]);

            $rows=[];
            foreach($fails as $f){
                $ssg=$this->getOrCreateSSG($stu,(int)$f['id'],$sec,$cur);
                $r2=$this->one("SELECT conducted_from,conducted_to,remedial_mark,recomputed_final,remarks FROM {$this->remedialTable} WHERE deleted=0 AND ssg_id=? AND subject_id=? LIMIT 1",[$ssg,(int)$f['id']]);
                $rows[]=[
        'subject_id'=>(int)$f['id'],
        'subject_label'=>$f['name'], // Changed this line
        'final_rating'=>(float)$f['final_average'],
                    'conducted_from'=>$r2['conducted_from']??null,
                    'conducted_to'=>$r2['conducted_to']??null,
                    'remedial_mark'=>$r2['remedial_mark']??null,
                    'recomputed_final'=>$r2['recomputed_final']??null,
                    'remarks'=>$r2['remarks']??''
                ];
            }
            echo json_encode(['status'=>true,'rows'=>$rows]);
        }catch(Throwable $e){
            error_log('fetchRemedial: '.$e->getMessage());
            echo json_encode(['status'=>false,'message'=>'Failed to load remedials.']);
        }
    }

    /* ------------ saveRemedial ------------ */
    public function saveRemedial(){
        try{
            $r=getRequestAll();
            $uid=$this->uid(); $ut=$this->utype();
            $sec=(int)($r['section_id']??0);
            $cur=(int)($r['curriculum_id']??0);
            $stu=(int)($r['student_id']??0);
            $from=trim($r['conducted_from']??'');
            $to=trim($r['conducted_to']??'');
            $rows=$r['rows']??[];
            if(is_string($rows)){ $tmp=json_decode($rows,true); if(json_last_error()===0)$rows=$tmp; }
            if(!$sec||!$cur||!$stu||!is_array($rows)){ echo json_encode(['status'=>false,'message'=>'Missing fields.']); return; }

            if($ut==2 && !$this->isAdviserOfSection($uid,$sec) && !$this->isAdviserOfCurriculum($uid,$cur)){
                echo json_encode(['status'=>false,'message'=>'Not allowed.']); return;
            }

            $this->txBegin();
            $seen=[];
            foreach($rows as $r2){
                $sub=(int)($r2['subject_id']??0);
                if(!$sub)continue;
                $ssg=$this->getOrCreateSSG($stu,$sub,$sec,$cur);
                $final=$r2['final_rating']!==''?(float)$r2['final_rating']:null;
                $mark=$r2['remedial_mark']!==''?(float)$r2['remedial_mark']:null;
                $rec=$r2['recomputed_final']!==''?(float)$r2['recomputed_final']:null;
                $rem=trim($r2['remarks']??'');
                $ex=$this->one("SELECT id FROM {$this->remedialTable} WHERE deleted=0 AND ssg_id=? AND subject_id=? LIMIT 1",[$ssg,$sub]);
                if($ex){
                    $this->db->Update("UPDATE {$this->remedialTable} SET conducted_from=?,conducted_to=?,final_rating=?,remedial_mark=?,recomputed_final=?,remarks=?,latest_edited_by=? WHERE id=?",[$from?:null,$to?:null,$final,$mark,$rec,$rem,$uid,(int)$ex['id']]);
                    $seen[]=(int)$ex['id'];
                }else{
                    $this->db->Insert("INSERT INTO {$this->remedialTable}(ssg_id,subject_id,conducted_from,conducted_to,final_rating,remedial_mark,recomputed_final,remarks,deleted,added_by,latest_edited_by) VALUES (?,?,?,?,?,?,?,?,0,?,?)",[$ssg,$sub,$from?:null,$to?:null,$final,$mark,$rec,$rem,$uid,$uid]);
                    $n=$this->one("SELECT id FROM {$this->remedialTable} WHERE deleted=0 AND ssg_id=? AND subject_id=? ORDER BY id DESC LIMIT 1",[$ssg,$sub]);
                    if($n)$seen[]=(int)$n['id'];
                }
            }
            if($seen){
                $in=implode(',',array_fill(0,count($seen),'?'));
                $this->db->Update("UPDATE {$this->remedialTable} SET deleted=1,latest_edited_by=? WHERE deleted=0 AND ssg_id IN(SELECT id FROM student_subject_grades WHERE student_id=? AND section_id=? AND curriculum_id=?) AND id NOT IN($in)",array_merge([$uid,$stu,$sec,$cur],$seen));
            }
            $this->txCommit();
            echo json_encode(['status'=>true,'message'=>'Remedial records saved.']);
        }catch(Throwable $e){
            $this->txRollback();
            error_log('saveRemedial: '.$e->getMessage());
            echo json_encode(['status'=>false,'message'=>'Failed to save remedials.']);
        }
    }

    /* ------------ assets ------------ */
    public function js(){  return [$this->view.'/js/custom.js']; }
    public function css(){ return []; }
}
