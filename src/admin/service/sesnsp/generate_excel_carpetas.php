<?php
ini_set('memory_limit', '2048M');
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Consulta_CNI_carpetas.xlsx"');
header('Cache-Control: max-age=0');
session_start();
include('D:/xampp/htdocs/sgr-dpe/service/connection.php');
require('D:/xampp/htdocs/sgr-dpe/vendor/autoload.php');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

// Obtener la conexión para la base de datos PRUEBA
$conn = $connections['sicap']['conn'];

// Verificar si la conexión fue exitosa
if (!$conn) {
    die("Error de conexión a la base de datos.");
}

// Obtener los parámetros del formulario
$fecha_inicial = isset($_POST['fecha_inicial']) ? $_POST['fecha_inicial'] : null;
$fecha_final = isset($_POST['fecha_final']) ? $_POST['fecha_final'] : null;

if ($fecha_inicial === null || $fecha_final === null) {
    die("Las fechas inicial y final son campos obligatorios.");
}

// Consulta SQL
$sql = "
    SELECT DISTINCT
    c.CarpetaID AS Id,
    c.NUC AS 'Nomenglatura_carpeta',
    c.FechaInicio,
    CONVERT(VARCHAR(10), c.FechaInicio, 23) AS 'Fecha Inicio',
    CONVERT(VARCHAR(8), c.FechaInicio, 8) AS 'Hora Inicio',
    1 AS [Tipo de expediente],
    CONVERT(VARCHAR(550), c.Hechos) AS hechos
FROM dbo.Carpeta c
INNER JOIN dbo.CatUIs u ON c.CatUIsID = u.CatUIsID
INNER JOIN dbo.CatFiscalias fisca ON u.CatFiscaliasID = fisca.CatFiscaliasID
WHERE 
    c.contar = 1
    AND CAST(c.FechaInicio AS DATE) BETWEEN '$fecha_inicial' AND '$fecha_final'
ORDER BY c.FechaInicio ASC, c.CarpetaID ASC;
";

// Ejecutar la consulta
$stmt = sqlsrv_query($conn, $sql);

if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Recoger los resultados de la consulta
$data = [];

// Recopilar los resultados en un array
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $data[] = $row;
}

// Depuración: Ver qué datos se están obteniendo
// var_dump($fecha_inicial, $fecha_final);
// exit; 

// Crear un nuevo objeto Spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Carpetas');

// Encabezados de la tabla (a partir de la fila 1)
$rowNum = 1; // Comenzar en la fila 1
$sheet->setCellValue('A' . $rowNum, 'ID_CI');
$sheet->setCellValue('B' . $rowNum, 'NTRA_CI');
$sheet->setCellValue('C' . $rowNum, 'FHA_DE_INI');
$sheet->setCellValue('D' . $rowNum, 'HRA_DE_INI');
$sheet->setCellValue('E' . $rowNum, 'ID_TEXP');
$sheet->setCellValue('F' . $rowNum, 'RMEN_DE_HCHOS');

// Estilo de los encabezados de la tabla
$sheet->getStyle("A$rowNum:F$rowNum")->getFont()->setBold(true)->getColor()->setRGB('FFFFFF'); // Texto blanco
$sheet->getStyle("A$rowNum:F$rowNum")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('152f4a'); // Fondo azul oscuro
$sheet->getStyle("A$rowNum:F$rowNum")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Centrar texto
$sheet->getStyle("A$rowNum:F$rowNum")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

// Ajuste de altura para encabezados
$sheet->getRowDimension($rowNum)->setRowHeight(20);

// Estilo alterno para las filas de datos (comenzando desde la fila 2)
$rowNum++; // Moverse a la fila siguiente para los datos
$colorAlterno = true; // Control de color alterno
$contador = 1;

// Rellenar las filas con los datos
foreach ($data as $row) {
    $sheet->setCellValue('A' . $rowNum, $row['Id']);
    $sheet->setCellValueExplicit('B' . $rowNum, $row['Nomenglatura_carpeta'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    $sheet->setCellValue('C' . $rowNum, $row['Fecha Inicio']);
    $sheet->setCellValue('D' . $rowNum, $row['Hora Inicio']);
    $sheet->setCellValue('E' . $rowNum, $row['Tipo de expediente']);
    $sheet->setCellValue('F' . $rowNum, $row['hechos']);
    $sheet->getStyle('F' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT); // Align "Hechos" to the left

    // Alternar colores de fondo
    if ($colorAlterno) {
        // Gris claro
        $sheet->getStyle('A' . $rowNum . ':F' . $rowNum)
            ->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()
            ->setRGB('C6C6C6');
    } else {
        // Blanco
        $sheet->getStyle('A' . $rowNum . ':F' . $rowNum)
            ->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()
            ->setRGB('FFFFFF');
    }

    // Cambiar el valor de $colorAlterno para la siguiente fila
    $colorAlterno = !$colorAlterno;

    // Incrementar la fila
    $contador++;
    $rowNum++;
}

// Aplicar bordes a todas las celdas de la tabla
$sheet->getStyle('A1:F' . ($rowNum - 1))->applyFromArray([
    'borders' => [
        'allBorders' => [
            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
            'color' => ['argb' => '646464'],
        ],
    ],
]);

// Centrar texto horizontal y verticalmente
$sheet->getStyle('A1:F' . ($rowNum - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('A1:F' . ($rowNum - 1))->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

// Encabezados en negritas
$sheet->getStyle('A1:F1')->getFont()->setBold(true);

// Ajustar anchos de columna
$sheet->getColumnDimension('B')->setWidth(22); // Nomenclatura carpeta
$sheet->getColumnDimension('C')->setWidth(15); // Fecha Inicio
$sheet->getColumnDimension('D')->setWidth(15); // Hora Inicio
$sheet->getColumnDimension('E')->setWidth(10); // Tipo de expediente
$sheet->getColumnDimension('F')->setWidth(600); // Hechos

// Crear el writer de Excel y enviar al navegador
$spreadsheet->setActiveSheetIndexByName('Carpetas');
$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
ob_end_clean();
$writer->save('php://output'); // Esto enviará el archivo directamente al navegador
exit;