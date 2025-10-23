<?php

class CurriculumnController
{
    protected $db;
    protected $view = 'CurriculumnController';

    public function __construct($db) {
        $this->db = $db;
    }

    /* ---------- tiny helpers ---------- */
    private function one($sql,$p=[]){ $r=$this->db->Select($sql,$p); return $r? $r[0]:null; }
    private function many($sql,$p=[]){ return $this->db->Select($sql,$p); }

    /* ---------- Page ---------- */
    public function index() {
        // List the curricula (grade & section name, adviser, school year, status)
        // NOTE: Added explicit aliases grade_name & section_name for clean client-side filtering
        $list = $this->db->Select(
            "SELECT 
                c.*,
                CONCAT(gl.name, ' - ', s.name) AS gs_name,
                gl.name AS grade_name,
                s.name  AS section_name,
                CONCAT(u.account_first_name, ' ', u.account_last_name) AS adviser_name
             FROM curriculum c
             JOIN section s      ON s.id = c.grade_id
             JOIN grade_level gl ON gl.id = s.grade_id
             JOIN users u        ON u.user_id = c.adviser_id
            WHERE c.deleted = 0
            ORDER BY gl.name, s.name, c.school_year DESC", []
        );

        return [
            'content' => loadView('components/'.$this->view.'/views/custom', ['list'=>$list]),
        ];
    }

    public function js()  { return [$this->view.'/js/custom.js']; }
    public function css() { return []; }

    /* ---------- AJAX: details for “View” ---------- */
    public function source(){
        header('Content-Type: application/json; charset=utf-8');
        try {
            $req = getRequestAll();
            $id  = (int)($req['id'] ?? 0);
            if (!$id) { echo json_encode(['status'=>false,'message'=>'Missing id']); return; }

            $row = $this->one(
                "SELECT 
                    c.*,
                    CONCAT(gl.name, ' - ', s.name)     AS gs_name,
                    gl.name AS grade_name,
                    s.name  AS section_name,
                    CONCAT(u.account_first_name, ' ', u.account_last_name) AS adviser_name
                 FROM curriculum c
                 JOIN section s      ON s.id = c.grade_id
                 JOIN grade_level gl ON gl.id = s.grade_id
                 JOIN users u        ON u.user_id = c.adviser_id
                WHERE c.deleted = 0 AND c.id = ? LIMIT 1", [$id]
            );
            if (!$row) { echo json_encode(['status'=>false,'message'=>'Curriculum not found']); return; }

            $subjects = $this->many(
                "SELECT c.id, c.subject_id, s.code, s.name
                   FROM curriculum_child c
                   JOIN subjects s ON s.id = c.subject_id
                  WHERE c.deleted = 0 AND c.curriculum_id = ?
               ORDER BY s.name", [$id]
            );

            // Build the details HTML (read-only)
            ob_start(); ?>
            <style>
              .cv-row{display:grid;grid-template-columns:180px 1fr;gap:12px;margin-bottom:10px}
              .cv-label{color:#64748b;font-weight:700}
              .cv-pill{display:inline-block;padding:3px 10px;border-radius:999px;background:#eff6ff;border:1px solid #bfdbfe;color:#1d4ed8;font-weight:800;font-size:.78rem}
              .cv-list{border:1px solid #eef2f7;border-radius:12px;padding:10px}
              .cv-item{display:flex;justify-content:space-between;gap:10px;padding:8px 10px;border-bottom:1px solid #f1f5f9}
              .cv-item:last-child{border-bottom:0}
              .cv-code{font-weight:800;color:#0f172a}
              .cv-name{color:#334155}
              .cv-status{display:inline-block;padding:3px 12px;border-radius:999px;font-size:.78rem;font-weight:900}
              .cv-status.on{background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46}
              .cv-status.off{background:#fee2e2;border:1px solid #fecaca;color:#991b1b}
            </style>

            <div>
              <div class="cv-row"><div class="cv-label">Grade &amp; Section</div><div><strong><?= htmlspecialchars($row['gs_name']) ?></strong></div></div>
              <div class="cv-row"><div class="cv-label">Curriculum</div><div><?= htmlspecialchars($row['name'] ?? '') ?></div></div>
              <div class="cv-row"><div class="cv-label">Adviser</div><div><?= htmlspecialchars($row['adviser_name'] ?? '—') ?></div></div>
              <div class="cv-row"><div class="cv-label">School Year</div><div><span class="cv-pill"><?= htmlspecialchars($row['school_year'] ?? '') ?></span></div></div>
              <div class="cv-row"><div class="cv-label">Status</div>
                <div>
                    <span class="cv-status <?= ((int)($row['status']??0)===1 ? 'on':'off') ?>">
                      <?= (int)($row['status']??0)===1 ? 'ACTIVE' : 'INACTIVE' ?>
                    </span>
                </div>
              </div>

              <div class="mt-3 mb-1" style="font-weight:900;color:#0f172a">Subjects</div>
              <div class="cv-list">
                <?php if (!empty($subjects)): foreach ($subjects as $s): ?>
                  <div class="cv-item">
                    <div class="cv-code"><?= htmlspecialchars($s['code']) ?></div>
                    <div class="cv-name"><?= htmlspecialchars($s['name']) ?></div>
                  </div>
                <?php endforeach; else: ?>
                  <div class="text-muted">No subjects linked.</div>
                <?php endif; ?>
              </div>
            </div>
            <?php
            $html = ob_get_clean();

            echo json_encode([
                'status' => true,
                'title'  => 'Curriculum Details',
                'html'   => $html,
                'footer' => '<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>',
            ]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['status'=>false,'message'=>'Server error']);
        }
    }
}
