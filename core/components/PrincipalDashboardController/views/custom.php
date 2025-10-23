<?php
ensureSessionStarted();

$principalName = htmlspecialchars($principalName ?? 'Principal');

$counts = $counts ?? [
  'totalStudents'   => 0,
  'totalTeachers'   => 0,
  'totalActive'     => 0,
  'totalInactive'   => 0
];

$recentUsers  = $recentUsers  ?? [];
$gradeLevels  = $gradeLevels  ?? []; // for Top Students filter
$sections     = $sections     ?? []; // not prefilled; we load by grade via AJAX

// Optional: pass this from controller; otherwise only "All School Years" shows.
$schoolYears  = isset($schoolYears) && is_array($schoolYears) ? $schoolYears : [];
?>
<div class="container-fluid student-dash py-3">
  <style>
    /* Global tokens */
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
    .sd-muted{color:var(--muted)}
    .sd-hero{border-radius:18px; background:linear-gradient(135deg,#e7f0ff 0%, #f7fbff 45%, #ffffff 100%); padding:24px}
    .sd-hero h1{font-weight:800;font-size:1.5rem;margin:0;color:#0f172a}
    .sd-hero p{margin:.25rem 0 0; color:var(--muted)}
    .sd-grid{display:grid;grid-template-columns:2fr 1fr;gap:18px}
    @media(max-width:991px){.sd-grid{grid-template-columns:1fr}}
    .sd-panel-h{display:flex;align-items:center;justify-content:space-between;padding:14px 16px;border-bottom:1px solid #eef2f7}
    .sd-panel-h .title{font-weight:700;color:#0f172a;font-size:1rem;}
    .sd-panel-b{padding:16px}

    /* Stats (Overview) */
    .sd-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px}
    @media(max-width:992px){.sd-stats{grid-template-columns:repeat(2,minmax(0,1fr))}}
    @media(max-width:480px){.sd-stats{grid-template-columns:1fr}}
    .sd-stat{
      display:flex; flex-direction:column; align-items:center; justify-content:center;
      border:1px solid #eef2f7; border-radius:12px; padding:16px; background:#fff;
    }
    .sd-stat .icon{
      width:52px;height:52px;font-size:1.5rem;margin-bottom:8px;
      border-radius:12px; background:#eef6ff;
      display:flex; align-items:center; justify-content:center;
      color:#1e3a8a; font-weight:800;
    }
    .sd-stat .label{
      font-size:.9rem;
      color:#000;
      margin-bottom:4px;
      font-weight:600;
      text-align:center;
    }
    .sd-stat .kpi{
      font-size:1.4rem;
      font-weight:800;
      color:#000;
      line-height:1.2;
    }

    /* Student Absent Card */
    .sa-card { border:1px solid #eef2f7; border-radius:16px; background:#fff; box-shadow:0 10px 24px rgba(15,23,42,.08); margin-top:16px; }
    .sa-head { display:flex; align-items:center; justify-content:space-between; padding:14px 16px; border-bottom:1px solid #eef2f7; background:linear-gradient(135deg,#f7fbff 0%, #ffffff 70%); }
    .sa-title { margin:0; font-weight:700; font-size:1rem; color:#0f172a; }
    .sa-date { display:flex; align-items:center; gap:8px; }
    .sa-date .form-control { height:34px; }
    .sa-date .btn { height:34px; padding:0 12px; }
    .sa-body { padding:12px 14px; }
    .sa-wrap { border:1px solid #eef2f7; border-radius:12px; overflow:hidden; }
    .sa-table { width:100%; border-collapse:separate; border-spacing:0; }
    .sa-table thead th {
      background:#f8fafc; color:#0f172a; font-weight:700; font-size:.9rem;
      padding:12px 14px; border-bottom:1px solid #eef2f7; text-wrap:nowrap;
    }
    .sa-table tbody td { padding:12px 14px; border-bottom:1px solid #f1f5f9; color:#0f172a; font-size:.95rem; }
    .sa-table tbody tr:nth-child(odd) { background:#fcfdff; }
    .sa-name { font-weight:700; }
    .sa-remark-muted { color:#94a3b8; }

    /* Top Students Card */
    .ts-card { border:1px solid #eef2f7; border-radius:16px; background:#fff; box-shadow:0 10px 24px rgba(15,23,42,.08); margin-top:16px; }
    .ts-head { display:flex; align-items:center; justify-content:space-between; padding:14px 16px; border-bottom:1px solid #eef2f7; background:linear-gradient(135deg,#f7fbff 0%, #ffffff 70%); }
    .ts-title { margin:0; font-weight:700; font-size:1rem; color:#0f172a; }
    .ts-filters { display:flex; align-items:center; gap:8px; flex-wrap:wrap; }
    .ts-filters .form-select { height:34px; padding:0 8px; font-size:.875rem; }
    .ts-filters .btn { height:34px; padding:0 12px; }
    .ts-body { padding:12px 14px; }
    .ts-table { width:100%; border-collapse:separate; border-spacing:0; }
    .ts-table thead th{
      background:#f8fafc; color:#0f172a; font-weight:700; font-size:.9rem;
      padding:12px 14px; border-bottom:1px solid #eef2f7; text-wrap:nowrap;
    }
    .ts-table tbody td{ padding:12px 14px; border-bottom:1px solid #f1f5f9; color:#0f172a; font-size:.95rem; }
    .ts-table tbody tr:nth-child(odd){ background:#fcfdff; }

    /* Quick links */
    .sd-link{display:flex;align-items:center;gap:8px;padding:10px 12px;border:1px solid #eef2f7;border-radius:12px;background:#fff;margin-top:8px;font-weight:500;color:#0f172a;text-decoration:none}
  </style>

  <!-- Hero -->
  <div class="sd-hero mb-4">
    <div class="d-flex align-items-center justify-content-between">
      <div>
        <h1>Welcome, <?= $principalName ?> 👋</h1>
        <p class="sd-muted">Here’s a quick snapshot of your school activity.</p>
      </div>
    </div>
  </div>

  <!-- Main Grid -->
  <div class="sd-grid">
    <!-- Left column -->
    <div>
      <!-- Overview -->
      <div class="sd-card mb-4">
        <div class="sd-panel-h"><div class="title">Overview</div></div>
        <div class="sd-panel-b">
          <div class="sd-stats">
            <div class="sd-stat"><div class="icon">🎓</div><div class="label">Student Counts</div><div class="kpi"><?= (int)$counts['totalStudents'] ?></div></div>
            <div class="sd-stat"><div class="icon">👩‍🏫</div><div class="label">Teachers</div><div class="kpi"><?= (int)$counts['totalTeachers'] ?></div></div>
            <div class="sd-stat"><div class="icon">✅</div><div class="label">Active Users</div><div class="kpi"><?= (int)$counts['totalActive'] ?></div></div>
            <div class="sd-stat"><div class="icon">🚫</div><div class="label">Inactive Users</div><div class="kpi"><?= (int)$counts['totalInactive'] ?></div></div>
          </div>
        </div>
      </div>

      <?php
        $absentToday = isset($absentToday) && is_array($absentToday) ? $absentToday : array();
        $reportDate  = isset($reportDate) ? $reportDate : date('Y-m-d');
      ?>

      <!-- Student Absent Card -->
      <div class="sa-card">
        <div class="sa-head">
          <h5 class="sa-title">Students Absent — <span id="saDateTitle"><?= htmlspecialchars(date('F j, Y', strtotime($reportDate)), ENT_QUOTES, 'UTF-8'); ?></span></h5>
          <form id="saForm" class="sa-date">
            <input id="saDateInput" type="date" name="date" value="<?= htmlspecialchars($reportDate, ENT_QUOTES, 'UTF-8'); ?>" class="form-control form-control-sm">
            <button id="saGoBtn" type="button" class="btn btn-sm btn-primary">Go</button>
          </form>
        </div>
        <div id="saBody" class="sa-body">
          <?php if (empty($absentToday)): ?>
            <div class="text-muted" style="padding:16px;">No whole-day absentees.</div>
          <?php else: ?>
            <div class="sa-wrap">
              <div class="table-responsive">
                <table class="sa-table table table-sm align-middle mb-0">
                  <thead><tr><th>Student</th><th>LRN</th><th>Gender</th><th>Grade</th><th>Section</th><th>Remarks</th></tr></thead>
                  <tbody>
                    <?php foreach ($absentToday as $r): ?>
                      <tr>
                        <td><div class="sa-name"><?= htmlspecialchars($r['full_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?></div></td>
                        <td><?= htmlspecialchars($r['LRN'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?= htmlspecialchars($r['gender'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?= htmlspecialchars($r['grade_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?= htmlspecialchars($r['section_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td>
                          <?php
                            $rem = trim((string)($r['remarks'] ?? ''));
                            echo $rem === '' ? '<span class="sa-remark-muted">—</span>' : htmlspecialchars($rem, ENT_QUOTES, 'UTF-8');
                          ?>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Top Students (School-wide) -->
      <div class="ts-card">
        <div class="ts-head">
          <h5 class="ts-title">Top Students (School-wide)</h5>
          <div class="ts-filters">
            <!-- School Year -->
            <select id="tsSY" class="form-select form-select-sm">
              <option value="">All School Years</option>
              <?php foreach ($schoolYears as $sy): ?>
                <option value="<?= htmlspecialchars($sy) ?>"><?= htmlspecialchars($sy) ?></option>
              <?php endforeach; ?>
            </select>

            <!-- Quarter -->
            <select id="tsQuarter" class="form-select form-select-sm">
              <option value="">All Quarters</option>
              <option value="1">1st Quarter</option>
              <option value="2">2nd Quarter</option>
              <option value="3">3rd Quarter</option>
              <option value="4">4th Quarter</option>
            </select>

            <!-- Grade -->
            <select id="tsGrade" class="form-select form-select-sm">
              <option value="">All Grades</option>
              <?php foreach ($gradeLevels as $g): ?>
                <option value="<?= (int)$g['id'] ?>"><?= htmlspecialchars($g['name'] ?? '') ?></option>
              <?php endforeach; ?>
            </select>

            <!-- Section (populated by grade) -->
            <select id="tsSection" class="form-select form-select-sm">
              <option value="">All Sections</option>
            </select>

            <!-- Subject (populated by grade [+ school year]) -->
            <select id="tsSubject" class="form-select form-select-sm">
              <option value="">All Subjects</option>
            </select>

            <button id="tsFilterBtn" class="btn btn-sm btn-primary" type="button">Filter</button>
          </div>
        </div>
        <div class="ts-body">
          <div class="table-responsive">
            <table class="ts-table table table-sm align-middle mb-0">
              <thead>
                <tr>
                  <th>Rank</th>
                  <th>Student</th>
                  <th>LRN</th>
                  <th>Grade</th>
                  <th>Section</th>
                  <th>Subject</th>
                  <th>Average</th>
                </tr>
              </thead>
              <tbody id="tsTableBody">
                <tr><td colspan="7" class="text-center text-muted py-3">Use filters and click <b>Filter</b> to load results.</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </div>

    <!-- Right column -->
    <div class="sd-right">
      <div class="sd-card mb-4">
        <div class="sd-panel-h"><div class="title">Quick Actions</div></div>
        <div class="sd-panel-b">
          <a href="<?=$_ENV['BASE_PATH']?>/component/student-management/index" class="sd-link"><span>🎓</span> Add Student</a>
          <a href="<?=$_ENV['BASE_PATH']?>/component/faculty-management/index" class="sd-link"><span>👩‍🏫</span> Manage Teachers</a>
          <a href="<?=$_ENV['BASE_PATH']?>/component/sf-reports/index" class="sd-link"><span>📄</span> School Forms</a>
          <a href="<?=$_ENV['BASE_PATH']?>/component/announcement/index" class="sd-link"><span>📢</span> Announcements</a>
        </div>
      </div>
      <div class="sd-card">
        <div class="sd-panel-h"><div class="title">Notes</div></div>
        <div class="sd-panel-b">
          <p class="sd-muted" style="margin:0;">Keep an eye on unverified accounts and pending approvals to maintain accurate records.</p>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
(function(){
  /* ==================== Shared helpers ==================== */
  function escapeHtml(s){
    return String(s||'').replace(/[&<>"']/g, m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#039;'}[m]));
  }
  function setOptions(el, items, firstLabel){
    if (!el) return;
    el.innerHTML = `<option value="">${escapeHtml(firstLabel)}</option>`;
    (items||[]).forEach(it=>{
      const v = (it.id != null ? it.id : it.value);
      const label = (it.code ? `${it.code} — ` : '') + (it.name || it.label || '');
      el.innerHTML += `<option value="${escapeHtml(v)}">${escapeHtml(label)}</option>`;
    });
  }
  function buildQuery(params){
    const q = [];
    Object.keys(params).forEach(k=>{
      const v = (params[k] ?? '').toString().trim();
      if (v !== '') q.push(encodeURIComponent(k)+'='+encodeURIComponent(v));
    });
    return q.length ? ('?'+q.join('&')) : '';
  }

  /* ==================== Absent card (AJAX) ==================== */
  const $saForm   = document.getElementById('saForm');
  const $saBtn    = document.getElementById('saGoBtn');
  const $saInput  = document.getElementById('saDateInput');
  const $saBody   = document.getElementById('saBody');
  const $saTitle  = document.getElementById('saDateTitle');

  function formatPrettyDate(iso){
    // Fallback pretty format (client-side); server still returns raw YYYY-MM-DD
    try {
      const d = new Date(iso+'T00:00:00');
      return d.toLocaleDateString(undefined, { year:'numeric', month:'long', day:'numeric' });
    } catch { return iso; }
  }

  function renderAbsences(rows){
    if (!rows || !rows.length){
      $saBody.innerHTML = '<div class="text-muted" style="padding:16px;">No whole-day absentees.</div>';
      return;
    }
    const tbody = rows.map(r=>`
      <tr>
        <td><div class="sa-name">${escapeHtml(r.full_name||'')}</div></td>
        <td>${escapeHtml(r.LRN||'')}</td>
        <td>${escapeHtml(r.gender||'')}</td>
        <td>${escapeHtml(r.grade_name||'')}</td>
        <td>${escapeHtml(r.section_name||'')}</td>
        <td>${(r.remarks && r.remarks.trim()!=='') ? escapeHtml(r.remarks) : '<span class="sa-remark-muted">—</span>'}</td>
      </tr>
    `).join('');
    $saBody.innerHTML = `
      <div class="sa-wrap">
        <div class="table-responsive">
          <table class="sa-table table table-sm align-middle mb-0">
            <thead><tr><th>Student</th><th>LRN</th><th>Gender</th><th>Grade</th><th>Section</th><th>Remarks</th></tr></thead>
            <tbody>${tbody}</tbody>
          </table>
        </div>
      </div>`;
  }

  function runAbsencesFetch(){
    const date = ($saInput?.value || '').trim();
    $saBody.innerHTML = '<div class="text-muted" style="padding:16px;">Loading…</div>';
    fetch('/component/principal-dashboard/fetchAbsences' + buildQuery({date}))
      .then(async r=>{
        const txt = await r.text();
        try { return { ok:r.ok, data: JSON.parse(txt) }; }
        catch(e){ throw new Error(txt || ('HTTP '+r.status)); }
      })
      .then(({ok,data})=>{
        if (ok && data && data.status){
          renderAbsences(data.rows||[]);
          if ($saTitle && date){ $saTitle.textContent = formatPrettyDate(date); }
        } else {
          const msg = (data && data.message) ? data.message : 'Failed to load.';
          $saBody.innerHTML = '<div class="text-danger" style="padding:16px;">'+escapeHtml(msg)+'</div>';
        }
      })
      .catch(err=>{
        console.error('Absences fetch error:', err);
        $saBody.innerHTML = '<div class="text-danger" style="padding:16px;">Failed to load.</div>';
      });
  }

  // Make the "Go" button behave like Filter (AJAX refresh only)
  $saBtn?.addEventListener('click', runAbsencesFetch);
  // Prevent form submit (no page reload)
  $saForm?.addEventListener('submit', (e)=>{ e.preventDefault(); runAbsencesFetch(); });

  /* ==================== Top Students card (existing AJAX) ==================== */
  const $grade   = document.getElementById('tsGrade');
  const $section = document.getElementById('tsSection');
  const $quarter = document.getElementById('tsQuarter');
  const $sy      = document.getElementById('tsSY');
  const $subject = document.getElementById('tsSubject');
  const $btn     = document.getElementById('tsFilterBtn');
  const $tbody   = document.getElementById('tsTableBody');

  function renderTopStudents(rows){
    if(!rows || !rows.length){
      $tbody.innerHTML='<tr><td colspan="7" class="text-center text-muted py-3">No results found.</td></tr>';
      return;
    }
    $tbody.innerHTML = rows.map((r,i)=>`
      <tr>
        <td>${i+1}</td>
        <td><strong>${escapeHtml(r.full_name)}</strong></td>
        <td>${escapeHtml(r.LRN)}</td>
        <td>${escapeHtml(r.grade_name)}</td>
        <td>${escapeHtml(r.section_name)}</td>
        <td>${escapeHtml(r.subject_name || r.subject || '—')}</td>
        <td><span class="badge bg-success">${(r.average!=null?Number(r.average).toFixed(2):'—')}</span></td>
      </tr>
    `).join('');
  }

  function loadSections(gradeId){
    $section.innerHTML = '<option>Loading…</option>';
    const url = '/component/principal-dashboard/fetchSectionsByGrade?grade_id=' + encodeURIComponent(gradeId||'');
    return fetch(url).then(r=>r.json()).then(d=>{
      setOptions($section, (d && d.rows) || [], 'All Sections');
    }).catch(()=> setOptions($section, [], 'All Sections'));
  }

  function loadSubjects(gradeId, schoolYear){
    $subject.innerHTML = '<option>Loading…</option>';
    const qs = [];
    if (gradeId) qs.push('grade_id='+encodeURIComponent(gradeId));
    if (schoolYear) qs.push('school_year='+encodeURIComponent(schoolYear));
    const url = '/component/principal-dashboard/fetchSubjectsByGrade' + (qs.length?('?'+qs.join('&')):'');
    return fetch(url).then(r=>r.json()).then(d=>{
      setOptions($subject, (d && d.rows) || [], 'All Subjects');
    }).catch(()=> setOptions($subject, [], 'All Subjects'));
  }

  $grade?.addEventListener('change', async ()=>{
    const gid = $grade.value;
    await Promise.all([ loadSections(gid), loadSubjects(gid, $sy?.value || '') ]);
  });

  $sy?.addEventListener('change', ()=>{
    const gid = $grade.value;
    loadSubjects(gid, $sy.value || '');
  });

  function runFetchTopStudents(){
    $tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-3">Loading…</td></tr>';
    const params = {
      grade_id:    $grade.value,
      section_id:  $section.value,
      subject_id:  $subject.value,
      quarter:     $quarter.value,
      school_year: $sy ? $sy.value : ''
    };
    const url = '/component/principal-dashboard/fetchTopStudents' + buildQuery(params);

    fetch(url)
      .then(async (r)=>{
        const text = await r.text();
        try { return { ok: r.ok, data: JSON.parse(text) }; }
        catch { throw new Error(text || ('HTTP '+r.status)); }
      })
      .then(({ok,data})=>{
        if (ok && data && data.status) renderTopStudents(data.rows || []);
        else {
          const msg = (data && data.message) ? data.message : 'Failed to load.';
          $tbody.innerHTML = '<tr><td colspan="7" class="text-center text-danger py-3">'+escapeHtml(msg)+'</td></tr>';
        }
      })
      .catch((e)=>{
        $tbody.innerHTML = '<tr><td colspan="7" class="text-center text-danger py-3">Failed to load.</td></tr>';
        console.error('TopStudents fetch error:', e);
      });
  }

  $btn?.addEventListener('click', runFetchTopStudents);
  document.querySelector('.ts-head')?.addEventListener('keydown', (e)=>{
    if (e.key === 'Enter') { e.preventDefault(); runFetchTopStudents(); }
  });
})();
</script>