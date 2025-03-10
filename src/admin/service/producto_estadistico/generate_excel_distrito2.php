<?php
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="reporte_distrito.xlsx"');
header('Cache-Control: max-age=0');
session_start();
include('C:/xampp/htdocs/sgr-dpe/service/connection.php');
require('C:/xampp/htdocs/sgr-dpe/vendor/autoload.php');  // PhpSpreadsheet library

use PhpOffice\PhpSpreadsheet\{Spreadsheet, IOFactory};
use PhpOffice\PhpSpreadsheet\Style\Fill;

// Obtener la conexión para la base de datos EJERCICIOS2
$conn = $connections['incidencia_sicap']['conn'];

if (empty($_POST['mesInicio']) || empty($_POST['mesFin']) || empty($_POST['anio'])) {
    die('Error: Todos los campos son obligatorios.');
}

$mesInicio = intval($_POST['mesInicio']);
$mesFin = intval($_POST['mesFin']);
$anio = intval($_POST['anio']);

if ($conn && $mesInicio && $mesFin && $anio) {
    // Array para convertir números de mes a texto
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
    $textoMesInicio = $mesesTexto[$mesInicio] ?? 'Mes inválido';
    $textoMesFin = $mesesTexto[$mesFin] ?? 'Mes inválido';

    // Crear un nuevo objeto Spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('INCIDENCIA DELICTIVA');

    // Consulta SQL
    $sqlDelitosComparativos = "
    SELECT Distrito, 
           DelitoAgrupado, 
           Año,
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
        $delitosComparativos[$row['Distrito']][$row['DelitoAgrupado']][$row['Año']] = $row['TotalDelitos'];
    }

    $rowNum = 1;

    $colorAlternoComparativos = true;

    foreach ($delitosComparativos as $distrito => $delitos) {
        $sheet->setCellValue('A' . $rowNum, mb_strtoupper("DISTRITO $distrito"));
        $sheet->mergeCells("A$rowNum:C$rowNum");
        $sheet->getStyle("A$rowNum")->getFont()->setBold(true)->setSize(14)->getColor()->setRGB('FFFFFF'); // Fuente blanca
        $sheet->getStyle("A$rowNum")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('152f4a'); // Fondo azul
        $rowNum++;

        // Encabezados de la tabla
        $sheet->setCellValue('A' . $rowNum, 'NÚMERO');
        $sheet->setCellValue('B' . $rowNum, 'DELITO');
        $sheet->setCellValue('C' . $rowNum, $anio);
        $sheet->getStyle("A$rowNum:C$rowNum")->getFont()->setBold(true)->getColor()->setRGB('FFFFFF'); // Fuente blanca
        $sheet->getStyle("A$rowNum:C$rowNum")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('152f4a'); // Fondo azul
        $rowNum++;

        $numDelito = 1;

        // Ordenar los delitos por el total del año actual
        uasort($delitos, function ($a, $b) use ($anio) {
            $totalA = isset($a[$anio]) ? $a[$anio] : 0;
            $totalB = isset($b[$anio]) ? $b[$anio] : 0;
            return $totalB <=> $totalA;
        });

        // Inicializar acumuladores
        $totalActual = 0;

        foreach ($delitos as $delito => $años) {
            $anioActual = isset($años[$anio]) ? $años[$anio] : 0;

            // Alternar colores de fondo para las filas de delitos
            if ($colorAlternoComparativos) {
                // Gris claro
                $sheet->getStyle('A' . $rowNum . ':C' . $rowNum)
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setRGB('C6C6C6');
            } else {
                // Blanco
                $sheet->getStyle('A' . $rowNum . ':C' . $rowNum)
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setRGB('FFFFFF');
            }

            $sheet->setCellValue('A' . $rowNum, $numDelito); 
            $sheet->setCellValue('B' . $rowNum, $delito);
            $sheet->setCellValue('C' . $rowNum, $anioActual);

            $numberFormat = '#,##0';
            $sheet->getStyle('C' . $rowNum)->getNumberFormat()->setFormatCode($numberFormat);
            $sheet->getStyle('D' . $rowNum)->getNumberFormat()->setFormatCode($numberFormat);
            $sheet->getStyle('E' . $rowNum)->getNumberFormat()->setFormatCode($numberFormat);
            $sheet->getStyle('F' . $rowNum)->getNumberFormat()->setFormatCode($numberFormat);
            $sheet->getStyle('G' . $rowNum)->getNumberFormat()->setFormatCode($numberFormat);

            // Sumar para totales
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
        $sheet->setCellValue('C' . $rowNum, $totalActual);
        $sheet->getStyle("A$rowNum:C$rowNum")->getFont()->setBold(true);

        $sheet->getStyle('C' . $rowNum)->getNumberFormat()->setFormatCode($numberFormat);
        $sheet->getStyle('D' . $rowNum)->getNumberFormat()->setFormatCode($numberFormat);
        $sheet->getStyle('E' . $rowNum)->getNumberFormat()->setFormatCode($numberFormat);
        $sheet->getStyle('F' . $rowNum)->getNumberFormat()->setFormatCode($numberFormat);
        $sheet->getStyle('G' . $rowNum)->getNumberFormat()->setFormatCode($numberFormat);

        // Espacio entre distritos
        $rowNum++;
    }

    // Aplicar bordes y estilos globales
    $sheet->getStyle("A1:C" . ($rowNum - 1))->applyFromArray([
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
    $sheet->getColumnDimension('A')->setWidth(12); //Número
    $sheet->getColumnDimension('B')->setWidth(120); // Delito
    $sheet->getColumnDimension('E')->setWidth(12); // Año actual

    // Crear el writer de Excel y enviar al navegador
    $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
    ob_end_clean();
    $writer->save('php://output'); 
    exit;
} else {
    echo "Error en la conexión o en los datos del formulario.";
}
