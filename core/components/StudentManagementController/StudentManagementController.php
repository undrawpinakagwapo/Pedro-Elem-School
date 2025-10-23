<?php
require_once __DIR__ . '/../../vendor/autoload.php'; // ✅ load Composer autoloader

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

require_once __DIR__ . '/AfterSubmitTrait.php'; // pulls in public function afterSubmit()

class StudentManagementController {
    use AfterSubmitTrait;

    protected $db;
    protected $view;

    public function __construct($db) {
        $this->db = $db;
        $this->view = "StudentManagementController";
    }

    public function modalimport(){
        $data = getRequestAll();
        extract($data);

        $d["details"] = false;

        $res = [
            'header'=> "Import Data Students",
            "html" => loadView('components/'.$this->view.'/views/modal_import', $d),
            'button' => '<button class="btn btn-primary" type="submit">Submit form</button>',
            'action' => 'afterSubmit'
        ];

        echo json_encode($res);
    }

    /** Normalize any header text into a compact key for matching. */
    private function normalizeHeader($text) {
        $s = mb_strtolower((string)$text, 'UTF-8');
        $s = preg_replace('/[^a-z0-9]+/u', '', $s);
        return $s ?? '';
    }

    /** Map normalized header keys to DB fields (kept for modal/manual forms). */
    private function headerToDbField($normKey) {
        $map = [
            'barangay'                                     => 'barangay',
            'province'                                     => 'province',
            'guardian'                                     => 'guardian',
            'relationship'                                 => 'relationship',
            'learningmodality'                             => 'learning_modality',
            'remarks'                                      => 'remarks',
            'contactnumberofparentorguardian'              => 'contact_no_of_parent',
            'contactnoofparentorguardian'                  => 'contact_no_of_parent',
            'contactnumberofparent'                        => 'contact_no_of_parent',
            'contactno'                                    => 'contact_no_of_parent',
            'municipalitycity'                             => 'municipality_city',
            'municipality'                                 => 'municipality_city',
            'city'                                         => 'municipality_city',
            'fathersname'                                  => 'father_name',
            'fathersnamelastnamefirstnamemiddlename'       => 'father_name',
            'mothersmaidenname'                            => 'mother_name',
            'mothersmaidennamelastnamefirstnamemiddlename' => 'mother_name',

            // NEW HEADERS
            'mothertongue'                                 => 'mother_tongue',
            'religion'                                     => 'religion',
            'house'                                        => 'house_street_sitio_purok',
            'housestreetsitiopurok'                        => 'house_street_sitio_purok',
            'house#streetsitiopurok'                       => 'house_street_sitio_purok',

            // optional
            'batch'                                        => 'batch',
            'set'                                          => 'set_group',
            'gradelevel'                                   => 'grade_level', // ✅ added optional map
        ];

        if (isset($map[$normKey])) return $map[$normKey];

        if (strpos($normKey, 'municipality') !== false && strpos($normKey, 'city') !== false) return 'municipality_city';
        if (strpos($normKey, 'fathersname') !== false) return 'father_name';
        if (strpos($normKey, 'mothersmaidenname') !== false) return 'mother_name';
        if (strpos($normKey, 'learning') !== false && strpos($normKey, 'modality') !== false) return 'learning_modality';
        if (strpos($normKey, 'contact') !== false && (strpos($normKey, 'parent') !== false || strpos($normKey, 'guardian') !== false)) return 'contact_no_of_parent';

        return null;
    }

    /** Normalize a DOB value to 'Y-m-d'. */
    private function normalizeDobValue($raw) {
        if ($raw === null) return null;
        if (!is_numeric($raw)) {
            $raw = trim((string)$raw);
            if ($raw === '') return null;
        }

        $isValidParse = function ($dt): bool {
            if (!$dt instanceof \DateTime) return false;
            $errors = \DateTime::getLastErrors();
            if ($errors === false) return true;
            $warn = $errors['warning_count'] ?? 0;
            $err  = $errors['error_count']   ?? 0;
            return ($warn === 0 && $err === 0);
        };

        // (1) MDY formats
        $mdyFormats = [
            'm-d-Y','m/d/Y','m.d.Y',
            'M-d-Y','M/d-Y','M d Y',
            'F-d-Y','F/d-Y','F d Y',
            'M d, Y','F d, Y'
        ];
        foreach ($mdyFormats as $fmt) {
            $dt = \DateTime::createFromFormat($fmt, (string)$raw);
            if ($isValidParse($dt)) return $dt->format('Y-m-d');
        }

        // (2) Excel serials
        if (is_numeric($raw)) {
            $n = (float)$raw;
            if ($n > 20000 && $n < 100000) {
                try {
                    $dt = ExcelDate::excelToDateTimeObject($n);
                    if ($dt instanceof \DateTimeInterface) {
                        return $dt->format('Y-m-d');
                    }
                } catch (\Throwable $e) { /* ignore */ }
            }
        }

        // (3) Fallbacks
        $fallbacks = ['Y-m-d','d-m-Y','d/m/Y','d.m.Y','d M Y','d-M-Y'];
        foreach ($fallbacks as $fmt) {
            $dt = \DateTime::createFromFormat($fmt, (string)$raw);
            if ($isValidParse($dt)) return $dt->format('Y-m-d');
        }

        // (4) strtotime
        $ts = strtotime((string)$raw);
        if ($ts !== false) return date('Y-m-d', $ts);

        return null;
    }

    /** Normalize gender to "Male"/"Female" (null if unknown). */
    private function normalizeGenderValue($raw) {
        $s = strtoupper(trim((string)$raw));
        if ($s === '' || $s === '0') return null;
        if (in_array($s, ['1','M','MALE','BOY'], true))   return 'Male';
        if (in_array($s, ['2','F','FEMALE','GIRL'], true)) return 'Female';
        if (preg_match('/^[A-Z]+$/', $s)) return ucfirst(strtolower($s));
        return null;
    }

    /** LRN must be exactly 12 digits. */
    private function isValidLrn($value): bool {
        $s = preg_replace('/\D+/', '', (string)$value); // keep digits only
        return (bool)preg_match('/^\d{12}$/', $s);
    }

    /**
     * EXCEL IMPORT for your fixed template:
     * - Student rows: A7/C7/G7/H7/J7/L7/O7/P7/R7/U7/W7/AB7/AF7/AK7/AO7/AP7/AR7 (downwards)
     * - Remarks: A57 downward, aligned by index (row 7 -> 57)
     * - Only include rows where LRN is exactly 12 digits.
     * - NEW: School Year from T4, Grade Level from AE4, Section from AM4 (combined into set_group)
     */
    public function upload_excel(){
        if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] !== 0) {
            echo '<div class="alert alert-warning">No file uploaded or file error.</div>';
            return;
        }

        $file = $_FILES['excel_file']['tmp_name'];

        try {
            $reader = IOFactory::createReaderForFile($file);
            if (method_exists($reader, 'setReadDataOnly')) $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($file);
            $sheet = $spreadsheet->getActiveSheet();

            // NEW: read meta from fixed cells
            $batch      = trim((string)$sheet->getCell('T4')->getValue());   // School Year
            $gradeLevel = trim((string)$sheet->getCell('AE4')->getValue());  // Grade Level
            $section    = trim((string)$sheet->getCell('AM4')->getValue());  // Section
            $set_group  = trim($gradeLevel . ' ' . $section);

            // Optional banner showing detected meta
            echo '<div class="alert alert-info mb-3">'
               . '<strong>Detected:</strong> School Year: <em>' . htmlspecialchars($batch) . '</em>'
               . ' | Grade Level: <em>' . htmlspecialchars($gradeLevel) . '</em>'
               . ' | Section: <em>' . htmlspecialchars($section) . '</em>'
               . '</div>';

            // Fixed positions
            $startRowData   = 7;   // first student row (5 & 6 are headers/aux)
            $startRowRemark = 57;  // first remarks row
            $remarksCol     = 'A';

            // Columns map in display order
            $colMap = [
                'A'  => 'lrn',
                'C'  => 'full_name',
                'G'  => 'gender',
                'H'  => 'dateof_birth',
                'J'  => 'age', // preview only
                'L'  => 'mother_tongue',
                'O'  => 'religion',
                'P'  => 'house_street_sitio_purok',
                'R'  => 'barangay',
                'U'  => 'municipality_city',
                'W'  => 'province',
                'AB' => 'father_name',
                'AF' => 'mother_name',
                'AK' => 'guardian',
                'AO' => 'relationship',
                'AP' => 'contact_no_of_parent',
                'AR' => 'learning_modality',
            ];

            // Find last data row by scanning A (LRN) downward
            $maxRow = $sheet->getHighestRow();
            $r = $startRowData;
            $emptyStreak = 0;
            $maxScanEmpties = 10;
            $lastDataRow = $startRowData - 1;

            while ($r <= $maxRow && $emptyStreak < $maxScanEmpties) {
                $cellVal = $sheet->getCell("A{$r}")->getValue();
                $lrnTrim = trim((string)$cellVal);
                if ($lrnTrim === '') {
                    $emptyStreak++;
                } else {
                    $emptyStreak = 0;
                    $lastDataRow = $r;
                }
                $r++;
            }

            if ($lastDataRow < $startRowData) {
                echo '<div class="alert alert-warning">No student data found starting at A7.</div>';
                return;
            }

            // ---- Render preview table (only valid LRNs) ----
            echo '<div class="card">';
            echo '<div class="card-header bg-secondary text-white">Excel Preview</div>';
            echo '<div class="card-body">';
            echo '<div class="table-responsive">';
            echo '<table class="table table-bordered table-striped">';

            // Header
            echo '<tr>';
            echo '<th>Student Status</th>';
            $labels = [
                'lrn'                     => 'LRN',
                'full_name'               => 'Student Name',
                'gender'                  => 'Gender',
                'dateof_birth'            => 'Birth Date',
                'age'                     => 'Age',
                'mother_tongue'           => 'Mother Tongue',
                'religion'                => 'Religion',
                'house_street_sitio_purok'=> 'House #/Street/Sitio/Purok',
                'barangay'                => 'Barangay',
                'municipality_city'       => 'Municipality/City',
                'province'                => 'Province',
                'father_name'             => "Father's Name",
                'mother_name'             => "Mother's Name",
                'guardian'                => 'Guardian',
                'relationship'            => 'Relationship',
                'contact_no_of_parent'    => 'Contact Number',
                'learning_modality'       => 'Learning Modality',
            ];
            foreach ($colMap as $col => $field) {
                echo '<th>'.htmlspecialchars($labels[$field] ?? $field).'</th>';
            }
            // ✅ Added Grade Level column in preview
            echo '<th>School Year</th><th>Grade Level</th><th>Grade & Section</th><th>Remarks</th>';
            echo '</tr>';

            // Rows (only include if LRN is exactly 12 digits)
            $validCount = 0;
            for ($row = $startRowData; $row <= $lastDataRow; $row++) {

                $raw = [];
                foreach ($colMap as $col => $field) {
                    $raw[$field] = $sheet->getCell("{$col}{$row}")->getValue();
                }

                $lrnRaw = $raw['lrn'] ?? '';
                $lrn    = trim((string)$lrnRaw);

                // Skip if LRN is not exactly 12 digits
                if (!$this->isValidLrn($lrn)) {
                    continue;
                }

                $full_name  = trim((string)($raw['full_name'] ?? ''));
                $gender     = $this->normalizeGenderValue($raw['gender'] ?? '');
                $dob        = $this->normalizeDobValue($raw['dateof_birth'] ?? '');
                $agePreview = trim((string)($raw['age'] ?? ''));

                // Extras
                $extraValues = [
                    'mother_tongue'            => trim((string)($raw['mother_tongue'] ?? '')),
                    'religion'                 => trim((string)($raw['religion'] ?? '')),
                    'house_street_sitio_purok' => trim((string)($raw['house_street_sitio_purok'] ?? '')),
                    'barangay'                 => trim((string)($raw['barangay'] ?? '')),
                    'municipality_city'        => trim((string)($raw['municipality_city'] ?? '')),
                    'province'                 => trim((string)($raw['province'] ?? '')),
                    'father_name'              => trim((string)($raw['father_name'] ?? '')),
                    'mother_name'              => trim((string)($raw['mother_name'] ?? '')),
                    'guardian'                 => trim((string)($raw['guardian'] ?? '')),
                    'relationship'             => trim((string)($raw['relationship'] ?? '')),
                    'contact_no_of_parent'     => trim((string)($raw['contact_no_of_parent'] ?? '')),
                    'learning_modality'        => trim((string)($raw['learning_modality'] ?? '')),
                ];

                // Remarks (A57 for row 7 => +50)
                $remarksRow = ($row - $startRowData) + $startRowRemark;
                $remarksVal = trim((string)$sheet->getCell("{$remarksCol}{$remarksRow}")->getValue());

                // Determine status
                $student_id = null;
                $student_status = 'NEW';
                $check = $this->db->Select("SELECT user_id FROM users WHERE deleted = 0 AND LRN = ?", [$lrn]);
                if (count($check) > 0) {
                    $student_id = $check[0]['user_id'];
                    $student_status = 'OLD';
                }

                // Hidden inputs for afterSubmit()
                $input  = '<input type="hidden" name="data['.$row.'][user_id]" value="'.htmlspecialchars((string)$student_id).'"/>';
                $input .= '<input type="hidden" name="data['.$row.'][lrn]" value="'.htmlspecialchars($lrn).'"/>';
                $input .= '<input type="hidden" name="data['.$row.'][full_name]" value="'.htmlspecialchars($full_name).'"/>';
                $input .= '<input type="hidden" name="data['.$row.'][gender]" value="'.htmlspecialchars((string)$gender).'"/>';
                $input .= '<input type="hidden" name="data['.$row.'][dateof_birth]" value="'.htmlspecialchars((string)$dob).'"/>';

                foreach ($extraValues as $k => $v) {
                    $input .= '<input type="hidden" name="data['.$row.']['.$k.']" value="'.htmlspecialchars($v).'"/>';
                }
                $input .= '<input type="hidden" name="data['.$row.'][remarks]" value="'.htmlspecialchars($remarksVal).'"/>';

                // Auto-detected School Year, Grade Level & Set (Grade + Section)
                $input .= '<input type="hidden" name="data['.$row.'][batch]" value="'.htmlspecialchars($batch).'"/>';
                $input .= '<input type="hidden" name="data['.$row.'][grade_level]" value="'.htmlspecialchars($gradeLevel).'"/>';
                $input .= '<input type="hidden" name="data['.$row.'][set_group]" value="'.htmlspecialchars($set_group).'"/>';

                // Render table row
                $rowClass = ($student_id) ? 'class="text-danger"' : '';
                echo '<tr '.$rowClass.'>';
                echo '<td>'.$student_status.$input.'</td>';

                foreach ($colMap as $col => $field) {
                    if ($field === 'gender') {
                        echo '<td>'.htmlspecialchars((string)$gender).'</td>';
                    } elseif ($field === 'dateof_birth') {
                        echo '<td>'.htmlspecialchars((string)($dob ?? '')).'</td>';
                    } elseif ($field === 'age') {
                        echo '<td>'.htmlspecialchars($agePreview).'</td>';
                    } else {
                        echo '<td>'.htmlspecialchars((string)($raw[$field] ?? '')).'</td>';
                    }
                }

                // ✅ show School Year, Grade Level, Grade & Section, then Remarks
                echo '<td>'.htmlspecialchars($batch).'</td>';
                echo '<td>'.htmlspecialchars($gradeLevel).'</td>';
                echo '<td>'.htmlspecialchars($set_group).'</td>';
                echo '<td>'.htmlspecialchars($remarksVal).'</td>';
                echo '</tr>';

                $validCount++;
            }

            if ($validCount === 0) {
                echo '</table></div></div></div>';
                echo '<div class="alert alert-info mt-2">No valid rows to import. LRN must be exactly 12 digits.</div>';
                return;
            }

            echo '</table></div></div></div>';

        } catch (\Throwable $e) {
            echo '<div class="alert alert-danger">Invalid Excel File: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    }

    public function list(){
        $data["list"] = $this->db->Select("select * from grade_level where deleted = 0", array());
        echo json_encode($data["list"]);
    }

    public function index() {
        $data = [];

        // MAIN LIST
        $data["list"] = $this->db->Select(
            "select 
                CONCAT(u.account_last_name, ', ', u.account_first_name  , ', ', u.account_middle_name) as full_name,
                u.*
             FROM users u 
             where u.user_type = 5 and u.deleted = 0 
             order by u.account_last_name",
            array()
        );

        // Distinct batches & sets for filters
        $data["batches"] = $this->db->Select("SELECT DISTINCT batch FROM users WHERE deleted = 0 AND user_type = 5 AND batch IS NOT NULL AND batch <> '' ORDER BY batch DESC", []);
        $data["sets"]    = $this->db->Select("SELECT DISTINCT set_group AS set_group FROM users WHERE deleted = 0 AND user_type = 5 AND set_group IS NOT NULL AND set_group <> '' ORDER BY set_group ASC", []);

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

        if (isset($action) && $action == "edit" && !empty($id)) {
            $result = $this->db->Select(
                "select u.* FROM users u where u.user_id = ?",
                array($id)
            );
            if ($result) { $d["details"] = $result[0]; }
        }

        // Supply dropdown options to the modal
        $d["batches"] = $this->db->Select("SELECT DISTINCT batch FROM users WHERE deleted = 0 AND user_type = 5 AND batch IS NOT NULL AND batch <> '' ORDER BY batch DESC", []);
        $d["sets"]    = $this->db->Select("SELECT DISTINCT set_group AS set_group FROM users WHERE deleted = 0 AND user_type = 5 AND set_group IS NOT NULL AND set_group <> '' ORDER BY set_group ASC", []);

        $res = [
            'header'=> (isset($action) && $action == "add") ? "" : 'Edit',
            "html" => loadView('components/'.$this->view.'/views/modal_details', $d),
            'button' => '<button class="btn btn-primary" type="submit">Submit form</button>',
            'action' => 'afterSubmit'
        ];

        echo json_encode($res);
    }

    public function delete(){
    $data = getRequestAll();
    extract($data);

    // ✅ Corrected to update the 'users' table
    $this->db->Update("UPDATE users SET deleted = 1 WHERE user_id = ?", [$id]);

    $res = [
        'status'=> true,
        'msg' => 'Successfully deleted!'
    ];
    echo json_encode($res);
}
}
