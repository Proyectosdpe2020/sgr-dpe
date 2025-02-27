$(document).ready(function(){

    checkSession({
        success: {
            function: null
        },
        failed: {
            function: redirectTo,
            attr: '../../index.html'
        },
        location: '../../service/check_session.php'
    });

    getCatalog({
        service_file: 'service/sedena/get_crimes.php'/*,
        on_service_success: {
            handle_data_catalog_array_field_key: attr.array_catalogs[field].handle_data_catalog_array_field_key,
            functions: [
                {
                    function: loadCatalogsByArray,
                    attr: {
                        array_catalogs: [
                            attr.array_catalogs[field]
                        ]
                    }
                }
            ]
        }*/
    });
});


function getCatalog(attr){

    if(attr.service_file != null){
        $.ajax({
            url: attr.service_file,
            dataType: "json",
            cache: false
        }).done(function(response){
    
            sedena_handle_data.crimes = response;

            /*console.log('load select before: ', {
                template_file: attr.template_file,
                element_attr: {
                    ...attr.element_attr,
                    elements: response
                }
            });*/




    
            loadSelect({
                template_file: 'templates/elements/multiselect.php',
                element_attr: {
                    element_id: 'crimes-multiselect',
                    elements: response,
                    element_placeholder: 'selecciona',
                    element_event_listener: ''
                }
            });

            setMultiselectActions({
                id: 'crimes-multiselect',
                name: 'crimes-multiselect',
                counter: 1,
                iterations: 50,
                delay: 500
            });
            
    
            console.log(response);
    
        });
    }
}

function loadSelect(attr){
	$.ajax({
		url: attr.template_file,
		type:'POST',
		dataType: "html",
		data: attr.element_attr,
		cache:false
	}).done(function(response){
		$('#'+attr.element_attr.element_id+'-section').html(response);
	});
}





function showLoading(ind){
    if(ind)
        $(".loader-div").addClass("loader");
    else
        $(".loader-div").removeClass("loader");
}

function getSEDENA(){


    console.log('entre');


    let query = {
        "1": 'service/sedena/get_query_1.php',
        "2": 'service/sedena/get_query_2.php'
    };

    let nom_op = {
        "1": 'Delitos',
        "2": 'Víctimas'
    };

    //let initial_date = document.getElementById('search-initial-date').value;
    //let finish_date = document.getElementById('search-finish-date').value;
    let op = document.getElementById('main-search-option').value;

    let initial_date = new Date(document.getElementById('search-initial-date').value);

    let finish_date = new Date(document.getElementById('search-finish-date').value);

    let initial_date_2 = new Date(document.getElementById('search-initial-date-2').value);

    let finish_date_2 = new Date(document.getElementById('search-finish-date-2').value);

    if(sedena_handle_data.current_multiselect['crimes-multiselect'].length != 0 && document.getElementById('search-initial-date').value != '' && document.getElementById('search-finish-date').value != ''
&& document.getElementById('search-initial-date-2').value != '' && document.getElementById('search-finish-date-2').value != ''){


    console.log('valido form');

    $('#main-frame-sedena-section').html('<h1>Cargando...</h1>');
    $('#sedena-table-title').html('');

        $.ajax({
            url: query[op],
            type: 'POST',
            dataType: "json",
            data: {
                initial_date: ''+initial_date.getDate()+'-'+(initial_date.getMonth()+1)+'-'+initial_date.getFullYear()+'',
                finish_date: ''+finish_date.getDate()+'-'+(finish_date.getMonth()+1)+'-'+finish_date.getFullYear()+'',
                initial_date_2: ''+initial_date_2.getDate()+'-'+(initial_date_2.getMonth()+1)+'-'+initial_date_2.getFullYear()+'',
                finish_date_2: ''+finish_date_2.getDate()+'-'+(finish_date_2.getMonth()+1)+'-'+finish_date_2.getFullYear()+'',
                crimes: sedena_handle_data.current_multiselect['crimes-multiselect']
            },
            cache: false
        }).done(function(response){
    
            sedena_handle_data.crimes = response;


            
            drawDefaultTable({
                data: response,
                element_id: 'main-frame-sedena-section',
                title_element_id: 'sedena-table-title',
                title: nom_op[op]
            });
    
            console.log(response);
    
        });
    }
}


function drawDefaultTable(attr){

    console.log('entre a default data table: ', attr);

    if(attr.data != null){
        $.ajax({
            url: 'templates/tables/default_table.php',
            type: 'POST',
            dataType: "html",
            data: {
                data: JSON.stringify(attr.data)/*,
                table_id: attr.table_id,
                
                section_title: attr.procedure_op*/
            },
            cache: false
        }).done(function(response){
            $('#'+attr.title_element_id).html('<h1>'+attr.title+'</h1>');
            $('#'+attr.element_id).html(response);


            /*
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

            */
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