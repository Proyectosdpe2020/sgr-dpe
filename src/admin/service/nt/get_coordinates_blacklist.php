<?php
session_start();
include("../../../../service/connection.php");
include("../common.php");

$conn = $connections['incidencia_sicap']['conn'];

if($conn){

    $sql = "SELECT * FROM [norma_tecnica].[CoordenadasBlackList]";

    $params = array();
    $options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
    $stmt = sqlsrv_query( $conn, $sql , $params, $options );

    $row_count = sqlsrv_num_rows( $stmt );

    $return = array();

    if($row_count != 0){
        
        $fields = array();
        $data = array();

        foreach( sqlsrv_field_metadata( $stmt ) as $fieldMetadata ) {
            foreach( $fieldMetadata as $name => $value) {
                if($name == 'Name'){
                    array_push($fields, $value);
                }
            }
        }

        while($row = sqlsrv_fetch_array($stmt)){

            if(isset($data[$row['Hoja']])){
                
                if(!isset($data[$row['Hoja']][$row['Letra']])){

                    $data[$row['Hoja']] += array($row['Letra'] => array());
                }
                
                array_push($data[$row['Hoja']][$row['Letra']], $row['Posicion']);
            }
            else{

                $data += array($row['Hoja'] => array());
                $data[$row['Hoja']] += array($row['Letra'] => array());
                array_push($data[$row['Hoja']][$row['Letra']], $row['Posicion']);
            }  
        }
            
        $return = array(
            'state' => 'success',
            'data' => $data
        );
    }
    else{
        $return = array(
            'state' => 'not_found',
            'data' => null
        );
    }

    echo json_encode($return, JSON_FORCE_OBJECT);

    sqlsrv_close($conn);
}
else{
    $return = array(
        'state' => 'fail',
        'data' => null
    );

    echo json_encode($return, JSON_FORCE_OBJECT);
}
?>

