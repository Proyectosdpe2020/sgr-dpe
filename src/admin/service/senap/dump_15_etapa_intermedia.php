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

$db_insert_table = "[dbo].[EtapaIntermedia]
([FechaEscritoAcusacion]
,[CelebracionAudienciaIntermedia]
,[FechaAudienciaIntermedia]
,[PresentacionMediosPrueba]
,[MediosPrueba]
,[AcuerdosProbatorios]
,[DictoAutoAperturaJuicioOral]
,[CarpetaID],[ProcesoID])";

$db_insert_conditions = "YEAR(einter.FechaInicio) IN ($year) AND MONTH(einter.FechaInicio) IN ($month)";

$db_query = "SELECT einter.FechaEscritoAcusacion, einter.CelebracionAudienciaIntermedia, einter.FechaAudienciaIntermedia,
einter.PresentacionMediosPrueba, einter.MediosPrueba, einter.AcuerdosProbatorios, einter.DictoAutoAperturaJuicioOral, einter.CarpetaID, proces.ProcesoID
FROM
(SELECT 
'EI'+CONVERT(varchar(10), submc.idEstatusNucs) AS 'EtapaIntermediaID',
submc.FechaEscritoAcusacion,
CASE 
WHEN subai.idAudienciasIntermedias IS NULL THEN 0
ELSE 1
END AS 'CelebracionAudienciaIntermedia',
subai.FechaAudienciaIntermedia,
CASE 
WHEN subai.PresentacionMediosPrueba IS NULL THEN 0
ELSE subai.PresentacionMediosPrueba
END AS 'PresentacionMediosPrueba',
CASE 
WHEN subai.MediosPrueba IS NULL THEN 6
ELSE subai.MediosPrueba
END AS 'MediosPrueba',
CASE 
WHEN subai.AcuerdosProbatorios IS NULL THEN 0
ELSE subai.AcuerdosProbatorios
END AS 'AcuerdosProbatorios',
CASE 
WHEN subai.DictoAutoAperturaJuicioOral IS NULL THEN 0
ELSE subai.DictoAutoAperturaJuicioOral
END AS 'DictoAutoAperturaJuicioOral',
submc.CarpetaID,
submc.FechaInicio
FROM

(SELECT 
	mc.idEstatusNucs,
	en.nuc
	,CASE mc.[fechaEscritoAcusacion]
	WHEN '1900-01-01' THEN NULL
	ELSE mc.[fechaEscritoAcusacion]
	END AS 'FechaEscritoAcusacion',
	c.CarpetaID,
	c.FechaInicio

FROM [PRUEBA].[dbo].[Carpeta] c 
INNER JOIN [ESTADISTICAV2].[dbo].[estatusNucs] en 
ON c.NUC = en.nuc collate Modern_Spanish_CI_AI
INNER JOIN [ESTADISTICAV2].[senap].[medidaCautelar] mc
ON mc.idEstatusNucs = en.idEstatusNucs
) submc

LEFT JOIN

(SELECT  
	ai.idAudienciasIntermedias,
	ai.idEstatusNucs,
	en.nuc
	,ai.[fechaAudienciaIntermedia] AS 'FechaAudienciaIntermedia'
	,CASE ai.[mediosDePrueba]
	WHEN 0 THEN 0
	WHEN 1 THEN 1
	WHEN 2 THEN 0
	WHEN 3 THEN 0
	ELSE 0
	END AS 'PresentacionMediosPrueba'
	,CASE ai.[idTipoMedioPrueba]
	WHEN 0 THEN 6
	ELSE ai.[idTipoMedioPrueba]
	END AS 'MediosPrueba'
	,CASE ai.[acuerdoProbatorio]
	WHEN 0 THEN 0
	WHEN 1 THEN 1
	WHEN 2 THEN 0
	WHEN 3 THEN 0
	ELSE 0
	END AS 'AcuerdosProbatorios'
	,CASE ai.[aperturaJuicioOral]
	WHEN 0 THEN 0
	WHEN 1 THEN 1
	WHEN 2 THEN 0
	WHEN 3 THEN 0
	ELSE 0
	END AS 'DictoAutoAperturaJuicioOral',
	c.CarpetaID,
	c.FechaInicio

FROM [PRUEBA].[dbo].[Carpeta] c 
INNER JOIN [ESTADISTICAV2].[dbo].[estatusNucs] en 
ON c.NUC = en.nuc collate Modern_Spanish_CI_AI
INNER JOIN [ESTADISTICAV2].[senap].[audienciasIntermedias] ai
ON ai.idEstatusNucs = en.idEstatusNucs
) subai
ON submc.nuc = subai.nuc) einter

LEFT JOIN dbo.Procesos proces ON proces.CarpetaID = einter.CarpetaID";



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

