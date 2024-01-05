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

$db_insert_table = "[dbo].[Sentencias]
([FechaAudienciaJuicio]
,[TiposPruebasDesahogadasAudienciaJuicio] 
,[SentenciaDerivadaProcedimientoAbreviado]
,[FechaDictoProcedimientoAbreviado]
,[FechaDictoSentencia]
,[TipoSentencia]
,[TiempoPrision]
,[MontoReparacionDanoImpuesta]
,[SentenciaFirme]
,[CarpetaID],[ProcesoID])";

$db_insert_conditions = "YEAR(sen.FechaInicio) IN ($year) AND MONTH(sen.FechaInicio) IN ($month)";

$db_query = "SELECT 
CASE sen.FechaAudienciaJuicio
WHEN '1900-01-01' THEN NULL
ELSE sen.FechaAudienciaJuicio
END AS 'FechaAudienciaJuicio',
CASE 
WHEN sen.TiposPruebasDesahogadasAudienciaJuicio IS NULL THEN 6
ELSE sen.TiposPruebasDesahogadasAudienciaJuicio
END AS 'TiposPruebasDesahogadasAudienciaJuicio',
CASE sen.SentenciaDerivadaProcedimientoAbreviado
WHEN 0 THEN 0
WHEN 1 THEN 1
WHEN 2 THEN 0
WHEN 3 THEN 0
ELSE 0
END AS 'SentenciaDerivadaProcedimientoAbreviado',
CASE sen.FechaDictoProcedimientoAbreviado
WHEN '1900-01-01' THEN NULL
ELSE sen.FechaDictoProcedimientoAbreviado
END AS 'FechaDictoProcedimientoAbreviado',
CASE sen.FechaDictoSentencia
WHEN '1900-01-01' THEN NULL
ELSE sen.FechaDictoSentencia
END AS 'FechaDictoSentencia',
CASE sen.TipoSentencia
WHEN 0 THEN 3
ELSE sen.TipoSentencia
END AS 'TipoSentencia',
sen.TiempoPrision AS 'TiempoPrision',
CASE
WHEN sen.MontoReparacionDanoImpuesta IS NULL THEN 0
ELSE sen.MontoReparacionDanoImpuesta
END AS 'MontoReparacionDanoImpuesta',
CASE sen.SentenciaFirme
WHEN 0 THEN 0
WHEN 1 THEN 1
WHEN 2 THEN 0
WHEN 3 THEN 0
ELSE 0
END AS 'SentenciaFirme',
sen.CarpetaID, proces.ProcesoID
FROM

(SELECT [idSentencia]
,subaud.[fechaAudienciaJuicio] AS 'FechaAudienciaJuicio'
,subaud.[idTipoPruebasAudiencia] AS 'TiposPruebasDesahogadasAudienciaJuicio'
,se.[sentDerivaProcAbrv] AS 'SentenciaDerivadaProcedimientoAbreviado'
,se.[fechaDictoProcAbrv] AS 'FechaDictoProcedimientoAbreviado'
,se.[fechaDictoSentencia] AS 'FechaDictoSentencia'
,se.[idTipoSentencia] AS 'TipoSentencia'
,se.[aniosPrision] AS 'TiempoPrision'
,subrda.[montoReparacionDanio] AS 'MontoReparacionDanoImpuesta'
,se.[sentenciaEncuentraFirme] AS 'SentenciaFirme'
,c.CarpetaID
,c.FechaInicio

FROM [ESTADISTICAV2].[senap].[sentencias] se
INNER JOIN [PRUEBA].[dbo].[Resoluciones] res
ON se.ResolucionID = res.ResolucionID
INNER JOIN [PRUEBA].[dbo].[Carpeta] c 
ON res.CarpetaID = c.CarpetaID

LEFT JOIN

(SELECT en.nuc
,au.[fechaAudienciaJuicio]
,au.[idTipoPruebasAudiencia]
FROM [ESTADISTICAV2].[dbo].[estatusNucs] en 
INNER JOIN [ESTADISTICAV2].[senap].[audienciasJuicio] au
ON au.idEstatusNucs = en.idEstatusNucs) subaud

ON c.NUC = subaud.nuc collate Modern_Spanish_CI_AI

LEFT JOIN

(SELECT en.nuc
,rd.[montoReparacionDanio]
FROM [ESTADISTICAV2].[dbo].[estatusNucs] en 
INNER JOIN [ESTADISTICAV2].[senap].[reparacionDanios] rd
ON rd.idEstatusNucs = en.idEstatusNucs) subrda

ON c.NUC = subrda.nuc collate Modern_Spanish_CI_AI) sen

LEFT JOIN dbo.Procesos proces ON proces.CarpetaID = sen.CarpetaID";



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

