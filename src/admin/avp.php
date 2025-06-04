<?php session_start();
if($_SESSION['user_data']['id'] == 4 || $_SESSION['user_data']['id'] == 5 || $_SESSION['user_data']['id'] == 8){
	header('Location: validacion_victimas.php');
    exit();
}
?>
<!DOCTYPE HTML>
<html>
	<head>
		<title>SBN</title>
		<meta charset="utf-8"/>
		<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no"/>
		<meta http-equiv="cache-control" content="no-cache"/>
		<meta http-equiv="pragma" content="no-cache"/>

		<link rel="shortcut icon" href="../../assets/img/fge.png"/>
		<link rel="stylesheet" href="assets/css/main.css?v=<?php echo time(); ?>"/>
		<link rel="stylesheet" href="assets/css/styles.css?v=<?php echo time(); ?>"/>
		
		<!--<link rel="stylesheet" href="../../css/styles.css">-->
		<link rel="stylesheet" href="../../css/styles.css">
		<link rel="stylesheet" href="../../css/dropdown-style.css">
		<link rel="stylesheet" href="../../node_modules/bootstrap/dist/css/bootstrap.min.css">

		<script src="../../node_modules/jquery/dist/jquery.min.js" ></script>
		<script src="../../node_modules/bootstrap/dist/js/bootstrap.bundle.min.js" ></script>
		<script src="../../node_modules/sweetalert2/dist/sweetalert2.all.min.js"></script>

		<script src="../../js/script.js?v=<?php echo time(); ?>"></script>
		<script src="js/script.js?v=<?php echo time(); ?>"></script>
		<script src="js/avp.js?v=<?php echo time(); ?>"></script>
	</head>
	<body class="is-preload">

		<div class="loader-div"></div>

		<div id="wrapper">
			<div id="main">
				<header id="navbar">
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
					<h1>EXPORTAR BASE DE DATOS HISTÓRICA</h1>
				</div>
				
				<div class="inner">
					<section>
						<form class="search-form" action="#">
							<div class="form-row">
								<div class="col-md-6 form-group">
									<label style="font-weight:bold">Mes y año: *</label>
									<input id="main-search-search-month" type="month" class="form-control" required="true">	
								</div>
							</div>
							
							<div class="form-buttons">
								<button type="button" class="btn btn-outline-primary rounded-button" onclick="executeAVP()">Ejecutar</button>
							</div>
						</form>
					</section>
				</div>
			</div>

			<div id="sidebar">
				<div class="inner">
					<nav id="menu">
						<header class="major">
							<h2><a id="text-logo">FGE</a>&nbsp;&nbsp;&nbsp;BASES NACIONALES</h2>
						</header>
						<ul>
							<li><i class="fa fa-circle" aria-hidden="true"></i>&nbsp;<a href="senap.php">SENAP</a></li>
							<li><i class="fa fa-circle" aria-hidden="true"></i>&nbsp;<a href="microdato.php">Microdato</a></li>
							<li class="selected"><i class="fa fa-circle" aria-hidden="true"></i>&nbsp;<a href="#">Exportar base de datos histórica</a></li>
							<li><i class="fa fa-circle" aria-hidden="true"></i>&nbsp;<a href="norma_tecnica.php">Norma técnica</a></li>
							<li><i class="fa fa-circle" aria-hidden="true"></i>&nbsp;<a href="censo_procu.php">Censo procuración de justicia</a></li>
							<li><i class="fa fa-circle" aria-hidden="true"></i>&nbsp;<a href="incidencia_sesesp.php">Incidencia delictiva SESESP</a></li>
							<li><i class="fa fa-circle" aria-hidden="true"></i>&nbsp;<a href="validacion_victimas.php">Validación de víctimas</a></li>
							<li><i class="fa fa-circle" aria-hidden="true"></i>&nbsp;<a href="producto_estadistico.php">Producto estadístico</a></li>
							<li><i class="fa fa-circle" aria-hidden="true"></i>&nbsp;<a href="sedena.php">Consulta SEDENA</a></li>
							<li><i class="fa fa-circle" aria-hidden="true"></i>&nbsp;<a href="sesnsp.php">Nueva norma técnica</a></li>
						</ul>
					</nav>
					
					<footer id="footer">
						<!--<p>Sistema de gestion de reportes v1-21.08.04</p>-->
					</footer>
				</div>
			</div>
		</div>
		
		<script src="assets/js/jquery.min.js"></script>
		<script src="assets/js/browser.min.js"></script>
		<script src="assets/js/breakpoints.min.js"></script>
		<script src="assets/js/util.js"></script>
		<script src="assets/js/main.js"></script>
	</body>
</html>