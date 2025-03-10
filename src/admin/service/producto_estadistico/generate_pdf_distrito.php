<?php
session_start();
include('D:/xampp/htdocs/sgr-dpe/service/connection.php');
require('D:/xampp/htdocs/sgr-dpe/fpdf/fpdf.php');

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
    $pdf->Image('D:/xampp/htdocs/sgr-dpe/assets/img/1.3 FGE dorado.png', 20, 10, 30);
    $pageHeight = $pdf->GetPageHeight();
    $pageWidth = $pdf->GetPageWidth();
    $imageWidth = 40;
    $imageHeight = 20;
    $x = 10;
    $y = $pageHeight - $imageHeight - 10;
    $pdf->Image('D:/xampp/htdocs/sgr-dpe/assets/img/Mich.png', $x, $y, $imageWidth, $imageHeight);

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
        $pdf->Image('D:/xampp/htdocs/sgr-dpe/assets/img/fge.png', 20, 10, 20);
        $pdf->Image('D:/xampp/htdocs/sgr-dpe/assets/img/Mich.png', 254, 10, 35);
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

        // Colores para cada año
        $colorAnioMinus2 = [192, 159, 119]; // Año -2
        $colorAnioMinus1 = [21, 47, 74];    // Año -1
        $colorAnioActual = [198, 198, 198]; // Año actual

        // Preparar los datos para la gráfica de barras
        $delitosNames = array();
        $delitosValues = array();

        foreach ($topDelitos as $delito => $años) {
            $totalAnioMinus2 = isset($años[$anio - 2]) ? $años[$anio - 2] : 0;
            $totalAnioMinus1 = isset($años[$anio - 1]) ? $años[$anio - 1] : 0;
            $totalAnio = isset($años[$anio]) ? $años[$anio] : 0;

            $delitosNames[] = utf8_decode($delito);
            $delitosValues[] = [$totalAnioMinus2, $totalAnioMinus1, $totalAnio];
        }

        // Tamaño y posiciones
        $graphX = 211.4; // X de inicio
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
        $comparativoX = $graphX + 49.5;
        $pdf->SetXY($comparativoX, $pdf->GetY());
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell(0, 10, utf8_decode(mb_strtoupper("Comparativo de $textoMesInicio - $textoMesFin " . ($anio - 2) . " - " . ($anio - 1) . " - $anio")), 0, 1, 'C');
        $pdf->SetY($graphY);

        // Calcular el ancho total de la gráfica
        $graphTotalWidth = count($delitosValues) * ($spaceBetweenDelitos + $barWidth * 3) - 33.9; // Ancho total de la gráfica

        // Calcular la posición final de la gráfica
        $legendY = $graphY + $graphHeight + 10; 

        // Calcular la posición X centrada
        $legendTotalWidth = 40; // Ancho estimado (cuadro + espacio + texto)
        $legendX = $graphX + ($graphTotalWidth - $legendTotalWidth) / 2 + 15; // Centrar la leyenda respecto al ancho total de la gráfica

        // Dibujar la leyenda explicativa de los colores
        $pdf->SetFont('Arial', '', 8);
        $pdf->SetTextColor(0, 0, 0); 

        // Leyenda Año -2
        $pdf->SetXY($legendX, $legendY); // Posición inicial para la leyenda
        $pdf->SetFillColor($colorAnioMinus2[0], $colorAnioMinus2[1], $colorAnioMinus2[2]); // Color del año -2
        $pdf->Rect($pdf->GetX(), $pdf->GetY(), 4, 4, 'F'); // Cuadro de color reducido a 4x4
        $pdf->SetXY($legendX + 6, $legendY); // Mover el texto del año más a la derecha 
        $pdf->Cell(0, 4, utf8_decode($anio - 2), 0, 1, 'L');

        // Leyenda Año -1
        $pdf->SetXY($legendX, $pdf->GetY() + 3); // Ajustar posición para la siguiente línea
        $pdf->SetFillColor($colorAnioMinus1[0], $colorAnioMinus1[1], $colorAnioMinus1[2]); // Color del año -1
        $pdf->Rect($pdf->GetX(), $pdf->GetY(), 4, 4, 'F'); // Cuadro de color reducido a 4x4
        $pdf->SetXY($legendX + 6, $pdf->GetY()); // Mover el texto del año más a la derecha
        $pdf->Cell(0, 4, utf8_decode($anio - 1), 0, 1, 'L'); 

        // Leyenda Año actual
        $pdf->SetXY($legendX, $pdf->GetY() + 3); // Ajustar posición para la siguiente línea
        $pdf->SetFillColor($colorAnioActual[0], $colorAnioActual[1], $colorAnioActual[2]); // Color del año actual
        $pdf->Rect($pdf->GetX(), $pdf->GetY(), 4, 4, 'F'); // Cuadro de color reducido a 4x4
        $pdf->SetXY($legendX + 6, $pdf->GetY()); // Mover el texto del año más a la derecha
        $pdf->Cell(0, 4, utf8_decode($anio), 0, 1, 'L'); 

        $pdf->Ln(10);

        // Dibujar la gráfica de barras
        $pdf->SetFillColor(200, 220, 255); // Color de relleno de las barras
        $pdf->SetTextColor(0, 0, 0); 

        foreach ($delitosValues as $index => $values) {
            $delitoName = $delitosNames[$index];

            foreach ($values as $yearIndex => $value) {
                $barHeight = ($value / $maxValue) * $graphHeight;

                $barX = $graphX + $index * ($barWidth + $spaceBetweenDelitos) + $yearIndex * ($barWidth + $spaceBetweenBars);

                if ($yearIndex == 0) {
                    $pdf->SetFillColor($colorAnioMinus2[0], $colorAnioMinus2[1], $colorAnioMinus2[2]); // Año -2
                } elseif ($yearIndex == 1) {
                    $pdf->SetFillColor($colorAnioMinus1[0], $colorAnioMinus1[1], $colorAnioMinus1[2]); // Año -1
                } else {
                    $pdf->SetFillColor($colorAnioActual[0], $colorAnioActual[1], $colorAnioActual[2]); // Año actual
                }

                $pdf->Rect($barX, $graphY + $graphHeight - $barHeight, $barWidth, $barHeight, 'F');

                $formattedValue = number_format($value);

                $pdf->SetFont('Arial', 'B', 6);
                $pdf->Text($barX - 1, $graphY + $graphHeight - $barHeight - 2, number_format($value, 0, '.', ','));
            }
            $currentY += 30;
        }
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
        $pdf->Cell($colWidthNumero, 14, utf8_decode('NÚMERO'), 1, 0, 'C', true);
        $pdf->Cell($colWidthDelito, 14, utf8_decode('DELITOS'), 1, 0, 'C', true);
        $pdf->Cell($colWidthAnio * 3, 7, utf8_decode('TOTAL'), 1, 0, 'C', true);
        $pdf->Cell(30, 7, utf8_decode('DIFERENCIA'), 1, 0, 'C', true);
        $pdf->Cell(27, 7, utf8_decode('PORCENTAJE'), 1, 0, 'C', true);
        $pdf->Ln();
        $espacioExtra = 51.5;
        $pdf->Cell($espacioExtra, 7, '', 0, 0, 'C');
        $pdf->Cell($colWidthAnio, 7, utf8_decode($anio - 2), 1, 0, 'C', true);
        $pdf->Cell($colWidthAnio, 7, utf8_decode($anio - 1), 1, 0, 'C', true);
        $pdf->Cell($colWidthAnio, 7, utf8_decode($anio), 1, 0, 'C', true);
        $pdf->SetFont('Arial', 'B', 7.5);
        $pdf->Cell($colWidthAnio, 7, utf8_decode(($anio - 2) . '-' . $anio), 1, 0, 'C', true);
        $pdf->Cell($colWidthAnio, 7, utf8_decode(($anio - 1) . '-' . $anio), 1, 0, 'C', true);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(27, 7, utf8_decode(($anio - 1) . '-' . $anio), 1, 1, 'C', true);

        // Inicializar las sumas por cada año
        $sumaAnioMinus2 = 0;
        $sumaAnioMinus1 = 0;
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
            $totalAnioMinus2 = isset($años[$anio - 2]) ? $años[$anio - 2] : 0;
            $totalAnioMinus1 = isset($años[$anio - 1]) ? $años[$anio - 1] : 0;
            $totalAnio = isset($años[$anio]) ? $años[$anio] : 0;

            // Mostrar los valores para cada año
            $pdf->Cell($colWidthAnio, 7, number_format($totalAnioMinus2, 0, '.', ','), 1, 0, 'C', true);
            $pdf->Cell($colWidthAnio, 7, number_format($totalAnioMinus1, 0, '.', ','), 1, 0, 'C', true);
            $pdf->Cell($colWidthAnio, 7, number_format($totalAnio, 0, '.', ','), 1, 0, 'C', true);


            // Sumar los valores para los totales de cada año
            $sumaAnioMinus2 += $totalAnioMinus2;
            $sumaAnioMinus1 += $totalAnioMinus1;
            $sumaAnio += $totalAnio;

            // Calcular las diferencias 
            $diffAnoMinus1 = $totalAnio - $totalAnioMinus1;  // Diferencia entre el año actual y el anterior
            $diffAnoMinus2 = $totalAnio - $totalAnioMinus2;  // Diferencia entre el año actual y el año antepasado

            // Calcular el porcentaje 
            if ($totalAnioMinus1 > 0) {
                $porcentajeAnoMinus1 = ($diffAnoMinus1 / $totalAnioMinus1) * 100;
                $porcentajeText = number_format($porcentajeAnoMinus1, 2) . '%';  // Porcentaje calculado
            } else {
                $porcentajeText = 'NC';  // No calculado si el divisor es cero
            }

            // Mostrar las diferencias con colores dinámicos
            if ($diffAnoMinus2 > 0) {
                $pdf->SetTextColor(255, 0, 0); // Rojo para positivo
            } else {
                $pdf->SetTextColor(0, 0, 255); // Azul para negativo o cero
            }
            $pdf->Cell($colWidthAnio, 7, $diffAnoMinus2, 1, 0, 'C', true);

            if ($diffAnoMinus1 > 0) {
                $pdf->SetTextColor(255, 0, 0); // Rojo para positivo
            } else {
                $pdf->SetTextColor(0, 0, 255); // Azul para negativo o cero
            }
            $pdf->Cell($colWidthAnio, 7, $diffAnoMinus1, 1, 0, 'C', true);

            // Mostrar porcentaje
            if ($porcentajeText === 'NC') {
                $pdf->SetTextColor(0, 0, 0);
                $pdf->Cell(27, 7, $porcentajeText, 1, 1, 'C', true);  // Mostrar "NC" en lugar de porcentaje
            } else {
                // Determinar flecha y color
                if ($porcentajeAnoMinus1 > 0) {
                    $flecha = 'D:/xampp/htdocs/sgr-dpe/assets/img/up.png'; // Ruta de la imagen de flecha hacia arriba
                    $color = [255, 0, 0];  // Rojo para positivo
                } elseif ($porcentajeAnoMinus1 < 0) {
                    $flecha = 'D:/xampp/htdocs/sgr-dpe/assets/img/down.png'; // Ruta de la imagen de flecha hacia abajo
                    $color = [0, 0, 255]; // Azul para negativo
                } else {
                    $flecha = '';  // Sin flecha
                    $color = [0, 0, 0]; // Color negro para 0%
                }

                // Mostrar porcentaje
                $porcentajeTextFormatted = number_format($porcentajeAnoMinus1, 2) . '%';
                $pdf->SetTextColor($color[0], $color[1], $color[2]);

                // Insertar porcentaje en celda
                $startX = $pdf->GetX(); // Obtener posición inicial X
                $startY = $pdf->GetY(); // Obtener posición inicial Y
                $pdf->Cell(27, 7, $porcentajeTextFormatted, 1, 0, 'C', true);

                // Insertar la flecha (si aplica)
                if ($flecha !== '') {
                    $imageX = $startX + 5; // Ajustar posición X de la imagen dentro de la celda
                    $imageY = $startY + 1.5; // Ajustar posición Y para centrar la imagen verticalmente
                    $pdf->Image($flecha, $imageX, $imageY, 3, 3); // Insertar imagen (ancho y alto de 3)
                }

                // Restablecer el color a negro
                $pdf->SetTextColor(0, 0, 0);
                $pdf->Ln(); // Mover a la siguiente línea
            }
        }
        //Fila de los totales
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetX($leftMargin);
        $pdf->Cell($colWidthNumero + $colWidthDelito, 7, 'TOTAL', 1, 0, 'C');
        $pdf->Cell($colWidthAnio, 7, number_format($sumaAnioMinus2, 0, '.', ','), 1, 0, 'C');  // Total para el año -2
        $pdf->Cell($colWidthAnio, 7, number_format($sumaAnioMinus1, 0, '.', ','), 1, 0, 'C');  // Total para el año -1
        $pdf->Cell($colWidthAnio, 7, number_format($sumaAnio, 0, '.', ','), 1, 0, 'C');  // Total para el año actual


        // Cálculo de las diferencias para el total
        $diffTotalMinus2 = $sumaAnio - $sumaAnioMinus2;  // Diferencia para el año -2
        $diffTotalMinus1 = $sumaAnio - $sumaAnioMinus1;  // Diferencia para el año -1

        // Mostrar diferencias y porcentajes para los totales
        if ($diffTotalMinus2 < 0 || $diffTotalMinus2 == 0) {
            $pdf->SetTextColor(0, 0, 255); // Azul para negativo o cero
        } else {
            $pdf->SetTextColor(255, 0, 0); // Rojo para positivo
        }
        $pdf->Cell($colWidthAnio, 7, number_format($diffTotalMinus2, 0, '.', ','), 1, 0, 'C');

        if ($diffTotalMinus1 < 0 || $diffTotalMinus1 == 0) {
            $pdf->SetTextColor(0, 0, 255); // Azul para negativo o cero
        } else {
            $pdf->SetTextColor(255, 0, 0); // Rojo para positivo
        }
        $pdf->Cell($colWidthAnio, 7, number_format($diffTotalMinus1, 0, '.', ','), 1, 0, 'C');

        // Calcular porcentaje para el total
        $porcentajeTotal = $sumaAnioMinus1 > 0 ? ($diffTotalMinus1 / $sumaAnioMinus1) * 100 : 0;
        $porcentajeTotalText = number_format($porcentajeTotal, 2) . '%';

        // Cambiar el color del porcentaje
        if ($porcentajeTotal < 0) {
            $pdf->SetTextColor(0, 0, 255); // Azul para porcentaje negativo
        } else {
            $pdf->SetTextColor(255, 0, 0); // Rojo para porcentaje positivo
        }
        $pdf->Cell(27, 7, $porcentajeTotalText, 1, 1, 'C');

        // Restablecer el color a negro después de mostrar los datos
        $pdf->SetTextColor(0, 0, 0);
    }
    $pdf->Ln(10); // Espaciado entre Fiscalías
    // Mostrar PDF
    $pdf->Output('I', 'reporte_distrito.pdf');
} else {
    echo "Error en la conexión o en los datos del formulario.";
}
