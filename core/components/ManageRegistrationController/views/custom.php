<style>
    .modal-xl { 
        width: 1300px;
    }
        
    .preview-img {
        width: 100%;
        height: 200px;
        object-fit: cover;
        border: 2px dashed #ccc;
        display: block;
        margin-top: 10px;
    }
</style>

<div class="col-xl-12 col-md-12">
    <button class="btn waves-effect waves-light btn-primary openmodaldetails-modal" data-type="add"><i class="fa fa-plus"></i>&nbsp;Add Student</button>
    <!-- <button class="btn waves-effect waves-light btn-danger importsutdentmodal" data-type="import"><i class="fa fa-download"></i>&nbsp;Import Students</button> -->
    <div class="card table-card">
        <div class="card-header">
            <h5>Manage Registrar</h5>
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
                    <table class="table table-hover"  id="mainTable"> 
                        <thead>
                        <tr>
                            <th>No.</th>
                            <th>School Year</th>
                            <th>Grade Level & Section</th>
                            <th>LRN</th>
                            <th>Student Name</th>
                            <th>Added Date</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                              if(count($list) > 0){
                                $i = 1;
                                  foreach ($list as $key => $value) {
                                      ?>
                                      <tr>
                                          <td><?=$i?></td>
                                          <td><?=$value["school_year"]?></td>
                                          <td><?=$value["section_level"]?></td>
                                          <td><?=$value["LRN"]?></td>
                                          <td><?=$value["full_name"]?></td>
                                          <td><?=$value["created_at"]?></td>
                                          <td>
                                              <button class="btn waves-effect waves-light btn-grd-primary btn-sm openmodaldetails-modal" data-type="edit" data-id="<?=$value["id"]?>"><i class="fa fa-edit " ></i></button>
                                          </td>
                                      </tr>
  
                                      <?php
                                      $i++;
                                  }
                              } else {
                                  ?>
                                  <tr>
                                      <td > NO RECORD FOUND!</td>
                                      <td></td>
                                      <td></td>
                                      <td></td>
                                      <td></td>
                                      <td></td>
                                      <td></td>

                                  </tr>

                                  <?php
                              }
                          ?>   
                        
                        </tbody>
                    </table>
                   
                </div>   



        </div>
    </div>
</div>
