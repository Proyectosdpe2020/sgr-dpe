<?php
    $element_id = $_POST['element_id'];
    if(isset( $_POST['elements']))
        $elements = $_POST['elements'];
    else
        $elements = null;
    $element_placeholder = $_POST['element_placeholder'];
    $element_event_listener = $_POST['element_event_listener'];
?>

<div id="<?php echo $element_id; ?>" class="multiselect button-group">
    <button type="button" id="comboCheck" class="form-control dropdown-toggle" data-toggle="dropdown"><div><div class="dropdown-label"><?php echo $element_placeholder; ?></div><span class="caret"></span></div></button>

    <ul class="dropdown-menu">
     
<?php
    if($elements != null){
        foreach($elements as $element){
?> 

            <li><a id="<?php echo $element['id']; ?>" data-value="<?php echo $element['id']; ?>" name="<?php echo $element['id']; ?>" tabIndex="<?php echo $element['id']; ?>"><input type="checkbox" class="multiselect-element">&nbsp; <?php echo $element['name']; ?></label></a></li>
<?php
        }
    }
?>
    </ul>
</div>