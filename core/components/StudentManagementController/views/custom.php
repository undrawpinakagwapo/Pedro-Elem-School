<style>
  .modal-xl { width: 1300px; }

  .preview-img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    border: 2px dashed #ccc;
    display: block;
    margin-top: 10px;
  }

  /* Top action buttons flush with the card */
  .actions-top {
    display: flex;
    gap: .5rem;
    align-items: center;
    margin: 0;
    padding: 0;
  }
  .actions-top .btn { margin: 0; }
  .actions-top + .card { margin-top: 0 !important; }

  /* Card styling */
  .card.table-card .card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
    padding-top: .75rem;
    padding-bottom: .75rem;
  }
  .card.table-card .card-title {
    margin: 0;
    font-weight: 600;
  }
  .filters-row {
    border-top: 1px solid rgba(0,0,0,.05);
    padding-top: 1rem;
    margin-top: .5rem;
  }

  /* Make selects look like normal outlined inputs & beat theme overrides */
  .styled-select {
    appearance: none !important;
    -webkit-appearance: none !important;
    -moz-appearance: none !important;

    display: block !important;
    width: 100% !important;
    height: 2.375rem !important;                /* consistent height */
    line-height: 1.5 !important;
    padding: .375rem 2rem .375rem .75rem !important; /* right padding for caret */

    color: #495057 !important;
    background-color: #fff !important;
    background-image: none !important;

    border: 1px solid #ced4da !important;
    border-radius: .25rem !important;
    box-shadow: none !important;
  }
  .styled-select:focus {
    border-color: #80bdff !important;
    outline: 0 !important;
    box-shadow: 0 0 0 .2rem rgba(0,123,255,.25) !important;
  }
  .styled-select::-ms-expand { display: none; }  /* hide old IE arrow */
  .styled-select option[disabled] { color: #6c757d; } /* placeholder look */

  /* Wrapper for a custom arrow */
  .styled-select-wrapper { position: relative; }
  .styled-select-wrapper::after {
    content: "";
    position: absolute;
    right: .75rem;
    top: 50%;
    margin-top: -3px;
    width: 0; height: 0;
    border-left: 5px solid transparent;
    border-right: 5px solid transparent;
    border-top: 6px solid #6c757d;  /* caret color */
    pointer-events: none;           /* clicks go to select */
  }
</style>

<div class="col-xl-12 col-md-12">

  <!-- Buttons outside the card (flush to top) -->
  <div class="actions-top">
    <button class="btn waves-effect waves-light btn-primary openmodaldetails-modal" data-type="add">
      <i class="fa fa-plus"></i>&nbsp;Add Student
    </button>
    <button class="btn waves-effect waves-light btn-danger importsutdentmodal" data-type="import">
      <i class="fa fa-download"></i>&nbsp;Import Students
    </button>
  </div>

  <div class="card table-card">
    <div class="card-header">
      <h5 class="card-title">Manage Students</h5>
      <div class="card-header-right">
        <ul class="list-unstyled card-option">
          <li><i class="fa fa fa-wrench open-card-option"></i></li>
          <li><i class="fa fa-window-maximize full-card"></i></li>
          <li><i class="fa fa-minus minimize-card"></i></li>
          <li><i class="fa fa-refresh reload-card"></i></li>
          <li><i class="fa fa-trash close-card"></i></li>
        </ul>
      </div>
    </div>

    <div class="card-block">
      <!-- Filters -->
      <div class="row filters-row">
        <div class="col-md-6 mb-3">
          <label for="filterBatch" class="form-label">Filter by School Year</label>
          <div class="styled-select-wrapper">
            <select id="filterBatch" class="form-control styled-select">
              <option value="" selected disabled>All School Years</option>
              <?php
              if (!empty($batches)) {
                foreach ($batches as $b) {
                  $val = $b['batch'];
                  echo '<option value="'.htmlspecialchars($val).'">'.htmlspecialchars($val).'</option>';
                }
              }
              ?>
            </select>
          </div>
        </div>

        <div class="col-md-6 mb-3">
          <label for="filterSet" class="form-label">Filter by Grade &amp; Section</label>
          <div class="styled-select-wrapper">
            <select id="filterSet" class="form-control styled-select">
              <option value="" selected disabled>All Grades &amp; Sections</option>
              <?php
              if (!empty($sets)) {
                foreach ($sets as $s) {
                  $val = $s['set_group'];
                  echo '<option value="'.htmlspecialchars($val).'">'.htmlspecialchars($val).'</option>';
                }
              }
              ?>
            </select>
          </div>
        </div>
      </div>

      <!-- Table -->
      <div class="table-responsive">
        <table class="table table-hover" id="mainTable">
          <thead>
            <tr>
              <th>No.</th>
              <th>LRN</th>
              <th>Student Name</th>
              <th>Birth Date</th>
              <th>Gender</th>
              <th>School Year</th>
              <th>Grade &amp; Section</th>

              <!-- NEW COLUMNS -->
              <th>Mother Tongue</th>
              <th>Religion</th>
              <th>House #/ Street/ Sitio/ Purok</th>
              <th>Barangay</th>
              <th>Municipality/ City</th>
              <th>Province</th>
              <th>Father's Name</th>
              <th>Mother's Maiden Name</th>
              <th>Guardian</th>
              <th>Relationship</th>
              <th>Contact Number</th>
              <th>Learning Modality</th>
              <th>Remarks</th>
              <!-- /NEW -->

              <th>Added Date</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php
              if (count($list) > 0) {
                $i = 1;
                foreach ($list as $key => $value) {
            ?>
              <tr
                data-batch="<?=htmlspecialchars($value["batch"] ?? '')?>"
                data-set="<?=htmlspecialchars($value["set_group"] ?? '')?>"
              >
                <td><?=$i?></td>
                <td><?=$value["LRN"]?></td>
                <td><?=$value["full_name"]?></td>
                <td><?=$value["dateof_birth"]?></td>
                <td><?=$value["gender"]?></td>
                <td><?=htmlspecialchars($value["batch"] ?? '')?></td>
                <td><?=htmlspecialchars($value["set_group"] ?? '')?></td>

                <!-- NEW CELLS -->
                <td><?=htmlspecialchars($value["mother_tongue"] ?? '')?></td>
                <td><?=htmlspecialchars($value["religion"] ?? '')?></td>
                <td><?=htmlspecialchars($value["house_street_sitio_purok"] ?? '')?></td>
                <td><?=htmlspecialchars($value["barangay"] ?? '')?></td>
                <td><?=htmlspecialchars($value["municipality_city"] ?? '')?></td>
                <td><?=htmlspecialchars($value["province"] ?? '')?></td>
                <td><?=htmlspecialchars($value["father_name"] ?? '')?></td>
                <td><?=htmlspecialchars($value["mother_name"] ?? '')?></td>
                <td><?=htmlspecialchars($value["guardian"] ?? '')?></td>
                <td><?=htmlspecialchars($value["relationship"] ?? '')?></td>
                <td><?=htmlspecialchars($value["contact_no_of_parent"] ?? '')?></td>
                <td><?=htmlspecialchars($value["learning_modality"] ?? '')?></td>
                <td><?=htmlspecialchars($value["remarks"] ?? '')?></td>
                <!-- /NEW -->

                <td><?=$value["created_at"]?></td>
                <td>
                  <button
                    class="btn waves-effect waves-light btn-grd-primary btn-sm openmodaldetails-modal"
                    data-type="edit" data-id="<?=$value["user_id"]?>"
                  >
                    <i class="fa fa-edit"></i>
                  </button>
                </td>
              </tr>
            <?php
                  $i++;
                }
              } else {
            ?>
            <?php
              }
            ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
  // Simple client-side filter for School Year & Grade+Section
  (function() {
    const batchSel = document.getElementById('filterBatch');
    const setSel   = document.getElementById('filterSet');
    const rows     = document.querySelectorAll('#mainTable tbody tr');

    function applyFilter() {
      const b = (batchSel.value || '').toLowerCase();
      const s = (setSel.value   || '').toLowerCase();

      rows.forEach(tr => {
        const tb = (tr.getAttribute('data-batch') || '').toLowerCase();
        const ts = (tr.getAttribute('data-set')   || '').toLowerCase();

        const matchB = !b || tb === b;
        const matchS = !s || ts === s;

        tr.style.display = (matchB && matchS) ? '' : 'none';
      });
    }

    batchSel.addEventListener('change', applyFilter);
    setSel.addEventListener('change', applyFilter);
  })();
</script>
