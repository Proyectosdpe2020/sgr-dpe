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

$db_insert_table = "[dbo].[Determinaciones]
([CarpetaID]
,[EstatusCarpeta]
,[SentidoDeterminacion]
,[AcuerdosReparatorios]
,[ReactivacionCarpeta]
,[FechaDeterminacion]
,[TipoCriteriosOportunidad]
,[ejercicios_id])";

$db_insert_conditions = "(idCarpeta<>0) and (month(c.FechaInicio) in (1,2,3,4,5,6) and year(c.FechaInicio)=2024) or year(c.FechaInicio)=2023) deter
	 inner join [PRUEBA].[dbo].[CatDeterminaciones] cd on cd.CatEstatusResolucionesID = deter.idEstatus";

$db_query = "SELECT deter.idCarpeta as 'CarpetaID', cd.CatEstatusCarpetaID as 'EstatusCarpeta', cd.CatDeterminacionID as 'SentidoDeterminacion', deter.acuerdo as 'AcuerdosReparatorios', 
NULL as 'ReactivacionCarpeta', deter.fecha as 'FechaDeterminacion', deter.criterioOportunidad as 'TipoCriteriosOportunidad', deter.id from 
(SELECT distinct
		c.FechaInicio,
      estadis.[idCarpeta]
	  ,estadis.nuc
	  ,estadis.[idEstatus]
	  ,estadis.fecha
	  ,c.id
	  ,(select top 1 CatDeterminacionID from [PRUEBA].[dbo].[CatDeterminaciones] where estadis.idEstatus=[PRUEBA].[dbo].[CatDeterminaciones].[CatEstatusResolucionesID]) determinacion
	  ,(select top 1 [idTipoAcuerdoReparatorio] FROM [ESTADISTICAV2].[senap].[acuerdosReparatorios] acuerdo left join [ESTADISTICAV2].[dbo].estatusNucs estatus on acuerdo.idEstatusNucs=estatus.idEstatusNucs where estatus.nuc=estadis.nuc and estadis.idEstatus=23) as acuerdo
	  ,(select top 1 idTipoSentencia FROM [ESTADISTICAV2].[senap].[sentencias] sentencia left join [ESTADISTICAV2].[dbo].estatusNucs estatus on sentencia.idEstatusNucs=estatus.idEstatusNucs where estatus.nuc=estadis.nuc) as sentencia
	  ,(select top 1 idTipoCriterioOportunidad FROM [ESTADISTICAV2].[senap].[criteriosOportunidad] criterio left join [ESTADISTICAV2].[dbo].estatusNucs estatus on criterio.idEstatusNucs=estatus.idEstatusNucs where estatus.nuc=estadis.nuc) as criterioOportunidad
     FROM [ESTADISTICAV2].[dbo].[estatusNucsCarpetas] estadis inner join [EJERCICIOS2].[dbo].[Carpetas] c on abs(c.CarpetaID) = estadis.idCarpeta";



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

