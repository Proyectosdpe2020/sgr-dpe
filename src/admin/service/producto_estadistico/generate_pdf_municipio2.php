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
    // Consulta SQL 
    $sql = "
        WITH Totales AS (
    SELECT Municipio, 
           COUNT(*) AS totalDelitos
    FROM carpetasMapas
    WHERE Año = $anio
      AND Mes BETWEEN $mesInicio AND $mesFin -- Rango dinámico de meses
      AND Contar = 1 -- Condición adicional para los totales
    GROUP BY Municipio
)
SELECT Municipio,
       totalDelitos,
       (SELECT COUNT(*) 
        FROM carpetasMapas 
        WHERE Año = $anio
          AND Mes BETWEEN $mesInicio AND $mesFin -- Rango dinámico también para el total general
          AND Contar = 1) AS totalDelitosAll, -- Total general considerando el rango de meses
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

    // Título principal
    $pdf->SetTextColor(255, 255, 255); // Texto blanco
    $pdf->SetFont('Arial', 'B', 24);
    $pdf->SetY(15); // Posiciona en la parte superior
    $pdf->SetX(50); // Desplaza hacia la derecha
    $pdf->Cell(0, 10, utf8_decode('FISCALÍA GENERAL DEL ESTADO DE MICHOACÁN'), 0, 1, 'C');
    $pdf->Ln(5);

    // Texto descriptivo debajo del título
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('Arial', '', 18);
    $pdf->Cell(0, 10, utf8_decode('ESTADÍSTICA DE INCIDENCIA DELICTIVA'), 0, 1, 'C');

    // Espacio entre el bloque azul y el contenido
    $pdf->SetY(90);

    // Título "MUNICIPIOS"
    $pdf->SetFont('Arial', 'B', 40);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(0, 10, utf8_decode('MUNICIPIOS'), 0, 1, 'C');
    $pdf->Ln(5);

    // Línea divisoria decorativa
    $pdf->SetDrawColor(100, 100, 100); // Gris
    $pdf->SetLineWidth(0.8);
    $pdf->Line(20, $pdf->GetY(), 277, $pdf->GetY()); // Línea horizontal
    $pdf->Ln(12);

    // Información adicional 
    // Posición inicial para el rectángulo
    $x = 20; // Coordenada X del rectángulo
    $y = $pdf->GetY(); // Coordenada Y actual donde comienza el texto
    $width = 257; // Ancho del rectángulo (toda la página menos márgenes)
    $height = 25; // Alto del rectángulo, ajustado al contenido

    // Dibujar el rectángulo gris claro
    $pdf->SetFillColor(200, 200, 200); // Gris claro
    $pdf->Rect($x, $y, $width, $height, 'F'); // Dibuja el rectángulo (relleno)

    // Ajustar la posición del texto
    $pdf->SetFont('Arial', '', 14);
    $pdf->SetTextColor(0, 0, 0); // Texto negro

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

    $pdf->AddPage();
    $pdf->Image('D:/xampp/htdocs/sgr-dpe/assets/img/fge.png', 20, 10, 20);
    $pdf->Image('D:/xampp/htdocs/sgr-dpe/assets/img/Mich.png', 254, 10, 35);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(0, 7, utf8_decode('FISCALÍA GENERAL DEL ESTADO DE MICHOACÁN'), 0, 1, 'C');
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(0, 5, utf8_decode('RELACIÓN DE MUNICIPIOS ORDENADOS DE ACUERDO AL NÚMERO DE DELITOS'), 0, 1, 'C');
    $pdf->Cell(0, 7, utf8_decode('Y LUGAR QUE OCUPAN DE MAYOR A MENOR INCIDENCIA DELICTIVA'), 0, 1, 'C');
    $pdf->Ln(0);
    $textoSeleccion = mb_strtoupper("Periodo $textoMesInicio - $textoMesFin $anio");
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell(0, 7, utf8_decode($textoSeleccion), 0, 1, 'C');
    $pdf->Ln(4);

    // Ancho total de la tabla
    $totalTableWidth = 20 + 90 + 50 + 20; // Suma de los anchos de todas las columnas

    // Calcular el margen izquierdo para centrar la tabla en la página
    $pageWidth = $pdf->GetPageWidth();
    $leftMargin = ($pageWidth - $totalTableWidth) / 2;
    $pdf->SetLeftMargin($leftMargin);

    // Encabezados de tabla
    $pdf->SetFillColor(21, 47, 74);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetLineWidth(0.5);
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(20, 7, utf8_decode(mb_strtoupper('Número', 'UTF-8')), 1, 0, 'C', true);
    $pdf->Cell(90, 7, utf8_decode(mb_strtoupper('Municipio', 'UTF-8')), 1, 0, 'C', true);
    $pdf->Cell(50, 7, utf8_decode(mb_strtoupper('Número de delitos', 'UTF-8')), 1, 0, 'C', true);
    $pdf->Cell(20, 7, mb_strtoupper('Lugar', 'UTF-8'), 1, 0, 'C', true);
    $pdf->Ln();

    $colorAlterno = true;

    // Ordenar los municipios por total de delitos de mayor a menor
    usort($data, function ($a, $b) {
        return $b['totalDelitos'] - $a['totalDelitos']; // Orden descendente por totalDelitos
    });

    // Agregar filas a la tabla con el ranking de los municipios
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('Arial', '', 9);
    $ranking = 1;
    foreach ($data as $row) {
        // Alternar colores de fondo (blanco y gris)
        if ($colorAlterno) {
            $pdf->SetFillColor(198, 198, 198); // Gris claro
        } else {
            $pdf->SetFillColor(255, 255, 255); // Blanco
        }
        // Cambiar el valor de $colorAlterno para la siguiente fila
        $colorAlterno = !$colorAlterno;

        $pdf->Cell(20, 7, $ranking, 1, 0, 'C', true);
        $pdf->Cell(90, 7, utf8_decode(mb_strtoupper($row['Municipio'])), 1, 0, 'C', true);
        $pdf->Cell(50, 7, number_format($row['totalDelitos']), 1, 0, 'C', true);
        $pdf->Cell(20, 7, $row['ranking'], 1, 0, 'C', true);
        $pdf->Ln();
        $ranking++;
    }

    // Muestra el total de delitos en todos los municipios al final de la tabla
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(110, 7, strtoupper('Total de delitos'), 1, 0, 'C'); // Celda que ocupa el espacio de las otras
    $pdf->Cell(50, 7, number_format($totalDelitosAll), 1, 0, 'C'); // El total de delitos, centrado en la celda
    $pdf->Ln();

    // Consulta SQL para obtener los delitos agrupados por Fiscalía, Municipio y tipo de delito
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
ORDER BY Fiscalia, Municipio, SUM(COUNT(*)) OVER (PARTITION BY Fiscalia, Municipio, DelitoAgrupado) DESC, DelitoAgrupado, Año DESC;
 ";

    $stmtDelitosComparativos = sqlsrv_query($conn, $sqlDelitosComparativos);

    if ($stmtDelitosComparativos === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    $delitosComparativos = [];
    while ($row = sqlsrv_fetch_array($stmtDelitosComparativos, SQLSRV_FETCH_ASSOC)) {
        $delitosComparativos[$row['Fiscalia']][$row['Municipio']][$row['DelitoAgrupado']][$row['Anio']] = $row['TotalDelitos'];
    }

    foreach ($delitosComparativos as $fiscalia => $municipios) {
        $pdf->AddPage();
        $pageWidth = $pdf->GetPageWidth();
        $totalContentWidth = 190;
        $leftMargin = ($pageWidth - $totalContentWidth) / 2;
        $pdf->SetLeftMargin($leftMargin);
        $pdf->SetRightMargin($pageWidth - $leftMargin - $totalContentWidth);

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

        // Título principal
        $pdf->SetTextColor(255, 255, 255); // Texto blanco
        $pdf->SetFont('Arial', 'B', 24);
        $pdf->SetY(15); // Posiciona en la parte superior
        $pdf->SetX(95); // Desplaza hacia la derecha
        $pdf->Cell(0, 10, utf8_decode('FISCALÍA GENERAL DEL ESTADO DE MICHOACÁN'), 0, 1, 'C');
        $pdf->Ln(5);

        // Texto descriptivo debajo del título
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('Arial', '', 18);
        $pdf->Cell(0, 10, utf8_decode('ESTADÍSTICA DE INCIDENCIA DELICTIVA'), 0, 1, 'C');

        // Espacio entre el bloque azul y el contenido
        $pdf->SetY(90);

        // Título "FISCALÍA"
        $pdf->SetFont('Arial', 'B', 40);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(0, 10, utf8_decode(mb_strtoupper("FISCALÍA $fiscalia")), 0, 1, 'C');
        $pdf->Ln(5);

        // Línea divisoria decorativa
        $pdf->SetDrawColor(100, 100, 100); // Gris
        $pdf->SetLineWidth(0.8);
        $pdf->Line(20, $pdf->GetY(), 277, $pdf->GetY()); // Línea horizontal
        $pdf->Ln(12);

        // Información adicional 
        // Guardar la posición inicial para calcular la altura necesaria
        $x = 20; // Coordenada X del rectángulo
        $y = $pdf->GetY(); // Coordenada Y actual donde comienza el texto
        $width = 257; // Ancho del rectángulo (toda la página menos márgenes)

        // Clonar la posición Y para el texto
        $textY = $y;

        // Ajustar la posición del texto
        $pdf->SetFont('Arial', '', 14);
        $pdf->SetTextColor(0, 0, 0); // Texto negro

        // Calcular altura necesaria sin dibujar el texto
        $initialY = $pdf->GetY(); // Guarda la posición inicial
        $pdf->MultiCell(0, 10, utf8_decode(mb_strtoupper("Dirección General de Tecnologías de la Información, Planeación y Estadística")), 0, 'C', false);
        $pdf->Ln(4); // Espaciado entre los textos
        $pdf->MultiCell(0, 10, utf8_decode(mb_strtoupper("Dirección de Planeación y Estadística")), 0, 'C', false);
        $finalY = $pdf->GetY(); // Guarda la posición después del texto

        // Calcular la altura total del rectángulo
        $height = $finalY - $initialY;

        // Dibujar el rectángulo antes de escribir el texto
        $pdf->SetFillColor(200, 200, 200); // Gris claro
        $pdf->Rect($x, $initialY, $width, $height, 'F'); // Dibuja el rectángulo (relleno)

        // Restaurar la posición inicial y escribir el texto
        $pdf->SetY($textY); // Vuelve a la posición inicial del texto
        $pdf->SetFont('Arial', '', 14);
        $pdf->SetTextColor(0, 0, 0); // Texto negro
        $pdf->MultiCell(0, 10, utf8_decode(mb_strtoupper("Dirección General de Tecnologías de la Información, Planeación y Estadística")), 0, 'C', false);
        $pdf->Ln(4);
        $pdf->MultiCell(0, 10, utf8_decode(mb_strtoupper("Dirección de Planeación y Estadística")), 0, 'C', false);

        // Bloque del periodo
        $pdf->Ln(15);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, utf8_decode(mb_strtoupper("PERIODO $textoMesInicio - $textoMesFin $anio")), 0, 1, 'C');
        $pdf->Ln(7);
        $pdf->MultiCell(0, 5, utf8_decode('(S.I. y S.A.)'), 0, 'C');

        foreach ($municipios as $municipio => $delitos) {
            $pdf->AddPage();
            $pdf->Image('D:/xampp/htdocs/sgr-dpe/assets/img/fge.png', 20, 10, 20);
            $pdf->Image('D:/xampp/htdocs/sgr-dpe/assets/img/Mich.png', 254, 10, 35);
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(0, 10, utf8_decode(mb_strtoupper("FISCALÍA GENERAL DEL ESTADO DE MICHOACÁN")), 0, 1, 'C');
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->Cell(0, 10, utf8_decode(mb_strtoupper("Incidencia delictiva por averiguación previa registrada en el municipio de $municipio")), 0, 1, 'C');
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->Cell(0, 10, utf8_decode(mb_strtoupper("Periodo de $textoMesInicio - $textoMesFin $anio")), 0, 1, 'C');

            $pdf->Ln(3);

            // Ordenar los delitos en base al total del año seleccionado
            uasort($delitos, function ($a, $b) use ($anio) {
                $totalA = isset($a[$anio]) ? $a[$anio] : 0; // Total para el año seleccionado en el delito A
                $totalB = isset($b[$anio]) ? $b[$anio] : 0; // Total para el año seleccionado en el delito B
                return $totalB - $totalA; // Orden descendente
            });

            // Obtener los 5 primeros delitos más frecuentes
            $topDelitos = array_slice($delitos, 0, 5, true);

            // Colores para el año actual
            $colorAnioActual = [21, 47, 74]; // Año actual

            // Preparar los datos para la gráfica de barras
            $delitosNames = array();
            $delitosValues = array();

            foreach ($topDelitos as $delito => $años) {
                $totalAnio = isset($años[$anio]) ? $años[$anio] : 0; // Solo el total del año actual
                $delitosNames[] = utf8_decode($delito);
                $delitosValues[] = [$totalAnio]; // Solo el total para el año actual
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

            $municipioX = $graphX + 30; // Coordenada X (ajustada a la derecha, alineada con la gráfica)
            $municipioY = $graphY - 20;

            $currentY = $graphY;

            // Mostrar el nombre del municipio encima de la gráfica
            $pdf->SetXY($municipioX, $municipioY); // Posición exacta para el texto
            $pdf->SetFont('Arial', 'B', 10); // Configura la fuente
            $pdf->SetTextColor(0, 0, 0); // Color negro
            $pdf->Cell(0, 10, utf8_decode(mb_strtoupper($municipio)), 0, 0, 'L');
            $pdf->Ln(5);
            $comparativoX = $graphX + 9.5;
            $pdf->SetXY($comparativoX, $pdf->GetY());
            $pdf->SetFont('Arial', '', 8);
            $pdf->Cell(0, 10, utf8_decode(mb_strtoupper("Periodo de $textoMesInicio - $textoMesFin $anio")), 0, 1, 'C');
            $pdf->SetY($graphY);

            // Calcular el ancho total de la gráfica
            $graphTotalWidth = count($delitosValues) * ($spaceBetweenDelitos + $barWidth * 3) - 33.9; // Ancho total de la gráfica

            // Calcular la posición final de la gráfica
            $legendY = $graphY + $graphHeight + 10; // Posicionar la leyenda 10 unidades debajo de la gráfica

            // Calcular la posición X centrada
            $legendTotalWidth = 40; // Ancho estimado (cuadro + espacio + texto)
            $legendX = $graphX + ($graphTotalWidth - $legendTotalWidth) / 2 + 15; // Centrar la leyenda respecto al ancho total de la gráfica

            // Dibujar la leyenda explicativa de los colores
            $pdf->SetFont('Arial', '', 8);
            $pdf->SetTextColor(0, 0, 0); // Color negro para el texto

            // Leyenda Año actual
            $pdf->SetXY($legendX, $legendY); // Posición inicial para la leyenda
            $pdf->SetFillColor($colorAnioActual[0], $colorAnioActual[1], $colorAnioActual[2]); // Color del año actual
            $pdf->Rect($pdf->GetX(), $pdf->GetY(), 4, 4, 'F'); // Cuadro de color reducido a 4x4
            $pdf->SetXY($legendX + 6, $legendY); // Mover el texto del año más a la derecha (6 unidades después del cuadro)
            $pdf->Cell(0, 4, utf8_decode($anio), 0, 1, 'L'); // Texto del año

            $pdf->Ln(10);

            // Dibujar la gráfica de barras
            $pdf->SetFillColor(200, 220, 255); // Color de relleno de las barras
            $pdf->SetTextColor(0, 0, 0); // Color del texto

            foreach ($delitosValues as $index => $values) {
                $delitoName = $delitosNames[$index];

                // Dibujar la barra para cada delito
                foreach ($values as $yearIndex => $value) {
                    // Calcular la altura de la barra proporcional
                    $barHeight = ($value / $maxValue) * $graphHeight;

                    // Posición X ajustada para la separación de las barras
                    $barX = $graphX + $index * ($barWidth + $spaceBetweenDelitos) + $yearIndex * ($barWidth + $spaceBetweenBars);

                    // Seleccionar el color correspondiente al año
                    $pdf->SetFillColor($colorAnioActual[0], $colorAnioActual[1], $colorAnioActual[2]); // Año actual

                    // Dibujar la barra
                    $pdf->Rect($barX, $graphY + $graphHeight - $barHeight, $barWidth, $barHeight, 'F');

                    $formattedValue = number_format($value);

                    // Colocar la cantidad de delitos encima de cada barra
                    $pdf->SetFont('Arial', 'B', 6);  // Ajusta el tamaño de la fuente si es necesario
                    $pdf->Text($barX - 0.5, $graphY + $graphHeight - $barHeight - 2, (string)$formattedValue);
                }
                $currentY += 30;
            }

            //Añadir los nombres de los delitos debajo de las barras
            $pdf->SetFont('Arial', '', 6); // Configura la fuente para los nombres de los delitos
            $pdf->SetTextColor(0, 0, 0); // Asegura que el texto sea negro
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
                $textY = $graphY + $graphHeight + 5; // Ajusta 5 unidades debajo de la base de la gráfica

                // Dibujar el nombre del delito
                $pdf->Text($textX, $textY, $delitoName);
            }

            $pdf->SetY($graphY - 50);

            $colWidthNumero = 15;
            $colWidthDelito = 80;
            $colWidthAnio = 15;

            $tableWidth = $colWidthNumero + $colWidthDelito + $colWidthAnio; // Solo se muestra el año actual

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

            // Inicializar las sumas solo para el año actual
            $sumaAnio = 0;

            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFont('Arial', '', 8);
            $numero = 1;

            $colorAlterno = true;

            foreach ($delitos as $delito => $años) {
                // Alternar colores de fondo (blanco y gris)
                if ($colorAlterno) {
                    $pdf->SetFillColor(198, 198, 198); // Gris claro
                } else {
                    $pdf->SetFillColor(255, 255, 255); // Blanco
                }
                $colorAlterno = !$colorAlterno;

                $pdf->SetX($leftMargin);
                $pdf->Cell($colWidthNumero, 7, $numero++, 1, 0, 'C', true);
                $abbreviatedDelito = utf8_decode($delito);
                if (strlen($abbreviatedDelito) > 55) {
                    $abbreviatedDelito = substr($abbreviatedDelito, 0, 55) . '...'; // Abreviar si es muy largo
                }
                $pdf->Cell($colWidthDelito, 7, $abbreviatedDelito, 1, 0, 'L', true);

                // Solo el total del año actual
                $totalAnio = isset($años[$anio]) ? $años[$anio] : 0;
                $totalAnioFormateado = number_format($totalAnio);
                $pdf->Cell($colWidthAnio, 7, $totalAnioFormateado, 1, 0, 'C', true);

                // Sumar los valores para el total del año actual
                $sumaAnio += $totalAnio;

                $pdf->Ln();
            }

            // Fila de los totales
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->SetX($leftMargin);
            $pdf->Cell($colWidthNumero + $colWidthDelito, 7, 'TOTAL', 1, 0, 'C');
            $sumaAnioFormateada = number_format($sumaAnio);
            $pdf->Cell($colWidthAnio, 7, $sumaAnioFormateada, 1, 0, 'C'); // Total del año actual
        }
        $pdf->Ln(10); // Espaciado entre Fiscalías
    }
    // Mostrar PDF
    $pdf->Output('I', 'reporte_municipio.pdf');
} else {
    echo "Error en la conexión o en los datos del formulario.";
}
