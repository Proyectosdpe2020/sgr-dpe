//const { data } = require("jquery");

$(document).ready(function(){ 
    //$( "#main-search-search-year" ).datepicker({dateFormat: 'yy'});
});

function getVictims(attr){

    if(attr.section != undefined){

        attr = attr.btn_id != undefined ? attr : {
            ...attr,
            btn_id: 'search-btn'
        }

        attr = attr.table_placement_element_id != undefined ? attr : {
            ...attr,
            table_placement_element_id: 'records-section'
        }

        if(victim_handle_data.table_has_changed){

            Swal.fire({
                title: '¿Estas seguro?',
                text: 'Los cambios no guardados se perderán si haces una nueva busqueda',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Si',
                cancelButtonText: 'No'
            }).then((result) => {
                if(result.value){
                    resetVictims();
                    getPrevFormElements(attr);
                }
            });
        }
        else{
            getPrevFormElements(attr);
        }
    }
}

function getPrevFormElements(attr){

    console.log('get prev form eleme: ', attr);

    if(attr.section != undefined && attr.btn_id != undefined && attr.table_placement_element_id != undefined){

        disableBtn({
            btn_id: attr.btn_id,
            disable: true
        });

        

        attr = attr.search_form_elements != undefined ? attr : {
            ...attr,
            search_form_elements: getSearchFormElementsBySection({
                section: attr.section
            })
        }
    
        if(validateElementsByIdContent({
            elements: attr.search_form_elements
        })){

            clearDiv({
                element_id: attr.table_placement_element_id
            });

            showLoadingTable(true);

            console.log('attr: ', attr);

            getRecords({
                ...attr,
                table_placement_element_id: attr.table_placement_element_id,
                is_datatable: true,
                functions_on_success: [
                    {
                        function: drawRecordsTable
                    }
                ]
            });
        }
        else{
            console.log('error');

            console.log('missing 1: ', attr);
            displaySwalMessage({message_op: 'missing_data'});

            disableBtn({
                btn_id: attr.btn_id,
                disable: false
            });
        }
    }
}

function getRecords(attr){

    console.log('get rec: ', attr);

    if(attr != null){

        //showLoading(true);

        attr = !attr.hasOwnProperty('initial_interation') ? {
            ...attr,
            initial_interation: 1,
            finish_interation: 10
        } : attr;
        
        if(attr.initial_interation < attr.finish_interation){
    
            attr = attr.search_form_elements != undefined ? attr : {
                ...attr,
                search_form_elements: getSearchFormElementsBySection({
                    section: attr.section
                })
            }
            
            if(attr.search_form_elements != null){

                console.log('vali: ', attr);
                
                attr = attr.json_form_elements != undefined ? attr : {
                    ...attr,
                    json_form_elements: formJsonFromFormElements({
                        elements: attr.search_form_elements
                    })
                }
    
                attr.json_form_elements = attr.json_form_elements.search_op != undefined ? attr.json_form_elements : {
                    ...attr.json_form_elements,
                    search_op: 'default'
                }
    
                let service_url = getSearchService({
                    search_op: attr.json_form_elements.search_op
                });
    
                attr = !attr.hasOwnProperty('post_data') ? {
                    ...attr,
                    post_data: attr.json_form_elements
                } : {
                    ...attr,
                    post_data: {
                        ...attr.post_data,
                        ...attr.json_form_elements //voy aqui check missing elements crear funcion
                    }
                };

                console.log('a ver: ', service_url);
                console.log('a ver 2: ', attr);

    
                if(service_url != null && attr.post_data != null){
    
                    $.ajax({
                        url: service_url,
                        type: 'POST',
                        dataType: 'json',
                        data: attr.post_data,
                        cache: false
                    }).done(function(response){

                        console.log('response de service: ', response);
    
                        if(response.state == 'success'){
    
                            victim_handle_data.current_records_search_data = JSON.stringify(response.data);
    
                            attr.post_data = {
                                ...attr.post_data,
                                data: JSON.stringify(response.data)
                            }

                            console.log('voy a mandar: ', attr);

                            if(attr.functions_on_success != undefined){
                                if(attr.functions_on_success != null){
                                    for(os_function in attr.functions_on_success){
                                        attr.functions_on_success[os_function].function(attr);
                                    }
                                }
                            }
                        }
                        else if(response.state == 'not_found'){

                            let dashboard_template_file = getTemplateElementService({
                                type: 'dashboard_alert'
                            });
            
                            if(dashboard_template_file != null){

                                setTemplateElment({
                                    service_url: dashboard_template_file,
                                    placement_element_id: 'records-section',
                                    post_data: {
                                        type: 'secondary',
                                        message: '¡No hay registros!'
                                    } 
                                });
                            }
                            showLoadingTable(false);
                            disableBtn({
                                btn_id: attr.btn_id,
                                disable: false
                            });
                        }
                        else{
                            displaySwalMessage({message_op: 'unexpected_dpe'});
                            showLoadingTable(false);
                            disableBtn({
                                btn_id: attr.btn_id,
                                disable: false
                            });
                        }
                    }).fail(function(){
                        attr.initial_interation++;
                        getRecords(attr);
                    });
                }
                else{
                    displaySwalMessage({message_op: 'unexpected_dpe'});
                    showLoadingTable(false);
                    disableBtn({
                        btn_id: attr.btn_id,
                        disable: false
                    });
                }
            }
            else{
                displaySwalMessage({message_op: 'missing_data'});
                showLoadingTable(false);
                disableBtn({
                    btn_id: attr.btn_id,
                    disable: false
                });
            }
        }
        else{
            displaySwalMessage({message_op: 'unexpected_dpe'});
            showLoadingTable(false);
            disableBtn({
                btn_id: attr.btn_id,
                disable: false
            });
        }
    }	
}

function drawRecordsTable(attr){

    console.log('voy a pintar?',attr);

    if(attr != null){

        console.log('entre a pintar');


        attr = attr.search_form_elements != undefined ? attr : {
            ...attr,
            search_form_elements: getSearchFormElementsBySection({
                section: attr.section
            })
        }




        let service_url = getTableTemplateService({
            search_op: attr.json_form_elements.search_op
        });
    
        if(service_url != null){

            console.log('pin 1',service_url);
                
            if(attr.post_data != null){
    
                //attr.post_data.data = JSON.stringify(attr.post_data.data);

                console.log('pin 2',attr);
        
                $.ajax({
                    url: service_url,
                    type: 'POST',
                    dataType: 'html',
                    data: attr.post_data,
                    cache: false
                }).done(function(response){

                    console.log('se supone q si se va  a pintar a');
    
                    $('#'+attr.table_placement_element_id).html(response);

                    if(attr.is_datatable != undefined){
                        
                        if(attr.is_datatable){
                            $('.data-table').DataTable(getDefaultDataTableConfig());
                        }

                        showLoadingTable(false);
                        disableBtn({
                            btn_id: attr.btn_id,
                            disable: false
                        });
                    }
                });
            }
            else{
                let dashboard_template_file = getTemplateElementService({
                    type: 'dashboard_alert'
                });

                if(dashboard_template_file != null){

                    setTemplateElment({
                        service_url: dashboard_template_file,
                        placement_element_id: 'records-section',
                        post_data: {
                            type: 'secondary',
                            message: '¡No hay registros!'
                        } 
                    });
                }
                showLoadingTable(false);
                disableBtn({
                    btn_id: attr.btn_id,
                    disable: false
                });
            }
        }
        else{
            displaySwalMessage({message_op: 'unexpected_dpe'});
            showLoadingTable(false);
            disableBtn({
                btn_id: attr.btn_id,
                disable: false
            });
        }
    }
}

function onchangeElementTable(attr){

    console.log('toy cambiando: ',attr);

    if(attr.id != undefined && attr.search_op != undefined){

        let prefix_id = getTableRowIDPrefixBySearchOP({
            search_op: attr.search_op
        });

        console.log('prefix_id: ', prefix_id);

        if(prefix_id != null){

            console.log('toy cambiando 2: ',attr.id);

            let composite_id = prefix_id+attr.id;

            let row_elements = formTableIDElements({
                elements: getTableFormElementsBySearchOP({
                    search_op: attr.search_op
                }),
                suffix_id: attr.id
            });

            console.log('row_elements: ',row_elements);

            if(validateExistantElementsById({
                elements: row_elements
            })){

                console.log('etre');

                if(document.getElementById(composite_id)){

                    console.log(composite_id);
                    $('#'+composite_id).addClass('update-row');

                    addRowElement({
                        elements: row_elements,
                        id: attr.id
                    });
                }

                if(!victim_handle_data.table_has_changed){
                    victim_handle_data.table_has_changed = true;

                    let save_button_template_file = getTemplateElementService({
                        type: 'rounded_button'
                    });

                    if(save_button_template_file != null){

                        setTemplateElment({
                            service_url: save_button_template_file,
                            placement_element_id: 'save-victims-button-section',
                            post_data: {
                                element_placeholder: 'Guardar',
                                element_type: 'success',
                                element_event_listener: `onclick="saveVictims({search_op: '`+attr.search_op+`'})"`
                            } 
                        });
                    }
                }

                let dashboard_template_file = getTemplateElementService({
                    type: 'dashboard_alert'
                });

                if(dashboard_template_file != null){

                    let to_update_count = Object.keys(victim_handle_data.table_data_to_update).length

                    setTemplateElment({
                        service_url: dashboard_template_file,
                        placement_element_id: 'dashboard-alert-section',
                        post_data: {
                            type: 'warning',
                            message: 'Hay '+to_update_count+' registros sin guardar!'
                        } 
                    });
                }
            }
        }
    }
}

function formTableIDElements(attr){

    console.log('asdasdasd: ',attr);

    if(attr.elements != null && attr.suffix_id != null){
        for(element in attr.elements){
            attr.elements[element].id = attr.elements[element].id+'-'+attr.suffix_id
        }
    }

    return attr.elements;
}

function resetVictims(){
    victim_handle_data.table_data_to_update = {};
    victim_handle_data.table_has_changed = false;
    $('#dashboard-alert-section').html('');
    $('#save-victims-button-section').html('');
    $('#records-section').html('');
}

function saveVictims(attr){

    console.log('voy a guardar: ',attr);

    if(attr.search_op != undefined){

        let service_url = getUpdateService({
            search_op: attr.search_op
        });
    
        if(victim_handle_data.table_data_to_update != undefined && service_url != null){
    
            if(victim_handle_data.table_data_to_update != {}){

                updateData({
                    service_url: service_url,
                    post_data: victim_handle_data.table_data_to_update,
                    search_op: attr.search_op
                });

                /*
    
                let table_data_to_update = victim_handle_data.table_data_to_update;
                let data = {};
    
                for(row in table_data_to_update){
    
                    let row_data = {};
                    let complete_row_data = true;
    
                    for(row_element in table_data_to_update[row]){
    
                        if(document.getElementById(table_data_to_update[row][row_element].id)){
    
                            row_data = {
                                ...row_data,
                                [table_data_to_update[row][row_element].json_key]: document.getElementById(table_data_to_update[row][row_element].id).value
                            }
                        }
                        else{
                            complete_row_data = false;
                            break;
                        }
                    }
    
                    if(complete_row_data){
                        data = {
                           ...data,
                            [row]: {
                                ...row_data,
                                id: row
                            }
                        }
                    }
                }
    
                console.log('tengo esto: ', data);

                if(data != {}){
                    updateData({
                        service_url: service_url,
                        post_data: data
                    });
                }

                */

                
            }
        }

    }
}

function updateData(attr){

    if(attr.service_url != undefined && attr.post_data != undefined){
        if(attr.service_url != null && attr.post_data != null){

            showLoading(true);

            $.ajax({
                url: attr.service_url,
                type: 'POST',
                dataType : 'json', 
                data: {
                    to_update: JSON.stringify(attr.post_data)
                },
                cache: false
            }).done(function(response){
                if(response.state == 'success'){
                    
                    Swal.fire('Correcto', 
                        response.data.updated_count+'/'+response.data.to_update_count+' registros guardados correctamente', 
                        'success'
                    );

                    

                    console.log('chido chido', response);
                    console.log('chido lo', response.state);
                    resetVictims();
                    showLoading(false);

                    getVictims({section: 'victim_validation'});

                }
                else{
        
                    Swal.fire('Error', 'Ha ocurrido un error, vuelva a intentarlo', 'error');
        
                    console.log('not chido', response);
                    console.log('chido no lo', response.state);
                    showLoading(false);
                }
                
            }).fail(function (jqXHR, textStatus) {
                Swal.fire('Error', 'Ha ocurrido un error inesperado del servidor, Favor de nofificar a DPE.', 'error');
        
                showLoading(false);
            });

        }
    }

    
}

function addRowElement(attr){

    if(attr.elements != undefined && attr.id != undefined){
        if(attr.elements != null && attr.id != null){

            if(victim_handle_data.table_data_to_update.hasOwnProperty(attr.id)){
                delete victim_handle_data.table_data_to_update[attr.id];
            }

            let row_data = {};
            let complete_row_data = true;

            for(row_element in attr.elements){

                if(document.getElementById(attr.elements[row_element].id)){

                    row_data = {
                        ...row_data,
                        [attr.elements[row_element].json_key]: document.getElementById(attr.elements[row_element].id).value
                    }
                }
                else{
                    complete_row_data = false;
                    break;
                }
            }

            if(complete_row_data){
                victim_handle_data.table_data_to_update = {
                    ...victim_handle_data.table_data_to_update,
                    [attr.id]: {
                        ...row_data,
                        id: attr.id
                    }
                }
            }
        }
    }
}

function showOtherVictims(attr){

    console.log('show other: ', attr.id);

    if(attr.id != null && attr.id != undefined && attr.id != ''){

        getOtherVictims({
            file_location: 'service/victim_validation/get_other_victims_by_id.php',
            post_data: {
                id: attr.id,
                cid: attr.cid
            },
            success: {
                function: setModal,
                attr: {
                    file_location: 'templates/modals/unknown_age_gener_modal.php',
                    element_modal_section_id: 'victim-validation-default-modal-section',
                    post_data: {
                        id: attr.id,
                        cid: attr.cid,
                        nuc: attr.nuc
                    },
                    success: {
                        functions: [
                            {
                                function: showModal,
                                attr: {
                                    show: true,
                                    modal_id: 'large-modal'
                                }
                            }
                        ]
                    },
                }
            }
        });
    }
}

function getOtherVictims(attr){

    if(attr.post_data != null){
        $.ajax({
            url: attr.file_location,
            type: 'POST',
            dataType: "JSON",
            data: attr.post_data,
            cache: false
        }).done(function(response){ 

            console.log('response de rejected: ', response);

            console.log('to modal: ', attr.success.attr);

            if(response.state == 'success'){
                attr.success.attr.post_data = {
                    ...attr.success.attr.post_data,
                    victims: JSON.stringify(response.data)
                }
                
            }

            if(attr.success != undefined && attr.success != null){

                attr.success.function(attr.success.attr);
            }

        });
    }
}

function setModal(attr){

    console.log('set modal: ', attr);

    if(attr.post_data != null){
        $.ajax({
            url: attr.file_location,
            type: 'POST',
            dataType: "html",
            data: attr.post_data,
            cache: false
        }).done(function(response){

            $('#'+attr.element_modal_section_id).html(response);

            /*if(attr.success != undefined){
                attr.success.function(attr.success.attr);
            }*/

            if(attr.success != undefined){
                for(func in attr.success.functions){
                    attr.success.functions[func].function(attr.success.functions[func].attr);
                }
            }

            

        });
    }
}

function showModal(attr){

    if(attr.modal_id != null && attr.show != null){

        if(attr.show){
            $('#'+attr.modal_id).modal('show');
        }
        else{
            $('#'+attr.modal_id).modal('hide');
        }
    }
}