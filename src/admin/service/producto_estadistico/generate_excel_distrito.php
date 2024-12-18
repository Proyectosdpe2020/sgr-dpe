<?php
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="reporte_distrito.xlsx"');
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

if (empty($_POST['mesInicio']) || empty($_POST['mesFin']) || empty($_POST['anio'])) {
    die('Error: Todos los campos son obligatorios.');
}

$mesInicio = intval($_POST['mesInicio']);
$mesFin = intval($_POST['mesFin']);
$anio = intval($_POST['anio']);

if ($conn && $mesInicio && $mesFin && $anio) {

    // Arreglo para convertir números de mes a texto
    $mesesTexto = [
        1 => 'Enero',
        2 => 'Febrero',
        3 => 'Marzo',
        4 => 'Abril',
        5 => 'Mayo',
        6 => 'Junio',
        7 => 'Julio',
        8 => 'Agosto',
        9 => 'Septiembre',
        10 => 'Octubre',
        11 => 'Noviembre',
        12 => 'Diciembre'
    ];

    // Convertir los meses a texto
    $textoMesInicio = array_key_exists($mesInicio, $mesesTexto) ? $mesesTexto[$mesInicio] : 'Mes inválido';
    $textoMesFin = array_key_exists($mesFin, $mesesTexto) ? $mesesTexto[$mesFin] : 'Mes inválido';

    // Crear un nuevo objeto Spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('INCIDENCIA DELICTIVA');

    // Consulta SQL
    $sqlDelitosComparativos = "
    SELECT Distrito, 
           DelitoAgrupado, 
           Año AS Anio,
           SUM(TotalDelitos) AS TotalDelitos
    FROM (
        SELECT Distrito, 
               DelitoAgrupado, 
               Año,
               COUNT(*) AS TotalDelitos
        FROM carpetasMapas
        WHERE Mes BETWEEN $mesInicio AND $mesFin
          AND Año IN ($anio, $anio - 1, $anio - 2)
          AND (DelitoAgrupado IS NOT NULL AND DelitoAgrupado <> '')
          AND Contar=1
        GROUP BY Distrito, DelitoAgrupado, Año
    ) AS subquery
    GROUP BY Distrito, DelitoAgrupado, Año
    ORDER BY Distrito, DelitoAgrupado, Año DESC;
    ";

    $stmtDelitosComparativos = sqlsrv_query($conn, $sqlDelitosComparativos);

    if ($stmtDelitosComparativos === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    $delitosComparativos = [];
    while ($row = sqlsrv_fetch_array($stmtDelitosComparativos, SQLSRV_FETCH_ASSOC)) {
        $delitosComparativos[$row['Distrito']][$row['DelitoAgrupado']][$row['Anio']] = $row['TotalDelitos'];
    }

    $rowNum = 1;

    $colorAlternoComparativos = true;

    //Encabezado 
    $sheet->setCellValue('A1', 'FISCALÍA GENERAL DEL ESTADO DE MICHOACÁN');
    $sheet->mergeCells("A1:H1");
    $sheet->getStyle("A1")->getFont()->setBold(true)->setSize(16);
    $sheet->getStyle("A1")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); 

    //Título
    $sheet->setCellValue('A2', "INCIDENCIA DELICTIVA");
    $sheet->mergeCells("A2:H2");
    $sheet->getStyle("A2")->getFont()->setBold(true)->setSize(14); 
    $sheet->getStyle("A2")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    
    $rowNum = 5; 

    foreach ($delitosComparativos as $distrito => $delitos) {
        $sheet->setCellValue('A' . $rowNum, mb_strtoupper("DISTRITO $distrito"));
        $sheet->mergeCells("A$rowNum:H$rowNum");
        $sheet->getStyle("A$rowNum")->getFont()->setBold(true)->setSize(14)->getColor()->setRGB('FFFFFF'); 
        $sheet->getStyle("A$rowNum")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('152f4a'); 
        $rowNum++;

        // Encabezados de la tabla
        $sheet->setCellValue('A' . $rowNum, 'NÚMERO');
        $sheet->setCellValue('B' . $rowNum, 'DELITO');
        $sheet->setCellValue('C' . $rowNum, $anio - 2);
        $sheet->setCellValue('D' . $rowNum, $anio - 1);
        $sheet->setCellValue('E' . $rowNum, $anio);
        $sheet->setCellValue('F' . $rowNum, 'DIF. ' . ($anio - 2) . '-' . $anio);
        $sheet->setCellValue('G' . $rowNum, 'DIF. ' . ($anio - 1) . '-' . $anio);
        $sheet->setCellValue('H' . $rowNum, 'PORCENTAJE');
        $sheet->getStyle("A$rowNum:H$rowNum")->getFont()->setBold(true)->getColor()->setRGB('FFFFFF'); 
        $sheet->getStyle("A$rowNum:H$rowNum")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('152f4a'); 
        $rowNum++;

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
                $sheet->getStyle('A' . $rowNum . ':H' . $rowNum)
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setRGB('C6C6C6');
            } else {
                // Blanco
                $sheet->getStyle('A' . $rowNum . ':H' . $rowNum)
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setRGB('FFFFFF');
            }

            $sheet->setCellValue('A' . $rowNum, $numDelito); 
            $sheet->setCellValue('B' . $rowNum, $delito);
            $sheet->setCellValue('C' . $rowNum, $anioMinus2);
            $sheet->setCellValue('D' . $rowNum, $anioMinus1);
            $sheet->setCellValue('E' . $rowNum, $anioActual);
            $sheet->setCellValue('F' . $rowNum, $diffMinus2);
            $sheet->setCellValue('G' . $rowNum, $diffMinus1);
            $sheet->setCellValue('H' . $rowNum, $porcentaje === 'NC' ? 'NC' : $porcentaje . '%');

            $numberFormat = '#,##0';
            $sheet->getStyle('C' . $rowNum)->getNumberFormat()->setFormatCode($numberFormat);
            $sheet->getStyle('D' . $rowNum)->getNumberFormat()->setFormatCode($numberFormat);
            $sheet->getStyle('E' . $rowNum)->getNumberFormat()->setFormatCode($numberFormat);
            $sheet->getStyle('F' . $rowNum)->getNumberFormat()->setFormatCode($numberFormat);
            $sheet->getStyle('G' . $rowNum)->getNumberFormat()->setFormatCode($numberFormat);

            // Cambiar color de las celdas de diferencias y porcentaje (rojo si positivo, azul si negativo)
            $diffMinus2Cell = 'F' . $rowNum;
            $diffMinus1Cell = 'G' . $rowNum;
            $porcentajeCell = 'H' . $rowNum;

            // Color para E (Diferencia - 2)
            if ($diffMinus2 == 0) {
                $sheet->getStyle($diffMinus2Cell)->getFont()->getColor()->setRGB('000000'); // Negro
            } elseif ($diffMinus2 > 0) {
                $sheet->getStyle($diffMinus2Cell)->getFont()->getColor()->setRGB('FF0000'); // Rojo
            } else {
                $sheet->getStyle($diffMinus2Cell)->getFont()->getColor()->setRGB('0000FF'); // Azul
            }

            // Color para F (Diferencia - 1)
            if ($diffMinus1 == 0) {
                $sheet->getStyle($diffMinus1Cell)->getFont()->getColor()->setRGB('000000'); // Negro
            } elseif ($diffMinus1 > 0) {
                $sheet->getStyle($diffMinus1Cell)->getFont()->getColor()->setRGB('FF0000'); // Rojo
            } else {
                $sheet->getStyle($diffMinus1Cell)->getFont()->getColor()->setRGB('0000FF'); // Azul
            }

            // Color para G (Porcentaje)
            if ($porcentaje === 'NC') {
                $sheet->getStyle($porcentajeCell)->getFont()->getColor()->setRGB('000000'); // Negro para "NC"
            } elseif ($porcentaje == 0) {
                $sheet->getStyle($porcentajeCell)->getFont()->getColor()->setRGB('000000'); // Negro para cero
            } elseif ($porcentaje > 0) {
                $sheet->getStyle($porcentajeCell)->getFont()->getColor()->setRGB('FF0000'); // Rojo
            } else {
                $sheet->getStyle($porcentajeCell)->getFont()->getColor()->setRGB('0000FF'); // Azul
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
                        ->setCoordinates('H' . $rowNum)
                        ->setOffsetX(15)
                        ->setOffsetY(2)
                        ->setWorksheet($sheet);
                }
            }

            // Sumar para totales
            $totalMinus2 += $anioMinus2;
            $totalMinus1 += $anioMinus1;
            $totalActual += $anioActual;

            // Cambiar el valor de $colorAlternoComparativos para la siguiente fila
            $colorAlternoComparativos = !$colorAlternoComparativos;

            $numDelito++;
            $rowNum++;
        }

        // Escribir totales
        $sheet->setCellValue('A' . $rowNum, 'TOTAL');
        $sheet->getStyle('A' . $rowNum)->getFont()->setBold(true);
        $sheet->mergeCells("A$rowNum:B$rowNum");
        $sheet->setCellValue('C' . $rowNum, $totalMinus2);
        $sheet->setCellValue('D' . $rowNum, $totalMinus1);
        $sheet->setCellValue('E' . $rowNum, $totalActual);
        $sheet->setCellValue('F' . $rowNum, $totalActual - $totalMinus2);
        $sheet->setCellValue('G' . $rowNum, $totalActual - $totalMinus1);
        $sheet->setCellValue('H' . $rowNum, $totalMinus1 ? round((($totalActual - $totalMinus1) / $totalMinus1) * 100, 2) . '%' : '0%');
        $sheet->getStyle("A$rowNum:H$rowNum")->getFont()->setBold(true);

        $sheet->getStyle('C' . $rowNum)->getNumberFormat()->setFormatCode($numberFormat);
        $sheet->getStyle('D' . $rowNum)->getNumberFormat()->setFormatCode($numberFormat);
        $sheet->getStyle('E' . $rowNum)->getNumberFormat()->setFormatCode($numberFormat);
        $sheet->getStyle('F' . $rowNum)->getNumberFormat()->setFormatCode($numberFormat);
        $sheet->getStyle('G' . $rowNum)->getNumberFormat()->setFormatCode($numberFormat);


        // Cambiar el color de las celdas de las diferencias y el porcentaje de los totales
        $diffMinus2TotalCell = 'F' . $rowNum;
        $diffMinus1TotalCell = 'G' . $rowNum;
        $porcentajeTotalCell = 'H' . $rowNum;

        // Color para E (Diferencia - 2)
        if ($totalActual - $totalMinus2 > 0) {
            $sheet->getStyle($diffMinus2TotalCell)->getFont()->getColor()->setRGB('FF0000'); // Rojo
        } else {
            $sheet->getStyle($diffMinus2TotalCell)->getFont()->getColor()->setRGB('0000FF'); // Azul
        }

        // Color para F (Diferencia - 1)
        if ($totalActual - $totalMinus1 > 0) {
            $sheet->getStyle($diffMinus1TotalCell)->getFont()->getColor()->setRGB('FF0000'); // Rojo
        } else {
            $sheet->getStyle($diffMinus1TotalCell)->getFont()->getColor()->setRGB('0000FF'); // Azul
        }

        // Color para G (Porcentaje)
        if ($totalMinus1 ? round((($totalActual - $totalMinus1) / $totalMinus1) * 100, 2) > 0 : false) {
            $sheet->getStyle($porcentajeTotalCell)->getFont()->getColor()->setRGB('FF0000'); // Rojo
        } else {
            $sheet->getStyle($porcentajeTotalCell)->getFont()->getColor()->setRGB('0000FF'); // Azul
        }

        $rowNum++;

        // Espacio entre distritos
        $rowNum++;
    }

    // Aplicar bordes y estilos globales
    $sheet->getStyle("A1:H" . ($rowNum - 1))->applyFromArray([
        'borders' => [
            'allBorders' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                'color' => ['argb' => '646464'],
            ],
        ],
        'alignment' => [
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
        ],
    ]);

    // Ajustar anchos de columna
    $sheet->getColumnDimension('A')->setWidth(12); //Número
    $sheet->getColumnDimension('B')->setWidth(120); // Delito
    $sheet->getColumnDimension('C')->setWidth(12); // Año - 2
    $sheet->getColumnDimension('D')->setWidth(12); // Año - 1
    $sheet->getColumnDimension('E')->setWidth(12); // Año actual
    $sheet->getColumnDimension('F')->setWidth(15); // Dif. año - 2
    $sheet->getColumnDimension('G')->setWidth(15); // Dif. año - 1
    $sheet->getColumnDimension('H')->setWidth(15); // Porcentaje

    // Crear el writer de Excel y enviar al navegador
    $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
    ob_end_clean();
    $writer->save('php://output'); 
    exit;
} else {
    echo "Error en la conexión o en los datos del formulario.";
}
