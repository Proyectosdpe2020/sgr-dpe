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
	                ,[Nombre]
                    ,[Paterno]
                    ,[Materno]
                    ,[Sexo]
                    ,[Grupo] AS 'DelitoAgrupado'
                    ,[NUC]
                FROM dbo.Victimas v 
                    INNER JOIN dbo.Carpetas c ON c.CarpetaID = v.CarpetaID
                    INNER JOIN dbo.AgrupacionDelito ad ON ad.CatModalidadesEstadisticasID = v.CatModalidadesEstadisticasID
                        WHERE year(FechaInicio) = $year
                        AND month(FechaInicio) = $month
                        AND Victima = 1
                        AND Contar = 1
                        AND Sexo = 3
                        AND Grupo IN ('Acoso Sexual','Abuso sexual','Estupro',
                            'Lesiones culposas','Hostigamiento sexual','Incumplimiento de la obligación alimentaria',
                            'Violación en grado de tentativa','Violencia digital a la intimidad sexual',
                            'Violencia vicaria','Lesiones dolosas','Secuestro','Violación','Violencia familiar',
                            'Robo a bancos','Robo a caja de ahorro','Robo a casa habitación',
                            'Robo a chofer de autobus','Robo a comercios','Robo a cuentahabiente',
                            'Robo a Embarcaciones Pequeñas y Grandes','Robo a escuelas',
                            'Robo a gasolineras','Robo a industria','Robo a institución bancaria (a cajero atm)',
                            'Robo a institución religiosa','Robo a interior de vehículo',
                            'Robo a oficinas','Robo a talleres','Robo a transeúnte','Lesiones dolosas en razón de parentesco',
                            'Robo a transporte público (colectivo camión)','Robo a transporte público (combi)',
                            'Robo a transporte público (taxi)','Robo a transportista','Robo a vehículos',
                            'Robo calificado','Robo de autopartes','Robo de cobre','Robo de documentos',
                            'Robo de hidrocarburos','Robo de motocicleta','Robo de remolque','Robo de Tractores',
                            'Robo de uso','Robo de vehículo','Robo de vehículos (maquinaria)','Robo en carretera',
                            'Robo en grado de tentativa','Robo en transporte individual','Robo en transporte público colectivo',
                            'Robo entre ascendientes y descendientes','Robo entre cónyugues','Robo equiparado','Robo simple')";

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