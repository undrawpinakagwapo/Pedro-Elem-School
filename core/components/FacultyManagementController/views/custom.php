<style>
    .modal-xl { 
        width: 1140px;
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
    <button class="btn waves-effect waves-light btn-primary openmodaldetails-modal" data-type="add"><i class="fa fa-plus"></i>&nbsp;Add Faculty</button>
    <div class="card table-card">
        <div class="card-header">
            <h5>Manage Faculty</h5>
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
                            <th>Employee ID</th>
                            <th>Employee Name</th>
                            <th>Email</th>
                            <th>Contact NO.</th>

                            <th>Date Registered</th>
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
                                            <td><?=$value["emp_id"]?></td>
                                            <td><?=$value["account_first_name"].' '.$value["account_middle_name"].' '.$value["account_last_name"]?></td>
                                            <td><?=$value["email"]?></td>
                                            <td><?=$value["contact_no"]?></td>
                                            <td><?=$value["created_at"]?></td>
                                            <td><?=$status[$value["status"]]?></td>
                                            <td>
                                                <button class="btn waves-effect waves-light btn-grd-primary btn-sm openmodaldetails-modal" data-type="edit" data-id="<?=$value["user_id"]?>"><i class="fa fa-edit " ></i></button>
                                            </td>
                                        </tr>
    
                                        <?php
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
