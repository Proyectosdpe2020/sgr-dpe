<?php

// Obtener fecha_final y construir nombre dinámico
$nombreArchivo = "Consulta_CNI_delitos.xlsx"; // Valor por defecto

if (isset($_POST['fecha_final'])) {
    $fechaFinal = DateTime::createFromFormat('Y-m-d', $_POST['fecha_final']);
    if ($fechaFinal) {
        $anio = $fechaFinal->format('Y');
        $mes = $fechaFinal->format('m');
        $nombreArchivo = "16_{$anio}{$mes}_delitos.xlsx";
    }
}

ini_set('memory_limit', '2048M');
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment;filename=\"$nombreArchivo\"");
header('Cache-Control: max-age=0');

require('D:/xampp/htdocs/sgr-dpe/vendor/autoload.php');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

// Validar que se recibió data por POST
if (!isset($_POST['data'])) {
    die("No se recibió información para generar el Excel.");
}

// Decodificar los datos recibidos desde sesnsp.js
$data2 = json_decode($_POST['data'], true);

if (!is_array($data2) || empty($data2)) {
    die("Datos inválidos o vacíos.");
}

$spreadsheet = new Spreadsheet();
$sheet2 = $spreadsheet->getActiveSheet();
$sheet2->setTitle('Delitos');

// Encabezados de la tabla (a partir de la fila 1)
$rowNum2 = 1; // Comenzar en la fila 1
$sheet2->setCellValue('A' . $rowNum2, 'ID_CI');
$sheet2->setCellValue('B' . $rowNum2, 'ID_DELITO');
$sheet2->setCellValue('C' . $rowNum2, 'DTO');
// $sheet2->setCellValue('D' . $rowNum2, 'DTO_PRIN');
$sheet2->setCellValue('D' . $rowNum2, 'MODA_DTO');
$sheet2->setCellValue('E' . $rowNum2, 'FORMA_ACC');
$sheet2->setCellValue('F' . $rowNum2, 'FHA_DE_HCHOS');
$sheet2->setCellValue('G' . $rowNum2, 'HRA_DE_HCHOS');
$sheet2->setCellValue('H' . $rowNum2, 'EMTO_COM_DTO');
$sheet2->setCellValue('I' . $rowNum2, 'GRDO_CONS');
$sheet2->setCellValue('J' . $rowNum2, 'CLASF_DE_DTO');
$sheet2->setCellValue('K' . $rowNum2, 'NOM_ENT_HCHOS');
$sheet2->setCellValue('L' . $rowNum2, 'ID_ENT_HCHOS');
$sheet2->setCellValue('M' . $rowNum2, 'NOM_MUN_HCHOS');
$sheet2->setCellValue('N' . $rowNum2, 'ID_MUN_HCHOS');
$sheet2->setCellValue('O' . $rowNum2, 'NOM_LOC_HCHOS');
$sheet2->setCellValue('P' . $rowNum2, 'ID_LOC_HCHOS');
$sheet2->setCellValue('Q' . $rowNum2, 'NOM_COL_HCHOS');
$sheet2->setCellValue('R' . $rowNum2, 'ID_COL_HCHOS');
$sheet2->setCellValue('S' . $rowNum2, 'CP');
$sheet2->setCellValue('T' . $rowNum2, 'COORD_X');
$sheet2->setCellValue('U' . $rowNum2, 'COORD_Y');
$sheet2->setCellValue('V' . $rowNum2, 'DOM_HCHOS');

// Estilo para encabezados
$sheet2->getStyle("A$rowNum2:V$rowNum2")->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
$sheet2->getStyle("A$rowNum2:V$rowNum2")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('152f4a');
$sheet2->getStyle("A$rowNum2:V$rowNum2")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Ajuste de altura para encabezados
$sheet2->getRowDimension($rowNum2)->setRowHeight(20);

// Filas con datos
$rowNum2++;
$colorAlterno = true;
$contador = 1;

// Rellenar las filas con los datos
foreach ($data2 as $row2) {
    $sheet2->setCellValue('A' . $rowNum2, $row2['Id']);
    $sheet2->setCellValue('B' . $rowNum2, $row2['id_delito']);
    $sheet2->setCellValue('C' . $rowNum2, $row2['Delito']);
    // $sheet2->setCellValue('D' . $rowNum2, $row2['Delito principal']);
    $sheet2->setCellValue('D' . $rowNum2, $row2['Modalidad']);
    $sheet2->setCellValue('E' . $rowNum2, $row2['Forma (violencia)']);
    $sheet2->setCellValue('F' . $rowNum2, $row2['Fecha Hechos']);
    $sheet2->setCellValue('G' . $rowNum2, $row2['Hora Hechos']);
    $sheet2->setCellValue('H' . $rowNum2, $row2['Elemento de comision']);
    $sheet2->setCellValue('I' . $rowNum2, $row2['Cosumacion']);
    //$sheet2->setCellValue('K' . $rowNum2, $row2['elemento_clasificacion']);
    $sheet2->setCellValueExplicit('J' . $rowNum2, $row2['elemento_clasificacion'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    $sheet2->setCellValue('K' . $rowNum2, $row2['Entidad federativa']);
    $sheet2->setCellValue('L' . $rowNum2, $row2['ID_Entidad_federativa']);
    $sheet2->setCellValue('M' . $rowNum2, $row2['Municipio del Hecho']);
    $sheet2->setCellValue('N' . $rowNum2, $row2['Identificador fiscalia']);
    $sheet2->setCellValue('O' . $rowNum2, $row2['Localidad']);
    $sheet2->setCellValue('P' . $rowNum2, $row2['ID_Localidad']);
    $sheet2->setCellValue('Q' . $rowNum2, $row2['Colonia']);
    $sheet2->setCellValue('R' . $rowNum2, $row2['ID_Colonia']);
    $sheet2->setCellValue('S' . $rowNum2, $row2['Codigo_postal']);
    $sheet2->setCellValue('T' . $rowNum2, $row2['longitud']);
    $sheet2->setCellValue('U' . $rowNum2, $row2['latitud']);
    $sheet2->setCellValue('V' . $rowNum2, $row2['Domicilio de los hechos']);

    // Alternar colores de fondo
    if ($colorAlterno) {
        // Gris claro
        $sheet2->getStyle('A' . $rowNum2 . ':V' . $rowNum2)
            ->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()
            ->setRGB('C6C6C6');
    } else {
        // Blanco
        $sheet2->getStyle('A' . $rowNum2 . ':V' . $rowNum2)
            ->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()
            ->setRGB('FFFFFF');
    }

    // Cambiar el valor de $colorAlterno para la siguiente fila
    $colorAlterno = !$colorAlterno;

    // Incrementar la fila
    $contador++;
    $rowNum2++;
}

// Aplicar bordes a todas las celdas de la tabla
$sheet2->getStyle('A1:V' . ($rowNum2 - 1))->applyFromArray([
    'borders' => [
        'allBorders' => [
            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
            'color' => ['argb' => '646464'],
        ],
    ],
]);

// Centrar texto horizontal y verticalmente
$sheet2->getStyle('A1:V' . ($rowNum2 - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
$sheet2->getStyle('A1:V' . ($rowNum2 - 1))->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

// Encabezados en negritas
$sheet2->getStyle('A1:V1')->getFont()->setBold(true);

// Ajustar anchos de columna
$sheet2->getColumnDimension('B')->setWidth(22); // Id_delito
$sheet2->getColumnDimension('C')->setWidth(110); // Delito
// $sheet2->getColumnDimension('D')->setWidth(15); // Delito principal
$sheet2->getColumnDimension('D')->setWidth(20); // Modalidad
$sheet2->getColumnDimension('E')->setWidth(20); // Forma (violencia)
$sheet2->getColumnDimension('F')->setWidth(18); // Fecha Hechos
$sheet2->getColumnDimension('G')->setWidth(18); // Hora Hechos
$sheet2->getColumnDimension('H')->setWidth(20); // Elemento de comisión
$sheet2->getColumnDimension('I')->setWidth(17); // Consumación
$sheet2->getColumnDimension('J')->setWidth(20); // Elemento clasificación
$sheet2->getColumnDimension('K')->setWidth(22); // Entidad federativa
$sheet2->getColumnDimension('L')->setWidth(20); // ID Entidad federativa
$sheet2->getColumnDimension('M')->setWidth(25); // Municipio del Hecho
$sheet2->getColumnDimension('N')->setWidth(20); // Identificador fiscalía
$sheet2->getColumnDimension('O')->setWidth(50); // Localidad
$sheet2->getColumnDimension('P')->setWidth(18); // ID Localidad
$sheet2->getColumnDimension('Q')->setWidth(35); // Colonia
$sheet2->getColumnDimension('R')->setWidth(20); // ID Colonia
$sheet2->getColumnDimension('S')->setWidth(15); // Código postal
$sheet2->getColumnDimension('T')->setWidth(20); // Longitud
$sheet2->getColumnDimension('U')->setWidth(20); // Latitud
$sheet2->getColumnDimension('V')->setWidth(157); // Domicilio de los hechos

// Crear el writer de Excel y enviar al navegador
$spreadsheet->setActiveSheetIndexByName('Delitos');
$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
ob_end_clean();
$writer->save('php://output'); // Esto enviará el archivo directamente al navegador
exit;
