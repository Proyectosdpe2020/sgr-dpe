<?php
session_start();
include("../../../../service/connection.php");

$conn = $connections['sicap']['conn'];

$options = array("Scrollable" => SQLSRV_CURSOR_KEYSET);

$myparams['fecha_inicio'] = $_POST['fecha_inicial'];
$myparams['fechai_fin'] = $_POST['fecha_final'];
$myparams['tipo'] = $_POST['type'];

$procedure_params = array(
    array(&$myparams['fecha_inicio'], SQLSRV_PARAM_IN),
    array(&$myparams['fechai_fin'], SQLSRV_PARAM_IN),
    array(&$myparams['tipo'], SQLSRV_PARAM_IN)
);

$sql = "EXEC dbo.nueva_norma_tecnica @fecha_inicio = ?, @fechai_fin = ?, @tipo = ?;";
$stmt = sqlsrv_prepare($conn, $sql, $procedure_params, array());

if (sqlsrv_execute($stmt) === false) {

    die(print_r(sqlsrv_errors(), true));
} else {

    $fields = array();
    $data = array();

    foreach (sqlsrv_field_metadata($stmt) as $fieldMetadata) {
        foreach ($fieldMetadata as $name => $value) {
            if ($name == 'Name') {
                array_push($fields, $value);
            }
        }
    }

    while ($row = sqlsrv_fetch_array($stmt)) {

        $current_row = array();

        for ($i = 0; $i < count($fields); $i++) {

            $current_row += [$fields[$i] => $row[$fields[$i]]];
        }

        array_push($data, $current_row);
    }

    echo json_encode($data, JSON_FORCE_OBJECT);
}

sqlsrv_close($conn);
