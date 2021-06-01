<?php
session_start();
include("../../../service/connection.php");

$conn = $connections['cmasc']['conn'];
$db_table = '[dbo].[Usuario]';

$data = array();

$query = "SELECT * FROM $db_table";

$params = array();
$options = array( "Scrollable" => SQLSRV_CURSOR_KEYSET );





// specify params - MUST be a variable that can be passed by reference!
$myparams['mes'] = 2;
$myparams['anio'] = 2021;

// Set up the proc params array - be sure to pass the param by reference
$procedure_params = array(
array(&$myparams['mes'], SQLSRV_PARAM_IN),
array(&$myparams['anio'], SQLSRV_PARAM_IN)
);

//$sql = "{call dbo.PNT (@Item_ID = ?, @Item_Name = ?)}";

$sql = "EXEC dbo.PNT @mes = ?, @anio = ?";

$stmt = sqlsrv_prepare($conn, $sql, $procedure_params);


/*$row_count = sqlsrv_num_rows( $stmt );

$fields = array();
foreach( sqlsrv_field_metadata( $stmt ) as $fieldMetadata ) {
    foreach( $fieldMetadata as $name => $value) {
        if($name == 'Name'){
            array_push($fields, $value);
        }
    }
}*/


//echo json_encode($stmt, JSON_FORCE_OBJECT);

if(sqlsrv_execute($stmt)){
    //echo json_encode(sqlsrv_next_result($stmt), JSON_FORCE_OBJECT);
    while($res = sqlsrv_next_result($stmt)){
      // make sure all result sets are stepped through, since the output params may not be set until this happens
      echo $res['DelitoID'];
      echo json_encode($res, JSON_FORCE_OBJECT);
    }
    // Output params are now set,
    /*print_r($params);
    print_r($myparams);*/
  }else{
    die( print_r( sqlsrv_errors(), true));
  }




//$result = sqlsrv_query( $conn, $query , $params, $options );

/*$row_count = sqlsrv_num_rows( $result );

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
}*/


echo json_encode($data, JSON_FORCE_OBJECT);

sqlsrv_close($conn);

?>