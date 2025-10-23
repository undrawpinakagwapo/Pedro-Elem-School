<?php
// components/ManageSubjectsController/views/custom.php
?>
<style>
  .modal-l { width: 600px; }
</style>

<div class="col-xl-12 col-md-12">
  <button class="btn waves-effect waves-light btn-primary openmodaldetails-modal" data-type="add">
    <i class="fa fa-plus"></i>&nbsp;Add New
  </button>

  <div class="card table-card mt-3">
    <div class="card-header">
      <h5>Manage Subjects</h5>
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
      <div class="table-responsive">
        <table id="mainTable" class="table table-hover">
          <thead>
            <tr>
              <th>#</th>
              <th>Subject Code</th>
              <th>Subject Name</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($list)): ?>
              <?php foreach ($list as $i => $row): ?>
                <tr>
                  <td><?= $i+1 ?></td>
                  <td><?= htmlspecialchars($row['code'] ?? '') ?></td>
                  <td><?= htmlspecialchars($row['name'] ?? '') ?></td>
                  <td>
                    <button class="btn waves-effect waves-light btn-grd-primary btn-sm openmodaldetails-modal"
                            data-type="edit" data-id="<?= (int)($row['id'] ?? 0) ?>">
                      <i class="fa fa-edit"></i>
                    </button>
                    |
                    <button class="btn waves-effect waves-light btn-grd-danger btn-sm delete"
                            data-id="<?= (int)($row['id'] ?? 0) ?>">
                      <i class="fa fa-times"></i>
                    </button>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
