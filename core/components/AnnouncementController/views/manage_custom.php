<style>
  <style>
  .modal-xl { width: 1140px; }
  .preview-img {
    width: 100%; height: 200px; object-fit: cover;
    border: 2px dashed #ccc; display: block; margin-top: 10px;
  }

  /* Label styling for Audience/Status */
  .label {
    display:inline-block;
    padding:.35rem .65rem;
    border-radius:.35rem;
    font-size:.8rem;
    font-weight:700;
    color:#fff;            /* force white text for contrast */
    text-transform: uppercase;
    letter-spacing: .5px;
  }

  .label-info    { background:#0284c7; } /* stronger blue for ALL */
  .label-primary { background:#2563eb; } /* solid blue for STUDENTS */
  .label-warning { background:#eab308; } /* gold for TEACHERS */
  .label-success { background:#16a34a; } /* green for ACTIVE */
  .label-danger  { background:#dc2626; } /* red for INACTIVE */
</style>

</style>

<div class="col-xl-12 col-md-12">
  <button class="btn waves-effect waves-light btn-primary openmodaldetails-modal" data-type="add">
    <i class="fa fa-plus"></i>&nbsp;Add Announcement
  </button>

  <div class="card table-card">
    <div class="card-header">
      <h5>Manage Announcements</h5>
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

      <!-- Audience quick filters -->
      <div class="mb-3">
        <?php
          $aud = isset($current_audience) ? $current_audience : 'all';
          function audLink($label, $value, $current) {
            $active = ($current === $value) ? 'btn-primary' : 'btn-outline-primary';
            $href = 'index?audience=' . $value;
            return '<a class="btn '.$active.' btn-sm mr-2" href="'.$href.'">'.$label.'</a>';
          }
          echo audLink('All', 'all', $aud);
          echo audLink('Students', 'students', $aud);
          echo audLink('Teachers', 'teachers', $aud);
        ?>
      </div>

      <div class="table-responsive">
        <table id="mainTable" class="table table-hover">
          <thead>
            <tr>
              <th>Title</th>
              <th>Audience</th>
              <th>Start Date</th>
              <th>End Date</th>
              <th>Date Created</th>
              <th>Status</th>
              <th style="width:150px;">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php
              $status = [
                1 => '<label class="label label-success">ACTIVE</label>',
                0 => '<label class="label label-danger">INACTIVE</label>',
              ];
              if(isset($list) && count($list) > 0):
                foreach ($list as $row):
            ?>
            <tr>
              <td><?= htmlspecialchars($row["title"]) ?></td>
              <td>
                <?php
                  $scope = strtolower((string)$row["audience_scope"]);
                  $labels = [
                    'all' => '<label class="label label-info">ALL</label>',
                    'students' => '<label class="label label-primary">STUDENTS</label>',
                    'teachers' => '<label class="label label-warning">TEACHERS</label>',
                  ];
                  echo $labels[$scope] ?? htmlspecialchars($scope);
                ?>
              </td>
              <td><?= htmlspecialchars($row["start_date"]) ?></td>
              <td><?= htmlspecialchars($row["end_date"]) ?></td>
              <td><?= htmlspecialchars($row["created_at"]) ?></td>
              <td><?= $status[(int)$row["status"]] ?></td>
              <td>
                <button class="btn waves-effect waves-light btn-grd-primary btn-sm openmodaldetails-modal"
                        data-type="edit" data-id="<?= (int)$row["announcement_id"] ?>">
                  <i class="fa fa-edit"></i>
                </button>
                <button class="btn waves-effect waves-light btn-secondary btn-sm openmodaldetails-modal"
                        data-type="view" data-id="<?= (int)$row["announcement_id"] ?>">
                  <i class="fa fa-eye"></i>
                </button>
                <button class="btn waves-effect waves-light btn-danger btn-sm delete"
                        data-id="<?= (int)$row["announcement_id"] ?>">
                  <i class="fa fa-trash"></i>
                </button>
              </td>
            </tr>
            <?php endforeach; else: ?>
            <!-- <tr><td colspan="7">NO RECORD FOUND!</td></tr> -->
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
