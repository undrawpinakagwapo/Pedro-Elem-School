<option value="" >--SELECT--</option>
<?php 
    foreach ($list as $key => $value) {
        echo '<option value="'.$value["id"].'" >'.$value["name"].'</option>';
    }

?>