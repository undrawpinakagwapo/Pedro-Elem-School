<?php
// components/StudentProfileController/views/custom.php

// Expect $details (row from users). Build safe name fallback + QR guessing.
$details = $details ?? [];

$first  = trim((string)($details['account_first_name']  ?? ''));
$middle = trim((string)($details['account_middle_name'] ?? ''));
$last   = trim((string)($details['account_last_name']   ?? ''));
$full   = trim((string)($details['full_name'] ?? ''));

if ($full === '' || $full === ',') {
  $parts = array_filter([$last, $first], fn($v) => $v !== '');
  $full  = $parts ? implode(', ', $parts) . ($middle ? ' ' . $middle : '') : '';
}

// LRN & QR source
$lrnRaw     = $details['lrn'] ?? ($details['LRN'] ?? '');
$lrnDigits  = preg_replace('/\D+/', '', (string)$lrnRaw);
$qrGuess    = (strlen($lrnDigits) === 12) ? ("/uploads/qrcodes/{$lrnDigits}.png") : null;
$qrSrc      = $details['qr_code'] ?? $qrGuess; // prefer DB path when present
$canShowQR  = !empty($qrSrc);

// Section cleanup: remove leading "Grade X " if present
$sectionRaw   = (string)($details['set_group'] ?? '');
$sectionClean = preg_replace('/^Grade\s*\d+\s*/i', '', $sectionRaw);

// Simple helpers
function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
?>
<meta name="viewport" content="width=device-width, initial-scale=1" />

<style>
:root{
  --bg:#f5f7fb;
  --ink:#0f172a; --muted:#64748b; --line:#e8edf6; --ring:#2563eb;
  --ok-bg:#ecfdf5; --ok-bd:#a7f3d0; --ok-ink:#065f46;
  --off-bg:#fee2e2; --off-bd:#fecaca; --off-ink:#991b1b;

  --radius: 16px;
  --pad: clamp(14px, 2.6vw, 26px);
  --gap: clamp(12px, 2.2vw, 20px);
  --text-xs: clamp(11px, 1.2vw, 12px);
  --text-sm: clamp(12px, 1.35vw, 13.5px);
  --text-base: clamp(14px, 1.6vw, 16px);
  --text-lg: clamp(16px, 1.9vw, 18px);
  --shadow: 0 18px 40px rgba(15,23,42,.08);
}

html, body { height: 100%; margin:0; }
body { background: var(--bg); }

/* Uncap theme wrappers for this page */
.student-shell .container,
.student-shell .container-fluid,
.student-shell .content-wrapper,
.student-shell .main-content{
  max-width: 100% !important; width: 100% !important; padding-left: 0; padding-right: 0;
}

/* ===== Page shell ===== */
.student-shell{
  min-height: 100dvh; padding: var(--pad);
  display:flex; width:100%;
}

/* ===== Layout: left summary rail + right content ===== */
.student-grid{
  display:grid; gap: var(--gap);
  grid-template-columns: 320px 1fr; width:100%;
}
@media (max-width: 991.98px){ .student-grid{ grid-template-columns: 1fr; } }

/* ===== Card primitives ===== */
.card{
  background:#fff; border:1px solid var(--line);
  border-radius: var(--radius); box-shadow: var(--shadow);
  overflow:hidden;
}
.card-head{
  display:flex; align-items:center; gap:.75rem;
  padding: .9rem var(--pad);
  background:
    radial-gradient(800px 200px at 20% -40%, #e9f2ff 0%, transparent 70%),
    radial-gradient(800px 200px at 85% -60%, #f3f8ff 0%, transparent 70%),
    #fbfdff;
  border-bottom:1px solid #eef2f7;
}
.card-head .mark{
  width:38px; height:38px; border-radius:10px; flex:0 0 auto;
  background:#e9efff; color:#2563eb; display:flex; align-items:center; justify-content:center;
  font-weight:800; font-size:1rem;
}
.card-head .title{ margin:0; font-size:var(--text-lg); font-weight:900; color:var(--ink); }
.card-body{ padding: var(--pad); }

/* ===== Summary rail ===== */
.summary{
  position:sticky; top: var(--pad);
  display:flex; flex-direction:column; gap: var(--gap);
  height: fit-content;
}
.avatar{
  position:relative; width:100%; aspect-ratio:1/1;
  border-radius:12px; overflow:hidden;
  background:#f1f5f9; border:1px dashed #d2dae6;
  box-shadow:0 8px 24px rgba(15,23,42,.06);
}
.avatar img{ width:100%; height:100%; object-fit:cover; display:block; }
.avatar .overlay{
  position:absolute; inset:auto 0 0 0; display:flex; align-items:center; justify-content:space-between;
  padding:.5rem .6rem; color:#fff; font-size:.85rem;
  background:linear-gradient(180deg,transparent,rgba(0,0,0,.55));
}
.overlay .btn{
  padding:.32rem .62rem; line-height:1.1; font-size:.78rem; border-radius:8px;
  border:1px solid rgba(255,255,255,.35); background:rgba(255,255,255,.18); color:#fff; cursor:pointer;
}

.kv{ display:flex; gap:.5rem; align-items:center; min-width:0; font-size:var(--text-sm); }
.k{ color:var(--muted); min-width:88px; }
.v{ color:#0f172a; font-weight:700; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }

.badges{ display:flex; flex-wrap:wrap; gap:6px; }
.badge{ font-size:.75rem; font-weight:900; padding:4px 10px; border-radius:999px; border:1px solid transparent; }
.badge.info{ background:#f0f5ff; border-color:#e7ecf5; color:#1e293b; }
.badge.on  { background:var(--ok-bg);  border-color:var(--ok-bd);  color:var(--ok-ink); }
.badge.off { background:var(--off-bg); border-color:var(--off-bd); color:var(--off-ink); }

/* ===== Content sections ===== */
.section + .section{ margin-top: var(--gap); }
.section-title{
  display:flex; align-items:center; justify-content:space-between; gap:10px;
  font-size:.95rem; font-weight:900; color:var(--ink); margin:0 0 .75rem;
}
.hr-soft{ border:0; height:1px; margin:.9rem 0 1.1rem; background:linear-gradient(90deg,transparent,#e9eef6,transparent); }

/* ===== Responsive fields ===== */
.grid{ display:grid; gap: var(--gap); }
.cols-2{ grid-template-columns: 1fr; }  @media (min-width:768px){ .cols-2{ grid-template-columns: 1fr 1fr; } }
.cols-3{ grid-template-columns: 1fr; }  @media (min-width:992px){ .cols-3{ grid-template-columns: repeat(3,1fr); } }
.auto-fit{ grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); }

/* ===== Read-only fields look ===== */
.form-label { font-weight:800; color:#0f172a; font-size:.88rem; margin-bottom:.35rem; display:block; }
.form-control{
  border:1px solid #dbe3ef; border-radius:.55rem; padding:.58rem .75rem;
  font-size:var(--text-base); background:#f7f9fc; color:#111827; width:100%;
}
.form-control[readonly]{ background:#f7f9fc; color:#6b7280; }
.helper{ color:var(--muted); font-size:var(--text-xs); }

/* ===== Actions (QR) ===== */
.head-actions{ margin-left:auto; display:flex; gap:10px; align-items:center; }
.btn-qr{
  appearance:none; border:1px solid #c7d2fe; background:#fff; color:#1d4ed8;
  padding:9px 12px; font-weight:800; border-radius:12px; cursor:pointer;
}
.btn-qr:hover{ border-color:#93c5fd; box-shadow:0 0 0 4px rgba(59,130,246,.12); }
.btn-qr:disabled{ opacity:.55; cursor:not-allowed; }
.btn-qr svg{ width:18px; height:18px; vertical-align:-3px; margin-right:6px; }

/* ===== QR Modal (keeps your existing IDs) ===== */
.qr-modal-backdrop{
  position:fixed; inset:0; background:rgba(15,23,42,.5); display:none; align-items:center; justify-content:center; z-index:1050;
}
.qr-modal{
  background:#fff; border-radius:14px; border:1px solid #e5e7eb; width:min(92vw, 420px);
  box-shadow:0 15px 35px rgba(2,6,23,.35); overflow:hidden;
}
.qr-modal-head{ padding:12px 14px; border-bottom:1px solid #eef2f7; display:flex; align-items:center; justify-content:space-between; }
.qr-modal-body{ padding:16px; display:flex; align-items:center; justify-content:center; background:#f8fafc; }
.qr-modal-body img{ width:100%; height:auto; display:block; image-rendering:pixelated; }
.qr-close{ border:0; background:transparent; font-size:20px; cursor:pointer; color:#0f172a; }
.qr-hint{ padding:10px 14px; font-size:.85rem; color:#475569; }

/* Small polish */
@media (max-width: 575.98px){
  .badge.info{ display:none; }
}
</style>

<div class="student-shell">
  <div class="student-grid">

    <!-- ===== Left Summary Rail ===== -->
    <aside class="summary">
      <div class="card">
        <div class="card-head">
          <div class="mark">🎓</div>
          <h5 class="title">Student Overview</h5>
        </div>
        <div class="card-body">

          <!-- Avatar -->
          <div class="avatar">
            <img id="preview"
                 src="<?= h(($_ENV['BASE_PATH'] ?? '') . ($details['image'] ?? '')) ?>"
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

          <!-- Badges -->
          <div class="badges">
            <span class="badge info"><?= $details['batch'] ? 'SY '.$details['batch'] : 'No SY' ?></span>
            <?php $active = (int)($details['status'] ?? 0) === 1; ?>
            <span class="badge <?= $active ? 'on':'off' ?>"><?= $active ? 'ACTIVE' : 'INACTIVE' ?></span>
          </div>

          <!-- Quick facts -->
          <div style="display:grid; gap:.55rem; margin-top:12px;">
            <div class="kv"><span class="k">LRN</span><span class="v" title="<?= h($lrnDigits) ?>"><?= h($lrnDigits ?: '—') ?></span></div>
            <div class="kv"><span class="k">Name</span><span class="v" title="<?= h($full) ?>"><?= h($full ?: '—') ?></span></div>
            <div class="kv"><span class="k">Grade</span><span class="v" title="<?= h($details['grade_level'] ?? '') ?>"><?= h($details['grade_level'] ?? '—') ?></span></div>
            <div class="kv"><span class="k">Section</span><span class="v" title="<?= h($sectionClean) ?>"><?= h($sectionClean ?: '—') ?></span></div>
          </div>

        </div>
      </div>
    </aside>

    <!-- ===== Right Main Content ===== -->
    <main class="card">
      <div class="card-head">
        <div class="mark">📄</div>
        <h5 class="title">Student Profile</h5>

        <div class="head-actions">
          <button
            class="btn-qr"
            id="btnShowQr"
            <?php if (!$canShowQR): ?>disabled<?php endif; ?>
            data-qrsrc="<?= h((string)$qrSrc) ?>"
            data-lrn="<?= h((string)$lrnDigits) ?>"
            title="<?= $canShowQR ? 'View QR Code' : 'QR not available (no valid LRN yet)'; ?>"
          >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
              <path d="M3 3h8v8H3zM13 3h8v8h-8zM3 13h8v8H3zM16 13h2v2h-2zM20 13h1v4h-1zM16 17h2v4h-2zM19 20h2v1h-2z"/>
            </svg>
            View QR Code
          </button>
        </div>
      </div>

      <div class="card-body">

        <!-- Core details -->
        <div class="section">
          <div class="section-title">Core Details</div>
          <div class="grid auto-fit">
            <div>
              <label class="form-label">Student Name</label>
              <input type="text" class="form-control" value="<?= h($full) ?>" readonly>
            </div>
            <div>
              <label class="form-label">LRN</label>
              <input type="text" class="form-control" value="<?= h($lrnDigits) ?>" readonly>
            </div>
            <div>
              <label class="form-label">Gender</label>
              <input type="text" class="form-control" value="<?= h($details['gender'] ?? '') ?>" readonly>
            </div>
            <div>
              <label class="form-label">Birth Date</label>
              <input type="text" class="form-control" value="<?= h($details['dateof_birth'] ?? '') ?>" readonly>
            </div>
            <div>
              <label class="form-label">Age</label>
              <input type="text" class="form-control" value="<?= h($details['age'] ?? '') ?>" readonly>
            </div>
            <div>
              <label class="form-label">Learning Modality</label>
              <input type="text" class="form-control" value="<?= h($details['learning_modality'] ?? '') ?>" readonly>
            </div>
          </div>
        </div>

        <hr class="hr-soft">

        <!-- Demographics -->
        <div class="section">
          <div class="section-title">Demographics</div>
          <div class="grid cols-3">
            <div>
              <label class="form-label">Mother Tongue</label>
              <input type="text" class="form-control" value="<?= h($details['mother_tongue'] ?? '') ?>" readonly>
            </div>
            <div>
              <label class="form-label">Religion</label>
              <input type="text" class="form-control" value="<?= h($details['religion'] ?? '') ?>" readonly>
            </div>
            <div>
              <label class="form-label">House/Street/Sitio/Purok</label>
              <input type="text" class="form-control" value="<?= h($details['house_street_sitio_purok'] ?? '') ?>" readonly>
            </div>
            <div>
              <label class="form-label">Barangay</label>
              <input type="text" class="form-control" value="<?= h($details['barangay'] ?? '') ?>" readonly>
            </div>
            <div>
              <label class="form-label">Municipality/City</label>
              <input type="text" class="form-control" value="<?= h($details['municipality_city'] ?? '') ?>" readonly>
            </div>
            <div>
              <label class="form-label">Province</label>
              <input type="text" class="form-control" value="<?= h($details['province'] ?? '') ?>" readonly>
            </div>
          </div>
        </div>

        <hr class="hr-soft">

        <!-- Parents / Guardian -->
        <div class="section">
          <div class="section-title">Parents / Guardian</div>
          <div class="grid cols-3">
            <div>
              <label class="form-label">Father's Name</label>
              <input type="text" class="form-control" value="<?= h($details['father_name'] ?? '') ?>" readonly>
            </div>
            <div>
              <label class="form-label">Mother's Name</label>
              <input type="text" class="form-control" value="<?= h($details['mother_name'] ?? '') ?>" readonly>
            </div>
            <div>
              <label class="form-label">Guardian</label>
              <input type="text" class="form-control" value="<?= h($details['guardian'] ?? '') ?>" readonly>
            </div>
            <div>
              <label class="form-label">Relationship</label>
              <input type="text" class="form-control" value="<?= h($details['relationship'] ?? '') ?>" readonly>
            </div>
            <div>
              <label class="form-label">Contact Number</label>
              <input type="text" class="form-control" value="<?= h($details['contact_no_of_parent'] ?? '') ?>" readonly>
            </div>
          </div>
        </div>

        <hr class="hr-soft">

        <!-- Enrollment -->
        <div class="section">
          <div class="section-title">Enrollment</div>
          <div class="grid cols-3">
            <div>
              <label class="form-label">Grade Level</label>
              <input type="text" class="form-control" value="<?= h($details['grade_level'] ?? '') ?>" readonly>
            </div>
            <div>
              <label class="form-label">Section</label>
              <input type="text" class="form-control" value="<?= h($sectionClean) ?>" readonly>
            </div>
            <div>
              <label class="form-label">School Year</label>
              <input type="text" class="form-control" value="<?= h($details['batch'] ?? '') ?>" readonly>
            </div>
          </div>
        </div>

      </div>
    </main>

  </div>
</div>

<!-- Lightweight QR modal (keeps your existing IDs & structure) -->
<div class="qr-modal-backdrop" id="qrBackdrop" role="dialog" aria-modal="true" aria-labelledby="qrTitle">
  <div class="qr-modal">
    <div class="qr-modal-head">
      <strong id="qrTitle">Student QR Code</strong>
      <button class="qr-close" id="qrClose" aria-label="Close">&times;</button>
    </div>
    <div class="qr-modal-body">
      <img id="qrImg" alt="Student QR Code">
    </div>
    <div class="qr-hint" id="qrHint">Tip: right-click the image to download or print.</div>
  </div>
</div>
