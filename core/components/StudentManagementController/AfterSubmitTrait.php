<?php
// components/StudentManagementController/AfterSubmitTrait.php

trait AfterSubmitTrait
{
    /* ========================== Autoload & Logging =========================== */

    /** Ensure QR classes are available (Composer first, then local PSR-4 fallback). */
    private function ensureQrAutoload(): void {
        // Try Composer autoload from /core/vendor if present
        $auto = __DIR__ . '/../../vendor/autoload.php';
        if (is_file($auto)) {
            require_once $auto;
        }

        // If still not available, register a minimal PSR-4 fallback loader
        if (!class_exists(\chillerlan\QRCode\QROptions::class, true)) {
            spl_autoload_register(function(string $class){
                $psr4 = [
                    'chillerlan\\QRCode\\'   => __DIR__ . '/../../vendor/chillerlan/php-qrcode/src/',
                    'chillerlan\\Settings\\' => __DIR__ . '/../../vendor/chillerlan/php-settings-container/src/',
                ];
                foreach ($psr4 as $prefix => $baseDir) {
                    if (strncmp($class, $prefix, strlen($prefix)) === 0) {
                        $rel  = substr($class, strlen($prefix));
                        $file = $baseDir . str_replace('\\', '/', $rel) . '.php';
                        if (is_file($file)) {
                            require $file;
                        }
                    }
                }
            }, false, true);
        }
    }

    /** write a tiny log for debugging into /uploads/qrcodes/qr.log */
    private function qrLog(string $msg): void {
        $logDir = __DIR__ . '/../../uploads/qrcodes';
        if (!is_dir($logDir)) @mkdir($logDir, 0775, true);
        @file_put_contents($logDir.'/qr.log', '['.date('Y-m-d H:i:s')."] {$msg}\n", FILE_APPEND);
    }

    /* ============================== Validators ============================== */

    /** LRN must be exactly 12 digits */
    private function isValidLrn($value): bool {
        $s = preg_replace('/\D+/', '', (string)$value);
        return (bool)preg_match('/^\d{12}$/', $s);
    }

    /* ============================== QR Handling ============================= */

    /**
     * Generate (if needed) QR for LRN and return its relative path, or null.
     * PNG saved under /uploads/qrcodes/{LRN}.png
     */
    private function ensureQrForLrn(?string $lrn): ?string {
        // Make sure classes are loadable
        $this->ensureQrAutoload();

        if (!$this->isValidLrn($lrn)) {
            $this->qrLog("skip: invalid LRN [{$lrn}]");
            return null;
        }

        if (!class_exists(\chillerlan\QRCode\QROptions::class, true)) {
            $this->qrLog("QR lib missing after fallback");
            return null;
        }

        $dir = __DIR__ . '/../../uploads/qrcodes';
        if (!is_dir($dir)) @mkdir($dir, 0775, true);

        $fname = preg_replace('/\D+/', '', (string)$lrn) . '.png';
        $abs   = $dir . '/' . $fname;
        $rel   = '/uploads/qrcodes/' . $fname;

        if (is_file($abs)) {
            $this->qrLog("reuse {$rel}");
            return $rel;
        }

        try {
            $opts = new \chillerlan\QRCode\QROptions([
                'outputType' => \chillerlan\QRCode\QRCode::OUTPUT_IMAGE_PNG,
                'eccLevel'   => \chillerlan\QRCode\Common\EccLevel::M,
                'scale'      => 8,
                'margin'     => 2,
            ]);
            (new \chillerlan\QRCode\QRCode($opts))->render($lrn, $abs);
            $this->qrLog("generated {$rel}");
            return $rel;
        } catch (\Throwable $e) {
            $this->qrLog("render error for {$lrn}: ".$e->getMessage());
            return null;
        }
    }

    /** After you know the user_id, force-save qr_code by user_id (guaranteed write). */
    private function saveQrByUserId(?int $userId, ?string $lrn): void {
        if (!$userId || !$this->isValidLrn($lrn)) { return; }
        $qr = $this->ensureQrForLrn($lrn);
        if ($qr === null) { return; }
        $this->db->Update("UPDATE users SET qr_code=? WHERE user_id=?", [$qr, (int)$userId]);
        $this->qrLog("wrote qr_code for user_id={$userId} -> {$qr}");
    }

    /** Find user id by LRN (helper for inserts where we didn’t have one) */
    private function getUserIdByLrn(?string $lrn): ?int {
        if (!$this->isValidLrn($lrn)) return null;
        $row = $this->db->Select("SELECT user_id FROM users WHERE LRN = ? AND deleted = 0 LIMIT 1", [$lrn]);
        return $row && isset($row[0]['user_id']) ? (int)$row[0]['user_id'] : null;
    }

    /* ====================== Existing Normalizers (kept) ===================== */

    /** Normalize DOB to Y-m-d */
    private function normalizeDobValue($raw) {
        if ($raw === null) return null;
        if (!is_numeric($raw)) {
            $raw = trim((string)$raw);
            if ($raw === '') return null;
        }
        $isValidParse = function ($dt): bool {
            if (!$dt instanceof \DateTime) return false;
            $errors = \DateTime::getLastErrors(); if ($errors === false) return true;
            return (($errors['warning_count'] ?? 0) === 0 && ($errors['error_count'] ?? 0) === 0);
        };
        $mdy = ['m-d-Y','m/d/Y','m.d.Y','M-d-Y','M/d-Y','M d Y','F-d-Y','F/d-Y','F d Y','M d, Y','F d, Y'];
        foreach ($mdy as $fmt) { $dt = \DateTime::createFromFormat($fmt, (string)$raw); if ($isValidParse($dt)) return $dt->format('Y-m-d'); }
        if (is_numeric($raw)) {
            $n = (float)$raw;
            if ($n > 20000 && $n < 100000) {
                try {
                    $dt = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($n);
                    if ($dt instanceof \DateTimeInterface) return $dt->format('Y-m-d');
                } catch (\Throwable $e) {}
            }
        }
        foreach (['Y-m-d','d-m-Y','d/m/Y','d.m.Y','d M Y','d-M-Y'] as $fmt) {
            $dt = \DateTime::createFromFormat($fmt, (string)$raw); if ($isValidParse($dt)) return $dt->format('Y-m-d');
        }
        $ts = strtotime((string)$raw); if ($ts !== false) return date('Y-m-d', $ts);
        return null;
    }

    /** Normalize gender to "Male"/"Female" (null if unknown) */
    private function normalizeGenderValue($raw) {
        $s = strtoupper(trim((string)$raw));
        if ($s === '' || $s === '0') return null;
        if (in_array($s, ['1','M','MALE','BOY'], true))   return 'Male';
        if (in_array($s, ['2','F','FEMALE','GIRL'], true)) return 'Female';
        if (preg_match('/^[A-Z]+$/', $s)) return ucfirst(strtolower($s));
        return null;
    }

    /* ================================ Main ================================= */

    // In C:\...\StudentManagementController\AfterSubmitTrait.php

    public function afterSubmit(){
    $req = getRequestAll();
    $table = "users";

    /* -------- BRANCH A: Manual modal (add/edit from the form) -------- */
    if (!isset($req['data'])) {
        // This part for manual add/edit is already correct.
        $user_id = trim((string)($req['user_id'] ?? ''));
        $update = $req;

        if ($user_id !== '') {
            // EDIT
            if (!empty($update['LRN'])) {
                $dupeLrn = $this->db->Select("SELECT user_id FROM users WHERE LRN = ? AND deleted = 0 AND user_id != ?", [$update['LRN'], $user_id]);
                if (count($dupeLrn) > 0) {
                    header('Location: index?type=warning&message=Another student with this LRN already exists!');
                    exit();
                }
            }
            if (!empty($update['email'])) {
                $dupeEmail = $this->db->Select("SELECT user_id FROM users WHERE email = ? AND deleted = 0 AND user_id != ?", [$update['email'], $user_id]);
                if (count($dupeEmail) > 0) {
                    header('Location: index?type=warning&message=Email already exists for another user!');
                    exit();
                }
            }

            if (!empty($update['password'])) {
                $update['password'] = password_hash($update['password'], PASSWORD_DEFAULT);
            } else {
                unset($update['password']);
            }

            $qrPath = $this->ensureQrForLrn($update['LRN'] ?? null);
            if ($qrPath) { $update['qr_code'] = $qrPath; }

            $this->db->updateField($table, $update, ['user_id' => $user_id]);
            header('Location: index?type=success&message=Student updated successfully!');
            exit();

        } else {
            // ===================== CREATE =====================

            // Check for duplicate LRN or email before creating
            if (!empty($req['LRN'])) {
                $exists = $this->db->Select("SELECT user_id FROM users WHERE deleted = 0 AND LRN = ?", [$req['LRN']]);
                if ($exists) {
                    header('Location: index?type=warning&message=A student with this LRN already exists.');
                    exit();
                }
            }
            if (!empty($req['email'])) {
                $exists = $this->db->Select("SELECT user_id FROM users WHERE deleted = 0 AND email = ?", [$req['email']]);
                if ($exists) {
                    header('Location: index?type=warning&message=A user with this email already exists.');
                    exit();
                }
            }

            // Enforce the default password formula: lastname + lrn
            $lastName = trim($req['account_last_name'] ?? '');
            $lrn = trim($req['LRN'] ?? '');
            if ($lastName === '' || !$this->isValidLrn($lrn)) {
                header('Location: index?type=warning&message=Last Name and a valid 12-digit LRN are required to create a student.');
                exit();
            }

            // Automatically set the password to lastname + LRN
            $defaultPassword = $lastName . $lrn;
            $update['password'] = password_hash($defaultPassword, PASSWORD_DEFAULT);

            // THE FIX: Explicitly set the username to be the LRN
            $update['username'] = $lrn;

            // Set user_type and other defaults
            $update['user_type'] = 5; // Student
            $update['token'] = generateToken();

            // Ensure QR code is generated
            $qrPath = $this->ensureQrForLrn($lrn);
            if ($qrPath) {
                $update['qr_code'] = $qrPath;
            }

            $this->db->insertRequestBatchRquest($update, $table);
            header('Location: index?type=success&message=Student created successfully!');
            exit();
        }
    }

    /* ----------------- BRANCH B: Import (payload from Excel) ----------------- */
    $items = $req['data'] ?? [];
    if (empty($items)) {
        header('Location: index?type=warning&message=No rows to process.');
        exit();
    }

        foreach ($items as $value) {
            $lrn = trim((string)($value["lrn"] ?? ''));
            if (!$this->isValidLrn($lrn)) {
                continue; 
            }

            $fullName = trim((string)($value["full_name"] ?? ''));
            $lastName = ''; $firstName = ''; $middleName = '';
            if (strpos($fullName, ',') !== false) {
                $parts = array_map('trim', explode(',', $fullName, 2));
                $lastName = $parts[0] ?? '';
                $nameParts = preg_split('/\s+/', $parts[1] ?? '');
                $firstName = $nameParts[0] ?? '';
                $middleName = implode(' ', array_slice($nameParts, 1));
            } else {
                $parts = preg_split('/\s+/', $fullName);
                if (count($parts) > 0) { $lastName = array_pop($parts); }
                if (count($parts) > 0) { $firstName = array_shift($parts); }
                $middleName = implode(' ', $parts);
            }

            $updateData = [
                'LRN' => $lrn,
                'account_first_name' => $firstName,
                'account_middle_name' => $middleName,
                'account_last_name' => $lastName,
                'gender' => $this->normalizeGenderValue($value["gender"] ?? null),
                'dateof_birth' => $this->normalizeDobValue($value["dateof_birth"] ?? null),
                'mother_tongue' => trim($value['mother_tongue'] ?? ''),
                'religion' => trim($value['religion'] ?? ''),
                'house_street_sitio_purok' => trim($value['house_street_sitio_purok'] ?? ''),
                'barangay' => trim($value['barangay'] ?? ''),
                'municipality_city' => trim($value['municipality_city'] ?? ''),
                'province' => trim($value['province'] ?? ''),
                'father_name' => trim($value['father_name'] ?? ''),
                'mother_name' => trim($value['mother_name'] ?? ''),
                'guardian' => trim($value['guardian'] ?? ''),
                'relationship' => trim($value['relationship'] ?? ''),
                'contact_no_of_parent' => trim($value['contact_no_of_parent'] ?? ''),
                'learning_modality' => trim($value['learning_modality'] ?? ''),
                'remarks' => trim($value['remarks'] ?? ''),
                'batch' => trim($value['batch'] ?? ''),
                'grade_level' => trim($value['grade_level'] ?? ''),
                'set_group' => trim($value['set_group'] ?? ''),
                'user_type' => 5, 'status' => 1, 'verify' => 1,
                'qr_code' => $this->ensureQrForLrn($lrn),
            ];

            $existingUser = $this->db->Select("SELECT user_id FROM users WHERE deleted = 0 AND LRN = ?", [$lrn]);
            if ($existingUser) {
                // UPDATE existing student
                
                // ✅ FINAL FIX: Explicitly remove login fields from the update array.
                // This prevents them from being erased if your db->updateField method is destructive.
                unset($updateData['password']);
                unset($updateData['username']);
                unset($updateData['email']);
                
                $this->db->updateField($table, $updateData, ['user_id' => $existingUser[0]['user_id']]);
            } else {
                // INSERT new student with default password
                $defaultPassword = $lastName . $lrn;
                $updateData['password'] = password_hash($defaultPassword, PASSWORD_DEFAULT);
                $updateData['username'] = $lrn; // Default username to LRN
                $updateData['token'] = generateToken();
                $this->db->insertRequestBatchRquest($updateData, $table);
            }
        }

        header('Location: index?type=success&message=Import processed successfully!');
        exit();
    }
}
