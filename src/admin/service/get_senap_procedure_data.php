<?php
session_start();
include("../../../service/connection.php");

$conn = $connections['sicapepe']['conn'];

$options = array( "Scrollable" => SQLSRV_CURSOR_KEYSET );

sqlsrv_configure('WarningsReturnAsErrors',0);

$myparams['month'] = 2;
$myparams['year'] = 2021;
$myparams['option'] = 1;

$procedure_params = array(
    array(&$myparams['month'], SQLSRV_PARAM_IN),
    array(&$myparams['year'], SQLSRV_PARAM_IN),
    array(&$myparams['option'], SQLSRV_PARAM_IN)
);

$sql = "EXEC dbo.SENAP19TEST @mes = ?, @anio = ?, @opcion = ?";
$stmt = sqlsrv_prepare($conn, $sql, $procedure_params, $options);

if( sqlsrv_execute( $stmt ) === false ) {

    die( print_r( sqlsrv_errors(), true));

}
else{

    $fields = array();
    $data = array();

    foreach( sqlsrv_field_metadata( $stmt ) as $fieldMetadata ) {
        foreach( $fieldMetadata as $name => $value) {
            if($name == 'Name'){
                array_push($fields, $value);
            }
        }
    }

    while( $row = sqlsrv_fetch_array($stmt) ) {

        $current_row = array();

        for($i=0; $i<count($fields); $i++){

            $current_row += [$fields[$i] => $row[$fields[$i]]];

        }

        array_push($data, $current_row);
        
    }

    echo json_encode($data, JSON_FORCE_OBJECT);

}

sqlsrv_close($conn);

?>