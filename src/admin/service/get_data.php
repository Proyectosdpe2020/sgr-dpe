<?php
session_start();
include("../../../service/connection.php");

$conn = $connections['sicapepe']['conn'];
$db_table = '[dbo].[Usuario]';

$data = array();

$query = "";


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

?>