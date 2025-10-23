<?php  include('header.php') ?>
    <?php  include('loading.php') ?>
    <!-- Pre-loader end -->
    <div id="pcoded" class="pcoded">
        <div class="pcoded-overlay-box"></div>
        <div class="pcoded-container navbar-wrapper">

            <?php include('top.php'); ?>

            <div class="pcoded-main-container">
                
                <div class="pcoded-wrapper">
                    

                    <?php 

                    $const_display_component_NAME = isset(getDetailsSideBarActive()["Title"]) ? getDetailsSideBarActive()["Title"] : '';
                    $const_display_component_LABEL = isset(getDetailsSideBarActive()["Description"]) ? getDetailsSideBarActive()["Description"] : '';
                    $const_display_component_ICON = isset(getDetailsSideBarActive()["icon"]) ? getDetailsSideBarActive()["icon"] : '';
                    
                    include('sidebar.php');
                    ?>

                    <div class="pcoded-content">
                        <!-- Page-header start -->
                        <!-- <div class="page-header">
                            <div class="page-block">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <div class="page-header-title">
                                            <h5 class="m-b-10"> <i class="ti-<?=$const_display_component_ICON;?>"></i> <?=$const_display_component_NAME;?></h5>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                        </div> -->
                        <!-- Page-header end -->
                        <div class="pcoded-inner-content">
                            <!-- Main-body start -->
                            <div class="main-body">
                                <div class="page-wrapper">
                                    <!-- Page-body start -->
                                    <div class="page-body">
                                        <div class="row">

                                                <div class="boxings custome-<?=isset($_GET["type"])?$_GET["type"]:''?>">
                                                    <?=isset($_GET["message"])?$_GET["message"]:''?>
                                                </div>

                                            <?=$content?>
                                            
                                        </div>
                                    </div>
                                    <!-- Page-body end -->
                                </div>
                                <div id="styleSelector"> </div>
                            </div>
                        </div>


                        
                    </div> <!-- end pcoded-content -->
                </div> <!-- end pcoded-main-container -->
            </div>
        </div>
    </div>



        
    <!-- Modal start -->
    <div class="modal fade modalOpenCustom" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <form action="" method="POST" id="general-form"  enctype="multipart/form-data">
                    <div class="modal-header">
                        <h4 class="modal-title" id="myModalLabel"></h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        
                    </div>
                    <div class="modal-footer">

                    </div>
                </form>
            </div>
        </div>
    </div>
 
    
    <?php include('footer.php'); ?>
    <script>
        var maintable = $('#mainTable');

        if(typeof maintable != 'undefined') {
            maintable.DataTable();
        }

    </script>
    <?php
        if(isset($js)) {
            for ($i=0; $i < count($js) ; $i++) { 
                echo '<script src="'.$_ENV['BASE_PATH'].'/components/'.$js[$i].'"></script>';
            }
        }
    ?>

  
</body>

</html>
