<?php
    session_start();
    
    $cid = isset($_POST['cid']) ? $_POST['cid'] : null;
    $nuc = isset($_POST['nuc']) ? $_POST['nuc'] : null;
    $data = isset($_POST['victims']) ? $_POST['victims'] : null;
    
?>

<div class="modal fade bd-example-modal-lg" id="large-modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">

    <div class="modal-dialog" id="large-modal-dialog">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title" id="exampleModalLabel">Carpeta: <?php echo "$nuc"; ?></h5>

            </div>

            <div class="modal-body">

                <table class="data-table table table-striped overflow-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>NUC</th>
                            <th>Nombre</th>
                            <th>Sexo</th>
                            <th>Edad</th>
                        </tr>
                    </thead>
                    <tbody>
<?php
                    if($data != null){
                        $i=1;
                        foreach(json_decode($data, true) as $element){
?> 
                        <tr>
                            <td class="center-text"><?php echo $i; ?></td>
                            <td class="bold-text center-text"><?php echo $element['NUC']; ?></td>
                            <td class="bold-text center-text">
                                <?php echo $element['Nombre'].' '.$element['Paterno'].' '.$element['Materno']; ?>
                            </td>
                            <td>
                                <?php echo $element['Sexo'] == 1 ? 'Masculino' : 
                                ($element['Sexo'] == 2 ? 'Femenino' : 
                                ($element['Sexo'] == 3 ? 'Moral' : 
                                'Desconocido'));?>
                            </td>
                            <td class="bold-text center-text">
                                <?php echo $element['Edad']; ?>
                            </td>
                        </tr>
<?php
                            $i++;
                        }
                    }
                    else{
?> 
                        <tr>
                            <td colspan="12" class="bold-text center-text" style="padding: 7px;">
                                No hay más víctimas en la carpeta
                            </td>
                        </tr>
                <?php
                    }
                ?>
                    </tbody>
                </table>
            </div>
	

            <div class="modal-footer">
                
                <button type="button" class="btn rounded-button btn-outline-danger" data-dismiss="modal">Cerrar</button>

            </div>
        
        </div>

    </div>

</div>
