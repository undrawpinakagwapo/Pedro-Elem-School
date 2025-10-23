<?php
// components/StudentAttendanceController/ExportSf2.php
trait ExportSf2Trait
{
    /** ===== EXCEL EXPORT (SF2) ===== */
    public function export(){
        $req = getRequestAll();

        $uid = $this->uid();
        $ut  = $this->utype();

        $sectionId    = isset($req['section_id'])    ? (int)$req['section_id']    : 0;
        $curriculumId = isset($req['curriculum_id']) ? (int)$req['curriculum_id'] : 0;
        $date         = isset($req['date']) ? trim((string)$req['date']) : date('Y-m-d');

        if (!$sectionId || !$curriculumId || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            http_response_code(400); echo 'Missing/invalid required fields.'; return;
        }
        if ($ut == 2 && !$this->assertTeacherAccess($uid, $sectionId, $curriculumId)) {
            http_response_code(403); echo 'You are not assigned to this section/curriculum.'; return;
        }

        // MULTI-HOLIDAY support: parse holidays=YYYY-MM-DD,YYYY-MM-DD,...
        $holidayDatesSet = [];
        if (!empty($req['holidays'])) {
            $parts = preg_split('/[,\s]+/', (string)$req['holidays'], -1, PREG_SPLIT_NO_EMPTY);
            foreach ($parts as $p) {
                $p = trim($p);
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $p)) $holidayDatesSet[$p] = true;
            }
        }
        // Back-compat: single "holiday=1" means mark the currently selected $date
        $isHolidayReq = isset($req['holiday']) && (string)$req['holiday'] === '1';
        if ($isHolidayReq) $holidayDatesSet[$date] = true;

        /* 1) Students (include IDs & gender) */
        $students = $this->db->Select(
            "SELECT u.user_id AS id,
                    u.account_last_name   AS last,
                    u.account_first_name  AS first,
                    u.account_middle_name AS middle,
                    u.gender              AS gender
               FROM registrar_student rs
               JOIN users u ON u.user_id = rs.student_id
              WHERE rs.deleted = 0
                AND rs.status  = 1
                AND rs.section_id    = ?
                AND rs.curriculum_id = ?
           ORDER BY u.account_last_name, u.account_first_name",
            [$sectionId, $curriculumId]
        );

        // Partition by gender (normalized + dedup)
        $normG = function($g){
            $g = strtoupper(trim((string)$g));
            $g = preg_replace('/\s+/', '', $g);
            if ($g === 'M' || $g === 'MALE' || $g === 'BOY') return 'M';
            if ($g === 'F' || $g === 'FEMALE' || $g === 'GIRL') return 'F';
            return '';
        };
        $boys=[]; $girls=[]; $seen=[];
        foreach ($students as $s){
            $sid = (int)($s['id'] ?? 0);
            if(!$sid || isset($seen[$sid])) continue;
            $seen[$sid]=true;
            $g = $normG($s['gender'] ?? '');
            if ($g==='M') $boys[] = $s;
            if ($g==='F') $girls[] = $s;
        }
        $boysCount   = count($boys);
        $girlsCount  = count($girls);
        $totalCount  = $boysCount + $girlsCount;

        $maleIds   = array_map(fn($x)=>(int)$x['id'], $boys);
        $femaleIds = array_map(fn($x)=>(int)$x['id'], $girls);
        $isMale    = array_fill_keys($maleIds,   true);
        $isFemale  = array_fill_keys($femaleIds, true);

        /* 2) Month frame */
        $dtBase = new DateTime($date);
        $year   = (int)$dtBase->format('Y');
        $month  = (int)$dtBase->format('n');

        $firstDay     = new DateTime(sprintf('%04d-%02d-01', $year, $month));
        $dowFirst     = (int)$firstDay->format('N'); // 1..7 (Mon=1)
        $firstWeekMon = (clone $firstDay)->modify('-'.($dowFirst - 1).' days'); // Monday of the week containing the 1st
        $lastDay      = (clone $firstDay)->modify('last day of this month');

        /* 3) Load template */
        $templatePath = __DIR__ . '/templates/School Form 2 (SF2) Daily Attendance Report of Learners.xlsx';
        if (!file_exists($templatePath)) { http_response_code(500); echo "Template not found at: " . htmlspecialchars($templatePath); return; }
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($templatePath);
        $sheet       = $spreadsheet->getSheetByName('School Form 2 (SF2)') ?: $spreadsheet->getActiveSheet();

        /* 3a) Top info */
        $syRow = $this->db->Select("SELECT school_year FROM curriculum WHERE id=? LIMIT 1", [$curriculumId]);
        $schoolYear = $syRow ? ($syRow[0]['school_year'] ?? '') : '';
        theSheet:
        $sheet->setCellValue('K6', $schoolYear);
        $sheet->getStyle('K6')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $monthName = strtoupper($dtBase->format('F'));
        $sheet->setCellValue('X6', $monthName);
        $sheet->getStyle('X6')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $secInfo = $this->db->Select(
            "SELECT s.name AS section_name, gl.name AS grade_name
               FROM section s
               JOIN grade_level gl ON gl.id = s.grade_id
              WHERE s.id = ?
              LIMIT 1",
            [$sectionId]
        );
        $UC = fn($s)=>mb_strtoupper(trim((string)$s), 'UTF-8');
        $sheet->setCellValue('X8',  $UC($secInfo[0]['grade_name']  ?? ''));
        $sheet->setCellValue('AC8', $UC($secInfo[0]['section_name'] ?? ''));
        $sheet->getStyle('X8')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('AC8')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        /* 4) Clear merges intersecting A/B from row 14 down */
        $idxA = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString('A');
        $idxB = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString('B');
        foreach ($sheet->getMergeCells() as $range) {
            [$tl,$br] = explode(':',$range);
            $tlCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString(preg_replace('/\d+$/','',$tl));
            $tlRow = (int)preg_replace('/^\D+/','',$tl);
            $brCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString(preg_replace('/\d+$/','',$br));
            $brRow = (int)preg_replace('/^\D+/','',$br);
            $hit = (($idxA >= $tlCol && $idxA <= $brCol) || ($idxB >= $tlCol && $idxB <= $brCol)) && $brRow >= 14;
            if ($hit) $sheet->unmergeCells($range);
        }

        /* 5) Render boys/girls lists ... (unchanged) */
        $formatName = function($last,$first,$middle) use ($UC){
            $mi = ''; $m = trim((string)$middle);
            if ($m !== '') $mi = mb_strtoupper(mb_substr($m,0,1,'UTF-8'),'UTF-8').'.';
            return trim($UC($last).', '.$UC($first).($mi ? ' '.$mi : ''));
        };
        $styleNum  = ['alignment'=>['horizontal'=>\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,'vertical'=>\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]];
        $styleName = ['alignment'=>['horizontal'=>\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT, 'vertical'=>\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]];

        $rowIndexByStudentId = [];
        $rowBoysStart = 14;
        $r = $rowBoysStart; $n = 1;
        foreach ($boys as $s) {
            $sheet->setCellValue('A'.$r, $n);
            $sheet->getStyle('A'.$r)->applyFromArray($styleNum);
            $sheet->setCellValue('B'.$r, $formatName($s['last']??'',$s['first']??'',$s['middle']??''));
            $sheet->getStyle('B'.$r)->applyFromArray($styleName);
            $rowIndexByStudentId[(int)$s['id']] = $r;
            $r++; $n++;
        }
        $lastBoyRow = $r - 1;

        $rowGirlsStart = 36;
        $rg = $rowGirlsStart; $ng = 1;
        foreach ($girls as $s) {
            $sheet->setCellValue('A'.$rg, $ng);
            $sheet->getStyle('A'.$rg)->applyFromArray($styleNum);
            $sheet->setCellValue('B'.$rg, $formatName($s['last']??'',$s['first']??'',$s['middle']??''));
            $sheet->getStyle('B'.$rg)->applyFromArray($styleName);
            $rowIndexByStudentId[(int)$s['id']] = $rg;
            $rg++; $ng++;
        }
        $lastGirlRow = $rg - 1;

        /* 7) Dates/Weekdays grid ... (unchanged up to building $letters) */
        $startCol = 'D'; $endCol='AB'; $rowDates=11; $rowDays=12;
        $startIdx = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($startCol);
        $endIdx   = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($endCol);

        $letters = [];
        for ($i = $startIdx; $i <= $endIdx; $i++) {
            $letters[] = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
        }

        // weekday headers + date numbers  (unchanged)
        $dayTags = ['M','T','W','TH','F'];
        for ($w = 0; $w < 5; $w++) for ($d = 0; $d < 5; $d++) $sheet->setCellValue($letters[$w*5 + $d].$rowDays, $dayTags[$d]);
        for ($w = 0; $w < 5; $w++) for ($d = 0; $d < 5; $d++) {
            $col = $letters[$w*5 + $d];
            $thisDate = (clone $firstWeekMon)->modify('+' . ($w*7 + $d) . ' days');
            $sheet->setCellValue($col.$rowDates, ((int)$thisDate->format('n') === $month) ? (int)$thisDate->format('j') : '');
        }
        $sheet->getStyle($startCol.$rowDates.':'.$endCol.$rowDays)->getFont()->setBold(true);
        $sheet->getStyle($startCol.$rowDates.':'.$endCol.$rowDays)->getAlignment()
              ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
              ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        $colByDate = function(string $ymd) use ($month, $firstWeekMon, $letters) {
            $d = DateTime::createFromFormat('Y-m-d', $ymd);
            if (!$d || (int)$d->format('n') !== $month) return null;
            $dow = (int)$d->format('N'); if ($dow < 1 || $dow > 5) return null;
            $daysDiff  = (int)$firstWeekMon->diff($d)->format('%r%a');
            if ($daysDiff < 0) return null;
            $weekIndex = intdiv($daysDiff, 7); if ($weekIndex < 0 || $weekIndex > 4) return null;
            $dayIndex  = $dow - 1; $i = $weekIndex*5 + $dayIndex;
            return $letters[$i] ?? null;
        };

        /* 8) Diagonals (unchanged) */
        if ($lastBoyRow >= $rowBoysStart) {
            $st = $sheet->getStyle($startCol.$rowBoysStart.':'.$endCol.$lastBoyRow);
            $st->getBorders()->setDiagonalDirection(\PhpOffice\PhpSpreadsheet\Style\Borders::DIAGONAL_UP);
            $st->getBorders()->getDiagonal()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DASHED)->getColor()->setARGB('FF000000');
        }
        if ($lastGirlRow >= $rowGirlsStart) {
            $st = $sheet->getStyle($startCol.$rowGirlsStart.':'.$endCol.$lastGirlRow);
            $st->getBorders()->setDiagonalDirection(\PhpOffice\PhpSpreadsheet\Style\Borders::DIAGONAL_UP);
            $st->getBorders()->getDiagonal()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DASHED)->getColor()->setARGB('FF000000');
        }

        /* 9) Draw triangles + NEW: fractional Absences */
        $colPixels = function(string $col) use ($sheet): int {
            $w = $sheet->getColumnDimension($col)->getWidth();
            if ($w === -1) { $w = $sheet->getDefaultColumnDimension()->getWidth(); }
            return max(1, (int)(($w <= 1.0) ? floor(12 * (float)$w + 0.5) : floor(7 * (float)$w + 5)));
        };
        $rowPixels = function(int $row) use ($sheet): int {
            $h = $sheet->getRowDimension($row)->getRowHeight();
            if ($h === -1) { $h = $sheet->getDefaultRowDimension()->getRowHeight(); }
            return max(1, (int)round((float)$h * 96 / 72));
        };

        $monthStart = $firstDay->format('Y-m-01');
        $monthEnd   = (clone $firstDay)->modify('last day of this month')->format('Y-m-d');
        $rowsAtt = $this->db->Select(
            "SELECT student_id, attendance_date, am_status, pm_status, remarks
               FROM student_attendance
              WHERE section_id=? AND curriculum_id=? AND attendance_date BETWEEN ? AND ?",
            [$sectionId, $curriculumId, $monthStart, $monthEnd]
        );

        $makeTriangle = function(string $which){
            $w = 100; $h = 100;
            $im = imagecreatetruecolor($w, $h);
            imagesavealpha($im, true);
            $transparent = imagecolorallocatealpha($im, 0, 0, 0, 127);
            imagefill($im, 0, 0, $transparent);
            $black = imagecolorallocatealpha($im, 0, 0, 0, 0);
            @imageantialias($im, true);
            if ($which === 'FULL') imagefilledrectangle($im, 0, 0, $w, $h, $black);
            elseif ($which === 'AM') imagefilledpolygon($im, [0,0, $w,0, 0,$h], 3, $black);
            else imagefilledpolygon($im, [$w,$h, $w,0, 0,$h], 3, $black);
            return $im;
        };
        $imgAM   = $makeTriangle('AM');
        $imgPM   = $makeTriangle('PM');
        $imgFULL = $makeTriangle('FULL');

        $BORDER_PAD_X = 4; $BORDER_PAD_Y = 4; $FIX_RIGHT = 1; $FIX_BOTTOM = 1;

        // now floats
        $absentByStudent=[]; $tardyByStudent=[];
        foreach ($rowIndexByStudentId as $sid => $_) { $absentByStudent[(int)$sid]=0.0; $tardyByStudent[(int)$sid]=0; }

        // SCHOOL DAYS (Mon–Fri within month, excluding holidays)
        $schoolDays = []; $schoolDaySet = [];
        for ($d = clone $firstDay; $d <= $lastDay; $d->modify('+1 day')) {
            $ymd = $d->format('Y-m-d');
            $dow = (int)$d->format('N');
            if ($dow >= 1 && $dow <= 5 && empty($holidayDatesSet[$ymd])) {
                $schoolDays[] = $ymd;
                $schoolDaySet[$ymd] = true;
            }
        }
        $D = (int)count($schoolDays);

        $halfByStudent = [];
        $presentMap    = [];

        // For drawing, compute holiday columns
        $holidayCols = [];
        foreach (array_keys($holidayDatesSet) as $ymd) {
            $cl = $colByDate($ymd);
            if ($cl) $holidayCols[$cl] = true;
        }

        foreach ($rowsAtt as $a){
            $sid    = (int)$a['student_id'];
            $ymd    = (string)$a['attendance_date'];
            $rowNum = $rowIndexByStudentId[$sid] ?? null;
            $colLtr = $colByDate($ymd);

            // Draw triangles (skip holiday columns) — shade ABSENCES instead of presence
            if ($rowNum && $colLtr && !isset($holidayCols[$colLtr])) {
                $am  = strtoupper((string)($a['am_status'] ?? 'A'));
                $pm  = strtoupper((string)($a['pm_status'] ?? 'A'));
                $img = null;

                $amPresent = ($am === 'P');
                $pmPresent = ($pm === 'P');

                // Shade rules:
                // - both present -> no shade
                // - AM absent only -> shade AM half
                // - PM absent only -> shade PM half
                // - both absent -> full shade
                if (!$amPresent && !$pmPresent) {
                    // Whole-day absent
                    $img = $imgFULL;
                } elseif (!$amPresent && $pmPresent) {
                    // Absent in AM only
                    $img = $imgAM;
                } elseif ($amPresent && !$pmPresent) {
                    // Absent in PM only
                    $img = $imgPM;
                } else {
                    // both present -> no image
                    $img = null;
                }

                if ($img){
                    $pxW  = $colPixels($colLtr);
                    $imgW = max(1, $pxW - $BORDER_PAD_X - $FIX_RIGHT);
                    $offX = (int) floor(($pxW - $imgW) / 2);

                    $pxH  = $rowPixels($rowNum);
                    $imgH = max(1, $pxH - $BORDER_PAD_Y - $FIX_BOTTOM);
                    $offY = (int) floor(($pxH - $imgH) / 2);

                    $md = new \PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing();
                    $md->setName('att'); $md->setDescription('attendance');
                    $md->setImageResource($img);
                    $md->setRenderingFunction(\PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing::RENDERING_PNG);
                    $md->setMimeType(\PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing::MIMETYPE_DEFAULT);
                    $md->setResizeProportional(false);
                    $md->setCoordinates($colLtr.$rowNum);
                    $md->setOffsetX($offX);
                    $md->setOffsetY($offY);
                    $md->setWidth($imgW);
                    $md->setHeight($imgH);
                    $md->setWorksheet($sheet);
                }
            }

            // ===== New absence/tardy logic (school days only) =====
            $am = strtoupper((string)($a['am_status'] ?? 'A'));
            $pm = strtoupper((string)($a['pm_status'] ?? 'A'));
            $rem = strtoupper(trim((string)($a['remarks'] ?? '')));

            if (isset($schoolDaySet[$ymd])) {
                // Tardy: still simple count of occurrences
                if ($rem === 'TARDY') {
                    $tardyByStudent[$sid] = (int)($tardyByStudent[$sid] ?? 0) + 1;
                }

                // Absences: 1 for whole-day, 0.5 for AM/PM only, else infer from statuses
                $absUnit = 0.0;
                if ($rem === 'ABSENT') {
                    $absUnit = 1.0;
                } elseif ($rem === 'ABSENT AM' || $rem === 'ABSENT PM') {
                    $absUnit = 0.5;
                } else {
                    // Fallback inference from statuses
                    $absUnit += ($am === 'P') ? 0.0 : 0.5;
                    $absUnit += ($pm === 'P') ? 0.0 : 0.5;
                }
                $absentByStudent[$sid] = (float)($absentByStudent[$sid] ?? 0.0) + (float)$absUnit;

                // ADA inputs & presence map
                $half = 0.0;
                if ($am === 'P') { $half += 0.5; }
                if ($pm === 'P') { $half += 0.5; }
                if (!isset($halfByStudent[$sid])) $halfByStudent[$sid] = 0.0;
                $halfByStudent[$sid] += $half;

                if (!isset($presentMap[$sid])) $presentMap[$sid] = [];
                $presentMap[$sid][$ymd] = ($am === 'P' || $pm === 'P');
            }
        }

        // Per-student totals (AC absent can be .5; AD tardy stays integer)
        $styleCenter = ['alignment'=>[
            'horizontal'=>\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            'vertical'  =>\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
        ]];
        foreach ($rowIndexByStudentId as $sid => $rowNum) {
            $sheet->setCellValueExplicit('AC'.$rowNum, (float)($absentByStudent[(int)$sid] ?? 0.0), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
            $sheet->setCellValueExplicit('AD'.$rowNum, (int)($tardyByStudent[(int)$sid]  ?? 0),   \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
            $sheet->getStyle('AC'.$rowNum.':AD'.$rowNum)->applyFromArray($styleCenter);
            // show one decimal for absences
            $sheet->getStyle('AC'.$rowNum)->getNumberFormat()->setFormatCode('0.0#');
        }

        /* totals rows (unchanged styling) */
        $applyTotalStyle = function(string $range, int $row) use ($sheet){
            $al = $sheet->getStyle($range)->getAlignment();
            $al->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $al->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            $al->setWrapText(false);
            $al->setTextRotation(0);
            $al->setShrinkToFit(false);
            $al->setIndent(0);
            $sheet->getStyle($range)->getNumberFormat()
                  ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_GENERAL);
            $sheet->getStyle($range)->getFont()->setBold(false);
            $sheet->getRowDimension($row)->setRowHeight(-1);
        };
        $applyTotalStyle('D35:AB35', 35);
        $applyTotalStyle('D61:AB61', 61);
        $applyTotalStyle('D62:AB62', 62);

        // totals by column incl. holidays (unchanged)

        for ($w = 0; $w < 5; $w++) {
            for ($d = 0; $d < 5; $d++) {
                $col      = $letters[$w * 5 + $d];
                $dateD    = (clone $firstWeekMon)->modify('+' . ($w * 7 + $d) . ' days');

                if ((int)$dateD->format('n') !== $month) {
                    $sheet->setCellValue($col.'35', '');
                    $sheet->setCellValue($col.'61', '');
                    $sheet->setCellValue($col.'62', '');
                    continue;
                }

                if (isset($holidayCols[$col])) {
                    $sheet->setCellValue($col.'35', '');
                    $sheet->setCellValue($col.'61', '');
                    $sheet->setCellValue($col.'62', '');

                    $word = ['H','O','L','I','D','A','Y'];
                    for ($k = 0; $k < count($word); $k++) {
                        $r = 14 + $k;
                        $sheet->setCellValueExplicit($col.$r, $word[$k], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                        $sheet->getStyle($col.$r)->getAlignment()
                              ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                              ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                        $sheet->getStyle($col.$r)->getFont()->setBold(false);
                    }
                    $rng = $col.'14:'.$col.'20';
                    $b = $sheet->getStyle($rng)->getBorders();
                    $b->setDiagonalDirection(\PhpOffice\PhpSpreadsheet\Style\Borders::DIAGONAL_NONE);
                    $b->getDiagonal()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_NONE);
                } else {
                    $sheet->setCellValueExplicit($col.'35', $boysCount,  \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                    $sheet->setCellValueExplicit($col.'61', $girlsCount, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                    $sheet->setCellValueExplicit($col.'62', $totalCount, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                }
            }
        }

        /* 11) Absent/Tardy totals (male, female, grand) into AC/AD */
        $sumFor = function(array $people, array $byKey){
            $sum = 0.0;
            foreach ($people as $p) { $sum += (float)($byKey[(int)$p['id']] ?? 0.0); }
            return $sum;
        };
        $maleAbsent   = $sumFor($boys,  $absentByStudent);
        $maleTardy    = (int)$sumFor($boys,  $tardyByStudent);
        $femaleAbsent = $sumFor($girls, $absentByStudent);
        $femaleTardy  = (int)$sumFor($girls, $tardyByStudent);
        $totalAbsent  = $maleAbsent + $femaleAbsent;
        $totalTardy   = $maleTardy  + $femaleTardy;

        $sheet->setCellValueExplicit('AC35', $maleAbsent,    \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
        $sheet->setCellValueExplicit('AD35', $maleTardy,     \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
        $sheet->setCellValueExplicit('AC61', $femaleAbsent,  \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
        $sheet->setCellValueExplicit('AD61', $femaleTardy,   \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
        $sheet->setCellValueExplicit('AC62', $totalAbsent,   \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
        $sheet->setCellValueExplicit('AD62', $totalTardy,    \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);

        // one-decimal for Absences totals
        foreach (['AC35','AC61','AC62'] as $addr){
            $sheet->getStyle($addr)->getNumberFormat()->setFormatCode('0.0#');
        }
        foreach (['AD35','AD61','AD62'] as $addr){
            $sheet->getStyle($addr)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_GENERAL);
        }
        foreach (['AC35:AD35','AC61:AD61','AC62:AD62'] as $rng){
            $sheet->getStyle($rng)->getAlignment()
                  ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                  ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            $sheet->getStyle($rng)->getFont()->setBold(false);
        }

        /* ==== Summary cells & ADA (unchanged) ==== */
        $sheet->setCellValueExplicit('AH66', $boysCount,  \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
        $sheet->setCellValueExplicit('AI66', $girlsCount, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
        $sheet->setCellValueExplicit('AJ66', $totalCount, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);

        $sheet->setCellValueExplicit('AH70', $boysCount,  \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
        $sheet->setCellValueExplicit('AI70', $girlsCount, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
        $sheet->setCellValueExplicit('AJ70', $totalCount, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);

        if ($totalCount > 0) {
            $maleEnrolPct   = $boysCount  / $totalCount;
            $femaleEnrolPct = $girlsCount / $totalCount;
            $totalEnrolPct  = 1;
        } else { $maleEnrolPct = $femaleEnrolPct = $totalEnrolPct = 0; }
        $sheet->setCellValueExplicit('AH72', $maleEnrolPct,   \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
        $sheet->setCellValueExplicit('AI72', $femaleEnrolPct, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
        $sheet->setCellValueExplicit('AJ72', $totalEnrolPct,  \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);

        $maleHalfDays   = 0.0; $femaleHalfDays = 0.0;
        foreach ($halfByStudent as $sid => $half) {
            if (isset($isMale[$sid]))   $maleHalfDays   += (float)$half;
            if (isset($isFemale[$sid])) $femaleHalfDays += (float)$half;
        }
        $totalHalfDays = $maleHalfDays + $femaleHalfDays;

        // D = number of school days in month
        $den_male   = $boysCount   * $D;
        $den_female = $girlsCount  * $D;
        $den_total  = $totalCount  * $D;

        $ADA_rate_male   = ($den_male   > 0) ? ($maleHalfDays   / $den_male)   : 0;
        $ADA_rate_female = ($den_female > 0) ? ($femaleHalfDays / $den_female) : 0;
        $ADA_rate_total  = ($den_total  > 0) ? ($totalHalfDays  / $den_total)  : 0;

        $sheet->setCellValueExplicit('AH74', $ADA_rate_male,   \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
        $sheet->setCellValueExplicit('AI74', $ADA_rate_female, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
        $sheet->setCellValueExplicit('AJ74', $ADA_rate_total,  \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);

        $sheet->setCellValueExplicit('AH75', $ADA_rate_male,   \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
        $sheet->setCellValueExplicit('AI75', $ADA_rate_female, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
        $sheet->setCellValueExplicit('AJ75', $ADA_rate_total,  \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);

        // 5+ total absences now uses fractional totals too
        $maleFive = 0; foreach ($maleIds as $sid) if ((float)($absentByStudent[(int)$sid] ?? 0) >= 5.0) $maleFive++;
        $femaleFive = 0; foreach ($femaleIds as $sid) if ((float)($absentByStudent[(int)$sid] ?? 0) >= 5.0) $femaleFive++;
        $totalFive = $maleFive + $femaleFive;

        $sheet->setCellValueExplicit('AH77', $maleFive,   \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
        $sheet->setCellValueExplicit('AI77', $femaleFive, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
        $sheet->setCellValueExplicit('AJ77', $totalFive,  \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);

        /* unified styling (unchanged) */
        $sheet->getStyle('AH66:AJ83')->getFont()->setBold(false)->setSize(11);
        $sheet->getStyle('AH66:AJ83')->getAlignment()
              ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
              ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getStyle('AH66:AJ83')->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_GENERAL);
        $sheet->getStyle('AH72:AJ75')->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_PERCENTAGE_00);
        foreach (['AH','AI','AJ'] as $col) { if ($sheet->getColumnDimension($col)->getWidth() < 12) $sheet->getColumnDimension($col)->setWidth(12); }

        $style77 = $sheet->getStyle('AH77:AJ77');
        $style77->getAlignment()->setTextRotation(0)->setWrapText(false)->setShrinkToFit(false)
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $style77->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER);
        $style77->getFont()->setSize(11)->setBold(false);

        /* 12) Stream file */
        $filename = "SF2_Section{$sectionId}_Curr{$curriculumId}_".$dtBase->format('Y-m').".xlsx";
        if (function_exists('ob_get_length') && ob_get_length()) { @ob_end_clean(); }
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'.$filename.'"');
        header('Expires: 0');
        header('Pragma: no-cache');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Cache-Control: post-check=0, pre-check=0', false);

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit();
    }
}
