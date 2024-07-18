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
        $age = cleanTextInjec($obj_elem->age);
        $id = cleanTextInjec($obj_elem->id);

        $sql = "UPDATE [EJERCICIOS2].[dbo].[Victimas]
                    SET [Edad] = $age
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