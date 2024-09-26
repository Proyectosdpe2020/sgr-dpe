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

$db_insert_table = "[dbo].[MandamientosJudiciales]
([FechaSolicitudMJ]
,[CatTipoMandamientoID]
,[FechaLibramientoMJ]
,[CatEstatusMJID]
,[FechaCumplimientoMJ]
,[CarpetaID],[ProcesoID],[ejercicios_id])";

$db_insert_conditions = "YEAR(manju.FechaInicio) IN ($year) AND MONTH(manju.FechaInicio) IN ($month)";

$db_query = "SELECT manju.FechaSolicitudMJ, ctm.senap_id as 'CatTipoMandamientoID', manju.FechaLibramientoMJ, manju.CatEstatusMJID, manju.FechaCumplimientoMJ, manju.CarpetaID, proces.ProcesoID, proces.ejercicios_id FROM
(SELECT
'MJUD'+CONVERT(varchar(10), en.idEstatusNucs) AS 'MandamientoJudicialID',
CONVERT(date, en.fecha, 5) AS 'FechaSolicitudMJ',
CASE e.idEstatus
WHEN 50 THEN 1
WHEN 57 THEN 1
WHEN 53 THEN 2
WHEN 58 THEN 2
ELSE 4
END AS 'CatTipoMandamientoID',
CONVERT(date, en.fecha, 5) AS 'FechaLibramientoMJ',
CASE e.idEstatus
WHEN 50 THEN 2
WHEN 57 THEN 1
WHEN 53 THEN 2
WHEN 58 THEN 1
ELSE 3
END AS 'CatEstatusMJID',
oa.fechaCumplimento AS 'FechaCumplimientoMJ',
c.CarpetaID,
c.FechaInicio,
c.id AS 'ejercicios_id'
FROM [EJERCICIOS2].[dbo].[Carpetas] c 
INNER JOIN [ESTADISTICAV2].[dbo].[estatusNucs] en 
ON c.NUC = en.nuc collate Modern_Spanish_CI_AI
INNER JOIN [ESTADISTICAV2].[dbo].[estatus] e
ON e.idEstatus = en.idEstatus
LEFT JOIN [ESTADISTICAV2].[senap].[ordenesAprehension] oa
ON en.idEstatusNucs = oa.idEstatusNucs
WHERE en.idEstatus IN (50, 53, 57, 58) ) manju

--LEFT JOIN dbo.Procesos proces ON proces.CarpetaID = manju.CarpetaID
LEFT JOIN dbo.Procesos proces ON proces.ejercicios_id = manju.ejercicios_id
LEFT JOIN CatTipoMandamientos ctm ON ctm.TipoMandamientoID = manju.CatTipoMandamientoID";



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

