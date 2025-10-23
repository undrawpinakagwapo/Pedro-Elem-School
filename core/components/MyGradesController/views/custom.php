<?php
// $student_id, $student, $enrollments, $curriculum_id, $subjects, $rev
?>
<script>
window.__MYGRADES_BOOT__ = <?= json_encode([
  'student_id'    => $student_id ?? null,
  'student'       => $student ?? null,
  'enrollments'   => $enrollments ?? [],
  'curriculum_id' => $curriculum_id ?? null,
  'subjects'      => $subjects ?? [],
  'rev'           => $rev ?? null,
]) ?>;
</script>

<style>
/* ===== Full-screen, responsive card ===== */
:root{ --page-pad: clamp(12px, 2vw, 20px); }
html, body { height: 100%; }

.myg-page{
  width: 100%;
  min-height: 100dvh;
  margin: 0;
  padding: 0;
  background: #f8fafc;
}

.myg-card{
  width: 100%;
  min-height: 100dvh;
  display: grid;
  grid-template-rows:
    auto   /* head */
    auto   /* toolbar */
    auto   /* meta */
    1fr    /* table (scrolls) */
    auto;  /* footer */
  background:#fff;
  border-radius: 0;
  box-shadow:none;
  border:none;
}

.myg-head{ padding:var(--page-pad) var(--page-pad) calc(var(--page-pad) - 6px); border-bottom:1px solid #eef2f7; }
.myg-title{ display:flex; align-items:center; gap:10px; font-weight:800; color:#0f172a; }
.myg-subtle{ color:#64748b; font-size:13px; margin-top:4px; }

.myg-toolbar{ padding:var(--page-pad); display:flex; gap:14px; align-items:center; flex-wrap:wrap; }
.myg-label{ font-size:13px; color:#64748b; }
.myg-select{ border:none; border-bottom:1px solid #e5e7eb; background:transparent; padding:6px 22px 6px 6px; outline:none; min-height:34px; font-size:14px; color:#0f172a; }
.myg-select:focus{ border-bottom-color:#3b82f6; }

.myg-meta{
  padding:0 var(--page-pad) var(--page-pad);
  display:grid; grid-template-columns: repeat(2, minmax(220px,1fr)); gap:8px 16px;
}
@media (max-width: 640px){ .myg-meta{ grid-template-columns:1fr; } }
.myg-meta div{ font-size:13px; color:#475569; }
.myg-meta b{ color:#0f172a; }

.myg-tablewrap{ min-height:0; overflow:auto; }
.myg-table{ width:100%; border-collapse:separate; border-spacing:0; min-width:720px; }
.myg-table thead th{ font-size:12.5px; font-weight:700; color:#64748b; background:#f9fafb; border-top:1px solid #eef2f7; border-bottom:1px solid #eef2f7; padding:12px 14px; text-align:left; }
.myg-table tbody td{ border-bottom:1px solid #f1f5f9; padding:12px 14px; font-size:14px; color:#0f172a; }
.myg-table tbody tr:hover{ background:#fafcff; }
.text-center{ text-align:center; }

.myg-foot{ display:flex; justify-content:flex-end; gap:12px; padding:var(--page-pad); border-top:1px solid #eef2f7; background:#fff; }
.myg-btn{ border:0; padding:10px 14px; border-radius:10px; font-weight:600; cursor:pointer; font-size:14px; }

.myg-pill{ display:inline-block; min-width:60px; padding:6px 12px; border-radius:16px; font-weight:700; font-size:12.5px; text-align:center; color:#fff; background:#9ca3af; }
.myg-pill.ok{ background:#16a34a; } .myg-pill.warn{ background:#f59e0b; } .myg-pill.mute{ background:#9ca3af; }

/* iOS safe-area */
@supports (padding: max(0px)){
  .myg-head, .myg-toolbar, .myg-meta, .myg-foot{
    padding-left: max(var(--page-pad), env(safe-area-inset-left));
    padding-right: max(var(--page-pad), env(safe-area-inset-right));
  }
}
</style>

<div class="myg-page">
  <div class="myg-card">
    <div class="myg-head">
      <div class="myg-title">My Grades</div>
      <div class="myg-subtle" id="mygStudentLine"></div>
    </div>

    <div class="myg-toolbar">
      <label class="myg-label">School Year</label>
      <select id="mygCurriculum" class="myg-select" style="min-width:180px;"></select>
      <div class="myg-subtle" id="mygDenied" style="display:none; color:#ef4444;">You are not allowed to view these grades.</div>
    </div>

    <div class="myg-meta" id="mygMeta"></div>

    <div class="myg-tablewrap">
      <table class="myg-table">
        <thead>
          <tr>
            <th>Subject</th>
            <th class="text-center">1st Quarter</th>
            <th class="text-center">2nd Quarter</th>
            <th class="text-center">3rd Quarter</th>
            <th class="text-center">4th Quarter</th>
            <th class="text-center">Final Average</th>
          </tr>
        </thead>
        <tbody id="mygTbody">
          <tr><td colspan="6" class="text-center" style="color:#94a3b8;padding:22px;">Loading…</td></tr>
        </tbody>
      </table>
    </div>

    <!-- <div class="myg-foot">
      <button id="mygPrint" class="myg-btn">Print</button>
    </div> -->
  </div>
</div>
