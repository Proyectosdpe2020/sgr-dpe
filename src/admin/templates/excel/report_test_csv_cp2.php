<?php
require '../../../../vendor/autoload.php';
$json_data = json_decode($_POST['data'], true);

$sheet = drawTableByConcept((object) array(
    'json_data' => $json_data,
    'header' => true
))->csv_data;

echo $sheet;
header('Content-Type: text/html; charset=utf-8'); 
header('Content-Encoding: UTF-8');
header('Content-type: application/csv');
header('Content-Disposition: attachment; filename=Customers_Export.csv;');

/*$response =  array(
    'op' => 'ok',
    'file' => "data:application/vnd.ms-excel;base64,".base64_encode($xlsData)
);*/
echo "\xEF\xBB\xBF";

//$writer = new Xlsx($spreadsheet);
ob_start();
ob_end_clean();
//die(json_encode($response));

function drawTableByConcept($attr){

    $csv_data = '';

    if($attr->header){
        $csv_data.= drawTableHeaderByArrayKeys((object) array(
            'json_data' => $attr->json_data
        ))->header;
    }

    foreach($attr->json_data as $data){

        foreach(array_keys($data) as $metadata){

            if(isset($data[$metadata]['date'])){
                $csv_data.= explode(' ', $data[$metadata]['date'] )[0].',';
            }
            else{
                $csv_data.= $data[$metadata].',';
            }

        }

        $csv_data.= '\n';
    }

    return (object) array(
        'csv_data' => $csv_data
    );
}

function drawTableHeaderByArrayKeys($attr){

    $csv_header = '';

    foreach($attr->json_data as $data){
        foreach(array_keys($data) as $metadata){
            $csv_header.= $metadata.',';
        }
        $csv_header.= '\n';
        break;
    }

    return (object) array(
        'header' => $csv_header
    );
}

?>