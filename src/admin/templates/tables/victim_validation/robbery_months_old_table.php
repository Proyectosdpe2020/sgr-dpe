<?php
    session_start();
    $data = isset($_POST['data']) ? $_POST['data'] : null;
    $search_op = isset($_POST['search_op']) ? $_POST['search_op'] : null;
?>
<div class="table-records-header-text">Robos a víctimas de meses de edad</div>

<table class="data-table table table-striped overflow-table">
    <thead>
        <tr>
            <th>#</th>
            <th>NUC</th>
            <th>Nombre</th>
            <th>Paterno</th>
            <th>Materno</th>
            <th>Delito Agrupado</th>
            <th>Edad</th>
        </tr>
    </thead>
    <tbody>
<?php
    if($data != null){
        $i=1;
        foreach(json_decode($data, true) as $element){
?> 
        <tr id="<?php echo 'robbery-months-old-row-'.$element['id']; ?>">
            <td class="center-text"><?php echo $i; ?></td>
            <td class="bold-text center-text"><?php echo $element['NUC']; ?></td>
            <td class="center-text"><?php echo $element['Nombre']; ?></td>
            <td class="center-text"><?php echo $element['Paterno']; ?></td>
            <td class="center-text"><?php echo $element['Materno']; ?></td>
            <td class="bold-text"><?php echo $element['DelitoAgrupado']; ?></td>
            <td>
                <div><?php echo $element['Edad']; ?></div>
                <input class="input-custom-cell" type="number" id="<?php echo 'age-'.$element['id']; ?>" name="age" size="4" min="-9" max="150" value="<?php echo $element['Edad']; ?>" onchange="onchangeElementTable({<?php echo 'id: '.$element['id'].', search_op: `'.$search_op.'`'; ?>})">
            </td>
        </tr>
<?php
            $i++;
        }
    }
    else{
?> 
        <tr>
            <td colspan="12" style="text-align: center; padding: 7px;">
                No hay registros
            </td>
        </tr>
<?php
    }
?>
    </tbody>
</table>