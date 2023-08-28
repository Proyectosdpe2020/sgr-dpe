<?php
    if(isset( $_POST['data']))
        $data = $_POST['data'];
    else
        $data = 'null';

    if(isset( $_POST['table_id']))
        $table_id = $_POST['table_id'];
    else
        $table_id = 'null';

    if(isset( $_POST['section_title']))
        $section_title = $_POST['section_title'];
    else
        $section_title = '';
?>

<h1><?php echo $section_title; ?></h1>

<?php
    if($data != 'null' && $table_id != 'null'){

?>

<table class="data-table table table-striped" id="<?php echo $table_id; ?>">
    <thead>
        <tr>
            <th>#</th>

<?php
        foreach(json_decode($data, true) as $element){
            foreach(array_keys($element) as $key){
?>
            <th><?php echo $key; ?></th>
<?php
            }
            break;
        }
?>

        </tr>
    </thead>
    <tbody>
<?php
        $i=1;
        foreach(json_decode($data, true) as $element){
?> 
        <tr>
            <td><?php echo $i; ?></td>
<?php
            foreach($element as $sub){
?>
            <td><?php echo $sub; ?></td>
<?php
            }
?>
        </tr>
<?php
            $i++;
        }
?>
    </tbody>
</table>

<?php

    }
    else{
?>
    <h2>No hay registros</h2>
<?php

    }
?>
