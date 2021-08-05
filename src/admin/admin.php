<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta http-equiv="X-UA-Compatible" content="ie=edge">

		<meta http-equiv="cache-control" content="no-cache" />
		<meta http-equiv="pragma" content="no-cache" />
        
		<link rel="shortcut icon" href="../../assets/img/fge.png"/>
		<link rel="stylesheet" href="../../css/styles.css">
		<link rel="stylesheet" href="../../node_modules/bootstrap/dist/css/bootstrap.min.css">
		
		<script src="../../node_modules/jquery/dist/jquery.min.js" ></script>
		<script src="../../node_modules/bootstrap/dist/js/bootstrap.bundle.min.js" ></script>
		<script src="../../node_modules/sweetalert2/dist/sweetalert2.all.min.js"></script>

		<script src="js/script.js"></script>

		<title>SGR</title>
	</head>
	<body>

        <h1>Admin frame</h1>

		<form class="search-form" action="#">	

			<div class="form-row">

				<!--<div class="col-md-4¿2 form-group">

					<label style="font-weight:bold">Año: *</label>

					<input type="text" class="form-control" id="search-year" maxlength="4" onkeypress="validateNumber(event);">	

				</div>-->

				<div class="col-md-4 form-group">

					<label style="font-weight:bold">Total o Parcial: *</label>

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

				<button type="button" class="btn btn-outline-success" style="height:38px; width: 100px;"  onclick="searchSenap(null)">Buscar</button>

				<button type="button" class="btn btn-outline-success" style="height:38px; width: 100px;"  onclick="testm(null)">test</button>

				<!--<button type="button" class="btn btn-outline-success" style="height:38px; width: 100px;"  onclick="tableToExcel()">excel</button>-->
			
			</div>

		</form>

		<div id="records-section"></div>

	</body>
</html>