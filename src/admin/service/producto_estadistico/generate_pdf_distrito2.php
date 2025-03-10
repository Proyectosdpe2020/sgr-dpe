<?php
session_start();
include('C:/xampp/htdocs/sgr-dpe/service/connection.php');
require('C:/xampp/htdocs/sgr-dpe/fpdf/fpdf.php');

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

    // Configuración de PDF usando FPDF
    $pdf = new FPDF('L', 'mm', 'A4');
    $pdf->AddPage();

    // Fondo superior
    $pdf->SetFillColor(21, 47, 74);
    $pdf->Rect(0, 0, 297, 50, 'F');

    // Espacios para logos
    $pdf->Image('C:/xampp/htdocs/sgr-dpe/assets/img/1.3 FGE dorado.png', 20, 10, 30);
    $pageHeight = $pdf->GetPageHeight();
    $pageWidth = $pdf->GetPageWidth();
    $imageWidth = 40;
    $imageHeight = 20;
    $x = 10;
    $y = $pageHeight - $imageHeight - 10;
    $pdf->Image('C:/xampp/htdocs/sgr-dpe/assets/img/Mich.png', $x, $y, $imageWidth, $imageHeight);

    // Encabezado
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('Arial', 'B', 24);
    $pdf->SetY(15); // Posiciona en la parte superior
    $pdf->SetX(50); // Desplaza hacia la derecha
    $pdf->Cell(0, 10, utf8_decode('FISCALÍA GENERAL DEL ESTADO DE MICHOACÁN'), 0, 1, 'C');
    $pdf->Ln(5);

    // Título principal
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('Arial', '', 18);
    $pdf->Cell(0, 10, utf8_decode('ESTADÍSTICA DE INCIDENCIA DELICTIVA'), 0, 1, 'C');

    // Espacio entre el bloque azul y el contenido
    $pdf->SetY(90);

    // Título "DISTRITOS JUDICIALES"
    $pdf->SetFont('Arial', 'B', 40);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(0, 10, utf8_decode('DISTRITOS JUDICIALES'), 0, 1, 'C');
    $pdf->Ln(5);

    // Línea divisoria 
    $pdf->SetDrawColor(100, 100, 100); // Gris
    $pdf->SetLineWidth(0.8);
    $pdf->Line(20, $pdf->GetY(), 277, $pdf->GetY());
    $pdf->Ln(12);

    // Posición inicial para el rectángulo
    $x = 20; // Coordenada X del rectángulo
    $y = $pdf->GetY(); // Coordenada Y actual donde comienza el texto
    $width = 257; // Ancho del rectángulo (toda la página menos márgenes)
    $height = 25; // Alto del rectángulo, ajustado al contenido

    // Dibujar el rectángulo 
    $pdf->SetFillColor(200, 200, 200);
    $pdf->Rect($x, $y, $width, $height, 'F');

    // Ajustar la posición del texto
    $pdf->SetFont('Arial', '', 14);
    $pdf->SetTextColor(0, 0, 0);

    // Escribir el texto dentro del rectángulo
    $pdf->MultiCell(0, 10, utf8_decode(mb_strtoupper("Dirección General de Tecnologías de la Información, Planeación y Estadística")), 0, 'C', false);
    $pdf->Ln(4);
    $pdf->MultiCell(0, 10, utf8_decode(mb_strtoupper("Dirección de Planeación y Estadística")), 0, 'C', false);

    // Bloque del periodo
    $pdf->Ln(15);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, utf8_decode(mb_strtoupper("PERIODO $textoMesInicio - $textoMesFin $anio")), 0, 1, 'C');
    $pdf->Ln(7);
    $pdf->MultiCell(0, 5, utf8_decode('(S.I. y S.A.)'), 0, 'C');

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

    foreach ($delitosComparativos as $distrito => $delitos) {
        $pageWidth = $pdf->GetPageWidth();
        $totalContentWidth = 190;
        $leftMargin = ($pageWidth - $totalContentWidth) / 2;
        $pdf->SetLeftMargin($leftMargin);
        $pdf->SetRightMargin($pageWidth - $leftMargin - $totalContentWidth);
        $pdf->AddPage();
        $pdf->Image('C:/xampp/htdocs/sgr-dpe/assets/img/fge.png', 20, 10, 20);
        $pdf->Image('C:/xampp/htdocs/sgr-dpe/assets/img/Mich.png', 254, 10, 35);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, utf8_decode(mb_strtoupper("FISCALÍA GENERAL DEL ESTADO DE MICHOACÁN")), 0, 1, 'C');
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(0, 10, utf8_decode(mb_strtoupper("Incidencia delictiva por averiguación previa registrada en el distrito judicial de $distrito")), 0, 1, 'C');
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(0, 10, utf8_decode(mb_strtoupper("Comparativo de $textoMesInicio - $textoMesFin " . ($anio - 2) . " - " . ($anio - 1) . " - $anio")), 0, 1, 'C');

        $pdf->Ln(3);

        // Ordenar los delitos por el total de delitos en orden descendente para cada distrito
        foreach ($delitos as $delito => $años) {
            // Calcular el total de delitos para cada delito (sumando los valores por cada año)
            $totalDelitos = array_sum($años);
            $delitos[$delito]['total'] = $totalDelitos;  // Añadir el total como valor
        }

        // Ordenar los delitos en base al total del año seleccionado
        uasort($delitos, function ($a, $b) use ($anio) {
            $totalA = isset($a[$anio]) ? $a[$anio] : 0;
            $totalB = isset($b[$anio]) ? $b[$anio] : 0;
            return $totalB - $totalA; // Orden descendente
        });

        // Obtener los 5 primeros delitos más frecuentes
        $topDelitos = array_slice($delitos, 0, 5, true);

        // Colores el año
        $colorAnioActual = [21, 47, 74]; // Año actual

        // Preparar los datos para la gráfica de barras
        $delitosNames = array();
        $delitosValues = array();

        foreach ($topDelitos as $delito => $años) {
            $totalAnio = isset($años[$anio]) ? $años[$anio] : 0;
            $delitosNames[] = utf8_decode($delito);
            $delitosValues[] = [$totalAnio];
        }

        // Tamaño y posiciones
        $graphX = 171.4; // X de inicio
        $graphY = $pdf->GetY() + 50; // Y de inicio
        $graphWidth = 30; // Ancho de la gráfica
        $graphHeight = 40; // Altura de la gráfica
        $barWidth = 3; // Ancho de cada barra 
        $spaceBetweenBars = 2; // Espacio entre barras dentro de un mismo delito 
        $spaceBetweenDelitos = 14; // Espacio entre diferentes delitos
        $maxValue = max(array_map('max', $delitosValues)); // Valor máximo para ajustar las barras

        $municipioX = $graphX + 30; // Coordenada X 
        $municipioY = $graphY - 20;

        $currentY = $graphY;

        // Mostrar el nombre del municipio encima de la gráfica
        $pdf->SetXY($municipioX, $municipioY); // Posición exacta para el texto
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(0, 10, utf8_decode(mb_strtoupper($distrito)), 0, 0, 'L');
        $pdf->Ln(5);
        $comparativoX = $graphX + 9.5;
        $pdf->SetXY($comparativoX, $pdf->GetY());
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell(0, 10, utf8_decode(mb_strtoupper("Periodo de $textoMesInicio - $textoMesFin $anio")), 0, 1, 'C');
        $pdf->SetY($graphY);

        // Calcular el ancho total de la gráfica
        $graphTotalWidth = count($delitosValues) * ($spaceBetweenDelitos + $barWidth * 3) - 33.9; // Ancho total de la gráfica

        // Calcular la posición final de la gráfica
        $legendY = $graphY + $graphHeight + 10;

        // Calcular la posición X centrada
        $legendTotalWidth = 40; // Ancho estimado (cuadro + espacio + texto)
        $legendX = $graphX + ($graphTotalWidth - $legendTotalWidth) / 2 + 15; // Centrar la leyenda respecto al ancho total de la gráfica

        // Dibujar la gráfica de barras
        $pdf->SetFillColor(200, 220, 255); // Color de relleno de las barras
        $pdf->SetTextColor(0, 0, 0);

        foreach ($delitosValues as $index => $values) {
            $delitoName = $delitosNames[$index];

            foreach ($values as $yearIndex => $value) {
                $barHeight = ($value / $maxValue) * $graphHeight;

                $barX = $graphX + $index * ($barWidth + $spaceBetweenDelitos) + $yearIndex * ($barWidth + $spaceBetweenBars);

                // Seleccionar el color correspondiente al año
                $pdf->SetFillColor($colorAnioActual[0], $colorAnioActual[1], $colorAnioActual[2]); // Año actual

                $pdf->Rect($barX, $graphY + $graphHeight - $barHeight, $barWidth, $barHeight, 'F');

                $formattedValue = number_format($value);

                $pdf->SetFont('Arial', 'B', 6);
                $pdf->Text($barX - 1, $graphY + $graphHeight - $barHeight - 2, number_format($value, 0, '.', ','));
            }
        }

        // Ajustar la posición de la leyenda debajo de la gráfica
        $legendY = $graphY + $graphHeight + 10; // Espacio extra de 10 unidades debajo de la gráfica
        $pdf->SetY($legendY);

        // Dibujar la leyenda explicativa de los colores
        $pdf->SetFont('Arial', '', 8);
        $pdf->SetTextColor(0, 0, 0);

        // Leyenda Año actual
        $pdf->SetX($legendX); // Establece la posición horizontal de la leyenda
        $pdf->SetFillColor($colorAnioActual[0], $colorAnioActual[1], $colorAnioActual[2]); // Color del año actual
        $pdf->Rect($pdf->GetX(), $pdf->GetY(), 4, 4, 'F'); // Cuadro de color reducido a 4x4
        $pdf->SetX($legendX + 6); // Mover el texto del año más a la derecha
        $pdf->Cell(0, 4, utf8_decode($anio), 0, 1, 'L');

        //Añadir los nombres de los delitos debajo de las barras
        $pdf->SetFont('Arial', '', 6);
        $pdf->SetTextColor(0, 0, 0);
        $maxLength = 16;

        foreach ($delitosValues as $index => $values) {
            $delitoName = $delitosNames[$index]; // Obtiene el nombre del delito actual

            if (strlen($delitoName) > $maxLength) {
                $delitoName = substr($delitoName, 0, $maxLength - 3) . '...'; // Abreviar y agregar "..."
            }

            // Calcular posición X centrada para el nombre del delito
            $groupWidth = $barWidth * 3 + $spaceBetweenBars * 2; // Ancho total del grupo de barras
            $textX = $graphX + $index * ($barWidth + $spaceBetweenDelitos) + ($groupWidth - $pdf->GetStringWidth($delitoName)) / 2;

            // Calcular posición Y debajo de la gráfica
            $textY = $graphY + $graphHeight + 5;

            // Dibujar el nombre del delito
            $pdf->Text($textX, $textY, $delitoName);
        }
        $pdf->SetY($graphY - 50);

        $colWidthNumero = 15;
        $colWidthDelito = 80;
        $colWidthAnio = 15;

        $tableWidth = $colWidthNumero + $colWidthDelito + (3 * $colWidthAnio);

        $pageWidth = $pdf->GetPageWidth();
        $leftMargin = 10;

        $pdf->SetX($leftMargin);

        // Encabezados de la tabla
        $pdf->SetFillColor(21, 47, 74);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetLineWidth(0.5);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell($colWidthNumero, 7, utf8_decode('NÚMERO'), 1, 0, 'C', true);
        $pdf->Cell($colWidthDelito, 7, utf8_decode('DELITOS'), 1, 0, 'C', true);
        $pdf->Cell($colWidthAnio, 7, utf8_decode('TOTAL'), 1, 0, 'C', true);
        $pdf->Ln();
        $espacioExtra = 51.5;
        $pdf->Cell($espacioExtra, 7, '', 0, 0, 'C');

        // Inicializar las suma
        $sumaAnio = 0;

        // Mostrar los delitos por municipio
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('Arial', '', 8);
        $numero = 1;
        $maxLength = 55;

        $colorAlterno = true;

        foreach ($delitos as $delito => $años) {
            // Alternar colores de fondo 
            if ($colorAlterno) {
                $pdf->SetFillColor(198, 198, 198); // Gris claro
            } else {
                $pdf->SetFillColor(255, 255, 255); // Blanco
            }
            // Cambiar el valor de $colorAlterno para la siguiente fila
            $colorAlterno = !$colorAlterno;

            $pdf->SetX($leftMargin);
            $pdf->Cell($colWidthNumero, 7, $numero++, 1, 0, 'C', true);
            $abbreviatedDelito = utf8_decode($delito);
            if (strlen($abbreviatedDelito) > $maxLength) {
                $abbreviatedDelito = substr($abbreviatedDelito, 0, $maxLength) . '...';  // Abreviar con "..."
            }
            $pdf->Cell($colWidthDelito, 7, $abbreviatedDelito, 1, 0, 'L', true);

            // Delitos por año
            $totalAnio = isset($años[$anio]) ? $años[$anio] : 0;

            // Mostrar los valores para cada año
            $pdf->Cell($colWidthAnio, 7, number_format($totalAnio, 0, '.', ','), 1, 0, 'C', true);

            // Sumar los valores para los totales de cada año
            $sumaAnio += $totalAnio;

            // Restablecer el color a negro
            $pdf->SetTextColor(0, 0, 0);
            $pdf->Ln(); // Mover a la siguiente línea
        }
        //Fila de los totales
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetX($leftMargin);
        $pdf->Cell($colWidthNumero + $colWidthDelito, 7, 'TOTAL', 1, 0, 'C');
        $pdf->Cell($colWidthAnio, 7, number_format($sumaAnio, 0, '.', ','), 1, 0, 'C');  // Total para el año actual

    }
    $pdf->Ln(10); // Espaciado entre Fiscalías
    // Mostrar PDF
    $pdf->Output('I', 'reporte_distrito.pdf');
} else {
    echo "Error en la conexión o en los datos del formulario.";
}
