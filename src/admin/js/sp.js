$(document).ready(function(){ 
    //$( "#main-search-search-year" ).datepicker({dateFormat: 'yy'});
});

function searchSPData(){
    if(document.getElementById('main-search-option') && document.getElementById('subsection-search-option') && document.getElementById('module-search-op')){

        let main_search_op = document.getElementById('main-search-option').value;
        let subsec_search_op = document.getElementById('subsection-search-option').value;
        let module_search_op = document.getElementById('module-search-op').value;

        $('#main-frame-sense-section').html('');

        if(main_search_op != '' && subsec_search_op != '' && module_search_op != ''){
            getProcedureOP({
                search_op: main_search_op,
                subsec_search_op: subsec_search_op,
                module_search_op: module_search_op
            });
        }
        else{
            Swal.fire('Campos faltantes', 'Tiene que completar alguno de los campos para completar la busqueda', 'warning');
        }

    }
}

function getProcedureOP(attr){
    $.ajax({
        url: 'service/get_sp_procedure_op.php',
        type: 'POST',
        dataType: 'JSON',
        data: {
            search_op: attr.search_op,
            subsec_search_op: attr.subsec_search_op,
            module_search_op: attr.module_search_op
        },
        cache: false
    }).done(function(response){

        console.log(response);
        
        executeSPService({
            array_op: response,
            procedure: attr.module_search_op
        });

    }).fail(function(){
        console.log('fallo');

        Swal.fire('error', 'Ha ocurrido un error inesperado, favor de contactar a DPE', 'error');

    });
}


function executeSPService(attr){

    let array_op = attr.array_op;

    if(checkValues({
        elements: {
            search_month: {
                element_id: 'main-search-year',
                element_type: 'date'
            }
        }
    })){
        setMultipleFramesInSection({
            main_frame: 'main-frame-sense-section',
            array_frames: array_op,
            on_success: {
                functions: [
                    {
                        function: getSPService,
                        attr: {
                            array_op: array_op,
                            array_op_index: 0,
                            procedure: attr.procedure
                        },
                        response: false
                    }
                ]
            }
        });    
    }
    else{
        Swal.fire('Campos faltantes', 'Tiene que completar alguno de los campos para completar la busqueda', 'warning');
    }
}

function getSPService(attr){

    let array_op = attr.array_op;
    let array_op_index = attr.array_op_index;

    let search_date = document.getElementById('main-search-year').value;

    if(array_op.length > 0 && array_op.length > array_op_index){

        get_retransmission_service({
            url_service_file: 'service/get_sense_procedure_data.php',
            parameters: {
                year: search_date,
                procedure_op: array_op[array_op_index],
                procedure: attr.procedure
            },
            show_loading: false,
            on_success: {
                functions: [
                    {
                        function: drawDefaultDataTable,
                        attr: {
                            element_id: array_op[array_op_index]+'-section-div',
                            table_id: array_op[array_op_index]+'-sp-table',
                            data: null,
                            procedure_op: array_op[array_op_index]
                        },
                        response: true
                    },
                    {
                        function: getSPService,
                        attr: {
                            array_op: array_op,
                            array_op_index: array_op_index+1,
                            procedure: attr.procedure
                        },
                        response: false
                    }
                ]
            }
        });
    }
}


function copiarAlPortapapeles(id_elemento) {
    var aux = document.createElement("input");
    aux.setAttribute("value", document.getElementById(id_elemento).innerHTML);
    document.body.appendChild(aux);
    aux.select();
    document.execCommand("copy");
    document.body.removeChild(aux);
  }

  function copyToClipBoard() {

    var content = 'title1\ttitle2\n\text1\ttext2\n\text3\ttext4';
    
    content.select();
    document.execCommand('copy');

    alert("Copied!");
}

function changeSPOptions(){

    let main_search_op = document.getElementById('main-search-option').value;
    let module_search_op = document.getElementById('module-search-op').value;

    if(main_search_op != ''){

        getSPOptions({
            main_op: main_search_op,
            module_search_op: module_search_op
        });
    }
    else{
        document.getElementById('subsection-search-option').selectedIndex = 0;
        document.getElementById('subsection-search-option').disabled = true;
    }

    

    /*switch(main_search_op){

        case '2':
            document.getElementById('subsection-search-option').disabled = false;

            getSPOptions({
                main_op: main_search_op,
                module_search_op: module_search_op
            });
            break;
        case '3':
            document.getElementById('subsection-search-option').disabled = false;

            getSPOptions({
                main_op: main_search_op,
                module_search_op: module_search_op
            });
        case '4':
            document.getElementById('subsection-search-option').disabled = false;

            getSPOptions({
                main_op: main_search_op,
                module_search_op: module_search_op
            });
        case '5':
            document.getElementById('subsection-search-option').disabled = false;

            getSPOptions({
                main_op: main_search_op,
                module_search_op: module_search_op
            });
            break;
        default:
            document.getElementById('subsection-search-option').disabled = true;
    }*/
}

function getSPOptions(attr){

    $.ajax({
        url: 'service/get_sp_op_by_procedure_op.php',
        type: 'POST',
        dataType: 'JSON',
        data: {
            main_op: attr.main_op,
            module_search_op: attr.module_search_op
        },
        cache: false
    }).done(function(response){

        console.log(response);
        
        loadSelect({
            template_file: 'templates/elements/select.php',
            element_attr: {
                element_id: 'subsection-search-option',
                element_placeholder: ' -- Selecciona -- ',
                element_event_listener: '',
                elements: response
            },
            element_id_section: 'subsection-op-section'
        });

    }).fail(function(){
        console.log('fallo');

        Swal.fire('error', 'Ha ocurrido un error inesperado, favor de contactar a DPE', 'error');

    });

}

function changeModuleOptions(){
    document.getElementById('main-search-option').selectedIndex = 0;
    document.getElementById('subsection-search-option').selectedIndex = 0;
    document.getElementById('subsection-search-option').disabled = true;

    let module_search_op = document.getElementById('module-search-op').value;

    if(module_search_op != ''){

        document.getElementById('main-search-option').disabled = false;

        getSPOptionsByModule({
            module_search_op: module_search_op
        });
    }
    else{
        document.getElementById('module-search-op').selectedIndex = 0;
        document.getElementById('main-search-option').disabled = true;
    }

}

function getSPOptionsByModule(attr){

    $.ajax({
        url: 'service/get_sp_op_by_module_op.php',
        type: 'POST',
        dataType: 'JSON',
        data: {
            module_search_op: attr.module_search_op
        },
        cache: false
    }).done(function(response){

        console.log(response);
        
        loadSelect({
            template_file: 'templates/elements/select.php',
            element_attr: {
                element_id: 'main-search-option',
                element_placeholder: ' -- Selecciona -- ',
                element_event_listener: 'onchange="changeSPOptions()"',
                elements: response
            },
            element_id_section: 'main-search-section'
        });

    }).fail(function(){
        console.log('fallo');

        Swal.fire('error', 'Ha ocurrido un error inesperado, favor de contactar a DPE', 'error');

    });

}