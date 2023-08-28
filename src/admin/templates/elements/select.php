<?php
    $element_id = $_POST['element_id'];
    if(isset( $_POST['elements']))
        $elements = $_POST['elements'];
    else
        $elements = null;
    $element_placeholder = $_POST['element_placeholder'];
    $element_event_listener = $_POST['element_event_listener'];
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
