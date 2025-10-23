<?php

class TeacherDashboardController
{
    protected $db;
    protected $view = 'TeacherDashboardController';

    public function __construct($db) {
        // Basic session sanity (uncomment if you gate auth here)
        // if (empty($_SESSION['user_id']) || (int)($_SESSION['status'] ?? 0) !== 1) {
        //   header('Location: /auth'); exit;
        // }
        $this->db = $db;
    }

    /* ===== tiny utils ===== */
    private function one($sql,$p=[]){ $r=$this->db->Select($sql,$p); return $r? $r[0]:null; }
    private function many($sql,$p=[]){ return $this->db->Select($sql,$p); }
    private function uid(){   return $_SESSION['user_id']   ?? null; }
    private function utype(){ return $_SESSION['user_type'] ?? null; } // 1=admin,2=teacher,...

    /* ===== tx fallbacks ===== */
    private function txBegin(){ foreach (['Begin','begin','beginTransaction','startTransaction','StartTrans','BeginTrans','beginTrans'] as $m) if (method_exists($this->db,$m)) return $this->db->{$m}(); return null; }
    private function txCommit(){ foreach (['Commit','commit','commitTransaction','CompleteTrans','endTransaction','CommitTrans'] as $m) if (method_exists($this->db,$m)) return $this->db->{$m}(); return null; }
    private function txRollback(){ foreach (['Rollback','rollback','rollBack','FailTrans','cancelTransaction','RollbackTrans'] as $m) if (method_exists($this->db,$m)) return $this->db->{$m}(); return null; }

    /* ===== access helpers (same policy as StudentAttendanceController) ===== */
    private function isAdviserOfSection($teacherId, $sectionId) {
        return (bool)$this->one(
            "SELECT 1 FROM section s WHERE s.deleted=0 AND s.id=? AND s.adviser_id=? LIMIT 1",
            [$sectionId,$teacherId]
        );
    }
    private function isAdviserOfCurriculum($teacherId, $curriculumId) {
        return (bool)$this->one(
            "SELECT 1 FROM curriculum c WHERE c.deleted=0 AND c.id=? AND c.adviser_id=? LIMIT 1",
            [$curriculumId,$teacherId]
        );
    }
    private function sectionsForTeacher($teacherId){
        return $this->many(
            "SELECT DISTINCT s.id, s.name, gl.name AS grade_name
               FROM section s
               JOIN grade_level gl ON gl.id = s.grade_id
          LEFT JOIN curriculum c        ON c.grade_id = s.id AND c.deleted=0
          LEFT JOIN curriculum_child cc ON cc.curriculum_id = c.id AND cc.deleted=0
              WHERE s.deleted=0
                AND (s.adviser_id = ? OR c.adviser_id = ? OR cc.adviser_id = ?)
           ORDER BY gl.name, s.name",
           [$teacherId,$teacherId,$teacherId]
        );
    }
    private function curriculaForTeacher($teacherId, $sectionId){
        if ($this->isAdviserOfSection($teacherId,$sectionId)) {
            return $this->many(
                "SELECT c.id, c.school_year
                   FROM curriculum c
                  WHERE c.deleted=0 AND c.grade_id=?
               ORDER BY c.school_year DESC",
               [$sectionId]
            );
        }
        return $this->many(
            "SELECT DISTINCT c.id, c.school_year
               FROM curriculum c
          LEFT JOIN curriculum_child cc ON cc.curriculum_id = c.id AND cc.deleted=0
              WHERE c.deleted=0 AND c.grade_id=? AND (c.adviser_id = ? OR cc.adviser_id = ?)
           ORDER BY c.school_year DESC",
           [$sectionId,$teacherId,$teacherId]
        );
    }
    private function assertTeacherAccess($teacherId, $sectionId, $curriculumId){
        $cur = $this->one("SELECT grade_id AS section_id FROM curriculum WHERE id=? AND deleted=0 LIMIT 1", [$curriculumId]);
        if (!$cur || (int)$cur['section_id'] !== (int)$sectionId) return false;
        if ($this->isAdviserOfSection($teacherId,$sectionId) || $this->isAdviserOfCurriculum($teacherId,$curriculumId)) return true;
        return (bool)$this->one(
            "SELECT 1 FROM curriculum_child WHERE deleted=0 AND curriculum_id=? AND adviser_id=? LIMIT 1",
            [$curriculumId,$teacherId]
        );
    }

    /* ===== data helpers ===== */
    private function students($sectionId, $curriculumId){
        return $this->many(
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
           [$sectionId,$curriculumId]
        );
    }
    private function fmtName($last,$first,$middle){
        $mi = ''; $m = trim((string)$middle);
        if ($m !== '') $mi = mb_strtoupper(mb_substr($m,0,1,'UTF-8'),'UTF-8').'.';
        return trim(mb_strtoupper($last,'UTF-8').', '.mb_strtoupper($first,'UTF-8').($mi ? ' '.$mi : ''));
    }

    private function fmtFirstMiLast($first,$middle,$last){
        $first = trim((string)$first); $middle = trim((string)$middle); $last = trim((string)$last);
        $mi = $middle !== '' ? (mb_substr($middle, 0, 1) . '.') : '';
        return trim($first.' '.($mi ? $mi.' ' : '').$last);
    }

    private function weekdaysBetween($from,$to){
        $d1=new DateTime($from); $d2=new DateTime($to); if($d1>$d2) [$d1,$d2]=[$d2,$d1];
        $cnt=0; while($d1<=$d2){ $dow=(int)$d1->format('N'); if($dow<=5) $cnt++; $d1->modify('+1 day'); }
        return $cnt;
    }
    private function monthRange($ymd){
        $d=new DateTime($ymd); $y=(int)$d->format('Y'); $m=(int)$d->format('m');
        $first=sprintf('%04d-%02d-01',$y,$m);
        $last=(new DateTime($first))->modify('last day of this month')->format('Y-m-d');
        return [$first,$last];
    }

    private function buildContext($req){
        $uid  = $this->uid(); $ut = $this->utype();

        if ($ut != 2) {
            $sections=$this->many("SELECT s.id,s.name,gl.name AS grade_name FROM section s JOIN grade_level gl ON gl.id=s.grade_id WHERE s.deleted=0 ORDER BY gl.name,s.name");
            $section_id=$sections ? (int)($req['section_id'] ?? $sections[0]['id']) : null;
            $curricula=$section_id ? $this->many("SELECT c.id,c.school_year FROM curriculum c WHERE c.deleted=0 AND c.grade_id=? ORDER BY c.school_year DESC",[$section_id]) : [];
            $curriculum_id=$curricula ? (int)($req['curriculum_id'] ?? $curricula[0]['id']) : null;
        } else {
            $sections      = $this->sectionsForTeacher($this->uid());
            $section_id    = $sections ? (int)($req['section_id'] ?? $sections[0]['id']) : null;
            $curricula     = $section_id ? $this->curriculaForTeacher($this->uid(),$section_id) : [];
            $curriculum_id = $curricula ? (int)($req['curriculum_id'] ?? $curricula[0]['id']) : null;
        }

        $today = date('Y-m-d');
        $from  = isset($req['from']) && preg_match('/^\d{4}-\d{2}-\d{2}$/',(string)$req['from']) ? (string)$req['from'] : date('Y-m-d', strtotime('-13 days'));
        $to    = isset($req['to'])   && preg_match('/^\d{4}-\d{2}-\d{2}$/',(string)$req['to'])   ? (string)$req['to']   : $today;
        $date  = isset($req['date']) && preg_match('/^\d{4}-\d{2}-\d{2}$/',(string)$req['date']) ? (string)$req['date'] : $today;

        return compact('sections','section_id','curricula','curriculum_id','from','to','date');
    }

    /** Build the teacher's display name: "First M. Last" with fallbacks */
    private function resolveTeacherHelloName(){
        $uid = (int)($this->uid() ?? 0);
        if ($uid > 0) {
            $row = $this->one("SELECT account_first_name, account_middle_name, account_last_name FROM users WHERE deleted=0 AND user_id=? LIMIT 1", [$uid]);
            if ($row) {
                $name = $this->fmtFirstMiLast($row['account_first_name'] ?? '', $row['account_middle_name'] ?? '', $row['account_last_name'] ?? '');
                if ($name !== '') return $name;
            }
        }
        if (!empty($_SESSION['account_display_name'])) return (string)$_SESSION['account_display_name'];
        if (!empty($_SESSION['account_first_name']) || !empty($_SESSION['account_last_name'])) {
            return $this->fmtFirstMiLast($_SESSION['account_first_name'] ?? '', $_SESSION['account_middle_name'] ?? '', $_SESSION['account_last_name'] ?? '');
        }
        if (!empty($_SESSION['username'])) return (string)$_SESSION['username'];
        return 'Teacher';
    }

    /* ===== endpoints ===== */
    public function index(){
        $ctx = $this->buildContext($_GET ?? []);
        $syRow = ($ctx['curriculum_id'] ?? null) ? $this->many("SELECT school_year FROM curriculum WHERE id=? LIMIT 1", [$ctx['curriculum_id']]) : [];
        $active_sy = $syRow ? ($syRow[0]['school_year'] ?? '') : '';

        // NEW: compute Hello Name from DB and pass to the view
        $helloName = $this->resolveTeacherHelloName();

        $data = $ctx + [
            'active_sy' => $active_sy,
            'helloName' => $helloName,
        ];
        return [
            'header'  => 'Attendance Dashboard',
            'content' => loadView('components/'.$this->view.'/views/custom', $data),
        ];
    }

    public function fetch(){
        header('Content-Type: application/json; charset=utf-8');
        $req = getRequestAll();
        $uid = $this->uid(); $ut = $this->utype();

        try {
            $ctx  = $this->buildContext($req);
            $sid  = (int)($req['section_id']    ?? $ctx['section_id']    ?? 0);
            $cid  = (int)($req['curriculum_id'] ?? $ctx['curriculum_id'] ?? 0);
            $from = $ctx['from']; $to = $ctx['to']; $date = $ctx['date'];

            if (!$sid) { echo json_encode(['status'=>false,'message'=>'Missing section.']); return; }

            // allowed curricula list for this section
            if ($ut == 2) {
                if (!$this->isAdviserOfSection($uid,$sid)) {
                    $curricula = $this->curriculaForTeacher($uid,$sid);
                } else {
                    $curricula = $this->many(
                        "SELECT c.id, c.school_year FROM curriculum c
                         WHERE c.deleted=0 AND c.grade_id=? ORDER BY c.school_year DESC", [$sid]
                    );
                }
            } else {
                $curricula = $this->many(
                    "SELECT c.id, c.school_year FROM curriculum c
                     WHERE c.deleted=0 AND c.grade_id=? ORDER BY c.school_year DESC", [$sid]
                );
            }

            if (!$curricula) { echo json_encode(['status'=>false,'message'=>'No curricula assigned for this section.','curricula'=>[]]); return; }

            $allowedIds = array_map(function($c){ return (int)$c['id']; }, $curricula);
            if (!$cid || !in_array($cid, $allowedIds, true)) { $cid = (int)$curricula[0]['id']; }

            $cur = $this->one("SELECT grade_id FROM curriculum WHERE id=? AND deleted=0 LIMIT 1", [$cid]);
            if (!$cur || (int)$cur['grade_id'] !== (int)$sid) { $cid = (int)$curricula[0]['id']; }

            // Section meta for UI (grade & section names)
            $secMeta = $this->one(
                "SELECT s.name AS section_name, gl.name AS grade_name
                   FROM section s
                   JOIN grade_level gl ON gl.id = s.grade_id
                  WHERE s.id=? LIMIT 1",
                [$sid]
            );
            $section_name = $secMeta['section_name'] ?? '';
            $grade_name   = $secMeta['grade_name'] ?? '';

            // Students
            $students = $this->students($sid,$cid);
            $totalStudents = count($students);
            $byId=[]; foreach($students as $s){ $byId[(int)$s['id']]=$s; }

            if (strtotime($from) > strtotime($to)) { [$from,$to] = [$to,$from]; }
            if ((strtotime($to)-strtotime($from)) > 120*86400) { $from = date('Y-m-d', strtotime($to.' -120 days')); }

            $rows = $this->many(
                "SELECT student_id, attendance_date,
                        UPPER(COALESCE(am_status,'A')) AS am_status,
                        UPPER(COALESCE(pm_status,'A')) AS pm_status,
                        UPPER(COALESCE(remarks,''))    AS remarks
                   FROM student_attendance
                  WHERE section_id=? AND curriculum_id=? AND attendance_date BETWEEN ? AND ?",
                [$sid,$cid,$from,$to]
            );

            $perDay=[]; $presentByStudent=[]; $recordsOnDate=[];
            foreach($rows as $r){
                $ymd=$r['attendance_date'];
                if (!isset($perDay[$ymd])) $perDay[$ymd]=['present'=>0,'recorded'=>0];
                $perDay[$ymd]['recorded']++;
                $anyP = ($r['am_status']==='P' || $r['pm_status']==='P');
                if ($anyP){ $perDay[$ymd]['present']++; $presentByStudent[(int)$r['student_id']] = (int)(($presentByStudent[(int)$r['student_id']] ?? 0) + 1); }
                if (!isset($recordsOnDate[$ymd])) $recordsOnDate[$ymd]=[];
                $recordsOnDate[$ymd][(int)$r['student_id']]=$r;
            }

            $todayRows = $recordsOnDate[$date] ?? [];
            $present_full=0; $present_am_only=0; $present_pm_only=0; $absent_full=0; $tardy_today=0; $absent_today_list=[];
            foreach($todayRows as $sidK=>$r){
                $am=$r['am_status']; $pm=$r['pm_status'];
                if ($am==='P' && $pm==='P') $present_full++;
                elseif ($am==='P' && $pm!=='P') $present_am_only++;
                elseif ($pm==='P' && $am!=='P') $present_pm_only++;
                else {
                    $absent_full++;
                    $u=$byId[$sidK]??null;
                    if($u){
                        $absent_today_list[]=[
                            'full_name'=>$this->fmtName($u['last'],$u['first'],$u['middle']),
                            'LRN'=>$u['LRN']??'',
                        ];
                    }
                }
                if ($r['remarks']==='TARDY') $tardy_today++;
            }

            $d = new DateTime($date); $dow=(int)$d->format('N'); $monday=(clone $d)->modify('-'.($dow-1).' days');
            $labels=['Mon','Tue','Wed','Thu','Fri']; $weekly=[];
            for($i=0;$i<5;$i++){
                $ymd=(clone $monday)->modify("+$i days")->format('Y-m-d');
                $p = $perDay[$ymd]['present'] ?? 0;
                $rate = ($totalStudents>0) ? round(($p/$totalStudents)*100,1) : 0.0;
                $weekly[]=['date'=>$ymd,'label'=>$labels[$i],'rate'=>$rate];
            }

            $weekdays = $this->weekdaysBetween($from,$to);
            $presentTotal=0; foreach($perDay as $ymd=>$agg){ $presentTotal += (int)$agg['present']; }
            $presentRate = ($weekdays>0 && $totalStudents>0) ? ($presentTotal/($weekdays*$totalStudents)) : 0;

            [$mFrom,$mTo] = $this->monthRange($date);
            $mRows = $this->many(
                "SELECT attendance_date, UPPER(COALESCE(am_status,'A')) AS am_status, UPPER(COALESCE(pm_status,'A')) AS pm_status
                   FROM student_attendance
                  WHERE section_id=? AND curriculum_id=? AND attendance_date BETWEEN ? AND ?",
                [$sid,$cid,$mFrom,$mTo]
            );
            $mPresent=0; $mDays=$this->weekdaysBetween($mFrom,$mTo); $byDayP=[];
            foreach($mRows as $r){ $ymd=$r['attendance_date']; if(!isset($byDayP[$ymd]))$byDayP[$ymd]=0; if($r['am_status']==='P'||$r['pm_status']==='P') $byDayP[$ymd]++; }
            foreach($byDayP as $ymd=>$pc){ $mPresent += $pc; }
            $mDenom = max(0, $mDays*$totalStudents); $mAbsent = max(0, $mDenom - $mPresent);

            $leaders_present=[]; foreach($presentByStudent as $sidK=>$cnt){ $u=$byId[$sidK]??null; if(!$u) continue;
                $leaders_present[]=['full_name'=>$this->fmtName($u['last'],$u['first'],$u['middle']),'LRN'=>$u['LRN']??'','count'=>(int)$cnt];
            }
            usort($leaders_present,function($a,$b){ return $b['count']<=>$a['count']; }); $leaders_present=array_slice($leaders_present,0,10);

            $daysRange=$this->weekdaysBetween($from,$to);
            $low_attendance=[]; foreach($byId as $sidK=>$u){
                $p=(int)($presentByStudent[$sidK]??0); $rate=($daysRange>0)? $p/$daysRange : 0;
                $low_attendance[]=['full_name'=>$this->fmtName($u['last'],$u['first'],$u['middle']),'LRN'=>$u['LRN']??'','rate'=>$rate];
            }
            usort($low_attendance,function($a,$b){ return $a['rate']<=>$b['rate']; }); $low_attendance=array_slice($low_attendance,0,10);

            $syRow = $this->many("SELECT school_year FROM curriculum WHERE id=? LIMIT 1", [$cid]);
            $school_year = $syRow ? ($syRow[0]['school_year'] ?? '') : '';

            echo json_encode([
                'status'=>true,
                'section_id'=>$sid,
                'curriculum_id'=>$cid,
                'curricula'=>$curricula,
                'from'=>$from,'to'=>$to,'date'=>$date,
                'school_year'=>$school_year,
                // meta for absent card
                'grade_name'=>$grade_name,
                'section_name'=>$section_name,

                'total_students'=>$totalStudents,
                'today'=>[
                    'present_full'=>$present_full,
                    'present_am_only'=>$present_am_only,
                    'present_pm_only'=>$present_pm_only,
                    'absent_full'=>$absent_full,
                    'tardy'=>$tardy_today,
                    'recorded'=>count($todayRows),
                    'unrecorded'=>max(0,$totalStudents - count($todayRows))
                ],
                'weekly'=>$weekly,
                'monthly'=>['present'=>$mPresent,'absent'=>$mAbsent],
                'overall_present_rate'=>$presentRate,
                'leaders_present'=>$leaders_present,
                'low_attendance'=>$low_attendance,
                'absent_today_list'=>$absent_today_list
            ], JSON_UNESCAPED_UNICODE|JSON_PARTIAL_OUTPUT_ON_ERROR);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['status'=>false,'message'=>'Server error','debug'=>$e->getMessage() ]);
        }
        exit;
    }

    public function js(){  return [$this->view.'/js/custom.js']; }
    public function css(){ return []; } // CSS is inline in the view
}
