<?php
session_start();
include("../../../../service/connection.php");
include("../common.php");

$conn = $connections['sicap']['conn'];

$month = $_POST['month'];
$year = $_POST['year'];

$db_search_table = "";
$db_search_fields = "";
$db_insert_fields = "";

$db_insert_table = "[dbo].[Procesos]
([NumeroAsuntoCausaPenal]
,[FechaIngresoCausaPenal]
,[CelebracionAudienciaInicial]
,[MotivoAudienciaInicial]
,[FechaAudienciaInicial]
,[CarpetaID]
,[ejercicios_id])";

//$db_insert_conditions = "YEAR(proce.FechaInicio) IN ($year) AND MONTH(proce.FechaInicio) IN ($month)";

$db_insert_conditions = "( year(FechaInicio) = 2023 or (year(FechaInicio) = 2024 and month(FechaInicio) <= 6) )";

$db_query = "SELECT proce.NumeroAsuntoCausaPenal, proce.FechaIngresoCausaPenal, proce.CelebracionAudienciaInicial, proce.MotivoAudienciaInicial, proce.FechaAudienciaInicial, proce.CarpetaID, proce.ejercicios_id FROM
(SELECT
CONVERT(varchar(10), c.CarpetaID)+CONVERT(varchar(10), en.idEstatusNucs) AS 'ProcesoID',
j.causaPenal AS 'NumeroAsuntoCausaPenal',
CONVERT(date, j.fechaCausaPenal, 5) AS 'FechaIngresoCausaPenal',
j.audienciaInicial AS 'CelebracionAudienciaInicial',
CASE WHEN cma.CatMotivoAudienciaInicialID IS NOT NULL
THEN cma.CatMotivoAudienciaInicialID
ELSE 9 END AS 'MotivoAudienciaInicial',
CASE j.fechaAudienciaInicial
WHEN '1900-01-01' THEN NULL
ELSE CONVERT(date, j.fechaAudienciaInicial, 5) END AS 'FechaAudienciaInicial',
c.CarpetaID,
c.FechaInicio,
c.id as 'ejercicios_id'
FROM [EJERCICIOS2].[dbo].[Carpetas] c 
INNER JOIN [ESTADISTICAV2].[dbo].[estatusNucs] en 
ON c.NUC = en.nuc collate Modern_Spanish_CI_AI
INNER JOIN [ESTADISTICAV2].[senap].[judicializadas] j
ON en.idEstatusNucs = j.idEstatusNucs
LEFT JOIN CatMotivosAudienciaInicial cma
ON cma.CatMotivoAudienciaInicialID = j.motivoNoCelebracion) proce";



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

