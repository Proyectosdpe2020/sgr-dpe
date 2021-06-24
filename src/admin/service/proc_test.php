<?php
session_start();
include("../../../service/connection.php");

$conn = $connections['sicapepe']['conn'];

$options = array( "Scrollable" => SQLSRV_CURSOR_KEYSET );

sqlsrv_configure('WarningsReturnAsErrors',0);

$myparams['mes'] = 2;
$myparams['anio'] = 2021;
$myparams['opcion'] = 1;

$procedure_params = array(
    array(&$myparams['mes'], SQLSRV_PARAM_IN),
    array(&$myparams['anio'], SQLSRV_PARAM_IN),
    array(&$myparams['opcion'], SQLSRV_PARAM_IN)
);


//EXEC the procedure, {call stp_Create_Item (@Item_ID = ?, @Item_Name = ?)} seems to fail with various errors in my experiments
$sql = "EXEC dbo.SENAP19TEST @mes = ?, @anio = ?, @opcion = ?";
$stmt = sqlsrv_prepare($conn, $sql, $procedure_params, $options);




/*$sql = "{call dbo.PT2(?,?)}";

$params = array(2, 2021); 

$stmt = sqlsrv_prepare($conn, $sql, $params, $options);*/

if( sqlsrv_execute( $stmt ) === false ) {

    die( print_r( sqlsrv_errors(), true));

}
else{

    //print_r(sqlsrv_fetch_array($stmt));

}

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

sqlsrv_close($conn);

?>