function getSearchService(attr){

    let service_location = 'service/victim_validation/';

    let urls = {
        unknown_age: service_location+'search_unknown_age.php',
        unknown_gener: service_location+'search_unknown_gener.php',
        crimes_by_minor: service_location+'search_crimes_by_minor.php',
        crimes_by_unknown_gener: service_location+'search_crimes_by_unknown_gener.php',
        crimes_by_moral: service_location+'search_crimes_by_moral.php',
        robbery_months_old: service_location+'search_robbery_months_old.php',
        default: service_location+'search_unknown_age.php'
    };

    return attr.search_op != undefined ? (
        urls[attr.search_op]!= undefined? urls[attr.search_op] : null
    ) : null;
}

function getUpdateService(attr){

    let service_location = 'service/victim_validation/';

    let urls = {
        unknown_age: service_location+'update_unknown_age.php',
        unknown_gener: service_location+'update_unknown_gener.php',
        crimes_by_minor: service_location+'update_crimes_by_minor.php',
        crimes_by_unknown_gener: service_location+'update_crimes_by_unknown_gener.php',
        crimes_by_moral: service_location+'update_crimes_by_moral.php',
        robbery_months_old: service_location+'update_robbery_months_old.php'
    };

    return attr.search_op != undefined ? (
        urls[attr.search_op]!= undefined? urls[attr.search_op] : null
    ) : null;
}

function getTableTemplateService(attr){

    let template_service_location = 'templates/tables/victim_validation/';

    let urls = {
        unknown_age: template_service_location+'unknown_age_table.php',
        unknown_gener: template_service_location+'unknown_gener_table.php',
        crimes_by_minor: template_service_location+'crimes_by_minor_table.php',
        crimes_by_unknown_gener: template_service_location+'crimes_by_unknown_gener_table.php',
        crimes_by_moral: template_service_location+'crimes_by_moral_table.php',
        robbery_months_old: template_service_location+'robbery_months_old_table.php'
    };

    return attr.search_op != undefined ? (
        urls[attr.search_op]!= undefined? urls[attr.search_op] : null
    ) : null;
}

function getSearchFormElementsBySection(attr){

    let form_elements_by_section = {
        victim_validation: [
            {
                id: 'main-search-month',
                type: 'date',
                json_key: 'year_month',
                required: true
            },
            {
                id: 'main-search-op',
                type: 'text',
                json_key: 'search_op',
                required: true
            }
        ]
    };
    
    return attr.section != undefined ? (
        form_elements_by_section[attr.section]!= undefined? form_elements_by_section[attr.section] : null
    ) : null;
}

function getDefaultDataTableConfig(){
    return {
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
                "last": "Último",
                "next": "Siguiente",
                "previous": "Anterior"
            }
        },
        lengthMenu: [
            [50, 100, 200, 500, -1],
            [50, 100, 200, 500, "TODO"]
        ]
    };
}

function getTableFormElementsBySearchOP(attr){

    let form_elements_by_search_op = {
        unknown_age: [
            {
                id: 'age',
                type: 'text',
                json_key: 'age'
            },
            {
                id: 'gener',
                type: 'text',
                json_key: 'gener'
            },
            {
                id: 'name',
                type: 'text',
                json_key: 'name'
            },
            {
                id: 'ap',
                type: 'text',
                json_key: 'ap'
            },
            {
                id: 'am',
                type: 'text',
                json_key: 'am'
            }
        ],
        unknown_gener: [
            {
                id: 'gener',
                type: 'text',
                json_key: 'gener'
            }
        ],
        crimes_by_minor: [
            {
                id: 'age',
                type: 'text',
                json_key: 'age'
            },
            {
                id: 'name',
                type: 'text',
                json_key: 'name'
            },
            {
                id: 'ap',
                type: 'text',
                json_key: 'ap'
            },
            {
                id: 'am',
                type: 'text',
                json_key: 'am'
            }
        ],
        crimes_by_unknown_gener: [
            {
                id: 'gener',
                type: 'text',
                json_key: 'gener'
            },
            {
                id: 'name',
                type: 'text',
                json_key: 'name'
            },
            {
                id: 'ap',
                type: 'text',
                json_key: 'ap'
            },
            {
                id: 'am',
                type: 'text',
                json_key: 'am'
            }
        ],
        crimes_by_moral: [
            {
                id: 'gener',
                type: 'text',
                json_key: 'gener'
            },
            {
                id: 'name',
                type: 'text',
                json_key: 'name'
            },
            {
                id: 'ap',
                type: 'text',
                json_key: 'ap'
            },
            {
                id: 'am',
                type: 'text',
                json_key: 'am'
            }
        ],
        robbery_months_old: [
            {
                id: 'age',
                type: 'text',
                json_key: 'age'
            },
            {
                id: 'name',
                type: 'text',
                json_key: 'name'
            },
            {
                id: 'ap',
                type: 'text',
                json_key: 'ap'
            },
            {
                id: 'am',
                type: 'text',
                json_key: 'am'
            }
        ]
    };
    
    return attr.search_op != undefined ? (
        form_elements_by_search_op[attr.search_op]!= undefined? form_elements_by_search_op[attr.search_op] : null
    ) : null;
}

function getTableRowIDPrefixBySearchOP(attr){
    
    let prefix_id = {
        unknown_age: 'unknown-age-row-',
        unknown_gener: 'unknown-gener-row-',
        crimes_by_minor: 'crimes-minor-row-',
        crimes_by_unknown_gener: 'crimes-gener-row-',
        crimes_by_moral: 'crimes-moral-row-',
        robbery_months_old: 'robbery-months-old-row-'
    };
    
    return attr.search_op != undefined ? (
        prefix_id[attr.search_op]!= undefined? prefix_id[attr.search_op] : null
    ) : null;
}

function getTemplateElementService(attr){

    let service_location = 'templates/elements/';

    let urls = {
        dashboard_alert: service_location+'dashboard_alert.php',
        select: service_location+'select.php',
        rounded_button: service_location+'rounded_button.php'
    };

    return attr.type != undefined ? (
        urls[attr.type]!= undefined? urls[attr.type] : null
    ) : null;
}