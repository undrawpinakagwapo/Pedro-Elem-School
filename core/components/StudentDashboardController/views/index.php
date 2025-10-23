

<?php
ensureSessionStarted();
$student = $student ?? null;

$first   = htmlspecialchars($student['account_first_name'] ?? '');
$middle  = htmlspecialchars($student['account_middle_name'] ?? '');
$last    = htmlspecialchars($student['account_last_name'] ?? '');
$lrn     = htmlspecialchars($student['LRN'] ?? '');
$email   = htmlspecialchars($student['email'] ?? '');
$usern   = htmlspecialchars($student['username'] ?? '');
$gender  = htmlspecialchars($student['gender'] ?? '');
$dob     = htmlspecialchars($student['dateof_birth'] ?? '');
$full    = trim($last . ', ' . $first . ($middle ? ' ' . $middle : ''));
?>

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
      --shadow:0 12px 30px rgba(15,23,42,.12);
      background:var(--bg);
      min-height:100vh;
    }


    
    .sd-card{background:var(--card); border:1px solid #eef2f7; border-radius:16px; box-shadow:var(--shadow)}
    .sd-btn{background:var(--brand); color:#fff; border:0; padding:.55rem 1rem; border-radius:10px; font-weight:600}
    .sd-muted{color:var(--muted)}
    .sd-hero{border-radius:18px; background:linear-gradient(135deg,#e7f0ff 0%, #f7fbff 45%, #ffffff 100%); padding:24px}
    .sd-hero h1{font-weight:800;font-size:1.2rem;margin:0;color:var(--ink)}
    .sd-hero p{margin:.25rem 0 0; color:var(--muted)}
    .sd-grid{display:grid;grid-template-columns:2fr 1fr;gap:18px}
    @media(max-width:991px){.sd-grid{grid-template-columns:1fr}}
    .sd-panel-h{display:flex;align-items:center;justify-content:space-between;padding:14px 16px;border-bottom:1px solid #eef2f7}
    .sd-panel-h .title{font-weight:700;color:#0f172a}
    .sd-panel-b{padding:16px}
    .sd-course{display:grid;grid-template-columns:44px 1fr auto;align-items:center;gap:12px;
      padding:10px 12px;border:1px solid #eef2f7;border-radius:12px;margin-bottom:10px}
    .sd-badge{width:44px;height:44px;border-radius:12px;background:#eef6ff;display:grid;place-items:center;font-weight:700;color:#1e3a8a}
    .sd-progress{font-weight:600}
    .sd-result{display:flex;align-items:center;justify-content:space-between;margin-bottom:10px}
    .sd-bar{flex:1;height:6px;border-radius:6px;background:#e5e7eb;overflow:hidden;margin:0 10px}
    .sd-bar span{display:block;height:100%}
    .sd-link{display:flex;align-items:center;gap:8px;padding:10px 12px;border:1px solid #eef2f7;border-radius:12px;background:#fff;margin-top:8px;font-weight:500;color:var(--ink);text-decoration:none}

    /* Attendance chips */
    .sd-result { display:flex; align-items:center; justify-content:space-between; gap:12px; }
    .sd-date   { min-width: 48%; }
    .sd-chip   { display:inline-block; padding:2px 10px; border-radius:999px; font-weight:700; font-size:.8rem; }
    .sd-chip.ok  { background:#e8f7ee; color:#166534; }  /* Present */
    .sd-chip.no  { background:#fee2e2; color:#991b1b; }  /* Absent */

    /* 2-column grid for the subject list */
    .sd-courses-grid{
      display:grid;
      grid-template-columns:repeat(2, minmax(0,1fr));
      gap:12px;
    }
    @media (max-width:576px){
      .sd-courses-grid{ grid-template-columns:1fr; }
    }

    /* subject item look */
    .sd-course{
      display:flex; align-items:center; gap:10px;
      border:1px solid #e5e7eb; border-radius:10px;
      padding:10px 12px; background:#fff;
    }
    .sd-badge{
      width:32px; height:32px; border-radius:8px;
      background:#f1f5f9; display:inline-flex; align-items:center; justify-content:center;
      font-weight:800; color:#0f172a;
    }
    .sd-subject-name{
      font-weight:600; color:#0f172a;
      white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
    }

    /* empty/fallback state spans both columns */
    .sd-courses-grid .sd-empty{ grid-column:1 / -1; color:#94a3b8; text-align:center; }



  </style>


  <!-- Hero -->
   <!-- Hero -->
  <div class="sd-hero mb-4">
    <div class="d-flex align-items-center justify-content-between">
    <!-- Left: Text -->
    <div>
      <h1 style="font-size:1.5rem; font-weight:800;">
        <?= strtoupper($first . ' ' . $last) ?: 'Student'; ?>,
      </h1>
      
      <!-- Student short description -->
      <p style="color:#64748b; font-size:1rem; margin-top:.50rem;">
        <?= $gender ? ucfirst($gender) : 'Learner'; ?> • 
        <?= $dob ? 'Born on ' . date('F j, Y', strtotime($dob)) : 'No birthdate info'; ?> • 
        LRN: <?= $lrn ?: 'N/A'; ?>
      </p>
    </div>

      <!-- Right: Image -->
      <div class="d-none d-md-block">
        <img src="/src/images/logos/final-student-removebg-preview.png" alt="Dashboard illustration" style="max-width:380px; height:auto;">
      </div>
    </div>
  </div>

  <!-- Main Grid -->
<div class="sd-grid">
  <!-- Left Column -->
  <div>
    <!-- Courses -->
    <div class="sd-card mb-4" id="sd-courses-card"
         data-curriculum-id="<?= htmlspecialchars($curriculum_id ?? '') ?>"
         data-rev="<?= htmlspecialchars($rev ?? '') ?>">
      <div class="sd-panel-h">
        <div class="title">Subjects</div>
        <div class="sd-muted" id="sd-class-label">
          <!-- Section/Grade/SY will be filled by JS -->
        </div>
      </div>

      <div class="sd-panel-b">
        <!-- Loading placeholder (JS will replace this) -->
        <div id="sd-course-list">
          <div class="sd-course">
            <div class="sd-badge">…</div>
            <div class="sd-muted">Loading subjects…</div>
            <div class="sd-progress text-end">—</div>
          </div>
        </div>

        <!-- Optional footer actions -->
        <!-- <div class="mt-2 d-flex gap-2">
          <a href="/component/my-grades/index" class="sd-btn">View Grades</a>
          <a href="/component/my-grades/index" class="sd-btn" style="background:#fff;color:#2563eb;border:1px solid #2563eb">Enroll Course</a>
        </div> -->
      </div>
    </div>
  </div>

    <!-- Right Column -->
    <!-- Right Column -->
<div class="sd-right">
  <!-- Recent Attendance -->
  <div class="sd-card mb-4" id="sd-att-card" data-limit="7">
    <div class="sd-panel-h">
      <div class="title">Recent Attendance</div>
      <a href="/component/my-attendance/index" class="sd-muted">View More</a>
    </div>
    <div class="sd-panel-b" id="sd-att-list">
      <div class="sd-result">
        <div class="sd-muted">Loading recent attendance…</div>
      </div>
    </div>
  </div>

      <!-- Quick Links -->
      <a href="/component/announcement/index" class="sd-link">
        <span>📣</span> View Announcement
      </a>
      <a href="/component/complaints/index" class="sd-link">
        <span>⚠️</span> Complaint — Want to complain against someone?
      </a>
    </div>
  </div>
</div>
