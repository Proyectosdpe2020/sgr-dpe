<?php
    if(isset( $_POST['data']))
        $data = $_POST['data'];
    else
        $data = 'null';
?>

<?php
    if($data != 'null'){
?>

<table class="data-table table table-striped">
    <tr>
        <th>#</th>

<?php
        foreach(json_decode($data, true) as $element){
            foreach(array_keys($element) as $key){
?>
        <th><?php echo $key; ?></th>
<?php
            }
            break;
        }
?>

    </tr>
<?php
        $i=1;
        foreach(json_decode($data, true) as $element){
?> 
    <tr>
        <td><?php echo $i; ?></td>
<?php
            foreach($element as $sub){
?>
        <td><?php echo $sub; ?></td>
<?php
            }
?>
    </tr>
<?php
            $i++;
        }
?>
</table>

<?php

    }
    else{
?>
    <h2>No hay registros</h2>
<?php

    }
?>

<?php
header('Content-Type: text/html; charset=utf-8'); 

include ("../../../Conexiones/Conexion.php");
require '../../../vendors/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$general_data_fiscalias = json_decode($_POST['general_data'], true);
$arresteds_by_fiscalias = json_decode($_POST['arresteds_by_fiscalia'], true);
$drugs_by_fiscalias = json_decode($_POST['drugs_by_fiscalia'], true);
$weapons_by_fiscalias = json_decode($_POST['weapons_by_fiscalia'], true);
//$vehicles_by_fiscalias = json_decode($_POST['vehicles_by_fiscalia'], true);
$injunctions_by_fiscalias = json_decode($_POST['injunctions_by_fiscalia'], true);
$bands_by_fiscalias = json_decode($_POST['bands_by_fiscalia'], true);
$vehicle_data_by_fiscalias = json_decode($_POST['vehicle_data_by_fiscalia'], true);
$motocicle_data_by_fiscalias = json_decode($_POST['motocicle_data_by_fiscalia'], true);
$month = $_POST['month'];
$year = $_POST['year'];

$fiscalias = array("APATZINGAN", "COALCOMAN", "HUETAMO", "JIQUILPAN", "LA PIEDAD", "LAZARO CARDENAS", "MORELIA", "URUAPAN", "ZAMORA", "ZITACUARO");
$letters = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
$months = array('Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre');

/*__________________________________________________________________________________________________________________________________*/

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle("Investigaciónes");
$drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();

$array_return = setHeader($drawing, $sheet, $month, $year, $months);
$sheet = $array_return['sheet'];
$current_row = $array_return['current_row'];

$array_return = drawSimpleLine($sheet, 'A', $current_row, 'Investigaciones cumplidas');
$sheet = $array_return['sheet'];
$current_row = $array_return['current_row'];


$json_data = $general_data_fiscalias;
$sheet = drawSimpleTableRows($sheet, $letters, $fiscalias, $json_data, $current_row, 'investigations', true, 'Investigaciónes')['sheet'];

/*__________________________________________________________________________________________________________________________________*/

$spreadsheet->createSheet();
$spreadsheet->setActiveSheetIndex(1);
$sheet = $spreadsheet->getActiveSheet()->setTitle('Mandamientos');
$drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();

$array_return = setHeader($drawing, $sheet, $month, $year, $months);
$sheet = $array_return['sheet'];
$current_row = $array_return['current_row'];

$array_return = drawSimpleLine($sheet, 'A', $current_row, 'Mandamientos judiciales cumplidos por policía ministerial');
$sheet = $array_return['sheet'];
$current_row = $array_return['current_row'];

$json_data = $injunctions_by_fiscalias;
$sheet = drawTableByConcept($sheet, $letters, $fiscalias, $json_data, $current_row, 'injunction', true, true)['sheet'];

/*__________________________________________________________________________________________________________________________________*/


$spreadsheet->createSheet();
$spreadsheet->setActiveSheetIndex(2);
$sheet = $spreadsheet->getActiveSheet()->setTitle('Detenidos');
$drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();

$array_return = setHeader($drawing, $sheet, $month, $year, $months);
$sheet = $array_return['sheet'];
$current_row = $array_return['current_row'];

$array_return = drawSimpleLine($sheet, 'A', $current_row, 'Detenidos por delito');
$sheet = $array_return['sheet'];
$current_row = $array_return['current_row'];


$json_data = $arresteds_by_fiscalias;
$sheet = drawTableByConcept($sheet, $letters, $fiscalias, $json_data, $current_row, 'arrested', true, true)['sheet'];

/*__________________________________________________________________________________________________________________________________*/

$spreadsheet->createSheet();
$spreadsheet->setActiveSheetIndex(3);
$sheet = $spreadsheet->getActiveSheet()->setTitle('Bandas');
$drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();

$array_return = setHeader($drawing, $sheet, $month, $year, $months);
$sheet = $array_return['sheet'];
$current_row = $array_return['current_row'];

$array_return = drawSimpleLine($sheet, 'A', $current_row, 'Bandas desmanteladas');
$sheet = $array_return['sheet'];
$current_row = $array_return['current_row'];

$json_data = $bands_by_fiscalias;
$sheet = drawTableByConcept($sheet, $letters, $fiscalias, $json_data, $current_row, 'band', true, true)['sheet'];


/*__________________________________________________________________________________________________________________________________*/

$spreadsheet->createSheet();
$spreadsheet->setActiveSheetIndex(4);
$sheet = $spreadsheet->getActiveSheet()->setTitle('Estado Completo');
$drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();

$array_return = setHeader($drawing, $sheet, $month, $year, $months);
$sheet = $array_return['sheet'];
$current_row = $array_return['current_row'];

$array_return = drawSimpleLine($sheet, 'A', $current_row, 'Aseguramientos Diversos');
$sheet = $array_return['sheet'];
$current_row = $array_return['current_row'];

$json_data = $weapons_by_fiscalias;
$array_return = drawTableByConcept($sheet, $letters, $fiscalias, $json_data, $current_row, 'weapon', true, false);
$sheet = $array_return['sheet'];
$current_row = $array_return['current_row'];

$json_data = $general_data_fiscalias;
$array_return = drawSimpleTableRows($sheet, $letters, $fiscalias, $json_data, $current_row, 'insured_money_MXN', false, 'Moneda Nacional');
$sheet = $array_return['sheet'];
$current_row = $array_return['current_row'];

/*------------------------------------*/

$array_return = drawSimpleLine($sheet, 'A', $current_row, 'Droga Asegurada');
$sheet = $array_return['sheet'];
$current_row = $array_return['current_row'];


$json_data = $drugs_by_fiscalias;
$array_return = drawTableByConcept($sheet, $letters, $fiscalias, $json_data, $current_row, 'drug', true, true);
$sheet = $array_return['sheet'];
$current_row = $array_return['current_row'];

/*------------------------------------*/

$array_return = drawSimpleLine($sheet, 'A', $current_row, 'Otras Actividades');
$sheet = $array_return['sheet'];
$current_row = $array_return['current_row'];

$json_data = $general_data_fiscalias;
$array_return = drawSimpleTableRows($sheet, $letters, $fiscalias, $json_data, $current_row, 'cateos', true, 'Cateos');
$sheet = $array_return['sheet'];
$current_row = $array_return['current_row'];

$array_return = drawSimpleTableRows($sheet, $letters, $fiscalias, $json_data, $current_row, 'recorridos', false, 'Recorridos');
$sheet = $array_return['sheet'];
$current_row = $array_return['current_row'];

$json_data = $arresteds_by_fiscalias;
$array_return = drawSimpleTableTotalRows($sheet, $letters, $fiscalias, $json_data, $current_row, 'arrested', false, 'Detenidos');
$sheet = $array_return['sheet'];
$current_row = $array_return['current_row'];

$json_data = $bands_by_fiscalias;
$array_return = drawSimpleTableTotalRows($sheet, $letters, $fiscalias, $json_data, $current_row, 'band', false, 'Bandas Desmanteladas');
$sheet = $array_return['sheet'];
$current_row = $array_return['current_row'];

/*------------------------------------*/

$array_return = drawSimpleLine($sheet, 'A', $current_row, 'Automoviles');
$sheet = $array_return['sheet'];
$current_row = $array_return['current_row'];

$json_data = $vehicle_data_by_fiscalias;
$array_return = drawTableByConcept($sheet, $letters, $fiscalias, $json_data, $current_row, 'vehicle_data', true, true);
$sheet = $array_return['sheet'];
$current_row = $array_return['current_row'];

/*------------------------------------*/

$array_return = drawSimpleLine($sheet, 'A', $current_row, 'Motocicletas');
$sheet = $array_return['sheet'];
$current_row = $array_return['current_row'];

$json_data = $motocicle_data_by_fiscalias;
$array_return = drawTableByConcept($sheet, $letters, $fiscalias, $json_data, $current_row, 'motocicle_data', true, true);
$sheet = $array_return['sheet'];
$current_row = $array_return['current_row'];

/*__________________________________________________________________________________________________________________________________*/

header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="GeneratedFile.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
ob_start();
$writer->save('php://output');
$xlsData = ob_get_contents();
ob_end_clean();

$response =  array(
        'op' => 'ok',
        'file' => "data:application/vnd.ms-excel;base64,".base64_encode($xlsData)
    );

die(json_encode($response));



function setHeader($drawing, $sheet, $month, $year, $months){
    $drawing->setName('Logo');
    $drawing->setDescription('Logo');
    $drawing->setPath('../../../images/logoFiscaliaTrimesReporte.png');
    $drawing->setHeight(150);
    $drawing->setWidth(1180);
    $drawing->setWorksheet($sheet);

    $sheet->setCellValue('A8', "TRABAJO REALIZADO POR LA POLICÍA DE INVESTIGACIÓN");
    
    $sheet->setCellValue('A9', 'PERIODO: '.$months[$month-1].' del '.$year);

    return array(
        'drawing' => $drawing,
        'sheet' => $sheet,
        'current_row' => 11
    );
}

function drawSimpleTableRows($sheet, $letters, $fiscalias, $json_data, $current_row, $concept, $header, $concept_name){
    
    if($header){
        $current_row = drawTableHeader($sheet, $letters, $fiscalias, $current_row)['current_row'];
    }

    $i = 1;
    $current_row_before_table = $current_row;

    $sheet->setCellValue($letters[0].''.$current_row, $concept_name);

    foreach($json_data as $data){
        $sheet->setCellValue($letters[$i].''.$current_row, $data[$concept]);

        $sheet->getStyle($letters[$i].''.$current_row)->getAlignment()->setHorizontal('center');

        $i++;

        if($i == count($json_data)){
            $sheet->getStyle($letters[$i].''.$current_row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('8CFFC5');
        }
        
    }

    return array(
        'sheet' => $sheet,
        'current_row' => $current_row+1
    );
}

function drawTableByConcept($sheet, $letters, $title_headers, $json_data, $current_row, $concept, $header, $total){
    
    if($header){
        $current_row = drawTableHeader($sheet, $letters, $title_headers, $current_row)['current_row'];
    }

    $i = 1;
    $current_row_before_table = $current_row;
    $current_row_after_table = $current_row;

    foreach($json_data as $data){

        foreach(array_keys($data[$concept]) as $consept_metadata){

            if($total && $consept_metadata == 'Total' || !$total && $consept_metadata != 'Total' || $total && $consept_metadata != 'Total'){

                $sheet->setCellValue($letters[0].''.$current_row, $consept_metadata);
                $sheet->getStyle($letters[$i].''.$current_row)->getAlignment()->setHorizontal('center');
                $sheet->setCellValue($letters[$i].''.$current_row, $data[$concept][$consept_metadata]);
                

                if($i == count($json_data)){
                    $sheet->getStyle($letters[$i].''.$current_row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('8CFFC5');
                }

                $current_row++;
            }

        }

        if($consept_metadata == 'Total'){
            if($i != count($json_data) && $total){
                $sheet->getStyle($letters[$i].''.($current_row-1))->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('8CFFC5');
            }
            else if($total){
                $sheet->getStyle($letters[0].''.($current_row-1))->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('2EC378');
                $sheet->getStyle($letters[$i].''.($current_row-1))->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('2EC378');
            }
        }

        $current_row_after_table = $current_row;
        $current_row = $current_row_before_table;
        $i++;
    }

    return array(
        'sheet' => $sheet,
        'current_row' => $current_row_after_table
    );
}

function drawTableHeader($sheet, $letters, $title_headers, $current_row){

    $sheet->getColumnDimension($letters[0])->setWidth(40);
    $sheet->getStyle($letters[0].''.$current_row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('7C8B9E');
    $sheet->getStyle($letters[0].''.$current_row)->getAlignment()->setHorizontal('center');
    $sheet->getStyle($letters[0].''.$current_row)->getFont()->getColor()->setARGB('FFFFFF');
    $sheet->setCellValue($letters[0].''.$current_row, 'CONCEPTO');

    for($i = 0; $i < count($title_headers); $i++){
        $sheet->getColumnDimension($letters[$i+1])->setWidth(20);
        $sheet->getStyle($letters[$i+1].''.$current_row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('7C8B9E');
        $sheet->getStyle($letters[$i+1].''.$current_row)->getAlignment()->setHorizontal('center');
        $sheet->getStyle($letters[$i+1].''.$current_row)->getFont()->getColor()->setARGB('FFFFFF');
        $sheet->setCellValue($letters[$i+1].''.$current_row, $title_headers[$i]);
    }

    $sheet->getColumnDimension($letters[count($title_headers)+1])->setWidth(15);
    $sheet->getStyle($letters[count($title_headers)+1].''.$current_row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('E9CFB8');
    $sheet->getStyle($letters[count($title_headers)+1].''.$current_row)->getAlignment()->setHorizontal('center');
    $sheet->getStyle($letters[count($title_headers)+1].''.$current_row)->getFont()->getColor()->setARGB('FFFFFF');
    $sheet->setCellValue($letters[count($title_headers)+1].''.$current_row, 'TOTAL');

    return array(
        'sheet' => $sheet,
        'current_row' => $current_row+1
    );
}

function drawSimpleLine($sheet, $col, $current_row, $text){
    
    $current_row++;
    $sheet->setCellValue($col.''.$current_row, $text);
    $sheet->getStyle($col.''.$current_row)->getFont()->setBold(true);

    return array(
        'sheet' => $sheet,
        'current_row' => $current_row+1
    );
}

function drawSimpleTableTotalRows($sheet, $letters, $title_headers, $json_data, $current_row, $concept, $header, $concept_name){
    
    if($header){
        $current_row = drawTableHeader($sheet, $letters, $title_headers, $current_row)['current_row'];
    }

    $i = 1;
    $current_row_before_table = $current_row;

    $sheet->setCellValue($letters[0].''.$current_row, $concept_name);

    foreach($json_data as $data){

        $sheet->getStyle($letters[$i].''.$current_row)->getAlignment()->setHorizontal('center');
        $sheet->setCellValue($letters[$i].''.$current_row, $data[$concept]['Total']);

        if($i == count($json_data)){
            $sheet->getStyle($letters[$i].''.($current_row))->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('8CFFC5');
        }

        $i++;
        
    }

    return array(
        'sheet' => $sheet,
        'current_row' => $current_row+1
    );
}




?>