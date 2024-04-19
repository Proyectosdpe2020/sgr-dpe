<?php session_start(); ?>
<!DOCTYPE HTML>
<!--
	Editorial by HTML5 UP
	html5up.net | @ajlkn
	Free for personal and commercial use under the CCA 3.0 license (html5up.net/license)
-->
<html>
	<head>
		<title>SBN</title>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />

		<meta http-equiv="cache-control" content="no-cache" />
		<meta http-equiv="pragma" content="no-cache" />


		<link rel="shortcut icon" href="../../assets/img/fge.png"/>

		<link rel="stylesheet" href="assets/css/main.css" />
		<link rel="stylesheet" href="assets/css/styles.css" />

		<!--<link rel="stylesheet" href="../../css/styles.css">-->
		<link rel="stylesheet" href="../../css/styles.css">
		<link rel="stylesheet" href="../../css/dropdown-style.css">
		<link rel="stylesheet" href="../../node_modules/bootstrap/dist/css/bootstrap.min.css">

		<script src="../../node_modules/jquery/dist/jquery.min.js" ></script>
		<script src="../../node_modules/bootstrap/dist/js/bootstrap.bundle.min.js" ></script>
		<script src="../../node_modules/sweetalert2/dist/sweetalert2.all.min.js"></script>

		<script src="../../js/script.js"></script>

		<script src="js/script.js"></script>
	</head>
	<body class="is-preload">

		<div class="loader-div"></div>

		<!-- Wrapper -->
			<div id="wrapper">

				<!-- Main -->
					<div id="main">

						<!-- navbar -->
						<header id="navbar">

							<!--<div class="dropdown">
								<div onclick="myFunction()" class="dropbtn">
								
									<img src="../../assets/img/user.png" alt="">

									<div class="username-section">
	
										<div id="username">José Daniel García Sánchez</div>
	
										<div id="role">Administrador</div>
										
									</div></div>

								

								<div id="myDropdown" class="dropdown-content">
									<a href="#home">Home</a>
									<a href="#about">About</a>
									<a href="#contact">Contact</a>
								</div>
							</div>-->

							<!--<img src="../../assets/img/user.png" alt="">

							<div class="username-section">

								<div id="username">José Daniel García Sánchez</div>

								<div id="role">Administrador</div>
								
							</div>-->

							<div class="dropdown username-section">

								<div class="dropbtn">
								
									<img onclick="myFunction()" src="../../assets/img/user.png" alt="">

									<div onclick="myFunction()">
	
										<div id="username"><?php echo $_SESSION['user_data']['name'].' '.$_SESSION['user_data']['paternal_surname'].' '.$_SESSION['user_data']['maternal_surname']; ?></div>
	
										<div id="role">Administrador</div>
										
									</div>
								</div>

								<div id="myDropdown" class="dropdown-content">
									
									<a onclick="closeSession()">Cerrar Sesión</a>

								</div>
							</div>


						</header>

						<div class="background-header">

							<h1>SENAP</h1>

						</div>

						<div class="inner">

							


							

							<!-- Section -->
								<section>

									<div class="form-top-Buttons" id="senap-menu">		
							
										<button type="button" class="btn btn-outline-primary senap-menu-button left-button" id="reports" style="height:38px; width: 150px;" onclick="changeSenapForm('reports')" disabled> Reportes </button>

										<button type="button" class="btn btn-outline-primary senap-menu-button right-button" id="dump" style="height:38px; width: 150px;" onclick="changeSenapForm('dump')"> Volcado </button>
						
										<!--<button type="button" class="btn btn-outline-success" style="height:38px; width: 100px;"  onclick="testm(null)">test</button>-->
						
										<!--<button type="button" class="btn btn-outline-success" style="height:38px; width: 100px;"  onclick="tableToExcel()">excel</button>-->
									
									</div>

									<br>

									<div id="senap-form-section">

										<form class="search-form" action="#">	

											<div class="form-row">
								
												<!--<div class="col-md-4¿2 form-group">
								
													<label style="font-weight:bold">Año: *</label>
								
													<input type="text" class="form-control" id="search-year" maxlength="4" onkeypress="validateNumber(event);">	
								
												</div>-->
								
												<div class="col-md-6 form-group">
								
													<label style="font-weight:bold">Opcion: *</label>
								
													<select id="main-search-option" class="form-control" required="true">									
														<option value ="1" selected>Noticia criminal</option>
														<option value ="2">Carpeta de investigación</option>
														<option value ="3">Etapa de investigación</option>
														<option value ="4">Actos de investigación</option>
														<option value ="5">Delitos</option>
														<option value ="6">Bienes asegurados</option>
														<option value ="7">Victimas-Delitos</option>
														<option value ="8">Imputados-Delitos</option>
														<option value ="9">Victima-Imputado</option>
														<option value ="10">Determinación</option>
														<option value ="11">Proceso</option>
														<option value ="12">Mandamientos Judiciales</option>
														<option value ="13">Investigación Complementaria</option>
														<option value ="14">Medidas Cautelares</option>
														<option value ="15">Etapa Intermedia</option>
														<option value ="16">MASC</option>
														<option value ="17">Sobreseimientos</option>
														<option value ="18">Suspensión Condicional</option>
														<option value ="19">Sentencias</option>
													</select>	
								
												</div>
								
												<div class="col-md-6 form-group">
								
													<label style="font-weight:bold">Mes y año: *</label>
								
													<input id="main-search-search-month" type="month" class="form-control" required="true">	
								
												</div>
												
												
											</div>
											
								
											<div class="form-buttons">
												
												<button type="button" class="btn btn-outline-primary" style="height:38px; width: 160px;"  onclick="searchSenap('xlsx')">Descargar EXCEL</button>
								
												<button type="button" class="btn btn-outline-success" style="height:38px; width: 150px;"  onclick="searchSenap('csv')">Descargar CSV</button>
								
												<!--<button type="button" class="btn btn-outline-success" style="height:38px; width: 100px;"  onclick="testm(null)">test</button>-->
								
												<!--<button type="button" class="btn btn-outline-success" style="height:38px; width: 100px;"  onclick="tableToExcel()">excel</button>-->
											
											</div>
								
										</form>

									</div>

								</section>

						</div>
					</div>

				<!-- Sidebar -->
					<div id="sidebar">
						<div class="inner">

							<!-- Search -->
								<!--<section id="search" class="alt">
									<form method="post" action="#">
										<input type="text" name="query" id="query" placeholder="Search" />
									</form>
								</section>-->

							<!-- Menu -->
								<nav id="menu">
									<header class="major">
										<!--<h2><img src="../../assets/img/fge.png" alt="" width="40" height="40">&nbsp;BASES NACIONALES</h2>-->
										<h2><a id="text-logo">FGE</a>&nbsp;&nbsp;&nbsp;BASES NACIONALES</h2>
									</header>
									<ul>
										<li class="selected"><i class="fa fa-circle" aria-hidden="true"></i>&nbsp;<a href="#">SENAP</a></li>
										<li><i class="fa fa-circle" aria-hidden="true"></i>&nbsp;<a href="microdato.php">MICRODATO</a></li>
										<li><i class="fa fa-circle" aria-hidden="true"></i>&nbsp;<a href="avp.php">Exportar Base de datos histórica</a></li>
										<li><i class="fa fa-circle" aria-hidden="true"></i>&nbsp;<a href="norma_tecnica.php">Norma Técnica</a></li>
										<li><i class="fa fa-circle" aria-hidden="true"></i>&nbsp;<a href="censo_procu.php">Censo procuración de justicia</a></li>
										<li><i class="fa fa-circle" aria-hidden="true"></i>&nbsp;<a href="incidencia_sesesp.php">Incidencia delictiva SESESP</a></li>
									</ul>
								</nav>

							<!-- Footer -->
								<footer id="footer">
									<!--<p>Sistema de gestion de reportes v1-21.08.04</p>-->
								</footer>

						</div>

						
					</div>


			</div>

		<!-- Scripts -->
			<script src="assets/js/jquery.min.js"></script>
			<script src="assets/js/browser.min.js"></script>
			<script src="assets/js/breakpoints.min.js"></script>
			<script src="assets/js/util.js"></script>
			<script src="assets/js/main.js"></script>

	</body>
</html>