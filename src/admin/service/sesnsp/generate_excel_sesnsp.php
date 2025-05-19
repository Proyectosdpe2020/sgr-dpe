<?php
ini_set('memory_limit', '2048M');
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Consulta_CNI.xlsx"');
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

// Primera consulta 
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

// Segunda consulta
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

$sheet2 = $spreadsheet->createSheet();
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

// Tercera consulta
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

$sheet3 = $spreadsheet->createSheet();
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
$spreadsheet->setActiveSheetIndexByName('Carpetas');
$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
ob_end_clean();
$writer->save('php://output'); // Esto enviará el archivo directamente al navegador
exit;