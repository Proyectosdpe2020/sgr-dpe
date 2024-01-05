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

$db_insert_table = "[dbo].[Sobreseimientos]
([EtapaDictoSobreseimiento]
,[CausaSobreseimiento]
,[FechDictoSobreseimiento]
,[TipoSobreseimiento]
,[CarpetaID],[ProcesoID])";

$db_insert_conditions = "YEAR(sobres.FechaInicio) IN ($year) AND MONTH(sobres.FechaInicio) IN ($month)";

$db_query = "SELECT sobres.EtapaDictoSobreseimiento, sobres.CausaSobreseimiento, sobres.FechDictoSobreseimiento, sobres.TipoSobreseimiento, sobres.CarpetaID, proces.ProcesoID
FROM
(SELECT s.[idSobreseimientos]
,1 AS 'EtapaDictoSobreseimiento'
,CASE en.idEstatus
WHEN 89 THEN 1
WHEN 90 THEN 2
WHEN 91 THEN 3
WHEN 93 THEN 4
WHEN 99 THEN 5
ELSE NULL
END AS 'CausaSobreseimiento'
,s.[fechaDictoSobreseimiento] AS 'FechDictoSobreseimiento'
,CASE 
WHEN s.[idTipoSobreseimiento] IS NULL THEN 3
ELSE s.[idTipoSobreseimiento]
END AS 'TipoSobreseimiento'
,c.CarpetaID
,c.FechaInicio
FROM [PRUEBA].[dbo].[Carpeta] c 
INNER JOIN [ESTADISTICAV2].[dbo].[estatusNucs] en 
ON c.NUC = en.nuc collate Modern_Spanish_CI_AI
LEFT JOIN [ESTADISTICAV2].[senap].[sobreseimientos] s
ON s.idEstatusNucs = en.idEstatusNucs
WHERE en.idEstatus IN(89, 90, 91, 93, 99)) sobres

LEFT JOIN dbo.Procesos proces ON proces.CarpetaID = sobres.CarpetaID";



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

