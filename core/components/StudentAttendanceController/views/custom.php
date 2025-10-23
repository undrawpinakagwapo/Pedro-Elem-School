<?php
// Expecting: $sections, $section_id, $curricula, $curriculum_id, $date, $rows
$active_sy = '';
if (!empty($curricula) && $curriculum_id) {
  foreach ($curricula as $c) { if ((int)$c['id'] === (int)$curriculum_id) { $active_sy = $c['school_year'] ?? ''; break; } }
}
?>
<script>
window.__SAE_BOOT__ = <?= json_encode([
  'sections'      => $sections ?? [],
  'section_id'    => $section_id ?? null,
  'curricula'     => $curricula ?? [],
  'curriculum_id' => $curriculum_id ?? null,
  'date'          => $date ?? date('Y-m-d'),
  'rows'          => $rows ?? [],
  'is_holiday'    => false, // UI will override with localStorage
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

/* Card spans the full viewport; grid lets the table area scroll */
.sgx-card{
  width: 100%;
  min-height: 100dvh;
  display: grid;
  grid-template-rows:
    auto
    1fr
    auto
    auto;
  background:#fff;
  border-radius: 0;
  box-shadow:none;
  border:none;
}

/* Header */
.sgx-header{ padding: var(--page-pad); padding-bottom: calc(var(--page-pad) - 6px); }
.sgx-titlebar{ display:flex; align-items:center; gap:10px; margin-bottom:10px; }
.sgx-accent{ width:4px; height:20px; background:#3b82f6; border-radius:2px; }
.sgx-title{ font-weight:700; color:#0f172a; font-size:16px; }
.sgx-subtle{ color:#64748b; font-size:13px; margin:6px 0 0 0; }

/* toolbar & inputs */
.sgx-toolbar{ display:flex; align-items:center; gap:18px; flex-wrap:wrap; }
.sgx-filter{ display:flex; align-items:center; gap:10px; }
.sgx-label{ font-size:13px; color:#64748b; }
.sgx-grow{ flex:1 1 auto; }

.sgx-select, .sgx-date, .sgx-search{
  border:1px solid #e5e7eb; border-radius:8px; background:#fff; padding:8px 10px; outline:none; font-size:14px; color:#0f172a;
}
.sgx-select{ min-width:220px; }
.sgx-date{ min-width:160px; }
.sgx-search{ min-width:240px; }
.sgx-select:focus, .sgx-date:focus, .sgx-search:focus{ border-color:#3b82f6; box-shadow:0 0 0 3px rgba(59,130,246,.1); }

/* Table area */
.sgx-tablewrap{ min-height: 0; overflow: auto; border-top: 1px solid #eef2f7; }
.sgx-table{ width:100%; border-collapse:separate; border-spacing:0; min-width:820px; }
.sgx-table thead th{ font-size:12.5px; font-weight:700; color:#64748b; background:#f9fafb; border-bottom:1px solid #eef2f7; padding:12px 14px; text-align:left; }
.sgx-table thead th.text-center{ text-align:center; }
.sgx-table tbody td{ border-bottom:1px solid #f1f5f9; padding:12px 14px; font-size:14px; color:#0f172a; }
.sgx-table tbody tr:hover{ background:#fafcff; }
.text-center{ text-align:center; }

/* checkbox only */
.sae-chk{ width:18px; height:18px; cursor:pointer; }
.sae-chk:focus{ outline:2px solid #3b82f6; outline-offset:2px; }

/* remarks */
.sgx-remarks{
  width:100%; max-width:420px; border:1px solid #e5e7eb; border-radius:8px; padding:8px 10px; font-size:14px; outline:none; background:#fff;
}
.sgx-remarks:focus{ border-color:#3b82f6; box-shadow:0 0 0 3px rgba(59,130,246,.1); }

/* auto-remarks pill above the dropdown */
.sae-auto-remarks{
  display:inline-block;
  margin-bottom:6px;
  padding:4px 10px;
  font-size:12.5px;
  border-radius:999px;
  border:1px solid #e5e7eb;
  background:#f8fafc;
  color:#0f172a;
}

/* tip + footer */
.sgx-tip{ color:#94a3b8; font-size:12.5px; padding: var(--page-pad); border-top:1px solid #eef2f7; }
.sgx-footer{ display:flex; flex-wrap:wrap; gap:12px; justify-content:flex-end; padding: var(--page-pad); border-top:1px solid #eef2f7; background:#fff; }
.sgx-btn{ border:0; padding:10px 16px; border-radius:10px; font-weight:600; cursor:pointer; font-size:14px; }
.sgx-btn-primary{ background:#3b82f6; color:#fff; }
.sgx-btn-primary:hover{ background:#2e6fe0; }
.sgx-btn-outline{ background:#fff; color:#ef4444; border:1px solid #fecaca; }
.sgx-btn-outline:hover{ background:#fef2f2; }

.sgx-linkbtn{ background:none; border:0; padding:0 2px; margin-left:6px; cursor:pointer; font-size:12px; color:#3b82f6; }
.sgx-linkbtn:hover{ text-decoration:underline; }
.sgx-badge{ display:none; padding:2px 8px; font-size:12px; border-radius:999px; background:#fee2e2; color:#b91c1c; border:1px solid #fecaca; margin-left:8px; }

/* Scanner overlay */
#qrOverlay[hidden]{ display:none; }
#qrOverlay{
  position:fixed; inset:0; background:rgba(0,0,0,.6); z-index:9999;
  display:flex; align-items:center; justify-content:center; padding:16px;
}
.qr-sheet{
  width:min(720px, 96vw); background:#0b1220; border-radius:16px; overflow:hidden; box-shadow:0 10px 30px rgba(0,0,0,.3);
}
.qr-head{
  display:flex; align-items:center; justify-content:space-between;
  padding:10px 14px; color:#cbd5e1; border-bottom:1px solid rgba(255,255,255,.08);
}
.qr-head strong{ color:#fff; }
.qr-body{ padding:14px; display:grid; gap:12px; }
#qrVideo{
  width:100%; aspect-ratio: 16 / 10; background:#000; border-radius:12px; object-fit:cover;
}
.qr-actions{ display:flex; gap:8px; justify-content:flex-end; }
.qr-btn{ border:1px solid rgba(255,255,255,.2); background:transparent; color:#fff; padding:8px 12px; border-radius:10px; cursor:pointer; }
.qr-btn:hover{ background:rgba(255,255,255,.06); }

/* row flash when scanned */
@keyframes saeHit {
  0% { background:#e6fffa; }
  100% { background:transparent; }
}
tr.sae-hit { animation: saeHit .9s ease-out 1; }

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
        <span class="sgx-title">Student Attendance</span>
        <span id="saeHolidayBadge" class="sgx-badge">HOLIDAY</span>
      </div>

      <div class="sgx-toolbar" style="margin-bottom:8px;">
        <div class="sgx-filter">
          <label class="sgx-label">Section</label>
          <select id="saeSection" class="sgx-select">
            <?php if (!empty($sections)) foreach ($sections as $s): ?>
              <option value="<?= (int)$s['id'] ?>" <?= ((int)$s['id'] === (int)($section_id ?? 0) ? 'selected' : '') ?>>
                <?= 'Grade '.($s['grade_name'] ?? '').' - '.($s['name'] ?? '') ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="sgx-filter">
          <label class="sgx-label">School Year</label>
          <select id="saeCurriculum" class="sgx-select">
            <?php if (!empty($curricula)) foreach ($curricula as $c): ?>
              <option value="<?= (int)$c['id'] ?>" <?= ((int)$c['id'] === (int)($curriculum_id ?? 0) ? 'selected' : '') ?>>
                <?= $c['school_year'] ?? '' ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="sgx-filter">
          <label class="sgx-label">Date</label>
          <input id="saeDate" type="date" class="sgx-date" value="<?= htmlspecialchars($date ?? date('Y-m-d')) ?>">
        </div>

        <!-- Gender-only Filter dropdown -->
        <div class="sgx-filter">
          <label class="sgx-label">Gender</label>
          <select id="saeSortGender" class="sgx-select" aria-label="Filter by gender">
            <option value="ALL" selected>All</option>
            <option value="F">Female</option>
            <option value="M">Male</option>
          </select>
        </div>

        <div class="sgx-grow"></div>
        <input id="saeSearch" class="sgx-search" placeholder="Search name/LRN">
      </div>

      <?php if ($active_sy): ?>
        <div class="sgx-subtle">Active school year: <?= htmlspecialchars($active_sy) ?></div>
      <?php endif; ?>
    </div>

    <div class="sgx-tablewrap">
      <table id="saeTable" class="sgx-table">
        <thead>
          <tr>
            <th>Student Name</th>
            <th class="text-center">
              AM
              <button id="saeCheckAllAM" type="button" class="sgx-linkbtn" aria-label="Check all AM">Check All</button>
              <button id="saeScanAM" type="button" class="sgx-linkbtn" aria-label="Scan AM">Scan AM</button>
            </th>
            <th class="text-center">
              PM
              <button id="saeCheckAllPM" type="button" class="sgx-linkbtn" aria-label="Check all PM">Check All</button>
              <button id="saeScanPM" type="button" class="sgx-linkbtn" aria-label="Scan PM">Scan PM</button>
            </th>
            <th>Remarks</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($rows)) foreach ($rows as $r): ?>
            <?php
              $amChecked = ((string)($r['am_status'] ?? '') === 'P') ? 'checked' : '';
              $pmChecked = ((string)($r['pm_status'] ?? '') === 'P') ? 'checked' : '';
              $rv = (string)($r['remarks'] ?? '');
            ?>
            <tr data-student="<?= (int)$r['student_id'] ?>" data-lrn="<?= htmlspecialchars(preg_replace('/\D+/','',$r['LRN'] ?? '')) ?>">
              <td>
                <div style="font-weight:600;"><?= htmlspecialchars($r['full_name'] ?? '') ?></div>
                <div style="color:#64748b; font-size:12.5px;">LRN: <?= htmlspecialchars($r['LRN'] ?? '') ?></div>
              </td>
              <td class="text-center">
                <input type="checkbox" class="sae-chk" name="am_status_<?= (int)$r['student_id'] ?>" data-k="am_status" aria-label="Mark AM present" <?= $amChecked ?>>
              </td>
              <td class="text-center">
                <input type="checkbox" class="sae-chk" name="pm_status_<?= (int)$r['student_id'] ?>" data-k="pm_status" aria-label="Mark PM present" <?= $pmChecked ?>>
              </td>
              <td>
                <!-- auto-computed text + Tardy dropdown only -->
                <div class="sae-auto-remarks" data-k="auto_remarks" aria-live="polite"></div>
                <select class="sgx-remarks" data-k="remarks" aria-label="Remarks (Tardy only)">
                  <option value="" <?= ($rv === '' ? 'selected' : '') ?>>—</option>
                  <option value="Tardy"  <?= ($rv === 'Tardy'  ? 'selected' : '') ?>>Tardy</option>
                </select>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($rows)): ?>
            <tr><td colspan="4" class="text-center" style="color:#94a3b8; padding:22px;">No students found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <div class="sgx-tip">
      Check the box for Present; leave unchecked for Absent.
      <br><strong>Remarks are automatic:</strong> ABSENT, ABSENT AM, or ABSENT PM based on AM/PM status. Use the dropdown only if the student is <strong>TARDY</strong>.
      <br>Tip: for phone scanning you must open this page over <strong>HTTPS</strong> to allow camera access.
    </div>

    <div class="sgx-footer">
  <button id="saeExport" class="sgx-btn" style="background:#10b981; color:#fff;">Export SF2</button>
  <button id="saeSave"   class="sgx-btn sgx-btn-primary">Save Attendance</button>
  <!-- Hide Edit by default; JS shows it when locked -->
  <button id="saeEdit"   class="sgx-btn sgx-btn-outline" type="button"
          style="display:none; color:#2563eb; border-color:#bfdbfe;">
    Edit Attendance
  </button>
  <button id="saeHoliday" class="sgx-btn sgx-btn-outline" type="button">Mark as Holiday</button>
</div>

  </div>
</div>

<!-- ===== Scanner Overlay ===== -->
<div id="qrOverlay" hidden>
  <div class="qr-sheet">
    <div class="qr-head">
      <div>
        <strong>QR Scanner</strong> <span id="qrSlotLabel" style="opacity:.8">(AM)</span>
      </div>
      <div>
        <button id="qrSwitchBtn" class="qr-btn" type="button">Switch Camera</button>
        <button id="qrCloseBtn" class="qr-btn" type="button">Close</button>
      </div>
    </div>
    <div class="qr-body">
      <video id="qrVideo" autoplay playsinline muted></video>
    </div>
  </div>
</div>
