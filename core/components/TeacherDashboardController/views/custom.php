<?php
// Expects: $sections, $section_id, $curricula, $curriculum_id, $date, $active_sy, helloName (optional)
ensureSessionStarted();

if (!isset($helloName) || trim((string)$helloName) === '') {
  // Build from session if controller didn't pass it
  $first  = trim((string)($_SESSION['account_first_name']  ?? ''));
  $middle = trim((string)($_SESSION['account_middle_name'] ?? ''));
  $last   = trim((string)($_SESSION['account_last_name']   ?? ''));
  if ($first !== '' || $last !== '') {
    $mi = $middle !== '' ? (mb_substr($middle, 0, 1) . '.') : '';
    $helloName = trim($first.' '.($mi ? $mi.' ' : '').$last);
  } else {
    $helloName = trim((string)($_SESSION['username'] ?? 'Teacher'));
  }
}
?>
<script>
window.__TD_BOOT__ = <?= json_encode([
  'sections'      => $sections ?? [],
  'section_id'    => $section_id ?? null,
  'curricula'     => $curricula ?? [],
  'curriculum_id' => $curriculum_id ?? null,
  'date'          => $date ?? date('Y-m-d'),
  'school_year'   => $active_sy ?? ''
]) ?>;
</script>


<div class="container-fluid student-dash py-3">
  <style>
    .student-dash{
      --bg:#f1f6fd;
      --card:#ffffff;
      --ink:#0f172a;
      --muted:#64748b;
      --brand:#2563eb;
      --ok:#22c55e;
      --warn:#ef4444;
      --line:#eef2f7;
      --shadow:0 12px 30px rgba(15,23,42,.12);
      background:var(--bg);
      min-height:100vh;
    }

    

    /* Cards, hero, panels */
    .sd-card{background:var(--card); border:1px solid var(--line); border-radius:16px; box-shadow:var(--shadow)}
    .sd-muted{color:var(--muted)}
    .sd-hero{border-radius:18px; background:linear-gradient(135deg,#e7f0ff 0%, #f7fbff 45%, #ffffff 100%); padding:24px}
    .sd-hero h1{font-weight:800;font-size:1.5rem;margin:0;color:var(--ink)}
    .sd-hero p{margin:.5rem 0 0; color:var(--muted)}
    .sd-grid{display:grid;grid-template-columns:2fr 1fr;gap:18px}
    @media(max-width:991px){.sd-grid{grid-template-columns:1fr}}
    .sd-panel-h{display:flex;align-items:center;justify-content:space-between;padding:14px 16px;border-bottom:1px solid var(--line)}
    .sd-panel-h .title{font-weight:700;color:var(--ink)}
    .sd-panel-b{padding:16px}

    /* Top row: Filters (left) + Right stack (right) */
    .sd-topbar{display:grid; grid-template-columns:2fr 1fr; gap:18px; margin-bottom:18px;}
    @media(max-width:991px){ .sd-topbar{ grid-template-columns:1fr; } }

    /* Right column stack to hold Quick Links + Announcements vertically */
    .sd-rightstack{ display:flex; flex-direction:column; gap:18px; }

    /* Filters – 3 even columns, responsive */
    .sd-toolbar{
      display:grid;
      grid-template-columns:repeat(3, minmax(220px, 1fr));
      gap:16px 18px;
      align-items:end;
    }
    @media (max-width:1200px){
      .sd-toolbar{ grid-template-columns:repeat(2, minmax(220px, 1fr)); }
    }
    @media (max-width:640px){
      .sd-toolbar{ grid-template-columns:1fr; }
    }
    .sd-field{ min-width:0; }
    .sd-label{ font-size:13px; color:var(--muted); display:block; margin-bottom:4px; }
    .sd-select, .sd-date{
      border:1px solid #e5e7eb; border-radius:10px; background:#fff;
      padding:10px 12px; font-size:14px; outline:none; color:#0f172a; width:100%;
    }
    .sd-select:focus, .sd-date:focus{ border-color:#3b82f6; box-shadow:0 0 0 3px rgba(59,130,246,.12); }

    /* SY badge */
    .sy-badge{
      display:inline-block; padding:4px 10px; font-size:.78rem; font-weight:700;
      border-radius:999px; background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe;
      margin-left:8px;
    }

    /* Metrics */
    .sd-metrics{ display:grid; grid-template-columns:repeat(12,minmax(0,1fr)); gap:12px; }
    .sd-col-4{ grid-column:span 4; } .sd-col-12{ grid-column:span 12; }
    @media (max-width:1024px){ .sd-col-4,.sd-col-12{ grid-column:span 12; } }
    .sd-metric{ padding:16px; border:1px solid var(--line); border-radius:12px; background:#fff; }
    .sd-metric h4{ margin:0 0 6px 0; color:#64748b; font-size:12px; font-weight:700; letter-spacing:.2px; }
    .sd-metric .val{ font-size:28px; font-weight:800; color:#0f172a; }

    /* Lists */
    .sd-absent-list{ display:flex; flex-direction:column; }
    .sd-item{
      display:flex; align-items:center; justify-content:space-between; gap:12px;
      padding:10px 0; border-bottom:1px solid #f1f5f9;
    }
    .sd-item:last-child{ border-bottom:0; }
    .sd-left{ display:flex; align-items:center; gap:10px; min-width:0; }
    .sd-avatar{ width:32px; height:32px; border-radius:8px; background:#f1f5f9; display:inline-flex; align-items:center; justify-content:center; font-weight:800; color:#0f172a; }
    .sd-name{ font-weight:700; color:#0f172a; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .sd-meta{ font-size:.85rem; color:#64748b; white-space:nowrap; }
    .sd-chip{ display:inline-block; padding:2px 10px; border-radius:999px; font-weight:700; font-size:.78rem; background:#fee2e2; color:#991b1b; border:1px solid #fecaca; }

    /* Rank/score pills */
    .sd-rank{ width:28px; height:28px; border-radius:999px; background:#eff6ff; color:#1d4ed8; font-weight:800; display:inline-flex; align-items:center; justify-content:center; }
    .sd-pill{ display:inline-block; padding:2px 10px; border-radius:999px; font-weight:700; font-size:.78rem; background:#eef2ff; color:#1d4ed8; border:1px solid #c7d2fe; }
    .sd-bad{ display:inline-block; padding:2px 10px; border-radius:999px; font-weight:700; font-size:.78rem; background:#fee2e2; color:#991b1b; border:1px solid #fecaca; }

    /* Quick links */
    .sd-ql-list{ display:flex; flex-direction:column; gap:10px; }
    .sd-ql{
      display:flex; align-items:center; gap:10px; text-decoration:none;
      border:1px solid var(--line); background:#fff; border-radius:12px; padding:10px 12px;
      color:var(--ink); font-weight:500;
    }
    .sd-ql i{ width:26px; height:26px; display:inline-grid; place-items:center; border-radius:7px; background:#f1f5f9; font-style:normal; }
    .sd-empty-row{ color:#94a3b8; text-align:center; padding:8px 0; }

    /* Mini announcements list (same vibe as quicklinks) */
    .sd-anno-list{ display:flex; flex-direction:column; gap:10px; }
    .sd-anno{
      display:flex; align-items:center; gap:10px;
      border:1px solid var(--line); background:#fff; border-radius:12px; padding:10px 12px;
      color:var(--ink); font-weight:500; text-decoration:none;
    }
    .sd-anno i{ width:26px; height:26px; display:inline-grid; place-items:center; border-radius:7px; background:#f1f5f9; font-style:normal; }
    .sd-anno-title{ flex:1; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .sd-anno-date{ font-size:.85rem; color:var(--muted); white-space:nowrap; }
  </style>

  <!-- Hero -->
  <div class="sd-hero mb-3">
    <div class="d-flex align-items-start justify-content-between gap-3">
      <div>
        <div class="d-flex align-items-center gap-2">
          <h1>Hello, <?= htmlspecialchars($helloName) ?>!</h1>
          <?php if (!empty($active_sy)): ?>
            <span id="syBadge" class="sy-badge"><?= htmlspecialchars($active_sy) ?></span>
          <?php endif; ?>
        </div>
        <p class="mb-0">Quick overview of attendance and grades for the selected section &amp; school year.</p>
      </div>
      <!-- <div class="d-none d-md-block">
        <img src="/src/images/logos/final-student-removebg-preview.png" alt="" style="max-width:340px;height:auto;">
      </div> -->
    </div>
  </div>

  <!-- Top row: Filters (left) + Right stack (right) -->
  <div class="sd-topbar">
    <!-- Filters card -->
    <div class="sd-card">
      <div class="sd-panel-h">
        <div class="title">Filters</div>
      </div>
      <div class="sd-panel-b">
        <div class="sd-toolbar">
          <div class="sd-field">
            <span class="sd-label">Section</span>
            <select id="tdSection" class="sd-select">
              <?php if (!empty($sections)) foreach ($sections as $s): ?>
                <option value="<?= (int)$s['id'] ?>" <?= ((int)$s['id'] === (int)($section_id ?? 0) ? 'selected' : '') ?>>
                  <?= 'Grade '.($s['grade_name'] ?? '').' - '.($s['name'] ?? '') ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="sd-field">
            <span class="sd-label">School Year</span>
            <select id="tdCurriculum" class="sd-select">
              <?php if (!empty($curricula)) foreach ($curricula as $c): ?>
                <option value="<?= (int)$c['id'] ?>" <?= ((int)$c['id'] === (int)($curriculum_id ?? 0) ? 'selected' : '') ?>>
                  <?= $c['school_year'] ?? '' ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="sd-field">
            <span class="sd-label">Date</span>
            <input id="tdDate" type="date" class="sd-date" value="<?= htmlspecialchars($date ?? date('Y-m-d')) ?>">
          </div>
        </div>
      </div>
    </div>

    <!-- Right column: Quick Links card + Announcements card stacked -->
    <div class="sd-rightstack">
      <!-- Quick Links card -->
      <div class="sd-card">
        <div class="sd-panel-h">
          <div class="title">Quick Links</div>
        </div>
        <div class="sd-panel-b">
          <div class="sd-ql-list">
            <a class="sd-ql" href="/component/student-attendance/index">
              <i>🗓️</i> Attendance
            </a>
            <a class="sd-ql" href="/component/student-grade-entry/index">
              <i>📝</i> Grades
            </a>
            <a class="sd-ql" href="/component/announcement/index">
              <i>📣</i> Announcements
            </a>
          </div>
        </div>
      </div>

      
    </div><!-- /sd-rightstack -->
  </div><!-- /sd-topbar -->

  <!-- Main content -->
  <div class="sd-grid">
    <!-- Left column -->
    <div>
      <!-- Metrics -->
      <div class="sd-metrics mb-3">
        <div class="sd-col-4"><div class="sd-metric"><h4>Total Students</h4><div id="cardTotal" class="val">—</div></div></div>
        <div class="sd-col-4"><div class="sd-metric"><h4>Present Today</h4><div id="cardPresent" class="val">—</div></div></div>
        <div class="sd-col-4"><div class="sd-metric"><h4>Absent Today</h4><div id="cardAbsent" class="val">—</div></div></div>
      </div>

      <!-- Absent students today -->
      <div class="sd-card mb-4">
        <div class="sd-panel-h">
          <div class="title">Absent Students (Today)</div>
          <a href="/component/student-attendance/index" class="sd-muted">Open Attendance</a>
        </div>
        <div class="sd-panel-b">
          <div id="sd-absent-list" class="sd-absent-list">
            <div class="sd-empty-row">Loading…</div>
          </div>
        </div>
      </div>

      <!-- Students Below 75 (Quarter Average) -->
      <div class="sd-card mb-4" id="sd-under75-card">
        <div class="sd-panel-h">
          <div class="title">Students &lt; 75 (Quarter Average)</div>
          <div class="d-flex align-items-center gap-2">
            <label for="sd-under75-q" class="sd-muted mb-0" style="font-size:.85rem;">Quarter</label>
            <select id="sd-under75-q" class="sd-select" style="min-width:120px;">
              <option value="q1">Q1</option>
              <option value="q2">Q2</option>
              <option value="q3">Q3</option>
              <option value="q4">Q4</option>
            </select>
          </div>
        </div>
        <div class="sd-panel-b">
          <div id="sd-under75-list">
            <div class="sd-empty-row">Loading…</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Right column -->
    <div class="sd-right">
      <!-- Top Students (Quarter Average) -->
      <div class="sd-card mb-4" id="sd-top10-card">
        <div class="sd-panel-h">
          <div class="title">Top Students (Quarter Average)</div>
          <div class="d-flex align-items-center gap-2">
            <label for="sd-top10-q" class="sd-muted mb-0" style="font-size:.85rem;">Quarter</label>
            <select id="sd-top10-q" class="sd-select" style="min-width:120px;">
              <option value="q1">Q1</option>
              <option value="q2">Q2</option>
              <option value="q3">Q3</option>
              <option value="q4">Q4</option>
            </select>
          </div>
        </div>
        <div class="sd-panel-b">
          <div id="sd-top10-list">
            <div class="sd-item">
              <div class="sd-left">
                <div class="sd-rank">—</div>
                <div>
                  <div class="sd-name">Loading…</div>
                  <div class="sd-meta">&nbsp;</div>
                </div>
              </div>
              <span class="sd-pill">—</span>
            </div>
          </div>
        </div>
      </div>
    </div><!-- /Right column -->
  </div><!-- /sd-grid -->
</div><!-- /student-dash -->
