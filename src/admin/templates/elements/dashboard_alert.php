<?php
    $message = isset($_POST['message']) ? $_POST['message'] : null;
    $type = isset($_POST['type']) ? $_POST['type'] : null;

    if($message != null){
?>
        <div class="alert alert-<?php echo $type;?>" role="alert">
            <?php echo $message; ?>
        </div>
<?php
    }
?>