<?php  include('header.php') ?>


    <?php  include('svg.php') ?>


    <?php  include('mobile_header.php') ?>


    <?php  include('top.php') ?>



    <main >
 
        <div class="boxings custome-<?=isset($_GET["type"])?$_GET["type"]:''?>">
            <?=isset($_GET["message"])?$_GET["message"]:''?>
        </div>

        <?=$content?>

        
    </main>

    <?php  include('loading.php') ?>
   



    <!-- Go To Top -->
    <div id="scrollTop" class="visually-hidden end-0"></div>

    <!-- Page Overlay -->
    <div class="page-overlay"></div><!-- /.page-overlay -->
    
        
    <!-- Modal start -->
    <div class="modal fade modalOpenCustom" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog " role="document">
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



    <?php
        if(isset($js)) {
            for ($i=0; $i < count($js) ; $i++) { 
                echo '<script src="'.$_ENV['BASE_PATH'].'/components/'.$js[$i].'"></script>';
            }
        }
    ?>
    </body>

</html>
