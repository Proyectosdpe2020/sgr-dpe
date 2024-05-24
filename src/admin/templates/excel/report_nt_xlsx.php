<?php
header('Content-Type: text/html; charset=utf-8'); 
require '../../../../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;

$reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
$spreadsheet = $reader->load("files/norma_tecnica.xlsx");
$spreadsheet->setActiveSheetIndex(0);
$sheet = $spreadsheet->getActiveSheet();
$json_data = json_decode($_POST['data'], true);

$sheet = clearNTFields((object) array(
    'sheet' => $sheet,
    'json_data' => $json_data,
    'spreadsheet' => $spreadsheet
))->sheet;

$sheet = clearVictimNTFields((object) array(
    'sheet' => $sheet,
    'json_data' => $json_data,
    'spreadsheet' => $spreadsheet
))->sheet;

$sheet = drawData((object) array(
    'sheet' => $sheet,
    'json_data' => $json_data,
    'spreadsheet' => $spreadsheet
))->sheet;

$sheet = drawVictimData((object) array(
    'sheet' => $sheet,
    'json_data' => $json_data,
    'spreadsheet' => $spreadsheet
))->sheet;

$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");

ob_start();

$writer->save('php://output');

header('Content-Encoding: UTF-8');
header('Content-type: application/x-www-form-urlencoded');
header('Content-Transfer-Encoding: Binary');


$xlsData = ob_get_contents();
ob_end_clean();

$response =  array(
    'op' => 'ok',
    'file' => "data:application/vnd.ms-excel;base64,".base64_encode($xlsData)
);

die(json_encode($response));

function drawData($attr){

    $prev_sheet_index = 0;
    $attr->spreadsheet->setActiveSheetIndex(0);
    $attr->sheet = $attr->spreadsheet->getActiveSheet();
    
    foreach($attr->json_data['nt'] as $nt){
        
        if(isset($attr->json_data['sheets'][$nt['idmunicipio']]['Hoja'])){

            if($attr->json_data['sheets'][$nt['idmunicipio']]['Hoja']-1 >= 0){

                if($prev_sheet_index != $attr->json_data['sheets'][$nt['idmunicipio']]['Hoja']-1){

                    $prev_sheet_index = $attr->json_data['sheets'][$nt['idmunicipio']]['Hoja']-1;
                    
                    $attr->spreadsheet->setActiveSheetIndex($attr->json_data['sheets'][$nt['idmunicipio']]['Hoja']-1);
                    $attr->sheet = $attr->spreadsheet->getActiveSheet();
                }
                
                $attr->sheet->setCellValue($attr->json_data['coordinates'][$nt['ClasificacionNormaID']]['Letra'].''.$attr->json_data['coordinates'][$nt['ClasificacionNormaID']]['Posicion'], $nt['total']);
            }
        }
    }

    $attr->spreadsheet->setActiveSheetIndex(0);
    $attr->sheet = $attr->spreadsheet->getActiveSheet();

    return (object) array(
        'sheet' => $attr->sheet
    );
}

function drawVictimData($attr){

    $prev_sheet_index = 0;
    $attr->spreadsheet->setActiveSheetIndex(0);
    $attr->sheet = $attr->spreadsheet->getActiveSheet();
    
    foreach($attr->json_data['victim_nt'] as $nt){
        
        foreach($attr->json_data['victim_classification'] as $victim_class){
        
            if(isset($victim_class['Hoja'])){
    
                if($victim_class['Hoja']-1 >= 0){
    
                    if($prev_sheet_index != $victim_class['Hoja']-1){
    
                        $prev_sheet_index = $victim_class['Hoja']-1;
    
                        $attr->spreadsheet->setActiveSheetIndex($victim_class['Hoja']-1);
                        $attr->sheet = $attr->spreadsheet->getActiveSheet();
                    }

                    $position = $attr->json_data['victim_coordinates'][$nt['ClasificacionNormaID']]['Posicion'];

                    


                    if(!isset($attr->json_data['coordinates_blacklist'][$victim_class['Hoja']][$victim_class['Letra']])){

                        $attr->sheet->setCellValue($victim_class['Letra'].''.$position, $nt[$victim_class['Inciso']]);
                    }
                    else{

                        $flag = false;

                        foreach($attr->json_data['coordinates_blacklist'][$victim_class['Hoja']][$victim_class['Letra']] as $blacklist_element){


                            if($blacklist_element == $position){
                                
                                $flag = true;
                                break;
                            }

                        }

                        if(!$flag){
                            
                            $attr->sheet->setCellValue($victim_class['Letra'].''.$position, $nt[$victim_class['Inciso']]);
                        }



                        
                    }
                    

    /*
                    foreach($attr->json_data['victim_coordinates'] as $coordinate){
    
                        $attr->sheet->setCellValue($victim_class['Letra'].''.$coordinate['Posicion'], 1);

                        echo '$nt[$victim_class["Inciso"]]: '.$victim_class['Inciso'].' - '.$nt[$victim_class['Inciso']];
                    }*/
                }
            }
        }
    }

    $attr->spreadsheet->setActiveSheetIndex(0);
    $attr->sheet = $attr->spreadsheet->getActiveSheet();

    return (object) array(
        'sheet' => $attr->sheet
    );
}

function clearNTFields($attr){

    $prev_sheet_index = 0;
    $attr->spreadsheet->setActiveSheetIndex(0);
    $attr->sheet = $attr->spreadsheet->getActiveSheet();

    foreach($attr->json_data['sheets'] as $muni_sheet){
        
        if(isset($muni_sheet['Hoja'])){

            if($muni_sheet['Hoja']-1 >= 0){

                if($prev_sheet_index != $muni_sheet['Hoja']-1){

                    $prev_sheet_index = $muni_sheet['Hoja']-1;
                    
                    $attr->spreadsheet->setActiveSheetIndex($muni_sheet['Hoja']-1);
                    $attr->sheet = $attr->spreadsheet->getActiveSheet();
                }

                foreach($attr->json_data['coordinates'] as $coordinate){

                    $attr->sheet->setCellValue($coordinate['Letra'].''.$coordinate['Posicion'], 0);
                }
            }
        }
    }

    return (object) array(
        'sheet' => $attr->sheet
    );
}

function clearVictimNTFields($attr){

    $prev_sheet_index = 0;
    $attr->spreadsheet->setActiveSheetIndex(0);
    $attr->sheet = $attr->spreadsheet->getActiveSheet();

    foreach($attr->json_data['victim_classification'] as $victim_class){
        
        if(isset($victim_class['Hoja'])){

            if($victim_class['Hoja']-1 >= 0){

                if($prev_sheet_index != $victim_class['Hoja']-1){

                    $prev_sheet_index = $victim_class['Hoja']-1;

                    $attr->spreadsheet->setActiveSheetIndex($victim_class['Hoja']-1);
                    $attr->sheet = $attr->spreadsheet->getActiveSheet();
                }

                foreach($attr->json_data['victim_coordinates'] as $coordinate){

                    $position = $victim_class['Letra'].''.$coordinate['Posicion'];

                    if(!isset($attr->json_data['coordinates_blacklist'][$victim_class['Hoja']][$victim_class['Letra']])){

                        $attr->sheet->setCellValue($position, 0);
                    }
                    else{

                        $flag = false;

                        foreach($attr->json_data['coordinates_blacklist'][$victim_class['Hoja']][$victim_class['Letra']] as $blacklist_element){


                            if($blacklist_element == $coordinate['Posicion']){

                                
                                $flag = true;
                                break;
                            }

                        }

                        if(!$flag){
                            
                            $attr->sheet->setCellValue($position, 0);
                        }



                        
                    }

                    
                }
            }
        }
    }

    return (object) array(
        'sheet' => $attr->sheet
    );
}
?>