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

				<div class="col-md-6 form-group">

					<label style="font-weight:bold">Mes: *</label>

					<input type="month" class="form-control" id="search-month">	

				</div>
				
				
			</div>
			

			<div class="form-buttons">		

				<button type="button" class="btn btn-outline-success" style="height:38px; width: 100px;"  onclick="searchSenap(null)">Buscar</button>

				<!--<button type="button" class="btn btn-outline-success" style="height:38px; width: 100px;"  onclick="tableToExcel()">excel</button>-->
			
			</div>

		</form>

		<div id="records-section"></div>

	</body>
</html>