function validateNumber(evt) {
    
	var theEvent = evt || window.event;
  
	if(theEvent.type === 'paste'){
		key = event.clipboardData.getData('text/plain');
    } 
    else{
		var key = theEvent.keyCode || theEvent.which;
		key = String.fromCharCode(key);
    }
    
    var regex = /[0-9]|\./;
    
	if( !regex.test(key) ){
	    theEvent.returnValue = false;
    if(theEvent.preventDefault) 
        theEvent.preventDefault();
	}
}

function validateForm(section){

    let attr = {};
    let validated = false;

    checkCompletedFields({
        fields: [
            {
                id: 'search-month'
            }
        ]
    });

    if(document.getElementById('search-month')){
        if(document.getElementById('search-month').value == ''){
            Swal.fire('Campos faltantes', 'Tiene que completar alguno de los campos para completar la busqueda', 'warning');
        }
        else{
            
            date = document.getElementById('search-month').value;
            d = new Date(date+'-01');
            d.setHours(d.getHours()+6); 
            attr = {
                month: (d.getMonth()+1),
                year: d.getFullYear()
            }
            validated = true;
        }
    }

    if(validated){
        console.log(sections[section].search_file, attr);
        $.ajax({
            url:'service/'+sections[section].search_file,
            type:'POST',
            dataType: "json",
            data: attr,
            cache:false
        }).done(function(response){
            console.log(response);
            test = response;
            drawRecordsTable({
                data: response,
                file: 'templates/tables/'+section+'_table.php',
                element_id: 'records-section'
            });
        });
    }
    else{
        //Swal.fire('Error', 'Ha ocurrido un error, vuelva a intentarlo', 'error');
    }
}

function checkCompletedFields(attr){
    validated = true;

    for(field in attr.fields){
        if(document.getElementById(attr.fields[field].id)){
            if(document.getElementById(attr.fields[field].id).value == ''){
                Swal.fire('Campos faltantes', 'Tiene que completar alguno de los campos para completar la busqueda', 'warning');
            }
            else{
                
                date = document.getElementById('search-month').value;
                d = new Date(date+'-01');
                d.setHours(d.getHours()+6); 
                attr = {
                    month: (d.getMonth()+1),
                    year: d.getFullYear()
                }
                validated = true;
            }
        }
        else{
            validated = false;
            break;
        }
    }
}

function testm(){

    date = document.getElementById('main-search-search-month').value;
    date_format = new Date(date+'-01');
    date_format.setHours(date_format.getHours()+6); 
    console.log(date_format.getMonth()+1);

    console.log(date_format.getFullYear());
}

function searchSenap(file_type){

    attr = null;

    csv_names = {
        1: 'SENAP_Carga_NoticiaCriminal',
        2: 'SENAP_Carga_CarpetaInvestigacion',
        3: 'SENAP_Carga_EtapaInvestigacion',
        4: 'SENAP_Carga_ActosInvestigacion',
        5: 'SENAP_Carga_Delitos',
        6: 'SENAP_Carga_Aseguramientos',
        7: 'SENAP_Carga_VictimasDelito',
        8: 'SENAP_Carga_ImputadoDelito',
        9: 'SENAP_Carga_VictimaImputado',
        10: 'SENAP_Carga_Determinacion',
        11: 'SENAP_Carga_Proceso',
        12: 'SENAP_Carga_MandamientosJudiciales',
        13: 'SENAP_Carga_InvestigacionComplementaria',
        14: 'SENAP_Carga_MedidasCautelares',
        15: 'SENAP_Carga_EtapaIntermedia',
        16: 'SENAP_Carga_MASC',
        17: 'SENAP_Carga_Sobreseimiento',
        18: 'SENAP_Carga_SuspensionCondicional',
        19: 'SENAP_Carga_Sentencia'
    }

    date = document.getElementById('main-search-search-month').value;
    procedure_op = document.getElementById('main-search-option').value;

    date_format = null;
    completed_form = false;


    if(date == "" || procedure_op == ""){
        completed_form = false;
    }
    else{
        date_format = new Date(date+'-01');
        date_format.setHours(date_format.getHours()+6); 
        completed_form = true;
    }

    if(attr == null && completed_form){
        attr = {
            url_service_file: 'service/get_senap_procedure_data.php',
            parameters: {
                month: date_format.getMonth()+1,
                year: date_format.getFullYear(),
                procedure_op: procedure_op,
                procedure: 1
            },
            on_success: {
                functions: [
                    /*{
                        function: drawTable,
                        attr: {
                            data: null,
                            url_service_file: 'templates/tables/default_table.php',
                            element_id: 'records-section'
                        },
                        response: true
                    },*/
                    {
                        function: createExcelReportCopy,
                        attr: {
                            data: null,
                            url_service_file: file_type == 'csv'? 'templates/excel/report_test_csv.php' : 'templates/excel/report_test.php',
                            file_name: file_type == 'csv'? csv_names[procedure_op]+'.csv' : csv_names[procedure_op]+'.xlsx'
                        },
                        response: true
                    }
                ]
            }
        }

        if(attr.url_service_file){
            console.log('showl');
            showLoading(true);
    
            $.ajax({
                url: attr.url_service_file,
                type: 'POST',
                dataType: 'JSON',
                data: attr.parameters,
                cache: false
            }).done(function(response){
                console.log(response);
                //test = response;
    
                for(os_function in attr.on_success.functions){
                    if(attr.on_success.functions[os_function].response){
                        attr.on_success.functions[os_function].attr.data = response;
                    }
                    attr.on_success.functions[os_function].function(attr.on_success.functions[os_function].attr);
                }
    
            });
        }
    }
    else{
        Swal.fire('Campos faltantes', 'Tiene que completar alguno de los campos para completar la busqueda', 'warning');
    }
    
    
}

function drawTable(attr){

    if(attr.data != null){
        $.ajax({
            url: attr.url_service_file,
            type: 'POST',
            dataType: "html",
            data: {
                data: JSON.stringify(attr.data)
            },
            cache: false
        }).done(function(response){
            $('#'+attr.element_id).html(response);
        });
    }
    else{
        /*loadDashboardAlert({
            template_file: 'templates/elements/dashboard_alert.php',
            element_id: attr.element_id,
            element_attr: {
                attr: {
                    type: 'secondary',
                    message: 'No hay registros!'
                }
            } 
        });*/
    }
}

function downloadExcelReport() {

	var month = document.getElementById('mesPuestaSelected').value;
	var year = document.getElementById('anioCmasc').value;
	
	var data = [];

	generalDataFiscalias(month, year, data);

}

function createExcelReport(attr) {

    if(attr.data != null){

        console.log('createeee', attr);
        $.ajax({
            type: "POST",
            dataType: 'json',
            url: attr.url_service_file,
            data: {
                data: JSON.stringify(attr.data)
                //data: attr.data
            },
        }).done(function(data){
            console.log('good response');
            var $a = $("<a>");
            $a.attr("href",data.file);
            $("body").append($a);
            $a.attr("download", attr.file_name+'.xlsx');
            $a[0].click();
            $a.remove();
            showLoading(false);
        });
    }

}

function createExcelReportCopy(attr) {

    if(attr.data != null){

        console.log('createeee', attr);
        $.ajax({
            type: "POST",
            dataType: 'json',
            url: attr.url_service_file,
            data: {
                data: JSON.stringify(attr.data)
                //data: attr.data
            },
        }).done(function(data){
            console.log('good response');
            var $a = $("<a>");
            $a.attr("href",data.file);
            $("body").append($a);
            $a.attr("download", attr.file_name);
            $a[0].click();
            $a.remove();
            showLoading(false);
        });
    }

}

function tableToExcel(){
    var uri = 'data:application/vnd.ms-excel;base64,'
      , template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><meta cha' + 'rset="UTF-8"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>{worksheet}</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head><body><table>{table}</table></body></html>'
      , base64 = function(s) { return window.btoa(unescape(encodeURIComponent(s))) }
      , format = function(s, c) { return s.replace(/{(\w+)}/g, function(m, p) { return c[p]; }) }
    
      
  
    table = document.getElementsByClassName('data-table')[0];
    var ctx = {worksheet: name || 'Worksheet', table: table.innerHTML}
    window.location.href = uri + base64(format(template, ctx));
    
}


/*function showLoading(ind){
    if(ind)
        $(".loader-div").addClass("loader");
    else
        $(".loader-div").removeClass("loader");
}*/


function checkValues(attr){

    let validate = true;

    if(checkExistantElements(attr)){

        for(element in attr.elements){

            switch(attr.elements[element].element_type){
                case 'date':
                    if(document.getElementById(attr.elements[element].element_id).value == ''){
                        validate = false;
                    }
                    break;
                case 'text':
                    if(document.getElementById(attr.elements[element].element_id).value == ''){
                        validate = false;
                    }
                    break;
                default:
                    break;
            }

            if(!document.getElementById(attr.elements[element].element_id)){
                validate = false;
                break;
            }
        }
    }
    else{
        validate = false;
    }

    return validate;
}

function checkExistantElements(attr){

    let validate = true;

    for(element in attr.elements){
        if(!document.getElementById(attr.elements[element].element_id)){
            validate = false;
            break;
        }
    }

    return validate;
}

function getLocalDateFormat(date){
    date_format = new Date(date+'-01');
    date_format.setHours(date_format.getHours()+6);
    return date_format;
}

function get_retransmission_service(attr){

    if(!attr.hasOwnProperty('initial_interation')){
        attr = {
            ...attr,
            initial_interation: 1,
            finish_interation: 10
        }
    }

    //showLoading(true);

    if(attr.initial_interation < attr.finish_interation){

        if(attr.show_loading){
            showLoading(true);
        }

        $.ajax({
            url: attr.url_service_file,
            type: 'POST',
            dataType: 'JSON',
            data: attr.parameters,
            cache: false
        }).done(function(response){
            console.log(response);
            //test = response;
    
            console.log('no se q hace');
    
            for(os_function in attr.on_success.functions){
                if(attr.on_success.functions[os_function].response){
                    attr.on_success.functions[os_function].attr.data = response;
                }
                attr.on_success.functions[os_function].function(attr.on_success.functions[os_function].attr);
            }
    
        }).fail(function(){
            console.log('fallo');
    
            attr.initial_interation++;
    
            console.log('attr +iteration', attr);

            get_retransmission_service(attr);
    
        });
    }

    else{

        if(attr.show_loading){
            showLoading(false);
        }

        Swal.fire('error', 'Ha ocurrido un error inesperado, favor de contactar a DPE', 'error');
    }
}

function setMultipleFramesInSection(attr){

    let array_frames = attr.array_frames;

    if(document.getElementById(attr.main_frame)){

        for(frame in array_frames){

            let div = document.createElement('div');

            div.id = array_frames[frame]+'-section-div';

            document.getElementById(attr.main_frame).appendChild(div);

            setStaticLoader({
                section_id: array_frames[frame]+'-section-div',
                class: 'static-loader'
            });

            $("#"+array_frames[frame]+'-section-div').addClass("sp-frame-record");
        }

        for(os_function in attr.on_success.functions){
            if(attr.on_success.functions[os_function].response){
                attr.on_success.functions[os_function].attr.data = response;
            }
            attr.on_success.functions[os_function].function(attr.on_success.functions[os_function].attr);
        }

        

    }
}

function drawDefaultDataTable(attr){

    console.log('entre a default data table: ', attr);

    if(attr.data != null){
        $.ajax({
            url: 'templates/tables/default_data_table.php',
            type: 'POST',
            dataType: "html",
            data: {
                table_id: attr.table_id,
                data: JSON.stringify(attr.data),
                section_title: attr.procedure_op
            },
            cache: false
        }).done(function(response){
            $('#'+attr.element_id).html(response);

            console.log('trying to set: ', attr.table_id);

            $('#'+attr.table_id).DataTable({
                language: {
                    "decimal": "",
                    "emptyTable": "No hay información",
                    "info": "_START_ - _END_ / _TOTAL_ Registros",
                    "infoEmpty": "Sin registros",
                    "infoFiltered": "(Filtrado de _MAX_ registros)",
                    "infoPostFix": "",
                    "thousands": ",",
                    "lengthMenu": "Muestra de _MENU_ registros",
                    "loadingRecords": "Cargando...",
                    "processing": "Procesando...",
                    "search": "Buscar:",
                    "zeroRecords": "Sin resultados encontrados",
                    "paginate": {
                        "first": "Primero",
                        "last": "Ultimo",
                        "next": "Siguiente",
                        "previous": "Anterior"
                    }
                },
                paging: false,
                ordering:  false
            });

            showLoading(false);
        });
    }
    else{
        /*loadDashboardAlert({
            template_file: 'templates/elements/dashboard_alert.php',
            element_id: attr.element_id,
            element_attr: {
                attr: {
                    type: 'secondary',
                    message: 'No hay registros!'
                }
            } 
        });*/
    }
}

function setStaticLoader(attr){
    $('#'+attr.section_id).html('<div class="'+attr.class+'">Cargando datos... </div>');
}

var handle_data = {
    tables: 'title1\ttitle2\n\text1\ttext2\n\text3\ttext4'
}

function loadSelect(attr){
	$.ajax({
		url: attr.template_file,
		type:'POST',
		dataType: "html",
		data: attr.element_attr,
		cache:false
	}).done(function(response){
		$('#'+attr.element_id_section).html(response);
	});
}

function changeSenapForm(section){

    console.log('changing section form');
    
    switch(section){

        case 'dump':
            changeMenu({
                class: 'senap-menu-button',
                current_section: section
            });
            loadForm({
                section: section,
                form_file_location: 'forms/senap_dump_form.php',
                content_div_id: 'senap-form-section'
            });
            break;
        case 'reports':
            changeMenu({
                class: 'senap-menu-button',
                current_section: section
            });
            loadForm({
                section: section,
                form_file_location: 'forms/senap_reports_form.php',
                content_div_id: 'senap-form-section'
            });
            break;
    }
}

function loadForm(attr){
    
    console.log('loading form...', attr);

    $.ajax({
        url: attr.form_file_location,
        type: 'POST',
        contentType: false,
        processData: false,
        cache: false
    }).done(function(response){

        $("#"+attr.content_div_id).html(response);
        
    });
}

function changeMenu(attr){

    console.log('changing menu');

    if(document.getElementsByClassName(attr.class)[0]){

        let elements = document.getElementsByClassName(attr.class);

        for(var i = 0; i < elements.length; i++){

            document.getElementsByClassName(attr.class)[i].disabled = false;

        }

        document.getElementById(attr.current_section).disabled = true;

    }

    console.log('finishing menu');
}

function checkSenapBeforeDump(){

    showLoading(true);
    
    console.log('checking senap ...');

    let senap_dump_op = document.getElementById('senap-dump-option').value;

    let current_senap_attr = getSenapAttr({
        index: senap_dump_op
    });

    let month_year = null;

    let start_dump = false;

    let parameters = {};

    if(document.getElementById('senap-dump-search-month')){

        console.log('checo si existe el search month');

        if(document.getElementById('senap-dump-search-month').value == 0){

            console.log('checo si hay algun valor');

            Swal.fire('Campos faltantes', 'Tiene que completar alguno de los campos para completar la busqueda', 'warning');
        }
        else{

            console.log('si hay valor');

            month_year = convertDateFormat({
                date: document.getElementById('senap-dump-search-month').value,
                convert_to: 1
            });

            parameters = {
                month: month_year.month,
                year: month_year.year
            }

            start_dump = true;

            console.log('converti el valor de la fecha: ',month_year);
        }
    }

    console.log('voy a mandar: ',parameters);

    console.log('file location? ', current_senap_attr);

    console.log('start_dump? ', start_dump);


    if(start_dump){

        console.log('entre a buscar ');

        $.ajax({
            url: current_senap_attr.search_data_file,
            type: 'POST',
            dataType: 'JSON',
            data: parameters,
            cache: false
        }).done(function(response){
    
            console.log(response);
    
            if(response.state == 'success'){

                showLoading(false);
                Swal.fire('Datos previamente volcados', 'Este apartado ya contiene datos en su tabla respectiva', 'warning');
            }
            else if(response.state == 'not_found'){
    
                if(senap_dump_op >= 12){
                    checkExistantSenapSection({
                        parameters: parameters,
                        index_section: 11
                    });
                }
                else{
                    dumpSenap();
                }
            }
            else{

                showLoading(false);
                Swal.fire('Error', 'Ha ocurrido un error, vuelva a intentarlo', 'error');
            }
        });
    }
    else{
        console.log('no ?????');
    }
}


function dumpSenap(){

    console.log('Dumping senap ...');

    let current_senap_attr = {};

    let month_year = null;

    let senap_dump_op = document.getElementById('senap-dump-option').value;

    if(document.getElementById('senap-dump-search-month')){

        console.log('checo si existe el search month');

        if(document.getElementById('senap-dump-search-month').value == ''){

            console.log('checo si hay algun valor');

            Swal.fire('Campos faltantes', 'Tiene que completar alguno de los campos para completar la busqueda', 'warning');
        }
        else{

            console.log('si hay valor');

            current_senap_attr = getSenapAttr({
                index: senap_dump_op
            });

            month_year = convertDateFormat({
                date: document.getElementById('senap-dump-search-month').value,
                convert_to: 1
            });

            console.log('converti el valor de la fecha: ',month_year);
        }
    }

    let parameters = {
        month: month_year.month,
        year: month_year.year
    }

    console.log('voy a mandar: ',parameters);

    console.log('file location? ', current_senap_attr);

    if(current_senap_attr != {}){
        $.ajax({
            url: current_senap_attr.dump_file,
            type: 'POST',
            dataType: 'JSON',
            data: parameters,
            cache: false
        }).done(function(response){
    
            console.log(response);
    
            showLoading(false);
    
            Swal.fire('¡Exito!', 'Datos volcados correctamente', 'success');
        });
    }
    else{
        showLoading(false);
        Swal.fire('Error', 'Ha ocurrido un error, vuelva a intentarlo', 'error');
    }

    
}

function checkExistantSenapSection(attr){
    
    console.log('checking senap especific section ...');

    let current_senap_attr = getSenapAttr({
        index: attr.index_section
    });

    console.log('voy a mandar: ',attr.parameters);

    console.log('file location? ', current_senap_attr);

    $.ajax({
        url: current_senap_attr.search_data_file,
        type: 'POST',
        dataType: 'JSON',
        data: attr.parameters,
        cache: false
    }).done(function(response){

        console.log(response);

        if(response.state == 'success'){
            dumpSenap();
        }
        else if(response.state == 'not_found'){

            showLoading(false);
            Swal.fire('Datos de la tabla procesos faltantes', 'Se necesita tener los datos volcados de procesos para poder volcar esta seccion', 'warning');
        }
        else{

            showLoading(false);
            Swal.fire('Error', 'Ha ocurrido un error, vuelva a intentarlo', 'error');
        }
    });
}

function convertDateFormat(attr){

    let converted_date = null;
    
    switch(attr.convert_to){
        
        case 1: 

            d = new Date(attr.date+'-01');
            d.setHours(d.getHours()+6); 
            converted_date = {
                month: (d.getMonth()+1),
                year: d.getFullYear()
            }
        break;
        default:
    }

    return converted_date;
}

function getSenapAttr(attr){

    console.log('entre a get senap attr');

    let senap_json = senapJSON();
    let dump_file = null;

    let senap_attr = {};

    for(element in senap_json){

        console.log('recorriendo ciclo ...', senap_json[element].index);

        if(senap_json[element].index == attr.index){

            senap_attr = senap_json[element];
            break;
        }
    }

    console.log('se supone q acabe el ciclo');

    if(senap_attr != {}){

        return senap_attr;
    }
    else{

        return senap_attr;
    }
}

function senapJSON(){

    let senap = {
        SENAP_Carga_NoticiaCriminal: {
            index: 1,
            dump_file: null,
            search_data_file: null
        },
        SENAP_Carga_CarpetaInvestigacion: {
            index: 2,
            dump_file: null,
            search_data_file: null
        },
        SENAP_Carga_EtapaInvestigacion: {
            index: 3,
            dump_file: 'service/senap/dump_3_etapa_investigacion.php',
            search_data_file: 'service/senap/search_3_etapa_investigacion.php'
        },
        SENAP_Carga_ActosInvestigacion: {
            index: 4,
            dump_file: 'service/senap/dump_4_actos_investigacion.php',
            search_data_file: 'service/senap/search_4_actos_investigacion.php'
        },
        SENAP_Carga_Delitos: {
            index: 5,
            dump_file: null,
            search_data_file: null
        },
        SENAP_Carga_Aseguramientos: {
            index: 6,
            dump_file: 'service/senap/dump_6_aseguramientos.php',
            search_data_file: 'service/senap/search_6_aseguramientos.php'
        },
        SENAP_Carga_VictimasDelito: {
            index: 7,
            dump_file: null,
            search_data_file: null
        },
        SENAP_Carga_ImputadoDelito: {
            index: 8,
            dump_file: null,
            search_data_file: null
        },
        SENAP_Carga_VictimaImputado: {
            index: 9,
            dump_file: null,
            search_data_file: null
        },
        SENAP_Carga_Determinacion: {
            index: 10,
            dump_file: 'service/senap/dump_10_determinaciones.php',
            search_data_file: 'service/senap/search_10_determinaciones.php'
        },
        SENAP_Carga_Proceso: {
            index: 11,
            dump_file: 'service/senap/dump_11_procesos.php',
            search_data_file: 'service/senap/search_11_procesos.php'
        },
        SENAP_Carga_MandamientosJudiciales: {
            index: 12,
            dump_file: 'service/senap/dump_12_mandamientos_judiciales.php',
            search_data_file: 'service/senap/search_12_mandamientos_judiciales.php'
        },
        SENAP_Carga_InvestigacionComplementaria: {
            index: 13,
            dump_file: 'service/senap/dump_13_investigacion_complementaria.php',
            search_data_file: 'service/senap/search_13_investigacion_complementaria.php'
        },
        SENAP_Carga_MedidasCautelares: {
            index: 14,
            dump_file: 'service/senap/dump_14_medidas_cautelares.php',
            search_data_file: 'service/senap/search_14_medidas_cautelares.php'
        },
        SENAP_Carga_EtapaIntermedia: {
            index: 15,
            dump_file: 'service/senap/dump_15_etapa_intermedia.php',
            search_data_file: 'service/senap/search_15_etapa_intermedia.php'
        },
        SENAP_Carga_MASC: {
            index: 16,
            dump_file: 'service/senap/dump_16_masc.php',
            search_data_file: 'service/senap/search_16_masc.php'
        },
        SENAP_Carga_Sobreseimiento: {
            index: 17,
            dump_file: 'service/senap/dump_17_sobreseimientos.php',
            search_data_file: 'service/senap/search_17_sobreseimientos.php'
        },
        SENAP_Carga_SuspensionCondicional: {
            index: 18,
            dump_file: 'service/senap/dump_18_suspension_condicional.php',
            search_data_file: 'service/senap/search_18_suspension_condicional.php'
        },
        SENAP_Carga_Sentencia: {
            index: 19,
            dump_file: 'service/senap/dump_19_sentencias.php',
            search_data_file: 'service/senap/search_19_sentencias.php'
        }
    }

    return senap;
}

function showLoading(ind){
    if(ind)
        $(".loader-div").addClass("loader");
    else
        $(".loader-div").removeClass("loader");
}