<?php
header('Content-Type: text/html; charset=utf-8'); 

require '../../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
//use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

use PhpOffice\PhpSpreadsheet\Writer\Csv;
//$spreadsheet = new Csv();
//$reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle("Investigaciónes");
//$drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();

$json_data = json_decode($_POST['data'], true);

$letters = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ');

$sheet = drawTableByConcept((object) array(
    'sheet' => $sheet,
    'letters' => $letters,
    'letter_index' => 0,
    'json_data' => $json_data,
    'current_row' => 1,
    'header' => true
))->sheet;

/*

$sheet = drawTableByConcept((object) array(
    'sheet' => $sheet,
    'letters' => getArrayLettersByArrayKeys((object) array(
        'json_data' => $json_data
    )),
    'letter_index' => 0,
    'json_data' => $json_data,
    'current_row' => 1,
    'header' => true
))->sheet; */

/*__________________________________________________________________________________________________________________________________*/

/*header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="GeneratedFile.xlsx"');
header('Cache-Control: max-age=0');*/


//header('content-type:application/csv;charset=UTF-8');

header('Content-Encoding: UTF-8');
header('Content-type: application/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename=Customers_Export.csv');

echo "\xEF\xBB\xBF";

//$writer = new Xlsx($spreadsheet);
$writer = new \PhpOffice\PhpSpreadsheet\Writer\Csv($spreadsheet);
ob_start();
$writer->save('php://output');
$xlsData = ob_get_contents();
ob_end_clean();

$response =  array(
    'op' => 'ok',
    'file' => "data:application/vnd.ms-excel;base64,".base64_encode($xlsData)
);

die(json_encode($response));

function drawTableByConcept($attr){

    if($attr->header){
        $attr->current_row = drawTableHeaderByArrayKeys((object) array(
            'sheet' => $attr->sheet,
            'letters' => $attr->letters,
            'letter_index' => $attr->letter_index,
            'json_data' => $attr->json_data,
            'current_row' => $attr->current_row
        ))->current_row;
    }

    $initial_letter_index = $attr->letter_index;

    foreach($attr->json_data as $data){

        foreach(array_keys($data) as $metadata){

            if(isset($data[$metadata]['date'])){
                $attr->sheet->setCellValue($attr->letters[$attr->letter_index].''.$attr->current_row, explode(' ', $data[$metadata]['date'] )[0]);
            }
            else{
                $attr->sheet->setCellValue($attr->letters[$attr->letter_index].''.$attr->current_row, $data[$metadata]);
            }

            $attr->letter_index++;

        }

        $attr->letter_index = $initial_letter_index;
        $attr->current_row++;
    }

    return (object) array(
        'sheet' => $attr->sheet,
        'current_row' => $attr->current_row
    );
}

function drawTableHeaderByArrayKeys($attr){
    foreach($attr->json_data as $data){
        foreach(array_keys($data) as $metadata){
            $attr->sheet->setCellValue($attr->letters[$attr->letter_index].''.$attr->current_row, $metadata);
            $attr->letter_index++;
        }
        break;
    }

    return (object) array(
        'sheet' => $attr->sheet,
        'current_row' => $attr->current_row+1
    );
}

function getArrayLettersByArrayKeys($attr){

    $main_letters = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
    $count_keys = 0;
    $letters = array();
    $depurated_letters = array();
    $current_letter = '';

    foreach($attr->json_data as $data){
        foreach(array_keys($data) as $metadata){
            $count_keys++;
        }
        break;
    }

    $letters = $main_letters;

    if($count_keys < count($main_letters)){
        
        foreach($main_letters as $first_letter){

            foreach($main_letters as $second_letter){
                array_push($letters, $first_letter.''.$second_letter);
            }
        }
    }

    $i = 0;

    foreach($letters as $letter){
        if($i < $count_keys){
            array_push($depurated_letters, $letter);
        }
        else{
            break;
        }
        $i++;
    }

    return (object) array(
        'letters' => $depurated_letters
    );
}


?>