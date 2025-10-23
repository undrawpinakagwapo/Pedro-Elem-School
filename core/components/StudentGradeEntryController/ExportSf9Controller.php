<?php
// components/StudentGradeEntryController/ExportSf9Controller.php

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class ExportSf9Controller
{
    protected $db;
    public function __construct($db) { $this->db = $db; }

    /* tiny DB helpers */
    private function one($sql,$p=[]){ $r=$this->db->Select($sql,$p); return $r? $r[0]:null; }
    private function many($sql,$p=[]){ return $this->db->Select($sql,$p); }

    /** Parse school year string like "2024-2025" -> [2024, 2025] */
    private function parseSY($sy){
        if (preg_match('/^\s*(\d{4})\s*[-–]\s*(\d{4})\s*$/', (string)$sy, $m)) {
            $a = (int)$m[1]; $b = (int)$m[2];
            if ($b === $a+1) return [$a,$b];
        }
        $y = (int)date('Y');
        return [$y, $y+1];
    }

    /** Get distinct Mon–Fri attendance dates for class in month, excluding holidays */
    private function schoolDatesForMonth(int $sectionId, int $curriculumId, int $year, int $month, array $holidaySet): array {
        $first  = sprintf('%04d-%02d-01', $year, $month);
        $last   = (new DateTime($first))->modify('last day of this month')->format('Y-m-d');

        $rows = $this->many(
            "SELECT DISTINCT attendance_date AS ymd
               FROM student_attendance
              WHERE section_id=? AND curriculum_id=? AND attendance_date BETWEEN ? AND ?
           ORDER BY attendance_date",
            [$sectionId, $curriculumId, $first, $last]
        );

        $out = [];
        foreach ($rows as $r){
            $ymd = (string)$r['ymd'];
            $dt  = DateTime::createFromFormat('Y-m-d', $ymd);
            if (!$dt) continue;
            $dow = (int)$dt->format('N'); // 1..7 (Mon..Sun)
            if ($dow >= 1 && $dow <= 5 && empty($holidaySet[$ymd])) {
                $out[] = $ymd;
            }
        }
        return $out;
    }

    /** Build presence map for student for a list of class school dates */
    private function presentAbsentByDate(int $studentId, int $sectionId, int $curriculumId, array $schoolDates): array {
        if (!$schoolDates) return [];
        $in = implode(',', array_fill(0, count($schoolDates), '?'));
        $params = array_merge([$studentId, $sectionId, $curriculumId], $schoolDates);
        $rows = $this->many(
            "SELECT attendance_date AS ymd,
                    UPPER(COALESCE(remarks,'')) AS remarks,
                    UPPER(COALESCE(am_status,'')) AS am,
                    UPPER(COALESCE(pm_status,'')) AS pm
               FROM student_attendance
              WHERE student_id=? AND section_id=? AND curriculum_id=? AND attendance_date IN ($in)",
            $params
        );
        $map = [];
        foreach ($rows as $r){
            $ymd = (string)$r['ymd'];
            $remarkAbsent = ($r['remarks'] === 'ABSENT');
            $presentAny   = (!$remarkAbsent) && ($r['am'] === 'P' || $r['pm'] === 'P');
            $map[$ymd] = $presentAny ? 'P' : 'A';
        }
        return $map;
    }

    /** Compute monthly counts for June..April for a student */
    private function computeMonthlyAttendance(int $sectionId, int $curriculumId, int $studentId, string $schoolYear, array $holidaySet): array {
        [$yearA, $yearB] = $this->parseSY($schoolYear);

        $months = [6,7,8,9,10,11,12,1,2,3,4];
        $yearOf = function(int $m) use ($yearA,$yearB){ return ($m >= 6 ? $yearA : $yearB); };

        $result = []; // month => ['school'=>int,'present'=>int,'absent'=>int]

        foreach ($months as $m){
            $yr = $yearOf($m);

            $classDays = $this->schoolDatesForMonth($sectionId, $curriculumId, $yr, $m, $holidaySet);
            $map       = $this->presentAbsentByDate($studentId, $sectionId, $curriculumId, $classDays);

            $present = 0; $absent = 0;
            foreach ($classDays as $ymd){
                $presentAny = isset($map[$ymd]) && $map[$ymd] === 'P';
                if ($presentAny) $present++; else $absent++;
            }

            $result[$m] = [
                'school'  => count($classDays),
                'present' => $present,
                'absent'  => $absent,
            ];
        }
        return $result;
    }

    /**
     * Write B..L for months, and M for totals, at a specific row.
     * If a month has **no class records** (school=0), leave that cell BLANK instead of 0.
     * Column M is BLANK if ALL months were blank; otherwise it sums only months with records.
     */
    private function writeMonthlyRow(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, int $row, array $byMonth, string $key){
        $cols   = ['B','C','D','E','F','G','H','I','J','K','L']; // June..April
        $months = [6,7,8,9,10,11,12,1,2,3,4];

        $total = 0;
        $any   = false;

        foreach ($months as $i => $m){
            $hasRecords = (int)($byMonth[$m]['school'] ?? 0) > 0;
            if (!$hasRecords) {
                // leave BLANK
                $sheet->setCellValue($cols[$i].$row, '');
                continue;
            }
            $any = true;
            $val = (int)($byMonth[$m][$key] ?? 0); // 0 is valid when there ARE records
            $total += $val;
            $sheet->setCellValueExplicit(
                $cols[$i].$row,
                $val,
                \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC
            );
        }

        // Column M (total)
        if ($any) {
            $sheet->setCellValueExplicit('M'.$row, $total, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
        } else {
            $sheet->setCellValue('M'.$row, '');
        }
    }

    /** Pick an SSG id for this student/section/curriculum that actually has Core Values */
    private function pickSSGForCoreValues(int $studentId, int $sectionId, int $curriculumId): ?int {
        // Prefer an SSG with rows in the per-behavior table
        $row = $this->one(
            "SELECT ssg.id
               FROM student_subject_grades ssg
          LEFT JOIN student_subject_core_values_rows r
                 ON r.deleted=0 AND r.ssg_id=ssg.id
              WHERE ssg.deleted=0
                AND ssg.student_id=? AND ssg.section_id=? AND ssg.curriculum_id=?
           GROUP BY ssg.id
           ORDER BY COUNT(r.id) DESC, ssg.id DESC
           LIMIT 1",
           [$studentId,$sectionId,$curriculumId]
        );
        if ($row && (int)$row['id'] > 0) return (int)$row['id'];

        // Otherwise, any SSG that has a legacy summary row
        $legacy = $this->one(
            "SELECT ssg.id
               FROM student_subject_grades ssg
               JOIN student_subject_core_values v
                 ON v.deleted=0 AND v.ssg_id=ssg.id
              WHERE ssg.deleted=0
                AND ssg.student_id=? AND ssg.section_id=? AND ssg.curriculum_id=?
           ORDER BY ssg.id DESC
           LIMIT 1",
           [$studentId,$sectionId,$curriculumId]
        );
        return ($legacy && (int)$legacy['id']>0) ? (int)$legacy['id'] : null;
    }

    /** Load Core Values as rows keyed by core_name and behavior_index */
    private function loadCoreValuesRows(int $ssgId): array {
        $out = [
            'maka_diyos'     => [],
            'makatao'        => [],
            'maka_kalikasan' => [],
            'maka_bansa'     => [],
        ];

        // Try the per-behavior rows table first
        $rows = $this->many(
            "SELECT core_name, behavior_index, q1, q2, q3, q4
               FROM student_subject_core_values_rows
              WHERE deleted=0 AND ssg_id=?
           ORDER BY core_name, behavior_index",
            [$ssgId]
        );
        if ($rows && count($rows)) {
            foreach ($rows as $r) {
                $core = $r['core_name'];
                if (!isset($out[$core])) $out[$core] = [];
                $out[$core][(int)$r['behavior_index']] = [
                    'q1' => (string)($r['q1'] ?? ''),
                    'q2' => (string)($r['q2'] ?? ''),
                    'q3' => (string)($r['q3'] ?? ''),
                    'q4' => (string)($r['q4'] ?? ''),
                ];
            }
            return $out;
        }

        // Fallback: legacy summary table → use as behavior #1
        $legacy = $this->one(
            "SELECT
               md_q1,md_q2,md_q3,md_q4,
               mt_q1,mt_q2,mt_q3,mt_q4,
               mk_q1,mk_q2,mk_q3,mk_q4,
               mb_q1,mb_q2,mb_q3,mb_q4
             FROM student_subject_core_values
            WHERE deleted=0 AND ssg_id=? LIMIT 1",
            [$ssgId]
        );
        if ($legacy) {
            $out['maka_diyos'][1]     = ['q1'=>$legacy['md_q1']??'','q2'=>$legacy['md_q2']??'','q3'=>$legacy['md_q3']??'','q4'=>$legacy['md_q4']??''];
            $out['makatao'][1]        = ['q1'=>$legacy['mt_q1']??'','q2'=>$legacy['mt_q2']??'','q3'=>$legacy['mt_q3']??'','q4'=>$legacy['mt_q4']??''];
            $out['maka_kalikasan'][1] = ['q1'=>$legacy['mk_q1']??'','q2'=>$legacy['mk_q2']??'','q3'=>$legacy['mk_q3']??'','q4'=>$legacy['mk_q4']??''];
            $out['maka_bansa'][1]     = ['q1'=>$legacy['mb_q1']??'','q2'=>$legacy['mb_q2']??'','q3'=>$legacy['mb_q3']??'','q4'=>$legacy['mb_q4']??''];
        }
        return $out;
    }

    /** Write Core Values to the back sheet using the specified cell map */
    private function writeCoreValues(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, array $values): void {
        // Cell map for both templates
        $map = [
            'maka_diyos' => [
                1 => ['Y7','Z7','AA7','AB7'],
                2 => ['Y10','Z10','AA10','AB10'],
            ],
            'makatao' => [
                1 => ['Y13','Z13','AA13','AB13'],
                2 => ['Y16','Z16','AA16','AB16'], // (fixed AA16)
            ],
            'maka_kalikasan' => [
                1 => ['Y18','Z18','AA18','AB18'],
            ],
            'maka_bansa' => [
                1 => ['Y20','Z20','AA20','AB20'],
                2 => ['Y22','Z22','AA22','AB22'],
            ],
        ];
        $norm = function($v){
            $v = strtoupper(trim((string)$v));
            return in_array($v, ['AO','SO','RO','NO'], true) ? $v : '';
        };

        foreach ($map as $core => $behaviors) {
            $coreRows = $values[$core] ?? [];
            foreach ($behaviors as $idx => $cells) {
                $row = $coreRows[$idx] ?? ['q1'=>'','q2'=>'','q3'=>'','q4'=>''];
                $qs  = [$norm($row['q1']??''), $norm($row['q2']??''), $norm($row['q3']??''), $norm($row['q4']??'')];
                // Y, Z, AA, AB in order
                for ($i=0; $i<4; $i++){
                    $sheet->setCellValue($cells[$i], $qs[$i]);
                }
            }
        }
    }

    /**
     * POST /component/student-grade-entry/exportSf9
     * Expects: section_id, curriculum_id, student_id
     * Optional: holidays=YYYY-MM-DD,YYYY-MM-DD,...
     */
    public function export() {
        try {
            $req = getRequestAll();
            $sectionId    = (int)($req['section_id']    ?? 0);
            $curriculumId = (int)($req['curriculum_id'] ?? 0);
            $studentId    = (int)($req['student_id']    ?? 0);
            if (!$sectionId || !$curriculumId || !$studentId) {
                http_response_code(400);
                echo 'Missing required fields: section_id, curriculum_id, student_id';
                return;
            }

            $cur = $this->one(
                "SELECT id, school_year, grade_id AS section_id
                   FROM curriculum
                  WHERE id=? AND deleted=0
                  LIMIT 1", [$curriculumId]
            );
            if (!$cur || (int)$cur['section_id'] !== (int)$sectionId) {
                http_response_code(400);
                echo 'Invalid section/curriculum pairing.';
                return;
            }
            $schoolYear = (string)($cur['school_year'] ?? '');

            $sec = $this->one(
                "SELECT s.name AS section_name, gl.name AS grade_name
                   FROM section s
                   JOIN grade_level gl ON gl.id = s.grade_id
                  WHERE s.id=? AND s.deleted=0
                  LIMIT 1", [$sectionId]
            );

            $stu = $this->one(
                "SELECT u.user_id AS id,
                        u.account_last_name   AS last,
                        u.account_first_name  AS first,
                        u.account_middle_name AS middle,
                        u.LRN,
                        u.gender,
                        u.dateof_birth
                   FROM users u
                   JOIN registrar_student rs ON rs.student_id = u.user_id
                  WHERE u.user_id=? AND rs.deleted=0 AND rs.status=1
                    AND rs.section_id=? AND rs.curriculum_id=?
                  LIMIT 1",
                [$studentId, $sectionId, $curriculumId]
            );
            if (!$stu) { http_response_code(404); echo 'Student not found in this section/curriculum.'; return; }

            $fullName = trim(
                ($stu['last'] ?? '') . ', ' . ($stu['first'] ?? '') .
                (!empty($stu['middle']) ? ' ' . $stu['middle'] : '')
            );
            $gRaw = strtoupper(trim((string)($stu['gender'] ?? '')));
            $genderLabel = (in_array($gRaw, ['M','MALE','BOY','1'], true) ? 'Male'
                            : (in_array($gRaw, ['F','FEMALE','GIRL','2'], true) ? 'Female' : ''));

            $age = '';
            if (!empty($stu['dateof_birth']) && $stu['dateof_birth'] !== '0000-00-00') {
                try { $dob = new DateTime($stu['dateof_birth']); $now = new DateTime('today'); $age = $dob->diff($now)->y; }
                catch (Throwable $e) { $age = ''; }
            }

            $gradeLevel  = $sec['grade_name']   ?? '';
            $sectionName = $sec['section_name'] ?? '';

            // optional holidays
            $holidaySet = [];
            if (!empty($req['holidays'])) {
                $parts = preg_split('/[,\s]+/', (string)$req['holidays'], -1, PREG_SPLIT_NO_EMPTY);
                foreach ($parts as $p) {
                    $p = trim($p);
                    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $p)) $holidaySet[$p] = true;
                }
            }

            // choose template
            $tplBaseDir   = __DIR__ . '/templates/';
            $defaultTpl   = 'ELEM SF9 Learners Progress Report Card.xlsx';
            $grade1Tpl    = 'ELEM SF9 for Grade 1.xlsx';

            $isGrade1 = preg_match('/\bgrade\s*1\b|\bgr\s*1\b|^1$|\bgrade\s*one\b/i', (string)$gradeLevel) === 1;
            $isGrade4 = preg_match('/\bgrade\s*4\b|\bgr\s*4\b|^4$|\bgrade\s*four\b/i', (string)$gradeLevel) === 1;
            $isGrade5 = preg_match('/\bgrade\s*5\b|\bgr\s*5\b|^5$|\bgrade\s*five\b/i', (string)$gradeLevel) === 1;
            $isGrade6 = preg_match('/\bgrade\s*6\b|\bgr\s*6\b|^6$|\bgrade\s*six\b/i', (string)$gradeLevel) === 1;

            $templateFile = $isGrade1 ? $grade1Tpl : $defaultTpl;
            $templatePath = $tplBaseDir . $templateFile;
            if (!is_file($templatePath)) { $templateFile = $defaultTpl; $templatePath = $tplBaseDir . $templateFile; }
            if (!is_file($templatePath)) { http_response_code(500); echo 'Template not found at: ' . htmlspecialchars($templatePath); return; }

            // load template
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            $reader->setReadDataOnly(false);
            $reader->setIncludeCharts(true);
            $spreadsheet = $reader->load($templatePath);

            // FRONT sheet
            $front = $spreadsheet->getSheet(0);
            $spreadsheet->setActiveSheetIndex(0);

            $cells = $isGrade1
                ? ['name' => 'Q22', 'lrn' => 'S24', 'age' => 'Q26', 'gender' => 'T26', 'grade' => 'Q28', 'section' => 'T28']
                : ['name' => 'Q22', 'lrn' => 'S24', 'age' => 'Q26', 'gender' => 'T26', 'grade' => 'Q28', 'section' => 'T28'];


            $front->setCellValue($cells['name'], $fullName);
            $front->getStyle($cells['lrn'])->getNumberFormat()->setFormatCode('@');
            $front->setCellValueExplicit($cells['lrn'], (string)($stu['LRN'] ?? ''), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $front->setCellValue($cells['age'], $age);
            $front->setCellValue($cells['gender'], $genderLabel);
            $front->setCellValue($cells['grade'], $gradeLevel);
            $front->setCellValue($cells['section'], $sectionName);
            $front->setCellValue('Q30', $schoolYear);

            $logoPath = __DIR__ . '/templates/deped-logo.png';
            if (is_file($logoPath)) {
                $logo = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                $logo->setName('DepEd Logo');
                $logo->setDescription('DepEd Logo');
                $logo->setPath($logoPath);
                $logo->setResizeProportional(true);
                $logo->setHeight(68);
                $logo->setCoordinates('P4');
                $logo->setWorksheet($front);
            }

            /* ===== BACK SHEET ===== */
            if ($spreadsheet->getSheetCount() > 1) {
                $back = $spreadsheet->getSheet(1);

                // ---- Subjects + GA ----
                $grades = $this->many(
                    "SELECT cc.subject_id AS id, s.code, s.name,
                            g.q1, g.q2, g.q3, g.q4, g.final_average
                       FROM curriculum_child cc
                       JOIN subjects s ON s.id=cc.subject_id
                  LEFT JOIN student_subject_grades g
                         ON g.deleted=0
                        AND g.student_id=?
                        AND g.section_id=?
                        AND g.curriculum_id=?
                        AND g.subject_id=cc.subject_id
                      WHERE cc.deleted=0 AND cc.curriculum_id=?
                   ORDER BY s.name",
                    [$studentId,$sectionId,$curriculumId,$curriculumId]
                );

                $byName = [];
                foreach ($grades as $g) {
                    $key = mb_strtolower(trim((string)$g['name']));
                    $byName[$key] = [
                        'q1' => $g['q1'], 'q2' => $g['q2'], 'q3' => $g['q3'], 'q4' => $g['q4'],
                        'final' => $g['final_average']
                    ];
                }

                $lookup = function(array $names) use ($byName) {
                    foreach ($names as $n) {
                        $k = mb_strtolower(trim($n));
                        if (isset($byName[$k])) return $byName[$k];
                    }
                    return ['q1'=>null,'q2'=>null,'q3'=>null,'q4'=>null,'final'=>null];
                };
                $computeFinal = function($row) {
                    if ($row['final'] !== null && $row['final'] !== '') return (int) round((float)$row['final'], 0);
                    $vals = [];
                    foreach (['q1','q2','q3','q4'] as $qk) {
                        if ($row[$qk] !== null && $row[$qk] !== '') $vals[] = (float)$row[$qk];
                    }
                    return $vals ? (int) round(array_sum($vals)/count($vals), 0) : null;
                };
                $writeRow = function($rowIdx, $labelCell, $label, $rowData) use ($back, $computeFinal) {
                    $back->setCellValue($labelCell.$rowIdx, $label);
                    $q1 = $rowData['q1']; $q2 = $rowData['q2']; $q3 = $rowData['q3']; $q4 = $rowData['q4'];
                    $fin = $computeFinal($rowData);
                    $back->getStyle('N'.$rowIdx.':R'.$rowIdx)->getNumberFormat()->setFormatCode('0');
                    $back->setCellValue('N'.$rowIdx, ($q1 === null || $q1 === '') ? '' : (int) round((float)$q1, 0));
                    $back->setCellValue('O'.$rowIdx, ($q2 === null || $q2 === '') ? '' : (int) round((float)$q2, 0));
                    $back->setCellValue('P'.$rowIdx, ($q3 === null || $q3 === '') ? '' : (int) round((float)$q3, 0));
                    $back->setCellValue('Q'.$rowIdx, ($q4 === null || $q4 === '') ? '' : (int) round((float)$q4, 0));
                    $back->setCellValue('R'.$rowIdx, ($fin === null) ? '' : (int)$fin);
                    $back->setCellValue('S'.$rowIdx, ($fin === null) ? '' : (($fin < 75) ? 'FAILED' : 'PASSED'));
                    return $fin;
                };

                $isG1 = $isGrade1; $isG4 = $isGrade4; $isG5 = $isGrade5; $isG6 = $isGrade6;

                if ($isG1) {
                    // Grade 1 (no MAPEH row/components in this layout)
                    $subjectsPlan = [
                        7  => ['A', 'Reading and Literacy',           ['Reading and Literacy','Reading & Literacy','Reading','English (Reading)']],
                        8  => ['A', 'Language',                       ['Language','Filipino','English (Language)','Mother Tongue']],
                        9  => ['A', 'Mathematics',                    ['Mathematics','Math']],
                        11 => ['A', 'Makabansa / Araling Panlipunan', ['Makabansa/ Araling Panlipunan','Araling Panlipunan','Makabansa','AP']],
                        12 => ['A', 'GMRC / ESP',                     ['GMRC / ESP','GMRC','ESP','Edukasyon sa Pagpapakatao']],
                    ];
                    $finalsForGA = [];
                    foreach ($subjectsPlan as $rowIdx => [$labelCol, $label, $syns]) {
                        $fin = $writeRow($rowIdx, $labelCol, $label, $lookup($syns));
                        if ($fin !== null) $finalsForGA[] = (int)$fin;
                    }
                    $genAvg = $finalsForGA ? (int) round(array_sum($finalsForGA)/count($finalsForGA), 0) : null;
                    $back->getStyle('R13')->getNumberFormat()->setFormatCode('0');
                    $back->setCellValue('R13', $genAvg === null ? '' : $genAvg);
                    $back->setCellValue('S13', ($genAvg !== null && $genAvg > 75) ? 'PROMOTED' : '');
                } elseif ($isG4 || $isG5 || $isG6) {
                    // Grades 4,5,6 — GA counts MAPEH once
                    $subjectsPlan = [
                        7  => ['A', 'Filipino',                      ['Filipino','Wikang Filipino']],
                        8  => ['A', 'English',                       ['English']],
                        9  => ['A', 'Mathematics',                   ['Mathematics','Math']],
                        11 => ['A', 'Science',                       ['Science','Sci']],
                        12 => ['A', 'Araling Panlipunan',            ['Araling Panlipunan','Makabansa','Makabansa/ Araling Panlipunan','AP']],
                        13 => ['A', 'GMRC / ESP',                    ['GMRC / ESP','GMRC','ESP','Edukasyon sa Pagpapakatao','Edukasyon sa Pagpapakatao (EsP)']],
                        15 => ['A', 'Edukasyong Pantahanan at Pangkabuhayan', ['Edukasyong Pantahanan at Pangkabuhayan','EPP','EPP / TLE','TLE','EPP/TLE']],
                        17 => ['A', 'MAPEH',                         ['MAPEH']],
                        18 => ['B', 'Music',                         ['Music','MAPEH - Music']],
                        19 => ['B', 'Arts',                          ['Arts','MAPEH - Arts']],
                        20 => ['B', 'Physical Education',            ['Physical Education','PE','MAPEH - Physical Education']],
                        21 => ['B', 'Health',                        ['Health','MAPEH - Health']],
                    ];

                    $gaFinals   = [];   // learning areas (excluding MAPEH components)
                    $mapehFinal = null; // if MAPEH row has a final, use it
                    $mapehParts = [];   // else average these components

                    foreach ($subjectsPlan as $rowIdx => [$labelCol, $label, $syns]) {
                        $fin = $writeRow($rowIdx, $labelCol, $label, $lookup($syns));
                        if ($label === 'MAPEH') {
                            $mapehFinal = ($fin !== null) ? (int)$fin : null;
                        } elseif (in_array($label, ['Music','Arts','Physical Education','Health'], true)) {
                            if ($fin !== null) $mapehParts[] = (int)$fin;
                        } else {
                            if ($fin !== null) $gaFinals[] = (int)$fin;
                        }
                    }

                    if ($mapehFinal !== null) {
                        $gaFinals[] = $mapehFinal;
                    } elseif (count($mapehParts) > 0) {
                        $gaFinals[] = (int) round(array_sum($mapehParts)/count($mapehParts), 0);
                    }

                    $r22 = $gaFinals ? (int) round(array_sum($gaFinals)/count($gaFinals), 0) : null;
                    $back->getStyle('R22')->getNumberFormat()->setFormatCode('0');
                    $back->setCellValue('R22', $r22 === null ? '' : $r22);
                    $back->setCellValue('S22', ($r22 === null) ? '' : (($r22 > 75) ? 'PASSED' : 'FAILED'));
                } else {
                    // Grades 2–3 — GA counts MAPEH once
                    $subjectsPlan = [
                        7  => ['A', 'Mother Tongue',                 ['Mother Tongue','MTB','MTB-MLE']],
                        8  => ['A', 'Filipino',                      ['Filipino','Wikang Filipino']],
                        9  => ['A', 'English',                       ['English']],
                        11 => ['A', 'Mathematics',                   ['Mathematics','Math']],
                        12 => ['A', 'Science',                       ['Science','Sci']],
                        13 => ['A', 'Araling Panlipunan',            ['Araling Panlipunan','Makabansa','Makabansa/ Araling Panlipunan','AP']],
                        15 => ['A', 'GMRC / ESP',                    ['GMRC / ESP','GMRC','ESP','Edukasyon sa Pagpapakatao','Edukasyon sa Pagpapakatao (EsP)']],
                        17 => ['A', 'MAPEH',                         ['MAPEH']],
                        18 => ['B', 'Music',                         ['Music','MAPEH - Music']],
                        19 => ['B', 'Arts',                          ['Arts','MAPEH - Arts']],
                        20 => ['B', 'Physical Education',            ['Physical Education','PE','MAPEH - Physical Education']],
                        21 => ['B', 'Health',                        ['Health','MAPEH - Health']],
                    ];

                    $gaFinals   = [];
                    $mapehFinal = null;
                    $mapehParts = [];

                    foreach ($subjectsPlan as $rowIdx => [$labelCol, $label, $syns]) {
                        $fin = $writeRow($rowIdx, $labelCol, $label, $lookup($syns));
                        if ($label === 'MAPEH') {
                            $mapehFinal = ($fin !== null) ? (int)$fin : null;
                        } elseif (in_array($label, ['Music','Arts','Physical Education','Health'], true)) {
                            if ($fin !== null) $mapehParts[] = (int)$fin;
                        } else {
                            if ($fin !== null) $gaFinals[] = (int)$fin;
                        }
                    }

                    if ($mapehFinal !== null) {
                        $gaFinals[] = $mapehFinal;
                    } elseif (count($mapehParts) > 0) {
                        $gaFinals[] = (int) round(array_sum($mapehParts)/count($mapehParts), 0);
                    }

                    $r22 = $gaFinals ? (int) round(array_sum($gaFinals)/count($gaFinals), 0) : null;
                    $back->getStyle('R22')->getNumberFormat()->setFormatCode('0');
                    $back->setCellValue('R22', $r22 === null ? '' : $r22);
                    $back->setCellValue('S22', ($r22 === null) ? '' : (($r22 > 75) ? 'PASSED' : 'FAILED'));
                }

                // ---- Core Values (both templates) ----
                $ssgId = $this->pickSSGForCoreValues($studentId,$sectionId,$curriculumId);
                if ($ssgId) {
                    $cv = $this->loadCoreValuesRows($ssgId);
                    $this->writeCoreValues($back, $cv);
                }
            }

            /* ===== MONTHLY ATTENDANCE on FRONT (June→April) ===== */
            $byMonth = $this->computeMonthlyAttendance($sectionId, $curriculumId, $studentId, $schoolYear, $holidaySet);

            // Row 7: School Days, Row 9: Days Present, Row 12: Days Absent
            $this->writeMonthlyRow($front,  7, $byMonth, 'school');
            $this->writeMonthlyRow($front,  9, $byMonth, 'present');
            $this->writeMonthlyRow($front, 12, $byMonth, 'absent');

            // integer formats (blanks stay blank)
            foreach ([7,9,12] as $rowIdx){
                $front->getStyle("B{$rowIdx}:M{$rowIdx}")
                      ->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER);
            }

            // stream
            $safeName = preg_replace('/[^A-Za-z0-9_\-]+/', '_', $fullName ?: 'Student');
            $fileName = "SF9_{$safeName}_{$schoolYear}.xlsx";

            if (function_exists('ob_get_length')) { while (ob_get_length()) { @ob_end_clean(); } }
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="'.$fileName.'"');
            header('Cache-Control: max-age=0');
            header('Pragma: public');

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->setIncludeCharts(true);
            $writer->save('php://output');
            exit;
        } catch (Throwable $e) {
            http_response_code(500);
            echo 'Export failed: ' . htmlspecialchars($e->getMessage());
        }
    }
}
