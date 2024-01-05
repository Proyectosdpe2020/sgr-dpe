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
,[CarpetaID])";

$db_insert_conditions = "YEAR(c.FechaInicio) IN ($year) AND MONTH(c.FechaInicio) IN ($month)";

$db_query = "SELECT ase.CatBienAseguradoID, ase.CantidadBienAsegurado, ase.CatUnidadMedidaID, c.CarpetaID
FROM [PRUEBA].[dbo].[Carpeta] c 
INNER JOIN
(
(SELECT 
	'DROG'+CONVERT(varchar(10), id) AS 'SEC',
	CASE idTipoDroga
	WHEN 1 THEN 58
	WHEN 2 THEN 59
	WHEN 3 THEN 60
	WHEN 4 THEN 61
	WHEN 5 THEN 62
	WHEN 6 THEN 63
	WHEN 7 THEN 64
	WHEN 8 THEN 65
	WHEN 9 THEN 66
	WHEN 10 THEN 67
	WHEN 11 THEN 68
	ELSE 0
	END AS 'CatBienAseguradoID',
	Cantidad AS 'CantidadBienAsegurado',
	CASE idUnidadMedida
	WHEN 0 THEN 25
	ELSE idUnidadMedida
	END AS 'CatUnidadMedidaID',
	CarpetaID
	FROM [PRUEBA].[dbo].[carpetaDroga] WHERE idTipoDroga NOT IN (0))

UNION

(SELECT 
	'FORES'+CONVERT(varchar(10), id) AS 'SEC',
	CASE idTipoMadera
	WHEN 1 THEN 69
	WHEN 2 THEN 70
	WHEN 3 THEN 71
	WHEN 4 THEN 72
	WHEN 5 THEN 73
	WHEN 6 THEN 74
	WHEN 7 THEN 75
	WHEN 8 THEN 76
	WHEN 9 THEN 77
	WHEN 10 THEN 78
	WHEN 11 THEN 79
	WHEN 12 THEN 80
	WHEN 13 THEN 81
	WHEN 14 THEN 82
	WHEN 15 THEN 83
	WHEN 16 THEN 84
	WHEN 17 THEN 85
	WHEN 18 THEN 86
	ELSE 0
	END AS 'CatBienAseguradoID',
	cantidad AS 'CantidadBienAsegurado',
	CASE idUnidadMedida
	WHEN 0 THEN 13
	ELSE idUnidadMedida
	END AS 'CatUnidadMedidaID',
	CarpetaID
	FROM [PRUEBA].[dbo].[carpetaMadera] WHERE idTipoMadera NOT IN (0))

UNION

(SELECT 
	'ARM'+CONVERT(varchar(10), id) AS 'SEC',
	CASE idTipo
	WHEN 0 THEN 0
	ELSE idTipo
	END AS 'CatBienAseguradoID',
	1 AS 'CantidadBienAsegurado',
	22 AS 'CatUnidadMedidaID',
	CarpetaID
	FROM [PRUEBA].[dbo].[carpetaArma] WHERE idTipo NOT IN (0) AND idTipo < 58)

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

