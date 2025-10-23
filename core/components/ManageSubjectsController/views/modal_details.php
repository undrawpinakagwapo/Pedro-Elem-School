<?php 
    if($details) {
        echo '<input type="hidden" name="id" value="'.htmlspecialchars($details["id"]).'">';
    }
?>

<style>
/* ===== Subject Details: soft card design (scoped) ===== */
.subject-card {
  background:#fff; border:1px solid #e8edf6; border-radius:12px; overflow:hidden;
  box-shadow:0 8px 18px rgba(15,23,42,.05);
}
.subject-card .card-header{
  display:flex; align-items:center; justify-content:space-between; gap:.75rem;
  padding:.9rem 1rem; background:#f8fafc; border-bottom:1px solid #eef2f7;
}
.subject-card .card-title{
  margin:0; font-weight:800; font-size:1rem; color:#111827; letter-spacing:.2px;
  display:flex; align-items:center; gap:.5rem;
}
.subject-card .card-body{ padding:1rem; }

/* badge chip */
.subject-chip{
  display:inline-flex; align-items:center; gap:.4rem;
  font-size:.78rem; padding:.25rem .55rem; border-radius:999px;
  border:1px solid #e7ecf5; background:#f8fafc; color:#475569;
}

/* Labels & inputs (scoped to exact IDs you use) */
label[for="code"], label[for="name"]{
  font-weight:700; color:#0f172a; font-size:.88rem; margin-bottom:.35rem; display:inline-block;
}
#code, #name{
  border:1px solid #dbe3ef; border-radius:.55rem; padding:.58rem .75rem; font-size:.95rem; background:#fff; color:#111827;
  transition: box-shadow .15s, border-color .15s;
}
#code:focus, #name:focus{
  border-color:#86b7fe; outline:0; box-shadow:0 0 0 .2rem rgba(13,110,253,.12);
}
.form-text{ color:#6b7280; font-size:.8rem; }
.form-group{ margin-bottom:1rem; }

/* Subtle divider (optional) */
.subject-hr{
  border:0; height:1px; margin:1rem 0; background:linear-gradient(90deg,transparent,#e9eef6,transparent);
}

/* Small responsive tweak */
@media (max-width: 576px){
  .subject-card .card-body{ padding:.85rem; }
}
</style>

<div class="subject-card">
  <div class="card-header">
    <h5 class="card-title">
      <span style="font-size:1.05rem;">📘</span>
      Subject Details
    </h5>
    <span class="subject-chip">Basics</span>
  </div>

  <div class="card-body">
    <div class="form-group">
      <label for="code">Subject Code</label>
      <input
        type="text"
        class="form-control"
        id="code"
        name="code"
        value="<?= ($details)?htmlspecialchars($details['code']):'' ?>"
        placeholder="e.g., ENG10"
        autocomplete="off"
      >
      <div class="form-text">Use a short, unique code (e.g., MATH7, SCI-101).</div>
    </div>

    <div class="form-group">
      <label for="name">Subject Name</label>
      <input
        type="text"
        class="form-control"
        id="name"
        name="name"
        value="<?= ($details)?htmlspecialchars($details['name']):'' ?>"
        placeholder="e.g., English 10"
        autocomplete="off"
      >
      <div class="form-text">Full descriptive title as seen on reports.</div>
    </div>

    <!-- Optional divider if you add more fields later -->
    <!-- <hr class="subject-hr"> -->
  </div>
</div>
