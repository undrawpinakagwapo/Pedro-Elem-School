<?php
// components/StudentGradeEntryController/ExportSf10Controller.php

// PhpSpreadsheet autoload (Composer)
$vendorAutoload = __DIR__ . '/../../vendor/autoload.php';
if (file_exists($vendorAutoload)) {
    require_once $vendorAutoload;
}

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExportSf10Controller
{
    protected $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /* ---------- tiny DB helpers ---------- */
    private function one($sql, $p = []) {
        $r = $this->db->Select($sql, $p);
        return $r ? $r[0] : null;
    }
    private function many($sql, $p = []) {
        return $this->db->Select($sql, $p);
    }

    /* ---------- subject fetching & writing helpers ---------- */
    private function fetchSubjectGrades($studentId, $sectionId, $curriculumId, array $aliases, $subjectTable = 'subjects') {
    $w = [];
    $p = [$studentId, $sectionId, $curriculumId];
    foreach ($aliases as $a) {
        $w[] = 's.name LIKE ?';  $p[] = '%'.$a.'%';
        $w[] = 's.code LIKE ?';  $p[] = '%'.$a.'%';
    }
    if (!$w) return null;
    $where = implode(' OR ', $w);

    // --- QUERY NOW SELECTS BOTH ORIGINAL AND RECOMPUTED GRADES ---
    $sql = "SELECT 
                s.name, 
                g.q1, g.q2, g.q3, g.q4, 
                g.final_average AS original_final,
                rc.recomputed_final
            FROM student_subject_grades g
            JOIN {$subjectTable} s ON s.id = g.subject_id
            LEFT JOIN student_remedial_classes rc ON rc.ssg_id = g.id AND rc.deleted = 0
            WHERE g.deleted=0
            AND g.student_id=? AND g.section_id=? AND g.curriculum_id=?
            AND ({$where})
            LIMIT 1";
    // --- END OF UPDATE ---

    return $this->one($sql, $p);
}
    private function computeFinal(?float $q1, ?float $q2, ?float $q3, ?float $q4): ?int {
        $vals = array_values(array_filter([$q1,$q2,$q3,$q4], fn($v)=>$v!==null && $v!=='' && is_numeric($v)));
        return $vals ? (int) round(array_sum($vals)/count($vals), 0) : null;
    }

    private function composeMapehFromParts($studentId,$sectionId,$curriculumId,$subjectTable,$SUBJECT_DEFS){
        $parts = ['*Music','*Arts','*Physical Education','*Health'];
        $qs = ['q1'=>[],'q2'=>[],'q3'=>[],'q4'=>[]];
        foreach ($parts as $p) {
            if (!isset($SUBJECT_DEFS[$p])) continue;
            $row = $this->fetchSubjectGrades($studentId,$sectionId,$curriculumId,$SUBJECT_DEFS[$p]['aliases'],$subjectTable);
            if (!$row) continue;
            foreach (['q1','q2','q3','q4'] as $q) {
                if (isset($row[$q]) && $row[$q] !== null && $row[$q] !== '' && is_numeric($row[$q])) {
                    $qs[$q][] = (float)$row[$q];
                }
            }
        }
        if (empty($qs['q1']) && empty($qs['q2']) && empty($qs['q3']) && empty($qs['q4'])) return null;
        $avgQ = function(array $arr){ return $arr ? (float) (array_sum($arr)/count($arr)) : null; };
        return [
            'q1' => $avgQ($qs['q1']),
            'q2' => $avgQ($qs['q2']),
            'q3' => $avgQ($qs['q3']),
            'q4' => $avgQ($qs['q4']),
            'final_average' => null,
        ];
    }

    private function writeSubjectRow(
    \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, array $COLS, int $row, string $label,
    ?float $q1, ?float $q2, ?float $q3, ?float $q4, ?float $finalFromDB,
    bool $writeLabel = true
): ?int {
    if ($writeLabel) $sheet->setCellValue($COLS['subj'].$row, $label);

    $final = null; // Start with null

    // --- LOGIC IS NOW CORRECTED ---
    // PRIORITIZE the final grade from the database (which includes the recomputed grade)
    if ($finalFromDB !== null && $finalFromDB !== '') {
        $final = (int) round((float)$finalFromDB, 0);
    } 
    // ONLY if the database has no final grade, then calculate it from quarters
    else {
        $final = $this->computeFinal($q1, $q2, $q3, $q4);
    }
    // --- END OF CORRECTION ---

    foreach (['q1','q2','q3','q4','final'] as $k) {
        $sheet->getStyle($COLS[$k].$row)->getNumberFormat()->setFormatCode('0');
    }

    $sheet->setCellValue($COLS['q1'].$row,    ($q1===null ? '' : (int)round($q1,0)));
    $sheet->setCellValue($COLS['q2'].$row,    ($q2===null ? '' : (int)round($q2,0)));
    $sheet->setCellValue($COLS['q3'].$row,    ($q3===null ? '' : (int)round($q3,0)));
    $sheet->setCellValue($COLS['q4'].$row,    ($q4===null ? '' : (int)round($q4,0)));
    $sheet->setCellValue($COLS['final'].$row, ($final===null ? '' : $final));

    $remarks = ($final !== null && $final >= 75) ? 'PASSED' : (($final !== null) ? 'FAILED' : '');
    $sheet->setCellValue($COLS['remarks'].$row, $remarks);

    return $final;
}

    private function averageRange(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, string $col, int $rowStart, int $rowEnd): ?float {
        $nums = [];
        for ($r = $rowStart; $r <= $rowEnd; $r++) {
            $v = $sheet->getCell($col.$r)->getCalculatedValue();
            if ($v === null || $v === '') continue;
            if (is_numeric($v)) $nums[] = (float)$v;
        }
        if (!$nums) return null;
        return array_sum($nums) / count($nums);
    }

    /**
     * Fetch up to two remedial rows (with remarks only) for a given section+curriculum.
     * Returns:
     *   subject_label, final_rating, remedial_mark, recomputed_final, remarks, conducted_from, conducted_to
     */
    private function fetchRemedialsWithRemarks($studentId, $sectionId, $curriculumId): array {
        $sql = "SELECT
                    s.code, s.name,
                    rc.final_rating, rc.remedial_mark, rc.recomputed_final, rc.remarks,
                    rc.conducted_from, rc.conducted_to
                FROM student_remedial_classes rc
                JOIN student_subject_grades ssg
                     ON ssg.id = rc.ssg_id AND ssg.deleted = 0
                JOIN subjects s
                     ON s.id = rc.subject_id
               WHERE rc.deleted = 0
                 AND ssg.student_id = ?
                 AND ssg.section_id = ?
                 AND ssg.curriculum_id = ?
                 AND rc.remarks IS NOT NULL
                 AND rc.remarks <> ''
            ORDER BY s.name
               LIMIT 2";
        $rows = $this->many($sql, [$studentId,$sectionId,$curriculumId]);

        $out = [];
        foreach ($rows as $r) {
    $out[] = [
        'subject_label'    => $r['name'] ?? '', // Changed this line
        'final_rating'     => ($r['final_rating']     === null || $r['final_rating']     === '') ? null : (float)$r['final_rating'],
        'remedial_mark'    => ($r['remedial_mark']    === null || $r['remedial_mark']    === '') ? null : (float)$r['remedial_mark'],
        'recomputed_final' => ($r['recomputed_final'] === null || $r['recomputed_final'] === '') ? null : (float)$r['recomputed_final'],
        'remarks'          => trim((string)($r['remarks'] ?? '')),
        'conducted_from'   => $r['conducted_from'] ?? null,
        'conducted_to'     => $r['conducted_to']   ?? null,
    ];
}
        return $out;
    }

    /** Generic writer for any grade’s remedial block. */
    private function writeRemedialsBlock(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, array $map, array $remRows): void {
        // Clear cells first
        $clear = array_merge(
            [$map['from'], $map['to']],
            [$map['rows'][0]['subj'],$map['rows'][0]['fr'],$map['rows'][0]['rcm'],$map['rows'][0]['rfg'],$map['rows'][0]['rem']],
            [$map['rows'][1]['subj'],$map['rows'][1]['fr'],$map['rows'][1]['rcm'],$map['rows'][1]['rfg'],$map['rows'][1]['rem']]
        );
        foreach ($clear as $addr) { $sheet->setCellValue($addr, ''); }

        if (empty($remRows)) return; // nothing to write

        // Dates: use min(from) and max(to)
        $fmtDate = function($raw){ if(!$raw||$raw==='0000-00-00') return ''; try{ return (new \DateTime($raw))->format('m/d/Y'); }catch(\Throwable $e){ return ''; } };
        $froms = array_values(array_filter(array_map(fn($r)=>$r['conducted_from'] ?? null, $remRows)));
        $tos   = array_values(array_filter(array_map(fn($r)=>$r['conducted_to']   ?? null, $remRows)));
        if (!empty($froms)) { sort($froms); $sheet->setCellValue($map['from'], $fmtDate($froms[0])); }
        if (!empty($tos))   { sort($tos);   $sheet->setCellValue($map['to'],   $fmtDate($tos[count($tos)-1])); }

        // Helper for whole-number format
        $setInt = function($addr) use ($sheet){
            $sheet->getStyle($addr)->getNumberFormat()->setFormatCode('0');
        };

        foreach ($remRows as $i => $r) {
            if ($i > 1) break;
            $t = $map['rows'][$i];

            $sheet->setCellValue($t['subj'], $r['subject_label']);

            if ($r['final_rating'] !== null)      { $setInt($t['fr']);  $sheet->setCellValue($t['fr'],  (int)round($r['final_rating'],0)); }
            if ($r['remedial_mark'] !== null)     { $setInt($t['rcm']); $sheet->setCellValue($t['rcm'], (int)round($r['remedial_mark'],0)); }
            if ($r['recomputed_final'] !== null)  { $setInt($t['rfg']); $sheet->setCellValue($t['rfg'], (int)round($r['recomputed_final'],0)); }
            $sheet->setCellValue($t['rem'], $r['remarks']);
        }
    }

    public function export() {
        // 1) Gather & validate input from POST
        $req = getRequestAll();
        $sectionId    = (int)($req['section_id']    ?? 0);
        $curriculumId = (int)($req['curriculum_id'] ?? 0);
        $studentId    = (int)($req['student_id']    ?? 0);

        if (!$sectionId || !$curriculumId || !$studentId) {
            http_response_code(400);
            echo 'Missing required fields: section_id, curriculum_id, student_id';
            return;
        }

        // 2) Validate curriculum exists
        $cur = $this->one(
            "SELECT id, school_year, adviser_id
               FROM curriculum
              WHERE id=? AND deleted=0
              LIMIT 1",
            [$curriculumId]
        );
        if (!$cur) { http_response_code(400); echo 'Invalid curriculum.'; return; }

        // 3) Student in this section & curriculum
        $stu = $this->one(
            "SELECT u.user_id AS id,
                    u.account_last_name   AS last,
                    u.account_first_name  AS first,
                    u.account_middle_name AS middle,
                    u.LRN,
                    u.gender,
                    u.dateof_birth AS dob
               FROM users u
               JOIN registrar_student rs ON rs.student_id = u.user_id
              WHERE u.user_id=? AND rs.deleted=0 AND rs.status=1
                AND rs.section_id=? AND rs.curriculum_id=?
              LIMIT 1",
            [$studentId, $sectionId, $curriculumId]
        );
        if (!$stu) { http_response_code(404); echo 'Student not found in this section/curriculum.'; return; }

        // 4) Load template
        $templatePath = __DIR__ . '/templates/School-Form-10-ES-Learners-Permanent-Record.xlsx';
        if (!file_exists($templatePath)) { http_response_code(500); echo 'Template not found at: ' . htmlspecialchars($templatePath); return; }
        try { $spreadsheet = IOFactory::load($templatePath); }
        catch (\Throwable $e) { http_response_code(500); echo 'Failed to load template: ' . htmlspecialchars($e->getMessage()); return; }

        // Sheets
        $sheetFront = $spreadsheet->getSheetByName('Front') ?: $spreadsheet->getActiveSheet();
        $sheetBack  = $spreadsheet->getSheetByName('Back');
        if (!$sheetBack && $spreadsheet->getSheetCount() > 1) $sheetBack = $spreadsheet->getSheet(1);

        // 5) Name parsing for suffix
        $lastName   = (string)($stu['last']   ?? '');
        $firstName  = (string)($stu['first']  ?? '');
        $middleName = (string)($stu['middle'] ?? '');
        $nameExt    = '';
        if ($lastName && preg_match('/\s+(Jr\.?|Sr\.?|I|II|III|IV|V)$/i', $lastName, $m)) {
            $nameExt  = trim($m[1]);
            $lastName = trim(preg_replace('/\s+(Jr\.?|Sr\.?|I|II|III|IV|V)$/i', '', $lastName));
        }

        // 6) LRN / DOB / Gender
        $lrn = (string)($stu['LRN'] ?? '');
        $dobRaw = $stu['dob'] ?? null;
        $dobFormatted = '';
        if (!empty($dobRaw) && $dobRaw !== '0000-00-00') {
            try { $dobFormatted = (new \DateTime($dobRaw))->format('m/d/Y'); } catch (\Throwable $e) { $dobFormatted = ''; }
        }

        $gRaw = strtoupper(trim((string)($stu['gender'] ?? '')));
        $genderText = ($gRaw==='M'||$gRaw==='MALE'||$gRaw==='1'||$gRaw==='TRUE'||$gRaw==='YES') ? 'Male'
                    : (($gRaw==='F'||$gRaw==='FEMALE'||$gRaw==='2'||$gRaw==='0') ? 'Female' : '');

        // 7) Header
        $sheetFront->setCellValue('E9',  $lastName);
        $sheetFront->setCellValue('R9',  $firstName);
        $sheetFront->setCellValue('AD9', $nameExt);
        $sheetFront->setCellValue('AQ9', $middleName);

        $sheetFront->setCellValue('J10',  $lrn);
        $sheetFront->setCellValue('V10',  $dobFormatted);
        $sheetFront->setCellValue('AT10', $genderText);

        // 8) All enrollments
        $enrollments = $this->many(
            "SELECT 
                 rs.section_id,
                 c.id AS curriculum_id,
                 c.school_year,
                 s.name AS section_name,
                 gl.name AS grade_name,
                 COALESCE(s.adviser_id, c.adviser_id) AS adviser_id
             FROM registrar_student rs
             JOIN curriculum c   ON c.id = rs.curriculum_id AND c.deleted=0
             JOIN section s      ON s.id = rs.section_id AND s.deleted=0
             JOIN grade_level gl ON gl.id = s.grade_id
            WHERE rs.deleted=0 AND rs.status=1
              AND rs.student_id=?
            ORDER BY c.school_year ASC",
            [$studentId]
        );

        // 9) Latest per grade
        $byGrade = [];
        foreach ($enrollments as $row) {
            $gradeName = (string)($row['grade_name'] ?? '');
            $gNum = (int)preg_replace('/\D+/', '', $gradeName);
            if ($gNum <= 0) continue;

            $startYear = null;
            if (preg_match('/(\d{4})/', (string)$row['school_year'], $m)) $startYear = (int)$m[1];

            if (!isset($byGrade[$gNum])) {
                $byGrade[$gNum] = [
                    'section_id'=>(int)$row['section_id'],'curriculum_id'=>(int)$row['curriculum_id'],
                    'section'=>(string)$row['section_name'],'sy'=>(string)$row['school_year'],
                    'adviser_id'=>$row['adviser_id'] ? (int)$row['adviser_id'] : null, 'start'=>$startYear ?? -1,
                ];
            } else {
                if (($startYear ?? -1) >= $byGrade[$gNum]['start']) {
                    $byGrade[$gNum]['section_id']    = (int)$row['section_id'];
                    $byGrade[$gNum]['curriculum_id'] = (int)$row['curriculum_id'];
                    $byGrade[$gNum]['section']       = (string)$row['section_name'];
                    $byGrade[$gNum]['sy']            = (string)$row['school_year'];
                    $byGrade[$gNum]['adviser_id']    = $row['adviser_id'] ? (int)$row['adviser_id'] : null;
                    $byGrade[$gNum]['start']         = $startYear ?? $byGrade[$gNum]['start'];
                }
            }
        }

        // 10) Roman numerals & placements
        $romanMap = [1=>'I',2=>'II',3=>'III',4=>'IV',5=>'V',6=>'VI',7=>'VII',8=>'VIII',9=>'IX',10=>'X'];
        $placements = [
            1 => ['sheet'=>'front', 'grade'=>'F25',  'section'=>'J25',  'sy'=>'S25',  'adviser'=>'H26'],
            2 => ['sheet'=>'front', 'grade'=>'Z25',  'section'=>'AE25', 'sy'=>'AU25', 'adviser'=>'AC26'],
            3 => ['sheet'=>'front', 'grade'=>'F54',  'section'=>'J54',  'sy'=>'S54',  'adviser'=>'H55'],
            4 => ['sheet'=>'front', 'grade'=>'Z54',  'section'=>'AE54', 'sy'=>'AU54', 'adviser'=>'AC55'],
            5 => ['sheet'=>'back',  'grade'=>'E5',   'section'=>'H5',   'sy'=>'O5',   'adviser'=>'F6'],
            6 => ['sheet'=>'back',  'grade'=>'U5',   'section'=>'X5',   'sy'=>'AG5',  'adviser'=>'V6'],
        ];

        $advCache = [];
        $getAdviserName = function($uid) use (&$advCache) {
            if (!$uid) return '';
            if (isset($advCache[$uid])) return $advCache[$uid];
            $row = $this->one(
                "SELECT account_last_name AS last, account_first_name AS first, account_middle_name AS middle
                   FROM users WHERE user_id=? LIMIT 1",
                [$uid]
            );
            if (!$row) return $advCache[$uid] = '';
            $mi = $row['middle'] ? (' ' . mb_substr($row['middle'], 0, 1) . '.') : '';
            return $advCache[$uid] = trim(($row['last'] ?? '') . ', ' . ($row['first'] ?? '') . $mi);
        };

        foreach ($byGrade as $g => $info) {
            if (!isset($placements[$g])) continue;
            $cells = $placements[$g];
            $targetSheetMeta = ($cells['sheet'] === 'back' && $sheetBack) ? $sheetBack : $sheetFront;

            $targetSheetMeta->setCellValue($cells['grade'],   $romanMap[$g] ?? (string)$g);
            $targetSheetMeta->setCellValue($cells['section'], (string)$info['section']);
            $targetSheetMeta->setCellValue($cells['sy'],      (string)$info['sy']);
            $targetSheetMeta->setCellValue($cells['adviser'], $getAdviserName($info['adviser_id']));
        }

        /* ================================================================
         * Fixed school meta cells per grade
         * ================================================================ */
        $SCHOOL_META = [
            1 => ['sheet'=>'Front',
                ['cell'=>'D23','value'=>'PEDRO SA BACULIO ELEMENTARY SCHOOL'],
                ['cell'=>'S23','value'=>'127677'],
                ['cell'=>'D24','value'=>'II'],
                ['cell'=>'I24','value'=>'EL SALVADOR CITY'],
                ['cell'=>'T24','value'=>'X'],
            ],
            2 => ['sheet'=>'Front',
                ['cell'=>'X23','value'=>'PEDRO SA BACULIO ELEMENTARY SCHOOL'],
                ['cell'=>'AW23','value'=>'127677'],
                ['cell'=>'X24','value'=>'II'],
                ['cell'=>'AD24','value'=>'EL SALVADOR CITY'],
                ['cell'=>'AX24','value'=>'X'],
            ],
            3 => ['sheet'=>'Front',
                ['cell'=>'D52','value'=>'PEDRO SA BACULIO ELEMENTARY SCHOOL'],
                ['cell'=>'S52','value'=>'127677'],
                ['cell'=>'D53','value'=>'II'],
                ['cell'=>'I53','value'=>'EL SALVADOR CITY'],
                ['cell'=>'T53','value'=>'X'],
            ],
            4 => ['sheet'=>'Front',
                ['cell'=>'X52','value'=>'PEDRO SA BACULIO ELEMENTARY SCHOOL'],
                ['cell'=>'AW52','value'=>'127677'],
                ['cell'=>'X53','value'=>'II'],
                ['cell'=>'AD53','value'=>'EL SALVADOR CITY'],
                ['cell'=>'AX53','value'=>'X'],
            ],
            5 => ['sheet'=>'Back',
                ['cell'=>'C3','value'=>'PEDRO SA BACULIO ELEMENTARY SCHOOL'],
                ['cell'=>'O3','value'=>'127677'],
                ['cell'=>'C4','value'=>'II'],
                ['cell'=>'H4','value'=>'EL SALVADOR CITY'],
                ['cell'=>'P4','value'=>'X'],
            ],
            6 => ['sheet'=>'Back',
                ['cell'=>'T3','value'=>'PEDRO SA BACULIO ELEMENTARY SCHOOL'],
                ['cell'=>'AG3','value'=>'127677'],
                ['cell'=>'T4','value'=>'II'],
                ['cell'=>'X4','value'=>'EL SALVADOR CITY'],
                ['cell'=>'AH4','value'=>'X'],
            ],
        ];
        foreach ($byGrade as $g => $_) {
            if (!isset($SCHOOL_META[$g])) continue;
            $meta = $SCHOOL_META[$g];
            $metaSheet = ($meta['sheet'] === 'Back' && $sheetBack) ? $sheetBack : $sheetFront;
            foreach ($meta as $entry) {
                if (!is_array($entry) || !isset($entry['cell'])) continue;
                $metaSheet->setCellValue($entry['cell'], $entry['value']);
            }
        }

        /* ================================================================
         * Subject export layouts & columns
         * ================================================================ */

        $SUBJECT_DEFS = [
            'Mother Tongue' => [
                'label'=>'Mother Tongue',
                'aliases'=>['Mother Tongue','Mother Tounge','MTB-MLE','MTBMLE','MTB','Mother Tongue/MTB-MLE'],
            ],
            'Filipino' => ['label'=>'Filipino','aliases'=>['Filipino','FIL','Fil']],
            'English'  => ['label'=>'English','aliases'=>['English','ENG']],
            'Language' => ['label'=>'Language','aliases'=>['Language','LANG']],
            'Reading & Literacy' => [
                'label'=>'Reading & Literacy',
                'aliases'=>['Reading & Literacy','Reading','Literacy','READ','RLIT'],
            ],
            'Mathematics' => ['label'=>'Mathematics','aliases'=>['Mathematics','Math','MATH']],
            'Science'     => ['label'=>'Science','aliases'=>['Science','SCI','Science & Health','Sci']],
            'Makabansa/ Araling Panlipunan' => [
                'label'=>'Makabansa/ Araling Panlipunan',
                'aliases'=>['Makabansa/ Araling Panlipunan','Makabansa / Araling Panlipunan','Araling Panlipunan','AP','Makabansa','Makabayan'],
            ],
            'EPP / TLE' => [
                'label'=>'EPP / TLE',
                'aliases'=>['EPP / TLE','EPP/TLE','EPP','TLE','Edukasyong Pantahanan at Pangkabuhayan','Home Economics and Livelihood Education','HELE'],
            ],
            'MAPEH' => [
                'label'=>'MAPEH',
                'aliases'=>['MAPEH','M.A.P.E.H','MAPE','Music, Arts, Physical Education and Health','Music Arts PE Health'],
            ],
            '*Music'  => ['label'=>'*Music','aliases'=>['Music','MUSIC','Musika']],
            '*Arts'   => ['label'=>'*Arts','aliases'=>['Arts','ARTS','Art','Sining']],
            '*Physical Education' => ['label'=>'*Physical Education','aliases'=>['Physical Education','PE','P.E.','Pisikal na Edukasyon']],
            '*Health' => ['label'=>'*Health','aliases'=>['Health','HEALTH','Kalusugan']],
            'GMRC / ESP' => [
                'label'=>'GMRC / ESP',
                'aliases'=>['GMRC','G.M.R.C','ESP','EsP','Edukasyon sa Pagpapakatao','Good Moral and Right Conduct'],
            ],
        ];

        $COLS    = ['subj'=>'B',  'q1'=>'K',  'q2'=>'L',  'q3'=>'N',  'q4'=>'O',  'final'=>'P',  'remarks'=>'S'];
        $COLS_G2 = ['subj'=>'AA', 'q1'=>'AJ', 'q2'=>'AM', 'q3'=>'AO', 'q4'=>'AR', 'final'=>'AT', 'remarks'=>'AW'];
        $COLS_G4 = ['subj'=>'V',  'q1'=>'AJ', 'q2'=>'AM', 'q3'=>'AO', 'q4'=>'AR', 'final'=>'AT', 'remarks'=>'AW'];
        $COLS_G5 = ['subj'=>'B',  'q1'=>'H',  'q2'=>'I',  'q3'=>'J',  'q4'=>'K',  'final'=>'L',  'remarks'=>'O'];
        $COLS_G6 = ['subj'=>'S',  'q1'=>'AB', 'q2'=>'AD', 'q3'=>'AE', 'q4'=>'AF', 'final'=>'AG', 'remarks'=>'AH'];

        $GRADE_LAYOUTS = [
            1 => ['sheet'=>'Front','cols'=>$COLS,'rows'=>[
                'Mother Tongue'=>30, 'Language'=>31, 'Reading & Literacy'=>32, 'Mathematics'=>33, 'Science'=>34,
                'Makabansa/ Araling Panlipunan'=>35, 'EPP / TLE'=>36, 'MAPEH'=>37, '*Music'=>38, '*Arts'=>39,
                '*Physical Education'=>40, '*Health'=>41, 'GMRC / ESP'=>42,
            ]],
            2 => ['sheet'=>'Front','cols'=>$COLS_G2,'rows'=>[
                'Mother Tongue'=>30, 'Filipino'=>31, 'English'=>32, 'Mathematics'=>33, 'Science'=>34,
                'Makabansa/ Araling Panlipunan'=>35, 'EPP / TLE'=>36, 'MAPEH'=>37, '*Music'=>38, '*Arts'=>39,
                '*Physical Education'=>40, '*Health'=>41, 'GMRC / ESP'=>42,
            ]],
            3 => ['sheet'=>'Front','cols'=>$COLS,'rows'=>[
                'Mother Tongue'=>60, 'Filipino'=>61, 'English'=>62, 'Mathematics'=>63, 'Science'=>64,
                'Makabansa/ Araling Panlipunan'=>65, 'EPP / TLE'=>66, 'MAPEH'=>67, '*Music'=>68, '*Arts'=>69,
                '*Physical Education'=>70, '*Health'=>71, 'GMRC / ESP'=>72,
            ]],
            4 => ['sheet'=>'Front','cols'=>$COLS_G4,'rows'=>[
                'Mother Tongue'=>60, 'Filipino'=>61, 'English'=>62, 'Mathematics'=>63, 'Science'=>64,
                'Makabansa/ Araling Panlipunan'=>65, 'EPP / TLE'=>66, 'MAPEH'=>67, '*Music'=>68, '*Arts'=>69,
                '*Physical Education'=>70, '*Health'=>71, 'GMRC / ESP'=>72,
            ]],
            5 => ['sheet'=>'Back','cols'=>$COLS_G5,'rows'=>[
                'Mother Tongue'=>10, 'Filipino'=>11, 'English'=>12, 'Mathematics'=>13, 'Science'=>14,
                'Makabansa/ Araling Panlipunan'=>15, 'EPP / TLE'=>16, 'MAPEH'=>17, '*Music'=>18, '*Arts'=>19,
                '*Physical Education'=>20, '*Health'=>21, 'GMRC / ESP'=>22,
            ]],
            6 => ['sheet'=>'Back','cols'=>$COLS_G6,'rows'=>[
                'Mother Tongue'=>10, 'Filipino'=>11, 'English'=>12, 'Mathematics'=>13, 'Science'=>14,
                'Makabansa/ Araling Panlipunan'=>15, 'EPP / TLE'=>16, 'MAPEH'=>17, '*Music'=>18, '*Arts'=>19,
                '*Physical Education'=>20, '*Health'=>21, 'GMRC / ESP'=>22,
            ]],
        ];

        // Remedial cell maps per grade (exact per your instructions)
        $REMEDIAL_MAPS = [
            1 => ['sheet'=>'Front', 'from'=>'K47', 'to'=>'P47', 'rows'=>[
                    ['subj'=>'B49','fr'=>'G49','rcm'=>'K49','rfg'=>'O49','rem'=>'S49'],
                    ['subj'=>'B50','fr'=>'G50','rcm'=>'K50','rfg'=>'O50','rem'=>'S50'],
                ]],
            2 => ['sheet'=>'Front', 'from'=>'AJ47','to'=>'AS47','rows'=>[
                    ['subj'=>'V49','fr'=>'AA49','rcm'=>'AJ49','rfg'=>'AQ49','rem'=>'AW49'],
                    ['subj'=>'V50','fr'=>'AA50','rcm'=>'AJ50','rfg'=>'AQ50','rem'=>'AW50'],
                ]],
            3 => ['sheet'=>'Front', 'from'=>'K77','to'=>'P77','rows'=>[
                    ['subj'=>'B79','fr'=>'G79','rcm'=>'K79','rfg'=>'O79','rem'=>'S79'],
                    ['subj'=>'B80','fr'=>'G80','rcm'=>'K80','rfg'=>'O80','rem'=>'S80'],
                ]],
            4 => ['sheet'=>'Front', 'from'=>'AJ77','to'=>'AS77','rows'=>[
                    ['subj'=>'V79','fr'=>'AA79','rcm'=>'AJ79','rfg'=>'AQ79','rem'=>'AW79'],
                    ['subj'=>'V80','fr'=>'AA80','rcm'=>'AJ80','rfg'=>'AQ80','rem'=>'AW80'],
                ]],
            5 => ['sheet'=>'Back',  'from'=>'J27','to'=>'N27','rows'=>[
                    ['subj'=>'B29','fr'=>'F29','rcm'=>'H29','rfg'=>'K29','rem'=>'O29'],
                    ['subj'=>'B30','fr'=>'F30','rcm'=>'H30','rfg'=>'K30','rem'=>'O30'],
                ]],
            6 => ['sheet'=>'Back',  'from'=>'AD27','to'=>'AH27','rows'=>[
                    ['subj'=>'S29','fr'=>'W29','rcm'=>'AC29','rfg'=>'AF29','rem'=>'AH29'],
                    ['subj'=>'S30','fr'=>'W30','rcm'=>'AC30','rfg'=>'AF30','rem'=>'AH30'],
                ]],
        ];

        // Determine grades present
        $gradesToExport = array_keys($byGrade);
        sort($gradesToExport);

        foreach ($gradesToExport as $g) {
            if (!isset($GRADE_LAYOUTS[$g])) continue;

            $layout      = $GRADE_LAYOUTS[$g];
            $sheetTag    = $layout['sheet'];
            $rowsMap     = $layout['rows'];
            $colsForG    = $layout['cols'] ?? $COLS;
            $targetSheet = ($sheetTag === 'Back' && $sheetBack) ? $sheetBack : $sheetFront;

            // Query context for that grade
            $gradeSectionId    = $byGrade[$g]['section_id']    ?? $sectionId;
            $gradeCurriculumId = $byGrade[$g]['curriculum_id'] ?? $curriculumId;
            $subjectTable      = 'subjects';

            $gaFinals   = [];
            $mapehFinal = null;
            $mapehParts = [];

            foreach ($SUBJECT_DEFS as $key => $def) {
                if (!isset($rowsMap[$key])) continue;
                $row = (int)$rowsMap[$key];

              // --- THIS IS THE NEW, CORRECTED CODE BLOCK ---
$gRow = $this->fetchSubjectGrades($studentId, $gradeSectionId, $gradeCurriculumId, $def['aliases'], $subjectTable);
if ((!$gRow || ((!($gRow['q1'] ?? null)) && (!($gRow['q2'] ?? null)) && (!($gRow['q3'] ?? null)) && (!($gRow['q4'] ?? null)))) && $key === 'MAPEH') {
    $gRow = $this->composeMapehFromParts($studentId,$gradeSectionId,$gradeCurriculumId,$subjectTable,$SUBJECT_DEFS) ?? $gRow;
}

$q1 = ($gRow && ($gRow['q1'] ?? '') !== '') ? (float)$gRow['q1'] : null;
$q2 = ($gRow && ($gRow['q2'] ?? '') !== '') ? (float)$gRow['q2'] : null;
$q3 = ($gRow && ($gRow['q3'] ?? '') !== '') ? (float)$gRow['q3'] : null;
$q4 = ($gRow && ($gRow['q4'] ?? '') !== '') ? (float)$gRow['q4'] : null;

// Get both the original and the recomputed grades
$originalFinal   = ($gRow && ($gRow['original_final'] ?? '') !== '') ? (float)$gRow['original_final'] : null;
$recomputedFinal = ($gRow && ($gRow['recomputed_final'] ?? '') !== '') ? (float)$gRow['recomputed_final'] : null;

// This grade will be written in the subject's "Final" column (e.g., 70)
$gradeForDisplay = $originalFinal;

// This grade will be used for the General Average calculation (e.g., 80)
$gradeForCalculation = $originalFinal;

// Write the original grade to the Excel sheet
$this->writeSubjectRow($targetSheet, $colsForG, $row, $def['label'], $q1,$q2,$q3,$q4, $gradeForDisplay, (bool)($def['write_label'] ?? true));

// Use the recomputed grade for the General Average
$final = $gradeForCalculation !== null ? (int)round($gradeForCalculation, 0) : null;

$isComponent = (strpos($key, '*') === 0);
if ($isComponent) {
    if ($final !== null) $mapehParts[] = (int)$final;
} else {
    if (strcasecmp($key, 'MAPEH') === 0) {
        $mapehFinal = ($final !== null) ? (int)$final : null;
    } elseif ($final !== null) {
        $gaFinals[] = (int)$final;
    }
}
            }

            if ($mapehFinal !== null) $gaFinals[] = $mapehFinal;
            elseif (count($mapehParts) > 0) $gaFinals[] = (int) round(array_sum($mapehParts)/count($mapehParts), 0);

            $GA = $gaFinals ? (int) round(array_sum($gaFinals)/count($gaFinals), 0) : null;
            $failedSubjectsCount = count(array_filter($gaFinals, fn($grade) => $grade < 75));

            $finalRemark = '';
if ($GA !== null) {
    if ($failedSubjectsCount >= 3) {
        $finalRemark = 'RETAINED';
    } elseif ($GA >= 75) { // 75 is the passing grade
        $finalRemark = 'PROMOTED';
    } else {
        $finalRemark = 'FAILED';
    }
}
            // Totals/summary rows
            switch ($g) {
                case 1:
                    $avgK = $this->averageRange($sheetFront, 'K', 30, 44);
                    $avgL = $this->averageRange($sheetFront, 'L', 30, 44);
                    $avgN = $this->averageRange($sheetFront, 'N', 30, 44);
                    $avgO = $this->averageRange($sheetFront, 'O', 30, 44);
                    foreach (['K45'=>$avgK,'L45'=>$avgL,'N45'=>$avgN,'O45'=>$avgO,'P45'=>$GA] as $addr=>$val) {
                        $sheetFront->getStyle($addr)->getNumberFormat()->setFormatCode('0');
                        $sheetFront->setCellValue($addr, $val===null ? '' : (int)round($val,0));
                    }
                   $sheetFront->setCellValue('S45', $finalRemark);
                    break;

                case 2:
                    $avgAJ = $this->averageRange($sheetFront, 'AJ', 30, 44);
                    $avgAM = $this->averageRange($sheetFront, 'AM', 30, 44);
                    $avgAO = $this->averageRange($sheetFront, 'AO', 30, 44);
                    $avgAR = $this->averageRange($sheetFront, 'AR', 30, 44);
                    foreach (['AJ45'=>$avgAJ,'AM45'=>$avgAM,'AO45'=>$avgAO,'AR45'=>$avgAR,'AT45'=>$GA] as $addr=>$val) {
                        $sheetFront->getStyle($addr)->getNumberFormat()->setFormatCode('0');
                        $sheetFront->setCellValue($addr, $val===null ? '' : (int)round($val,0));
                    }
                   $sheetFront->setCellValue('AW45', $finalRemark);
                    break;

                case 3:
                    $avgK3 = $this->averageRange($sheetFront, 'K', 60, 74);
                    $avgL3 = $this->averageRange($sheetFront, 'L', 60, 74);
                    $avgN3 = $this->averageRange($sheetFront, 'N', 60, 74);
                    $avgO3 = $this->averageRange($sheetFront, 'O', 60, 74);
                    foreach (['K75'=>$avgK3,'L75'=>$avgL3,'N75'=>$avgN3,'O75'=>$avgO3,'P75'=>$GA] as $addr=>$val) {
                        $sheetFront->getStyle($addr)->getNumberFormat()->setFormatCode('0');
                        $sheetFront->setCellValue($addr, $val===null ? '' : (int)round($val,0));
                    }
                   $sheetFront->setCellValue('S75', $finalRemark);
                    break;

                case 4:
                    $avgAJ4 = $this->averageRange($sheetFront, 'AJ', 60, 74);
                    $avgAM4 = $this->averageRange($sheetFront, 'AM', 60, 74);
                    $avgAO4 = $this->averageRange($sheetFront, 'AO', 60, 74);
                    $avgAR4 = $this->averageRange($sheetFront, 'AR', 60, 74);
                    foreach (['AJ75'=>$avgAJ4,'AM75'=>$avgAM4,'AO75'=>$avgAO4,'AR75'=>$avgAR4,'AT75'=>$GA] as $addr=>$val) {
                        $sheetFront->getStyle($addr)->getNumberFormat()->setFormatCode('0');
                        $sheetFront->setCellValue($addr, $val===null ? '' : (int)round($val,0));
                    }
                    $sheetFront->setCellValue('AW75', $finalRemark);
                    break;

                case 5:
                    $back = $sheetBack ?: $sheetFront;
                    $avgH5 = $this->averageRange($back, 'H', 10, 24);
                    $avgI5 = $this->averageRange($back, 'I', 10, 24);
                    $avgJ5 = $this->averageRange($back, 'J', 10, 24);
                    $avgK5 = $this->averageRange($back, 'K', 10, 24);
                    foreach (['H25'=>$avgH5,'I25'=>$avgI5,'J25'=>$avgJ5,'K25'=>$avgK5,'L25'=>$GA] as $addr=>$val) {
                        $back->getStyle($addr)->getNumberFormat()->setFormatCode('0');
                        $back->setCellValue($addr, $val===null ? '' : (int)round($val,0));
                    }
                    $sheetFront->setCellValue('O25', $finalRemark);
                    break;

                case 6:
                    $back = $sheetBack ?: $sheetFront;
                    $avgAB6 = $this->averageRange($back, 'AB', 10, 24);
                    $avgAD6 = $this->averageRange($back, 'AD', 10, 24);
                    $avgAE6 = $this->averageRange($back, 'AE', 10, 24);
                    $avgAF6 = $this->averageRange($back, 'AF', 10, 24);
                    foreach (['AB25'=>$avgAB6,'AD25'=>$avgAD6,'AE25'=>$avgAE6,'AF25'=>$avgAF6,'AG25'=>$GA] as $addr=>$val) {
                        $back->getStyle($addr)->getNumberFormat()->setFormatCode('0');
                        $back->setCellValue($addr, $val===null ? '' : (int)round($val,0));
                    }
                   $sheetFront->setCellValue('AH25', $finalRemark);
                    break;
            }

            // ==== Remedial blocks per grade (only if remarks exist) ====
            if (isset($REMEDIAL_MAPS[$g])) {
                $map = $REMEDIAL_MAPS[$g];
                $sheetForRem = ($map['sheet'] === 'Back' && $sheetBack) ? $sheetBack : $sheetFront;
                $remRows = $this->fetchRemedialsWithRemarks($studentId, $gradeSectionId, $gradeCurriculumId);
                $this->writeRemedialsBlock($sheetForRem, $map, $remRows);
            }
        }

        // 13) Stream the workbook
        $safeLast  = preg_replace('/[^A-Za-z0-9\- ]+/', '', (string)$lastName);
        $safeFirst = preg_replace('/[^A-Za-z0-9\- ]+/', '', (string)$firstName);
        $filename  = 'SF10-' . (($stu['LRN'] ?? '') ?: ($safeLast . '-' . $safeFirst)) . '.xlsx';

        if (ob_get_length()) { @ob_end_clean(); }
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        header('Pragma: public');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
