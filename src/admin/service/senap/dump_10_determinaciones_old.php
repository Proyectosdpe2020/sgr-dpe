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

$db_insert_table = "[dbo].[Determinaciones]
([CarpetaID]
,[EstatusCarpeta]
,[SentidoDeterminacion]
,[AcuerdosReparatorios]
,[ReactivacionCarpeta]
,[FechaDeterminacion]
,[TipoCriteriosOportunidad]
,[ejercicios_id])";

$db_insert_conditions = "YEAR(deter.FechaInicio) IN ($year) AND MONTH(deter.FechaInicio) IN ($month) AND deter.SentidoDeterminacion NOT IN (0)";

$db_query = "SELECT deter.CarpetaID, deter.EstatusCarpeta, deter.SentidoDeterminacion, deter.AcuerdosReparatorios, deter.ReactivacionCarpeta, deter.FechaDeterminacion, deter.TipoCriteriosOportunidad, deter.id

FROM
(
SELECT c.CarpetaID,
cd.CatEstatusCarpetaID AS 'EstatusCarpeta', c.id,
CASE 
WHEN r.idEstatus IN (22) AND se.idSentencia IS NULL THEN 0
ELSE cd.CatDeterminacionID
END AS 'SentidoDeterminacion',
ar.idTipoAcuerdoReparatorio AS 'AcuerdosReparatorios',
NULL AS 'ReactivacionCarpeta',
r.fecha AS 'FechaDeterminacion',
co.idTipoCriterioOportunidad AS 'TipoCriteriosOportunidad',
c.FechaInicio
FROM [EJERCICIOS2].[dbo].[Carpetas] c 
LEFT JOIN [ESTADISTICAV2].[dbo].[estatusNucsCarpetas] r
ON c.CarpetaID = r.idCarpeta
LEFT JOIN [ESTADISTICAV2].[dbo].[estatusNucs] en
ON c.NUC = en.nuc collate Modern_Spanish_CI_AI
LEFT JOIN [ESTADISTICAV2].[senap].[acuerdosReparatorios] ar
ON ar.idEstatusNucs = en.idEstatusNucs
LEFT JOIN [ESTADISTICAV2].[senap].[criteriosOportunidad] co
ON en.idEstatusNucs = co.idEstatusNucs
INNER JOIN CatDeterminaciones cd 
ON r.idEstatus = cd.CatEstatusResolucionesID
LEFT JOIN [ESTADISTICAV2].[senap].[sentencias] se
ON en.idEstatusNucs = se.idEstatusNucs
) deter";



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

