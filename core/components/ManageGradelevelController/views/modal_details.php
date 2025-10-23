<?php 
    if($details) {
        echo '<input type="hidden" name="id" value="'.htmlspecialchars($details["id"]).'">';
    }
?>

<style>
/* ===== Generic Form Card ===== */
.simple-card {
  background:#fff; border:1px solid #e8edf6; border-radius:12px; overflow:hidden;
  box-shadow:0 6px 16px rgba(15,23,42,.06); margin-bottom:1rem;
}
.simple-card .card-body { padding:1rem 1.25rem; }

/* Labels & Inputs */
label[for="code"], label[for="name"] {
  font-weight:700; color:#0f172a; font-size:.88rem;
  margin-bottom:.35rem; display:inline-block;
}
#code, #name {
  border:1px solid #dbe3ef; border-radius:.55rem;
  padding:.58rem .75rem; font-size:.95rem;
  background:#fff; color:#111827;
  transition: border-color .15s, box-shadow .15s;
}
#code:focus, #name:focus {
  border-color:#86b7fe; outline:0;
  box-shadow:0 0 0 .2rem rgba(13,110,253,.12);
}
.form-text { color:#6b7280; font-size:.8rem; margin-top:.25rem; }
.form-group { margin-bottom:1rem; }

/* Responsive */
@media(max-width:576px){
  .simple-card .card-body { padding:.9rem; }
}
</style>

<div class="simple-card">
  <div class="card-body">
    <div class="form-group">
      <label for="code">Code</label>
      <input
        type="text"
        class="form-control"
        id="code"
        name="code"
        value="<?= ($details)?htmlspecialchars($details["code"]):'' ?>"
        placeholder="e.g., G1"
        autocomplete="off"
      >
      <div class="form-text">Enter a short, unique code (e.g., G1).</div>
    </div>

    <div class="form-group">
      <label for="name">Name</label>
      <input
        type="text"
        class="form-control"
        id="name"
        name="name"
        value="<?= ($details)?htmlspecialchars($details["name"]):'' ?>"
        placeholder="e.g., 1"
        autocomplete="off"
      >
      <div class="form-text">Provide the full descriptive name.</div>
    </div>
  </div>
</div>
