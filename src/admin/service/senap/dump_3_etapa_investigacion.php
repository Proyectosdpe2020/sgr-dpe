<?php
session_start();
include("../../../../service/connection.php");
include("../common.php");

$conn = $connections['sicap']['conn'];

$month = $_POST['month'];
$year = $_POST['year'];

$db_search_table = "[PRUEBA].[dbo].[Carpeta] c LEFT JOIN 
(
SELECT DISTINCT [CarpetaID], 1 AS 'Intermedia'
FROM [PRUEBA].[dbo].EtapaIntermedia
) ei 
ON c.CarpetaID = ei.CarpetaID
  LEFT JOIN 
(
SELECT DISTINCT [CarpetaID],
1 AS 'Complementaria'
FROM [PRUEBA].[dbo].[InvestigacionComplementaria]
) ic 
ON c.CarpetaID = ic.CarpetaID";

$db_search_fields = "CASE 
WHEN ei.Intermedia IS NOT NULL THEN 3
WHEN ic.Complementaria IS NOT NULL AND ei.Intermedia IS NULL THEN 2
ELSE 1
END AS 'CatEtapaProcesalID', c.[CarpetaID]";

$db_insert_table = "[dbo].[EtapaInvestigacion]";

$db_insert_fields = "([CatEtapaProcesalID]
,[CarpetaID])";

$db_insert_conditions = "YEAR(c.FechaInicio) IN ($year) AND MONTH(c.FechaInicio) IN ($month)";



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
		executeDumpQuery(
            (object) array(
                'data' => $data,
                'db_query_params' => (object) array(
                    'db_search_table' => $db_search_table,
                    'db_search_fields' => $db_search_fields,
                    'db_insert_table' => $db_insert_table,
                    'db_insert_fields' => $db_insert_fields,
                    'db_insert_conditions' => $db_insert_conditions
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

