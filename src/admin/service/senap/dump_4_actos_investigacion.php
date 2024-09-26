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

$db_insert_table = "[dbo].[ActosInvestigacion]
([CarpetaID]
,[CatTipoActosInvestigacionID]
,[CatClasificacionActoID]
,[ejercicios_id])";

$db_insert_conditions = "YEAR(c.FechaInicio) IN ($year) AND MONTH(c.FechaInicio) IN ($month)";

$db_query = "SELECT c.CarpetaID, act.CatTipoActosInvestigacionID, act.CatClasificacionActosID, c.id
FROM [EJERCICIOS2].[dbo].[Carpetas] c 
INNER JOIN
(

(SELECT 
	'DEF'+CONVERT(varchar(10), pd.[idPuestaDisposicion]) AS 'CLAS',
	pd.nuc,
	1 AS 'CatTipoActosInvestigacionID',
	1 AS 'CatClasificacionActosID'
	FROM [ESTADISTICAV2].[pueDisposi].[Defunciones] def
	INNER JOIN [ESTADISTICAV2].[pueDisposi].[puestaDisposicion] pd
	ON pd.idPuestaDisposicion = def.idPueDisposicion)

UNION

(SELECT 
	'ENT'+CONVERT(varchar(10), pd.[idPuestaDisposicion]) AS 'CLAS',
	pd.nuc,
	1 AS 'CatTipoActosInvestigacionID',
	2 AS 'CatClasificacionActosID'
	FROM [ESTADISTICAV2].[pueDisposi].[TrabajoDeCampo] tc
	INNER JOIN [ESTADISTICAV2].[pueDisposi].[puestaDisposicion] pd
	ON pd.idPuestaDisposicion = tc.idPueDisposicion
	WHERE tc.entrevistas IS NOT NULL AND tc.entrevistas != 0)

UNION 

(SELECT 
	'VISD'+CONVERT(varchar(10), pd.[idPuestaDisposicion]) AS 'CLAS',
	pd.nuc,
	1 AS 'CatTipoActosInvestigacionID',
	3 AS 'CatClasificacionActosID'
	FROM [ESTADISTICAV2].[pueDisposi].[TrabajoDeCampo] tc
	INNER JOIN [ESTADISTICAV2].[pueDisposi].[puestaDisposicion] pd
	ON pd.idPuestaDisposicion = tc.idPueDisposicion
	WHERE tc.visitasDomiciliarias IS NOT NULL AND tc.visitasDomiciliarias != 0)

UNION

(SELECT 
	'INVC'+CONVERT(varchar(10), pd.[idPuestaDisposicion]) AS 'CLAS',
	pd.nuc,
	1 AS 'CatTipoActosInvestigacionID',
	4 AS 'CatClasificacionActosID'
	FROM [ESTADISTICAV2].[pueDisposi].[TrabajoDeCampo] tc
	INNER JOIN [ESTADISTICAV2].[pueDisposi].[puestaDisposicion] pd
	ON pd.idPuestaDisposicion = tc.idPueDisposicion
	WHERE tc.investigacionesCumplidas IS NOT NULL AND tc.investigacionesCumplidas != 0)

UNION

(SELECT 
	'INVI'+CONVERT(varchar(10), pd.[idPuestaDisposicion]) AS 'CLAS',
	pd.nuc,
	1 AS 'CatTipoActosInvestigacionID',
	5 AS 'CatClasificacionActosID'
	FROM [ESTADISTICAV2].[pueDisposi].[TrabajoDeCampo] tc
	INNER JOIN [ESTADISTICAV2].[pueDisposi].[puestaDisposicion] pd
	ON pd.idPuestaDisposicion = tc.idPueDisposicion
	WHERE tc.investigacionesInformadas IS NOT NULL AND tc.investigacionesInformadas != 0)

UNION

(SELECT 
	'IDIV'+CONVERT(varchar(10), pd.[idPuestaDisposicion]) AS 'CLAS',
	pd.nuc,
	1 AS 'CatTipoActosInvestigacionID',
	6 AS 'CatClasificacionActosID'
	FROM [ESTADISTICAV2].[pueDisposi].[TrabajoDeCampo] tc
	INNER JOIN [ESTADISTICAV2].[pueDisposi].[puestaDisposicion] pd
	ON pd.idPuestaDisposicion = tc.idPueDisposicion
	WHERE tc.individuaciones IS NOT NULL AND tc.individuaciones != 0)

UNION

(SELECT 
	'SOLVI'+CONVERT(varchar(10), pd.[idPuestaDisposicion]) AS 'CLAS',
	pd.nuc,
	1 AS 'CatTipoActosInvestigacionID',
	7 AS 'CatClasificacionActosID'
	FROM [ESTADISTICAV2].[pueDisposi].[TrabajoDeCampo] tc
	INNER JOIN [ESTADISTICAV2].[pueDisposi].[puestaDisposicion] pd
	ON pd.idPuestaDisposicion = tc.idPueDisposicion
	WHERE tc.solicitudVideos IS NOT NULL AND tc.solicitudVideos != 0)

UNION

(SELECT 
	'PLAN'+CONVERT(varchar(10), pd.[idPuestaDisposicion]) AS 'CLAS',
	pd.nuc,
	1 AS 'CatTipoActosInvestigacionID',
	8 AS 'CatClasificacionActosID'
	FROM [ESTADISTICAV2].[pueDisposi].[TrabajoDeCampo] tc
	INNER JOIN [ESTADISTICAV2].[pueDisposi].[puestaDisposicion] pd
	ON pd.idPuestaDisposicion = tc.idPueDisposicion
	WHERE tc.planimetrias IS NOT NULL AND tc.planimetrias != 0)

UNION

(SELECT 
	'RECP'+CONVERT(varchar(10), pd.[idPuestaDisposicion]) AS 'CLAS',
	pd.nuc,
	1 AS 'CatTipoActosInvestigacionID',
	9 AS 'CatClasificacionActosID'
	FROM [ESTADISTICAV2].[pueDisposi].[TrabajoDeCampo] tc
	INNER JOIN [ESTADISTICAV2].[pueDisposi].[puestaDisposicion] pd
	ON pd.idPuestaDisposicion = tc.idPueDisposicion
	WHERE tc.recPersonas IS NOT NULL AND tc.recPersonas != 0)

UNION

(SELECT 
	'RECO'+CONVERT(varchar(10), pd.[idPuestaDisposicion]) AS 'CLAS',
	pd.nuc,
	1 AS 'CatTipoActosInvestigacionID',
	10 AS 'CatClasificacionActosID'
	FROM [ESTADISTICAV2].[pueDisposi].[TrabajoDeCampo] tc
	INNER JOIN [ESTADISTICAV2].[pueDisposi].[puestaDisposicion] pd
	ON pd.idPuestaDisposicion = tc.idPueDisposicion
	WHERE tc.recObjetos IS NOT NULL AND tc.recObjetos != 0)

UNION

(SELECT 
	'RECF'+CONVERT(varchar(10), pd.[idPuestaDisposicion]) AS 'CLAS',
	pd.nuc,
	1 AS 'CatTipoActosInvestigacionID',
	11 AS 'CatClasificacionActosID'
	FROM [ESTADISTICAV2].[pueDisposi].[TrabajoDeCampo] tc
	INNER JOIN [ESTADISTICAV2].[pueDisposi].[puestaDisposicion] pd
	ON pd.idPuestaDisposicion = tc.idPueDisposicion
	WHERE tc.recFotografias IS NOT NULL AND tc.recFotografias != 0)

UNION

(SELECT 
	'RECP'+CONVERT(varchar(10), pd.[idPuestaDisposicion]) AS 'CLAS',
	pd.nuc,
	1 AS 'CatTipoActosInvestigacionID',
	12 AS 'CatClasificacionActosID'
	FROM [ESTADISTICAV2].[pueDisposi].[TrabajoDeCampo] tc
	INNER JOIN [ESTADISTICAV2].[pueDisposi].[puestaDisposicion] pd
	ON pd.idPuestaDisposicion = tc.idPueDisposicion
	WHERE tc.recPersonas IS NOT NULL AND tc.recPersonas != 0)

UNION

(SELECT 
	'ACTSCJ'+CONVERT(varchar(10), [idEstatusNucs]) AS 'CLAS',
	nuc,
	1 AS 'CatTipoActosInvestigacionID',
	CASE idEstatus
	WHEN 121 THEN 12
	WHEN 122 THEN 13
	WHEN 123 THEN 14
	WHEN 124 THEN 15
	WHEN 125 THEN 16
	WHEN 126 THEN 17
	WHEN 127 THEN 18
	WHEN 128 THEN 19
	END AS 'CatClasificacionActosID'
	FROM [ESTADISTICAV2].[dbo].[estatusNucs]
	WHERE idEstatus IN (121, 122, 123, 124, 125, 126, 127, 128))

UNION

(SELECT 
	'ACTCCJ'+CONVERT(varchar(10), [idEstatusNucs]) AS 'CLAS',
	nuc,
	2 AS 'CatTipoActosInvestigacionID',
	CASE idEstatus
	WHEN 114 THEN 20
	WHEN 115 THEN 21
	WHEN 116 THEN 22
	WHEN 117 THEN 23
	WHEN 119 THEN 24
	WHEN 120 THEN 25
	END AS 'CatClasificacionActosID'
	FROM [ESTADISTICAV2].[dbo].[estatusNucs]
	WHERE idEstatus IN (114, 115, 116, 117, 119, 120))

UNION

(SELECT 
	'CATE'+CONVERT(varchar(10), [idPuestaDisposicion]) AS 'CLAS',
	nuc,
	2 AS 'CatTipoActosInvestigacionID',
	26 AS 'CatClasificacionActosID'
	FROM [ESTADISTICAV2].[pueDisposi].[puestaDisposicion]
	WHERE cate IS NOT NULL AND cate != 0)

) act

ON c.NUC = act.nuc collate Modern_Spanish_CI_AI";



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

