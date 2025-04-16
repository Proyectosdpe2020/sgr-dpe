<?php
ini_set('memory_limit', '2048M');
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Consulta_cni.xlsx"');
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
$sql = "
      SELECT DISTINCT
    c.CarpetaID AS Id,
    c.NUC AS 'Nomenglatura_carpeta',
	c.FechaInicio,
    CONVERT(VARCHAR(10), c.FechaInicio, 23) AS 'Fecha Inicio',
    CONVERT(VARCHAR(8), c.FechaInicio, 8) AS 'Hora Inicio',
    CONVERT(VARCHAR(2500), C.Hechos) AS hechos,
    c.CarpetaID AS Id,
	del.CatModalidadesID as id_delito,
    CONVERT(VARCHAR(255), mo.Nombre) AS 'Delito',
	CONVERT(VARCHAR(3), 
        CASE WHEN c.Violencia = 1 THEN 'SI' 
             WHEN c.Violencia = 0 THEN 'NO' 
        END
    ) AS 'Modalidad (violencia)',
    CONVERT(VARCHAR(10), c.FechaComision, 23) AS 'Fecha Hechos',
    CONVERT(VARCHAR(8), c.FechaComision, 8) AS 'Hora Hechos',
    CONVERT(VARCHAR(255), dbo.CatArmas.Nombre) AS 'Elemento de comision',
    CONVERT(VARCHAR(10), 
        CASE WHEN del.Consumacion = 1 THEN 'Consumado' 
             WHEN del.Consumacion = 0 THEN 'Tentativa' 
        END
    ) AS 'Cosumacion',
    CONVERT(VARCHAR(255), 
        CONCAT(ISNULL(delito_clasificacion.Clave, ''), ' ', ISNULL(delito_clasificacion.Nombre, ''))
    ) AS 'delito_clasificacion',
    CONVERT(VARCHAR(255), 
        CONCAT(ISNULL(modalidad_clasificacion.Clave, ''), ' ', ISNULL(modalidad_clasificacion.Nombre, ''))
    ) AS 'modalidad_clasificacion',
    CONVERT(VARCHAR(255), 
        CASE 
            WHEN elemento_calsificacion.Nombre IS NULL 
                THEN CONCAT(ISNULL(modalidad_clasificacion.Clave, ''), ' ', ISNULL(modalidad_clasificacion.Nombre, ''))
            ELSE CONCAT(ISNULL(elemento_calsificacion.Clave, ''), ' ', ISNULL(elemento_calsificacion.Nombre, '')) 
        END
    ) AS 'elemento_clasificacion',
    CONVERT(VARCHAR(50), 'Michoacán') AS 'Entidad federativa',
    CONVERT(VARCHAR(2), '16') AS 'ID_Entidad_federativa',
    CONVERT(VARCHAR(255), mun.Nombre) AS 'Municipio del Hecho',
    mun.CatMunicipiosID AS 'Identificador fiscalia',
    CONVERT(VARCHAR(255), dbo.CatLocalidades.Nombre) AS 'Localidad',
    dbo.CatLocalidades.LocalidadID AS 'ID_Localidad',
    CONVERT(VARCHAR(255), dbo.CatColonias.Nombre) AS 'Colonia',
    dbo.CatColonias.CatColoniasID AS 'ID_Colonia',
    CONVERT(VARCHAR(10), dbo.CatColonias.CodigoPostal) AS 'Codigo_postal',
    do.latitud AS 'latitud',
    do.longitud AS 'longitud',
    CONVERT(VARCHAR(255), do.CalleNumero) AS 'Domicilio de los hechos',
	c.CarpetaID AS Id,
	del.CatModalidadesID as id_delito,
    vic.InvolucradoID AS 'ID_Persona',
	CASE 
    WHEN ctv.Nombre IN ('Moral', 'Estado', 'Sociedad') THEN 'Moral'
    WHEN ctv.Nombre = 'Física' THEN 'Física'
	WHEN ctv.Nombre = 'Desconocido' THEN 'Física'
	END AS Tipo_Victima,
				CONVERT(VARCHAR(20), 
					CASE 
						WHEN VictimaOfendido.Persona IN (0, 3, 4) THEN 'No especificado' 
						ELSE 'No aplica' 
					END
				) AS 'Tipo_Persona_Moral',
    CONVERT(VARCHAR(10), 
        CASE WHEN vic.Sexo = 1 THEN 'Masculino' 
             WHEN vic.Sexo = 2 THEN 'Femenino' 
             WHEN vic.Sexo = 3 THEN 'Desconocido' 
             ELSE 'N/A' 
        END
    ) AS 'Sexo_de_la_Victima',
	CONVERT(VARCHAR(255), lgbtti.Nombre) AS 'Poblacion_LGBTTTI',
	CONVERT(VARCHAR(255), indigena.Nombre) AS 'Poblacion_Indigena',
	CONVERT(VARCHAR(255), discapacidad.Nombre) AS 'Tipo_de_Discapacidad',
	CONVERT(VARCHAR(10), vsenap.FechaNacimiento, 23) AS 'Fecha_de_Nacimiento',
    vic.Edad AS 'Edad_de_la_Victima',
    CONVERT(VARCHAR(255), nacion.Nombre) AS 'Nacionalidad',
    p.Nombre AS 'Relacion_imputado'  
FROM dbo.Carpeta c
    INNER JOIN dbo.CatUIs u ON c.CatUIsID = u.CatUIsID
    INNER JOIN dbo.CatFiscalias fisca ON u.CatFiscaliasID = fisca.CatFiscaliasID
    INNER JOIN dbo.Involucrado vic ON c.CarpetaID = vic.CarpetaID
    INNER JOIN dbo.VictimaOfendido ON dbo.VictimaOfendido.InvolucradoID = vic.InvolucradoID
    LEFT JOIN [PRUEBA].[dbo].[VictimaSENAP] vsenap ON VictimaOfendido.VictimaOfendidoID = vsenap.VictimaID
    LEFT JOIN PRUEBA.dbo.CatPoblacionesLGBTTI lgbtti ON vsenap.PoblacionLGBTTI = lgbtti.PoblacionLGBTTIID
    LEFT JOIN PRUEBA.dbo.CatPoblacionesIndigenas indigena ON vsenap.PoblacionIndigena = indigena.PoblacionIndigenaID
	LEFT JOIN PRUEBA.dbo.CatDiscapacidades discapacidad ON vsenap.TipoDiscapacidad = discapacidad.DiscapacidadID
    INNER JOIN dbo.DelitosVictima ON dbo.VictimaOfendido.VictimaOfendidoID = dbo.DelitosVictima.VictimaID
	LEFT JOIN CatTipoVictima ctv ON ctv.CatTipoVictimaID = VictimaOfendido.Persona
    INNER JOIN dbo.CatModalidadesEstadisticas mo ON dbo.DelitosVictima.CatModalidadesID = mo.CatModalidadesEstadisticasID
    INNER JOIN dbo.Delito del ON c.CarpetaID = del.CarpetaID
    INNER JOIN dbo.Domicilio do ON c.CarpetaID = do.CarpetaID
    INNER JOIN dbo.CatMunicipios mun ON do.CatMunicipiosID = mun.CatMunicipiosID
    INNER JOIN dbo.CatFiscalias fis ON fis.CatFiscaliasID = mun.CatFiscaliasID
    INNER JOIN dbo.CatColonias ON do.CatColoniasID = dbo.CatColonias.CatColoniasID
    LEFT JOIN dbo.CatLocalidades ON do.LocalidadID = dbo.CatLocalidades.LocalidadID
    LEFT JOIN dbo.Catlugares ON do.CatLugaresID = dbo.CatLugares.CatLugaresID
    INNER JOIN dbo.CatArmas ON c.TipoArma = dbo.CatArmas.Arma_ID
    LEFT JOIN dbo.CatNacionalidades nacion ON VictimaOfendido.CatNacionalidadesID = nacion.CatNacionalidadesID
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
    INNER JOIN Involucrado i ON c.CarpetaID = i.CarpetaID 
    INNER JOIN Imputado imp ON i.InvolucradoID = imp.InvolucradoID 
    INNER JOIN CatParentescos p ON imp.CatParentescosID = p.CatParentescosID
WHERE 
    contar = 1
    AND CAST(FechaInicio AS DATE) BETWEEN '$fecha_inicial' AND '$fecha_final'
    AND Victima = 1
    AND (
        mo.Nombre NOT LIKE '%hechos posiblemente constitutivos de delito%' 
        AND mo.Nombre NOT LIKE '%hechos (no localizados)%'
        AND mo.Nombre NOT LIKE '%sospecha de suicidio%'
        AND mo.Nombre NOT LIKE '%sospecha muerte natural%'
        AND mo.Nombre NOT LIKE '%recuperación de vehículos%'
        AND mo.Nombre NOT LIKE '%persona no localizada%'
    )
ORDER BY c.FechaInicio ASC;
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
$sheet->setTitle('Consulta CNI');

// Encabezados de la tabla (a partir de la fila 1)
$rowNum = 1; // Comenzar en la fila 1
$sheet->setCellValue('A' . $rowNum, 'ID');
$sheet->setCellValue('B' . $rowNum, 'NOMENCLATURA CARPETA');
$sheet->setCellValue('C' . $rowNum, 'FECHA INICIO');
$sheet->setCellValue('D' . $rowNum, 'HORA INICIO');
$sheet->setCellValue('E' . $rowNum, 'HECHOS');
$sheet->setCellValue('F' . $rowNum, 'ID');
$sheet->setCellValue('G' . $rowNum, 'ID DELITO');
$sheet->setCellValue('H' . $rowNum, 'DELITO');
$sheet->setCellValue('I' . $rowNum, 'MODALIDAD (VIOLENCIA)');
$sheet->setCellValue('J' . $rowNum, 'FECHA HECHOS');
$sheet->setCellValue('K' . $rowNum, 'HORA HECHOS');
$sheet->setCellValue('L' . $rowNum, 'ELEMENTO DE COMISIÓN');
$sheet->setCellValue('M' . $rowNum, 'CONSUMACIÓN');
$sheet->setCellValue('N' . $rowNum, 'DELITO CLASIFICACIÓN');
$sheet->setCellValue('O' . $rowNum, 'MODALIDAD CLASIFICACIÓN');
$sheet->setCellValue('P' . $rowNum, 'ELEMENTO CLASIFICACIÓN');
$sheet->setCellValue('Q' . $rowNum, 'ENTIDAD FEDERATIVA');
$sheet->setCellValue('R' . $rowNum, 'ID ENTIDAD FEDERATIVA');
$sheet->setCellValue('S' . $rowNum, 'MUNICIPIO DEL HECHO');
$sheet->setCellValue('T' . $rowNum, 'IDENTIFICADOR FISCALÍA');
$sheet->setCellValue('U' . $rowNum, 'LOCALIDAD');
$sheet->setCellValue('V' . $rowNum, 'ID LOCALIDAD');
$sheet->setCellValue('W' . $rowNum, 'COLONIA');
$sheet->setCellValue('X' . $rowNum, 'ID COLONIA');
$sheet->setCellValue('Y' . $rowNum, 'CÓDIGO POSTAL');
$sheet->setCellValue('Z' . $rowNum, 'LATITUD');
$sheet->setCellValue('AA' . $rowNum, 'LONGITUD');
$sheet->setCellValue('AB' . $rowNum, 'DOMICILIO DE LOS HECHOS');
$sheet->setCellValue('AC' . $rowNum, 'ID');
$sheet->setCellValue('AD' . $rowNum, 'ID DELITO');
$sheet->setCellValue('AE' . $rowNum, 'ID PERSONA');
$sheet->setCellValue('AF' . $rowNum, 'TIPO VICTIMA');
$sheet->setCellValue('AG' . $rowNum, 'TIPO PERSONA MORAL');
$sheet->setCellValue('AH' . $rowNum, 'SEXO DE LA VÍCTIMA');
$sheet->setCellValue('AI' . $rowNum, 'POBLACIÓN LGBTTTI');
$sheet->setCellValue('AJ' . $rowNum, 'POBLACIÓN INDÍGENA');
$sheet->setCellValue('AK' . $rowNum, 'TIPO DE DISCAPACIDAD');
$sheet->setCellValue('AL' . $rowNum, 'FECHA DE NACIMIENTO');
$sheet->setCellValue('AM' . $rowNum, 'EDAD DE LA VÍCTIMA');
$sheet->setCellValue('AN' . $rowNum, 'NACIONALIDAD');
$sheet->setCellValue('AO' . $rowNum, 'RELACIÓN IMPUTADO');

// Estilo de los encabezados de la tabla
$sheet->getStyle("A$rowNum:AO$rowNum")->getFont()->setBold(true)->getColor()->setRGB('FFFFFF'); // Texto blanco
$sheet->getStyle("A$rowNum:AO$rowNum")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('152f4a'); // Fondo azul oscuro
$sheet->getStyle("A$rowNum:AO$rowNum")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Centrar texto
$sheet->getStyle("A$rowNum:AO$rowNum")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

// Ajuste de altura para encabezados
$sheet->getRowDimension($rowNum)->setRowHeight(20);

// Estilo alterno para las filas de datos (comenzando desde la fila 2)
$rowNum++; // Moverse a la fila siguiente para los datos
$colorAlterno = true; // Control de color alterno
$contador = 1;

// Rellenar las filas con los datos
foreach ($data as $row) {
    $hora_inicio = date("h:i A", strtotime($row['Hora Inicio']));
    $hora_hechos = date("h:i A", strtotime($row['Hora Hechos']));
    $sheet->setCellValue('A' . $rowNum, $row['Id']);
    $sheet->setCellValueExplicit('B' . $rowNum, $row['Nomenglatura_carpeta'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    $sheet->setCellValue('C' . $rowNum, $row['Fecha Inicio']);
    $sheet->setCellValue('D' . $rowNum, $hora_inicio);
    $sheet->setCellValue('E' . $rowNum, $row['hechos']);
    $sheet->getStyle('E' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT); // Align "Hechos" to the left
    $sheet->setCellValue('F' . $rowNum, $row['Id']);
    $sheet->setCellValue('G' . $rowNum, $row['id_delito']);
    $sheet->setCellValue('H' . $rowNum, $row['Delito']);
    $sheet->setCellValue('I' . $rowNum, $row['Modalidad (violencia)']);
    $sheet->setCellValue('J' . $rowNum, $row['Fecha Hechos']);
    $sheet->setCellValue('K' . $rowNum, $hora_hechos);
    $sheet->setCellValue('L' . $rowNum, $row['Elemento de comision']);
    $sheet->setCellValue('M' . $rowNum, $row['Cosumacion']);
    $sheet->setCellValue('N' . $rowNum, $row['delito_clasificacion']);
    $sheet->setCellValue('O' . $rowNum, $row['modalidad_clasificacion']);
    $sheet->setCellValue('P' . $rowNum, $row['elemento_clasificacion']);
    $sheet->setCellValue('Q' . $rowNum, $row['Entidad federativa']);
    $sheet->setCellValue('R' . $rowNum, $row['ID_Entidad_federativa']);
    $sheet->setCellValue('S' . $rowNum, $row['Municipio del Hecho']);
    $sheet->setCellValue('T' . $rowNum, $row['Identificador fiscalia']);
    $sheet->setCellValue('U' . $rowNum, $row['Localidad']);
    $sheet->setCellValue('V' . $rowNum, $row['ID_Localidad']);
    $sheet->setCellValue('W' . $rowNum, $row['Colonia']);
    $sheet->setCellValue('X' . $rowNum, $row['ID_Colonia']);
    $sheet->setCellValue('Y' . $rowNum, $row['Codigo_postal']);
    $sheet->setCellValue('Z' . $rowNum, $row['latitud']);
    $sheet->setCellValue('AA' . $rowNum, $row['longitud']);
    $sheet->setCellValue('AB' . $rowNum, $row['Domicilio de los hechos']);
    $sheet->setCellValue('AC' . $rowNum, $row['Id']);
    $sheet->setCellValue('AD' . $rowNum, $row['id_delito']);
    $sheet->setCellValue('AE' . $rowNum, $row['ID_Persona']);
    $sheet->setCellValue('AF' . $rowNum, $row['Tipo_Victima']);
    $sheet->setCellValue('AG' . $rowNum, $row['Tipo_Persona_Moral']);
    $sheet->setCellValue('AH' . $rowNum, $row['Sexo_de_la_Victima']);
    $sheet->setCellValue('AI' . $rowNum, $row['Poblacion_LGBTTTI']);
    $sheet->setCellValue('AJ' . $rowNum, $row['Poblacion_Indigena']);
    $sheet->setCellValue('AK' . $rowNum, $row['Tipo_de_Discapacidad']);
    $sheet->setCellValue('AL' . $rowNum, $row['Fecha_de_Nacimiento']);
    $sheet->setCellValue('AM' . $rowNum, $row['Edad_de_la_Victima']);
    $sheet->setCellValue('AN' . $rowNum, $row['Nacionalidad']);
    $sheet->setCellValue('AO' . $rowNum, $row['Relacion_imputado']);

    // Alternar colores de fondo
    if ($colorAlterno) {
        // Gris claro
        $sheet->getStyle('A' . $rowNum . ':AO' . $rowNum)
            ->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()
            ->setRGB('C6C6C6');
    } else {
        // Blanco
        $sheet->getStyle('A' . $rowNum . ':AO' . $rowNum)
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
$sheet->getStyle('A1:AO' . ($rowNum - 1))->applyFromArray([
    'borders' => [
        'allBorders' => [
            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
            'color' => ['argb' => '646464'],
        ],
    ],
]);

// Centrar texto horizontal y verticalmente
$sheet->getStyle('A1:AO' . ($rowNum - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('A1:AO' . ($rowNum - 1))->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

// Encabezados en negritas
$sheet->getStyle('A1:AO1')->getFont()->setBold(true);

// Ajustar anchos de columna
$sheet->getColumnDimension('B')->setWidth(30); // Nomenclatura carpeta
$sheet->getColumnDimension('C')->setWidth(15); // Fecha Inicio
$sheet->getColumnDimension('D')->setWidth(15); // Hora Inicio
$sheet->getColumnDimension('E')->setWidth(100); // Hechos
$sheet->getColumnDimension('H')->setWidth(110); // Delito
$sheet->getColumnDimension('I')->setWidth(25); // Modalidad
$sheet->getColumnDimension('J')->setWidth(15); // Fecha Hechos
$sheet->getColumnDimension('K')->setWidth(15); // Hora Hechos
$sheet->getColumnDimension('L')->setWidth(40); // Elemento de comisión
$sheet->getColumnDimension('M')->setWidth(17); // Consumación
$sheet->getColumnDimension('N')->setWidth(63); // Delito clasificación
$sheet->getColumnDimension('O')->setWidth(63); // Modalidad clasificación
$sheet->getColumnDimension('P')->setWidth(63); // Elemento clasificación
$sheet->getColumnDimension('Q')->setWidth(22); // Entidad federativa
$sheet->getColumnDimension('R')->setWidth(25); // ID Entidad federativa
$sheet->getColumnDimension('S')->setWidth(25); // Municipio del Hecho
$sheet->getColumnDimension('T')->setWidth(30); // Identificador fiscalía
$sheet->getColumnDimension('U')->setWidth(50); // Localidad
$sheet->getColumnDimension('V')->setWidth(15); // ID Localidad
$sheet->getColumnDimension('W')->setWidth(35); // Colonia
$sheet->getColumnDimension('X')->setWidth(20); // ID Colonia
$sheet->getColumnDimension('Y')->setWidth(20); // Código postal
$sheet->getColumnDimension('Z')->setWidth(20); // Latitud
$sheet->getColumnDimension('AA')->setWidth(20); // Longitud
$sheet->getColumnDimension('AB')->setWidth(157); // Domilicio de los hechos
$sheet->getColumnDimension('AE')->setWidth(15); // ID Persona
$sheet->getColumnDimension('AF')->setWidth(15); // Tipo Victima
$sheet->getColumnDimension('AG')->setWidth(30); // Tipo Persona Moral
$sheet->getColumnDimension('AH')->setWidth(20); // Sexo de la víctima
$sheet->getColumnDimension('AI')->setWidth(20); // Población LGBTTTI
$sheet->getColumnDimension('AJ')->setWidth(24); // Población indígena
$sheet->getColumnDimension('AK')->setWidth(24); // Tipo de discapacidad
$sheet->getColumnDimension('AL')->setWidth(24); // Fecha de nacimiento
$sheet->getColumnDimension('AM')->setWidth(20); // Edad de la víctima
$sheet->getColumnDimension('AN')->setWidth(20); // Nacionalidad
$sheet->getColumnDimension('AO')->setWidth(20); // Relación imputado

// Crear el writer de Excel y enviar al navegador
$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
ob_end_clean();
$writer->save('php://output'); // Esto enviará el archivo directamente al navegador
exit;