<?php 
    // Safe helpers (this part stays at the top)
    $val = fn($k) => htmlspecialchars($details[$k] ?? '');
    $host = $_ENV['URL_HOST'] ?? '';
    $imgSrc = htmlspecialchars($host . ($details['image'] ?? ''));

    // QR helpers
    $qrRel   = (string)($details['qr_code'] ?? '');
    $qrSrcDb = $qrRel ? ($host . $qrRel) : '';
    $lrnRaw  = (string)($details['LRN'] ?? '');
    $lrnDig  = preg_replace('/\D+/', '', $lrnRaw);
    $isLrnValid = (bool)preg_match('/^\d{12}$/', $lrnDig);
    $qrExpectedRel = $isLrnValid ? ("/uploads/qrcodes/{$lrnDig}.png") : '';
    $qrExpectedAbs = $qrExpectedRel ? ($host . $qrExpectedRel) : '';

    // Maps & labels
    $genderMap      = [1=>'Male', 2=>'Female'];
    $nationalityMap = [1=>'FILIPINO', 2=>'ALIEN'];
    $statusMap      = [1=>'Active', 0=>'Inactive'];

    // Display helpers
    $fullName = trim(($details['account_last_name'] ?? '').', '.($details['account_first_name'] ?? '').' '.($details['account_middle_name'] ?? ''));
    if ($fullName === ',  ') $fullName = '';
    $statusText = ((int)($details['status'] ?? 1) === 1) ? 'Active' : 'Inactive';
?>

<style>
/* ... all your CSS styles remain unchanged ... */
.md-wrap { --gap: 1rem; }
.md-wrap .section-title{ font-size: .95rem; font-weight: 800; color: #0f172a; letter-spacing:.2px; margin: 0 0 .5rem; }
.hr-soft{ border:0;height:1px;margin:1.1rem 0;background:linear-gradient(90deg,transparent,#e9eef6,transparent); }
.profile-card{ background: #fff; border: 1px solid #e8edf6; border-radius: 12px; overflow: hidden; box-shadow: 0 8px 18px rgba(15,23,42,.05); }
.profile-head{ display:flex; gap:.75rem; align-items:center; padding:.9rem 1rem; background: #f8fafc; border-bottom:1px solid #eef2f7; }
.profile-head .dot{ width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center; background:#e9efff;color:#2563eb;font-weight:800; }
.profile-body{ padding: 1rem; }
.avatar-wrap{ position:relative;border-radius:12px;overflow:hidden;background:#f1f5f9;border:1px dashed #ced6e3; }
.avatar-wrap img{ width:100%; height:220px; object-fit:cover; display:block; border-radius:12px; }
.avatar-overlay{ position:absolute;left:0;right:0;bottom:0;display:flex;align-items:center;justify-content:space-between; padding:.5rem .6rem;background:linear-gradient(180deg, transparent, rgba(0,0,0,.55)); color:#fff;font-size:.85rem; }
.avatar-actions .btn{ padding:.25rem .55rem; line-height:1.1; font-size:.8rem; border-radius:8px; border:1px solid rgba(255,255,255,.3); background: rgba(255,255,255,.15); color:#fff; }
.badge-chip{ display:inline-flex; align-items:center; gap:.4rem; font-size:.78rem; padding:.3rem .55rem; border-radius:999px; border:1px solid #e7ecf5; background:#f8fafc; color:#0f172a; }
.badge-chip .dot-sm{ width:7px; height:7px; border-radius:50%; background:#16a34a; }
.badge-chip.warn .dot-sm{ background:#f59e0b; }
.form-label{ font-weight:700; color:#0f172a; font-size:.88rem; }
.form-text{ color:#6b7280; font-size:.8rem; }
.form-control, .form-select{ border:1px solid #dbe3ef; border-radius:.55rem; padding:.58rem .75rem; font-size:.95rem; transition: box-shadow .15s, border-color .15s; }
.form-control:focus, .form-select:focus{ border-color:#86b7fe; box-shadow:0 0 0 .2rem rgba(13,110,253,.12); outline:0; }
.help{ font-size:.78rem; color:#64748b; margin-top:.25rem; }
.input-group-text { background:#f8fafc; border:1px solid #dbe3ef; color:#475569; }
.password-toggle{ cursor:pointer; user-select:none; color:#475569; }
.password-toggle:hover{ color:#1f2937; }
.qr-card{ background: #fff; border: 1px solid #e8edf6; border-radius: 12px; overflow: hidden; box-shadow: 0 8px 18px rgba(15,23,42,.05); margin-top: 1rem; }
.qr-head{ display:flex; gap:.5rem; align-items:center; padding:.65rem .9rem; background:#f8fafc; border-bottom:1px solid #eef2f7; font-weight:800; color:#0f172a; }
.qr-body{ padding: .9rem; }
.qr-wrap{ display:flex; align-items:center; justify-content:center; border:1px dashed #dbe3ef; border-radius:12px; background:#f8fafc; min-height: 220px; }
.qr-wrap img{ max-width: 100%; width: 220px; height: 220px; object-fit: contain; }
.qr-actions{ display:flex; gap:.5rem; margin-top:.6rem; }
.qr-actions .btn{ font-size:.82rem; }
@media (max-width: 768px){ .profile-body .badge-row{ flex-direction: column; align-items: flex-start; gap:.5rem; } }
</style>

<div class="md-wrap">
    <?php 
    // ✅ MOVED HERE: This is now inside the main wrapper
    if (!empty($details)) {
        echo '<input type="hidden" name="user_id" value="'.htmlspecialchars($details["user_id"]).'">';
    }
    ?>
    <div class="row g-3">
        <div class="col-lg-4">
            <div class="profile-card">
                <div class="profile-head">
                    <div class="dot">🎓</div>
                    <div>
                        <div style="font-weight:800; color:#111827;">Student Profile</div>
                        <div class="text-muted" style="font-size:.85rem;">Upload photo & view account status</div>
                    </div>
                </div>
                <div class="profile-body">
                    <div class="avatar-wrap mb-3">
                        <img id="preview" class="preview-img" src="<?= $imgSrc ?>" alt="Profile Image">
                        <div class="avatar-overlay">
                            <span>Profile Photo</span>
                            <div class="avatar-actions">
                                <label for="imageInput" class="btn mb-0">Upload</label>
                                <input type="file" id="imageInput" name="image" accept="image/*" hidden>
                            </div>
                        </div>
                    </div>
                    <div class="mb-2" style="font-weight:700; font-size:1.05rem; color:#0f172a;">
                        <?= $fullName ? htmlspecialchars($fullName) : ($val('username') ?: '—') ?>
                    </div>
                    <div class="badge-row d-flex align-items-center gap-2 mb-3">
                        <span class="badge-chip">
                            <span style="font-weight:700;">Role:</span> Student
                        </span>
                        <span class="badge-chip <?= ((int)($details['status'] ?? 1)===1 ? '' : 'warn') ?>">
                            <span class="dot-sm"></span> <?= htmlspecialchars($statusText) ?>
                        </span>
                    </div>
                    <?php if (!empty($details['email'])): ?>
                    <div class="small text-muted">
                        Email on file: <strong><?= htmlspecialchars($details['email']) ?></strong>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="qr-card">
                <div class="qr-head">Student QR Code</div>
                <div class="qr-body">
                    <div class="qr-wrap">
                        <?php
                            $qrImgSrc = $qrSrcDb ?: $qrExpectedAbs;
                            if ($qrImgSrc):
                        ?>
                            <img id="qrPreview" src="<?= htmlspecialchars($qrImgSrc) ?>" alt="QR Code"
                                onerror="this.style.display='none';document.getElementById('qrFallback').style.display='block';">
                        <?php endif; ?>
                        <div id="qrFallback" style="display: <?= $qrImgSrc ? 'none' : 'block' ?>; text-align:center; padding:1rem;">
                            <div class="text-muted" style="font-size:.9rem;">
                                <?php if ($isLrnValid): ?>
                                    QR will be generated after saving this record.
                                <?php else: ?>
                                    Enter a valid 12-digit LRN to preview the expected QR filename.
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="qr-actions">
                        <?php if ($qrImgSrc): ?>
                            <a id="qrDownload" class="btn btn-sm btn-primary" href="<?= htmlspecialchars($qrImgSrc) ?>" download>Download PNG</a>
                            <button type="button" id="qrPrint" class="btn btn-sm btn-outline-secondary">Print</button>
                        <?php else: ?>
                            <button type="button" class="btn btn-sm btn-outline-secondary" disabled>Download PNG</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" disabled>Print</button>
                        <?php endif; ?>
                    </div>
                    <div class="help">
                        <?php if ($qrRel): ?>
                            Stored path: <code><?= htmlspecialchars($qrRel) ?></code>
                        <?php elseif ($qrExpectedRel): ?>
                            Expected path after save: <code id="qrPathText"><?= htmlspecialchars($qrExpectedRel) ?></code>
                        <?php else: ?>
                            Expected path will appear once a valid LRN is entered.
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="section-title">Student Information</div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="LRN" class="form-label">LRN ID</label>
                    <input type="text" class="form-control" id="LRN" name="LRN" value="<?= $val('LRN') ?>" placeholder="LRN">
                    <div class="form-text">Exactly 12 digits. The QR image filename will follow this LRN.</div>
                </div>
                <div class="col-md-6">
                    <label for="dateof_birth" class="form-label">Birth Date</label>
                    <input type="date" class="form-control" id="dateof_birth" name="dateof_birth" value="<?= $val('dateof_birth') ?>">
                </div>
                <div class="col-md-4">
                    <label for="account_first_name" class="form-label">First Name</label>
                    <input type="text" class="form-control" id="account_first_name" name="account_first_name" value="<?= $val('account_first_name') ?>" placeholder="e.g., Juan">
                </div>
                <div class="col-md-4">
                    <label for="account_middle_name" class="form-label">Middle Name</label>
                    <input type="text" class="form-control" id="account_middle_name" name="account_middle_name" value="<?= $val('account_middle_name') ?>" placeholder="Optional">
                </div>
                <div class="col-md-4">
                    <label for="account_last_name" class="form-label">Last Name</label>
                    <input type="text" class="form-control" id="account_last_name" name="account_last_name" value="<?= $val('account_last_name') ?>" placeholder="e.g., Dela Cruz">
                </div>
            </div>

            <hr class="hr-soft">

            <div class="section-title">Demographics</div>
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="gender" class="form-label">Gender</label>
                    <select name="gender" id="gender" class="form-select">
                        <?php 
                            $curr = $details["gender"] ?? null; // Keep raw value
                            foreach ($genderMap as $k=>$v) {
                                $sel = ((string)$curr === (string)$k) ? 'selected' : '';
                                echo '<option value="'.$k.'" '.$sel.'>'.htmlspecialchars($v).'</option>';
                            }
                        ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="nationality" class="form-label">Nationality</label>
                    <select name="nationality" id="nationality" class="form-select">
                        <?php 
                            $curr = $details["nationality"] ?? null;
                            foreach ($nationalityMap as $k=>$v) {
                                $sel = ((string)$curr === (string)$k) ? 'selected' : '';
                                echo '<option value="'.$k.'" '.$sel.'>'.htmlspecialchars($v).'</option>';
                            }
                        ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="religion" class="form-label">Religion</label>
                    <input type="text" class="form-control" id="religion" name="religion" value="<?= $val('religion') ?>" placeholder="e.g., Catholic">
                </div>
            </div>

            <div class="row g-3 mt-2">
                <div class="col-md-6">
                    <label for="mother_tongue" class="form-label">Mother Tongue</label>
                    <input type="text" class="form-control" id="mother_tongue" name="mother_tongue" value="<?= $val('mother_tongue') ?>" placeholder="e.g., Tagalog">
                </div>
                <div class="col-md-6">
                    <label for="house_street_sitio_purok" class="form-label">House # / Street / Sitio / Purok</label>
                    <input type="text" class="form-control" id="house_street_sitio_purok" name="house_street_sitio_purok" value="<?= $val('house_street_sitio_purok') ?>" placeholder="e.g., Purok 2, Sitio Mabini">
                </div>
            </div>

            <div class="row g-3 mt-2">
                <div class="col-md-4">
                    <label for="barangay" class="form-label">Barangay</label>
                    <input type="text" class="form-control" id="barangay" name="barangay" value="<?= $val('barangay') ?>">
                </div>
                <div class="col-md-4">
                    <label for="municipality_city" class="form-label">Municipality / City</label>
                    <input type="text" class="form-control" id="municipality_city" name="municipality_city" value="<?= $val('municipality_city') ?>">
                </div>
                <div class="col-md-4">
                    <label for="province" class="form-label">Province</label>
                    <input type="text" class="form-control" id="province" name="province" value="<?= $val('province') ?>">
                </div>
            </div>

            <div class="row g-3 mt-2">
                <div class="col-md-6">
                    <label for="father_name" class="form-label">Father's Name</label>
                    <input type="text" class="form-control" id="father_name" name="father_name" value="<?= $val('father_name') ?>">
                </div>
                <div class="col-md-6">
                    <label for="mother_name" class="form-label">Mother's Maiden Name</label>
                    <input type="text" class="form-control" id="mother_name" name="mother_name" value="<?= $val('mother_name') ?>">
                </div>
            </div>

            <div class="row g-3 mt-2">
                <div class="col-md-4">
                    <label for="guardian" class="form-label">Guardian</label>
                    <input type="text" class="form-control" id="guardian" name="guardian" value="<?= $val('guardian') ?>">
                </div>
                <div class="col-md-4">
                    <label for="relationship" class="form-label">Relationship</label>
                    <input type="text" class="form-control" id="relationship" name="relationship" value="<?= $val('relationship') ?>">
                </div>
                <div class="col-md-4">
                    <label for="contact_no_of_parent" class="form-label">Contact No. of Parent/Guardian</label>
                    <input type="text" class="form-control" id="contact_no_of_parent" name="contact_no_of_parent" value="<?= $val('contact_no_of_parent') ?>">
                </div>
            </div>

            <div class="row g-3 mt-2">
                <div class="col-md-6">
                    <label for="learning_modality" class="form-label">Learning Modality</label>
                    <input type="text" class="form-control" id="learning_modality" name="learning_modality" value="<?= $val('learning_modality') ?>">
                </div>
                <div class="col-md-6">
                    <label for="remarks" class="form-label">Remarks</label>
                    <input type="text" class="form-control" id="remarks" name="remarks" value="<?= $val('remarks') ?>">
                </div>
            </div>

            <hr class="hr-soft">

            <div class="section-title">Academic Grouping</div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="batch" class="form-label">Batch</label>
                    <select class="form-select" id="batch" name="batch">
                        <option value="">Select Batch</option>
                        <?php
                            if (!empty($batches)) {
                                $curr = $details['batch'] ?? '';
                                foreach ($batches as $b) {
                                    $valOpt = (string)($b['batch'] ?? '');
                                    if ($valOpt==='') continue;
                                    $sel = ((string)$curr === $valOpt) ? 'selected' : '';
                                    echo '<option value="'.htmlspecialchars($valOpt).'" '.$sel.'>'.htmlspecialchars($valOpt).'</option>';
                                }
                            }
                        ?>
                    </select>
                    <div class="help">Used for filtering and reporting.</div>
                </div>
                <div class="col-md-6">
                    <label for="set_group" class="form-label">Set</label>
                    <select class="form-select" id="set_group" name="set_group">
                        <option value="">Select Set</option>
                        <?php
                            if (!empty($sets)) {
                                $curr = $details['set_group'] ?? '';
                                foreach ($sets as $s) {
                                    $valOpt = (string)($s['set_group'] ?? '');
                                    if ($valOpt==='') continue;
                                    $sel = ((string)$curr === $valOpt) ? 'selected' : '';
                                    echo '<option value="'.htmlspecialchars($valOpt).'" '.$sel.'>'.htmlspecialchars($valOpt).'</option>';
                                }
                            }
                        ?>
                    </select>
                </div>
            </div>

            <hr class="hr-soft">

            <div class="section-title">User Information</div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" value="<?= $val('username') ?>" placeholder="User Name">
                    <div class="help">Usually the LRN for students.</div>
                </div>
                <div class="col-md-6">
                    <label for="email" class="form-label">Email</label>
                    <div class="input-group">
                        <span class="input-group-text">@</span>
                        <input type="text" class="form-control" id="email" name="email" value="<?= $val('email') ?>" placeholder="name@example.com">
                    </div>
                </div>
                <div class="col-md-6">
                    <label for="contact_no" class="form-label">Contact No.</label>
                    <input type="text" class="form-control" id="contact_no" name="contact_no" value="<?= $val('contact_no') ?>" placeholder="e.g., 09XXXXXXXXX">
                </div>
                <div class="col-md-6">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                       <input type="password" class="form-control" id="password" name="password" value="" placeholder="Leave blank to keep current password">
                        <span class="input-group-text password-toggle" id="pwToggle" title="Show/Hide">
                            <i class="ti-eye"></i>
                        </span>
                    </div>
                    <div class="help">Leave unchanged to keep current password.</div>
                </div>
            </div>

            <hr class="hr-soft">

            <div class="section-title">Role & Status</div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="user_type" class="form-label">User Role</label>
                    <input type="text" class="form-control" id="user_type" value="Student" readonly>
                    <input type="hidden" name="user_type" value="5">
                    <div class="help text-muted small">User role is fixed as Student.</div>
                </div>
                <div class="col-md-6">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <?php
                            $currStatus = isset($details['status']) ? (int)$details['status'] : 1;
                            foreach ($statusMap as $key => $label) {
                                $sel = ($key === $currStatus) ? 'selected' : '';
                                echo "<option value='{$key}' {$sel}>{$label}</option>";
                            }
                        ?>
                    </select>
                    <div class="help text-muted small">Change to <strong>Inactive</strong> if the student is no longer enrolled.</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function(){
  // Ensure the form supports file upload (in case the wrapper didn't set it)
  const form = document.querySelector('.modal.show form') || document.querySelector('form');
  if (form) {
    form.setAttribute('enctype', 'multipart/form-data');
    form.setAttribute('method', form.getAttribute('method') || 'POST');
  }

  // Password toggle
  const pw = document.getElementById('password');
  const tg = document.getElementById('pwToggle');
  if (pw && tg) {
    tg.addEventListener('click', function(){
      const isPwd = pw.getAttribute('type') === 'password';
      pw.setAttribute('type', isPwd ? 'text' : 'password');
      this.querySelector('i')?.classList.toggle('ti-eye');
      this.querySelector('i')?.classList.toggle('ti-eye-off');
    });
  }

  // Image preview
  const input = document.getElementById('imageInput');
  const img = document.getElementById('preview');
  if (input && img) {
    input.addEventListener('change', function(e){
      const file = e.target.files && e.target.files[0];
      if (!file) return;
      if (!/^image\//.test(file.type)) {
        alert('Please select an image file.');
        input.value = '';
        return;
      }
      const reader = new FileReader();
      reader.onload = ev => { img.src = ev.target?.result || img.src; };
      reader.readAsDataURL(file);
    });
  }

  // ===== NEW: live QR expected path & preview based on LRN =====
  const host = '<?= htmlspecialchars($host) ?>';
  const lrnInput = document.getElementById('LRN');
  const qrImg = document.getElementById('qrPreview');
  const qrFallback = document.getElementById('qrFallback');
  const qrPathText = document.getElementById('qrPathText');
  const qrDownload = document.getElementById('qrDownload');

  function setQr(src){
    if (!qrImg) return;
    if (src) {
      qrImg.style.display = 'block';
      qrImg.src = src;
      if (qrFallback) qrFallback.style.display = 'none';
      if (qrDownload) {
        qrDownload.href = src;
        qrDownload.removeAttribute('disabled');
        qrDownload.classList.remove('disabled');
      }
    } else {
      qrImg.style.display = 'none';
      if (qrFallback) qrFallback.style.display = 'block';
      if (qrDownload) {
        qrDownload.setAttribute('disabled','disabled');
        qrDownload.classList.add('disabled');
      }
    }
  }

  function updateQrFromLRN(){
    if (!lrnInput) return;
    const digits = (lrnInput.value || '').replace(/\D+/g,'');
    if (/^\d{12}$/.test(digits)) {
      const rel = `/uploads/qrcodes/${digits}.png`;
      if (qrPathText) qrPathText.textContent = rel;
      setQr(host + rel);
    } else {
      setQr('');
      if (qrPathText) qrPathText.textContent = '';
    }
  }

  if (lrnInput){
    lrnInput.addEventListener('input', updateQrFromLRN);
  }

  // Print button pops a minimal window with the QR
  const btnPrint = document.getElementById('qrPrint');
  if (btnPrint && qrImg){
    btnPrint.addEventListener('click', function(){
      if (!qrImg.src) return;
      const w = window.open('', '_blank', 'width=480,height=520');
      if (!w) return;
      w.document.write(`
        <html><head><title>Print QR</title></head>
        <body style="margin:0;display:flex;align-items:center;justify-content:center;height:100vh;">
          <img src="${qrImg.src}" style="width:360px;height:360px;object-fit:contain"/>
        </body></html>
      `);
      w.document.close();
      w.focus();
      w.print();
    });
  }

  // If the current image fails (404), show fallback text block
  if (qrImg){
    qrImg.onerror = function(){
      this.style.display = 'none';
      if (qrFallback) qrFallback.style.display = 'block';
    }
  }
})();
</script>
