<?php
    $element_id = isset($_POST['element_id']) ? 'id="'.$_POST['element_id'].'"' : null;
    $element_type = isset($_POST['element_type']) ? 'btn-outline-'.$_POST['element_type'] : null;
    $element_placeholder = isset($_POST['element_placeholder']) ? $_POST['element_placeholder'] : null;
    $element_event_listener = isset($_POST['element_event_listener']) ? $_POST['element_event_listener'] : null;
    
    if($element_placeholder != null){
?>
    <button type="button" class="btn rounded-button <?php echo $element_type; ?>" <?php echo $element_id; ?> <?php echo $element_event_listener; ?>><?php echo $element_placeholder; ?></button>
<?php
    }
?>