<?php
// $sections, $section_id, $curricula, $curriculum_id, $rows, $list_type, $can_edit
$active_sy = '';
if (!empty($curricula) && $curriculum_id) {
  foreach ($curricula as $c) {
    if ((int)$c['id'] === (int)$curriculum_id) {
      $active_sy = $c['school_year'] ?? '';
      break;
    }
  }
}
$list_type = ($list_type ?? 'summer') === 'retained' ? 'retained' : 'summer';
$can_edit = (bool)($can_edit ?? false);
?>
<script>
window.__SC_BOOT__ = <?= json_encode([
  'sections'      => $sections ?? [],
  'section_id'    => $section_id ?? null,
  'curricula'     => $curricula ?? [],
  'curriculum_id' => $curriculum_id ?? null,
  'rows'          => $rows ?? [],
  'list_type'     => $list_type,
  'can_edit'      => $can_edit,
]) ?>;
</script>

<style>
:root{ --page-pad: clamp(12px,2vw,20px); }
html,body{ height:100%; }
.scx-page{ width:100%; min-height:100dvh; background:#f8fafc; }
.scx-card{ width:100%; min-height:100dvh; display:grid; grid-template-rows:auto 1fr auto; background:#fff; }
.scx-header{ padding:var(--page-pad); padding-bottom:calc(var(--page-pad) - 6px); }
.scx-titlebar{ display:flex; align-items:center; gap:10px; margin-bottom:10px; }
.scx-accent{ width:4px; height:20px; background:#0ea5e9; border-radius:2px; }
.scx-title{ font-weight:800; color:#0f172a; font-size:16px; }
.scx-subtle{ color:#64748b; font-size:13px; margin-top:6px; }

.scx-toolbar{ display:flex; gap:16px; flex-wrap:wrap; align-items:center; }
.scx-select,.scx-input{ border:1px solid #e5e7eb; border-radius:8px; background:#fff; padding:8px 10px; font-size:14px; }
.scx-grow{ flex:1 1 auto; }

.scx-tablewrap{ min-height:0; overflow:auto; border-top:1px solid #eef2f7; }
.scx-table{ width:100%; border-collapse:separate; border-spacing:0; min-width:980px; }
.scx-table thead th{ font-size:12.5px; font-weight:700; color:#64748b; background:#f9fafb; border-bottom:1px solid #eef2f7; padding:12px 14px; text-align:left; }
.scx-table tbody td{ border-bottom:1px solid #f1f5f9; padding:12px 14px; font-size:14px; color:#0f172a; }
.text-center{ text-align:center; }
.scx-table tbody tr:hover{ background:#fafcff; }

.scx-footer{ padding:var(--page-pad); border-top:1px solid #eef2f7; color:#94a3b8; font-size:12.5px; }

/* buttons & badges */
.scx-btn{ border:0; padding:9px 14px; border-radius:10px; font-weight:600; cursor:pointer; font-size:14px; background:#0ea5e9; color:#fff; }
.scx-btn-ghost{ border:0; padding:8px 12px; border-radius:10px; font-weight:600; cursor:pointer; font-size:13px; background:#f1f5f9; color:#0f172a; }
.scx-btn-ghost:hover{ background:#e2e8f0; }
.sc-badge{ display:inline-block; padding:4px 8px; border-radius:999px; font-size:12px; font-weight:700; line-height:1; }
.sc-badge-danger{ background:#fee2e2; color:#991b1b; border:1px solid #fecaca; }
.sc-badge-muted{ background:#f1f5f9; color:#475569; border:1px solid #e2e8f0; }

/* ===== Remedial Modal ===== */
#scRemBackdrop{ display:none; position:fixed; inset:0; z-index:9998; background:rgba(15,23,42,.45); backdrop-filter: blur(2px); }
#scRemModal{ display:none; position:fixed; inset:0; z-index:9999; pointer-events:none; padding:24px; overflow:auto; }
.scm-card{ pointer-events:auto; max-width:980px; margin:auto; background:#fff; border-radius:14px; border:1px solid #eef2f7; box-shadow:0 30px 80px rgba(2,6,23,.35); overflow:hidden; }
.scm-head{ display:flex; align-items:center; justify-content:space-between; padding:14px 16px; background:linear-gradient(180deg,#fafafa,#ffffff); border-bottom:1px solid #eef2f7; }
.scm-title{ font-weight:800; color:#0f172a; }
.scm-actions{ display:flex; gap:8px; }
.scm-icon{ border:0; background:#f1f5f9; color:#0f172a; cursor:pointer; width:34px; height:34px; border-radius:10px; display:grid; place-items:center; }
.scm-icon:hover{ background:#e2e8f0; }
.scm-body{ padding:16px; }
.scm-mini{ font-size:12.5px; color:#64748b; margin-bottom:8px; }
.scm-row{ display:grid; grid-template-columns:auto minmax(180px,260px) auto minmax(180px,260px); gap:12px; align-items:center; margin-bottom:12px; }
@media (max-width: 680px){ .scm-row{ grid-template-columns:1fr; } }
.scm-input{ border:1px solid #e5e7eb; border-radius:8px; background:#fff; padding:8px 10px; font-size:14px; }
.scm-tablewrap{ overflow:auto; border:1px solid #eef2f7; border-radius:10px; }
.scm-table{ width:100%; border-collapse:separate; border-spacing:0; min-width:900px; }
.scm-table thead th{ position:sticky; top:0; z-index:1; text-align:left; padding:10px 12px; border-bottom:1px solid #e5e7eb; background:#f8fafc; color:#64748b; font-size:12.5px; }
.scm-table tbody td{ padding:10px 12px; border-bottom:1px solid #f1f5f9; }
.scm-footer{ display:flex; justify-content:flex-end; gap:10px; margin-top:12px; }
</style>

<div class="scx-page">
  <div class="scx-card">
    <div class="scx-header">
      <div class="scx-titlebar">
        <span class="scx-accent"></span>
        <span class="scx-title">Supplementary / Remedial Classes</span>
      </div>

      <div class="scx-toolbar" style="margin-bottom:8px;">
        <label>Section</label>
        <select id="scSection" class="scx-select" style="min-width:240px;"></select>

        <label>School Year</label>
        <select id="scCurriculum" class="scx-select" style="min-width:160px;"></select>

        <label>List</label>
        <select id="scListType" class="scx-select" style="min-width:220px;">
          <option value="summer">Summer-eligible (1–2 fails)</option>
          <option value="retained">Retained (3+ fails)</option>
        </select>

        <div class="scx-grow"></div>
        <input id="scSearch" class="scx-input" placeholder="Search name/LRN">
      </div>

      <?php if ($active_sy): ?>
      <div class="scx-subtle">Active school year: <?= htmlspecialchars($active_sy) ?></div>
      <?php endif; ?>
    </div>

    <div class="scx-tablewrap">
      <table class="scx-table" id="scTable">
        <thead>
          <tr>
            <th style="min-width:240px;">Student</th>
            <th>LRN</th>
            <th>Grade &amp; Section</th>
            <th class="text-center">Failed Subjects</th>
            <th>Subjects (Failed)</th>
            <th class="text-center">Status / Action</th>
          </tr>
        </thead>
        <tbody id="scTbody">
          <?php if (!empty($rows)) foreach ($rows as $s): ?>
          <tr data-student="<?= (int)$s['id'] ?>">
            <td><?= htmlspecialchars($s['full_name'] ?? '') ?></td>
            <td><?= htmlspecialchars($s['LRN'] ?? '') ?></td>
            <td><?= htmlspecialchars(($s['grade_name'] ?? '').' - '.($s['section_name'] ?? '')) ?></td>
            <td class="text-center"><?= (int)($s['failed_count'] ?? 0) ?></td>
            <td><?= htmlspecialchars($s['subjects_text'] ?? '') ?></td>
            <td class="text-center">
              <?php if (($s['status'] ?? 'eligible') === 'retained'): ?>
                <span class="sc-badge sc-badge-danger">RETAINED</span>
              <?php else: ?>
                <?php if ($can_edit): ?>
                  <button type="button" class="scx-btn-ghost sc-rem-btn"
                    data-name="<?= htmlspecialchars($s['full_name'] ?? '', ENT_QUOTES) ?>">Remedial</button>
                <?php else: ?>
                  <span class="sc-badge sc-badge-muted" title="Only the adviser can encode remedials.">Adviser only</span>
                <?php endif; ?>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($rows)): ?>
          <tr><td colspan="6" class="text-center" style="color:#94a3b8; padding:22px;">
            <?= $list_type === 'retained' ? 'No retained students.' : 'No students eligible for summer classes.' ?>
          </td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <div class="scx-footer">
      Tip: Use the “List” selector to switch between Summer-eligible and Retained students.
    </div>
  </div>
</div>

<!-- ===== Remedial Modal ===== -->
<div id="scRemBackdrop" aria-hidden="true"></div>
<div id="scRemModal" role="dialog" aria-modal="true" aria-labelledby="scRemTitle" aria-hidden="true">
  <div class="scm-card">
    <div class="scm-head">
      <div class="scm-title" id="scRemTitle">Remedial Classes • <span id="scRemStudentName"></span></div>
      <div class="scm-actions">
        <button id="scRemClose" class="scm-icon" title="Close">×</button>
      </div>
    </div>

    <div class="scm-body">
      <div class="scm-mini">
        Encoded per the official SF10 remedial block. “Recomputed Final Grade” defaults to the average of Final Rating and Remedial Class Mark (you can override).
      </div>

      <div class="scm-row">
        <label>Conducted from:</label>
        <input id="scRemFrom" type="date" class="scm-input" />
        <label>to</label>
        <input id="scRemTo" type="date" class="scm-input" />
      </div>

      <div class="scm-tablewrap">
        <table class="scm-table" aria-label="Remedial Table">
          <thead>
            <tr>
              <th>Learning Areas</th>
              <th>Final Rating</th>
              <th>Remedial Class Mark</th>
              <th>Recomputed Final Grade</th>
              <th>Remarks</th>
            </tr>
          </thead>
          <tbody id="scRemTbody">
            <tr><td colspan="5" class="text-center" style="color:#94a3b8;padding:14px;">Select a student…</td></tr>
          </tbody>
        </table>
      </div>

      <div class="scm-footer">
        <button id="scRemSave" class="scx-btn">Save</button>
      </div>
    </div>
  </div>
</div>
