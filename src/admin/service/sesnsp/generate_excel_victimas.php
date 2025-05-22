<?php
ini_set('memory_limit', '2048M');
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Consulta_CNI_victimas.xlsx"');
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
$sql3 = "
    SELECT DISTINCT
    c.CarpetaID AS Id,
    del.DelitoID AS id_delito,
    vic.ID_Persona,
    vic.Tipo_Victima,
    vic.Tipo_Persona_Moral,
	vic.Sexo,
    vic.Genero,
    vic.Poblacion_Indigena,
    vic.Tipo_Discapacidad,
    vic.Fecha_de_Nacimiento,
    vic.Edad_de_la_Victima,
    vic.Nacionalidad,
	vic.Relacion_imputado,
    c.FechaInicio,
    CONVERT(VARCHAR(10), c.FechaInicio, 23) AS 'Fecha Inicio'
FROM dbo.Carpeta c
INNER JOIN dbo.Delito del ON c.CarpetaID = del.CarpetaID
INNER JOIN dbo.CatModalidadesEstadisticas mo ON del.CatModalidadesID = mo.CatModalidadesEstadisticasID
LEFT JOIN (
    SELECT 
        inv.CarpetaID,
        dv.CatModalidadesID,
        inv.InvolucradoID AS ID_Persona,
		ctv.cni_id AS 'Tipo_Victima',
        CONVERT(VARCHAR(20), 
            CASE 
               WHEN vo.Persona IN (0, 3, 4) THEN 5
                ELSE 6
            END
        ) AS Tipo_Persona_Moral,
			CASE 
				WHEN inv.Sexo = 1 THEN 1 -- Hombre
				WHEN inv.Sexo = 2 THEN 2 -- Mujer
				WHEN inv.Sexo = 3 THEN 3 --No identificado
				ELSE 3 --No identificado
			END 
			AS Sexo,
            CASE 
                WHEN inv.Sexo = 1 THEN 1 -- Masculino
                WHEN inv.Sexo = 2 THEN 2 -- Femenino
                WHEN inv.Sexo = 3 THEN 3 -- No identificado
                ELSE 3 -- No identificado
            END
         AS Genero,
        CASE 
			WHEN indigena.Nombre IS NULL OR indigena.Nombre = 'No identificada' THEN 0 
			ELSE 1 
		END AS Poblacion_Indigena,
		CASE 
			WHEN discapacidad.Nombre IS NULL OR discapacidad.Nombre = 'No identificada' THEN 0 
			ELSE 1 
		END AS Tipo_Discapacidad,
        CONVERT(VARCHAR(10), vsenap.FechaNacimiento, 23) AS Fecha_de_Nacimiento,
        inv.Edad AS Edad_de_la_Victima,
        nacion.cni_id AS 'Nacionalidad',
		ISNULL(rel.Nombre, 'No identificada') AS Relacion_imputado,
        vo.Victima,
        CONCAT(inv.CarpetaID, '', dv.CatModalidadesID) AS carpeta_modalidad
    FROM dbo.Involucrado inv
    INNER JOIN dbo.Carpeta c ON inv.CarpetaID = c.CarpetaID
    INNER JOIN dbo.VictimaOfendido vo ON vo.InvolucradoID = inv.InvolucradoID
    LEFT JOIN PRUEBA.dbo.DelitosVictima dv ON dv.VictimaID = vo.VictimaOfendidoID
    LEFT JOIN PRUEBA.dbo.VictimaSENAP vsenap ON vo.VictimaOfendidoID = vsenap.VictimaID
    LEFT JOIN PRUEBA.dbo.CatPoblacionesLGBTTI lgbtti ON vsenap.PoblacionLGBTTI = lgbtti.PoblacionLGBTTIID
    LEFT JOIN PRUEBA.dbo.CatPoblacionesIndigenas indigena ON vsenap.PoblacionIndigena = indigena.PoblacionIndigenaID
    LEFT JOIN PRUEBA.dbo.CatDiscapacidades discapacidad ON vsenap.TipoDiscapacidad = discapacidad.DiscapacidadID
    LEFT JOIN dbo.CatNacionalidades nacion ON vo.CatNacionalidadesID = nacion.CatNacionalidadesID
    LEFT JOIN dbo.CatTipoVictima ctv ON ctv.CatTipoVictimaID = vo.Persona
	LEFT JOIN dbo.CatRelacionVictimaImputado rel ON vsenap.RelacionImputado = rel.RelacionID
    WHERE CAST(c.FechaInicio AS DATE) BETWEEN '$fecha_inicial' AND '$fecha_final'
) vic ON vic.carpeta_modalidad = CONCAT(c.CarpetaID, '', del.CatModalidadesID)
WHERE
	contar = 1
    AND CAST(c.FechaInicio AS DATE) BETWEEN '$fecha_inicial' AND '$fecha_final'
	AND (
		mo.Nombre NOT LIKE '%hechos posiblemente constitutivos de delito%' 
		AND mo.Nombre NOT LIKE '%hechos (no localizados)%'
		AND mo.Nombre NOT LIKE '%sospecha de suicidio%'
		AND mo.Nombre NOT LIKE '%sospecha muerte natural%'
		AND mo.Nombre NOT LIKE '%recuperación de vehículos%'
		AND mo.Nombre NOT LIKE '%persona no localizada%'
	)
ORDER BY c.FechaInicio ASC, c.CarpetaID ASC;
";

$stmt3 = sqlsrv_query($conn, $sql3);

if ($stmt3 === false) {
    die(print_r(sqlsrv_errors(), true));
}

$data3 = [];
while ($row3 = sqlsrv_fetch_array($stmt3, SQLSRV_FETCH_ASSOC)) {
    $data3[] = $row3;
}

$spreadsheet = new Spreadsheet();
$sheet3 = $spreadsheet->getActiveSheet();
$sheet3->setTitle('Víctimas');

// Encabezados de la tabla (a partir de la fila 1)
$rowNum3 = 1; // Comenzar en la fila 1
$sheet3->setCellValue('A' . $rowNum3, 'ID_CI');
$sheet3->setCellValue('B' . $rowNum3, 'ID_CI_DELITO');
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
$sheet3->setCellValue('M' . $rowNum3, 'REL_VIC_VMARIO');

// Estilo para encabezados
$sheet3->getStyle("A$rowNum3:M$rowNum3")->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
$sheet3->getStyle("A$rowNum3:M$rowNum3")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('152f4a');
$sheet3->getStyle("A$rowNum3:M$rowNum3")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

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
    $sheet3->setCellValue('J' . $rowNum3, $row3['Fecha_de_Nacimiento']);
    $sheet3->setCellValue('K' . $rowNum3, $row3['Edad_de_la_Victima']);
    $sheet3->setCellValue('L' . $rowNum3, $row3['Nacionalidad']);
    $sheet3->setCellValue('M' . $rowNum3, $row3['Relacion_imputado']);

    // Alternar colores de fondo
    if ($colorAlterno) {
        // Gris claro
        $sheet3->getStyle('A' . $rowNum3 . ':M' . $rowNum3)
            ->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()
            ->setRGB('C6C6C6');
    } else {
        // Blanco
        $sheet3->getStyle('A' . $rowNum3 . ':M' . $rowNum3)
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
$sheet3->getStyle('A1:M' . ($rowNum3 - 1))->applyFromArray([
    'borders' => [
        'allBorders' => [
            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
            'color' => ['argb' => '646464'],
        ],
    ],
]);

// Centrar texto horizontal y verticalmente
$sheet3->getStyle('A1:M' . ($rowNum3 - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
$sheet3->getStyle('A1:M' . ($rowNum3 - 1))->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

// Encabezados en negritas
$sheet3->getStyle('A1:M1')->getFont()->setBold(true);

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
$sheet3->getColumnDimension('K')->setWidth(15); // Edad de la víctima
$sheet3->getColumnDimension('L')->setWidth(15); // Nacionalidad
$sheet3->getColumnDimension('M')->setWidth(32); // Relación imputado

// Crear el writer de Excel y enviar al navegador
$spreadsheet->setActiveSheetIndexByName('Víctimas');
$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
ob_end_clean();
$writer->save('php://output'); // Esto enviará el archivo directamente al navegador
exit;