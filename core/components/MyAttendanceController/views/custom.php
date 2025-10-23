<?php
// Expecting: student_id, sections, section_id, school_year, date, days[]
?>
<script>
window.__MAE_BOOT__ = <?= json_encode([
  'student_id'  => $student_id ?? null,
  'sections'    => $sections ?? [],
  'section_id'  => $section_id ?? null,
  'school_year' => $school_year ?? '',
  'date'        => $date ?? date('Y-m-d'),
  'days'        => $days ?? [],
], JSON_UNESCAPED_UNICODE) ?>;
</script>

<style>
:root{ --page-pad: clamp(12px, 2vw, 20px); }
html, body { height: 100%; }
.sgx-page{ width:100%; min-height:100dvh; margin:0; padding:0; background:#f8fafc; }
.sgx-card{ width:100%; min-height:100dvh; display:grid; grid-template-rows:auto 1fr auto; background:#fff; border:none; border-radius:0; box-shadow:none; }
.sgx-header{ padding: var(--page-pad); padding-bottom: calc(var(--page-pad) - 6px); }
.sgx-titlebar{ display:flex; align-items:center; gap:10px; margin-bottom:10px; }
.sgx-accent{ width:4px; height:20px; background:#3b82f6; border-radius:2px; }
.sgx-title{ font-weight:700; color:#0f172a; font-size:16px; }

.sgx-toolbar{ display:flex; align-items:center; gap:18px; flex-wrap:wrap; }
.sgx-filter{ display:flex; align-items:center; gap:10px; }
.sgx-label{ font-size:13px; color:#64748b; }
.sgx-grow{ flex:1 1 auto; }

.sgx-select,.sgx-date{
  border:1px solid #e5e7eb; border-radius:8px; background:#fff; padding:8px 10px; outline:none; font-size:14px; color:#0f172a;
}
.sgx-select{ min-width:220px; }
.sgx-date{ min-width:160px; }
.sgx-select:focus,.sgx-date:focus{ border-color:#3b82f6; box-shadow:0 0 0 3px rgba(59,130,246,.1); }

/* school year pill */
.sgx-sy{ display:inline-block; padding:6px 10px; border-radius:999px; font-weight:700; font-size:12.5px; color:#3730a3; background:#eef2ff; }

.sgx-tablewrap{ min-height:0; overflow:auto; border-top:1px solid #eef2f7; }
.sgx-table{ width:100%; border-collapse:separate; border-spacing:0; min-width:720px; }
.sgx-table thead th{ font-size:12.5px; font-weight:700; color:#64748b; background:#f9fafb; border-bottom:1px solid #eef2f7; padding:12px 14px; text-align:left; }
.sgx-table thead th.text-center{ text-align:center; }
.sgx-table tbody td{ border-bottom:1px solid #f1f5f9; padding:12px 14px; font-size:14px; color:#0f172a; }
.sgx-table tbody tr:hover{ background:#fafcff; }
.text-center{ text-align:center; }

/* badges */
.badge{ display:inline-block; padding:6px 10px; border-radius:999px; font-weight:700; font-size:12.5px; color:#fff; background:#9ca3af; }
.badge.ok{   background:#16a34a; }
.badge.warn{ background:#ef4444; }
.badge.mute{ background:#9ca3af; }

/* footer/tip */
.sgx-tip{ color:#94a3b8; font-size:12.5px; padding: var(--page-pad); border-top:1px solid #eef2f7; }
</style>

<div class="sgx-page">
  <div class="sgx-card">
    <div class="sgx-header">
      <div class="sgx-titlebar">
        <span class="sgx-accent"></span>
        <span class="sgx-title">My Attendance</span>
      </div>

      <div class="sgx-toolbar" style="margin-bottom:8px;">
        <div class="sgx-filter">
          <label class="sgx-label">Section</label>
          <select id="maeSection" class="sgx-select">
            <?php if (!empty($sections)) foreach ($sections as $s): ?>
              <option value="<?= (int)$s['id'] ?>" <?= ((int)$s['id'] === (int)($section_id ?? 0) ? 'selected' : '') ?>>
                <?= 'Grade '.($s['grade_name'] ?? '').' - '.($s['name'] ?? '') ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="sgx-filter">
          <label class="sgx-label">School Year</label>
          <span id="maeSY" class="sgx-sy"><?= htmlspecialchars($school_year ?: '—') ?></span>
        </div>

        <div class="sgx-filter">
          <label class="sgx-label">Month</label>
          <input id="maeDate" type="date" class="sgx-date" value="<?= htmlspecialchars($date ?? date('Y-m-d')) ?>">
        </div>

        <div class="sgx-grow"></div>
        <span class="sgx-label" id="maeAutoNote">Auto-refreshing…</span>
      </div>

      <?php if (empty($student_id)): ?>
        <div class="sgx-label" style="color:#ef4444;margin-top:6px;">
          No student selected. Log in as a student or open with <code>?student_id=123</code>.
        </div>
      <?php endif; ?>
    </div>

    <div class="sgx-tablewrap">
      <table id="maeTable" class="sgx-table">
        <thead>
          <tr>
            <th>Date</th>
            <th>Day</th>
            <th class="text-center">AM</th>
            <th class="text-center">PM</th>
            <th>Remarks</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($days)): ?>
            <?php foreach ($days as $d): ?>
              <?php
                $am = $d['am_status'] ?? null;
                $pm = $d['pm_status'] ?? null;
                $rem = (string)($d['remarks'] ?? '');
                $isHol = !empty($d['is_holiday']);
                $badge = function($v){
                  if ($v === 'P') return '<span class="badge ok">Present</span>';
                  if ($v === 'A') return '<span class="badge warn">Absent</span>';
                  return '<span class="badge mute">—</span>';
                };
                // format YYYY-MM-DD -> YYYY-M-DD
                $dateOut = (string)($d['date'] ?? '');
                if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $dateOut, $m)) {
                  $dateOut = $m[1] . '-' . ((int)$m[2]) . '-' . $m[3];
                }
                if ($isHol) $dateOut .= ' - Holiday';
              ?>
              <tr>
                <td><?= htmlspecialchars($dateOut) ?></td>
                <td><?= htmlspecialchars($d['weekday'] ?? '') ?></td>
                <td class="text-center"><?= $isHol ? '<span class="badge mute">—</span>' : $badge($am) ?></td>
                <td class="text-center"><?= $isHol ? '<span class="badge mute">—</span>' : $badge($pm) ?></td>
                <td><?= $isHol ? '—' : htmlspecialchars($rem ?: '—') ?></td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="5" class="text-center" style="color:#94a3b8;padding:22px;">No entries for this month.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <div class="sgx-tip">
      Pick any date in a month to view the entire month. Holidays will show as “- Holiday” next to the date.
    </div>
  </div>
</div>
