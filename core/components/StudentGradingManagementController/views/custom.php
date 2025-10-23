<?php
if (!function_exists('e')) { function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); } }
$sections      = $sections      ?? [];
$section_id    = $section_id    ?? null;
$curricula     = $curricula     ?? [];
$curriculum_id = $curriculum_id ?? null;
$subjects      = $subjects      ?? [];
$subject_id    = $subject_id    ?? null;
$rows          = $rows          ?? [];

function sel($a,$b){ return (string)$a===(string)$b?' selected':''; }
?>
<style>
/* ===== Saved Grades (scoped to .sgm2) ===== */
.sgm2-card{
  background:#fff; border-radius:12px; box-shadow:0 6px 18px rgba(16,24,40,.06);
  border:1px solid #eef2f7; overflow:hidden;
}
.sgm2-header{ padding:16px 18px 10px 18px; }
.sgm2-titlebar{ display:flex; align-items:center; gap:10px; margin-bottom:10px; }
.sgm2-accent{ width:4px; height:20px; background:#3b82f6; border-radius:2px; }
.sgm2-title{ font-weight:700; color:#0f172a; font-size:16px; }

/* Filters row */
.sgm2-toolbar{ display:flex; align-items:center; gap:18px; flex-wrap:wrap; }
.sgm2-filter{ display:flex; align-items:center; gap:10px; }
.sgm2-label{ font-size:13px; color:#64748b; }
.sgm2-grow{ flex:1 1 auto; }

.sgm2-select{
  border:none; border-bottom:1px solid #e5e7eb; background:transparent; padding:6px 22px 6px 6px;
  outline:none; min-height:34px; font-size:14px; color:#0f172a; border-radius:0;
}
.sgm2-select:focus{ border-bottom-color:#3b82f6; }

.sgm2-search{
  min-width:240px; padding:8px 10px; border:1px solid #e5e7eb; border-radius:8px;
  outline:none; font-size:14px;
}
.sgm2-search:focus{ border-color:#3b82f6; box-shadow:0 0 0 3px rgba(59,130,246,.1); }

/* Table */
.sgm2-tablewrap{ padding:0; }
.sgm2-table{ width:100%; border-collapse:separate; border-spacing:0; }
.sgm2-table thead th{
  font-size:12.5px; font-weight:700; color:#64748b; letter-spacing:.02em;
  background:#f9fafb; border-top:1px solid #eef2f7; border-bottom:1px solid #eef2f7;
  padding:12px 14px; text-align:left;
}
.sgm2-table thead th.text-center{ text-align:center; }
.sgm2-table tbody td{
  border-bottom:1px solid #f1f5f9; padding:12px 14px; font-size:14px; color:#0f172a; vertical-align:middle;
}
.sgm2-table tbody tr:hover{ background:#fafcff; }
.text-center{ text-align:center; }

/* Average pill (your JS outputs <span class="badge ...">; we beautify inside .sgm2 only) */
.sgm2 .badge{
  display:inline-block; min-width:60px; padding:6px 12px; border-radius:16px; font-weight:700;
  font-size:12.5px; text-align:center; color:#fff;
}
.sgm2 .badge-success{ background:#16a34a; }
.sgm2 .badge-warning{ background:#f59e0b; color:#1f2937; }
.sgm2 .badge-secondary{ background:#9ca3af; }

/* Light layout stretch */
.sgm2-wrap{
  width:calc(100% + 1rem); margin-left:-0.5rem; margin-right:-0.5rem;
}
@media (min-width: 992px){
  .sgm2-wrap{ width:calc(100% + 1.5rem); margin-left:-0.75rem; margin-right:-0.75rem; }
}
</style>

<div class="sgm2 sgm2-wrap">
  <div class="sgm2-card">
    <div class="sgm2-header">
      <div class="sgm2-titlebar">
        <span class="sgm2-accent"></span>
        <span class="sgm2-title">Saved Grades</span>
      </div>

      <div class="sgm2-toolbar">
        <div class="sgm2-filter">
          <label for="sgmSection" class="sgm2-label">Section</label>
          <select id="sgmSection" class="sgm2-select" style="min-width:240px;">
            <?php foreach ($sections as $s): ?>
              <?php $label = (isset($s['grade_name']) && $s['grade_name'] !== '' ? 'Grade '.e($s['grade_name']).' - ' : '').e($s['name']); ?>
              <option value="<?= e($s['id']) ?>"<?= sel($s['id'],$section_id) ?>><?= $label ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="sgm2-filter">
          <label for="sgmCurriculum" class="sgm2-label">School Year</label>
          <select id="sgmCurriculum" class="sgm2-select" style="min-width:160px;"></select>
        </div>

        <div class="sgm2-filter">
          <label for="sgmSubject" class="sgm2-label">Subject</label>
          <select id="sgmSubject" class="sgm2-select" style="min-width:260px;"></select>
        </div>

        <div class="sgm2-grow"></div>

        <input type="text" id="sgmSearch" class="sgm2-search" placeholder="Search name/LRN">
      </div>
    </div>

    <div class="sgm2-tablewrap">
      <table class="sgm2-table" id="sgmTable">
        <thead>
          <tr>
            <th>Student</th>
            <th>LRN</th>
            <th>Section</th>
            <th>School Year</th>
            <th>Subject</th>
            <th class="text-center">Q1</th>
            <th class="text-center">Q2</th>
            <th class="text-center">Q3</th>
            <th class="text-center">Q4</th>
            <th class="text-center">Average</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</div>

<script>
/* Boot data for your existing JS (unchanged IDs) */
window.__SGM_BOOT__ = <?= json_encode([
  'curricula'     => $curricula,
  'curriculum_id' => $curriculum_id,
  'subjects'      => $subjects,
  'subject_id'    => $subject_id,
  'rows'          => $rows,
], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) ?>;
</script>
