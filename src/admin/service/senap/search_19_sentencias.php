<?php
session_start();
include("../../../../service/connection.php");
include("../common.php");

$conn = $connections['sicapepe']['conn'];

$month = $_POST['month'];
$year = $_POST['year'];

$db_search_conditions = "YEAR(c.FechaInicio) IN ($year) AND MONTH(c.FechaInicio) IN ($month)";

$db_query = "SELECT DISTINCT MONTH(c.FechaInicio) AS 'month', YEAR(c.FechaInicio) AS 'year'
FROM [PRUEBA].[dbo].[Sentencias] senten inner join Carpeta c on senten.CarpetaID = c.CarpetaID";

if($conn){
    $sql = "$db_query WHERE $db_search_conditions";

    $params = array();
    $options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
    $result = sqlsrv_query( $conn, $sql , $params, $options );

    $row_count = sqlsrv_num_rows( $result );

    $json = '';
    $return = array();

    if($row_count != 0){
        while( $row = sqlsrv_fetch_array( $result) ) {
            $json = json_encode($row);
        }
        
        $json = json_decode($json, true);
            
        $return = array(
            'state' => 'success',
            'data' => null
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
