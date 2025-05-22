<?php
ini_set('memory_limit', '2048M');
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Consulta_CNI_delitos.xlsx"');
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
$sql2 = "
    SELECT DISTINCT
    c.CarpetaID AS Id,
    del.DelitoID AS id_delito,
    CONVERT(VARCHAR(255), mo.Nombre) AS 'Delito',
    1 AS [Delito principal],
		CASE WHEN del.Modalidad = 0 THEN 'Simple'
			 WHEN del.Modalidad = 1 THEN 'Atenuado'
			 WHEN del.Modalidad = 2 THEN 'Agravado'
			 WHEN del.Modalidad = 3 THEN 'Calificado'
			 WHEN del.Modalidad = 4 THEN 'Agravado/Calificado'
		END AS 'Modalidad',
    CONVERT(VARCHAR(3), 
        CASE WHEN c.Violencia = 1 THEN 1 -- Con violencia
             WHEN c.Violencia = 0 THEN 2 -- Sin violencia
			 ELSE 3
        END
    ) AS 'Forma (violencia)',
    CONVERT(VARCHAR(10), c.FechaComision, 23) AS 'Fecha Hechos',
    CONVERT(VARCHAR(8), c.FechaComision, 8) AS 'Hora Hechos',
    dbo.CatArmas.idsenap AS 'Elemento de comision',
    CASE 
		WHEN del.Consumacion = 1 THEN 1 --Consumado
		WHEN del.Consumacion = 0 THEN 2 --Tentativa
	END AS 'Cosumacion',
    CONVERT(VARCHAR(50), 
    CASE 
        WHEN elemento_calsificacion.Nombre IS NULL THEN modalidad_clasificacion.Clave
        ELSE elemento_calsificacion.Clave
    END
	) AS 'elemento_clasificacion',
    CONVERT(VARCHAR(50), 'Michoacán') AS 'Entidad federativa',
    CONVERT(VARCHAR(2), '16') AS 'ID_Entidad_federativa',
    CONVERT(VARCHAR(255), mun.Nombre) AS 'Municipio del Hecho',
    mun.CatMunicipiosID AS 'Identificador fiscalia',
    CONVERT(VARCHAR(255), dbo.CatLocalidades.Nombre) AS 'Localidad',
	mun.localidad_senap_id AS 'ID_Localidad',
    CONVERT(VARCHAR(255), dbo.CatColonias.Nombre) AS 'Colonia',
    dbo.CatColonias.CatColoniasID AS 'ID_Colonia',
    CONVERT(VARCHAR(10), dbo.CatColonias.CodigoPostal) AS 'Codigo_postal',
    do.latitud AS 'latitud',
    do.longitud AS 'longitud',
    CONVERT(VARCHAR(255), do.CalleNumero) AS 'Domicilio de los hechos',
	    c.FechaInicio,
    CONVERT(VARCHAR(10), c.FechaInicio, 23) AS 'Fecha Inicio'
FROM dbo.Carpeta c
INNER JOIN dbo.CatUIs u ON c.CatUIsID = u.CatUIsID
INNER JOIN dbo.CatFiscalias fisca ON u.CatFiscaliasID = fisca.CatFiscaliasID
INNER JOIN dbo.Delito del ON c.CarpetaID = del.CarpetaID
INNER JOIN dbo.Domicilio do ON c.CarpetaID = do.CarpetaID
INNER JOIN dbo.CatMunicipios mun ON do.CatMunicipiosID = mun.CatMunicipiosID
INNER JOIN dbo.CatFiscalias fis ON fis.CatFiscaliasID = mun.CatFiscaliasID
INNER JOIN dbo.CatColonias ON do.CatColoniasID = dbo.CatColonias.CatColoniasID
LEFT JOIN dbo.CatLocalidades ON do.LocalidadID = dbo.CatLocalidades.LocalidadID
LEFT JOIN dbo.Catlugares ON do.CatLugaresID = dbo.CatLugares.CatLugaresID
INNER JOIN dbo.CatArmas ON c.TipoArma = dbo.CatArmas.Arma_ID
INNER JOIN dbo.CatModalidadesEstadisticas mo ON del.CatModalidadesID = mo.CatModalidadesEstadisticasID
INNER JOIN [PRUEBA].[dbo].[CatModalidadClasificacion] modalidad_clasificacion 
    ON modalidad_clasificacion.CatModalidadClasificacionID = mo.CatModalidadClasificacionID
INNER JOIN [PRUEBA].[dbo].[CatDelitoClasificacion] delito_clasificacion 
    ON delito_clasificacion.CatDelitoClasificacionID = modalidad_clasificacion.CatDelitoClasificacionID
LEFT JOIN [PRUEBA].[dbo].[CatElementoClasificacion] elemento_calsificacion 
    ON elemento_calsificacion.CatModalidadClasificacionID = modalidad_clasificacion.CatModalidadClasificacionID
    AND (
        (c.Violencia = 1 AND elemento_calsificacion.Nombre LIKE '%Con violencia%')
        OR (c.Violencia = 0 AND elemento_calsificacion.Nombre LIKE '%Sin violencia%')
        OR (dbo.CatArmas.Nombre LIKE '%arma de fuego%' AND elemento_calsificacion.Nombre LIKE '%Con arma de fuego%')
        OR (dbo.CatArmas.Nombre LIKE '%arma blanca%' AND elemento_calsificacion.Nombre LIKE '%Con arma blanca%')
        OR (dbo.CatArmas.Nombre LIKE '%vehículo%' AND elemento_calsificacion.Nombre LIKE '%En accidente de tránsito%')
        OR (dbo.CatArmas.Nombre LIKE '%desconocida%' AND elemento_calsificacion.Nombre LIKE '%No especificado%')
        OR (dbo.CatArmas.Nombre LIKE '%alguna parte del cuerpo%' AND elemento_calsificacion.Nombre LIKE '%Con otro elemento%')
        OR (dbo.CatArmas.Nombre LIKE '%otro elemento%' AND elemento_calsificacion.Nombre LIKE '%Con otro elemento%')
        OR (
            dbo.CatArmas.Nombre NOT LIKE '%arma de fuego%' 
            AND dbo.CatArmas.Nombre NOT LIKE '%arma blanca%'
            AND dbo.CatArmas.Nombre NOT LIKE '%vehículo%'
            AND dbo.CatArmas.Nombre NOT LIKE '%desconocida%'
            AND dbo.CatArmas.Nombre NOT LIKE '%alguna parte del cuerpo%'
            AND dbo.CatArmas.Nombre NOT LIKE '%otro elemento%'
            AND elemento_calsificacion.Nombre NOT LIKE '%Con violencia%'
            AND elemento_calsificacion.Nombre NOT LIKE '%Sin violencia%'
        )
    )
WHERE 
    contar = 1
    AND CAST(FechaInicio AS DATE) BETWEEN '$fecha_inicial' AND '$fecha_final'
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

$stmt2 = sqlsrv_query($conn, $sql2);

if ($stmt2 === false) {
    die(print_r(sqlsrv_errors(), true));
}

$data2 = [];
while ($row2 = sqlsrv_fetch_array($stmt2, SQLSRV_FETCH_ASSOC)) {
    $data2[] = $row2;
}

$spreadsheet = new Spreadsheet();
$sheet2 = $spreadsheet->getActiveSheet();
$sheet2->setTitle('Delitos');

// Encabezados de la tabla (a partir de la fila 1)
$rowNum2 = 1; // Comenzar en la fila 1
$sheet2->setCellValue('A' . $rowNum2, 'ID_CI');
$sheet2->setCellValue('B' . $rowNum2, 'ID_CI_DELITO');
$sheet2->setCellValue('C' . $rowNum2, 'DTO');
$sheet2->setCellValue('D' . $rowNum2, 'DTO_PRIN');
$sheet2->setCellValue('E' . $rowNum2, 'MODA_DTO');
$sheet2->setCellValue('F' . $rowNum2, 'FORMA_ACC');
$sheet2->setCellValue('G' . $rowNum2, 'FHA_DE_HCHOS');
$sheet2->setCellValue('H' . $rowNum2, 'HRA_DE_HCHOS');
$sheet2->setCellValue('I' . $rowNum2, 'EMTO_COM_DTO');
$sheet2->setCellValue('J' . $rowNum2, 'GRDO_CONS');
$sheet2->setCellValue('K' . $rowNum2, 'CLASF_DE_DTO');
$sheet2->setCellValue('L' . $rowNum2, 'NOM_ENT_HCHOS');
$sheet2->setCellValue('M' . $rowNum2, 'ID_ENT_HCHOS');
$sheet2->setCellValue('N' . $rowNum2, 'NOM_MUN_HCHOS');
$sheet2->setCellValue('O' . $rowNum2, 'ID_MUN_HCHOS');
$sheet2->setCellValue('P' . $rowNum2, 'NOM_LOC_HCHOS');
$sheet2->setCellValue('Q' . $rowNum2, 'ID_LOC_HCHOS');
$sheet2->setCellValue('R' . $rowNum2, 'NOM_COL_HCHOS');
$sheet2->setCellValue('S' . $rowNum2, 'ID_COL_HCHOS');
$sheet2->setCellValue('T' . $rowNum2, 'CP');
$sheet2->setCellValue('U' . $rowNum2, 'COORD_Y');
$sheet2->setCellValue('V' . $rowNum2, 'COORD_X');
$sheet2->setCellValue('W' . $rowNum2, 'DOM_HCHOS');

// Estilo para encabezados
$sheet2->getStyle("A$rowNum2:W$rowNum2")->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
$sheet2->getStyle("A$rowNum2:W$rowNum2")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('152f4a');
$sheet2->getStyle("A$rowNum2:W$rowNum2")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Ajuste de altura para encabezados
$sheet2->getRowDimension($rowNum2)->setRowHeight(20);

// Filas con datos
$rowNum2++;
$colorAlterno = true;
$contador = 1;

// Rellenar las filas con los datos
foreach ($data2 as $row2) {
    $sheet2->setCellValue('A' . $rowNum2, $row2['Id']);
    $sheet2->setCellValue('B' . $rowNum2, $row2['id_delito']);
    $sheet2->setCellValue('C' . $rowNum2, $row2['Delito']);
    $sheet2->setCellValue('D' . $rowNum2, $row2['Delito principal']);
    $sheet2->setCellValue('E' . $rowNum2, $row2['Modalidad']);
    $sheet2->setCellValue('F' . $rowNum2, $row2['Forma (violencia)']);
    $sheet2->setCellValue('G' . $rowNum2, $row2['Fecha Hechos']);
    $sheet2->setCellValue('H' . $rowNum2, $row2['Hora Hechos']);
    $sheet2->setCellValue('I' . $rowNum2, $row2['Elemento de comision']);
    $sheet2->setCellValue('J' . $rowNum2, $row2['Cosumacion']);
    $sheet2->setCellValue('K' . $rowNum2, $row2['elemento_clasificacion']);
    $sheet2->setCellValue('L' . $rowNum2, $row2['Entidad federativa']);
    $sheet2->setCellValue('M' . $rowNum2, $row2['ID_Entidad_federativa']);
    $sheet2->setCellValue('N' . $rowNum2, $row2['Municipio del Hecho']);
    $sheet2->setCellValue('O' . $rowNum2, $row2['Identificador fiscalia']);
    $sheet2->setCellValue('P' . $rowNum2, $row2['Localidad']);
    $sheet2->setCellValue('Q' . $rowNum2, $row2['ID_Localidad']);
    $sheet2->setCellValue('R' . $rowNum2, $row2['Colonia']);
    $sheet2->setCellValue('S' . $rowNum2, $row2['ID_Colonia']);
    $sheet2->setCellValue('T' . $rowNum2, $row2['Codigo_postal']);
    $sheet2->setCellValue('U' . $rowNum2, $row2['latitud']);
    $sheet2->setCellValue('V' . $rowNum2, $row2['longitud']);
    $sheet2->setCellValue('W' . $rowNum2, $row2['Domicilio de los hechos']);

    // Alternar colores de fondo
    if ($colorAlterno) {
        // Gris claro
        $sheet2->getStyle('A' . $rowNum2 . ':W' . $rowNum2)
            ->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()
            ->setRGB('C6C6C6');
    } else {
        // Blanco
        $sheet2->getStyle('A' . $rowNum2 . ':W' . $rowNum2)
            ->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()
            ->setRGB('FFFFFF');
    }

    // Cambiar el valor de $colorAlterno para la siguiente fila
    $colorAlterno = !$colorAlterno;

    // Incrementar la fila
    $contador++;
    $rowNum2++;
}

// Aplicar bordes a todas las celdas de la tabla
$sheet2->getStyle('A1:W' . ($rowNum2 - 1))->applyFromArray([
    'borders' => [
        'allBorders' => [
            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
            'color' => ['argb' => '646464'],
        ],
    ],
]);

// Centrar texto horizontal y verticalmente
$sheet2->getStyle('A1:W' . ($rowNum2 - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
$sheet2->getStyle('A1:W' . ($rowNum2 - 1))->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

// Encabezados en negritas
$sheet2->getStyle('A1:W1')->getFont()->setBold(true);

// Ajustar anchos de columna
$sheet2->getColumnDimension('B')->setWidth(22); // Id_delito
$sheet2->getColumnDimension('C')->setWidth(110); // Delito
$sheet2->getColumnDimension('D')->setWidth(15); // Delito principal
$sheet2->getColumnDimension('E')->setWidth(20); // Modalidad
$sheet2->getColumnDimension('F')->setWidth(20); // Forma (violencia)
$sheet2->getColumnDimension('G')->setWidth(18); // Fecha Hechos
$sheet2->getColumnDimension('H')->setWidth(18); // Hora Hechos
$sheet2->getColumnDimension('I')->setWidth(20); // Elemento de comisión
$sheet2->getColumnDimension('J')->setWidth(17); // Consumación
$sheet2->getColumnDimension('K')->setWidth(20); // Elemento clasificación
$sheet2->getColumnDimension('L')->setWidth(22); // Entidad federativa
$sheet2->getColumnDimension('M')->setWidth(20); // ID Entidad federativa
$sheet2->getColumnDimension('N')->setWidth(25); // Municipio del Hecho
$sheet2->getColumnDimension('O')->setWidth(20); // Identificador fiscalía
$sheet2->getColumnDimension('P')->setWidth(50); // Localidad
$sheet2->getColumnDimension('Q')->setWidth(18); // ID Localidad
$sheet2->getColumnDimension('R')->setWidth(35); // Colonia
$sheet2->getColumnDimension('S')->setWidth(20); // ID Colonia
$sheet2->getColumnDimension('T')->setWidth(15); // Código postal
$sheet2->getColumnDimension('U')->setWidth(20); // Latitud
$sheet2->getColumnDimension('V')->setWidth(20); // Longitud
$sheet2->getColumnDimension('W')->setWidth(157); // Domilicio de los hechos

// Crear el writer de Excel y enviar al navegador
$spreadsheet->setActiveSheetIndexByName('Delitos');
$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
ob_end_clean();
$writer->save('php://output'); // Esto enviará el archivo directamente al navegador
exit;