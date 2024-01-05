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

$db_insert_table = "[dbo].[SuspensionCondicional]
([DictoSuspencionCondicionalProceso]
,[FechaDictoSuspencionCondicionalProceso]
,[EtapaSuspensionCondicionalProceso]
,[TipoCondicionesSuspensionCondicionalProceso]
,[ReaperturaProceso]
,[FechaReaperturaProceso]
,[FechaCumplimentoSuspensionCondicionalProceso]
,[CarpetaID],[ProcesoID])";

$db_insert_conditions = "YEAR(susco.FechaInicio) IN ($year) AND MONTH(susco.FechaInicio) IN ($month)";

$db_query = "SELECT susco.DictoSuspencionCondicionalProceso, susco.FechaDictoSuspencionCondicionalProceso, 
susco.EtapaSuspensionCondicionalProceso, susco.TipoCondicionesSuspensionCondicionalProceso,
susco.ReaperturaProceso, susco.FechaReaperturaProceso, susco.FechaCumplimentoSuspensionCondicionalProceso, susco.CarpetaID, proces.ProcesoID
FROM
(SELECT 
sc.[idEstatusNucs]
,'1' AS 'DictoSuspencionCondicionalProceso'
,sc.[fechaDictoSuspCondProc] AS 'FechaDictoSuspencionCondicionalProceso'
,CASE sc.[idEtapaSuspCondProc] 
WHEN 0 THEN 3
ELSE sc.[idEtapaSuspCondProc]
END AS 'EtapaSuspensionCondicionalProceso'
,CASE sc.[idTipoCondImpuSuspConProc] 
WHEN 0 THEN 15
ELSE sc.[idTipoCondImpuSuspConProc] 
END AS 'TipoCondicionesSuspensionCondicionalProceso'
,CASE sc.[reaperturaProc]
WHEN 0 THEN 0
WHEN 1 THEN 1
WHEN 2 THEN 0
WHEN 3 THEN 0
ELSE 0
END AS 'ReaperturaProceso'
,CASE sc.[fechaReaperProc]
WHEN '1900-01-01' THEN NULL
ELSE sc.[fechaReaperProc]
END AS 'FechaReaperturaProceso'
,CASE sc.[fechaCumplimentoSuspCondPro]
WHEN '1900-01-01' THEN NULL
ELSE sc.[fechaCumplimentoSuspCondPro]
END AS 'FechaCumplimentoSuspensionCondicionalProceso',
c.CarpetaID,
c.FechaInicio
FROM [PRUEBA].[dbo].[Carpeta] c 
INNER JOIN [ESTADISTICAV2].[dbo].[estatusNucs] en 
ON c.NUC = en.nuc collate Modern_Spanish_CI_AI
INNER JOIN [ESTADISTICAV2].[senap].[suspCondProc] sc
ON sc.idEstatusNucs = en.idEstatusNucs) susco

LEFT JOIN dbo.Procesos proces ON proces.CarpetaID = susco.CarpetaID";



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

