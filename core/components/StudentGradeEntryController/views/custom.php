<?php
// $sections, $section_id, $curricula, $curriculum_id, $subjects, $subject_id, $rows


// derive active school year text
$active_sy = '';
if (!empty($curricula) && $curriculum_id) {
  foreach ($curricula as $c) { if ((int)$c['id'] === (int)$curriculum_id) { $active_sy = $c['school_year'] ?? ''; break; } }
}
?>
<script>
window.__SGE_BOOT__ = <?= json_encode([
  'sections'      => $sections ?? [],
  'section_id'    => $section_id ?? null,
  'curricula'     => $curricula ?? [],
  'curriculum_id' => $curriculum_id ?? null,
  'subjects'      => $subjects ?? [],
  'subject_id'    => $subject_id ?? null,
  'rows'          => $rows ?? [],
]) ?>;
</script>

<style>
/* ===== Full-screen, responsive layout ===== */
:root{ --page-pad: clamp(12px, 2vw, 20px); }
html, body { height: 100%; }

.sgx-page{
  width: 100%;
  min-height: 100dvh;
  margin: 0;
  padding: 0;
  background: #f8fafc;
}

/* Card spans the full viewport; uses grid so table area can scroll */
.sgx-card{
  width: 100%;
  min-height: 100dvh;
  display: grid;
  grid-template-rows:
    auto  /* header (title + filters + subtle) */
    1fr   /* table area (scrolls) */
    auto  /* tip */
    auto; /* footer */
  background:#fff;
  border-radius: 0;
  box-shadow: none;
  border: none;
}

/* Header */
.sgx-header{ padding: var(--page-pad); padding-bottom: calc(var(--page-pad) - 6px); }
.sgx-titlebar{ display:flex; align-items:center; gap:10px; margin-bottom:10px; }
.sgx-accent{ width:4px; height:20px; background:#3b82f6; border-radius:2px; }
.sgx-title{ font-weight:700; color:#0f172a; font-size:16px; }
.sgx-subtle{ color:#64748b; font-size:13px; margin:6px 0 0 0; }

/* toolbar & filters */
.sgx-toolbar{ display:flex; align-items:center; gap:18px; flex-wrap:wrap; }
.sgx-filter{ display:flex; align-items:center; gap:10px; }
.sgx-label{ font-size:13px; color:#64748b; }
.sgx-grow{ flex:1 1 auto; }

/* ==== DROPDOWNS & INPUTS ==== */
.sgx-select, .sgx-date, .sgx-search{
  border:1px solid #e5e7eb;
  border-radius:8px;
  background:#fff;
  padding:8px 10px;
  outline:none;
  font-size:14px;
  color:#0f172a;
}
.sgx-select{ min-width:220px; }
.sgx-date  { min-width:160px; }
.sgx-search{ min-width:240px; }

.sgx-select:focus,
.sgx-date:focus,
.sgx-search:focus{
  border-color:#3b82f6;
  box-shadow:0 0 0 3px rgba(59,130,246,.1);
}

/* table (fill remaining height and scroll) */
.sgx-tablewrap{
  min-height: 0;
  overflow: auto;
  border-top: 1px solid #eef2f7;
}
.sgx-table{ width:100%; border-collapse:separate; border-spacing:0; min-width:980px; }
.sgx-table thead th{ font-size:12.5px; font-weight:700; color:#64748b; background:#f9fafb; border-bottom:1px solid #eef2f7; padding:12px 14px; text-align:left; }
.sgx-table thead th.text-center{ text-align:center; }
.sgx-table tbody td{ border-bottom:1px solid #f1f5f9; padding:12px 14px; font-size:14px; color:#0f172a; }
.sgx-table tbody tr:hover{ background:#fafcff; }
.sge-table tbody tr:nth-of-type(odd) td:first-child[rowspan] {
  border-top: 2px solid #e2e8f0;
}

.text-center{ text-align:center; }

/* number inputs for grades */
.sgx-num{ width:110px; height:36px; text-align:center; border:1px solid #e5e7eb; border-radius:8px; font-size:14px; outline:none; background:#fff; }
.sgx-num:focus{ border-color:#3b82f6; box-shadow:0 0 0 3px rgba(59,130,246,.1); }

/* average pill */
.sgx-pill{ display:inline-block; min-width:60px; padding:6px 12px; border-radius:16px; font-weight:700; font-size:12.5px; text-align:center; color:#fff; background:#9ca3af; }
.sgx-pill.ok{   background:#16a34a; }
.sgx-pill.warn{ background:#f59e0b; }
.sgx-pill.mute{ background:#9ca3af; }

/* small button for table cell */
.sgx-btn-xs{
  border:0; padding:8px 12px; border-radius:10px; font-weight:600; cursor:pointer; font-size:13px;
  background:#f1f5f9; color:#0f172a;
}
.sgx-btn-xs:hover{ background:#e2e8f0; }

/* tip + footer */
.sgx-tip{ color:#94a3b8; font-size:12.5px; padding: var(--page-pad); border-top:1px solid #eef2f7; }
.sgx-footer{ display:flex; justify-content:flex-end; gap:12px; padding: var(--page-pad); border-top:1px solid #eef2f7; background:#fff; }
.sgx-btn{ border:0; padding:10px 16px; border-radius:10px; font-weight:600; cursor:pointer; font-size:14px; }
.sgx-btn-primary{ background:#3b82f6; color:#fff; }
.sgx-btn-primary:hover{ background:#2e6fe0; }

/* ===== Modal (shared styles) ===== */
#sgeModalBackdrop,
#sgeSF10Backdrop,
#sgeSF9Backdrop,
#sgeCoreBackdrop{
  display:none; position:fixed; inset:0; z-index:9998;
  background:rgba(15,23,42,.45); backdrop-filter: blur(2px);
}
#sgeModal,
#sgeSF10Modal,
#sgeSF9Modal,
#sgeCoreModal{
  display:none; position:fixed; inset:0; z-index:9999;
  pointer-events:none;
  padding: 24px;
  overflow: auto;
}
.sge-modal-card{
  pointer-events:auto;
  max-width: 980px; margin: auto;
  background: #fff; border-radius: 14px;
  box-shadow: 0 30px 80px rgba(2,6,23,.35);
  overflow: hidden; border: 1px solid #eef2f7;
}
.sge-modal-head{
  display:flex; align-items:center; justify-content:space-between;
  padding: 14px 16px;
  background: linear-gradient(180deg, #fafafa, #ffffff);
  border-bottom: 1px solid #eef2f7;
}
.sge-modal-title{ font-weight:800; color:#0f172a; letter-spacing:.2px; }
.sge-modal-actions{ display:flex; align-items:center; gap:8px; }
.sge-iconbtn{
  border:0; background:#f1f5f9; color:#0f172a; cursor:pointer;
  width:34px; height:34px; border-radius:10px; font-size:18px; line-height:1;
  display:grid; place-items:center;
}
.sge-iconbtn:hover{ background:#e2e8f0; }
.sge-iconbtn:active{ transform: translateY(1px); }
.sge-modal-body{ padding: 16px; }

.sge-modal-row{
  display:grid; grid-template-columns: auto minmax(220px, 420px) 1fr; gap:12px; align-items:center;
  margin-bottom:12px;
}
@media (max-width: 640px){
  .sge-modal-row{ grid-template-columns: 1fr; }
}

.sge-mini{ font-size:13px; color:#64748b; }

.sge-meta{
  display:grid; grid-template-columns: repeat(2, minmax(200px, 1fr)); gap:8px 16px;
  margin: 12px 0 6px;
}
@media (max-width: 640px){
  .sge-meta{ grid-template-columns: 1fr; }
}
.sge-meta div{ color:#475569; font-size:13px; }
.sge-meta b{ color:#0f172a; }

.sge-tablewrap{
  overflow:auto; border:1px solid #eef2f7; border-radius:10px;
}
.sge-table{
  width:100%; border-collapse:separate; border-spacing:0; min-width:720px;
}
.sge-table thead th{
  position: sticky; top: 0; z-index: 1;
  text-align:left; padding:10px 12px; border-bottom:1px solid #e5e7eb;
  background:#f8fafc; color:#64748b; font-size:12.5px;
}
.sge-table tbody td{
  padding:10px 12px; border-bottom:1px solid #f1f5f9;
}
.sge-table tbody tr:nth-child(odd){ background:#ffffff; }
.sge-table tbody tr:nth-child(even){ background:#fcfdff; }
.sge-strong{ font-weight:700; color:#0f172a; }

.sge-badge{
  display:inline-block; padding:2px 8px; border-radius:999px; font-size:12px; font-weight:700;
  background:#eef2ff; color:#3730a3;
}

.sge-help{
  display:flex; align-items:center; gap:8px; margin-bottom:8px;
  font-size:12.5px; color:#64748b;
}

.sge-skeleton{
  height:140px; border-radius:10px; background:
  linear-gradient(90deg, #f8fafc, #eef2f7, #f8fafc);
  background-size:200% 100%; animation:sgeShimmer 1.3s infinite;
  border:1px solid #eef2f7;
}
@keyframes sgeShimmer{ 0%{background-position:200% 0} 100%{background-position:-200% 0} }

/* iOS safe-area */
@supports (padding: max(0px)){
  .sgx-header, .sgx-footer, .sgx-tip{
    padding-left: max(var(--page-pad), env(safe-area-inset-left));
    padding-right: max(var(--page-pad), env(safe-area-inset-right));
  }
}
</style>

<div class="sgx-page">
  <div class="sgx-card">
    <div class="sgx-header">
      <div class="sgx-titlebar">
        <span class="sgx-accent"></span>
        <span class="sgx-title">Student Grades</span>
      </div>

      <div class="sgx-toolbar" style="margin-bottom:8px;">
        <div class="sgx-filter">
          <label class="sgx-label">Section</label>
          <select id="sgeSection" class="sgx-select" style="min-width:240px;">
            <?php if (!empty($sections)) foreach ($sections as $s): ?>
              <option value="<?= (int)$s['id'] ?>" <?= ((int)$s['id'] === (int)($section_id ?? 0) ? 'selected' : '') ?>>
                <?= 'Grade '.($s['grade_name'] ?? '').' - '.($s['name'] ?? '') ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="sgx-filter">
          <label class="sgx-label">School Year</label>
          <select id="sgeCurriculum" class="sgx-select" style="min-width:160px;">
            <?php if (!empty($curricula)) foreach ($curricula as $c): ?>
              <option value="<?= (int)$c['id'] ?>" <?= ((int)$c['id'] === (int)($curriculum_id ?? 0) ? 'selected' : '') ?>>
                <?= $c['school_year'] ?? '' ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="sgx-filter">
          <label class="sgx-label">Subject</label>
          <select id="sgeSubject" class="sgx-select" style="min-width:240px;">
            <?php if (!empty($subjects)) foreach ($subjects as $s): ?>
              <option value="<?= (int)$s['id'] ?>" <?= ((int)$s['id'] === (int)($subject_id ?? 0) ? 'selected' : '') ?>>
                <?= ($s['code'] ? $s['code'].' - ' : '').($s['name'] ?? '') ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- ✅ Gender filter -->
        <div class="sgx-filter">
          <label class="sgx-label">Gender</label>
          <select id="sgeGender" class="sgx-select" style="min-width:120px;">
            <option value="ALL" selected>All</option>
            <option value="M">Male</option>
            <option value="F">Female</option>
          </select>
        </div>

        <div class="sgx-grow"></div>
        <input id="sgeSearch" class="sgx-search" placeholder="Search name/LRN" />
      </div>

      <?php if ($active_sy): ?>
        <div class="sgx-subtle">Active school year: <?= htmlspecialchars($active_sy) ?></div>
      <?php endif; ?>
    </div>

    <div class="sgx-tablewrap">
      <table id="sgeTable" class="sgx-table">
        <thead>
          <tr>
            <th>Student Name</th>
            <th class="text-center">1st Quarter</th>
            <th class="text-center">2nd Quarter</th>
            <th class="text-center">3rd Quarter</th>
            <th class="text-center">4th Quarter</th>
            <th class="text-center">Core Values</th>
            <th class="text-center">Average</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($rows)) foreach ($rows as $r):
            // normalize gender for attribute
            $g = strtoupper(trim((string)($r['gender'] ?? '')));
            if ($g === 'MALE' || $g === 'M' || $g === 'BOY') $g = 'M';
            elseif ($g === 'FEMALE' || $g === 'F' || $g === 'GIRL') $g = 'F';
            else $g = '';
          ?>
            <tr data-student="<?= (int)$r['student_id'] ?>" data-gender="<?= htmlspecialchars($g) ?>">
              <td>
                <div style="font-weight:600;"><?= htmlspecialchars($r['full_name'] ?? '') ?></div>
                <div style="color:#64748b; font-size:12.5px;">LRN: <?= htmlspecialchars($r['LRN'] ?? '') ?></div>
              </td>
              <td class="text-center"><input type="number" step="0.01" class="sgx-num sge-q" data-q="q1" min="0" max="100" value="<?= ($r['q1'] === null ? '' : number_format((float)$r['q1'],2)) ?>"></td>
              <td class="text-center"><input type="number" step="0.01" class="sgx-num sge-q" data-q="q2" min="0" max="100" value="<?= ($r['q2'] === null ? '' : number_format((float)$r['q2'],2)) ?>"></td>
              <td class="text-center"><input type="number" step="0.01" class="sgx-num sge-q" data-q="q3" min="0" max="100" value="<?= ($r['q3'] === null ? '' : number_format((float)$r['q3'],2)) ?>"></td>
              <td class="text-center"><input type="number" step="0.01" class="sgx-num sge-q" data-q="q4" min="0" max="100" value="<?= ($r['q4'] === null ? '' : number_format((float)$r['q4'],2)) ?>"></td>

              <!-- ✅ New Core Values button -->
              <td class="text-center">
                <button type="button" class="sgx-btn-xs sge-core-btn" data-student="<?= (int)$r['student_id'] ?>">
                  Core Values
                </button>
              </td>

              <?php
                $avgTxt = ($r['final_average'] === null ? '—' : number_format((float)$r['final_average'], 2));
                $avgCls = ($avgTxt !== '—' && (float)$r['final_average'] >= 75) ? 'ok' : ($avgTxt === '—' ? 'mute' : 'warn');
              ?>
              <td class="text-center"><span class="sgx-pill <?= $avgCls ?>"><?= $avgTxt ?></span></td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($rows)): ?>
            <tr><td colspan="7" class="text-center" style="color:#94a3b8;padding:22px;">No students found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <div class="sgx-tip">Tip: Grades are 0–100. Average auto-calculates.</div>

    <div class="sgx-footer">
      <button id="sgeGradeSlip" class="sgx-btn">Grade Slip</button>
      <!-- ✅ New: Export buttons -->
      <button id="sgeExportSF9" class="sgx-btn" style="background:#10b981; color:#fff; ">Export SF9</button>
      <button id="sgeExportSF10" class="sgx-btn" style="background:#10b981; color:#fff;">Export SF10</button>
      <button id="sgeSave" class="sgx-btn sgx-btn-primary">Save Grades</button>
      <button id="sgeEditGrades" class="sgx-btn" style="background:#f59e0b; color:#fff;">Edit Grades</button>
    </div>
  </div>
</div>

<!-- ===== Grade Slip Modal ===== -->
<div id="sgeModalBackdrop" aria-hidden="true"></div>

<div id="sgeModal" role="dialog" aria-modal="true" aria-labelledby="sgeModalTitle" aria-hidden="true">
  <div class="sge-modal-card">
    <div class="sge-modal-head">
      <div class="sge-modal-title" id="sgeModalTitle">Grade Slip</div>
      <div class="sge-modal-actions">
        <button id="sgeModalPrint" class="sge-iconbtn" aria-label="Print" title="Print">🖨️</button>
        <button id="sgeModalClose" class="sge-iconbtn" aria-label="Close dialog" title="Close">×</button>
      </div>
    </div>

    <div class="sge-modal-body">
      <div class="sge-help">
        <span class="sge-badge">Tip</span>
        <span>Select a student to view their grades for every subject this school year.</span>
      </div>

      <div class="sge-modal-row">
        <label class="sgx-label" for="sgeSlipStudent">Student</label>
        <select id="sgeSlipStudent" class="sgx-select" style="min-width:320px;"></select>
        <span class="sge-mini">Shows all subjects, even without grades.</span>
      </div>

      <div id="sgeSlipMeta" class="sge-meta"></div>

      <div id="sgeSlipTableWrap" class="sge-tablewrap">
        <!-- Loading skeleton gets injected here -->
      </div>
    </div>
  </div>
</div>

<!-- ===== Export SF10 Modal ===== -->
<div id="sgeSF10Backdrop" aria-hidden="true"></div>

<div id="sgeSF10Modal" role="dialog" aria-modal="true" aria-labelledby="sgeSF10Title" aria-hidden="true">
  <div class="sge-modal-card" style="max-width:680px;">
    <div class="sge-modal-head">
      <div class="sge-modal-title" id="sgeSF10Title">Export SF10</div>
      <div class="sge-modal-actions">
        <button id="sgeSF10Close" class="sge-iconbtn" aria-label="Close dialog" title="Close">×</button>
      </div>
    </div>

    <div class="sge-modal-body">
      <div class="sge-help">
        <span class="sge-badge">Tip</span>
        <span>Select a student and click Export to generate the SF10 using the official template.</span>
      </div>

      <div class="sge-modal-row">
        <label class="sgx-label" for="sgeSF10Student">Student</label>
        <select id="sgeSF10Student" class="sgx-select" style="min-width:320px;"></select>
        <span class="sge-mini">List comes from the table (respects gender filter & search).</span>
      </div>

      <div class="sge-modal-row">
        <div></div>
        <div>
          <button id="sgeSF10ExportBtn" class="sgx-btn sgx-btn-primary">Export</button>
        </div>
        <div></div>
      </div>
    </div>
  </div>
</div>

<!-- ✅ Export SF9 Modal -->
<div id="sgeSF9Backdrop" aria-hidden="true"></div>

<div id="sgeSF9Modal" role="dialog" aria-modal="true" aria-labelledby="sgeSF9Title" aria-hidden="true">
  <div class="sge-modal-card" style="max-width:680px;">
    <div class="sge-modal-head">
      <div class="sge-modal-title" id="sgeSF9Title">Export SF9</div>
      <div class="sge-modal-actions">
        <button id="sgeSF9Close" class="sge-iconbtn" aria-label="Close dialog" title="Close">×</button>
      </div>
    </div>

    <div class="sge-modal-body">
      <div class="sge-help">
        <span class="sge-badge">Tip</span>
        <span>Select a student and click Export to generate the SF9.</span>
      </div>

      <div class="sge-modal-row">
        <label class="sgx-label" for="sgeSF9Student">Student</label>
        <select id="sgeSF9Student" class="sgx-select" style="min-width:320px;"></select>
        <span class="sge-mini">List comes from the table (respects gender filter & search).</span>
      </div>

      <div class="sge-modal-row">
        <div></div>
        <div>
          <button id="sgeSF9ExportBtn" class="sgx-btn sgx-btn-primary">Export</button>
        </div>
        <div></div>
      </div>
    </div>
  </div>
</div>

<!-- ✅ Core Values Modal -->
<div id="sgeCoreBackdrop" aria-hidden="true"></div>

<div id="sgeCoreModal" role="dialog" aria-modal="true" aria-labelledby="sgeCoreTitle" aria-hidden="true">
  <div class="sge-modal-card" style="max-width:920px;">
    <div class="sge-modal-head">
      <div class="sge-modal-title" id="sgeCoreTitle">Core Values • <span id="sgeCoreStudentName"></span></div>
      <div class="sge-modal-actions">
        <button id="sgeCoreClose" class="sge-iconbtn" aria-label="Close dialog" title="Close">×</button>
      </div>
    </div>

    <div class="sge-modal-body">
      <div class="sge-help">
        <span class="sge-badge">Tip</span>
        <span>Set the learner’s observed values per quarter (AO, SO, RO, NO).</span>
      </div>

      <div class="sge-tablewrap">
        <table class="sge-table" aria-describedby="sgeCoreHint">
          <thead>
            <tr>
              <th>Core Values</th>
              <th>Behavior Statements (reference)</th>
              <th class="text-center">Q1</th>
              <th class="text-center">Q2</th>
              <th class="text-center">Q3</th>
              <th class="text-center">Q4</th>
            </tr>
          </thead>
          <tbody>
  <!-- 1. Maka-Diyos -->
  <tr>
    <td class="sge-strong" rowspan="2">1. Maka-Diyos</td>
    <td>
      Expresses one’s spiritual beliefs while respecting the spiritual beliefs of others.
    </td>
    <td class="text-center">
      <select class="sgx-select sge-cv" data-core="maka_diyos" data-q="q1" data-beh="1">
        <option value="">—</option><option>AO</option><option>SO</option><option>RO</option><option>NO</option>
      </select>
    </td>
    <td class="text-center">
      <select class="sgx-select sge-cv" data-core="maka_diyos" data-q="q2" data-beh="1">
        <option value="">—</option><option>AO</option><option>SO</option><option>RO</option><option>NO</option>
      </select>
    </td>
    <td class="text-center">
      <select class="sgx-select sge-cv" data-core="maka_diyos" data-q="q3" data-beh="1">
        <option value="">—</option><option>AO</option><option>SO</option><option>RO</option><option>NO</option>
      </select>
    </td>
    <td class="text-center">
      <select class="sgx-select sge-cv" data-core="maka_diyos" data-q="q4" data-beh="1">
        <option value="">—</option><option>AO</option><option>SO</option><option>RO</option><option>NO</option>
      </select>
    </td>
  </tr>
  <tr>
    <td>
      Shows adherence to ethical principles by upholding truth in all undertakings.
    </td>
    <td class="text-center">
      <select class="sgx-select sge-cv" data-core="maka_diyos" data-q="q1" data-beh="2">
        <option value="">—</option><option>AO</option><option>SO</option><option>RO</option><option>NO</option>
      </select>
    </td>
    <td class="text-center">
      <select class="sgx-select sge-cv" data-core="maka_diyos" data-q="q2" data-beh="2">
        <option value="">—</option><option>AO</option><option>SO</option><option>RO</option><option>NO</option>
      </select>
    </td>
    <td class="text-center">
      <select class="sgx-select sge-cv" data-core="maka_diyos" data-q="q3" data-beh="2">
        <option value="">—</option><option>AO</option><option>SO</option><option>RO</option><option>NO</option>
      </select>
    </td>
    <td class="text-center">
      <select class="sgx-select sge-cv" data-core="maka_diyos" data-q="q4" data-beh="2">
        <option value="">—</option><option>AO</option><option>SO</option><option>RO</option><option>NO</option>
      </select>
    </td>
  </tr>

  <!-- 2. Makatao -->
  <tr>
    <td class="sge-strong" rowspan="2">2. Makatao</td>
    <td>
      Is sensitive to individual, social, and cultural differences.
    </td>
    <td class="text-center">
      <!-- ✅ FIXED: closed quote in data-beh -->
      <select class="sgx-select sge-cv" data-core="makatao" data-q="q1" data-beh="1">
        <option value="">—</option><option>AO</option><option>SO</option><option>RO</option><option>NO</option>
      </select>
    </td>
    <td class="text-center">
      <!-- ✅ FIXED -->
      <select class="sgx-select sge-cv" data-core="makatao" data-q="q2" data-beh="1">
        <option value="">—</option><option>AO</option><option>SO</option><option>RO</option><option>NO</option>
      </select>
    </td>
    <td class="text-center">
      <!-- ✅ FIXED -->
      <select class="sgx-select sge-cv" data-core="makatao" data-q="q3" data-beh="1">
        <option value="">—</option><option>AO</option><option>SO</option><option>RO</option><option>NO</option>
      </select>
    </td>
    <td class="text-center">
      <!-- ✅ FIXED -->
      <select class="sgx-select sge-cv" data-core="makatao" data-q="q4" data-beh="1">
        <option value="">—</option><option>AO</option><option>SO</option><option>RO</option><option>NO</option>
      </select>
    </td>
  </tr>
  <tr>
    <td>
      Demonstrates contributions towards solidarity.
    </td>
    <td class="text-center">
      <select class="sgx-select sge-cv" data-core="makatao" data-q="q1" data-beh="2">
        <option value="">—</option><option>AO</option><option>SO</option><option>RO</option><option>NO</option>
      </select>
    </td>
    <td class="text-center">
      <select class="sgx-select sge-cv" data-core="makatao" data-q="q2" data-beh="2">
        <option value="">—</option><option>AO</option><option>SO</option><option>RO</option><option>NO</option>
      </select>
    </td>
    <td class="text-center">
      <select class="sgx-select sge-cv" data-core="makatao" data-q="q3" data-beh="2">
        <option value="">—</option><option>AO</option><option>SO</option><option>RO</option><option>NO</option>
      </select>
    </td>
    <td class="text-center">
      <select class="sgx-select sge-cv" data-core="makatao" data-q="q4" data-beh="2">
        <option value="">—</option><option>AO</option><option>SO</option><option>RO</option><option>NO</option>
      </select>
    </td>
  </tr>

  <!-- 3. Maka-Kalikasan -->
  <tr>
    <td class="sge-strong">3. Maka-Kalikasan</td>
    <td>
      Cares for the environment and uses resources wisely, judiciously, and economically.
    </td>
    <td class="text-center">
      <select class="sgx-select sge-cv" data-core="maka_kalikasan" data-q="q1" data-beh="1">
        <option value="">—</option><option>AO</option><option>SO</option><option>RO</option><option>NO</option>
      </select>
    </td>
    <td class="text-center">
      <select class="sgx-select sge-cv" data-core="maka_kalikasan" data-q="q2" data-beh="1">
        <option value="">—</option><option>AO</option><option>SO</option><option>RO</option><option>NO</option>
      </select>
    </td>
    <td class="text-center">
      <select class="sgx-select sge-cv" data-core="maka_kalikasan" data-q="q3" data-beh="1">
        <option value="">—</option><option>AO</option><option>SO</option><option>RO</option><option>NO</option>
      </select>
    </td>
    <td class="text-center">
      <select class="sgx-select sge-cv" data-core="maka_kalikasan" data-q="q4" data-beh="1">
        <option value="">—</option><option>AO</option><option>SO</option><option>RO</option><option>NO</option>
      </select>
    </td>
  </tr>

  <!-- 4. Maka-Bansa -->
  <tr>
    <td class="sge-strong" rowspan="2">4. Maka-Bansa</td>
    <td>
      Demonstrates pride in being a Filipino; exercises the rights and responsibilities of a Filipino.
    </td>
    <td class="text-center">
      <select class="sgx-select sge-cv" data-core="maka_bansa" data-q="q1" data-beh="1">
        <option value="">—</option><option>AO</option><option>SO</option><option>RO</option><option>NO</option>
      </select>
    </td>
    <td class="text-center">
      <select class="sgx-select sge-cv" data-core="maka_bansa" data-q="q2" data-beh="1">
        <option value="">—</option><option>AO</option><option>SO</option><option>RO</option><option>NO</option>
      </select>
    </td>
    <td class="text-center">
      <select class="sgx-select sge-cv" data-core="maka_bansa" data-q="q3" data-beh="1">
        <option value="">—</option><option>AO</option><option>SO</option><option>RO</option><option>NO</option>
      </select>
    </td>
    <td class="text-center">
      <select class="sgx-select sge-cv" data-core="maka_bansa" data-q="q4" data-beh="1">
        <option value="">—</option><option>AO</option><option>SO</option><option>RO</option><option>NO</option>
      </select>
    </td>
  </tr>
  <tr>
    <td>
      Demonstrate appropriate behavior in carrying out activities in school, community and country.
    </td>
    <td class="text-center">
      <select class="sgx-select sge-cv" data-core="maka_bansa" data-q="q1" data-beh="2">
        <option value="">—</option><option>AO</option><option>SO</option><option>RO</option><option>NO</option>
      </select>
    </td>
    <td class="text-center">
      <select class="sgx-select sge-cv" data-core="maka_bansa" data-q="q2" data-beh="2">
        <option value="">—</option><option>AO</option><option>SO</option><option>RO</option><option>NO</option>
      </select>
    </td>
    <td class="text-center">
      <select class="sgx-select sge-cv" data-core="maka_bansa" data-q="q3" data-beh="2">
        <option value="">—</option><option>AO</option><option>SO</option><option>RO</option><option>NO</option>
      </select>
    </td>
    <td class="text-center">
      <select class="sgx-select sge-cv" data-core="maka_bansa" data-q="q4" data-beh="2">
        <option value="">—</option><option>AO</option><option>SO</option><option>RO</option><option>NO</option>
      </select>
    </td>
  </tr>
</tbody>

        </table>
      </div>

      <div class="sge-modal-row" style="grid-template-columns: 1fr auto 1fr; margin-top:16px;">
        <div></div>
        <div>
          <button id="sgeCoreSave" class="sgx-btn sgx-btn-primary">Save</button>
        </div>
        <div></div>
      </div>

      <div id="sgeCoreHint" class="sge-mini" style="margin-top:6px;">
        AO – Always Observed, SO – Sometimes Observed, RO – Rarely Observed, NO – Not Observed
      </div>
    </div>
  </div>
</div>
