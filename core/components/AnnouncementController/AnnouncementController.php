<?php

class AnnouncementController {
    protected $db;
    protected $view;

    public function __construct($db) {
        $this->db   = $db;
        // MATCH THE FOLDER NAME under core/components/<THIS>/...
        $this->view = "AnnouncementController";
    }

    /* ---------------- Session & Roles ---------------- */
    protected function ensureSession() {
        if (session_status() !== PHP_SESSION_ACTIVE) { @session_start(); }
    }
    protected function currentRole(): int {
        $this->ensureSession();
        return (int)($_SESSION['user_type'] ?? 0);
    }
    protected function hasRoleConstants(): bool {
        return defined('ROLE_ADMIN') && defined('ROLE_PRINCIPAL') && defined('ROLE_TEACHER') && defined('ROLE_STUDENT');
    }
    protected function isManager(): bool {
        if (!$this->hasRoleConstants()) return true; // allow during setup
        $r = $this->currentRole();
        return ($r === ROLE_ADMIN || $r === ROLE_PRINCIPAL);
    }

    /* ---------------- Helpers ---------------- */
    protected function normalizeDate(?string $val): ?string {
        if (!$val) return null;
        $val = str_replace('T', ' ', trim($val));
        $ts  = strtotime($val);
        return $ts ? date('Y-m-d H:i:s', $ts) : null;
    }

    protected function buildAudienceWhereForFeed(array &$params): string {
        $where = " WHERE deleted = 0 AND status = 1 ";
        if ($this->hasRoleConstants()) {
            $role = $this->currentRole();
            if ($role === ROLE_TEACHER) {
                $where .= " AND (audience_scope='all' OR audience_scope='teachers') ";
            } elseif ($role === ROLE_STUDENT) {
                $where .= " AND (audience_scope='all' OR audience_scope='students') ";
            }
        }
        return $where;
    }

    protected function buildAudienceWhereForManage(string $tab, array &$params): string {
        $where = " WHERE deleted = 0 ";
        if (in_array($tab, ['students','teachers'], true)) {
            $where .= " AND audience_scope = ? ";
            $params[] = $tab;
        }
        return $where;
    }

    protected function saveImagesFromFiles(array $files, string $folder): array {
        if (!isset($files['name']) || !is_array($files['name'])) return [];
        if (!is_dir($folder)) { @mkdir($folder, 0777, true); }
        $saved = [];
        $n = count($files['name']);
        for ($i=0; $i<$n; $i++) {
            if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;
            $tmp = $files['tmp_name'][$i];
            $ext = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
            $fn  = uniqid('annc_', true) . ($ext ? '.'.$ext : '');
            $dst = rtrim($folder,'/').'/'.$fn;
            if (@move_uploaded_file($tmp, $dst)) $saved[] = $dst; // store relative if your app needs it
        }
        return $saved;
    }

    protected function redirectWithFlash(bool $ok, string $message, string $to = 'index') {
        $type = $ok ? 'success' : 'danger';
        header('Location: '.$to.'?type='.$type.'&message='.urlencode($message));
        exit();
    }

    /* ---------------- Pages ---------------- */
    /** Admin/Principal manage page. Non-managers fall back to feed. */
    public function index() {
        if (!$this->isManager()) return $this->feed();

        $tab = $_GET['audience'] ?? 'all';
        $params = [];
        $where  = $this->buildAudienceWhereForManage($tab, $params);

        $list = $this->db->Select(
            "SELECT announcement_id, title, audience_scope, start_date, end_date, status, created_at
               FROM announcements
             $where
             ORDER BY created_at DESC",
            $params
        );

        $data = ['current_audience'=>$tab, 'list'=>$list ?: []];
        return ["content" => loadView('components/'.$this->view.'/views/manage_custom', $data)];
    }

    /** Role-filtered feed page. */
    public function feed() {
        $params = [];
        $where  = $this->buildAudienceWhereForFeed($params);

        $list = $this->db->Select(
            "SELECT announcement_id, title, body, audience_scope, start_date, end_date, status, created_at, image
               FROM announcements
             $where
             ORDER BY COALESCE(start_date, created_at) DESC",
            $params
        );

        $data = ['list'=>$list ?: []];
        return ["content" => loadView('components/'.$this->view.'/views/feed_list', $data)];
    }

    public function js()  { return [ $this->view.'/js/custom.js' ]; }
    public function css() { return []; }

    /* ---------------- Modal Source (HTML) ---------------- */
    public function source() {
        if (!$this->isManager()) {
            http_response_code(403);
            echo json_encode(['header'=>'Forbidden','html'=>'Not allowed','button'=>'','action'=>'noop']);
            return;
        }
        $req = getRequestAll();
        $action = $req['action'] ?? 'add';
        $id     = $req['id']     ?? null;

        $details = false;
        if (($action === 'edit' || $action === 'view') && !empty($id) && $id !== 'undefined') {
            $row = $this->db->Select("SELECT * FROM announcements WHERE announcement_id=? AND deleted=0", [$id]);
            $details = $row ? $row[0] : false;
        }

        // IMPORTANT: We put the submit button INSIDE the form now, so button='' here.
        $res = [
            'header'=> ($action === 'add') ? 'Add Announcement' : (($action === 'view') ? 'Announcement Details' : 'Edit Announcement'),
            'html'  => loadView('components/'.$this->view.'/views/modal_details', ['details'=>$details]),
            'button'=> '',
            'action'=> 'noop'
        ];
        header('Content-Type: application/json');
        echo json_encode($res);
    }

    /* ---------------- Create/Update (plain POST) ---------------- */
    public function afterSubmit() {
        if (!$this->isManager()) $this->redirectWithFlash(false, 'Forbidden.');

        $post   = getRequestAll();
        $folder = 'src/images/announcements/uploads/';

        // Required
        $title = trim($post['title'] ?? '');
        $body  = trim($post['body']  ?? '');
        if ($title === '' || $body === '') $this->redirectWithFlash(false, 'Title and Body are required.');

        // Normalize
        $aud = in_array(($post['audience_scope'] ?? 'all'), ['all','students','teachers'], true) ? $post['audience_scope'] : 'all';
        $start  = $this->normalizeDate($post['start_date'] ?? null);
        $end    = $this->normalizeDate($post['end_date']   ?? null);
        $status = isset($post['status']) ? (int)$post['status'] : 1;

        $this->ensureSession();
        $createdBy = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;

        // Files
        $images = [];
        if (!empty($_FILES['image'])) $images = $this->saveImagesFromFiles($_FILES['image'], $folder);
        $imagesStr = $images ? implode('|', $images) : null;

        $id = isset($post['announcement_id']) ? (int)$post['announcement_id'] : 0;

        try {
            if ($id > 0) {
                // UPDATE
                $cols = ['title','body','audience_scope','start_date','end_date','status','updated_at'];
                $vals = [$title, $body, $aud, $start, $end, $status, date('Y-m-d H:i:s')];
                if ($imagesStr !== null) { $cols[]='image'; $vals[]=$imagesStr; }
                $setParts = array_map(fn($c)=>"$c = ?", $cols);
                $vals[] = $id;

                $sql = "UPDATE announcements SET ".implode(', ',$setParts)." WHERE announcement_id = ?";
                $this->db->Update($sql, $vals);
                $this->redirectWithFlash(true, 'Successfully Updated!', 'index');
            } else {
                // INSERT
                $cols = ['title','body','audience_scope','start_date','end_date','status','image','created_by','created_at','deleted'];
                $vals = [$title,$body,$aud,$start,$end,$status,$imagesStr,$createdBy,date('Y-m-d H:i:s'),0];
                $ph   = implode(',', array_fill(0, count($cols), '?'));
                $sql  = "INSERT INTO announcements (".implode(',',$cols).") VALUES ($ph)";
                $this->db->Insert($sql, $vals);
                $this->redirectWithFlash(true, 'Successfully Created!', 'index');
            }
        } catch (\Throwable $e) {
            $this->redirectWithFlash(false, 'DB Error: '.$e->getMessage(), 'index');
        }
    }

    /* ---------------- Delete ---------------- */
    public function delete() {
        if (!$this->isManager()) {
            http_response_code(403);
            echo json_encode(['status'=>false, 'msg'=>'Forbidden']);
            return;
        }
        $req = getRequestAll();
        $id  = isset($req['id']) ? (int)$req['id'] : 0;
        if (!$id) { echo json_encode(['status'=>false, 'msg'=>'Missing id']); return; }
        $this->db->Update("UPDATE announcements SET deleted=1 WHERE announcement_id=?", [$id]);
        echo json_encode(['status'=>true, 'msg'=>'Successfully deleted!']);
    }
    public function latest() {
    header('Content-Type: application/json; charset=utf-8');

    // Reuse the audience filter (all/teachers/students) + active only
    $params = [];
    $where  = $this->buildAudienceWhereForFeed($params);

    // Limit: default 4, clamp 1..10 to be safe
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 4;
    if ($limit < 1)  $limit = 1;
    if ($limit > 10) $limit = 10;

    // Prefer start_date when present, then created_at
    $rows = $this->db->Select(
        "SELECT announcement_id, title, body, audience_scope, start_date, end_date, status, created_at
           FROM announcements
         $where
         ORDER BY COALESCE(start_date, created_at) DESC
         LIMIT $limit",
        $params
    );

    echo json_encode(['status'=>true, 'items'=>$rows ?: []], JSON_UNESCAPED_UNICODE);
    exit;
}

}
