<?php
    session_start();
    $data = isset($_POST['data']) ? $_POST['data'] : null;
    $search_op = isset($_POST['search_op']) ? $_POST['search_op'] : null;
?>
<div class="table-records-header-text">Edad y sexo desconocidos</div>

<table class="data-table table table-striped overflow-table">
    <thead>
        <tr>
            <th>#</th>
            <th>NUC</th>
            <th>Nombre</th>
            <th>Paterno</th>
            <th>Materno</th>
            <th>Sexo</th>
            <th>Edad</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
<?php
    if($data != null){
        $i=1;
        foreach(json_decode($data, true) as $element){
?> 
        <tr id="<?php echo 'unknown-age-row-'.$element['id']; ?>">
            <td class="center-text"><?php echo $i; ?></td>
            <td class="bold-text center-text"><?php echo $element['NUC']; ?></td>
            <td class="bold-text center-text">
                <div><?php echo $element['Nombre']; ?></div>
                <input class="input-custom-cell" type="text" id="<?php echo 'name-'.$element['id']; ?>" name="name" value="<?php echo $element['Nombre']; ?>" onchange="onchangeElementTable({<?php echo 'id: '.$element['id'].', search_op: `'.$search_op.'`'; ?>})">
            </td>
            <td class="bold-text center-text">
                <div><?php echo $element['Paterno']; ?></div>
                <input class="input-custom-cell" type="text" id="<?php echo 'ap-'.$element['id']; ?>" name="ap" value="<?php echo $element['Paterno']; ?>" onchange="onchangeElementTable({<?php echo 'id: '.$element['id'].', search_op: `'.$search_op.'`'; ?>})">
            </td>
            <td class="bold-text center-text">
                <div><?php echo $element['Materno']; ?></div>
                <input class="input-custom-cell" type="text" id="<?php echo 'am-'.$element['id']; ?>" name="am" value="<?php echo $element['Materno']; ?>" onchange="onchangeElementTable({<?php echo 'id: '.$element['id'].', search_op: `'.$search_op.'`'; ?>})">
            </td>
            <td>
                <div><?php echo $element['Sexo']; ?></div>
                <select id="<?php echo 'gener-'.$element['id']; ?>" class="form-control" required="true" onchange="onchangeElementTable({<?php echo 'id: '.$element['id'].', search_op: `'.$search_op.'`'; ?>})">
                    <option value="1" <?php echo $element['Sexo'] == 1 ? 'selected' : '';?>>Masculino</option>
                    <option value="2" <?php echo $element['Sexo'] == 2 ? 'selected' : '';?>>Femenino</option>
                    <option value="3" <?php echo $element['Sexo'] == 3 ? 'selected' : '';?>>Desconocido</option>
                    <option value="0" <?php echo $element['Sexo'] == 0 ? 'selected' : '';?>>Moral</option>
                </select>
            </td>
            <td class="bold-text center-text">
                <div><?php echo $element['Edad']; ?></div>
                <input class="input-custom-cell" type="number" id="<?php echo 'age-'.$element['id']; ?>" name="age" size="4" min="-9" max="150" value="<?php echo $element['Edad']; ?>" onchange="onchangeElementTable({<?php echo 'id: '.$element['id'].', search_op: `'.$search_op.'`'; ?>})">
            </td>
            <td class="center-text">
                <span onclick="showOtherVictims({<?php echo 'id: '.$element['id'].', cid: '.$element['cid'].', nuc: '.$element['NUC'].''; ?>})" data-toggle="tooltip" data-placement="top" style="cursor:pointer; display: inline; color: lightseagreen;" title="Víctimas" class="fa fa-users fa-lg" aria-hidden="true"></span>
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