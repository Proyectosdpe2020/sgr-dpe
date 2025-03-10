<?php
session_start();
include("../../../../service/connection.php");
include('../../../../service/validate_injection_string.php');
include("../common.php");

$conn = $connections['incidencia_sicap']['conn'];
$to_update = isset($_POST['to_update']) ? $_POST['to_update'] : null;

if($conn && $to_update != null){

    $to_update_elements = json_decode($to_update, true);
    $updated_count = 0;
    $to_update_count = count($to_update_elements);

    foreach($to_update_elements as $element){

        $obj_elem = (object) $element;
        $gener = cleanTextInjec($obj_elem->gener);
        $name = cleanTextInjec($obj_elem->name);
        $ap = cleanTextInjec($obj_elem->ap);
        $am = cleanTextInjec($obj_elem->am);
        $id = cleanTextInjec($obj_elem->id);

        $sql = "UPDATE [EJERCICIOS2].[dbo].[Victimas]
                    SET [Sexo] = $gener,
                        [Nombre] = '$name',
                        [Paterno] = '$ap',
                        [Materno] = '$am'
                    WHERE VictimaID = $id";
                    
        $return = updateGenericData(
            (object) array(
                'conn' => $conn,
                'sql' => $sql,
                'params' => array(),
                'options' => array("Scrollable" => SQLSRV_CURSOR_KEYSET)
            )
        );

        if($return['state'] == 'success'){
            $updated_count++;
        }
    }

    $return = $updated_count != 0 ? array(
        'state' =>'success',
        'data' => array(
            'to_update_count' => $to_update_count,
            'updated_count' => $updated_count
        )
    ) : array(
        'state' => 'fail',
        'data' => null
    );
}
else{
    $return = array(
        'state' => 'fail',
        'data' => null
    );
}
echo json_encode($return, JSON_FORCE_OBJECT);
sqlsrv_close($conn);
?>