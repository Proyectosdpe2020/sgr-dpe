<form id="senap-reports-form" action="#">	

	<div class="form-row">

        <div class="col-md-6 form-group">

            <label style="font-weight:bold">Sección a buscar: *</label>

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
    
    <!--</div>

	<hr>

	<h3>Información actual del sistema</h3>

	<div id="people-served-table-section"></div>

	<hr>
	

	<div class="form-buttons">		
		<button type="button" class="btn btn-primary" style="height:38px; float: left;"  onclick="loadSearchForm('agreements')">Busqueda <i class="fa fa-search" aria-hidden="true"></i></button>		
		<button type="button" class="btn btn-outline-dark" style="height:38px; width: 100px;"  onclick="resetSection('agreements')">Nuevo</button>	
		<button type="button" class="btn btn-outline-primary" style="height:38px; width: 100px;"  onclick="validateSection('agreements')">Guardar</button>	
 
	</div>-->

</form>