<?php
session_start();
include("../../../service/connection.php");

$conn = $connections['incidencia_sicap']['conn'];

$options = array( "Scrollable" => SQLSRV_CURSOR_KEYSET );

//sqlsrv_configure('WarningsReturnAsErrors',0);

/*$myparams['mes'] = $_POST['month'];
$myparams['año'] = $_POST['year'];
$myparams['opcion'] = $_POST['procedure_op'];*/

$myparams['year'] = $_POST['year'];
$myparams['string_option'] = $_POST['procedure_op'];
$myparams['integer_option'] = 0;
$myparams['procedure_option'] = $_POST['procedure'];

/*$procedure_params = array(
    array(&$myparams['mes'], SQLSRV_PARAM_IN),
    array(&$myparams['año'], SQLSRV_PARAM_IN),
    array(&$myparams['opcion'], SQLSRV_PARAM_IN)
);*/

$procedure_params = array(
    array(&$myparams['year'], SQLSRV_PARAM_IN),
    array(&$myparams['string_option'], SQLSRV_PARAM_IN),
    array(&$myparams['integer_option'], SQLSRV_PARAM_IN),
    array(&$myparams['procedure_option'], SQLSRV_PARAM_IN)
);

//$sql = "EXEC dbo.SENAPRETROACTIVO @mes = ?, @anio = ?, @opcion = ?";
$sql = "EXEC dbo.CENSOPROCINTERMEDIO @year = ?, @string_option = ?, @integer_option = ?, @procedure_option = ?";
//$sql = "EXEC dbo.MICRODATATEST @mes = ?, @año = ?, @opcion = ?";
$stmt = sqlsrv_prepare($conn, $sql, $procedure_params, array());

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