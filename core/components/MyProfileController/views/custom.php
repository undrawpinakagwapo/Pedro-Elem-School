<?php
// Expect $details
?>
<meta name="viewport" content="width=device-width, initial-scale=1" />

<style>
:root{
  --bg:#f5f7fb;
  --ink:#0f172a;
  --muted:#64748b;
  --line:#e8edf6;
  --ring:#2563eb;

  --ok-bg:#ecfdf5; --ok-bd:#a7f3d0; --ok-ink:#065f46;
  --off-bg:#fee2e2; --off-bd:#fecaca; --off-ink:#991b1b;

  --radius: 16px;
  --pad: clamp(14px, 2.6vw, 26px);
  --gap: clamp(12px, 2.2vw, 20px);
  --text-xs: clamp(11px, 1.2vw, 12px);
  --text-sm: clamp(12px, 1.35vw, 13.5px);
  --text-base: clamp(14px, 1.6vw, 16px);
  --text-lg: clamp(16px, 1.85vw, 18px);
  --shadow: 0 18px 40px rgba(15,23,42,.08);
}

html, body { height: 100%; margin:0; }
body { background: var(--bg); }

/* ====== Shell ====== */
.profile-shell{
  min-height: 100dvh;
  padding: var(--pad);
  display:flex; width:100%;
}

/* Uncap theme wrappers */
.profile-shell .container,
.profile-shell .container-fluid,
.profile-shell .content-wrapper,
.profile-shell .main-content{
  max-width: 100% !important;
  width: 100% !important;
  padding-left: 0; padding-right: 0;
}

/* ====== Layout: left summary rail + right content ====== */
.profile-grid{
  display:grid; gap: var(--gap);
  grid-template-columns: 320px 1fr;
  width:100%;
}
@media (max-width: 991.98px){
  .profile-grid{ grid-template-columns: 1fr; }
}

/* ====== Card primitives ====== */
.card{
  background:#fff; border:1px solid var(--line);
  border-radius: var(--radius); box-shadow: var(--shadow);
  overflow:hidden;
}
.card-head{
  display:flex; align-items:center; gap:.75rem;
  padding: .9rem var(--pad);
  background: linear-gradient(180deg,#fbfdff, #f6f9fe);
  border-bottom:1px solid #eef2f7;
}
.card-head .mark{
  width:38px; height:38px; border-radius:10px; flex:0 0 auto;
  background:#e9efff; color:#2563eb; display:flex; align-items:center; justify-content:center;
  font-weight:800; font-size:1rem;
}
.card-head .title{ margin:0; font-size:var(--text-lg); font-weight:900; color:var(--ink); }
.card-body{ padding: var(--pad); }

/* ====== Summary rail ====== */
.summary{
  position:sticky; top: var(--pad);
  display:flex; flex-direction:column; gap: var(--gap);
  height: fit-content;
}
.summary .avatar{
  position:relative; width:100%; aspect-ratio:1/1;
  border-radius:12px; overflow:hidden;
  background:#f1f5f9; border:1px dashed #d2dae6;
  box-shadow:0 8px 24px rgba(15,23,42,.06);
}
.summary .avatar img{ width:100%; height:100%; object-fit:cover; display:block; }
.summary .avatar .overlay{
  position:absolute; inset:auto 0 0 0; display:flex; align-items:center; justify-content:space-between;
  padding:.5rem .6rem; color:#fff; font-size:.85rem;
  background:linear-gradient(180deg,transparent,rgba(0,0,0,.55));
}
.summary .overlay .btn{
  padding:.32rem .62rem; line-height:1.1; font-size:.78rem; border-radius:8px;
  border:1px solid rgba(255,255,255,.35); background:rgba(255,255,255,.18); color:#fff; cursor:pointer;
}
.summary .meta{
  display:grid; gap:.5rem;
  grid-template-columns: 1fr;
}
.summary .meta .kv{
  display:flex; gap:.5rem; align-items:center; min-width:0;
  font-size:var(--text-sm); color:#0f172a;
}
.k{ color:var(--muted); min-width:88px; }
.v{ color:#0f172a; font-weight:700; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }

/* Status chips */
.badges{ display:flex; flex-wrap:wrap; gap:6px; }
.badge{ font-size:.75rem; font-weight:900; padding:4px 10px; border-radius:999px; border:1px solid transparent; }
.badge.view{ background:#f0f5ff; border-color:#e7ecf5; color:#1e293b; }
.badge.on { background:var(--ok-bg); border-color:var(--ok-bd); color:var(--ok-ink); }
.badge.off{ background:var(--off-bg); border-color:var(--off-bd); color:var(--off-ink); }

/* ====== Content sections ====== */
.section + .section{ margin-top: var(--gap); }
.section-title{
  display:flex; align-items:center; justify-content:space-between; gap:10px;
  font-size:.95rem; font-weight:900; color:var(--ink); margin:0 0 .75rem;
}
.hr-soft{ border:0; height:1px; margin:.9rem 0 1.1rem; background:linear-gradient(90deg,transparent,#e9eef6,transparent); }

/* ====== Responsive fields ====== */
.resp-row{ display:grid; gap: var(--gap); }
.cols-2{ grid-template-columns: 1fr; }
@media (min-width: 768px){ .cols-2{ grid-template-columns: 1fr 1fr; } }
.cols-3{ grid-template-columns: 1fr; }
@media (min-width: 768px){ .cols-3{ grid-template-columns: repeat(3,1fr); } }
.auto-fit{
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
}

/* ====== Form visuals (read-only) ====== */
.form-label { font-weight:800; color:#0f172a; font-size:.88rem; margin-bottom:.35rem; display:block; }
.form-control, .form-select{
  border:1px solid #dbe3ef; border-radius:.55rem; padding:.58rem .75rem;
  font-size:var(--text-base); background:#fff; color:#111827; width:100%;
}
.form-control[readonly], .form-select[disabled]{ background:#f7f9fc; color:#6b7280; cursor:not-allowed; }
.form-text{ color:#6b7280; font-size:var(--text-sm); }
.helper{ color:var(--muted); font-size:var(--text-xs); }

/* Small polish */
@media (max-width: 575.98px){
  .badges .badge.view{ display:none; }
}
</style>

<div class="profile-shell">
  <div class="profile-grid">

    <!-- ===== Left Summary Rail ===== -->
    <aside class="summary">
      <div class="card">
        <div class="card-head">
          <div class="mark">👤</div>
          <h5 class="title">Profile Overview</h5>
        </div>
        <div class="card-body">
          <div class="avatar">
            <img id="preview"
                 src="<?= htmlspecialchars(($_ENV['URL_HOST'] ?? '') . ($details['image'] ?? '')) ?>"
                 alt="Profile Image">
            <div class="overlay">
              <span>Profile Photo</span>
              <div>
                <label class="btn mb-0" for="imageInput">Upload</label>
                <input type="file" id="imageInput" accept="image/*" hidden>
              </div>
            </div>
          </div>
          <div class="helper" style="margin-top:.5rem;">Recommended: square, 600×600 or larger.</div>

          <hr class="hr-soft" style="margin:1rem 0;">

          <div class="badges">
            <span class="badge view">View Only</span>
            <?php
              $isActive = (int)($details['status'] ?? 0) === 1;
              echo '<span class="badge '.($isActive ? 'on' : 'off').'">'.($isActive ? 'ACTIVE' : 'INACTIVE').'</span>';
            ?>
          </div>

          <div class="meta" style="margin-top:12px;">
            <div class="kv"><span class="k">Faculty ID</span><span class="v" title="<?= htmlspecialchars($details['emp_id'] ?? '—') ?>"><?= htmlspecialchars($details['emp_id'] ?? '—') ?></span></div>
            <div class="kv"><span class="k">Username</span><span class="v" title="<?= htmlspecialchars($details['username'] ?? '—') ?>"><?= htmlspecialchars($details['username'] ?? '—') ?></span></div>
            <div class="kv"><span class="k">Email</span><span class="v" title="<?= htmlspecialchars($details['email'] ?? '—') ?>"><?= htmlspecialchars($details['email'] ?? '—') ?></span></div>
            <div class="kv"><span class="k">Contact</span><span class="v" title="<?= htmlspecialchars($details['contact_no'] ?? '—') ?>"><?= htmlspecialchars($details['contact_no'] ?? '—') ?></span></div>
          </div>
        </div>
      </div>
    </aside>

    <!-- ===== Right Main Content ===== -->
    <main class="card">
      <div class="card-head">
        <div class="mark">📋</div>
        <h5 class="title">Personal Information</h5>
        <div style="margin-left:auto" class="badges">
          <?php
            $nationality = (int)($details['nationality'] ?? 0) === 2 ? 'ALIEN' : 'FILIPINO';
            echo '<span class="badge view">'.$nationality.'</span>';
          ?>
        </div>
      </div>

      <div class="card-body">

        <!-- Top: Name + DOB -->
        <div class="section">
          <div class="section-title">Identity</div>
          <div class="resp-row auto-fit">
            <div>
              <label class="form-label">First Name</label>
              <input type="text" class="form-control" value="<?= htmlspecialchars($details['account_first_name'] ?? '') ?>" readonly>
            </div>
            <div>
              <label class="form-label">Middle Name</label>
              <input type="text" class="form-control" value="<?= htmlspecialchars($details['account_middle_name'] ?? '') ?>" readonly>
            </div>
            <div>
              <label class="form-label">Last Name</label>
              <input type="text" class="form-control" value="<?= htmlspecialchars($details['account_last_name'] ?? '') ?>" readonly>
            </div>
            <div>
              <label class="form-label">Birth Date</label>
              <input type="date" class="form-control" value="<?= htmlspecialchars($details['dateof_birth'] ?? '') ?>" readonly>
            </div>
          </div>
        </div>

        <hr class="hr-soft">

        <!-- Demographics -->
        <div class="section">
          <div class="section-title">Demographics</div>
          <div class="resp-row cols-3">
            <div>
              <label class="form-label">Gender</label>
              <select class="form-select" disabled>
                <?php
                  $opts = [1=>'Male', 2=>'Female'];
                  $curr = isset($details['gender']) ? (int)$details['gender'] : null;
                  foreach ($opts as $k=>$v) {
                    $sel = ($curr === $k) ? 'selected' : '';
                    echo "<option value='$k' $sel>".htmlspecialchars($v)."</option>";
                  }
                ?>
              </select>
            </div>
            <div>
              <label class="form-label">Nationality</label>
              <select class="form-select" disabled>
                <?php
                  $opts = [1=>'FILIPINO', 2=>'ALIEN'];
                  $curr = isset($details['nationality']) ? (int)$details['nationality'] : null;
                  foreach ($opts as $k=>$v) {
                    $sel = ($curr === $k) ? 'selected' : '';
                    echo "<option value='$k' $sel>".htmlspecialchars($v)."</option>";
                  }
                ?>
              </select>
            </div>
            <div>
              <label class="form-label">Religion</label>
              <select class="form-select" disabled>
                <?php
                  $opts = [1=>'Catholic', 2=>'Muslim'];
                  $curr = isset($details['religion']) ? (int)$details['religion'] : null;
                  foreach ($opts as $k=>$v) {
                    $sel = ($curr === $k) ? 'selected' : '';
                    echo "<option value='$k' $sel>".htmlspecialchars($v)."</option>";
                  }
                ?>
              </select>
            </div>
          </div>
        </div>

        <hr class="hr-soft">

        <!-- User Account -->
        <div class="section">
          <div class="section-title">User Account</div>
          <div class="resp-row cols-2">
            <div>
              <label class="form-label">User Name</label>
              <input type="text" class="form-control" value="<?= htmlspecialchars($details['username'] ?? '') ?>" readonly>
            </div>
            <div>
              <label class="form-label">Email</label>
              <input type="text" class="form-control" value="<?= htmlspecialchars($details['email'] ?? '') ?>" readonly>
            </div>
            <div>
              <label class="form-label">Contact No.</label>
              <input type="text" class="form-control" value="<?= htmlspecialchars($details['contact_no'] ?? '') ?>" readonly>
            </div>
            <div>
              <label class="form-label">Password</label>
              <input type="password" class="form-control" value="<?= htmlspecialchars($details['password'] ?? '') ?>" readonly>
            </div>
          </div>
        </div>

        <hr class="hr-soft">

        <!-- Account Settings -->
        <div class="section">
          <div class="section-title">Account Settings</div>
          <div class="resp-row cols-2">
            <div>
              <label class="form-label">User Role</label>
              <select class="form-select" disabled>
                <?php
                  $roles = [1=>'Admin', 2=>'Teacher', 3=>'Principal', 5=>'Student'];
                  $curr = isset($details['user_type']) ? (int)$details['user_type'] : null;
                  foreach ($roles as $k=>$v) {
                    $sel = ($curr === $k) ? 'selected' : '';
                    echo "<option value='$k' $sel>".htmlspecialchars($v)."</option>";
                  }
                ?>
              </select>
            </div>
            <div>
              <label class="form-label">Status</label>
              <select class="form-select" disabled>
                <?php
                  $opts = [1=>'Active', 0=>'Inactive'];
                  $curr = isset($details['status']) ? (int)$details['status'] : null;
                  foreach ($opts as $k=>$v) {
                    $sel = ($curr === $k) ? 'selected' : '';
                    echo "<option value='$k' $sel>".htmlspecialchars($v)."</option>";
                  }
                ?>
              </select>
            </div>
          </div>
        </div>

      </div>
    </main>

  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded',function(){
  const imgInput=document.getElementById('imageInput');
  if(imgInput){
    imgInput.addEventListener('change',function(e){
      const f=e.target.files&&e.target.files[0]; if(!f) return;
      if(!/^image\\//.test(f.type)){alert('Please select an image.');this.value='';return;}
      const r=new FileReader();
      r.onload=ev=>{const img=document.getElementById('preview'); if(img) img.src=ev.target.result;};
      r.readAsDataURL(f);
    });
  }
});
</script>
