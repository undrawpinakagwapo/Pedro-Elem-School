<?php 
    // If editing, keep the primary key
    if (!empty($details)) {
        echo '<input type="hidden" name="user_id" value="'.htmlspecialchars($details["user_id"]).'">';
    }

    // Safe helpers
    $val = fn($k) => htmlspecialchars($details[$k] ?? '');
    $imgSrc = htmlspecialchars(($_ENV['BASE_PATH'] ?? '') . ($details['image'] ?? ''));
    $roleMap = [
        1 => 'Admin',
        2 => 'Teacher',
        3 => 'Principal',
        5 => 'Student',
    ];
    $statusMap = [ 1 => 'Active', 0 => 'Inactive' ];
    $roleLabel   = $details ? ($roleMap[(int)($details['user_type'] ?? -1)] ?? '—') : '—';
    $statusLabel = $details ? ($statusMap[(int)($details['status'] ?? -1)] ?? '—') : '—';
    $fullName = trim(($details['account_last_name'] ?? '').', '.($details['account_first_name'] ?? '').' '.($details['account_middle_name'] ?? ''));
    if ($fullName === ',  ') $fullName = '';
?>

<style>
/* ====== Layout polish ====== */
.md-wrap { --gap: 1rem; }
.md-wrap .section-title{
  font-size: .95rem; font-weight: 800; color: #0f172a; letter-spacing:.2px; margin: 0 0 .5rem;
}
.hr-soft{
  border:0;height:1px;margin:1.1rem 0;background:linear-gradient(90deg,transparent,#e9eef6,transparent);
}

/* ====== Profile card ====== */
.profile-card{
  background: #fff; border: 1px solid #e8edf6; border-radius: 12px; overflow: hidden;
  box-shadow: 0 8px 18px rgba(15,23,42,.05);
}
.profile-head{
  display:flex; gap:.75rem; align-items:center; padding:.9rem 1rem; background: #f8fafc; border-bottom:1px solid #eef2f7;
}
.profile-head .dot{
  width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;
  background:#e9efff;color:#2563eb;font-weight:800;
}
.profile-body{ padding: 1rem; }
.avatar-wrap{
  position:relative;border-radius:12px;overflow:hidden;background:#f1f5f9;border:1px dashed #ced6e3;
}
.avatar-wrap img{ width:100%; height:220px; object-fit:cover; display:block; }
.avatar-overlay{
  position:absolute;left:0;right:0;bottom:0;display:flex;align-items:center;justify-content:space-between;
  padding:.5rem .6rem;background:linear-gradient(180deg, transparent, rgba(0,0,0,.55));
  color:#fff;font-size:.85rem;
}
.avatar-actions .btn{
  padding:.25rem .55rem; line-height:1.1; font-size:.8rem; border-radius:8px; border:1px solid rgba(255,255,255,.3);
  background: rgba(255,255,255,.15); color:#fff;
}
.badge-chip{
  display:inline-flex; align-items:center; gap:.4rem;
  font-size:.78rem; padding:.3rem .55rem; border-radius:999px; border:1px solid #e7ecf5; background:#f8fafc; color:#0f172a;
}
.badge-chip .dot-sm{ width:7px; height:7px; border-radius:50%; background:#16a34a; }
.badge-chip.warn .dot-sm{ background:#f59e0b; }

/* ====== Field styles ====== */
.form-label{ font-weight:700; color:#0f172a; font-size:.88rem; }
.form-text{ color:#6b7280; font-size:.8rem; }

.form-control, .form-select{
  border:1px solid #dbe3ef; border-radius:.55rem; padding:.58rem .75rem; font-size:.95rem;
  transition: box-shadow .15s, border-color .15s;
}
.form-control:focus, .form-select:focus{
  border-color:#86b7fe; box-shadow:0 0 0 .2rem rgba(13,110,253,.12); outline:0;
}
.help{
  font-size:.78rem; color:#64748b; margin-top:.25rem;
}

/* input groups */
.input-group-text { background:#f8fafc; border:1px solid #dbe3ef; color:#475569; }

/* Password toggle */
.password-toggle{
  cursor:pointer; user-select:none; color:#475569;
}
.password-toggle:hover{ color:#1f2937; }

/* Responsive tweaks */
@media (max-width: 768px){
  .profile-body .badge-row{ flex-direction: column; align-items: flex-start; gap:.5rem; }
}
</style>

<div class="md-wrap">
  <div class="row g-3">

    <!-- Left: Profile / Summary -->
    <div class="col-lg-4">
      <div class="profile-card">
        <div class="profile-head">
          <div class="dot">👤</div>
          <div>
            <div style="font-weight:800; color:#111827;">Profile</div>
            <div class="text-muted" style="font-size:.85rem;">Upload photo & view account status</div>
          </div>
        </div>
        <div class="profile-body">
          <div class="avatar-wrap mb-3">
            <img id="preview" class="preview-img" src="<?= $imgSrc ?>" alt="Profile Image">
            <div class="avatar-overlay">
              <span>Profile Photo</span>
              <div class="avatar-actions">
                <label class="btn mb-0">
                  Upload
                  <input type="file" id="imageInput" name="image[]" accept="image/*" hidden>
                </label>
              </div>
            </div>
          </div>

          <div class="mb-2" style="font-weight:700; font-size:1.05rem; color:#0f172a;">
            <?= $fullName ? htmlspecialchars($fullName) : ($val('username') ?: '—') ?>
          </div>

          <div class="badge-row d-flex align-items-center gap-2 mb-3">
            <span class="badge-chip">
              <span style="font-weight:700;">Role:</span> <?= htmlspecialchars($roleLabel) ?>
            </span>
            <span class="badge-chip <?= ((int)($details['status'] ?? 1)===1 ? '' : 'warn') ?>">
              <span class="dot-sm"></span> <?= htmlspecialchars($statusLabel) ?>
            </span>
          </div>

          <?php if (!empty($details['email'])): ?>
          <div class="small text-muted">
            Email on file: <strong><?= htmlspecialchars($details['email']) ?></strong>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Right: Forms -->
    <div class="col-lg-8">

      <!-- Personal Information -->
      <div class="section-title">Personal Information</div>
      <div class="row g-3">
        <div class="col-md-6">
          <label for="emp_id" class="form-label">Faculty ID</label>
          <input type="text" class="form-control" id="emp_id" name="emp_id" value="<?= $val('emp_id') ?>" placeholder="EMP ID">
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

      <!-- Demographics -->
      <div class="section-title">Demographics</div>
      <div class="row g-3">
        <div class="col-md-4">
          <label for="gender" class="form-label">Gender</label>
          <select name="gender" id="gender" class="form-select">
            <?php 
              $genderCurr = isset($details["gender"]) ? (int)$details["gender"] : null;
              $gopts = [1=>'Male', 2=>'Female'];
              foreach ($gopts as $k=>$v) {
                  $sel = ($genderCurr === (int)$k) ? 'selected' : '';
                  echo '<option value="'.$k.'" '.$sel.'>'.htmlspecialchars($v).'</option>';
              }
            ?>
          </select>
        </div>
        <div class="col-md-4">
          <label for="nationality" class="form-label">Nationality</label>
          <select name="nationality" id="nationality" class="form-select">
            <?php 
              $natCurr = isset($details["nationality"]) ? (int)$details["nationality"] : null;
              $nopts = [1=>'FILIPINO', 2=>'ALIEN'];
              foreach ($nopts as $k=>$v) {
                  $sel = ($natCurr === (int)$k) ? 'selected' : '';
                  echo '<option value="'.$k.'" '.$sel.'>'.htmlspecialchars($v).'</option>';
              }
            ?>
          </select>
        </div>
        <div class="col-md-4">
          <label for="religion" class="form-label">Religion</label>
          <select name="religion" id="religion" class="form-select">
            <?php 
              $relCurr = isset($details["religion"]) ? (int)$details["religion"] : null;
              $ropts = [1=>'Catholic', 2=>'Muslim'];
              foreach ($ropts as $k=>$v) {
                  $sel = ($relCurr === (int)$k) ? 'selected' : '';
                  echo '<option value="'.$k.'" '.$sel.'>'.htmlspecialchars($v).'</option>';
              }
            ?>
          </select>
        </div>
      </div>

      <hr class="hr-soft">

      <!-- Account & Security -->
      <div class="section-title">Account & Security</div>
      <div class="row g-3">
        <div class="col-md-6">
          <label for="username" class="form-label">Username</label>
          <input type="text" class="form-control" id="username" name="username" value="<?= $val('username') ?>" placeholder="e.g., 123456789 (LRN for students)">
          <div class="help">Leave unchanged to keep current username.</div>
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
          <div class="input-group">
            <span class="input-group-text"><i class="ti-mobile"></i></span>
            <input type="text" class="form-control" id="contact_no" name="contact_no" value="<?= $val('contact_no') ?>" placeholder="e.g., 09XXXXXXXXX">
          </div>
        </div>

        <div class="col-md-6">
          <label for="password" class="form-label">Password</label>
          <div class="input-group">
           <input type="password" class="form-control" id="password" name="password" value="" placeholder="Enter new password (optional)">
            <span class="input-group-text password-toggle" id="pwToggle" title="Show/Hide">
              <i class="ti-eye"></i>
            </span>
          </div>
          <div class="help">Leave unchanged to keep current password (plain as per your login policy).</div>
        </div>
      </div>

      <hr class="hr-soft">

      <!-- Role & Status -->
      <!-- Role & Status -->
<div class="section-title">Role & Status</div>
<div class="row g-3">
  <div class="col-md-6">
  <label for="user_type" class="form-label">User Role</label>

  <?php if ($details): ?>
    <!-- EDIT MODE: lock role -->
    <select id="user_type" class="form-select" disabled>
      <?php 
        $roleCurr = isset($details["user_type"]) ? (int)$details["user_type"] : null;
        $roles = [
          1 => 'Admin',
          3 => 'Principal',
        ];
        foreach ($roles as $k=>$v) {
            $sel = ($roleCurr === (int)$k) ? 'selected' : '';
            echo '<option value="'.$k.'" '.$sel.'>'.htmlspecialchars($v).'</option>';
        }
      ?>
    </select>
    <input type="hidden" name="user_type" value="<?= htmlspecialchars($details["user_type"] ?? '') ?>">
    <div class="help text-muted small">User role is locked and cannot be changed once created.</div>
  <?php else: ?>
    <!-- ADD MODE: editable -->
    <select name="user_type" id="user_type" class="form-select">
      <?php 
        $roles = [
          1 => 'Admin',
          3 => 'Principal',
        ];
        foreach ($roles as $k=>$v) {
            echo '<option value="'.$k.'">'.htmlspecialchars($v).'</option>';
        }
      ?>
    </select>
  <?php endif; ?>
</div>


  <div class="col-md-6">
    <label for="status" class="form-label">Status</label>
    <select name="status" id="status" class="form-select">
      <?php 
        $stCurr = isset($details["status"]) ? (int)$details["status"] : null;
        $stats = [1=>'Active', 0=>'Inactive'];
        foreach ($stats as $k=>$v) {
          $sel = ($stCurr === (int)$k) ? 'selected' : '';
          echo '<option value="'.$k.'" '.$sel.'>'.htmlspecialchars($v).'</option>';
        }
      ?>
    </select>
  </div>
</div>


    </div>
  </div>
</div>

<script>
// Simple password show/hide toggle
(function(){
  const pw = document.getElementById('password');
  const tg = document.getElementById('pwToggle');
  if (!pw || !tg) return;
  tg.addEventListener('click', function(){
    const isPwd = pw.getAttribute('type') === 'password';
    pw.setAttribute('type', isPwd ? 'text' : 'password');
    this.querySelector('i')?.classList.toggle('ti-eye');
    this.querySelector('i')?.classList.toggle('ti-eye-off');
  });
})();
</script>
