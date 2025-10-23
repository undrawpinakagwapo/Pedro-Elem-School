<?php
$kpi = $kpi ?? [];
function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
?>
<meta name="viewport" content="width=device-width, initial-scale=1" />

<style>
:root{
  --pad: clamp(12px, 2vw, 24px);
  --gap: clamp(12px, 1.8vw, 20px);
  --radius: 12px;
}

/* Fill viewport and uncap layout wrappers so the card can expand */
html, body { height: 100%; margin: 0; }
body { background:#f1f5f9; }

.kpi-page-shell{
  min-height: 100dvh;
  padding: var(--pad);
  display: flex;
  align-items: stretch;
  width: 100%;
}
.kpi-page-shell .container,
.kpi-page-shell .container-fluid,
.kpi-page-shell .content-wrapper,
.kpi-page-shell .main-content{
  max-width: 100% !important;
  width: 100% !important;
  padding-left: 0;
  padding-right: 0;
}

/* ===== Admin KPI Row (scoped to .adm-kpis) ===== */
.adm-kpis{ padding:0; flex: 1 1 auto; display:flex; }
@media (min-width: 1200px){ .adm-kpis{ padding:0; } }

.adm-card{
  background:#fff; border-radius:var(--radius); border:1px solid #eef2f7;
  box-shadow:0 6px 18px rgba(16,24,40,.06); overflow:hidden;
  flex: 1 1 auto; display:flex; flex-direction:column; min-height:0;
}

.adm-header{ padding:16px var(--pad) 8px var(--pad); flex:0 0 auto; background:#fff; }
@media (min-width: 768px){ .adm-header{ padding:18px var(--pad) 8px var(--pad); } }
.adm-titlebar{ display:flex; align-items:center; gap:10px; }
.adm-accent{ width:4px; height:22px; background:#3b82f6; border-radius:2px; }
.adm-title{ font-weight:700; color:#0f172a; font-size:17px; }
@media (min-width: 768px){ .adm-title{ font-size:18px; } }

/* === Responsive grid === */
.kpi-row{
  display:grid;
  gap: var(--gap);
  padding: var(--pad);
  /* Mobile-first: responsive auto-fit; on xl we hard-cap to 5 cols */
  grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
  align-content: start;
  flex: 1 1 auto; min-height: 0;
}
@media (min-width: 1200px){
  .kpi-row{ grid-template-columns: repeat(5, minmax(0, 1fr)); }
}

.kpi-link{ text-decoration:none; color:inherit; min-width:0; }

.kpi-tile{
  background:#f9fafb; border:1px solid #eef2f7; border-radius:16px;
  padding:20px 22px; min-height:124px; height:100%;
  transition:transform .08s ease, box-shadow .12s ease, border-color .12s ease;
  cursor:pointer; display:flex; align-items:center; gap:16px;
  outline:none;
}
.kpi-tile:hover{ transform:translateY(-1px); border-color:#3b82f6; box-shadow:0 2px 0 3px rgba(59,130,246,.08); }
.kpi-tile:focus-visible{ box-shadow:0 0 0 3px rgba(59,130,246,.25); border-color:#3b82f6; }

/* Icon bubble — larger */
.kpi-ico{
  width:56px; height:56px; border-radius:50%;
  display:flex; align-items:center; justify-content:center;
  background:var(--ico-bg, #e5e7eb);
  flex:0 0 auto;
}
.kpi-ico svg{ width:28px; height:28px; fill:none; stroke:#fff; stroke-width:2; }

/* Texts — larger number */
.kpi-meta{ display:flex; flex-direction:column; gap:6px; min-width:0; }
.kpi-label{ margin:0; font-size:13px; color:#64748b; font-weight:700; letter-spacing:.02em; }
.kpi-val{ margin:0; font-size:clamp(28px, 4.2vw, 36px); font-weight:800; color:#0f172a; line-height:1; }
.kpi-foot{ margin:0; font-size:12.5px; color:#94a3b8; }

/* Brand colors per tile */
.kpi-students .kpi-ico{ --ico-bg:#3b82f6; } /* blue */
.kpi-teachers .kpi-ico{ --ico-bg:#10b981; } /* green */
.kpi-sections .kpi-ico{ --ico-bg:#f59e0b; } /* amber */
.kpi-subjects .kpi-ico{ --ico-bg:#8b5cf6; } /* violet */
.kpi-curricula .kpi-ico{ --ico-bg:#ef4444; } /* red */

/* Small device polish */
@media (max-width: 420px){
  .kpi-tile{ padding:16px; border-radius:14px; }
  .kpi-ico{ width:50px; height:50px; }
}
</style>

<div class="kpi-page-shell">
  <div class="adm-kpis">
    <div class="adm-card">
      <div class="adm-header">
        <div class="adm-titlebar">
          <span class="adm-accent"></span>
          <div class="adm-title">Overview</div>
        </div>
      </div>

      <div class="kpi-row">
        <!-- Students -->
        <a class="kpi-link" href="/component/student-management/index" aria-label="Go to Students">
          <div class="kpi-tile kpi-students" tabindex="0">
            <div class="kpi-ico">
              <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M16 11c1.657 0 3-1.79 3-4s-1.343-4-3-4-3 1.79-3 4 1.343 4 3 4Z"/>
                <path d="M8 11c1.657 0 3-1.79 3-4S9.657 3 8 3 5 4.79 5 7s1.343 4 3 4Z"/>
                <path d="M2 21c0-3.314 2.686-6 6-6m8 0c3.314 0 6 2.686 6 6"/>
              </svg>
            </div>
            <div class="kpi-meta">
              <p class="kpi-label">Students</p>
              <p class="kpi-val"><?= (int)($kpi['students'] ?? 0) ?></p>
              <p class="kpi-foot">Manage student records</p>
            </div>
          </div>
        </a>

        <!-- Teachers -->
        <a class="kpi-link" href="/component/faculty-management/index" aria-label="Go to Teachers">
          <div class="kpi-tile kpi-teachers" tabindex="0">
            <div class="kpi-ico">
              <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Z"/>
                <path d="M3 21a7 7 0 0 1 14 0"/>
                <path d="M16 7h5M16 11h5"/>
              </svg>
            </div>
            <div class="kpi-meta">
              <p class="kpi-label">Teachers</p>
              <p class="kpi-val"><?= (int)($kpi['teachers'] ?? 0) ?></p>
              <p class="kpi-foot">View & assign faculty</p>
            </div>
          </div>
        </a>

        <!-- Sections -->
        <a class="kpi-link" href="/component/manage-section/index" aria-label="Go to Sections">
          <div class="kpi-tile kpi-sections" tabindex="0">
            <div class="kpi-ico">
              <svg viewBox="0 0 24 24" aria-hidden="true">
                <rect x="3" y="3" width="8" height="8" rx="2" />
                <rect x="13" y="3" width="8" height="8" rx="2" />
                <rect x="3" y="13" width="8" height="8" rx="2" />
                <rect x="13" y="13" width="8" height="8" rx="2" />
              </svg>
            </div>
            <div class="kpi-meta">
              <p class="kpi-label">Sections</p>
              <p class="kpi-val"><?= (int)($kpi['sections'] ?? 0) ?></p>
              <p class="kpi-foot">Create & set advisers</p>
            </div>
          </div>
        </a>

        <!-- Subjects -->
        <a class="kpi-link" href="/component/manage-subjects/index" aria-label="Go to Subjects">
          <div class="kpi-tile kpi-subjects" tabindex="0">
            <div class="kpi-ico">
              <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M4 19a3 3 0 0 0 3 3h12V5a3 3 0 0 0-3-3H7a3 3 0 0 0-3 3v14Z"/>
                <path d="M8 5h8"/>
              </svg>
            </div>
            <div class="kpi-meta">
              <p class="kpi-label">Subjects</p>
              <p class="kpi-val"><?= (int)($kpi['subjects'] ?? 0) ?></p>
              <p class="kpi-foot">Subjects & codes</p>
            </div>
          </div>
        </a>

        <!-- Curricula -->
        <a class="kpi-link" href="/component/curriculum-management/index" aria-label="Go to Curricula">
          <div class="kpi-tile kpi-curricula" tabindex="0">
            <div class="kpi-ico">
              <svg viewBox="0 0 24 24" aria-hidden="true">
                <rect x="3" y="4" width="18" height="17" rx="2"/>
                <path d="M8 2v4M16 2v4M3 10h18"/>
              </svg>
            </div>
            <div class="kpi-meta">
              <p class="kpi-label">Curriculum</p>
              <p class="kpi-val"><?= (int)($kpi['curricula'] ?? 0) ?></p>
              <p class="kpi-foot">School year setups</p>
            </div>
          </div>
        </a>
      </div>
    </div>
  </div>
</div>
