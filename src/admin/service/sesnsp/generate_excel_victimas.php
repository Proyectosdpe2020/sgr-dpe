<?php

// Obtener fecha_final y construir nombre dinámico
$nombreArchivo = "Consulta_CNI_victimas.xlsx"; // Valor por defecto

if (isset($_POST['fecha_final'])) {
    $fechaFinal = DateTime::createFromFormat('Y-m-d', $_POST['fecha_final']);
    if ($fechaFinal) {
        $anio = $fechaFinal->format('Y');
        $mes = $fechaFinal->format('m');
        $nombreArchivo = "16_{$anio}{$mes}_victimas.xlsx";
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

// Decodificar los datos recibidos desde nt.js
$data3 = json_decode($_POST['data'], true);

if (!is_array($data3) || empty($data3)) {
    die("Datos inválidos o vacíos.");
}

$spreadsheet = new Spreadsheet();
$sheet3 = $spreadsheet->getActiveSheet();
$sheet3->setTitle('Víctimas');

// Encabezados de la tabla (a partir de la fila 1)
$rowNum3 = 1; // Comenzar en la fila 1
$sheet3->setCellValue('A' . $rowNum3, 'ID_CI');
$sheet3->setCellValue('B' . $rowNum3, 'ID_DELITO');
$sheet3->setCellValue('C' . $rowNum3, 'ID_VICF');
$sheet3->setCellValue('D' . $rowNum3, 'ID_TV');
$sheet3->setCellValue('E' . $rowNum3, 'ID_TPM');
$sheet3->setCellValue('F' . $rowNum3, 'SEXO');
$sheet3->setCellValue('G' . $rowNum3, 'GENERO');
$sheet3->setCellValue('H' . $rowNum3, 'POB');
$sheet3->setCellValue('I' . $rowNum3, 'DISC');
$sheet3->setCellValue('J' . $rowNum3, 'FHA_NAC');
$sheet3->setCellValue('K' . $rowNum3, 'EDAD');
$sheet3->setCellValue('L' . $rowNum3, 'NACIONAL');
// $sheet3->setCellValue('M' . $rowNum3, 'REL_VIC_VMARIO');

// Estilo para encabezados
$sheet3->getStyle("A$rowNum3:L$rowNum3")->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
$sheet3->getStyle("A$rowNum3:L$rowNum3")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('152f4a');
$sheet3->getStyle("A$rowNum3:L$rowNum3")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Ajuste de altura para encabezados
$sheet3->getRowDimension($rowNum3)->setRowHeight(20);

// Filas con datos
$rowNum3++;
$colorAlterno = true;
$contador = 1;

// Rellenar las filas con los datos
foreach ($data3 as $row3) {
    $sheet3->setCellValue('A' . $rowNum3, $row3['Id']);
    $sheet3->setCellValue('B' . $rowNum3, $row3['id_delito']);
    $sheet3->setCellValue('C' . $rowNum3, $row3['ID_Persona']);
    $sheet3->setCellValue('D' . $rowNum3, $row3['Tipo_Victima']);
    $sheet3->setCellValue('E' . $rowNum3, $row3['Tipo_Persona_Moral']);
    $sheet3->setCellValue('F' . $rowNum3, $row3['Sexo']);
    $sheet3->setCellValue('G' . $rowNum3, $row3['Genero']);
    $sheet3->setCellValue('H' . $rowNum3, $row3['Poblacion_Indigena']);
    $sheet3->setCellValue('I' . $rowNum3, $row3['Tipo_Discapacidad']);
    $sheet3->getStyle('J')->getNumberFormat()->setFormatCode('@');
    $sheet3->setCellValueExplicit(
        'J' . $rowNum3,
        $row3['Fecha_de_Nacimiento'],
        \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
    );
    $sheet3->setCellValue('K' . $rowNum3, $row3['Edad_de_la_Victima']);
    $sheet3->setCellValue('L' . $rowNum3, $row3['Nacionalidad']);
    // $sheet3->setCellValue('M' . $rowNum3, $row3['Relacion_imputado']);

    // Alternar colores de fondo
    if ($colorAlterno) {
        // Gris claro
        $sheet3->getStyle('A' . $rowNum3 . ':L' . $rowNum3)
            ->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()
            ->setRGB('C6C6C6');
    } else {
        // Blanco
        $sheet3->getStyle('A' . $rowNum3 . ':L' . $rowNum3)
            ->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()
            ->setRGB('FFFFFF');
    }

    // Cambiar el valor de $colorAlterno para la siguiente fila
    $colorAlterno = !$colorAlterno;

    // Incrementar la fila
    $contador++;
    $rowNum3++;
}

// Aplicar bordes a todas las celdas de la tabla
$sheet3->getStyle('A1:L' . ($rowNum3 - 1))->applyFromArray([
    'borders' => [
        'allBorders' => [
            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
            'color' => ['argb' => '646464'],
        ],
    ],
]);

// Centrar texto horizontal y verticalmente
$sheet3->getStyle('A1:L' . ($rowNum3 - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
$sheet3->getStyle('A1:L' . ($rowNum3 - 1))->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

// Encabezados en negritas
$sheet3->getStyle('A1:L1')->getFont()->setBold(true);

// Ajustar anchos de columna
$sheet3->getColumnDimension('B')->setWidth(22); // Id_delito
$sheet3->getColumnDimension('C')->setWidth(13); // ID Persona
$sheet3->getColumnDimension('D')->setWidth(10); // Tipo Victima
$sheet3->getColumnDimension('E')->setWidth(15); // Tipo Persona Moral
$sheet3->getColumnDimension('F')->setWidth(15); // Sexo
$sheet3->getColumnDimension('G')->setWidth(15); // Genero
$sheet3->getColumnDimension('H')->setWidth(15); // Población indígena
$sheet3->getColumnDimension('I')->setWidth(15); // Tipo de discapacidad
$sheet3->getColumnDimension('J')->setWidth(21); // Fecha de nacimiento
$sheet3->getColumnDimension('K')->setWidth(17); // Edad de la víctima
$sheet3->getColumnDimension('L')->setWidth(15); // Nacionalidad
// $sheet3->getColumnDimension('M')->setWidth(32); // Relación imputado

// Crear el writer de Excel y enviar al navegador
$spreadsheet->setActiveSheetIndexByName('Víctimas');
$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
ob_end_clean();
$writer->save('php://output'); // Esto enviará el archivo directamente al navegador
exit;
