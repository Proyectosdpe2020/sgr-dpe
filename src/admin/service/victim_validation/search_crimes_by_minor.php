<?php
session_start();
include("../../../../service/connection.php");
include("../common.php");

$conn = $connections['incidencia_sicap']['conn'];
$year_month = isset($_POST['year_month']) ? $_POST['year_month'] : null;

if($conn && $year_month != null){

    $year = date('Y', strtotime($year_month));
    $month = date('m', strtotime($year_month));

    $sql = "SELECT DISTINCT [VictimaID] AS 'id'
	                ,v.[Nombre]
                    ,[Paterno]
                    ,[Materno]
                    ,[Edad]
                    ,[Grupo] AS 'DelitoAgrupado'
                    ,[NUC]
                FROM dbo.Victimas v 
                    INNER JOIN dbo.Carpetas c ON c.CarpetaID = v.CarpetaID
					INNER JOIN dbo.AgrupacionDelito ad ON ad.CatModalidadesEstadisticasID = v.CatModalidadesEstadisticasID
                        WHERE year(FechaInicio) = $year
                        AND month(FechaInicio) = $month
                        AND Victima = 1
                        AND Contar = 1
                        AND Edad BETWEEN -11 AND 17 AND Edad != 0";

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