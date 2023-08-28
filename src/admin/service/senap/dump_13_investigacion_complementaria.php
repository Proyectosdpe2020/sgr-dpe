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

$db_insert_table = "[dbo].[InvestigacionComplementaria]
([FormulacionImputacion]
,[FechaFormulacionImputacion]
,[ResolucionAutoVinculacionProceso]
,[FechaAutoVinculacionProceso]
,[MedidaCautelar]
,[FechaCierreInvestigacion]
,[FormuloAcusacion]
,[CarpetaID],[ProcesoID])";

$db_insert_conditions = "YEAR(invcomp.FechaInicio) IN ($year) AND MONTH(invcomp.FechaInicio) IN ($month)";

$db_query = "SELECT 
invcomp.FormulacionImputacion, invcomp.FechaFormulacionImputacion, invcomp.ResolucionAutoVinculacionProceso,
invcomp.FechaAutoVinculacionProceso, invcomp.MedidaCautelar, invcomp.FechaCierreInvestigacion, invcomp.FormuloAcusacion,
invcomp.CarpetaID, proces.ProcesoID
FROM 
(SELECT
'AVP'+CONVERT(varchar(10), en.idEstatusNucs) AS 'InvestigacionComplementariaID',
CASE
WHEN fi.[fechaFormulacion] IS NULL THEN 0
ELSE 1
END AS 'FormulacionImputacion',

fi.[fechaFormulacion] AS 'FechaFormulacionImputacion',
CASE
WHEN subavp.fechaAutoVincuProc IS NULL THEN 2
WHEN subavp.fechaAutoVincuProc = '1900-01-01' THEN 2
ELSE 1
END AS 'ResolucionAutoVinculacionProceso',
subavp.fechaAutoVincuProc AS 'FechaAutoVinculacionProceso',
1 AS 'MedidaCautelar',
CASE submc.fechaCierreInvest
WHEN '1900-01-01' THEN NULL
ELSE submc.fechaCierreInvest
END AS 'FechaCierreInvestigacion',
CASE submc.formulacionAcusacion
WHEN 1 THEN 1
ELSE 0
END AS 'FormuloAcusacion',
c.CarpetaID,
c.FechaInicio
FROM [PRUEBA].[dbo].[Carpeta] c 

INNER JOIN [ESTADISTICAV2].[dbo].[estatusNucs] en 
ON c.NUC = en.nuc collate Modern_Spanish_CI_AI

LEFT JOIN [ESTADISTICAV2].[senap].[formulacionesImputacion] fi
ON fi.idEstatusNucs = en.idEstatusNucs

LEFT JOIN

(SELECT c.NUC,
avp.ResolucionID,
avp.fechaAutoVincuProc
FROM [ESTADISTICAV2].[senap].[autoVincuProc] avp
INNER JOIN PRUEBA.dbo.Resoluciones res
ON avp.ResolucionID = res.ResolucionID
INNER JOIN PRUEBA.dbo.Carpeta c
ON res.CarpetaID = c.CarpetaID) subavp

ON c.NUC = subavp.NUC collate Modern_Spanish_CI_AI

LEFT JOIN

(SELECT c.NUC,
avp.ResolucionID,
avp.fechaAutoVincuProc
FROM [ESTADISTICAV2].[senap].[autoVincuProc] avp
INNER JOIN [ESTADISTICAV2].[dbo].[estatusNucs] en 
ON avp.idEstatusNucs = en.idEstatusNucs
INNER JOIN PRUEBA.dbo.Carpeta c
ON en.nuc = c.NUC collate Modern_Spanish_CI_AI) subavpnuevos

ON c.NUC = subavpnuevos.NUC collate Modern_Spanish_CI_AI

LEFT JOIN

(SELECT en.nuc,
mc.fechaCierreInvest,
mc.formulacionAcusacion
FROM [ESTADISTICAV2].[dbo].[estatusNucs] en
INNER JOIN [ESTADISTICAV2].[senap].[medidaCautelar] mc 
ON en.idEstatusNucs = mc.idEstatusNucs) submc

ON c.NUC = submc.nuc collate Modern_Spanish_CI_AI

UNION

SELECT
'NOAVP'+CONVERT(varchar(10), en.idEstatusNucs) AS 'InvestigacionComplementariaID',
CASE
WHEN fi.[fechaFormulacion] IS NULL THEN 0
ELSE 1
END AS 'FormulacionImputacion',
fi.[fechaFormulacion] AS 'FechaFormulacionImputacion',
2 AS 'ResolucionAutoVinculacionProceso',
NULL AS 'FechaAutoVinculacionProceso',
0 AS 'MedidaCautelar',
NULL AS 'FechaCierreInvestigacion',
0 AS 'FormuloAcusacion',
c.CarpetaID,
c.FechaInicio
FROM [PRUEBA].[dbo].[Carpeta] c 

INNER JOIN [ESTADISTICAV2].[dbo].[estatusNucs] en 
ON c.NUC = en.nuc collate Modern_Spanish_CI_AI

LEFT JOIN [ESTADISTICAV2].[senap].[formulacionesImputacion] fi
ON fi.idEstatusNucs = en.idEstatusNucs

WHERE en.idEstatus = 10) invcomp

LEFT JOIN dbo.Procesos proces ON proces.CarpetaID = invcomp.CarpetaID";



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

