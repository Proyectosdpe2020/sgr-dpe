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

//$db_insert_conditions = "YEAR(sen.FechaInicio) IN ($year) AND MONTH(sen.FechaInicio) IN ($month)";

$db_insert_conditions = "((month(sen.FechaInicio) in (1,2,3,4,5,6) and year(sen.FechaInicio)=2024) or year(sen.FechaInicio)=2023)";

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
,c.id as 'ejercicios_id'

FROM [ESTADISTICAV2].[senap].[sentencias] se
INNER JOIN [ESTADISTICAV2].dbo.estatusNucs es
ON se.idEstatusNucs = es.idEstatusNucs
INNER JOIN [EJERCICIOS2].[dbo].[Carpetas] c 
ON c.NUC = es.nuc collate Modern_Spanish_CI_AI

LEFT JOIN

(SELECT en.nuc
,subaj_menor_fecha.[fechaAudienciaJuicio]
,subaj_menor_fecha.[idTipoPruebasAudiencia]
FROM [ESTADISTICAV2].[dbo].[estatusNucs] en 
INNER JOIN (
    SELECT aj.idEstatusNucs, aj.fechaAudienciaJuicio, aj.idTipoPruebasAudiencia,
           ROW_NUMBER() OVER (PARTITION BY nuc ORDER BY fechaAudienciaJuicio ASC) AS fila
    FROM [ESTADISTICAV2].[senap].[audienciasJuicio] aj INNER JOIN [ESTADISTICAV2].[dbo].[estatusNucs] en ON en.idEstatusNucs = aj.idEstatusNucs
) AS subaj_menor_fecha
ON subaj_menor_fecha.idEstatusNucs = en.idEstatusNucs where fila = 1) subaud

ON c.NUC = subaud.nuc collate Modern_Spanish_CI_AI

LEFT JOIN

(SELECT en.nuc
,subrd_mayor_registro.[montoReparacionDanio]
FROM [ESTADISTICAV2].[dbo].[estatusNucs] en 
INNER JOIN (
	SELECT rd.idEstatusNucs, rd.montoReparacionDanio,
           ROW_NUMBER() OVER (PARTITION BY nuc ORDER BY idReparacionDanio DESC) AS fila
    FROM [ESTADISTICAV2].[senap].[reparacionDanios] rd INNER JOIN [ESTADISTICAV2].[dbo].[estatusNucs] en ON en.idEstatusNucs = rd.idEstatusNucs

) AS subrd_mayor_registro

ON subrd_mayor_registro.idEstatusNucs = en.idEstatusNucs WHERE fila = 1) subrda

ON c.NUC = subrda.nuc collate Modern_Spanish_CI_AI) sen

LEFT JOIN dbo.Procesos proces ON proces.ejercicios_id = sen.ejercicios_id";



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

