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

$db_insert_table = "[dbo].[BienesAsegurados]
([CatBienAseguradoID]
,[Cantidad]
,[CatUnidadMedidaID]
,[CarpetaID]
,[ejercicios_id])";

$db_insert_conditions = "YEAR(c.FechaInicio) IN ($year) AND MONTH(c.FechaInicio) IN ($month)";

$db_query = "SELECT ase.CatBienAseguradoID, ase.CantidadBienAsegurado, ase.CatUnidadMedidaID, c.CarpetaID, c.id
FROM [EJERCICIOS2].[dbo].[Carpetas] c 
INNER JOIN
(
(SELECT 
	'DROG'+CONVERT(varchar(10), d.id) AS 'SEC',
	cd.CatBienAseguradoID AS 'CatBienAseguradoID',
	Cantidad AS 'CantidadBienAsegurado',
	cm.id AS 'CatUnidadMedidaID',
	CarpetaID
	FROM [PRUEBA].[dbo].[carpetaDroga] d INNER JOIN [PRUEBA].[dbo].[carpetaTipoDroga] cd
	ON d.idTipoDroga = cd.id INNER JOIN [PRUEBA].[dbo].[carpetaUnidadMedida] cm ON cm.id = d.idUnidadMedida
	WHERE idTipoDroga != 0 AND Cantidad != 0 AND idUnidadMedida != 0)

UNION

(SELECT 
	'ARM'+CONVERT(varchar(10), ca.id) AS 'SEC',
	ta.CatBienAseguradoID AS 'CatBienAseguradoID',
	1 AS 'CantidadBienAsegurado',
	7 AS 'CatUnidadMedidaID',
	CarpetaID
	FROM [PRUEBA].[dbo].[carpetaArma] ca INNER JOIN [PRUEBA].[dbo].[carpetaTipoArma] ta
	ON ta.id = ca.idTipo
	WHERE idTipo NOT IN (0))

) ase
ON c.CarpetaID = ase.CarpetaID";



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

