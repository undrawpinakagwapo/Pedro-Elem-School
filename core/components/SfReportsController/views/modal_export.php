<?php
$FORM_LABEL = [
  'sf2'  => 'School Form 2 (SF2) • Daily Attendance',
  'sf9'  => 'School Form 9 (SF9) • Progress Report',
  'sf10' => 'School Form 10 (SF10) • Permanent Record',
];
?>

<style>
/* Modal card styling */
.sge-modal-card {
  max-width: 650px !important;
  width: 100%;
  margin: 40px auto;
  border-radius: 16px;
  overflow: hidden;
  border: 1px solid #eef2f7;
  box-shadow: 0 20px 50px rgba(15,23,42,0.25);
  background: #fff;
  padding: 0;
}

/* Header styling */
.sge-exp-head {
  display:flex; align-items:center; justify-content:space-between; gap:10px;
  padding:12px 14px;
  background: linear-gradient(180deg,#fafafa,#ffffff);
  border-bottom:1px solid #eef2f7;
}
.sge-exp-left { display:flex; align-items:center; gap:10px; }
.sge-exp-accent { width:4px; height:18px; background:#3b82f6; border-radius:2px; }
.sge-exp-title { font-weight:800; color:#0f172a; font-size:15px; }
.sge-exp-sub { color:#64748b; font-size:12.5px; }

/* Body styling */
.sge-exp-body { padding:16px; }
.sge-row { display:grid; grid-template-columns: 140px 1fr; gap:10px; align-items:center; margin-bottom:12px; }
@media (max-width:640px){ .sge-row { grid-template-columns: 1fr; } }
.sge-label { font-size:13px; color:#64748b; }

.sge-select {
  border:1px solid #e5e7eb; border-radius:8px; background:#fff; padding:8px 10px;
  font-size:14px; color:#0f172a; width:100%;
}
.sge-select:focus {
  border-color:#3b82f6; box-shadow:0 0 0 3px rgba(59,130,246,.1);
}

.sge-tip { color:#94a3b8; font-size:12.5px; padding-top:8px; border-top:1px solid #eef2f7; margin-top:8px; }
</style>

<div class="sge-modal-card">
  <!-- Header -->
  <div class="sge-exp-head">
    <div class="sge-exp-left">
      <span class="sge-exp-accent"></span>
      <div class="sge-exp-title"><?= htmlspecialchars($FORM_LABEL[$form] ?? 'Export Options') ?></div>
    </div>
    <div class="sge-exp-sub">Choose filters then click Export</div>
  </div>

  <!-- Body -->
  <div class="sge-exp-body">
    <div class="sge-row">
      <label class="sge-label" for="sfr_grade">Grade Level</label>
      <select id="sfr_grade" class="sge-select">
        <?php foreach ($grades as $g): ?>
          <option value="<?= (int)$g['id'] ?>" <?= ((int)$g['id']===(int)$grade_id?'selected':'') ?>>
            Grade <?= htmlspecialchars($g['grade_name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="sge-row">
      <label class="sge-label" for="sfr_section">Section</label>
      <select id="sfr_section" class="sge-select">
        <?php foreach ($sections as $s): ?>
          <option value="<?= (int)$s['id'] ?>" <?= ((int)$s['id']===(int)$section_id?'selected':'') ?>>
            <?= htmlspecialchars($s['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="sge-row">
      <label class="sge-label" for="sfr_curriculum">School Year</label>
      <select id="sfr_curriculum" class="sge-select">
        <?php foreach ($curricula as $c): ?>
          <option value="<?= (int)$c['id'] ?>" <?= ((int)$c['id']===(int)$curriculum_id?'selected':'') ?>>
            <?= htmlspecialchars($c['school_year']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <?php if ($form === 'sf9' || $form === 'sf10'): ?>
    <div class="sge-row">
      <label class="sge-label" for="sfr_student">Student</label>
      <select id="sfr_student" class="sge-select">
        <option value="">-- Select Student --</option>
      </select>
    </div>
    <?php endif; ?>

    <?php if ($form === 'sf2'): ?>
    <div class="sge-row">
      <label class="sge-label">Month</label>
      <select id="sfr_month" class="sge-select">
        <?php foreach ($months as $m): ?>
          <option value="<?= $m['value'] ?>" <?= ($m['value']===$month?'selected':'') ?>>
            <?= htmlspecialchars($m['label']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <?php endif; ?>

    <input type="hidden" id="sfr_form" value="<?= htmlspecialchars($form) ?>">

    <div class="sge-tip">
      <?php if ($form === 'sf2'): ?>
        SF2 uses the selected School Year + Month (YYYY-MM-01).
      <?php elseif ($form === 'sf9'): ?>
        SF9 exports a Progress Report Card for one student.
      <?php else: ?>
        SF10 exports a Permanent Record.
      <?php endif; ?>
    </div>
  </div>

  <!-- Footer -->
  <div style="border-top:1px solid #eef2f7; padding:12px 16px; display:flex; gap:10px; justify-content:flex-end;">
    <button type="button" onclick="sfReportsDoExport()" style="border:0; background:#3b82f6; color:#fff; padding:8px 14px; border-radius:10px; cursor:pointer; font-weight:600;">Export</button>
  </div>
</div>
