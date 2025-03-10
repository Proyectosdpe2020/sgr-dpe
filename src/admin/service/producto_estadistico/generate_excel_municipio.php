<?php
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="reporte_municipio.xlsx"');
header('Cache-Control: max-age=0');
session_start();
include('D:/xampp/htdocs/sgr-dpe/service/connection.php');
require('D:/xampp/htdocs/sgr-dpe/vendor/autoload.php');  

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
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
    $data[] = $row; // Recopilar los resultados en un arreglo
    $totalDelitosAll += $row['totalDelitos']; // Sumar el total de delitos de todos los municipios
}

// Crear un nuevo objeto Spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('RELACIÓN DE MUNICIPIOS');

// Encabezado 
$sheet->setCellValue('A1', 'FISCALÍA GENERAL DEL ESTADO DE MICHOACÁN');
$sheet->mergeCells("A1:D1");
$sheet->getStyle("A1")->getFont()->setBold(true)->setSize(16);
$sheet->getStyle("A1")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Iítulo
$sheet->setCellValue('A2', "RELACIÓN DE MUNICIPIOS");
$sheet->mergeCells("A2:D2");
$sheet->getStyle("A2")->getFont()->setBold(true)->setSize(14);
$sheet->getStyle("A2")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle("A2")->getAlignment()->setWrapText(true); 

// Encabezados de la tabla 
$rowNum = 4; // Comenzar en la fila 4
$sheet->setCellValue('A' . $rowNum, 'NÚMERO');
$sheet->setCellValue('B' . $rowNum, 'MUNICIPIO');
$sheet->setCellValue('C' . $rowNum, 'NÚMERO DE DELITOS');
$sheet->setCellValue('D' . $rowNum, 'LUGAR');

// Estilo de los encabezados de la tabla
$sheet->getStyle("A$rowNum:D$rowNum")->getFont()->setBold(true)->getColor()->setRGB('FFFFFF'); 
$sheet->getStyle("A$rowNum:D$rowNum")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('152f4a'); 
$sheet->getStyle("A$rowNum:D$rowNum")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); 
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

// Agregar un total al final
$sheet->setCellValue('B' . $rowNum, 'TOTAL');
$sheet->setCellValue('C' . $rowNum, $totalDelitosAll);
$sheet->getStyle('C' . $rowNum)->getNumberFormat()->setFormatCode('#,##0');
$sheet->getStyle('B' . $rowNum . ':C' . $rowNum)->getFont()->setBold(true);

// Aplicar bordes a todas las celdas de la tabla
$sheet->getStyle('A1:D' . ($rowNum))->applyFromArray([
    'borders' => [
        'allBorders' => [
            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
            'color' => ['rgb' => '646464'],
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

// Segunda consulta SQL
$sqlDelitosComparativos = "
    SELECT Fiscalia, 
        Municipio, 
        DelitoAgrupado, 
        Año AS Anio,
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

// Crear el arreglo de delitos comparativos
$delitosComparativos = [];

while ($row = sqlsrv_fetch_array($stmtDelitosComparativos, SQLSRV_FETCH_ASSOC)) {
    $fiscalia = $row['Fiscalia'];
    $municipio = $row['Municipio'];
    $delito = $row['DelitoAgrupado'];
    $rowAnio = $row['Anio'];
    $totalDelitos = $row['TotalDelitos'];

    // Inicializar la estructura si no existe
    if (!isset($delitosComparativos[$fiscalia][$municipio][$delito])) {
        $delitosComparativos[$fiscalia][$municipio][$delito] = [];
    }

    // Asignar el total de delitos al año correspondiente
    $delitosComparativos[$fiscalia][$municipio][$delito][$rowAnio] = $totalDelitos;
}

// Crear una nueva hoja para la incidencia delictiva
$sheetComparativos = $spreadsheet->createSheet();
$sheetComparativos->setTitle('INCIDENCIA DELICTIVA');

$rowNumComparativos = 1; // Comienza en la fila 1

$colorAlternoComparativos = true;

//Encabezado 
$sheetComparativos->setCellValue('A1', 'FISCALÍA GENERAL DEL ESTADO DE MICHOACÁN');
$sheetComparativos->mergeCells("A1:H1");
$sheetComparativos->getStyle("A1")->getFont()->setBold(true)->setSize(16);
$sheetComparativos->getStyle("A1")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); 

//Título
$sheetComparativos->setCellValue('A2', "INCIDENCIA DELICTIVA");
$sheetComparativos->mergeCells("A2:H2");
$sheetComparativos->getStyle("A2")->getFont()->setBold(true)->setSize(14); 
$sheetComparativos->getStyle("A2")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$rowNumComparativos = 5; 

foreach ($delitosComparativos as $fiscalia => $municipios) {

    // Escribir la fiscalía
    $sheetComparativos->setCellValue('A' . $rowNumComparativos, mb_strtoupper("Fiscalía $fiscalia"));
    $sheetComparativos->mergeCells("A$rowNumComparativos:H$rowNumComparativos");
    $sheetComparativos->getStyle("A$rowNumComparativos")->getFont()->setBold(true)->setSize(14)->getColor()->setRGB('FFFFFF'); 
    $sheetComparativos->getStyle("A$rowNumComparativos")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('152f4a'); 
    $rowNumComparativos++;

    foreach ($municipios as $municipio => $delitos) {

        // Escribir el municipio
        $sheetComparativos->setCellValue('A' . $rowNumComparativos, mb_strtoupper("$municipio"));
        $sheetComparativos->mergeCells("A$rowNumComparativos:H$rowNumComparativos");
        $sheetComparativos->getStyle("A$rowNumComparativos")->getFont()->setBold(true)->getColor()->setRGB('FFFFFF'); 
        $sheetComparativos->getStyle("A$rowNumComparativos")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('152f4a'); 
        $rowNumComparativos++;

        // Encabezados
        $sheetComparativos->setCellValue('A' . $rowNumComparativos, 'NÚMERO');
        $sheetComparativos->setCellValue('B' . $rowNumComparativos, 'DELITO');
        $sheetComparativos->setCellValue('C' . $rowNumComparativos, $anio - 2);
        $sheetComparativos->setCellValue('D' . $rowNumComparativos, $anio - 1);
        $sheetComparativos->setCellValue('E' . $rowNumComparativos, $anio);
        $sheetComparativos->setCellValue('F' . $rowNumComparativos, 'DIF. ' . ($anio - 2) . '-' . $anio);
        $sheetComparativos->setCellValue('G' . $rowNumComparativos, 'DIF. ' . ($anio - 1) . '-' . $anio);
        $sheetComparativos->setCellValue('H' . $rowNumComparativos, 'PORCENTAJE');
        $sheetComparativos->getStyle("A$rowNumComparativos:H$rowNumComparativos")->getFont()->setBold(true)->getColor()->setRGB('FFFFFF'); 
        $sheetComparativos->getStyle("A$rowNumComparativos:H$rowNumComparativos")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('152f4a'); 
        $rowNumComparativos++;

        $numDelito = 1;

        // Ordenar los delitos por el total del año actual
        uasort($delitos, function ($a, $b) use ($anio) {
            $totalA = isset($a[$anio]) ? $a[$anio] : 0; 
            $totalB = isset($b[$anio]) ? $b[$anio] : 0; 
            return $totalB - $totalA; 
        });

        // Inicializar acumuladores
        $totalMinus2 = 0;
        $totalMinus1 = 0;
        $totalActual = 0;

        // Procesar delitos
        foreach ($delitos as $delito => $años) {
            $anioMinus2 = isset($años[$anio - 2]) ? $años[$anio - 2] : 0;
            $anioMinus1 = isset($años[$anio - 1]) ? $años[$anio - 1] : 0;
            $anioActual = isset($años[$anio]) ? $años[$anio] : 0;

            $diffMinus2 = $anioActual - $anioMinus2;
            $diffMinus1 = $anioActual - $anioMinus1;
            $porcentaje = $anioMinus1 ? round(($diffMinus1 / $anioMinus1) * 100, 2) : 'NC';

            // Alternar colores de fondo para las filas de delitos
            if ($colorAlternoComparativos) {
                // Gris claro
                $sheetComparativos->getStyle('A' . $rowNumComparativos . ':H' . $rowNumComparativos)
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setRGB('C6C6C6');
            } else {
                // Blanco
                $sheetComparativos->getStyle('A' . $rowNumComparativos . ':H' . $rowNumComparativos)
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setRGB('FFFFFF');
            }

            $sheetComparativos->setCellValue('A' . $rowNumComparativos, $numDelito); 
            $sheetComparativos->setCellValue('B' . $rowNumComparativos, $delito);
            $sheetComparativos->setCellValue('C' . $rowNumComparativos, $anioMinus2);
            $sheetComparativos->setCellValue('D' . $rowNumComparativos, $anioMinus1);
            $sheetComparativos->setCellValue('E' . $rowNumComparativos, $anioActual);
            $sheetComparativos->setCellValue('F' . $rowNumComparativos, $diffMinus2);
            $sheetComparativos->setCellValue('G' . $rowNumComparativos, $diffMinus1);

            // Mostrar porcentaje con las flechas
            $porcentajeText = ($porcentaje === 'NC') ? 'NC' : $porcentaje . '%';
            $sheetComparativos->setCellValue('H' . $rowNumComparativos, $porcentajeText);

            $numberFormat = '#,##0';
            $sheetComparativos->getStyle('C' . $rowNumComparativos)->getNumberFormat()->setFormatCode($numberFormat);
            $sheetComparativos->getStyle('D' . $rowNumComparativos)->getNumberFormat()->setFormatCode($numberFormat);
            $sheetComparativos->getStyle('E' . $rowNumComparativos)->getNumberFormat()->setFormatCode($numberFormat);
            $sheetComparativos->getStyle('F' . $rowNumComparativos)->getNumberFormat()->setFormatCode($numberFormat);
            $sheetComparativos->getStyle('G' . $rowNumComparativos)->getNumberFormat()->setFormatCode($numberFormat);

            // Cambiar color de las celdas de diferencias y porcentaje (rojo si positivo, azul si negativo)
            $diffMinus2Cell = 'F' . $rowNumComparativos;
            $diffMinus1Cell = 'G' . $rowNumComparativos;
            $porcentajeCell = 'H' . $rowNumComparativos;

            // Color para E (Diferencia - 2)
            if ($diffMinus2 == 0) {
                $sheetComparativos->getStyle($diffMinus2Cell)->getFont()->getColor()->setRGB('000000'); // Negro
            } elseif ($diffMinus2 > 0) {
                $sheetComparativos->getStyle($diffMinus2Cell)->getFont()->getColor()->setRGB('FF0000'); // Rojo
            } else {
                $sheetComparativos->getStyle($diffMinus2Cell)->getFont()->getColor()->setRGB('0000FF'); // Azul
            }

            // Color para F (Diferencia - 1)
            if ($diffMinus1 == 0) {
                $sheetComparativos->getStyle($diffMinus1Cell)->getFont()->getColor()->setRGB('000000'); // Negro
            } elseif ($diffMinus1 > 0) {
                $sheetComparativos->getStyle($diffMinus1Cell)->getFont()->getColor()->setRGB('FF0000'); // Rojo
            } else {
                $sheetComparativos->getStyle($diffMinus1Cell)->getFont()->getColor()->setRGB('0000FF'); // Azul
            }

            // Color para G (Porcentaje)
            if ($porcentaje === 'NC') {
                $sheetComparativos->getStyle($porcentajeCell)->getFont()->getColor()->setRGB('000000'); // Negro para "NC"
            } elseif ($porcentaje == 0) {
                $sheetComparativos->getStyle($porcentajeCell)->getFont()->getColor()->setRGB('000000'); // Negro para cero
            } elseif ($porcentaje > 0) {
                $sheetComparativos->getStyle($porcentajeCell)->getFont()->getColor()->setRGB('FF0000'); // Rojo
            } else {
                $sheetComparativos->getStyle($porcentajeCell)->getFont()->getColor()->setRGB('0000FF'); // Azul
            }

            // Si el porcentaje no es "NC", agregar las flechas
            if ($porcentaje !== 'NC') {
                $flecha = ($porcentaje > 0) ? 'up.png' : (($porcentaje < 0) ? 'down.png' : '');
                if ($flecha) {
                    // Ruta de la imagen de la flecha
                    $flechaPath = 'D:/xampp/htdocs/sgr-dpe/assets/img/' . $flecha;

                    // Crear un nuevo objeto Drawing para la flecha
                    $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                    $drawing->setName('Flecha')
                        ->setPath($flechaPath)
                        ->setHeight(15)
                        ->setWidth(15)
                        ->setCoordinates('H' . $rowNumComparativos)
                        ->setOffsetX(15)
                        ->setOffsetY(2)
                        ->setWorksheet($sheetComparativos);
                }
            }

            // Sumar para totales
            $totalMinus2 += $anioMinus2;
            $totalMinus1 += $anioMinus1;
            $totalActual += $anioActual;

            // Cambiar el valor de $colorAlternoComparativos para la siguiente fila
            $colorAlternoComparativos = !$colorAlternoComparativos;

            $numDelito++;
            $rowNumComparativos++;
        }

        // Escribir totales
        $sheetComparativos->setCellValue('A' . $rowNumComparativos, 'TOTAL');
        $sheetComparativos->getStyle('A' . $rowNumComparativos)->getFont()->setBold(true);
        $sheetComparativos->mergeCells("A$rowNumComparativos:B$rowNumComparativos");
        $sheetComparativos->setCellValue('C' . $rowNumComparativos, $totalMinus2);
        $sheetComparativos->setCellValue('D' . $rowNumComparativos, $totalMinus1);
        $sheetComparativos->setCellValue('E' . $rowNumComparativos, $totalActual);
        $sheetComparativos->setCellValue('F' . $rowNumComparativos, $totalActual - $totalMinus2);
        $sheetComparativos->setCellValue('G' . $rowNumComparativos, $totalActual - $totalMinus1);
        $sheetComparativos->setCellValue('H' . $rowNumComparativos, $totalMinus1 ? round((($totalActual - $totalMinus1) / $totalMinus1) * 100, 2) . '%' : '0%');
        $sheetComparativos->getStyle("A$rowNumComparativos:H$rowNumComparativos")->getFont()->setBold(true);

        $sheetComparativos->getStyle('C' . $rowNumComparativos)->getNumberFormat()->setFormatCode($numberFormat);
        $sheetComparativos->getStyle('D' . $rowNumComparativos)->getNumberFormat()->setFormatCode($numberFormat);
        $sheetComparativos->getStyle('E' . $rowNumComparativos)->getNumberFormat()->setFormatCode($numberFormat);
        $sheetComparativos->getStyle('F' . $rowNumComparativos)->getNumberFormat()->setFormatCode($numberFormat);
        $sheetComparativos->getStyle('G' . $rowNumComparativos)->getNumberFormat()->setFormatCode($numberFormat);

        // Cambiar el color de las celdas de las diferencias y el porcentaje de los totales
        $diffMinus2TotalCell = 'F' . $rowNumComparativos;
        $diffMinus1TotalCell = 'G' . $rowNumComparativos;
        $porcentajeTotalCell = 'H' . $rowNumComparativos;

        // Color para E (Diferencia - 2)
        if ($totalActual - $totalMinus2 > 0) {
            $sheetComparativos->getStyle($diffMinus2TotalCell)->getFont()->getColor()->setRGB('FF0000'); // Rojo
        } else {
            $sheetComparativos->getStyle($diffMinus2TotalCell)->getFont()->getColor()->setRGB('0000FF'); // Azul
        }

        // Color para F (Diferencia - 1)
        if ($totalActual - $totalMinus1 > 0) {
            $sheetComparativos->getStyle($diffMinus1TotalCell)->getFont()->getColor()->setRGB('FF0000'); // Rojo
        } else {
            $sheetComparativos->getStyle($diffMinus1TotalCell)->getFont()->getColor()->setRGB('0000FF'); // Azul
        }

        // Color para G (Porcentaje)
        if ($totalMinus1 ? round((($totalActual - $totalMinus1) / $totalMinus1) * 100, 2) > 0 : false) {
            $sheetComparativos->getStyle($porcentajeTotalCell)->getFont()->getColor()->setRGB('FF0000'); // Rojo
        } else {
            $sheetComparativos->getStyle($porcentajeTotalCell)->getFont()->getColor()->setRGB('0000FF'); // Azul
        }

        $rowNumComparativos++;

        // Espacio entre municipios
        $rowNumComparativos++;
    }

    // Espacio entre fiscalías
    $rowNumComparativos += 2;
}

// Aplicar bordes y estilos globales
$sheetComparativos->getStyle("A1:H" . ($rowNumComparativos - 3))->applyFromArray([
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
$sheetComparativos->getColumnDimension('C')->setWidth(12); // Año - 2
$sheetComparativos->getColumnDimension('D')->setWidth(12); // Año - 1
$sheetComparativos->getColumnDimension('E')->setWidth(12); // Año actual
$sheetComparativos->getColumnDimension('F')->setWidth(15); // Dif. año - 2
$sheetComparativos->getColumnDimension('G')->setWidth(15); // Dif. año - 1
$sheetComparativos->getColumnDimension('H')->setWidth(15); // Porcentaje

// Crear el writer de Excel y enviar al navegador
$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
ob_end_clean();
$writer->save('php://output'); 
exit;