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

$db_insert_table = "[dbo].[MASC]
([AutoridadDerivaMASC]
,[FechaDerivaMASC]
,[TipoMASC]
,[TipoCumplimiento]
,[FechaCumplimientoMASC]
,[MontoReparacionDano]
,[CarpetaID],[ProcesoID])";

$db_insert_conditions = "YEAR(masc.FechaInicio) IN ($year) AND MONTH(masc.FechaInicio) IN ($month)";

$db_query = "SELECT masc.AutoridadDerivaMASC, masc.FechaDerivaMASC, masc.TipoMASC, masc.TipoCumplimiento, masc.FechaCumplimientoMASC, masc.MontoReparacionDano, masc.CarpetaID, proces.ProcesoID
FROM
(SELECT 
CASE 
WHEN cr.CarpetaRecibidaID IS NOT NULL THEN 'MASCCR'+CONVERT(varchar(10), cr.CarpetaRecibidaID)
ELSE 'MASCAC'+CONVERT(varchar(10), ac.AcuerdoCelebradoID)
END AS 'MASCID',
1 AS 'AutoridadDerivaMASC',
CASE
WHEN cr.[Fecha] IS NOT NULL THEN cr.[Fecha]
ELSE ac.Fecha
END AS 'FechaDerivaMASC',
CASE ac.[Mecanismo]
WHEN 'Mediación' THEN 1
WHEN 'Conciliación' THEN 2
WHEN 'Junta restaurativa' THEN 3
ELSE 6
END AS 'TipoMASC',
CASE ac.[Cumplimiento]
WHEN 'Inmediato' THEN 1
WHEN 'Diferido' THEN 2
ELSE 3
END AS 'TipoCumplimiento',
ac.[Fecha] AS 'FechaCumplimientoMASC',
ac.[MontoRecuperado] AS 'MontoReparacionDano',
c.CarpetaID,
c.FechaInicio,
c.id as 'ejercicios_id'
FROM
[EJERCICIOS2].[dbo].[Carpetas] c 
INNER JOIN
[EJERCICIOS].[dbo].[AcuerdosCelebrados] ac
ON c.NUC = ac.NUC collate Modern_Spanish_CI_AI
LEFT JOIN
[EJERCICIOS].[dbo].[CarpetasRecibidas] cr
ON cr.NUC = ac.NUC) masc

LEFT JOIN dbo.Procesos proces ON proces.ejercicios_id = masc.ejercicios_id";



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

