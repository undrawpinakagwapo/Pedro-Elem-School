<?php 
    if($details) {
        echo '<input type="hidden"  name="id" value="'.htmlspecialchars($details["id"]).'">';
    }
?>

<style>
/* ====== Borrowed design language from your 2nd file (visual only) ====== */
.md-wrap { --gap: 1rem; }

.section-title{
  font-size:.95rem; font-weight:800; color:#0f172a; letter-spacing:.2px; margin:0 0 .6rem;
}
.hr-soft{
  border:0;height:1px;margin:1.1rem 0;background:linear-gradient(90deg,transparent,#e9eef6,transparent);
}

/* Card containers */
.card.mod-card{
  background:#fff; border:1px solid #e8edf6; border-radius:12px; overflow:hidden;
  box-shadow:0 8px 18px rgba(15,23,42,.05);
}
.card.mod-card .card-header{
  display:flex; align-items:center; justify-content:space-between; gap:.75rem;
  padding:.9rem 1rem; background:#f8fafc; border-bottom:1px solid #eef2f7;
}
.card.mod-card .card-body{ padding:1rem; }

.badge-soft{
  display:inline-flex; align-items:center; gap:.4rem; font-size:.78rem;
  padding:.25rem .5rem; border-radius:999px; border:1px solid #e7ecf5; background:#f8fafc; color:#475569;
}

/* Labels & inputs (scoped to exact IDs you use) */
label[for="name"],
label[for="school_year"],
label[for="grade_id"],
label[for="adviser_id"] {
  font-weight:700; color:#0f172a; font-size:.88rem;
}

#name, #school_year, #grade_id, #adviser_id {
  border:1px solid #dbe3ef; border-radius:.55rem; padding:.58rem .75rem; font-size:.95rem; background:#fff; color:#111827;
  transition: box-shadow .15s ease, border-color .15s ease;
}
#name:focus, #school_year:focus, #grade_id:focus, #adviser_id:focus {
  border-color:#86b7fe; outline:0; box-shadow:0 0 0 .2rem rgba(13,110,253,.12);
}

.form-text{ color:#6b7280; font-size:.8rem; }
.form-group{ margin-bottom:1rem; }

/* Tables: #mysubjectlist and #subjectlist only */
.table-shell{
  background:#fff; border:1px solid #e8edf6; border-radius:12px; overflow:hidden;
  box-shadow:0 8px 18px rgba(15,23,42,.05);
}
.table-shell .table{
  margin:0;
  border-color:#e7eaf0;
}
#mysubjectlist thead th, #subjectlist thead th {
  background: linear-gradient(90deg, #2258e3, #2e6af0);
  color: #fff; border-color:#1e4fd3; font-weight:700; position:sticky; top:0; z-index:2;
}
#mysubjectlist tbody td, #subjectlist tbody td {
  vertical-align: middle; background:#fff;
}
#mysubjectlist tbody tr:hover, #subjectlist tbody tr:hover {
  background: rgba(13,110,253,.05);
}
/* compact and consistent cell spacing */
#mysubjectlist td, #mysubjectlist th,
#subjectlist td,   #subjectlist th {
  padding:.75rem .85rem;
}
/* center the action + numbering columns only */
#mysubjectlist th:nth-child(1), #mysubjectlist td:nth-child(1),
#mysubjectlist th:nth-child(2), #mysubjectlist td:nth-child(2),
#subjectlist  th:nth-child(1),   #subjectlist  td:nth-child(1) {
  text-align:center; width:72px; white-space:nowrap;
}
/* Buttons inside tables */
#mysubjectlist .btn.btn-sm, #subjectlist .btn.btn-sm {
  border-radius:.4rem; padding:.25rem .45rem;
}
/* wrappers keep rounded corners */
.table-responsive { border-radius:12px; }

/* Headings */
h4{
  font-weight:700; font-size:1rem; color:#1f2937; margin-bottom:.5rem; display:flex; align-items:center; gap:.5rem;
}
h4 .badge-soft{ font-size:.7rem; }

/* Layout spacing */
.mb-2{ margin-bottom:.5rem !important; }
.mb-3{ margin-bottom:1rem !important; }
.mb-4{ margin-bottom:1.25rem !important; }

/* Subtle background for notes */
.bg-light-subtle{ background:#f8fafc; border:1px solid #eef2f7; border-radius:12px; padding:.75rem; }
</style>

<div class="md-wrap">
  <!-- Top Card: Curriculum Details -->
  <div class="card mod-card mb-4">
    <div class="card-header">
      <div style="font-weight:800;color:#111827;">Curriculum Details</div>
      <span class="badge-soft">Setup</span>
    </div>
    <div class="card-body">
      <div class="form-group">
        <label for="name">Curriculum Name</label>
        <input type="text" class="form-control" id="name" name="name" value="<?=($details)?htmlspecialchars($details["name"]):''?>" placeholder="e.g., MATATAG">
        <div class="form-text">Give this curriculum a clear, searchable name.</div>
      </div>

      <div class="row g-3">
        <div class="col-sm">
          <div class="form-group">
            <label for="grade_id">Grade Level &amp; Section</label>
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
          </div>
        </div>
        <div class="col-sm">
          <div class="form-group">
            <label for="adviser_id">Adviser Name</label>
            <select name="adviser_id" id="adviser_id" class="form-control">
            <?php 
                if($adviser) {
                    foreach ($adviser as $key => $value) {
                        $selected =( ($details) && ($value["id"] == $details["adviser_id"]))?'selected':'';
                        echo '<option value="'.htmlspecialchars($value["id"]).'" '.$selected.'>'.htmlspecialchars($value["name"]).'</option>';
                    }
                }
            ?>
            </select>
          </div>
        </div>
      </div>

      <div class="form-group mb-0">
        <label for="school_year">School Year</label>
        <input type="text" class="form-control" id="school_year" name="school_year" value="<?=($details)?htmlspecialchars($details["school_year"]):''?>" placeholder="e.g., 2025 - 2026">
      </div>
    </div>
  </div>

  <!-- Divider -->
  <hr class="hr-soft">

  <!-- Bottom: Subjects Dual Column -->
  <div class="row g-4">
    <div class="col-sm-6">
      <div class="card mod-card">
        <div class="card-header">
          <div style="font-weight:800;color:#111827;">Subjects</div>
          <span class="badge-soft">Assigned</span>
        </div>
        <div class="card-body">
          <div class="table-shell">
            <div class="table-responsive">
              <table class="table table-hover table-bordered" id="mysubjectlist">
                  <thead>
                      <tr>
                          <th></th>
                          <th>No.#</th>
                          <th>Subject Code</th>
                          <th>Subject Name</th>
                      </tr>
                  </thead>
                  <tbody>
                  <?php if($child): ?>
                      <?php $i=1; ?>
                      <?php foreach ($child as $value): ?>
                          <tr>
                              <td>
                                  <button type="button" class="btn btn-sm btn-danger remove_edit" 
                                      data-id="<?= htmlspecialchars($value['subject_id']) ?>" 
                                      data-code="<?= htmlspecialchars($value['code']) ?>" 
                                      data-name="<?= htmlspecialchars($value['name']) ?>">
                                      <i class="fa fa-minus"></i>
                                  </button>
                                  <input type="hidden" value="<?= htmlspecialchars($value['subject_id']) ?>" name="itemlist[data][old<?=htmlspecialchars($value["id"])?>][subject_id]" >
                                  <input type="hidden" value="<?= htmlspecialchars($value['id']) ?>" name="itemlist[data][old<?=htmlspecialchars($value["id"])?>][id]" >
                                  <input type="hidden" class="deleted" value="0" name="itemlist[data][old<?=htmlspecialchars($value["id"])?>][deleted]" >
                              </td>
                              <td><?=$i?></td>
                              <td><?= htmlspecialchars($value['code']) ?></td>
                              <td><?= htmlspecialchars($value['name']) ?></td>
                          </tr>
                          <?php $i++; ?>
                      <?php endforeach; ?>
                  <?php endif; ?>
                  </tbody>
              </table>
            </div>
          </div>
          <div class="bg-light-subtle mt-3">
            Tip: You can remove a subject using the minus button.
          </div>
        </div>
      </div>
    </div>

    <div class="col-sm-6">
      <div class="card mod-card">
        <div class="card-header">
          <div style="font-weight:800;color:#111827;">List of Subjects</div>
          <span class="badge-soft">Available</span>
        </div>
        <div class="card-body">
          <div class="table-shell">
            <div class="table-responsive">
              <table class="table table-hover table-bordered" id="subjectlist">
                  <thead>
                      <tr>    
                          <th></th>
                          <th>Subject Code</th>
                          <th>Subject Name</th>
                      </tr>
                  </thead>
                  <tbody>
                  <?php foreach ($subjects as $value): ?>
                      <tr>
                          <td>
                              <button type="button" class="btn btn-sm btn-primary add_subject" 
                                  data-id="<?= htmlspecialchars($value['id']) ?>" 
                                  data-code="<?= htmlspecialchars($value['code']) ?>" 
                                  data-name="<?= htmlspecialchars($value['name']) ?>">
                                  <i class="fa fa-plus"></i>
                              </button>
                          </td>
                          <td><?= htmlspecialchars($value['code']) ?></td>
                          <td><?= htmlspecialchars($value['name']) ?></td>
                      </tr>
                  <?php endforeach; ?>
                  </tbody>
              </table>
            </div>
          </div>
          <div class="bg-light-subtle mt-3">
            Tip: Click the plus button to add a subject to the curriculum.
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
