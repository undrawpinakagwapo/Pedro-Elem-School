<style>
   .modal-xl { 
        width: 1200px;
    }
    .rowhide{
      display: none;
    }
</style>
<div class="col-xl-12 col-md-12">
    <button class="btn waves-effect waves-light btn-primary openmodaldetails-modal" data-type="add"><i class="fa fa-plus"></i>&nbsp;Add New</button>
    <div class="card table-card">
        <div class="card-header">
            <h5>Manage Curriculum</h5>
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
                  <table class="table table-hover" id="mainTable">
                      <thead>
                      <tr>
                          <th>ID</th>
                          <th>Curriculum Name</th>
                          <th>Grade Level & Section</th>
                          <th>Adviser Name</th>
                          <th>School Year</th>
                          <th>Status</th>
                          <th>Action</th>
                      </tr>
                      </thead>
                      <tbody>
                          <?php
                              if(count($list) > 0){
                                  foreach ($list as $key => $value) {
                                      $status = [
                                          1 => '<label class="label label-success">ACTIVE</label>',
                                          0 => '<label class="label label-danger">INACTIVE</label>',
                                      ];
                                      ?>
                                      <tr>
                                          <td><?=$value["id"]?></td>
                                          <td><?=$value["name"]?></td>
                                          <td><?=$value["gs_name"]?></td>
                                          <td><?=$value["adviser_name"]?></td>
                                          <td><?=$value["school_year"]?></td>
                                          <td><?=$status[$value["status"]]?></td>
                                          <td>
    <button class="btn waves-effect waves-light btn-grd-primary btn-sm openmodaldetails-modal" data-type="edit" data-id="<?=$value["id"]?>"><i class="fa fa-edit"></i></button>
    <button class="btn waves-effect waves-light btn-grd-danger btn-sm delete" data-id="<?=$value["id"]?>"><i class="fa fa-trash"></i></button>
</td>
                                      </tr>
  
                                      <?php
                                  }
                              } 
                          ?>
                      
                      </tbody>
                  </table>
                  
              </div>   

         



        </div>
    </div>
</div>
