<?php
session_start();
include("../../../../service/connection.php");
include("../common.php");

$conn = $connections['sicapepe']['conn'];

$month = $_POST['month'];
$year = $_POST['year'];

$db_search_table = "";
$db_search_fields = "";
$db_insert_fields = "";

$db_insert_table = "[dbo].[Procesos]
([ImputadoID]
,[NumeroAsuntoCausaPenal]
,[FechaIngresoCausaPenal]
,[CelebracionAudienciaInicial]
,[MotivoAudienciaInicial]
,[FechaAudienciaInicial]
,[CarpetaID])";

$db_insert_conditions = "YEAR(proce.FechaInicio) IN ($year) AND MONTH(proce.FechaInicio) IN ($month)";

$db_query = "SELECT proce.ImputadoID, proce.NumeroAsuntoCausaPenal, proce.FechaIngresoCausaPenal, proce.CelebracionAudienciaInicial, proce.MotivoAudienciaInicial, proce.FechaAudienciaInicial, proce.CarpetaID FROM
(SELECT
CONVERT(varchar(10), c.CarpetaID)+CONVERT(varchar(10), en.idEstatusNucs) AS 'ProcesoID',
i.ImputadoID,
j.causaPenal AS 'NumeroAsuntoCausaPenal',
CONVERT(date, j.fechaCausaPenal, 5) AS 'FechaIngresoCausaPenal',
j.audienciaInicial AS 'CelebracionAudienciaInicial',
j.motivoNoCelebracion AS 'MotivoAudienciaInicial',
CASE j.fechaAudienciaInicial
WHEN '1900-01-01' THEN NULL
ELSE CONVERT(date, j.fechaAudienciaInicial, 5) END AS 'FechaAudienciaInicial',
c.CarpetaID,
c.FechaInicio
FROM [PRUEBA].[dbo].[Carpeta] c 
INNER JOIN [PRUEBA].[dbo].[Involucrado] inv
ON c.CarpetaID = inv.InvolucradoID
INNER JOIN [PRUEBA].[dbo].[Imputado] i
ON inv.InvolucradoID = i.InvolucradoID
INNER JOIN [ESTADISTICAV2].[dbo].[estatusNucs] en 
ON c.NUC = en.nuc collate Modern_Spanish_CI_AI
INNER JOIN [ESTADISTICAV2].[dbo].[estatus] e
ON e.idEstatus = en.idEstatus
INNER JOIN [ESTADISTICAV2].[senap].[judicializadas] j
ON en.idEstatusNucs = j.idEstatusNucs) proce";



$data = (object) array(
	'user' => (object) array(
		'type' => 'number',
		'value' => null,
		'null' => false,
		'db_column' => null
	)
);


if(!isset($_SESSION['user_data']) && (!isset($_POST['month']) && !isset($_POST['year']))){
	echo json_encode(
		array(
			'state' => 'fail',
			'data' => null
		),
		JSON_FORCE_OBJECT
	);
}
else{

	$data->user->value = $_SESSION['user_data']['id'];
	$data->user->null = false;
	
	echo json_encode(
		executeDumpQuery2(
            (object) array(
                'data' => $data,
                'db_query_params' => (object) array(
                    'db_search_table' => $db_search_table,
                    'db_search_fields' => $db_search_fields,
                    'db_insert_table' => $db_insert_table,
                    'db_insert_fields' => $db_insert_fields,
                    'db_insert_conditions' => $db_insert_conditions,
					'db_query' => $db_query
                ),
                'db_connection_params' => (object) array(
                    'conn' => $conn,
                    'params' => $params,
                    'options' => $options
                )
            )
		), 
		JSON_FORCE_OBJECT
	);
}
?>

