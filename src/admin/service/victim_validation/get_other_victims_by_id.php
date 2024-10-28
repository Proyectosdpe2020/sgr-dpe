<?php
session_start();
include("../../../../service/connection.php");
include("../common.php");

$conn = $connections['incidencia_sicap']['conn'];
$id = isset($_POST['id']) ? $_POST['id'] : null;
$cid = isset($_POST['cid']) ? $_POST['cid'] : null;

if($conn && $id != null && $cid != null){

    $sql = "SELECT DISTINCT [VictimaID] AS 'id'
	                ,[Nombre]
                    ,[Paterno]
                    ,[Materno]
                    ,[Edad]
                    ,[Sexo]
                    ,[NUC]
                FROM dbo.Victimas v 
                    INNER JOIN dbo.Carpetas c ON c.CarpetaID = v.CarpetaID
                        WHERE c.CarpetaID = $cid AND VictimaID != $id AND Victima = 1";

    $return = getGenericData(
        (object) array(
            'conn' => $conn,
            'sql' => $sql,
            'params' => array(),
            'options' => array("Scrollable" => SQLSRV_CURSOR_KEYSET)
        )
    );

    echo json_encode($return, JSON_FORCE_OBJECT);

    sqlsrv_close($conn);
}
else{
    $return = array(
        'state' => 'fail',
        'data' => null
    );

    echo json_encode($return, JSON_FORCE_OBJECT);
}
?>