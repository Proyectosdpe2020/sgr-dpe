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

function searchSenap(attr){

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
                procedure_op: procedure_op
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
                        function: createExcelReport,
                        attr: {
                            data: null,
                            url_service_file: 'templates/excel/report_test_csv.php',
                            file_name: csv_names[procedure_op]+'.csv'
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
            $a.attr("download", attr.file_name);
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