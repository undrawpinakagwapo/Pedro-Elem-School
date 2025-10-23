<?php
// Expects: $list

// Build unique filter options from $list
$years = [];
$grades = [];
$sections = [];
if (!empty($list)) {
  foreach ($list as $r) {
    if (!empty($r['school_year'])) $years[$r['school_year']] = true;
    if (!empty($r['grade_name'] ?? $r['gs_name'])) {
      // Prefer explicit grade_name if available; else parse from gs_name "Grade - Section"
      $g = $r['grade_name'] ?? explode(' - ', (string)$r['gs_name'])[0] ?? '';
      if ($g !== '') $grades[$g] = true;
    }
    if (!empty($r['section_name'] ?? $r['gs_name'])) {
      $s = $r['section_name'] ?? (explode(' - ', (string)$r['gs_name'])[1] ?? '');
      if ($s !== '') $sections[$s] = true;
    }
  }
  krsort($years, SORT_NATURAL);
  ksort($grades, SORT_NATURAL);
  ksort($sections, SORT_NATURAL);
}
?>
<style>
  :root{
    --ink:#0f172a; --muted:#64748b; --line:#e9eef5; --ring:#2563eb;
    --bg:#f7f9fc; --card:#ffffff; --chip:#eff6ff; --chip-border:#bfdbfe;
    --ok-bg:#ecfdf5; --ok-bd:#a7f3d0; --ok-ink:#065f46;
    --bad-bg:#fee2e2; --bad-bd:#fecaca; --bad-ink:#991b1b;
    --shadow:0 18px 45px rgba(15,23,42,.10);
  }

  /* Page */
  .cm-page{background:linear-gradient(180deg,#f9fbff 0%, #ffffff 160px, #ffffff 100%); padding: 6px;}
  @media (min-width:992px){ .cm-page{padding: 10px;} }

  /* ---------- Header ---------- */
  .cm-hero{
    background: radial-gradient(1200px 300px at 20% -20%, #e9f2ff 0%, transparent 60%),
                radial-gradient(1200px 300px at 90% -40%, #f3f8ff 0%, transparent 65%),
                #fff;
    border: 1px solid var(--line);
    border-radius: 20px;
    box-shadow: var(--shadow);
    padding: 22px 22px 16px;
    margin-bottom: 14px;
  }
  .cm-titlebar{
    display:flex; flex-wrap:wrap; align-items:flex-end; gap:10px; justify-content:space-between;
  }
  .cm-h1{ margin:0; font-weight:900; color:var(--ink); font-size:1.7rem; letter-spacing:.2px; }
  .cm-subhead{ margin:.35rem 0 0; color:var(--muted); font-size:.98rem; }

  /* ---------- Filter Bar ---------- */
  .cm-filters{
    margin-top:14px; display:grid; gap:10px;
    grid-template-columns: repeat(12, 1fr);
  }
  .cm-filter{
    grid-column: span 12;
    background: #ffffffcc;
    backdrop-filter: blur(6px);
    border:1px solid var(--line); border-radius:14px; box-shadow: var(--shadow);
    padding: 10px;
    display:grid; gap:10px; align-items:center;
    grid-template-columns: 1fr 1fr 1fr auto;
  }
  @media (max-width: 992px){
    .cm-filter{ grid-template-columns: 1fr; }
  }
  .cm-select-wrap{ display:flex; flex-direction:column; gap:6px; }
  .cm-label{ font-size:.78rem; font-weight:800; color:#334155; letter-spacing:.2px; }
  .cm-select{
    appearance:none; background:#fff; border:1px solid #dbe4ee;
    border-radius:12px; padding:10px 12px; font-size:.95rem; color:#0f172a;
    line-height:1.2; outline:none;
  }
  .cm-select:focus{ border-color:#93c5fd; box-shadow:0 0 0 4px rgba(59,130,246,.12); }
  .cm-actions-end{ display:flex; align-items:flex-end; justify-content:flex-end; gap:8px; }
  .cm-btn{
    border:1px solid #c7d2fe; color:#1d4ed8; background:#fff;
    padding:10px 14px; border-radius:12px; font-weight:800; cursor:pointer;
  }
  .cm-btn:hover{ border-color:#93c5fd; box-shadow:0 0 0 4px rgba(59,130,246,.12); }
  .cm-chip{
    font-size:.78rem;font-weight:800;color:#1d4ed8;background:var(--chip);
    border:1px solid var(--chip-border); padding:2px 8px;border-radius:999px;
  }
  .cm-count{ color:#475569; font-size:.9rem; }

  /* ---------- Grid ---------- */
  .cm-wrap{ padding: 12px 6px 2px; }
  .cm-grid{
    display:grid; gap:16px;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  }
  .cm-card{
    background:var(--card); border:1px solid var(--line); border-radius:18px;
    box-shadow:var(--shadow); padding:16px 16px 14px;
    transition: box-shadow .15s, border-color .15s, transform .08s, background .15s;
  }
  .cm-card:hover{ border-color:#dbeafe; box-shadow:0 20px 50px rgba(37,99,235,.12); transform:translateY(-2px); }

  .cm-eyebrow{
    display:inline-flex;align-items:center;gap:6px;
    font-size:.78rem;font-weight:800;letter-spacing:.3px;
    color:#1d4ed8;background:var(--chip);border:1px solid var(--chip-border);
    padding:3px 10px;border-radius:999px;
  }
  .cm-title{
    margin:10px 0 2px; font-weight:900; color:var(--ink);
    white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
  }
  .cm-sub{ color:var(--muted); font-size:.95rem; margin-bottom:6px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
  .cm-meta{ color:#475569; font-size:.92rem; margin-bottom:10px; }
  .cm-line{ height:1px;background:#f1f5f9;margin:10px 0; }

  .cm-badges{ display:flex; gap:6px; flex-wrap:wrap; }
  .cm-badge{ font-size:.75rem;font-weight:800;padding:3px 10px;border-radius:999px;border:1px solid transparent; }
  .cm-badge.on{ color:var(--ok-ink); background:var(--ok-bg); border-color:var(--ok-bd); }
  .cm-badge.off{ color:var(--bad-ink); background:var(--bad-bg); border-color:var(--bad-bd); }

  .cm-actions{ display:flex; justify-content:flex-end; gap:8px; margin-top:8px; }
  .btn-view{
    border:1px solid #c7d2fe; color:#1d4ed8; background:#fff;
    padding:8px 12px; border-radius:10px; font-weight:800; cursor:pointer;
  }
  .btn-view:hover{ border-color:#93c5fd; box-shadow:0 0 0 3px rgba(59,130,246,.12); }

  /* ---------- Empty ---------- */
  .cm-empty{
    border:1px dashed #cbd5e1; background:#f8fafc; color:#475569;
    padding:20px; border-radius:14px; text-align:center;
  }

  /* ---------- Modal ---------- */
  .cv-overlay{
    position:fixed; inset:0; z-index:9999;
    display:none; align-items:flex-start; justify-content:center;
    padding:40px 12px; background:rgba(2,6,23,.55);
  }
  .cv-overlay.show{ display:flex; }
  .cv-card{
    max-width: 720px; width: 100%;
    border-radius: 16px; overflow: hidden;
    border: 1px solid #eef2f7; background:#fff;
    box-shadow: 0 20px 50px rgba(15,23,42,0.25);
    padding: 0; position: relative;
  }
  .cv-x{
    position:absolute; top:10px; right:10px;
    width:34px; height:34px; border-radius:8px;
    display:inline-flex; align-items:center; justify-content:center;
    border:1px solid #e5e7eb; background:#fff; cursor:pointer;
    font-size:18px; line-height:1; color:#0f172a;
  }
  .cv-x:hover{ border-color:#cbd5e1; box-shadow:0 0 0 3px rgba(59,130,246,.10); }
  .cv-body{ padding:16px; }
  .cv-foot{
    border-top:1px solid #eef2f7; padding:12px 16px;
    display:flex; gap:10px; justify-content:flex-end;
  }
  .cv-btn{
    border:0; background:#3b82f6; color:#fff;
    padding:8px 14px; border-radius:10px; cursor:pointer; font-weight:700;
  }
  .cv-btn.alt{
    background:#fff; color:#1f2937; border:1px solid #e5e7eb;
  }
</style>

<div class="col-xl-12 col-md-12 cm-page">
  <!-- Title / Header -->
  <div class="cm-hero">
    <div class="cm-titlebar">
      <div>
        <h1 class="cm-h1">Curriculum</h1>
        <p class="cm-subhead">Browse curriculum by grade &amp; section. Use the filters to narrow results and click <strong>View</strong> for full details.</p>
      </div>
      <div class="cm-chip" id="cm-count-chip">—</div>
    </div>

    <!-- Filter Bar -->
    <div class="cm-filters">
      <div class="cm-filter" role="region" aria-label="Curriculum Filters">
        <div class="cm-select-wrap">
          <label for="f-year" class="cm-label">School Year</label>
          <select id="f-year" class="cm-select" aria-label="School Year">
            <option value="">All Years</option>
            <?php foreach (array_keys($years) as $y): ?>
              <option value="<?= htmlspecialchars($y) ?>"><?= htmlspecialchars($y) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="cm-select-wrap">
          <label for="f-grade" class="cm-label">Grade Level</label>
          <select id="f-grade" class="cm-select" aria-label="Grade Level">
            <option value="">All Grades</option>
            <?php foreach (array_keys($grades) as $g): ?>
              <option value="<?= htmlspecialchars($g) ?>"><?= htmlspecialchars($g) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="cm-select-wrap">
          <label for="f-section" class="cm-label">Section</label>
          <select id="f-section" class="cm-select" aria-label="Section">
            <option value="">All Sections</option>
            <?php foreach (array_keys($sections) as $s): ?>
              <option value="<?= htmlspecialchars($s) ?>"><?= htmlspecialchars($s) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="cm-actions-end">
          <button type="button" id="f-reset" class="cm-btn" aria-label="Reset filters">Reset</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Cards -->
  <div class="cm-wrap">
    <?php if (!empty($list)): ?>
      <div class="cm-grid" id="cm-grid">
        <?php foreach ($list as $r): ?>
          <?php
            $isActive = (int)($r['status'] ?? 0) === 1;
            // Prefer explicit grade/section if present
            $grade = $r['grade_name'] ?? (explode(' - ', (string)($r['gs_name'] ?? ''))[0] ?? '');
            $section = $r['section_name'] ?? (explode(' - ', (string)($r['gs_name'] ?? ''))[1] ?? '');
          ?>
          <div class="cm-card"
               data-year="<?= htmlspecialchars($r['school_year'] ?? '') ?>"
               data-grade="<?= htmlspecialchars($grade) ?>"
               data-section="<?= htmlspecialchars($section) ?>">
            <span class="cm-eyebrow">
              <span><?= htmlspecialchars($r['school_year'] ?? '') ?></span>
            </span>

            <div class="cm-title" title="<?= htmlspecialchars($r['gs_name'] ?? '') ?>">
              <?= htmlspecialchars($r['gs_name'] ?? '') ?>
            </div>
            <div class="cm-sub" title="<?= htmlspecialchars($r['name'] ?? '') ?>">
              <?= htmlspecialchars($r['name'] ?? '') ?>
            </div>

            <div class="cm-meta">
              Adviser: <strong><?= htmlspecialchars($r['adviser_name'] ?? '—') ?></strong>
            </div>

            <div class="cm-line"></div>

            <div class="cm-badges">
              <span class="cm-badge <?= $isActive ? 'on' : 'off' ?>"><?= $isActive ? 'ACTIVE' : 'INACTIVE' ?></span>
              <?php if ($grade !== ''): ?><span class="cm-chip" title="Grade"><?= htmlspecialchars($grade) ?></span><?php endif; ?>
              <?php if ($section !== ''): ?><span class="cm-chip" title="Section"><?= htmlspecialchars($section) ?></span><?php endif; ?>
            </div>

            <div class="cm-actions">
              <button type="button" class="btn-view btn-view-curriculum" data-id="<?= (int)$r['id'] ?>">
                View
              </button>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      <div id="cm-empty" class="cm-empty" style="display:none">No curriculum records match your filters.</div>
    <?php else: ?>
      <div class="cm-empty" role="alert">No curriculum records found.</div>
    <?php endif; ?>
  </div>
</div>

<!-- Custom Modal Overlay (no Bootstrap, no header) -->
<div id="cv-overlay" class="cv-overlay" aria-hidden="true">
  <div class="cv-card" role="dialog" aria-modal="true">
    <button type="button" class="cv-x" id="cv-close-x" aria-label="Close">×</button>
    <div class="cv-body" id="cv-body"><!-- filled by JS --></div>
    <div class="cv-foot">
      <button type="button" class="cv-btn alt" id="cv-cancel">Close</button>
      <button type="button" class="cv-btn" id="cv-primary" style="display:none"></button>
    </div>
  </div>
</div>
