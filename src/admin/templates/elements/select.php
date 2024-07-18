<?php
    $element_id = isset($_POST['element_id']) ? $_POST['element_id'] : null;
    $elements = isset($_POST['elements']) ? $_POST['elements'] : null;
    $element_placeholder = isset($_POST['element_placeholder']) ? $_POST['element_placeholder'] : null;
    $element_event_listener = isset($_POST['element_event_listener']) ? $_POST['element_event_listener'] : null;
?>

<select class="form-control" id="<?php echo $element_id; ?>" style="width: 100%;" <?php echo $element_event_listener; ?> required>
    <option value="" selected><?php echo $element_placeholder; ?></option>         
<?php
    if($elements != null){
        foreach($elements as $element){
?> 
            <option value='<?php echo $element['id']; ?>'><?php echo $element['name']; ?></option>
<?php
        }
    }
?>
</select>
