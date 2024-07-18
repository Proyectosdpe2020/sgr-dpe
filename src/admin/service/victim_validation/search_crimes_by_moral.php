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
                    ,[Sexo]
                    ,[Grupo] AS 'DelitoAgrupado'
                    ,[Edad]
                    ,[NUC]
                FROM dbo.Victimas v 
                    INNER JOIN dbo.Carpetas c ON c.CarpetaID = v.CarpetaID
					INNER JOIN dbo.AgrupacionDelito ad ON ad.CatModalidadesEstadisticasID = v.CatModalidadesEstadisticasID
                        WHERE year(FechaInicio) = $year
                        AND month(FechaInicio) = $month
                        AND Victima = 1
                        AND Contar = 1
                        AND Sexo = 0
                        AND Grupo IN ('Acoso Sexual','Abuso sexual','Amenazas','Lesiones culposas',
                            'Lesiones dolosas','Homicidio culposo','Homicidio doloso','Secuestro',
                            'Lesiones dolosas en razón de parentesco','Aborto','Aborto en grado de tentativa',
                            'Desaparición cometida por particulares','Desaparición forzada de personas',
                            'Estupro','Feminicidio','Hostigamiento sexual','Incumplimiento de la obligación alimentaria',
                            'Lesiones en grado de tentativa','Privación de la libertad personal',
                            'Privación de la libertad personal en grado de tentativa','Secuestro en grado de tentativa',
                            'Violación','Violación en grado de tentativa','Violencia digital a la intimidad sexual',
                            'Violencia familiar','Violencia vicaria')";

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