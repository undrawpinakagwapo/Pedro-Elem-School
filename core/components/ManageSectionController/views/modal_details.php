<?php 
    if($details) {
        echo '<input type="hidden" name="id" value="'.htmlspecialchars($details["id"]).'">';
    }
?>

<style>
/* ===== Section Form Card Design ===== */
.section-card {
  background:#fff; border:1px solid #e8edf6; border-radius:12px; overflow:hidden;
  box-shadow:0 8px 18px rgba(15,23,42,.05); margin-bottom:1rem;
}
.section-card .card-header{
  display:flex; align-items:center; justify-content:space-between;
  padding:.9rem 1rem; background:#f8fafc; border-bottom:1px solid #eef2f7;
}
.section-card .card-title{
  margin:0; font-weight:800; font-size:1rem; color:#111827; letter-spacing:.2px;
}
.section-card .card-body{ padding:1rem; }

/* Labels & inputs */
label[for="grade_id"],
label[for="code"],
label[for="name"] {
  font-weight:700; color:#0f172a; font-size:.88rem; margin-bottom:.35rem; display:inline-block;
}
#grade_id, #code, #name {
  border:1px solid #dbe3ef; border-radius:.55rem; padding:.58rem .75rem; font-size:.95rem; background:#fff; color:#111827;
  transition: box-shadow .15s, border-color .15s;
}
#grade_id:focus, #code:focus, #name:focus {
  border-color:#86b7fe; outline:0; box-shadow:0 0 0 .2rem rgba(13,110,253,.12);
}
.form-text { color:#6b7280; font-size:.8rem; margin-top:.25rem; }
.form-group { margin-bottom:1rem; }

/* Responsive tweak */
@media (max-width:576px){
  .section-card .card-body{ padding:.85rem; }
}
</style>

<div class="section-card">
  <div class="card-header">
    <h5 class="card-title">Section Details</h5>
  </div>

  <div class="card-body">
    <div class="form-group">
      <label for="grade_id">Grade Level</label>
      <select name="grade_id" id="grade_id" class="form-control">
        <?php 
          if($grade) {
            foreach ($grade as $key => $value) {
              $selected =( ($details) && ($value["id"] == $details["grade_id"]))?'selected':'';
              echo '<option value="'.htmlspecialchars($value["id"]).'" '.$selected.'>'.htmlspecialchars($value["name"]).'</option>';
            }
          }
        ?>
      </select>
      <div class="form-text">Select the grade this section belongs to.</div>
    </div>

    <div class="form-group">
      <label for="code">Section Code</label>
      <input
        type="text"
        class="form-control"
        id="code"
        name="code"
        value="<?= ($details)?htmlspecialchars($details["code"]):'' ?>"
        placeholder="e.g., SEC-10A"
        autocomplete="off"
      >
      <div class="form-text">Use a short, unique code (e.g., G7A, SCI-B).</div>
    </div>

    <div class="form-group">
      <label for="name">Section Name</label>
      <input
        type="text"
        class="form-control"
        id="name"
        name="name"
        value="<?= ($details)?htmlspecialchars($details["name"]):'' ?>"
        placeholder="e.g., Grade 10 - Section A"
        autocomplete="off"
      >
      <div class="form-text">Full descriptive name as seen on records.</div>
    </div>
  </div>
</div>
