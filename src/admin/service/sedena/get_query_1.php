<?php
session_start();
include("../../../../service/connection.php");

$conn = $connections['sicap']['conn'];
$db_table = '[dbo].[Usuario]';

$data = array();

$initial_date = $_POST['initial_date'];
$finish_date = $_POST['finish_date'];
$initial_date_2 = $_POST['initial_date_2'];
$finish_date_2 = $_POST['finish_date_2'];
$crimes = $_POST['crimes'];


$formed_s = formSearchByMultipleValues($crimes);

$query = "SELECT ad.Grupo as 'Delito',
CASE WHEN per1.Cantidad is null
THEN 0 ELSE per1.Cantidad END AS '$initial_date a $finish_date',
CASE WHEN per2.Cantidad is null
THEN 0 ELSE per2.Cantidad END AS '$initial_date_2 a $finish_date_2'
FROM 

(SELECT DISTINCT [Grupo]
  FROM [PRUEBA].[dbo].[AgrupacionDelito] ) ad

  left join 
(SELECT a.Grupo AS 'Delito', count(a.Grupo) AS 'Cantidad'
FROM (
SELECT DISTINCT c.NUC AS 'NUC',
		c.FechaInicio AS 'Fecha Inicio',
		mo.Nombre AS 'Delito', ad.Grupo,
		c.Contar
	FROM dbo.Carpeta c
		INNER JOIN dbo.CatUIs u ON c.CatUIsID= u.CatUIsID
		INNER JOIN dbo.CatFiscalias fisca ON u.CatFiscaliasID=fisca.CatFiscaliasID
		inner JOIN dbo.Involucrado vic ON c.CarpetaID=vic.CarpetaID		
		INNER JOIN dbo.VictimaOfendido ON dbo.VictimaOfendido.InvolucradoID=vic.InvolucradoID				
		INNER JOIN dbo.DelitosVictima ON dbo.VictimaOfendido.VictimaOfendidoID=dbo.DelitosVictima.VictimaID
		INNER JOIN dbo.CatModalidadesEstadisticas mo ON dbo.DelitosVictima.CatModalidadesID=mo.CatModalidadesEstadisticasID
		INNER JOIN dbo.Delito del ON c.CarpetaID=del.CarpetaID
		inner join dbo.AgrupacionDelito ad ON ad.CatModalidadesEstadisticasID = mo.CatModalidadesEstadisticasID
	WHERE 
		contar=1			 
		and (FechaInicio between '$initial_date' and '$finish_date')
		and ad.Grupo in ($formed_s)
) a GROUP BY a.Grupo) per1
ON per1.Delito = ad.Grupo
LEFT JOIN
(SELECT a.Grupo AS 'Delito', count(a.Grupo) AS 'Cantidad'
FROM (
SELECT DISTINCT c.NUC AS 'NUC',
		c.FechaInicio AS 'Fecha Inicio',
		mo.Nombre AS 'Delito', ad.Grupo,
		c.Contar
	FROM dbo.Carpeta c
		INNER JOIN dbo.CatUIs u ON c.CatUIsID= u.CatUIsID
		INNER JOIN dbo.CatFiscalias fisca ON u.CatFiscaliasID=fisca.CatFiscaliasID
		inner JOIN dbo.Involucrado vic ON c.CarpetaID=vic.CarpetaID		
		INNER JOIN dbo.VictimaOfendido ON dbo.VictimaOfendido.InvolucradoID=vic.InvolucradoID				
		INNER JOIN dbo.DelitosVictima ON dbo.VictimaOfendido.VictimaOfendidoID=dbo.DelitosVictima.VictimaID
		INNER JOIN dbo.CatModalidadesEstadisticas mo ON dbo.DelitosVictima.CatModalidadesID=mo.CatModalidadesEstadisticasID
		INNER JOIN dbo.Delito del ON c.CarpetaID=del.CarpetaID
		inner join dbo.AgrupacionDelito ad ON ad.CatModalidadesEstadisticasID = mo.CatModalidadesEstadisticasID
	WHERE 
		contar=1			 
		and (FechaInicio between '$initial_date_2' and '$finish_date_2')
		and ad.Grupo in ($formed_s)
) a GROUP BY a.Grupo) per2
ON per2.Delito = ad.Grupo
where ad.Grupo in ($formed_s) ORDER BY ad.Grupo";

$params = array();
$options = array( "Scrollable" => SQLSRV_CURSOR_KEYSET );

$result = sqlsrv_query( $conn, $query , $params, $options );

$row_count = sqlsrv_num_rows( $result );

$fields = array();

foreach( sqlsrv_field_metadata( $result ) as $fieldMetadata ) {
    foreach( $fieldMetadata as $name => $value) {
        if($name == 'Name'){
            array_push($fields, $value);
        }
    }
}

if($row_count > 0){

    while( $row = sqlsrv_fetch_array($result) ) {

        $current_row = array();

        for($i=0; $i<count($fields); $i++){

            $current_row += [$fields[$i] => $row[$fields[$i]]];

        }

        array_push($data, $current_row);
        
    }

}

else{
    $data = "no data";
}


echo json_encode($data, JSON_FORCE_OBJECT);

sqlsrv_close($conn);

function formSearchByMultipleValues($data){
	$values = "";
	$i = 1;

	foreach ($data as $element) {
		
		$values.="'$element'";

		if($i < count((array) $data)){
			$values.=",";
		}

		$i++;
	}

	return $values;
}

?>