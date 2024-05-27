<?php
session_start();
include("../../../../service/connection.php");
include("../common.php");

$conn = $connections['incidencia_sicap']['conn'];

if($conn){

    $sql = "SELECT * FROM [norma_tecnica].[Coordenadas]";

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

        while($row = sqlsrv_fetch_array($stmt)) {

            $current_row = array();

            for($i=0; $i<count($fields); $i++){

                $current_row += [$fields[$i] => $row[$fields[$i]]];
            }

            $data += array($current_row['ClasificacionNormaID'] => $current_row);
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

