<?php
ini_set('memory_limit', '2048M');
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="consulta_cni.xlsx"');
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
    CONVERT(VARCHAR(10), c.FechaComision, 23) AS 'Fecha Hechos',
    CONVERT(VARCHAR(8), c.FechaComision, 8) AS 'Hora Hechos',
    CONVERT(VARCHAR(255), mo.Nombre) AS 'Delito',
    CONVERT(VARCHAR(3), 
        CASE WHEN c.Violencia = 1 THEN 'SI' 
             WHEN c.Violencia = 0 THEN 'NO' 
        END
    ) AS 'Modalidad (violencia)',
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
    vic.InvolucradoID AS 'ID_Persona',
    CONVERT(VARCHAR(10), 
        CASE WHEN vic.Sexo = 1 THEN 'Masculino' 
             WHEN vic.Sexo = 2 THEN 'Femenino' 
             WHEN vic.Sexo = 3 THEN 'Desconocido' 
             ELSE 'N/A' 
        END
    ) AS 'Sexo_de_la_Victima',
    vic.Edad AS 'Edad_de_la_Victima',
    CONVERT(VARCHAR(255), nacion.Nombre) AS 'Nacionalidad'
FROM dbo.Carpeta c
    INNER JOIN dbo.CatUIs u ON c.CatUIsID= u.CatUIsID
    INNER JOIN dbo.CatFiscalias fisca ON u.CatFiscaliasID=fisca.CatFiscaliasID
    INNER JOIN dbo.Involucrado vic ON c.CarpetaID=vic.CarpetaID
    INNER JOIN dbo.VictimaOfendido ON dbo.VictimaOfendido.InvolucradoID=vic.InvolucradoID
    INNER JOIN dbo.DelitosVictima on dbo.VictimaOfendido.VictimaOfendidoID=dbo.DelitosVictima.VictimaID
    INNER JOIN dbo.CatModalidadesEstadisticas mo ON dbo.DelitosVictima.CatModalidadesID=mo.CatModalidadesEstadisticasID
    INNER JOIN dbo.Delito del ON c.CarpetaID=del.CarpetaID
    INNER JOIN dbo.Domicilio do ON c.CarpetaID=do.CarpetaID
    INNER JOIN dbo.CatMunicipios mun ON do.CatMunicipiosID=mun.CatMunicipiosID
    INNER JOIN dbo.CatFiscalias fis ON fis.CatFiscaliasID=mun.CatFiscaliasID
    INNER JOIN dbo.CatColonias ON do.CatColoniasID=dbo.CatColonias.CatColoniasID
    LEFT JOIN dbo.CatLocalidades ON do.LocalidadID=dbo.CatLocalidades.LocalidadID
    LEFT JOIN dbo.Catlugares ON do.CatLugaresID=dbo.CatLugares.CatLugaresID
    INNER JOIN dbo.CatArmas ON c.TipoArma=dbo.CatArmas.Arma_ID
    LEFT JOIN dbo.CatNacionalidades nacion on VictimaOfendido.CatNacionalidadesID = nacion.CatNacionalidadesID
    INNER JOIN [PRUEBA].[dbo].[CatModalidadClasificacion] modalidad_clasificacion on modalidad_clasificacion.CatModalidadClasificacionID = mo.CatModalidadClasificacionID
	INNER JOIN [PRUEBA].[dbo].[CatDelitoClasificacion] delito_clasificacion on delito_clasificacion.CatDelitoClasificacionID = modalidad_clasificacion.CatDelitoClasificacionID
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
        OR (dbo.CatArmas.Nombre NOT LIKE '%arma de fuego%' 
            AND dbo.CatArmas.Nombre NOT LIKE '%arma blanca%'
            AND dbo.CatArmas.Nombre NOT LIKE '%vehículo%'
            AND dbo.CatArmas.Nombre NOT LIKE '%desconocida%'
            AND dbo.CatArmas.Nombre NOT LIKE '%alguna parte del cuerpo%'
			AND dbo.CatArmas.Nombre NOT LIKE '%otro elemento%'
            AND elemento_calsificacion.Nombre NOT LIKE '%Con violencia%'
            AND elemento_calsificacion.Nombre NOT LIKE '%Sin violencia%')
    )
    WHERE 
        contar=1
        AND CAST(FechaInicio AS DATE) BETWEEN '$fecha_inicial' AND '$fecha_final'
        AND Victima=1
        AND (mo.Nombre NOT LIKE '%hechos posiblemente constitutivos de delito%' 
             AND mo.Nombre NOT LIKE '%hechos (no localizados)%'
             AND mo.Nombre NOT LIKE '%sospecha de suicidio%'
             AND mo.Nombre NOT LIKE '%sospecha muerte natural%'
             AND mo.Nombre NOT LIKE '%recuperación de vehículos%'
             AND mo.Nombre NOT LIKE '%persona no localizada%')
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
$sheet->setTitle('Consulta SESNSP');

// Encabezado principal
$sheet->setCellValue('A1', 'FISCALÍA GENERAL DEL ESTADO DE MICHOACÁN');
$sheet->mergeCells("A1:AD1");
$sheet->getStyle("A1")->getFont()->setBold(true)->setSize(16);
$sheet->getStyle("A1")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Subtítulo
$sheet->setCellValue('A2', "CONSULTA CNI");
$sheet->mergeCells("A2:AD2");
$sheet->getStyle("A2")->getFont()->setBold(true)->setSize(14);
$sheet->getStyle("A2")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle("A2")->getAlignment()->setWrapText(true); // Permitir texto en varias líneas

// Encabezados de la tabla (a partir de la fila 4)
$rowNum = 4; // Comenzar en la fila 4
$sheet->setCellValue('A' . $rowNum, 'ID');
$sheet->setCellValue('B' . $rowNum, 'NOMENCLATURA CARPETA');
$sheet->setCellValue('C' . $rowNum, 'FECHA INICIO');
$sheet->setCellValue('D' . $rowNum, 'HORA INICIO');
$sheet->setCellValue('E' . $rowNum, 'HECHOS');
$sheet->setCellValue('F' . $rowNum, 'FECHA HECHOS');
$sheet->setCellValue('G' . $rowNum, 'HORA HECHOS');
$sheet->setCellValue('H' . $rowNum, 'DELITO');
$sheet->setCellValue('I' . $rowNum, 'MODALIDAD (VIOLENCIA)');
$sheet->setCellValue('J' . $rowNum, 'ELEMENTO DE COMISIÓN');
$sheet->setCellValue('K' . $rowNum, 'CONSUMACIÓN');
$sheet->setCellValue('L' . $rowNum, 'DELITO CLASIFICACIÓN');
$sheet->setCellValue('M' . $rowNum, 'MODALIDAD CLASIFICACIÓN');
$sheet->setCellValue('N' . $rowNum, 'ELEMENTO CLASIFICACIÓN');
$sheet->setCellValue('O' . $rowNum, 'ENTIDAD FEDERATIVA');
$sheet->setCellValue('P' . $rowNum, 'ID ENTIDAD FEDERATIVA');
$sheet->setCellValue('Q' . $rowNum, 'MUNICIPIO DEL HECHO');
$sheet->setCellValue('R' . $rowNum, 'IDENTIFICADOR FISCALÍA');
$sheet->setCellValue('S' . $rowNum, 'LOCALIDAD');
$sheet->setCellValue('T' . $rowNum, 'ID LOCALIDAD');
$sheet->setCellValue('U' . $rowNum, 'COLONIA');
$sheet->setCellValue('V' . $rowNum, 'ID COLONIA');
$sheet->setCellValue('W' . $rowNum, 'CÓDIGO POSTAL');
$sheet->setCellValue('X' . $rowNum, 'LATITUD');
$sheet->setCellValue('Y' . $rowNum, 'LONGITUD');
$sheet->setCellValue('Z' . $rowNum, 'DOMICILIO DE LOS HECHOS');
$sheet->setCellValue('AA' . $rowNum, 'ID PERSONA');
$sheet->setCellValue('AB' . $rowNum, 'SEXO DE LA VÍCTIMA');
$sheet->setCellValue('AC' . $rowNum, 'EDAD DE LA VÍCTIMA');
$sheet->setCellValue('AD' . $rowNum, 'NACIONALIDAD');

// Estilo de los encabezados de la tabla
$sheet->getStyle("A$rowNum:AD$rowNum")->getFont()->setBold(true)->getColor()->setRGB('FFFFFF'); // Texto blanco
$sheet->getStyle("A$rowNum:AD$rowNum")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('152f4a'); // Fondo azul oscuro
$sheet->getStyle("A$rowNum:AD$rowNum")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Centrar texto
$sheet->getStyle("A$rowNum:AD$rowNum")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

// Ajuste de altura para encabezados
$sheet->getRowDimension($rowNum)->setRowHeight(20);

// Estilo alterno para las filas de datos (comenzando desde la fila 5)
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
    $sheet->setCellValue('F' . $rowNum, $row['Fecha Hechos']);
    $sheet->setCellValue('G' . $rowNum, $hora_hechos);
    $sheet->setCellValue('H' . $rowNum, $row['Delito']);
    $sheet->setCellValue('I' . $rowNum, $row['Modalidad (violencia)']);
    $sheet->setCellValue('J' . $rowNum, $row['Elemento de comision']);
    $sheet->setCellValue('K' . $rowNum, $row['Cosumacion']);
    $sheet->setCellValue('L' . $rowNum, $row['delito_clasificacion']);
    $sheet->setCellValue('M' . $rowNum, $row['modalidad_clasificacion']);
    $sheet->setCellValue('N' . $rowNum, $row['elemento_clasificacion']);
    $sheet->setCellValue('O' . $rowNum, $row['Entidad federativa']);
    $sheet->setCellValue('P' . $rowNum, $row['ID_Entidad_federativa']);
    $sheet->setCellValue('Q' . $rowNum, $row['Municipio del Hecho']);
    $sheet->setCellValue('R' . $rowNum, $row['Identificador fiscalia']);
    $sheet->setCellValue('S' . $rowNum, $row['Localidad']);
    $sheet->setCellValue('T' . $rowNum, $row['ID_Localidad']);
    $sheet->setCellValue('U' . $rowNum, $row['Colonia']);
    $sheet->setCellValue('V' . $rowNum, $row['ID_Colonia']);
    $sheet->setCellValue('W' . $rowNum, $row['Codigo_postal']);
    $sheet->setCellValue('X' . $rowNum, $row['latitud']);
    $sheet->setCellValue('Y' . $rowNum, $row['longitud']);
    $sheet->setCellValue('Z' . $rowNum, $row['Domicilio de los hechos']);
    $sheet->setCellValue('AA' . $rowNum, $row['ID_Persona']);
    $sheet->setCellValue('AB' . $rowNum, $row['Sexo_de_la_Victima']);
    $sheet->setCellValue('AC' . $rowNum, $row['Edad_de_la_Victima']);
    $sheet->setCellValue('AD' . $rowNum, $row['Nacionalidad']);

    // Alternar colores de fondo
    if ($colorAlterno) {
        // Gris claro
        $sheet->getStyle('A' . $rowNum . ':AD' . $rowNum)
            ->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()
            ->setRGB('C6C6C6');
    } else {
        // Blanco
        $sheet->getStyle('A' . $rowNum . ':AD' . $rowNum)
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
$sheet->getStyle('A1:AD' . ($rowNum - 1))->applyFromArray([
    'borders' => [
        'allBorders' => [
            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
            'color' => ['argb' => '646464'],
        ],
    ],
]);

// Centrar texto horizontal y verticalmente
$sheet->getStyle('A1:AD' . ($rowNum - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('A1:AD' . ($rowNum - 1))->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

// Encabezados en negritas
$sheet->getStyle('A1:AD1')->getFont()->setBold(true);

// Ajustar anchos de columna
$sheet->getColumnDimension('B')->setWidth(30); // Nomenclatura carpeta
$sheet->getColumnDimension('C')->setWidth(15); // Fecha Inicio
$sheet->getColumnDimension('D')->setWidth(15); // Hora Inicio
$sheet->getColumnDimension('E')->setWidth(100); // Hechos
$sheet->getColumnDimension('F')->setWidth(15); // Fecha Hechos
$sheet->getColumnDimension('G')->setWidth(15); // Hora Hechos
$sheet->getColumnDimension('H')->setWidth(110); // Delito
$sheet->getColumnDimension('I')->setWidth(25); // Modalidad
$sheet->getColumnDimension('J')->setWidth(40); // Elemento de comisión
$sheet->getColumnDimension('K')->setWidth(17); // Consumación
$sheet->getColumnDimension('L')->setWidth(63); // Delito clasificación
$sheet->getColumnDimension('M')->setWidth(63); // Modalidad clasificación
$sheet->getColumnDimension('N')->setWidth(63); // Elemento clasificación
$sheet->getColumnDimension('O')->setWidth(22); // Entidad federativa
$sheet->getColumnDimension('P')->setWidth(25); // ID Entidad federativa
$sheet->getColumnDimension('Q')->setWidth(25); // Municipio del Hecho
$sheet->getColumnDimension('R')->setWidth(30); // Identificador fiscalía
$sheet->getColumnDimension('S')->setWidth(40); // Localidad
$sheet->getColumnDimension('T')->setWidth(15); // ID Localidad
$sheet->getColumnDimension('U')->setWidth(35); // Colonia
$sheet->getColumnDimension('V')->setWidth(20); // ID Colonia
$sheet->getColumnDimension('W')->setWidth(20); // Código postal
$sheet->getColumnDimension('X')->setWidth(20); // Latitud
$sheet->getColumnDimension('Y')->setWidth(20); // Longitud
$sheet->getColumnDimension('Z')->setWidth(157); // Domilicio de los hechos
$sheet->getColumnDimension('AA')->setWidth(15); // ID Persona
$sheet->getColumnDimension('AB')->setWidth(20); // Sexo de la víctima
$sheet->getColumnDimension('AC')->setWidth(20); // Edad de la víctima
$sheet->getColumnDimension('AD')->setWidth(20); // Nacionalidad

// Crear el writer de Excel y enviar al navegador
$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
ob_end_clean();
$writer->save('php://output'); // Esto enviará el archivo directamente al navegador
exit;