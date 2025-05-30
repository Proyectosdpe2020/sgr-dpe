<?php

// Obtener fecha_final y construir nombre dinámico
$nombreArchivo = "Consulta_CNI_carpetas.xlsx"; // Valor por defecto

if (isset($_POST['fecha_final'])) {
    $fechaFinal = DateTime::createFromFormat('Y-m-d', $_POST['fecha_final']);
    if ($fechaFinal) {
        $anio = $fechaFinal->format('Y');
        $mes = $fechaFinal->format('m');
        $nombreArchivo = "16_{$anio}{$mes}_carpetas.xlsx";
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
$data = json_decode($_POST['data'], true);

if (!is_array($data) || empty($data)) {
    die("Datos inválidos o vacíos.");
}

// Crear hoja de cálculo
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Carpetas');

// Encabezados
$sheet->setCellValue('A1', 'ID_CI');
$sheet->setCellValue('B1', 'NTRA_CI');
$sheet->setCellValue('C1', 'FHA_DE_INI');
$sheet->setCellValue('D1', 'HRA_DE_INI');
$sheet->setCellValue('E1', 'ID_TEXP');
$sheet->setCellValue('F1', 'RMEN_DE_HCHOS');

// Estilo encabezados
$sheet->getStyle("A1:F1")->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
$sheet->getStyle("A1:F1")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('152f4a');
$sheet->getStyle("A1:F1")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle("A1:F1")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
$sheet->getRowDimension(1)->setRowHeight(20);

// Cuerpo
$rowNum = 2;
$colorAlterno = true;

foreach ($data as $row) {
    $sheet->setCellValue('A' . $rowNum, $row['Id']);
    $sheet->setCellValueExplicit('B' . $rowNum, $row['Nomenglatura_carpeta'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    $sheet->setCellValue('C' . $rowNum, $row['Fecha Inicio']);
    $sheet->setCellValue('D' . $rowNum, $row['Hora Inicio']);
    $sheet->setCellValue('E' . $rowNum, $row['Tipo de expediente']);
    $sheet->setCellValue('F' . $rowNum, $row['hechos']);
    $sheet->getStyle('F' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

    $sheet->getStyle("A$rowNum:F$rowNum")->getFill()->setFillType(Fill::FILL_SOLID)
        ->getStartColor()->setRGB($colorAlterno ? 'C6C6C6' : 'FFFFFF');
    $colorAlterno = !$colorAlterno;
    $rowNum++;
}

// Bordes
$sheet->getStyle("A1:F" . ($rowNum - 1))->applyFromArray([
    'borders' => [
        'allBorders' => [
            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
            'color' => ['argb' => '646464'],
        ],
    ],
]);

// Ajustes finales
$sheet->getStyle("A1:F" . ($rowNum - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle("A1:F" . ($rowNum - 1))->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
$sheet->getColumnDimension('B')->setWidth(22);
$sheet->getColumnDimension('C')->setWidth(15);
$sheet->getColumnDimension('D')->setWidth(15);
$sheet->getColumnDimension('E')->setWidth(10);
$sheet->getColumnDimension('F')->setWidth(600);

// Exportar
$spreadsheet->setActiveSheetIndexByName('Carpetas');
$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
ob_end_clean();
$writer->save('php://output');
exit;