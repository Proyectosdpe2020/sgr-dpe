<?php
session_start();
include("../../../service/connection.php");

$params = array();
$options = array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
$conn = $connections['sicap']['conn'];
$db_table = '[cat].[TipoConclusion]';

$month = $_POST['month'];
$year = $_POST['year'];

$elements = array();

$sql = "";

$result = sqlsrv_query( $conn, $sql , $params, $options );

$row_count = sqlsrv_num_rows( $result );

if($row_count > 0){

	while( $row = sqlsrv_fetch_array( $result) ) {

		array_push($elements, array(
			'id' => $row['Nombre de la Fiscalia Regional o Especializada'],
			'name' => $row['Tipo Fiscalia']
		));
		
	}

}
else{
	$elements = null;
}

echo json_encode($elements, JSON_FORCE_OBJECT);

sqlsrv_close($conn);

?>
