<?php 
    // Preselected for edit mode
    $selectedIds = [];
    if (!empty($details) && !empty($details["student_id"])) {
        $selectedIds = [(string)$details["student_id"]];
    }

    if (!empty($details)) {
        echo '<input type="hidden" name="id" value="'.htmlspecialchars($details["id"]).'">';
    }
    // Flag so controller treats this as manual add
    echo '<input type="hidden" name="manualadd" value="manualadd">';
    // Used by JS when editing to preset curriculum
    echo '<input type="hidden" name="default_id_curriculum" id="defult_id_curriculum" value="">';
?>

<style>
/* ====== Visual design ====== */
.md-wrap { --gap: 1rem; }
.section-title{
  font-size:.95rem; font-weight:800; color:#0f172a; letter-spacing:.2px; margin:0 0 .6rem;
}
.hr-soft{
  border:0;height:1px;margin:1.1rem 0;background:linear-gradient(90deg,transparent,#e9eef6,transparent);
}

/* Card look & header */
.card.mod-card{
  background:#fff; border:1px solid #e8edf6; border-radius:12px; overflow:hidden;
  box-shadow:0 8px 18px rgba(15,23,42,.05);
}
.card.mod-card .card-header{
  display:flex; align-items:center; justify-content:space-between; gap:.75rem;
  padding:.9rem 1rem; background:#f8fafc; border-bottom:1px solid #eef2f7;
}
.schoolyear-badge{
  display:inline-flex; align-items:center; gap:.4rem;
  font-size:.78rem; padding:.3rem .55rem; border-radius:999px; border:1px solid #e7ecf5; background:#f8fafc; color:#0f172a;
}

/* Fields */
.form-label{ font-weight:700; color:#0f172a; font-size:.88rem; }
.form-text{ color:#6b7280; font-size:.8rem; }
.form-control, .form-select, .btn-students-trigger{
  border:1px solid #dbe3ef; border-radius:.55rem; padding:.58rem .75rem; font-size:.95rem; background:#fff;
  transition: box-shadow .15s, border-color .15s;
}
.form-control:focus, .form-select:focus, .btn-students-trigger:focus{
  border-color:#86b7fe; box-shadow:0 0 0 .2rem rgba(13,110,253,.12); outline:0;
}

/* Students dropdown shell (no sticky; JS sets max-height dynamically) */
.btn-students-trigger{
  width:100%; text-align:left; display:flex; align-items:center; justify-content:space-between;
}
.student-dropdown{
  border:1px solid #e8edf6; border-radius:12px;
  box-shadow:0 10px 22px rgba(15,23,42,.08);
  width:min(760px, 96vw);
  overflow:auto;                 /* scroll when content exceeds max-height */
  margin-top: 14px;              /* breathing room under trigger */
  /* keep Popper from shoving it under fixed headers */
  transform:none !important;
  inset:auto !important;
  left:0 !important;
  right:auto !important;
}

.student-list{ display:flex; flex-direction:column; gap:.25rem; }
.student-item{ padding:.25rem .25rem; border-radius:.35rem; }
.student-item:hover{ background:#f8fafc; }

/* Right column preview */
.preview-card{
  background:#fff; border:1px solid #e8edf6; border-radius:12px;
  box-shadow:0 8px 18px rgba(15,23,42,.05);
}
.preview-head{
  display:flex; align-items:center; justify-content:space-between; gap:.5rem;
  padding:.75rem 1rem; background:#f8fafc; border-bottom:1px solid #eef2f7;
  font-weight:700; color:#111827;
}
.badge-soft{
  display:inline-flex; align-items:center; gap:.4rem; font-size:.78rem;
  padding:.25rem .5rem; border-radius:999px; border:1px solid #e7ecf5; background:#f8fafc; color:#475569;
}
.preview-body{ padding:1rem; }
.bg-light-subtle{ background:#f8fafc; }

.card-footer{
  background:#fff; border-top:1px solid #eef2f7; padding:.8rem 1rem;
}

/* Viewport-sized dropdown panel */
.student-dropdown {
  position: fixed !important;   /* escape modal's flow */
  z-index: 1085 !important;     /* above modal content (BS modal backdrop is 1050) */
  width: min(1100px, 96vw) !important;
  max-height: 88vh !important;  /* tall preview */
  overflow: auto !important;
  margin-top: 0 !important;     /* we’ll set exact top/left via JS */

  /* stop Popper from messing with placement */
  transform: none !important;
  inset: auto !important;
  left: 0 !important;   /* will be overridden inline by JS */
  top: 0 !important;    /* will be overridden inline by JS */
  right: auto !important;
}

/* optional: nice rounded look still */
.student-dropdown {
  border: 1px solid #e8edf6;
  border-radius: 12px;
  box-shadow: 0 10px 22px rgba(15,23,42,.12);
}


/* Spacing */
.mb-1{ margin-bottom:.25rem !important; }
.mb-2{ margin-bottom:.5rem !important; }
.mb-3{ margin-bottom:1rem !important; }
.mb-4{ margin-bottom:1.25rem !important; }

/* Mobile tweak */
@media (max-width: 768px){
  .section-title{ margin-top:.5rem; }
}
</style>

<div class="md-wrap">
  <div class="card mod-card modal-details-card">
    <div class="card-header">
      <div class="d-flex align-items-center gap-2">
        <div>
          <h5 class="mb-0" style="font-weight:800; color:#111827;">Manage Registration</h5>
          <small class="text-muted">Choose grade/section &amp; curriculum, then filter and select students.</small>
        </div>
      </div>
      <span class="schoolyear-badge">
        <span class="me-1" style="font-weight:700;">School Year:</span>
        <span class="schoolyear">—</span>
      </span>
    </div>

    <div class="card-body" style="padding:1rem;">
      <div class="row g-4">
        <!-- LEFT COLUMN -->
        <div class="col-12 col-lg-6">
          <div class="card border-0">
            <div class="card-body p-0">
              <div class="section-title">Placement</div>

              <!-- GRADE & SECTION -->
              <div class="form-group mb-4">
                <label for="section_id" class="form-label">Grade Level &amp; Section</label>
                <div class="dropdown w-100">
                  <select name="section_id" id="section_id"
                          class="form-select select-change-modal"
                          data-type="section" data-append="curriculum_id">
                    <option value="">— Select —</option>
                    <?php 
                      foreach ($section as $value) {
                        $selected = ((!empty($details)) && ($value["id"] == ($details["csection_id"] ?? null))) ? 'selected' : '';
                        echo '<option value="'.htmlspecialchars($value["id"]).'" '.$selected.'>'.htmlspecialchars($value["name"]).'</option>';
                      }
                    ?>
                  </select>
                </div>
                <div class="form-text">Pick the grade &amp; section to reveal available curricula.</div>
              </div>

              <!-- CURRICULUM -->
              <div class="form-group mb-4">
                <label for="curriculum_id" class="form-label">Curriculum (Academic Year)</label>
                <div class="dropdown w-100">
                  <select name="curriculum_id" id="curriculum_id"
                          class="form-select select-change-modal"
                          data-type="curriculum" data-append="tableSubjectOffer">
                  </select>
                </div>
                <div class="form-text">Choose an academic year to preview the subjects offered.</div>
              </div>

              <!-- STUDENTS -->
              <div class="form-group mb-1" id="student_filter_root">
                <label class="form-label">Student Name(s)</label>

                <div class="dropdown w-100">
                  <button
                    class="btn-students-trigger"
                    type="button"
                    data-toggle="dropdown"
                    data-bs-toggle="dropdown"
                    data-bs-display="static"
                    data-bs-auto-close="outside"
                    data-bs-offset="0,12"
                    aria-expanded="false"
                  >
                    <span id="student_btn_label" class="text-truncate">Select students…</span>
                    <i class="ti-angle-down"></i>
                  </button>

                  <!-- Wide, scrollable dropdown; height set dynamically by JS -->
                  <div class="dropdown-menu p-3 shadow student-dropdown">
                    <!-- Filters -->
                    <div class="row g-2 mb-2">
                      <div class="col-6">
                        <label class="form-label small mb-1" for="filterBatch">Filter by Batch</label>
                        <select id="filterBatch" class="form-select form-select-sm">
                          <option value="">All Batches</option>
                          <?php
                            if (!empty($batches)) {
                              foreach ($batches as $b) {
                                $val = (string)($b['batch'] ?? '');
                                if ($val === '') continue;
                                echo '<option value="'.htmlspecialchars($val).'">'.htmlspecialchars($val).'</option>';
                              }
                            }
                          ?>
                        </select>
                      </div>
                      <div class="col-6">
                        <label class="form-label small mb-1" for="filterSet">Filter by Set</label>
                        <select id="filterSet" class="form-select form-select-sm">
                          <option value="">All Sets</option>
                          <?php
                            if (!empty($sets)) {
                              foreach ($sets as $s) {
                                $val = (string)($s['set_group'] ?? '');
                                if ($val === '') continue;
                                echo '<option value="'.htmlspecialchars($val).'">'.htmlspecialchars($val).'</option>';
                              }
                            }
                          ?>
                        </select>
                      </div>
                    </div>

                    <!-- Search + Bulk Buttons -->
                    <div class="d-flex align-items-center justify-content-between mb-2">
                      <input type="text" class="form-control me-2" placeholder="Search students…" id="student_filter" />
                      <div class="btn-group btn-group-sm" role="group" aria-label="bulk-actions">
                        <button type="button" class="btn btn-outline-secondary" id="student_select_all">Select all</button>
                        <button type="button" class="btn btn-outline-secondary" id="student_clear">Clear</button>
                      </div>
                    </div>

                    <!-- Student list -->
                    <div id="student_checkbox_list" class="student-list">
                      <?php foreach ($studentlist as $s): 
                          $checked = in_array((string)$s['id'], $selectedIds, true) ? 'checked' : '';
                          $batch   = (string)($s['batch'] ?? '');
                          $setg    = (string)($s['set_group'] ?? '');
                      ?>
                        <label class="student-item d-flex align-items-center"
                               data-batch="<?= htmlspecialchars(strtolower($batch)) ?>"
                               data-set="<?= htmlspecialchars(strtolower($setg)) ?>">
                          <input type="checkbox" class="student-check form-check-input me-2" value="<?= htmlspecialchars((string)$s['id']) ?>" <?= $checked ?>>
                          <span class="student-name text-truncate">
                            <?= htmlspecialchars($s['name']) ?>
                            <?php if ($batch || $setg): ?>
                              <small class="text-muted"> — <?= htmlspecialchars(trim($batch . ($batch && $setg ? ' • ' : '') . $setg)) ?></small>
                            <?php endif; ?>
                          </span>
                        </label>
                      <?php endforeach; ?>
                    </div>
                  </div>
                </div>

                <div id="student_hidden_inputs"></div>
                <div class="form-text">Tip: Use Batch/Set filter first, then search or bulk-select.</div>
              </div>
            </div>
          </div>
        </div>

        <!-- RIGHT COLUMN -->
        <div class="col-12 col-lg-6">
          <div class="preview-card">
            <div class="preview-head">
              <span>Subjects Offered</span>
              <span class="badge-soft">Preview</span>
            </div>
            <div class="preview-body">
              <p class="text-muted small mb-3">This updates once you select an Academic Year.</p>
              <div class="border rounded p-3 bg-light-subtle" style="min-height: 220px; border-color:#e8edf6;">
                <div id="tableSubjectOffer"></div>
              </div>
              <div class="alert alert-info mt-3 mb-0 small" style="border-radius:.55rem;">
                <strong>Note:</strong> Make sure students are not already registered for the same curriculum. Duplicates are automatically skipped.
              </div>
            </div>
          </div>
        </div>
      </div> <!-- /row -->
    </div> <!-- /card-body -->

    <div class="card-footer d-flex align-items-center justify-content-between">
      <small class="text-muted">Review your selections before submitting.</small>
      <div class="d-flex align-items-center gap-2">
        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
      </div>
    </div>
  </div>
</div>
