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

$db_insert_table = "[dbo].[MedidasCautelares]
([TipoMedidaCautelar]
,[CarpetaID],[ProcesoID])";

$db_insert_conditions = "YEAR(medc.FechaInicio) IN ($year) AND MONTH(medc.FechaInicio) IN ($month)";

$db_query = "SELECT medc.CatTipoMedidaCautelarID, medc.CarpetaID, proces.ProcesoID
FROM (SELECT 
'MEDC'+CONVERT(varchar(10), en.idEstatusNucs) AS 'MedidaCautelarID',
CASE en.idEstatus
WHEN 17 THEN 1
WHEN 18 THEN 2
WHEN 20 THEN 3
WHEN 21 THEN 4
WHEN 22 THEN 5
WHEN 23 THEN 6
WHEN 24 THEN 7
WHEN 25 THEN 8
WHEN 26 THEN 9
WHEN 27 THEN 10
WHEN 28 THEN 11
WHEN 29 THEN 12
WHEN 30 THEN 13
WHEN 31 THEN 14
WHEN 95 THEN 15
ELSE 16
END AS 'CatTipoMedidaCautelarID',
c.CarpetaID,
c.FechaInicio
FROM [PRUEBA].[dbo].[Carpeta] c 
INNER JOIN [ESTADISTICAV2].[dbo].[estatusNucs] en 
ON c.NUC = en.nuc collate Modern_Spanish_CI_AI
WHERE idEstatus IN (17, 18, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 95) ) medc

LEFT JOIN dbo.Procesos proces ON proces.CarpetaID = medc.CarpetaID";



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

