<?php
$isEdit = $details !== false && isset($details["announcement_id"]);
$isView = (!$isEdit && $details !== false);
function field($arr, $key, $default = '') { return isset($arr[$key]) ? htmlspecialchars($arr[$key]) : $default; }
$base = defined('URL_BASED') ? rtrim(URL_BASED, '/') . '/' : '/';
$actionUrl = $base . 'component/announcement/afterSubmit';
?>
<style>
/* --- Make the modal smaller & comfy --- */
.modal.no-header .modal-dialog {
  max-width: 720px;        /* <- set your preferred width (e.g., 640 / 720 / 800 px) */
  width: calc(100% - 2rem);/* keep some breathing room on mobile */
  margin: 1.5rem auto;     /* vertical spacing from viewport edges */
}
.modal.no-header .modal-content {
  border-radius: 12px;
  overflow: hidden;
}
.modal.no-header .modal-header { display: none !important; }

/* Keep content scrollable and not too tall */
.modal.no-header .modal-body {
  max-height: 80vh;        /* cap total height */
  overflow-y: auto;        /* scroll inside the body if needed */
  padding-top: 14px;
}

/* --- Card & form styling (slightly tightened) --- */
.section-card { background:#fff; border:1px solid #e8edf6; border-radius:12px; overflow:hidden; box-shadow:0 8px 18px rgba(15,23,42,.05); margin-bottom:0.5rem; }
.section-card .card-header{ display:flex; align-items:center; justify-content:space-between; padding:.75rem .9rem; background:#f8fafc; border-bottom:1px solid #eef2f7; }
.section-card .card-title{ margin:0; font-weight:800; font-size:1rem; color:#111827; letter-spacing:.2px; }
.section-card .card-body{ padding:.85rem .9rem; }

.form-group { margin-bottom:.75rem; }
.form-label { font-weight:700; color:#0f172a; font-size:.88rem; margin-bottom:.35rem; display:inline-block; }
.form-control, .form-select, .form-textarea {
  border:1px solid #dbe3ef; border-radius:.55rem; padding:.55rem .7rem; font-size:.95rem; background:#fff; color:#111827;
  transition: box-shadow .15s, border-color .15s; width:100%;
}
.form-textarea { min-height: 100px; resize: vertical; }
.form-control:focus, .form-select:focus, .form-textarea:focus {
  border-color:#86b7fe; outline:0; box-shadow:0 0 0 .2rem rgba(13,110,253,.12);
}
.form-text { color:#6b7280; font-size:.8rem; margin-top:.25rem; }

/* Image previews */
.preview-img { width:100%; height:180px; object-fit:cover; border:2px dashed #e5e7eb; display:none; margin-top:10px; border-radius:10px; }
.existing-img { width:100%; height:180px; object-fit:cover; margin-top:10px; border-radius:10px; border:2px solid #e5e7eb; }

/* On very small screens let it use most of the width */
@media (max-width: 480px) {
  .modal.no-header .modal-dialog { width: calc(100% - 1rem); margin: .75rem auto; }
}
</style>

<div class="section-card">
  <div class="card-header">
    <h5 class="card-title"><?= $isView ? 'View Announcement' : ($isEdit ? 'Edit Announcement' : 'New Announcement') ?></h5>
  </div>

  <div class="card-body">
    <!-- DIRECT POST -->
    <form id="announcementForm" method="POST" enctype="multipart/form-data" action="<?= htmlspecialchars($actionUrl) ?>">
      <?php if ($isEdit): ?>
        <input type="hidden" name="announcement_id" value="<?= (int)$details["announcement_id"] ?>">
      <?php endif; ?>

      <div class="form-group">
        <label class="form-label" for="title">Title <span class="text-danger">*</span></label>
        <input <?= $isView ? 'readonly' : '' ?> type="text" id="title" name="title" class="form-control" required value="<?= field($details, "title") ?>" placeholder="e.g., First Quarter Exams">
      </div>

      <div class="form-group">
        <label class="form-label" for="body">Body <span class="text-danger">*</span></label>
        <textarea <?= $isView ? 'readonly' : '' ?> id="body" name="body" class="form-textarea" required placeholder="Write the announcement details here..."><?= isset($details["body"]) ? htmlspecialchars($details["body"]) : '' ?></textarea>
      </div>

      <div class="form-group">
        <label class="form-label" for="audience_scope">Audience</label>
        <select <?= $isView ? 'disabled' : '' ?> id="audience_scope" name="audience_scope" class="form-select">
          <?php
            $currentScope = isset($details["audience_scope"]) ? $details["audience_scope"] : 'all';
            $opts = ['all'=>'Both (All Users)','students'=>'Students Only','teachers'=>'Teachers Only'];
            foreach ($opts as $val => $text) {
              $sel = ($currentScope === $val) ? 'selected' : '';
              echo "<option value=\"$val\" $sel>$text</option>";
            }
          ?>
        </select>
      </div>

      <div class="form-group">
        <label class="form-label" for="start_date">Start Date</label>
        <input <?= $isView ? 'readonly' : '' ?> type="datetime-local" id="start_date" name="start_date" class="form-control" value="<?= isset($details["start_date"]) ? date('Y-m-d\TH:i', strtotime($details["start_date"])) : '' ?>">
      </div>

      <div class="form-group">
        <label class="form-label" for="end_date">End Date</label>
        <input <?= $isView ? 'readonly' : '' ?> type="datetime-local" id="end_date" name="end_date" class="form-control" value="<?= isset($details["end_date"]) ? date('Y-m-d\TH:i', strtotime($details["end_date"])) : '' ?>">
      </div>

      <div class="form-group">
        <label class="form-label" for="status">Status</label>
        <select <?= $isView ? 'disabled' : '' ?> id="status" name="status" class="form-select">
          <option value="1" <?= (isset($details["status"]) && (int)$details["status"]===1)?'selected':''; ?>>Active</option>
          <option value="0" <?= (isset($details["status"]) && (int)$details["status"]===0)?'selected':''; ?>>Inactive</option>
        </select>
      </div>

      <div class="form-group">
        <label class="form-label" for="imageInput">Images</label>
        <input <?= $isView ? 'disabled' : '' ?> type="file" id="imageInput" name="image[]" class="form-control" accept="image/*" multiple>
        <img id="preview" class="preview-img" alt="Preview" style="display:none;">
        <?php if ($isEdit && !empty($details['image'])): ?>
          <div class="row mt-2">
            <?php foreach (explode('|', $details['image']) as $img): ?>
              <div class="col-12 col-sm-6 col-md-4">
                <img src="<?= htmlspecialchars($img) ?>" class="existing-img" alt="Existing image">
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <?php if (!$isView): ?>
        <div class="text-right">
          <button type="submit" class="btn btn-primary">Save</button>
        </div>
      <?php endif; ?>
    </form>
  </div>
</div>

<script>
(function(){
  // Remove Bootstrap header for this modal instance
  var modals = document.querySelectorAll('.modal.show, .modal.in');
  var modal  = modals.length ? modals[modals.length - 1] : document.querySelector('.modal');
  if (!modal) return;
  modal.classList.add('no-header');
  var hdr = modal.querySelector('.modal-header'); if (hdr) hdr.remove();

  // Simple image preview
  var input = document.getElementById('imageInput');
  var preview = document.getElementById('preview');
  if (input && preview) {
    input.addEventListener('change', function(){
      var f = this.files && this.files[0];
      if(!f){ preview.style.display='none'; return; }
      var r = new FileReader();
      r.onload = function(e){ preview.src = e.target.result; preview.style.display='block'; };
      r.readAsDataURL(f);
    });
  }
})();
</script>
