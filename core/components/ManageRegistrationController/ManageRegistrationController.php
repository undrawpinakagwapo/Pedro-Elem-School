<?php
use PhpOffice\PhpSpreadsheet\IOFactory; // (kept if other parts use PhpSpreadsheet elsewhere; safe to remove if unused)

class ManageRegistrationController
{
    protected $db;
    protected $view;

    public function __construct($db) {
        $this->db  = $db;
        $this->view = "ManageRegistrationController";
    }

    /* ---------------------------- helpers ---------------------------- */
    private function selectOne($sql, $params = []) {
        $rows = $this->db->Select($sql, $params);
        return ($rows && isset($rows[0])) ? $rows[0] : null;
    }
    private function pdoBegin()    { if (isset($this->db->pdo)) { $this->db->pdo->beginTransaction(); } }
    private function pdoCommit()   { if (isset($this->db->pdo) && $this->db->pdo->inTransaction()) { $this->db->pdo->commit(); } }
    private function pdoRollback() { if (isset($this->db->pdo) && $this->db->pdo->inTransaction()) { $this->db->pdo->rollBack(); } }

    /* -------------------------- UI endpoints ------------------------- */

    /**
     * Import modal entry point (kept for compatibility).
     * If you open imports from StudentManagement, you can ignore this.
     */
    public function modalimport() {
        $d["details"] = false;
        $res = [
            'header' => "Import Data Students",
            "html"   => loadView('components/'.$this->view.'/views/modal_import', $d),
            'button' => '<button class="btn btn-primary" type="submit">Submit form</button>',
            'action' => 'afterSubmit'
        ];
        echo json_encode($res);
    }

    /** List grade levels (unchanged) */
    public function list() {
        $data["list"] = $this->db->Select("SELECT * FROM grade_level WHERE deleted = 0", []);
        echo json_encode($data["list"]);
    }

    /** Main index: show enrolled students summary (unchanged) */
    public function index() {
        $sql = "SELECT rs.id, rs.created_at,
                       CONCAT(u.account_first_name, ' ', u.account_middle_name, ' ', u.account_last_name) AS full_name,
                       u.LRN,
                       c.school_year,
                       CONCAT('Grade ', gl.`name`, ' - ', s.`name`) AS section_level,
                       u.user_id
                  FROM registrar_student rs
                  JOIN curriculum c ON c.id = rs.curriculum_id
                  JOIN users u      ON u.user_id = rs.student_id
                  JOIN section s    ON s.id = c.grade_id
             LEFT JOIN grade_level gl ON gl.id = s.grade_id";
        $data["list"] = $this->db->Select($sql, []);
        return ["content" => loadView('components/'.$this->view.'/views/custom', $data)];
    }

    public function js()  { return [$this->view.'/js/custom.js']; }
    public function css() { return []; }

    /**
     * Build modal content for Add/Edit registration.
     * Supplies:
     *  - Sections (grade/section)
     *  - Distinct batches & sets (populated by StudentManagement import + AfterSubmitTrait)
     *  - Initial student list (with batch/set hints)
     */
    public function source() {
        $data = getRequestAll();
        extract($data);

        $d["details"] = false;

        // Left selects (Grade/Section)
        $d["section"] = $this->db->Select(
            "SELECT s.id, CONCAT(gl.`name`, ' - ', s.`name`) AS `name`
               FROM section s
               JOIN grade_level gl ON gl.id = s.grade_id
              WHERE s.deleted = 0
              ORDER BY gl.`name`, s.`name`", []
        );

        // Filters: batches & sets for students
        $d["batches"] = $this->db->Select(
            "SELECT DISTINCT batch
               FROM users
              WHERE deleted = 0 AND user_type = 5 AND batch IS NOT NULL AND batch <> ''
              ORDER BY batch DESC", []
        );
        $d["sets"] = $this->db->Select(
            "SELECT DISTINCT set_group
               FROM users
              WHERE deleted = 0 AND user_type = 5 AND set_group IS NOT NULL AND set_group <> ''
              ORDER BY set_group ASC", []
        );

        // Initial student list includes batch & set_group (for UI hint)
        $d["studentlist"] = $this->db->Select(
            "SELECT 
                CONCAT(u.LRN,'-', u.account_last_name, ' ', u.account_first_name, ' ', u.account_middle_name) AS `name`,
                u.user_id AS id,
                u.batch,
                u.set_group
             FROM users u
             WHERE u.user_type = 5 
               AND u.deleted = 0
             ORDER BY u.account_last_name, u.account_first_name", []
        );

        $details_student_id = '';
        if (($action ?? '') === "edit" && !empty($id) && $id !== 'undefined') {
            $result = $this->selectOne(
                "SELECT rs.*, c.grade_id AS csection_id
                   FROM registrar_student rs
                   JOIN curriculum c ON c.id = rs.curriculum_id
                  WHERE rs.id = ?",
                [$id]
            );
            $d["details"] = $result;
            $details_student_id = isset($result['student_id']) ? $result['student_id'] : '';
        }

        $res = [
            'header'              => (isset($action) && $action == "add") ? "Add" : 'Edit',
            "html"                => loadView('components/'.$this->view.'/views/modal_details', $d),
            'button'              => '<button class="btn btn-primary" type="submit">Submit form</button>',
            'action'              => 'afterSubmit',
            'curriculum_id'       => isset($d["details"]["curriculum_id"]) ? $d["details"]["curriculum_id"] : '',
            'details_student_id'  => $details_student_id
        ];
        echo json_encode($res);
    }

    /**
     * Handles manual add (single/multiple from modal).
     * Bulk import is handled by StudentManagementController + AfterSubmitTrait.
     * registrar_student.student_id → users.user_id (FK)
     */
    public function afterSubmit() {
        $request = getRequestAll();
        extract($request);

        $tableEnroll  = "registrar_student";

        if (!isset($manualadd)) {
            // If this endpoint is hit without manualadd flag, just ignore gracefully.
            header('Location: index?type=success&message='.urlencode('No changes made.'));
            exit();
        }

        $this->pdoBegin();
        try {
            // Accept either scalar or array
            $studentIds = [];
            if (isset($request["student_id"])) {
                if (is_array($request["student_id"])) {
                    $studentIds = array_filter(array_map('intval', $request["student_id"]));
                } else {
                    $studentIds = [(int)$request["student_id"]];
                }
            }
            $curriculumId = isset($request["curriculum_id"]) ? (int)$request["curriculum_id"] : 0;
            $sectionId    = isset($request["section_id"]) ? (int)$request["section_id"] : null;

            if ($curriculumId <= 0) {
                throw new \Exception("Please choose a valid curriculum.");
            }
            if (empty($studentIds)) {
                throw new \Exception("Please select at least one student.");
            }

            // Deduplicate
            $studentIds = array_values(array_unique($studentIds));

            foreach ($studentIds as $studentId) {
                // Validate user exists and is a student
                $u = $this->selectOne("SELECT user_id FROM users WHERE user_id = ? AND deleted = 0 AND user_type = 5", [$studentId]);
                if (!$u) {
                    // Skip invalid entries silently
                    continue;
                }

                // Prevent duplicate enrollment
                $dup = $this->selectOne(
                    "SELECT id FROM registrar_student
                      WHERE curriculum_id = ? AND student_id = ? AND deleted = 0",
                    [$curriculumId, $studentId]
                );

                if (!$dup) {
                    $row = [
                        'curriculum_id' => $curriculumId,
                        'student_id'    => $studentId,  // FK → users.user_id
                        'section_id'    => $sectionId,
                    ];
                    $this->db->insertRequestBatchRquest($row, $tableEnroll);
                }
            }

            $this->pdoCommit();
            header('Location: index?type=success&message='.urlencode('Successfully Added!'));
            exit();
        } catch (\Throwable $e) {
            $this->pdoRollback();
            header('Location: index?type=danger&message='.urlencode($e->getMessage()));
            exit();
        }
    }

    /** Soft-delete grade_level (unchanged) */
    public function delete() {
        $data = getRequestAll();
        extract($data);

        $this->db->Update("UPDATE grade_level SET deleted = 1 WHERE id = ?", [$id]);

        $res = ['status'=> true, 'msg' => 'Successfully deleted!'];
        echo json_encode($res);
    }

    /**
     * AJAX: returns HTML fragments for dependent UI pieces.
     *   - type=section      val=<section_id>     → curricula options
     *   - type=curriculum   val=<curriculum_id>  → subjects table
     *   - type=students     batch=<...>&set_group=<...>&curriculum_id=<...> → filtered student list
     *   - type=setsByBatch  val=<batch>          → <option> list for Set select (optional helper)
     */
    public function getDetailsSource() {
        $data = getRequestAll();

        $type          = $data['type']         ?? '';
        $val           = $data['val']          ?? '';
        $batch         = trim((string)($data['batch']        ?? ''));
        $set_group     = trim((string)($data['set_group']    ?? ''));
        $curriculum_id = (int)($data['curriculum_id'] ?? 0);

        $html = '';

        if ($type === 'section' && $val !== '') {
            // Load curricula for a Section (grade level)
            $d["list"] = $this->db->Select(
                "SELECT c.id, c.school_year AS `name`
                   FROM curriculum c
                   JOIN section s ON s.id = c.grade_id
                   JOIN grade_level gl ON gl.id = s.grade_id
                  WHERE c.deleted = 0 AND c.grade_id = ?
                  ORDER BY c.school_year DESC",
                [$val]
            );
            $html = loadView('components/'.$this->view.'/views/section', $d);

        } elseif ($type === 'curriculum' && $val !== '') {
            // Load curriculum subjects for preview
            $d["list"] = $this->db->Select(
                "SELECT c.id, c.subject_id, s.`code`, s.`name`
                   FROM curriculum_child c
                   JOIN subjects s ON s.id = c.subject_id
                  WHERE c.curriculum_id = ? AND c.deleted = 0
                  ORDER BY s.code, s.name",
                [$val]
            );
            $html = loadView('components/'.$this->view.'/views/curriculum', $d);

        } elseif ($type === 'students') {
            /**
             * Filter students by Batch & Set (both optional).
             * If curriculum_id is provided (>0), EXCLUDE students that are already
             * registered on registrar_student for that curriculum (deleted = 0).
             * This ensures “missing” students from import now appear correctly.
             */
            $params = [];
            $conds  = ["u.user_type = 5", "u.deleted = 0"];

            if ($batch !== '') {
                $conds[] = "u.batch = ?";
                $params[] = $batch;
            }
            if ($set_group !== '') {
                $conds[] = "u.set_group = ?";
                $params[] = $set_group;
            }

            // Base SQL includes batch & set_group for UI hints
            $sql = "
                SELECT 
                    CONCAT(u.LRN,'-', u.account_last_name, ' ', u.account_first_name, ' ', u.account_middle_name) AS `name`,
                    u.user_id AS id,
                    u.batch,
                    u.set_group
                  FROM users u
                 WHERE ".implode(' AND ', $conds)."
            ";

            // Exclude already-registered in this curriculum (if provided)
            if ($curriculum_id > 0) {
                $sql .= " AND NOT EXISTS (
                            SELECT 1
                              FROM registrar_student rs
                             WHERE rs.deleted = 0
                               AND rs.curriculum_id = ?
                               AND rs.student_id = u.user_id
                          )";
                $params[] = $curriculum_id;
            }

            $sql .= " ORDER BY u.account_last_name, u.account_first_name";

            $rows = $this->db->Select($sql, $params);

            // Return the label list expected by the modal (to drop into #student_checkbox_list)
            ob_start();
            foreach ($rows as $s) {
                $batchHint = (string)($s['batch'] ?? '');
                $setHint   = (string)($s['set_group'] ?? '');
                echo '<label class="student-item d-flex align-items-center">';
                echo '  <input type="checkbox" name="student_id[]" class="student-check form-check-input me-2" value="'.htmlspecialchars((string)$s['id']).'">';
                echo '  <span class="student-name text-truncate">'.htmlspecialchars($s['name']);
                if ($batchHint !== '' || $setHint !== '') {
                    $hint = trim($batchHint.($batchHint && $setHint ? ' • ' : '').$setHint);
                    echo ' <small class="text-muted"> — '.htmlspecialchars($hint).'</small>';
                }
                echo '</span>';
                echo '</label>';
            }
            $html = ob_get_clean();

        } elseif ($type === 'setsByBatch') {
            // Helper: populate Set select when a Batch is chosen
            $opts = $this->db->Select(
                "SELECT DISTINCT set_group
                   FROM users
                  WHERE deleted = 0
                    AND user_type = 5
                    AND set_group IS NOT NULL AND set_group <> ''
                    AND batch = ?
                  ORDER BY set_group ASC",
                [$val]
            );

            ob_start();
            echo '<option value="">— Select Set —</option>';
            foreach ($opts as $row) {
                $sg = (string)$row['set_group'];
                echo '<option value="'.htmlspecialchars($sg).'">'.htmlspecialchars($sg).'</option>';
            }
            $html = ob_get_clean();
        }

        echo json_encode(['content' => $html]);
    }
}
