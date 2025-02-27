<?php
session_start();
include("../../../../service/connection.php");
//include("../common.php");

$params = array();
$options = array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
$conn = $connections['sicap']['conn'];
$db_table = '[dbo].[CatModalidadesEstadisticas]';

$elements = array();

/*$sql = "SELECT [CatModalidadesEstadisticasID] AS 'id'
			,[Nombre]
		FROM $db_table ORDER BY [Nombre]";*/

$sql = "SELECT  id
        ,[Grupo] AS 'Nombre'
FROM (select distinct Grupo AS 'id', [Grupo] from [PRUEBA].[dbo].[AgrupacionDelito]) a
ORDER BY 
CASE 
    WHEN [Grupo] IN ('Homicidio doloso', 'Extorsión', 'Secuestro', 'Robo de vehículo', 'Narcomenudeo') THEN 0  -- Estos 4 ítems específicos van primero
    ELSE 1 
END, 
id ASC";

$result = sqlsrv_query( $conn, $sql , $params, $options );

$row_count = sqlsrv_num_rows( $result );

if($row_count > 0){

	while( $row = sqlsrv_fetch_array( $result) ) {

		array_push($elements, array(
			'id' => $row['id'],
			'name' => $row['Nombre']
		));
		
	}

}
else{
	$elements = null;
}

echo json_encode($elements, JSON_FORCE_OBJECT);

sqlsrv_close($conn);

?>