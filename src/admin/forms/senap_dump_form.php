<form id="senap-dump-form" action="#">	

	<div class="form-row">
        
        <div class="col-md-6 form-group">

            <label style="font-weight:bold">Sección a volcar: *</label>

            <select id="senap-dump-option" class="form-control" required="true">									
                <option value =3 selected>Etapa de investigación</option>
                <option value =4>Actos de investigación</option>
                <option value =6>Aseguramientos</option>
                <option value =10>Determinaciones</option>
                <option value =11>Procesos</option>
                <option value =12>Mandamientos Judiciales</option>
                <option value =13>Investigación Complementaria</option>
                <option value =14>Medidas Cautelares</option>
                <option value =15>Etapa Intermedia</option>
                <option value =16>MASC</option>
                <option value =17>Sobreseimientos</option>
                <option value =18>Suspensión Condicional</option>
                <option value =19>Sentencias</option>
            </select>	

        </div>

        <div class="col-md-6 form-group">

            <label style="font-weight:bold">Mes y año: *</label>

            <input id="senap-dump-search-month" type="month" class="form-control" required="true">	

        </div>
        
        
    </div>


    <div class="form-buttons">		

        <button type="button" class="btn btn-outline-success" style="height:38px; width: 150px;"  onclick="checkSenapBeforeDump()">Volcar</button>

        <!--<button type="button" class="btn btn-outline-success" style="height:38px; width: 100px;"  onclick="testm(null)">test</button>-->

        <!--<button type="button" class="btn btn-outline-success" style="height:38px; width: 100px;"  onclick="tableToExcel()">excel</button>-->

    </div>

	<!--<hr>

	<h3>Información actual del sistema</h3>

	<div id="people-served-table-section"></div>

	<hr>
	

	<div class="form-buttons">		
		<button type="button" class="btn btn-primary" style="height:38px; float: left;"  onclick="loadSearchForm('agreements')">Busqueda <i class="fa fa-search" aria-hidden="true"></i></button>		
		<button type="button" class="btn btn-outline-dark" style="height:38px; width: 100px;"  onclick="resetSection('agreements')">Nuevo</button>	
		<button type="button" class="btn btn-outline-primary" style="height:38px; width: 100px;"  onclick="validateSection('agreements')">Guardar</button>	
 
	</div>-->

</form>