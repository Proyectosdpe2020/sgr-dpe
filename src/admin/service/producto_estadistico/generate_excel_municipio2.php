<?php
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="reporte_municipio.xlsx"');
header('Cache-Control: max-age=0');
session_start();
include('C:/xampp/htdocs/sgr-dpe/service/connection.php');
require('C:/xampp/htdocs/sgr-dpe/vendor/autoload.php');

use PhpOffice\PhpSpreadsheet\{Spreadsheet, IOFactory};
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

// Obtener la conexión para la base de datos EJERCICIOS2
$conn = $connections['incidencia_sicap']['conn'];

// Verificar si la conexión fue exitosa
if (!$conn) {
    die("Error de conexión a la base de datos.");
}

// Obtener los parámetros del formulario
$mesInicio = isset($_POST['mesInicio']) ? intval($_POST['mesInicio']) : null;
$mesFin = isset($_POST['mesFin']) ? intval($_POST['mesFin']) : null;
$anio = isset($_POST['anio']) ? intval($_POST['anio']) : null;

if ($anio === null) {
    die("El año es un campo obligatorio.");
}

// Verificar que mesInicio y mesFin no sean NULL
if ($mesInicio === null || $mesFin === null) {
    die("Los meses de inicio y fin son campos obligatorios.");
}

// Consulta SQL para obtener los totales de delitos por municipio
$sql = "
    WITH Totales AS (
        SELECT Municipio, 
               COUNT(*) AS totalDelitos
        FROM carpetasMapas
        WHERE Año = $anio
          AND Mes BETWEEN $mesInicio AND $mesFin
          AND Contar = 1
        GROUP BY Municipio
    )
    SELECT Municipio,
           totalDelitos,
           (SELECT COUNT(*) 
            FROM carpetasMapas 
            WHERE Año = $anio
              AND Mes BETWEEN $mesInicio AND $mesFin
              AND Contar = 1) AS totalDelitosAll,
           DENSE_RANK() OVER (ORDER BY totalDelitos DESC) AS ranking
    FROM Totales;
";

// Ejecutar la consulta
$stmt = sqlsrv_query($conn, $sql);

if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Recoger los resultados de la consulta
$data = [];
$totalDelitosAll = 0; // Variable para el total de todos los delitos

while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $data[] = $row; // Recopilamos los resultados en un array
    $totalDelitosAll += $row['totalDelitos']; // Sumar el total de delitos de todos los municipios
}

// Crear un nuevo objeto Spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('RELACIÓN DE MUNICIPIOS');

// Encabezado principal
$sheet->setCellValue('A1', 'FISCALÍA GENERAL DEL ESTADO DE MICHOACÁN');
$sheet->mergeCells("A1:D1");
$sheet->getStyle("A1")->getFont()->setBold(true)->setSize(16);
$sheet->getStyle("A1")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Subtítulo
$sheet->setCellValue('A2', "RELACIÓN DE MUNICIPIOS");
$sheet->mergeCells("A2:D2");
$sheet->getStyle("A2")->getFont()->setBold(true)->setSize(14);
$sheet->getStyle("A2")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle("A2")->getAlignment()->setWrapText(true); // Permitir texto en varias líneas

// Encabezados de la tabla (a partir de la fila 4)
$rowNum = 4; // Comenzar en la fila 4
$sheet->setCellValue('A' . $rowNum, 'NÚMERO');
$sheet->setCellValue('B' . $rowNum, 'MUNICIPIO');
$sheet->setCellValue('C' . $rowNum, 'NÚMERO DE DELITOS');
$sheet->setCellValue('D' . $rowNum, 'LUGAR');

// Estilo de los encabezados de la tabla
$sheet->getStyle("A$rowNum:D$rowNum")->getFont()->setBold(true)->getColor()->setRGB('FFFFFF'); // Texto blanco
$sheet->getStyle("A$rowNum:D$rowNum")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('152f4a'); // Fondo azul oscuro
$sheet->getStyle("A$rowNum:D$rowNum")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Centrar texto
$sheet->getStyle("A$rowNum:D$rowNum")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

// Ajuste de altura para encabezados
$sheet->getRowDimension($rowNum)->setRowHeight(20);

// Estilo alterno para las filas de datos (comenzando desde la fila 5)
$rowNum++; // Moverse a la fila siguiente para los datos
$colorAlterno = true; // Control de color alterno
$contador = 1;

// Rellenar las filas con los datos
foreach ($data as $row) {
    $sheet->setCellValue('A' . $rowNum, $contador); // Número de fila
    $sheet->setCellValue('B' . $rowNum, mb_strtoupper($row['Municipio']));
    $sheet->setCellValue('C' . $rowNum, $row['totalDelitos']);
    $sheet->setCellValue('D' . $rowNum, $row['ranking']);

    // Alternar colores de fondo
    if ($colorAlterno) {
        // Gris claro
        $sheet->getStyle('A' . $rowNum . ':D' . $rowNum)
            ->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()
            ->setRGB('C6C6C6');
    } else {
        // Blanco
        $sheet->getStyle('A' . $rowNum . ':D' . $rowNum)
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

// Agregar un resumen al final
$sheet->setCellValue('B' . $rowNum, 'TOTAL');
$sheet->setCellValue('C' . $rowNum, $totalDelitosAll);
$sheet->getStyle('C' . $rowNum)->getNumberFormat()->setFormatCode('#,##0');
$sheet->getStyle('B' . $rowNum . ':C' . $rowNum)->getFont()->setBold(true);

// Aplicar bordes a todas las celdas de la tabla
$sheet->getStyle('A1:D' . ($rowNum))->applyFromArray([
    'borders' => [
        'allBorders' => [
            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
            'color' => ['argb' => '646464'],
        ],
    ],
]);

// Centrar texto horizontal y verticalmente
$sheet->getStyle('A1:D' . ($rowNum))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('A1:D' . ($rowNum))->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

// Formatear números con separadores de miles
$sheet->getStyle('C2:C' . ($rowNum - 1))->getNumberFormat()->setFormatCode('#,##0');

// Encabezados en negritas
$sheet->getStyle('A1:D1')->getFont()->setBold(true);

$sheet->getStyle('B' . ($rowNum - 1) . ':C' . ($rowNum - 1))->getFont()->setBold(true);
$sheet->getStyle('B117')->getFont()->setBold(false);
$sheet->getStyle('C117')->getFont()->setBold(false);


$sheet->getColumnDimension('B')->setWidth(30); // Municipio
$sheet->getColumnDimension('C')->setWidth(20); // Número de Delitos

// Segunda consulta para obtener delitos agrupados por Fiscalía, Municipio y tipo de delito
$sqlDelitosComparativos = "
    SELECT Fiscalia, 
        Municipio, 
        DelitoAgrupado, 
        Año,
        COUNT(*) AS TotalDelitos
 FROM carpetasMapas
 WHERE Mes BETWEEN $mesInicio AND $mesFin
   AND Año IN ($anio, $anio - 1, $anio - 2)
   AND (DelitoAgrupado IS NOT NULL AND DelitoAgrupado <> '')
   AND Contar=1
 GROUP BY Fiscalia, Municipio, DelitoAgrupado, Año
 ORDER BY Fiscalia, 
          Municipio, 
          SUM(COUNT(*)) OVER (PARTITION BY Fiscalia, Municipio, DelitoAgrupado) DESC, -- Total combinado por delito
          DelitoAgrupado, 
          Año DESC
";

$params = array($mesInicio, $mesFin, $anio, $anio - 1, $anio - 2);

$stmtDelitosComparativos = sqlsrv_query($conn, $sqlDelitosComparativos, $params);

if ($stmtDelitosComparativos === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Crear el array de delitos comparativos
$delitosComparativos = [];

// Supongamos que obtienes resultados de la base de datos
while ($row = sqlsrv_fetch_array($stmtDelitosComparativos, SQLSRV_FETCH_ASSOC)) {
    $fiscalia = $row['Fiscalia'];
    $municipio = $row['Municipio'];
    $delito = $row['DelitoAgrupado'];
    $rowAnio = $row['Año'];
    $totalDelitos = $row['TotalDelitos'];

    // Inicializar la estructura si no existe
    if (!isset($delitosComparativos[$fiscalia][$municipio][$delito])) {
        $delitosComparativos[$fiscalia][$municipio][$delito] = [];
    }

    // Asignar el total de delitos al año correspondiente
    $delitosComparativos[$fiscalia][$municipio][$delito][$rowAnio] = $totalDelitos;
}

// Crear una nueva hoja para los delitos comparativos
$sheetComparativos = $spreadsheet->createSheet();
$sheetComparativos->setTitle('INCIDENCIA DELICTIVA');

$rowNumComparativos = 1; // Comienza en la fila 1

$colorAlternoComparativos = true;

// Encabezado principal
$sheetComparativos->setCellValue('A1', 'FISCALÍA GENERAL DEL ESTADO DE MICHOACÁN');
$sheetComparativos->mergeCells("A1:C1");
$sheetComparativos->getStyle("A1")->getFont()->setBold(true)->setSize(16);
$sheetComparativos->getStyle("A1")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); 

// Encabezado del municipio y periodo
$sheetComparativos->setCellValue('A2', "INCIDENCIA DELICTIVA POR AVERIGUACIÓN PREVIA");
$sheetComparativos->mergeCells("A2:C2");
$sheetComparativos->getStyle("A2")->getFont()->setBold(true)->setSize(14); 
$sheetComparativos->getStyle("A2")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); 

$rowNumComparativos = 5; 

foreach ($delitosComparativos as $fiscalia => $municipios) {
    // Escribir la fiscalía
    $sheetComparativos->setCellValue('A' . $rowNumComparativos, mb_strtoupper("Fiscalía $fiscalia"));
    $sheetComparativos->mergeCells("A$rowNumComparativos:C$rowNumComparativos");
    $sheetComparativos->getStyle("A$rowNumComparativos")->getFont()->setBold(true)->setSize(14)->getColor()->setRGB('FFFFFF'); // Fuente blanca
    $sheetComparativos->getStyle("A$rowNumComparativos")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('152f4a'); // Fondo #152f4a
    $rowNumComparativos++;

    foreach ($municipios as $municipio => $delitos) {
        // Escribir el municipio
        $sheetComparativos->setCellValue('A' . $rowNumComparativos, mb_strtoupper("$municipio"));
        $sheetComparativos->mergeCells("A$rowNumComparativos:C$rowNumComparativos");
        $sheetComparativos->getStyle("A$rowNumComparativos")->getFont()->setBold(true)->getColor()->setRGB('FFFFFF'); // Fuente blanca
        $sheetComparativos->getStyle("A$rowNumComparativos")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('152f4a'); // Fondo #152f4a
        $rowNumComparativos++;

        // Encabezados (solo mostrar Año actual)
        $sheetComparativos->setCellValue('A' . $rowNumComparativos, 'NÚMERO');
        $sheetComparativos->setCellValue('B' . $rowNumComparativos, 'DELITO');
        $sheetComparativos->setCellValue('C' . $rowNumComparativos, $anio); // Solo Año Actual
        $sheetComparativos->getStyle("A$rowNumComparativos:C$rowNumComparativos")->getFont()->setBold(true)->getColor()->setRGB('FFFFFF'); // Fuente blanca
        $sheetComparativos->getStyle("A$rowNumComparativos:C$rowNumComparativos")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('152f4a'); // Fondo #152f4a
        $rowNumComparativos++;

        $numDelito = 1;

        // Ordenar los delitos por el total del año actual
        uasort($delitos, function ($a, $b) use ($anio) {
            $totalA = isset($a[$anio]) ? $a[$anio] : 0;
            $totalB = isset($b[$anio]) ? $b[$anio] : 0;
            return $totalB <=> $totalA;
        });

        // Inicializar acumuladores
        $totalActual = 0;

        // Procesar delitos
        foreach ($delitos as $delito => $años) {
            $anioActual = isset($años[$anio]) ? $años[$anio] : 0;

            // Alternar colores de fondo para las filas de delitos
            if ($colorAlternoComparativos) {
                // Gris claro
                $sheetComparativos->getStyle('A' . $rowNumComparativos . ':C' . $rowNumComparativos)
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setRGB('C6C6C6');
            } else {
                // Blanco
                $sheetComparativos->getStyle('A' . $rowNumComparativos . ':C' . $rowNumComparativos)
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setRGB('FFFFFF');
            }

            $sheetComparativos->setCellValue('A' . $rowNumComparativos, $numDelito);
            $sheetComparativos->setCellValue('B' . $rowNumComparativos, $delito);
            $sheetComparativos->setCellValue('C' . $rowNumComparativos, $anioActual);

            $numberFormat = '#,##0';
            $sheetComparativos->getStyle('C' . $rowNumComparativos)->getNumberFormat()->setFormatCode($numberFormat);

            // Sumar para totales
            $totalActual += $anioActual;

            // Cambiar el valor de $colorAlternoComparativos para la siguiente fila
            $colorAlternoComparativos = !$colorAlternoComparativos;

            $numDelito++;
            $rowNumComparativos++;
        }

        // Escribir totales (solo mostrar total del año actual)
        $sheetComparativos->setCellValue('A' . $rowNumComparativos, 'TOTAL');
        $sheetComparativos->getStyle('A' . $rowNumComparativos)->getFont()->setBold(true);
        $sheetComparativos->mergeCells("A$rowNumComparativos:B$rowNumComparativos");
        $sheetComparativos->setCellValue('C' . $rowNumComparativos, $totalActual);
        $sheetComparativos->getStyle("A$rowNumComparativos:C$rowNumComparativos")->getFont()->setBold(true);

        $sheetComparativos->getStyle('C' . $rowNumComparativos)->getNumberFormat()->setFormatCode($numberFormat);

        $rowNumComparativos++;

        // Espacio entre municipios
        $rowNumComparativos++;
    }

    // Espacio entre fiscalías
    $rowNumComparativos += 2;
}

// Aplicar bordes y estilos globales
$sheetComparativos->getStyle("A1:C" . ($rowNumComparativos - 3))->applyFromArray([
    'borders' => [
        'allBorders' => [
            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
            'color' => ['rgb' => '646464'],
        ],
    ],
    'alignment' => [
        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
    ],
]);

// Ajustar anchos de columna
$sheetComparativos->getColumnDimension('A')->setWidth(12); //Número
$sheetComparativos->getColumnDimension('B')->setWidth(120); // Delito
$sheetComparativos->getColumnDimension('C')->setWidth(12); // Año actual

// Crear el writer de Excel y enviar al navegador
$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
ob_end_clean();
$writer->save('php://output'); // Esto enviará el archivo directamente al navegador
exit;
