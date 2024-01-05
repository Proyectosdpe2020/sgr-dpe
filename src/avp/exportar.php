<?php session_start(); ?>
<!doctype html>
<html lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
		
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
		<title>Buscar Nuc</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/> 
		<meta name="tipo_contenido"  content="text/html;" http-equiv="content-type" charset="utf-8">	
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />	
		<meta name="author" content="Jake Rocheleau">
		
		<link rel="icon" href="http://pgje.michoacan.gob.mx/wp-content/uploads/2015/08/JPG_001.jpg">
		<link rel="stylesheet" type="text/css" media="all" href="style4.css">
		
    	<script type="text/javascript" src="./fancybox/jquery.mousewheel-3.0.4.pack.js"></script>
		<script type="text/javascript" src="./fancybox/jquery.fancybox-1.3.4.pack.js"></script>
		<script type="text/javascript" src="jquery-1.8.2.min.js"></script>
		
	</head>
	
	<body>
		<a href="../index.php">Regresar</a>
		<section id="container">
			<section id="container">
				
				
				<?php
				
					ini_set('memory_limit', '1024M');
					set_time_limit(6600); // para dar mas tiempoi de ejecucion
					
					
					
					$serverName = "localhost"; 
					///$connectionInfo = array( "Database"=>"PRUEBA", "UID"=>"juanito", "PWD"=>"1988aldebaran","CharacterSet" => "UTF-8");
					$connectionInfo = array( "Database"=>"PRUEBA", "UID"=>"israel", "PWD"=>"Planeacion2017#","CharacterSet" => "UTF-8");
				    
				    $conn = sqlsrv_connect( $serverName, $connectionInfo);
					
										
					//$serverName = "172.16.68.3"; 
				    //$connectionInfo = array( "Database"=>"HISTORICO2015");	
				    //$conn2 = sqlsrv_connect( $serverName, $connectionInfo);

				    if( $conn ) {
						
							
							$fechaINi="01-".$mesDatos."-".$anioDatos." 00:00:00";
				
							if($mesDatos==1 || $mesDatos==3 || $mesDatos==5 || $mesDatos==7 || $mesDatos==8 || $mesDatos==10 || $mesDatos==12)
							{
								$fechaFin="31-".$mesDatos."-".$anioDatos." 23:59:59";
							}
							elseif($mesDatos==4 || $mesDatos==6 || $mesDatos==9 || $mesDatos==11)
							{
								$fechaFin="30-".$mesDatos."-".$anioDatos." 23:59:59";
							}
							elseif($mesDatos==2)
							{
								if (($anioDatos%4==0 and $anioDatos%100!=0) or $anioDatos%400==0)
									{
										$fechaFin="29-".$mesDatos."-".$anioDatos." 23:59:59";
									}
									else 
									{ 
										$fechaFin="28-".$mesDatos."-".$anioDatos." 23:59:59";
									}
							}

							function importarDatosToDataBase($conexion, $consulta,$nuu){
								
								
									 if ( sqlsrv_begin_transaction( $conexion ) === false ) {
									 die( print_r( sqlsrv_errors(), true ));
								}							
								
								$stmt = sqlsrv_query( $conexion, $consulta );

								if( $stmt ) {
									 sqlsrv_commit( $conexion );
									 echo " aqui".$nuu;
								} else {
									 sqlsrv_rollback( $conexion );
									 echo "Transacción revertida.<br />";
								}								
								
							}	
								
							///////////////  CONSULTA CON INSERT DE DELITOS EN GENERAL     ////////////////////////
							
							$query0 = "INSERT INTO HISTORICO2015.dbo.AVE_MUNICIPIOS (IDSUBPRO, IDDISTRITO, IDDELITO, IDMUNICIPIO, CANTIDAD, MES, ANIO)

										SELECT T1.IDSUBPRO, T1.IDDISTRITO, T1.IDDELITO, T1.IDMUNICIPIO, SUM(T1.CANTIDAD) AS CANTIDAD, T1.MES, T1.ANIO 
										FROM (
										SELECT  CASE dbo.CatMunicipios.CatFiscaliasID WHEN 1 then 1  WHEN 2 then 8 WHEN 3 then 2 WHEN 4 then 3  WHEN 5 then 4  WHEN 6 then 5 WHEN 7 then 6 WHEN 8 THEN 9 WHEN 9 THEN 10 WHEN 10 THEN 11 END as 'IDSUBPRO',  
										  dbo.CatMunicipios.CatDistritosID as IDDISTRITO,
										  dbo.CatModalidadesEstadisticas.Idhistorico AS IDDELITO,
										  dbo.CatMunicipios.CatMunicipiosID AS IDMUNICIPIO,
										  count(dbo.Carpeta.NUC) AS 'CANTIDAD',
										  datepart(MM,Carpeta.fechaInicio) as MES,
										  datepart(YYYY,Carpeta.fechaInicio) as ANIO,
										  dbo.CatModalidadesEstadisticas.Nombre,
										  Carpeta.FechaInicio,
										  Carpeta.NUC
		
										  FROM
										  dbo.Carpeta
										  INNER JOIN dbo.Domicilio ON (dbo.Carpeta.CarpetaID = dbo.Domicilio.CarpetaID)  
                                          INNER JOIN dbo.CatMunicipios ON  (dbo.Domicilio.CatMunicipiosID = dbo.CatMunicipios.CatMunicipiosID)
										  INNER JOIN dbo.Delito ON (dbo.Carpeta.CarpetaID = dbo.Delito.CarpetaID)
										  INNER JOIN dbo.CatModalidadesEstadisticas ON (dbo.Delito.CatModalidadesID = dbo.CatModalidadesEstadisticas.CatModalidadesEstadisticasID) 
										WHERE
										   (dbo.Carpeta.FechaInicio >= '$fechaINi' AND   dbo.Carpeta.FechaInicio <= '$fechaFin') and
											dbo.carpeta.Contar = 1 and
											dbo.CatModalidadesEstadisticas.CatModalidadesEstadisticasID not in (9,216,217,251) and 
											dbo.CatModalidadesEstadisticas.CatModalidadesEstadisticasID not in (1,2,3,4,5,6,8,9,10,18,237,238,251) and 
											dbo.CatModalidadesEstadisticas.CatModalidadesEstadisticasID not in (26,27,28,29,30,31,32,33,116,241,242) 
										group by
											dbo.Carpeta.NUC,
											dbo.CatMunicipios.CatFiscaliasID ,
											dbo.CatMunicipios.CatDistritosID ,
											dbo.CatModalidadesEstadisticas.Idhistorico ,
											dbo.CatMunicipios.CatMunicipiosID ,
											dbo.CatMunicipios.Nombre ,
											Carpeta.fechaInicio,
											dbo.CatModalidadesEstadisticas.Nombre) T1

											GROUP BY
											T1.IDSUBPRO,
											T1.IDDISTRITO,
											T1.IDDELITO,
											T1.IDMUNICIPIO,
											T1.MES, 
											T1.ANIO   ";
											
											
							importarDatosToDataBase($conn, $query0,"0"); 							
							
							
							
							///////////////  CONSULTA CON INSERT HOMICIDIO DOLOSO ARMA FUEGO ////////////////////////
							
							$query2 = "INSERT INTO HISTORICO2015.dbo.AVE_MUNICIPIOS (IDSUBPRO, IDDISTRITO, IDDELITO, IDMUNICIPIO, CANTIDAD, MES, ANIO)

										SELECT T1.IDSUBPRO, T1.IDDISTRITO, T1.IDDELITO, T1.IDMUNICIPIO, SUM(T1.CANTIDAD) AS CANTIDAD, T1.MES, T1.ANIO 
										FROM (
										SELECT  CASE dbo.CatMunicipios.CatFiscaliasID WHEN 1 then 1  WHEN 2 then 8 WHEN 3 then 2 WHEN 4 then 3  WHEN 5 then 4  WHEN 6 then 5 WHEN 7 then 6 WHEN 8 THEN 9 WHEN 9 THEN 10 WHEN 10 THEN 11 END as 'IDSUBPRO',  
										  dbo.CatMunicipios.CatDistritosID as IDDISTRITO,
										  '161C' as IDDELITO,
										  dbo.CatMunicipios.CatMunicipiosID AS IDMUNICIPIO,
										  count(dbo.Carpeta.NUC) AS 'CANTIDAD',
										  datepart(MM,Carpeta.fechaInicio) as MES,
										  datepart(YYYY,Carpeta.fechaInicio) as ANIO,
										  dbo.CatModalidadesEstadisticas.Nombre,
										  Carpeta.FechaInicio,
										  Carpeta.NUC

										  FROM
										  dbo.Carpeta
										  INNER JOIN dbo.Domicilio ON (dbo.Carpeta.CarpetaID = dbo.Domicilio.CarpetaID)  
                                          INNER JOIN dbo.CatMunicipios ON  (dbo.Domicilio.CatMunicipiosID = dbo.CatMunicipios.CatMunicipiosID)
										  INNER JOIN dbo.Delito ON (dbo.Carpeta.CarpetaID = dbo.Delito.CarpetaID)
										  INNER JOIN dbo.CatModalidadesEstadisticas ON (dbo.Delito.CatModalidadesID = dbo.CatModalidadesEstadisticas.CatModalidadesEstadisticasID) 
										WHERE
										   (dbo.Carpeta.FechaInicio >= '$fechaINi' AND   dbo.Carpeta.FechaInicio <= '$fechaFin') and
											dbo.carpeta.Contar = 1 and   
											dbo.CatModalidadesEstadisticas.CatModalidadesEstadisticasID in (1,2,3,4,5,10,18)  
											and dbo.Carpeta.TipoArma = 1 
										group by
										dbo.Carpeta.NUC,
											dbo.CatMunicipios.CatFiscaliasID ,
											dbo.CatMunicipios.CatDistritosID ,
											dbo.CatModalidadesEstadisticas.Idhistorico ,
											dbo.CatMunicipios.CatMunicipiosID ,
											dbo.CatMunicipios.Nombre ,
											Carpeta.fechaInicio,
											dbo.CatModalidadesEstadisticas.Nombre) T1

											GROUP BY
											T1.IDSUBPRO,
											T1.IDDISTRITO,
											T1.IDDELITO,
											T1.IDMUNICIPIO,
											T1.MES, 
											T1.ANIO 
										 ";
											
							importarDatosToDataBase($conn, $query2,"2"); 				
							
							///////////////  CONSULTA CON INSERT HOMICIDIO DOLOSO ARMA BLANCA ////////////////////////
							
							$query3 = "INSERT INTO HISTORICO2015.dbo.AVE_MUNICIPIOS (IDSUBPRO, IDDISTRITO, IDDELITO, IDMUNICIPIO, CANTIDAD, MES, ANIO)

										SELECT T1.IDSUBPRO, T1.IDDISTRITO, T1.IDDELITO, T1.IDMUNICIPIO, SUM(T1.CANTIDAD) AS CANTIDAD, T1.MES, T1.ANIO 
										FROM (
											SELECT  CASE dbo.CatMunicipios.CatFiscaliasID WHEN 1 then 1  WHEN 2 then 8 WHEN 3 then 2 WHEN 4 then 3  WHEN 5 then 4  WHEN 6 then 5 WHEN 7 then 6 WHEN 8 THEN 9 WHEN 9 THEN 10 WHEN 10 THEN 11 END as 'IDSUBPRO',  
										  dbo.CatMunicipios.CatDistritosID as IDDISTRITO,
										  '161D' as IDDELITO,
										  dbo.CatMunicipios.CatMunicipiosID AS IDMUNICIPIO,
										  count(dbo.Carpeta.NUC) AS 'CANTIDAD',
										  datepart(MM,Carpeta.fechaInicio) as MES,
										  datepart(YYYY,Carpeta.fechaInicio) as ANIO,
										  dbo.CatModalidadesEstadisticas.Nombre,
										  Carpeta.FechaInicio,
										  Carpeta.NUC

										 FROM
										  dbo.Carpeta
										  INNER JOIN dbo.Domicilio ON (dbo.Carpeta.CarpetaID = dbo.Domicilio.CarpetaID)  
                                          INNER JOIN dbo.CatMunicipios ON  (dbo.Domicilio.CatMunicipiosID = dbo.CatMunicipios.CatMunicipiosID)
										  INNER JOIN dbo.Delito ON (dbo.Carpeta.CarpetaID = dbo.Delito.CarpetaID)
										  INNER JOIN dbo.CatModalidadesEstadisticas ON (dbo.Delito.CatModalidadesID = dbo.CatModalidadesEstadisticas.CatModalidadesEstadisticasID) 
										WHERE
										   (dbo.Carpeta.FechaInicio >= '$fechaINi' AND   dbo.Carpeta.FechaInicio <= '$fechaFin') and
											dbo.carpeta.Contar = 1 and   
											dbo.CatModalidadesEstadisticas.CatModalidadesEstadisticasID in (1,2,3,4,5,10,18)  
											and dbo.Carpeta.TipoArma = 2 
										group by
										dbo.Carpeta.NUC,
											dbo.CatMunicipios.CatFiscaliasID ,
											dbo.CatMunicipios.CatDistritosID ,
											dbo.CatModalidadesEstadisticas.Idhistorico ,
											dbo.CatMunicipios.CatMunicipiosID ,
											dbo.CatMunicipios.Nombre ,
											Carpeta.fechaInicio,
											dbo.CatModalidadesEstadisticas.Nombre) T1

											GROUP BY
											T1.IDSUBPRO,
											T1.IDDISTRITO,
											T1.IDDELITO,
											T1.IDMUNICIPIO,
											T1.MES, 
											T1.ANIO  ";
											
							importarDatosToDataBase($conn, $query3,"3"); 				
							
							///////////////  CONSULTA CON INSERT HOMICIDIO DOLOSO RESTO ////////////////////////
							
							$query4 = "INSERT INTO HISTORICO2015.dbo.AVE_MUNICIPIOS (IDSUBPRO, IDDISTRITO, IDDELITO, IDMUNICIPIO, CANTIDAD, MES, ANIO)

										SELECT T1.IDSUBPRO, T1.IDDISTRITO, T1.IDDELITO, T1.IDMUNICIPIO, SUM(T1.CANTIDAD) AS CANTIDAD, T1.MES, T1.ANIO 
										FROM (
											SELECT  CASE dbo.CatMunicipios.CatFiscaliasID WHEN 1 then 1  WHEN 2 then 8 WHEN 3 then 2 WHEN 4 then 3  WHEN 5 then 4  WHEN 6 then 5 WHEN 7 then 6 WHEN 8 THEN 9 WHEN 9 THEN 10 WHEN 10 THEN 11 END as 'IDSUBPRO',  
										  dbo.CatMunicipios.CatDistritosID as IDDISTRITO,
										  dbo.CatModalidadesEstadisticas.Idhistorico AS IDDELITO,
										  dbo.CatMunicipios.CatMunicipiosID AS IDMUNICIPIO,
										  count(dbo.Carpeta.NUC) AS 'CANTIDAD',
										  datepart(MM,Carpeta.fechaInicio) as MES,
										  datepart(YYYY,Carpeta.fechaInicio) as ANIO,
										  dbo.CatModalidadesEstadisticas.Nombre,
										  Carpeta.FechaInicio,
										  Carpeta.NUC

										FROM
										  dbo.Carpeta
										  INNER JOIN dbo.Domicilio ON (dbo.Carpeta.CarpetaID = dbo.Domicilio.CarpetaID)  
                                          INNER JOIN dbo.CatMunicipios ON  (dbo.Domicilio.CatMunicipiosID = dbo.CatMunicipios.CatMunicipiosID)
										  INNER JOIN dbo.Delito ON (dbo.Carpeta.CarpetaID = dbo.Delito.CarpetaID)
										  INNER JOIN dbo.CatModalidadesEstadisticas ON (dbo.Delito.CatModalidadesID = dbo.CatModalidadesEstadisticas.CatModalidadesEstadisticasID) 
										WHERE
										   (dbo.Carpeta.FechaInicio >= '$fechaINi' AND   dbo.Carpeta.FechaInicio <= '$fechaFin') and
											dbo.carpeta.Contar = 1 and   
											dbo.CatModalidadesEstadisticas.CatModalidadesEstadisticasID in (1,2,3,4,5,10,18)  
											and dbo.Carpeta.TipoArma NOT in (1,2) 
										group by
										dbo.Carpeta.NUC,
											dbo.CatMunicipios.CatFiscaliasID ,
											dbo.CatMunicipios.CatDistritosID ,
											dbo.CatModalidadesEstadisticas.Idhistorico ,
											dbo.CatMunicipios.CatMunicipiosID ,
											dbo.CatMunicipios.Nombre ,
											Carpeta.fechaInicio,
											dbo.CatModalidadesEstadisticas.Nombre) T1

											GROUP BY
											T1.IDSUBPRO,
											T1.IDDISTRITO,
											T1.IDDELITO,
											T1.IDMUNICIPIO,
											T1.MES, 
											T1.ANIO ";
											
							importarDatosToDataBase($conn, $query4,"4"); 				
							
							///////////////  CONSULTA CON INSERT HOMICIDIO CULPOSO ARMA FUEGO ////////////////////////
							
							$query5 = "INSERT INTO HISTORICO2015.dbo.AVE_MUNICIPIOS (IDSUBPRO, IDDISTRITO, IDDELITO, IDMUNICIPIO, CANTIDAD, MES, ANIO)

										SELECT T1.IDSUBPRO, T1.IDDISTRITO, T1.IDDELITO, T1.IDMUNICIPIO, SUM(T1.CANTIDAD) AS CANTIDAD, T1.MES, T1.ANIO 
										FROM (
											SELECT  CASE dbo.CatMunicipios.CatFiscaliasID WHEN 1 then 1  WHEN 2 then 8 WHEN 3 then 2 WHEN 4 then 3  WHEN 5 then 4  WHEN 6 then 5 WHEN 7 then 6 WHEN 8 THEN 9 WHEN 9 THEN 10 WHEN 10 THEN 11 END as 'IDSUBPRO',  
										  dbo.CatMunicipios.CatDistritosID as IDDISTRITO,
										   '161A' as IDDELITO,
										  dbo.CatMunicipios.CatMunicipiosID AS IDMUNICIPIO,
										  count(dbo.Carpeta.NUC) AS 'CANTIDAD',
										  datepart(MM,Carpeta.fechaInicio) as MES,
										  datepart(YYYY,Carpeta.fechaInicio) as ANIO,
										  dbo.CatModalidadesEstadisticas.Nombre,
										  Carpeta.FechaInicio,
										  Carpeta.NUC
										FROM
										  dbo.Carpeta
										  INNER JOIN dbo.Domicilio ON (dbo.Carpeta.CarpetaID = dbo.Domicilio.CarpetaID)  
                                          INNER JOIN dbo.CatMunicipios ON  (dbo.Domicilio.CatMunicipiosID = dbo.CatMunicipios.CatMunicipiosID)
										  INNER JOIN dbo.Delito ON (dbo.Carpeta.CarpetaID = dbo.Delito.CarpetaID)
										  INNER JOIN dbo.CatModalidadesEstadisticas ON (dbo.Delito.CatModalidadesID = dbo.CatModalidadesEstadisticas.CatModalidadesEstadisticasID)  
										WHERE
										   (dbo.Carpeta.FechaInicio >= '$fechaINi' AND   dbo.Carpeta.FechaInicio <= '$fechaFin') and
											dbo.carpeta.Contar = 1 and   
											dbo.CatModalidadesEstadisticas.CatModalidadesEstadisticasID in (6,8,237,238)  
											and dbo.Carpeta.TipoArma = 1 
										group by
										dbo.Carpeta.NUC,
											dbo.CatMunicipios.CatFiscaliasID ,
											dbo.CatMunicipios.CatDistritosID ,
											dbo.CatModalidadesEstadisticas.Idhistorico ,
											dbo.CatMunicipios.CatMunicipiosID ,
											dbo.CatMunicipios.Nombre ,
											Carpeta.fechaInicio,
											dbo.CatModalidadesEstadisticas.Nombre) T1

											GROUP BY
											T1.IDSUBPRO,
											T1.IDDISTRITO,
											T1.IDDELITO,
											T1.IDMUNICIPIO,
											T1.MES, 
											T1.ANIO  ";
											
							importarDatosToDataBase($conn, $query5,"5"); 				
							
							///////////////  CONSULTA CON INSERT HOMICIDIO CULPOSO ARMA BLANCA ////////////////////////
							
							$query6 = "INSERT INTO HISTORICO2015.dbo.AVE_MUNICIPIOS (IDSUBPRO, IDDISTRITO, IDDELITO, IDMUNICIPIO, CANTIDAD, MES, ANIO)

										SELECT T1.IDSUBPRO, T1.IDDISTRITO, T1.IDDELITO, T1.IDMUNICIPIO, SUM(T1.CANTIDAD) AS CANTIDAD, T1.MES, T1.ANIO 
										FROM (
											SELECT  CASE dbo.CatMunicipios.CatFiscaliasID WHEN 1 then 1  WHEN 2 then 8 WHEN 3 then 2 WHEN 4 then 3  WHEN 5 then 4  WHEN 6 then 5 WHEN 7 then 6 WHEN 8 THEN 9 WHEN 9 THEN 10 WHEN 10 THEN 11 END as 'IDSUBPRO',  
										  dbo.CatMunicipios.CatDistritosID as IDDISTRITO,
										  '161B' as IDDELITO,
										  dbo.CatMunicipios.CatMunicipiosID AS IDMUNICIPIO,
										  count(dbo.Carpeta.NUC) AS 'CANTIDAD',
										  datepart(MM,Carpeta.fechaInicio) as MES,
										  datepart(YYYY,Carpeta.fechaInicio) as ANIO,
										  dbo.CatModalidadesEstadisticas.Nombre,
										  Carpeta.FechaInicio,
										  Carpeta.NUC
										FROM
										  dbo.Carpeta
										  INNER JOIN dbo.Domicilio ON (dbo.Carpeta.CarpetaID = dbo.Domicilio.CarpetaID)  
                                          INNER JOIN dbo.CatMunicipios ON  (dbo.Domicilio.CatMunicipiosID = dbo.CatMunicipios.CatMunicipiosID)
										  INNER JOIN dbo.Delito ON (dbo.Carpeta.CarpetaID = dbo.Delito.CarpetaID)
										  INNER JOIN dbo.CatModalidadesEstadisticas ON (dbo.Delito.CatModalidadesID = dbo.CatModalidadesEstadisticas.CatModalidadesEstadisticasID) 
										WHERE
										   (dbo.Carpeta.FechaInicio >= '$fechaINi' AND   dbo.Carpeta.FechaInicio <= '$fechaFin') and
											dbo.carpeta.Contar = 1 and   
											dbo.CatModalidadesEstadisticas.CatModalidadesEstadisticasID in (6,8,237,238)   
											and dbo.Carpeta.TipoArma = 2 
										group by
										dbo.Carpeta.NUC,
											dbo.CatMunicipios.CatFiscaliasID ,
											dbo.CatMunicipios.CatDistritosID ,
											dbo.CatModalidadesEstadisticas.Idhistorico ,
											dbo.CatMunicipios.CatMunicipiosID ,
											dbo.CatMunicipios.Nombre ,
											Carpeta.fechaInicio,
											dbo.CatModalidadesEstadisticas.Nombre) T1

											GROUP BY
											T1.IDSUBPRO,
											T1.IDDISTRITO,
											T1.IDDELITO,
											T1.IDMUNICIPIO,
											T1.MES, 
											T1.ANIO  ";
											
							importarDatosToDataBase($conn, $query6,"6"); 				
							
							///////////////  CONSULTA CON INSERT HOMICIDIO CULPOSO RESTO ////////////////////////
							
							$query7 = "INSERT INTO HISTORICO2015.dbo.AVE_MUNICIPIOS (IDSUBPRO, IDDISTRITO, IDDELITO, IDMUNICIPIO, CANTIDAD, MES, ANIO)

										SELECT T1.IDSUBPRO, T1.IDDISTRITO, T1.IDDELITO, T1.IDMUNICIPIO, SUM(T1.CANTIDAD) AS CANTIDAD, T1.MES, T1.ANIO 
										FROM (
											SELECT  CASE dbo.CatMunicipios.CatFiscaliasID WHEN 1 then 1  WHEN 2 then 8 WHEN 3 then 2 WHEN 4 then 3  WHEN 5 then 4  WHEN 6 then 5 WHEN 7 then 6 WHEN 8 THEN 9 WHEN 9 THEN 10 WHEN 10 THEN 11 END as 'IDSUBPRO',  
										  dbo.CatMunicipios.CatDistritosID as IDDISTRITO,
										  dbo.CatModalidadesEstadisticas.Idhistorico AS IDDELITO,
										  dbo.CatMunicipios.CatMunicipiosID AS IDMUNICIPIO,
										  count(dbo.Carpeta.NUC) AS 'CANTIDAD',
										  datepart(MM,Carpeta.fechaInicio) as MES,
										  datepart(YYYY,Carpeta.fechaInicio) as ANIO,
										  dbo.CatModalidadesEstadisticas.Nombre,
										  Carpeta.FechaInicio,
										  Carpeta.NUC
										FROM
										  dbo.Carpeta
										  INNER JOIN dbo.Domicilio ON (dbo.Carpeta.CarpetaID = dbo.Domicilio.CarpetaID)  
                                          INNER JOIN dbo.CatMunicipios ON  (dbo.Domicilio.CatMunicipiosID = dbo.CatMunicipios.CatMunicipiosID)
										  INNER JOIN dbo.Delito ON (dbo.Carpeta.CarpetaID = dbo.Delito.CarpetaID)
										  INNER JOIN dbo.CatModalidadesEstadisticas ON (dbo.Delito.CatModalidadesID = dbo.CatModalidadesEstadisticas.CatModalidadesEstadisticasID) 
										WHERE
										   (dbo.Carpeta.FechaInicio >= '$fechaINi' AND   dbo.Carpeta.FechaInicio <= '$fechaFin') and
											dbo.carpeta.Contar = 1 and   
											dbo.CatModalidadesEstadisticas.CatModalidadesEstadisticasID in (6,8,237,238)  
											and dbo.Carpeta.TipoArma NOT in (1,2)
										group by
										dbo.Carpeta.NUC,
											dbo.CatMunicipios.CatFiscaliasID ,
											dbo.CatMunicipios.CatDistritosID ,
											dbo.CatModalidadesEstadisticas.Idhistorico ,
											dbo.CatMunicipios.CatMunicipiosID ,
											dbo.CatMunicipios.Nombre ,
											Carpeta.fechaInicio,
											dbo.CatModalidadesEstadisticas.Nombre) T1

											GROUP BY
											T1.IDSUBPRO,
											T1.IDDISTRITO,
											T1.IDDELITO,
											T1.IDMUNICIPIO,
											T1.MES, 
											T1.ANIO	";
											
							importarDatosToDataBase($conn, $query7,"7"); 				
							
							///////////////  CONSULTA CON INSERT LESIONES DOLOSAS ARMA FUEGO ////////////////////////
							
							$query8 = "INSERT INTO HISTORICO2015.dbo.AVE_MUNICIPIOS (IDSUBPRO, IDDISTRITO, IDDELITO, IDMUNICIPIO, CANTIDAD, MES, ANIO)

										SELECT T1.IDSUBPRO, T1.IDDISTRITO, T1.IDDELITO, T1.IDMUNICIPIO, SUM(T1.CANTIDAD) AS CANTIDAD, T1.MES, T1.ANIO 
										FROM (
											SELECT  CASE dbo.CatMunicipios.CatFiscaliasID WHEN 1 then 1  WHEN 2 then 8 WHEN 3 then 2 WHEN 4 then 3  WHEN 5 then 4  WHEN 6 then 5 WHEN 7 then 6 WHEN 8 THEN 9 WHEN 9 THEN 10 WHEN 10 THEN 11 END as 'IDSUBPRO',  
										  dbo.CatMunicipios.CatDistritosID as IDDISTRITO,
										  '1628' as IDDELITO,
										  dbo.CatMunicipios.CatMunicipiosID AS IDMUNICIPIO,
										  count(dbo.Carpeta.NUC) AS 'CANTIDAD',
										  datepart(MM,Carpeta.fechaInicio) as MES,
										  datepart(YYYY,Carpeta.fechaInicio) as ANIO,
										  dbo.CatModalidadesEstadisticas.Nombre,
										  Carpeta.FechaInicio,
										  Carpeta.NUC
										FROM
										  dbo.Carpeta
										  INNER JOIN dbo.Domicilio ON (dbo.Carpeta.CarpetaID = dbo.Domicilio.CarpetaID)  
                                          INNER JOIN dbo.CatMunicipios ON  (dbo.Domicilio.CatMunicipiosID = dbo.CatMunicipios.CatMunicipiosID)
										  INNER JOIN dbo.Delito ON (dbo.Carpeta.CarpetaID = dbo.Delito.CarpetaID)
										  INNER JOIN dbo.CatModalidadesEstadisticas ON (dbo.Delito.CatModalidadesID = dbo.CatModalidadesEstadisticas.CatModalidadesEstadisticasID)  
										WHERE
										   (dbo.Carpeta.FechaInicio >= '$fechaINi' AND   dbo.Carpeta.FechaInicio <= '$fechaFin') and
											dbo.carpeta.Contar = 1 and   
											dbo.CatModalidadesEstadisticas.CatModalidadesEstadisticasID in (26,27,28,29,30,31,116,241,242)   
											and dbo.Carpeta.TipoArma = 1 
										group by
											dbo.Carpeta.NUC,
											dbo.CatMunicipios.CatFiscaliasID ,
											dbo.CatMunicipios.CatDistritosID ,
											dbo.CatModalidadesEstadisticas.Idhistorico ,
											dbo.CatMunicipios.CatMunicipiosID ,
											dbo.CatMunicipios.Nombre ,
											Carpeta.fechaInicio,
											dbo.CatModalidadesEstadisticas.Nombre) T1

											GROUP BY
											T1.IDSUBPRO,
											T1.IDDISTRITO,
											T1.IDDELITO,
											T1.IDMUNICIPIO,
											T1.MES, 
											T1.ANIO ";
											
							importarDatosToDataBase($conn, $query8,"8"); 				
							
							///////////////  CONSULTA CON INSERT LESIONES DOLOSAS ARMA BLANCA ////////////////////////
							
							$query9 = "INSERT INTO HISTORICO2015.dbo.AVE_MUNICIPIOS (IDSUBPRO, IDDISTRITO, IDDELITO, IDMUNICIPIO, CANTIDAD, MES, ANIO)

										SELECT T1.IDSUBPRO, T1.IDDISTRITO, T1.IDDELITO, T1.IDMUNICIPIO, SUM(T1.CANTIDAD) AS CANTIDAD, T1.MES, T1.ANIO 
										FROM (
											SELECT  CASE dbo.CatMunicipios.CatFiscaliasID WHEN 1 then 1  WHEN 2 then 8 WHEN 3 then 2 WHEN 4 then 3  WHEN 5 then 4  WHEN 6 then 5 WHEN 7 then 6 WHEN 8 THEN 9 WHEN 9 THEN 10 WHEN 10 THEN 11 END as 'IDSUBPRO',  
										  dbo.CatMunicipios.CatDistritosID as IDDISTRITO,
										  '1629' as IDDELITO,
										  dbo.CatMunicipios.CatMunicipiosID AS IDMUNICIPIO,
										  count(dbo.Carpeta.NUC) AS 'CANTIDAD',
										  datepart(MM,Carpeta.fechaInicio) as MES,
										  datepart(YYYY,Carpeta.fechaInicio) as ANIO,
										  dbo.CatModalidadesEstadisticas.Nombre,
										  Carpeta.FechaInicio,
										  Carpeta.NUC

										 FROM
										  dbo.Carpeta
										  INNER JOIN dbo.Domicilio ON (dbo.Carpeta.CarpetaID = dbo.Domicilio.CarpetaID)  
                                          INNER JOIN dbo.CatMunicipios ON  (dbo.Domicilio.CatMunicipiosID = dbo.CatMunicipios.CatMunicipiosID)
										  INNER JOIN dbo.Delito ON (dbo.Carpeta.CarpetaID = dbo.Delito.CarpetaID)
										  INNER JOIN dbo.CatModalidadesEstadisticas ON (dbo.Delito.CatModalidadesID = dbo.CatModalidadesEstadisticas.CatModalidadesEstadisticasID)   
										WHERE
										   (dbo.Carpeta.FechaInicio >= '$fechaINi' AND   dbo.Carpeta.FechaInicio <= '$fechaFin') and
											dbo.carpeta.Contar = 1 and   
											dbo.CatModalidadesEstadisticas.CatModalidadesEstadisticasID in (26,27,28,29,30,31,116,241,242)  
											and dbo.Carpeta.TipoArma = 2 
										group by
										dbo.Carpeta.NUC,
											dbo.CatMunicipios.CatFiscaliasID ,
											dbo.CatMunicipios.CatDistritosID ,
											dbo.CatModalidadesEstadisticas.Idhistorico ,
											dbo.CatMunicipios.CatMunicipiosID ,
											dbo.CatMunicipios.Nombre ,
											Carpeta.fechaInicio,
											dbo.CatModalidadesEstadisticas.Nombre) T1

											GROUP BY
											T1.IDSUBPRO,
											T1.IDDISTRITO,
											T1.IDDELITO,
											T1.IDMUNICIPIO,
											T1.MES, 
											T1.ANIO ";											
											
							importarDatosToDataBase($conn, $query9,"9"); 				
							
							///////////////  CONSULTA CON INSERT LESIONES DOLOSAS RESTO ////////////////////////
							
							$query10 = "INSERT INTO HISTORICO2015.dbo.AVE_MUNICIPIOS (IDSUBPRO, IDDISTRITO, IDDELITO, IDMUNICIPIO, CANTIDAD, MES, ANIO)

										SELECT T1.IDSUBPRO, T1.IDDISTRITO, T1.IDDELITO, T1.IDMUNICIPIO, SUM(T1.CANTIDAD) AS CANTIDAD, T1.MES, T1.ANIO 
										FROM (
											SELECT  CASE dbo.CatMunicipios.CatFiscaliasID WHEN 1 then 1  WHEN 2 then 8 WHEN 3 then 2 WHEN 4 then 3  WHEN 5 then 4  WHEN 6 then 5 WHEN 7 then 6 WHEN 8 THEN 9 WHEN 9 THEN 10 WHEN 10 THEN 11 END as 'IDSUBPRO',  
										  dbo.CatMunicipios.CatDistritosID as IDDISTRITO,
										  dbo.CatModalidadesEstadisticas.Idhistorico AS IDDELITO,
										  dbo.CatMunicipios.CatMunicipiosID AS IDMUNICIPIO,
										  count(dbo.Carpeta.NUC) AS 'CANTIDAD',
										  datepart(MM,Carpeta.fechaInicio) as MES,
										  datepart(YYYY,Carpeta.fechaInicio) as ANIO,
										  dbo.CatModalidadesEstadisticas.Nombre,
										  Carpeta.FechaInicio,
										  Carpeta.NUC

										FROM
										  dbo.Carpeta
										  INNER JOIN dbo.Domicilio ON (dbo.Carpeta.CarpetaID = dbo.Domicilio.CarpetaID)  
                                          INNER JOIN dbo.CatMunicipios ON  (dbo.Domicilio.CatMunicipiosID = dbo.CatMunicipios.CatMunicipiosID)
										  INNER JOIN dbo.Delito ON (dbo.Carpeta.CarpetaID = dbo.Delito.CarpetaID)
										  INNER JOIN dbo.CatModalidadesEstadisticas ON (dbo.Delito.CatModalidadesID = dbo.CatModalidadesEstadisticas.CatModalidadesEstadisticasID)  
										WHERE
										   (dbo.Carpeta.FechaInicio >= '$fechaINi' AND   dbo.Carpeta.FechaInicio <= '$fechaFin') and
											dbo.carpeta.Contar = 1 and   
											dbo.CatModalidadesEstadisticas.CatModalidadesEstadisticasID in (26,27,28,29,30,31,116,241,242)  
											and dbo.Carpeta.TipoArma NOT in (1,2)
										group by
										dbo.Carpeta.NUC,
											dbo.CatMunicipios.CatFiscaliasID ,
											dbo.CatMunicipios.CatDistritosID ,
											dbo.CatModalidadesEstadisticas.Idhistorico ,
											dbo.CatMunicipios.CatMunicipiosID ,
											dbo.CatMunicipios.Nombre ,
											Carpeta.fechaInicio,
											dbo.CatModalidadesEstadisticas.Nombre) T1

											GROUP BY
											T1.IDSUBPRO,
											T1.IDDISTRITO,
											T1.IDDELITO,
											T1.IDMUNICIPIO,
											T1.MES, 
											T1.ANIO ";
											
											
							importarDatosToDataBase($conn, $query10,"10"); 				
							
							///////////////  CONSULTA CON INSERT LESIONES CULPOSAS ARMA FUEGO ////////////////////////
							
							$query11 = "INSERT INTO HISTORICO2015.dbo.AVE_MUNICIPIOS (IDSUBPRO, IDDISTRITO, IDDELITO, IDMUNICIPIO, CANTIDAD, MES, ANIO)

										SELECT T1.IDSUBPRO, T1.IDDISTRITO, T1.IDDELITO, T1.IDMUNICIPIO, SUM(T1.CANTIDAD) AS CANTIDAD, T1.MES, T1.ANIO 
										FROM (
											SELECT  CASE dbo.CatMunicipios.CatFiscaliasID WHEN 1 then 1  WHEN 2 then 8 WHEN 3 then 2 WHEN 4 then 3  WHEN 5 then 4  WHEN 6 then 5 WHEN 7 then 6 WHEN 8 THEN 9 WHEN 9 THEN 10 WHEN 10 THEN 11 END as 'IDSUBPRO',  
										  dbo.CatMunicipios.CatDistritosID as IDDISTRITO,
										  '1626' as IDDELITO,
										  dbo.CatMunicipios.CatMunicipiosID AS IDMUNICIPIO,
										  count(dbo.Carpeta.NUC) AS 'CANTIDAD',
										  datepart(MM,Carpeta.fechaInicio) as MES,
										  datepart(YYYY,Carpeta.fechaInicio) as ANIO,
										  dbo.CatModalidadesEstadisticas.Nombre,
										  Carpeta.FechaInicio,
										  Carpeta.NUC

										FROM
										  dbo.Carpeta
										  INNER JOIN dbo.Domicilio ON (dbo.Carpeta.CarpetaID = dbo.Domicilio.CarpetaID)  
                                          INNER JOIN dbo.CatMunicipios ON  (dbo.Domicilio.CatMunicipiosID = dbo.CatMunicipios.CatMunicipiosID)
										  INNER JOIN dbo.Delito ON (dbo.Carpeta.CarpetaID = dbo.Delito.CarpetaID)
										  INNER JOIN dbo.CatModalidadesEstadisticas ON (dbo.Delito.CatModalidadesID = dbo.CatModalidadesEstadisticas.CatModalidadesEstadisticasID)   
										WHERE
										   (dbo.Carpeta.FechaInicio >= '$fechaINi' AND   dbo.Carpeta.FechaInicio <= '$fechaFin') and
											dbo.carpeta.Contar = 1 and   
											dbo.CatModalidadesEstadisticas.CatModalidadesEstadisticasID in (32,33) 
											and dbo.Carpeta.TipoArma = 1 
										group by
										dbo.Carpeta.NUC,
											dbo.CatMunicipios.CatFiscaliasID ,
											dbo.CatMunicipios.CatDistritosID ,
											dbo.CatModalidadesEstadisticas.Idhistorico ,
											dbo.CatMunicipios.CatMunicipiosID ,
											dbo.CatMunicipios.Nombre ,
											Carpeta.fechaInicio,
											dbo.CatModalidadesEstadisticas.Nombre) T1

											GROUP BY
											T1.IDSUBPRO,
											T1.IDDISTRITO,
											T1.IDDELITO,
											T1.IDMUNICIPIO,
											T1.MES, 
											T1.ANIO ";
											
							importarDatosToDataBase($conn, $query11,"11"); 				
							
							///////////////  CONSULTA CON INSERT LESIONES CULPOSAS ARMA BLANZA ////////////////////////
							
							$query12 = "INSERT INTO HISTORICO2015.dbo.AVE_MUNICIPIOS (IDSUBPRO, IDDISTRITO, IDDELITO, IDMUNICIPIO, CANTIDAD, MES, ANIO)

										SELECT T1.IDSUBPRO, T1.IDDISTRITO, T1.IDDELITO, T1.IDMUNICIPIO, SUM(T1.CANTIDAD) AS CANTIDAD, T1.MES, T1.ANIO 
										FROM (
											SELECT  CASE dbo.CatMunicipios.CatFiscaliasID WHEN 1 then 1  WHEN 2 then 8 WHEN 3 then 2 WHEN 4 then 3  WHEN 5 then 4  WHEN 6 then 5 WHEN 7 then 6 WHEN 8 THEN 9 WHEN 9 THEN 10 WHEN 10 THEN 11 END as 'IDSUBPRO',  
										  dbo.CatMunicipios.CatDistritosID as IDDISTRITO,
										  '1627' as IDDELITO,
										  dbo.CatMunicipios.CatMunicipiosID AS IDMUNICIPIO,
										  count(dbo.Carpeta.NUC) AS 'CANTIDAD',
										  datepart(MM,Carpeta.fechaInicio) as MES,
										  datepart(YYYY,Carpeta.fechaInicio) as ANIO,
										  dbo.CatModalidadesEstadisticas.Nombre,
										  Carpeta.FechaInicio,
										  Carpeta.NUC
										 FROM
										  dbo.Carpeta
										  INNER JOIN dbo.Domicilio ON (dbo.Carpeta.CarpetaID = dbo.Domicilio.CarpetaID)  
                                          INNER JOIN dbo.CatMunicipios ON  (dbo.Domicilio.CatMunicipiosID = dbo.CatMunicipios.CatMunicipiosID)
										  INNER JOIN dbo.Delito ON (dbo.Carpeta.CarpetaID = dbo.Delito.CarpetaID)
										  INNER JOIN dbo.CatModalidadesEstadisticas ON (dbo.Delito.CatModalidadesID = dbo.CatModalidadesEstadisticas.CatModalidadesEstadisticasID)   
										WHERE
										   (dbo.Carpeta.FechaInicio >= '$fechaINi' AND   dbo.Carpeta.FechaInicio <= '$fechaFin') and
											dbo.carpeta.Contar = 1 and   
											dbo.CatModalidadesEstadisticas.CatModalidadesEstadisticasID in (32,33)   
											and dbo.Carpeta.TipoArma = 2 
										group by
										dbo.Carpeta.NUC,
											dbo.CatMunicipios.CatFiscaliasID ,
											dbo.CatMunicipios.CatDistritosID ,
											dbo.CatModalidadesEstadisticas.Idhistorico ,
											dbo.CatMunicipios.CatMunicipiosID ,
											dbo.CatMunicipios.Nombre ,
											Carpeta.fechaInicio,
											dbo.CatModalidadesEstadisticas.Nombre) T1

											GROUP BY
											T1.IDSUBPRO,
											T1.IDDISTRITO,
											T1.IDDELITO,
											T1.IDMUNICIPIO,
											T1.MES, 
											T1.ANIO ";
											
							importarDatosToDataBase($conn, $query12,"12"); 				
							
							///////////////  CONSULTA CON INSERT LESIONES CULPOSAS RESTO ////////////////////////
						
							$query13 = "INSERT INTO HISTORICO2015.dbo.AVE_MUNICIPIOS (IDSUBPRO, IDDISTRITO, IDDELITO, IDMUNICIPIO, CANTIDAD, MES, ANIO)

										SELECT T1.IDSUBPRO, T1.IDDISTRITO, T1.IDDELITO, T1.IDMUNICIPIO, SUM(T1.CANTIDAD) AS CANTIDAD, T1.MES, T1.ANIO 
										FROM (
											SELECT  CASE dbo.CatMunicipios.CatFiscaliasID WHEN 1 then 1  WHEN 2 then 8 WHEN 3 then 2 WHEN 4 then 3  WHEN 5 then 4  WHEN 6 then 5 WHEN 7 then 6 WHEN 8 THEN 9 WHEN 9 THEN 10 WHEN 10 THEN 11 END as 'IDSUBPRO',  
										  dbo.CatMunicipios.CatDistritosID as IDDISTRITO,
										  dbo.CatModalidadesEstadisticas.Idhistorico AS IDDELITO,
										  dbo.CatMunicipios.CatMunicipiosID AS IDMUNICIPIO,
										  count(dbo.Carpeta.NUC) AS 'CANTIDAD',
										  datepart(MM,Carpeta.fechaInicio) as MES,
										  datepart(YYYY,Carpeta.fechaInicio) as ANIO,
										  dbo.CatModalidadesEstadisticas.Nombre,
										  Carpeta.FechaInicio,
										  Carpeta.NUC
										FROM
										  dbo.Carpeta
										  INNER JOIN dbo.Domicilio ON (dbo.Carpeta.CarpetaID = dbo.Domicilio.CarpetaID)  
                                          INNER JOIN dbo.CatMunicipios ON  (dbo.Domicilio.CatMunicipiosID = dbo.CatMunicipios.CatMunicipiosID)
										  INNER JOIN dbo.Delito ON (dbo.Carpeta.CarpetaID = dbo.Delito.CarpetaID)
										  INNER JOIN dbo.CatModalidadesEstadisticas ON (dbo.Delito.CatModalidadesID = dbo.CatModalidadesEstadisticas.CatModalidadesEstadisticasID) 
										WHERE
										   (dbo.Carpeta.FechaInicio >= '$fechaINi' AND   dbo.Carpeta.FechaInicio <= '$fechaFin') and
											dbo.carpeta.Contar = 1 and   
											dbo.CatModalidadesEstadisticas.CatModalidadesEstadisticasID in (32,33)  
											and dbo.Carpeta.TipoArma NOT in (1,2) 
										group by
										dbo.Carpeta.NUC,
											dbo.CatMunicipios.CatFiscaliasID ,
											dbo.CatMunicipios.CatDistritosID ,
											dbo.CatModalidadesEstadisticas.Idhistorico ,
											dbo.CatMunicipios.CatMunicipiosID ,
											dbo.CatMunicipios.Nombre ,
											Carpeta.fechaInicio,
											dbo.CatModalidadesEstadisticas.Nombre) T1

											GROUP BY
											T1.IDSUBPRO,
											T1.IDDISTRITO,
											T1.IDDELITO,
											T1.IDMUNICIPIO,
											T1.MES, 
											T1.ANIO ";
											
							importarDatosToDataBase($conn, $query13,"13"); 

							///////////////  CONSULTA CON AVERIGUACIONES CON VIOLENCIAS ////////////////////////			
							
							
							$query14 = "INSERT INTO HISTORICO2015.dbo.VIOLENCIA (IDSUBPRO, IDMUNICIPIO, DELITO, CANTIDAD, MES, ANIO)

							SELECT T1.IDSUBPRO, T1.IDMUNICIPIO, T1.DELITO, SUM(T1.CANTIDAD) AS CANTIDAD, T1.MES, T1.ANIO 
										FROM (
										SELECT  CASE dbo.CatMunicipios.CatFiscaliasID WHEN 1 then 1  WHEN 2 then 8 WHEN 3 then 2 WHEN 4 then 3  WHEN 5 then 4  WHEN 6 then 5 WHEN 7 then 6 WHEN 8 THEN 9 WHEN 9 THEN 10 WHEN 10 THEN 11 END as 'IDSUBPRO',  
										  dbo.CatMunicipios.CatMunicipiosID AS IDMUNICIPIO,
										  dbo.CatModalidadesEstadisticas.Idhistorico AS DELITO,										  
										  count(dbo.Carpeta.NUC) AS 'CANTIDAD',
										  datepart(MM,Carpeta.fechaInicio) as MES,
										  datepart(YYYY,Carpeta.fechaInicio) as ANIO,
										  dbo.CatModalidadesEstadisticas.Nombre,
										  Carpeta.FechaInicio,
										  Carpeta.NUC
		
										  FROM
										  dbo.Carpeta
										  INNER JOIN dbo.Domicilio ON (dbo.Carpeta.CarpetaID = dbo.Domicilio.CarpetaID)  
                                          INNER JOIN dbo.CatMunicipios ON  (dbo.Domicilio.CatMunicipiosID = dbo.CatMunicipios.CatMunicipiosID)
										  INNER JOIN dbo.Delito ON (dbo.Carpeta.CarpetaID = dbo.Delito.CarpetaID)
										  INNER JOIN dbo.CatModalidadesEstadisticas ON (dbo.Delito.CatModalidadesID = dbo.CatModalidadesEstadisticas.CatModalidadesEstadisticasID) 
										WHERE
										   (dbo.Carpeta.FechaInicio >= '$fechaINi' AND   dbo.Carpeta.FechaInicio <= '$fechaFin') and
											dbo.carpeta.Contar = 1 and
											dbo.Carpeta.Violencia = 1 and 
											dbo.CatModalidadesEstadisticas.CatModalidadesEstadisticasID in (54,57,58,246,75,87,246)
										group by
											dbo.Carpeta.NUC,
											dbo.CatMunicipios.CatFiscaliasID ,
											dbo.CatMunicipios.CatDistritosID ,
											dbo.CatModalidadesEstadisticas.Idhistorico ,
											dbo.CatMunicipios.CatMunicipiosID ,
											dbo.CatMunicipios.Nombre ,
											Carpeta.fechaInicio,
											dbo.CatModalidadesEstadisticas.Nombre) T1

											GROUP BY
											T1.IDSUBPRO,
											T1.DELITO,
											T1.IDMUNICIPIO,
											T1.MES, 
											T1.ANIO";
											
											
								importarDatosToDataBase($conn, $query14,"14"); 	
							
							echo "<h3><br><br>DATOS EXPORTADOS EXITOSAMENTE!</h3>"; 
							
					}else{echo "No se conectoooooooooo............";}	
				
				?>
				
				
			</section>
		</section>
				
			</body>
		</html>		