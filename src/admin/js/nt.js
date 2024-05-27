function getNT(){

    if(checkValues({
        elements: {
            search_month: {
                element_id: 'main-search-search-month',
                element_type: 'date'
            }
        }
    })){

        showLoading(true);

        let search_date = getLocalDateFormat(document.getElementById('main-search-search-month').value);

        $.ajax({
            url: 'service/nt/get_nt_procedure_data.php',
            type: 'POST',
            dataType: "JSON",
            data: {
                month: search_date.getMonth()+1,
                year: search_date.getFullYear(),
                type: 1
            },
            cache: false
        }).done(function(response){
            
            getNTVictims({
                nt: response
            });
        }).fail(function(){
            
            showLoading(false);
            Swal.fire('error', 'Ha ocurrido un error inesperado, favor de contactar a DPE', 'error');
        });
    }
    else{
        Swal.fire('Campos faltantes', 'Tiene que completar alguno de los campos para completar la busqueda', 'warning');
    }
}

function getNTVictims(attr){

    if(checkValues({
        elements: {
            search_month: {
                element_id: 'main-search-search-month',
                element_type: 'date'
            }
        }
    })){

        showLoading(true);

        let search_date = getLocalDateFormat(document.getElementById('main-search-search-month').value);

        $.ajax({
            url: 'service/nt/get_nt_procedure_data.php',
            type: 'POST',
            dataType: "JSON",
            data: {
                month: search_date.getMonth()+1,
                year: search_date.getFullYear(),
                type: 2
            },
            cache: false
        }).done(function(response){
            
            getCoordinates({
                ...attr,
                victim_nt: response
            });
        }).fail(function(){
            
            showLoading(false);
            Swal.fire('error', 'Ha ocurrido un error inesperado, favor de contactar a DPE', 'error');
        });
    }
    else{
        Swal.fire('Campos faltantes', 'Tiene que completar alguno de los campos para completar la busqueda', 'warning');
    }
}

function getCoordinates(attr){

    $.ajax({
        url: 'service/nt/get_coordinates.php',
        type: 'POST',
        dataType: "JSON",
        data: null,
        cache: false
    }).done(function(response){

        getSheets({
            ...attr,
            coordinates: response.data
        });
    }).fail(function(){
            
        showLoading(false);
        Swal.fire('error', 'Ha ocurrido un error inesperado, favor de contactar a DPE', 'error');
    });
}

function getSheets(attr){

    $.ajax({
        url: 'service/nt/get_municipality_sheets.php',
        type: 'POST',
        dataType: "JSON",
        data: null,
        cache: false
    }).done(function(response){

        getCoordinatesBlackList({
            ...attr,
            sheets: response.data
        });
    }).fail(function(){
            
        showLoading(false);
        Swal.fire('error', 'Ha ocurrido un error inesperado, favor de contactar a DPE', 'error');
    });
}

function getCoordinatesBlackList(attr){

    $.ajax({
        url: 'service/nt/get_coordinates_blacklist.php',
        type: 'POST',
        dataType: "JSON",
        data: null,
        cache: false
    }).done(function(response){

        getVictimCoordinates({
            ...attr,
            coordinates_blacklist: response.data
        });
    }).fail(function(){
            
        showLoading(false);
        Swal.fire('error', 'Ha ocurrido un error inesperado, favor de contactar a DPE', 'error');
    });
}

function getVictimCoordinates(attr){

    $.ajax({
        url: 'service/nt/get_victim_coordinates.php',
        type: 'POST',
        dataType: "JSON",
        data: null,
        cache: false
    }).done(function(response){

        getVictimClasification({
            ...attr,
            victim_coordinates: response.data
        });
    }).fail(function(){
            
        showLoading(false);
        Swal.fire('error', 'Ha ocurrido un error inesperado, favor de contactar a DPE', 'error');
    });
}

function getVictimClasification(attr){

    $.ajax({
        url: 'service/nt/get_victim_classification.php',
        type: 'POST',
        dataType: "JSON",
        data: null,
        cache: false
    }).done(function(response){

        downloadNT({
            ...attr,
            victim_classification: response.data
        });
    }).fail(function(){
            
        showLoading(false);
        Swal.fire('error', 'Ha ocurrido un error inesperado, favor de contactar a DPE', 'error');
    });
}

function downloadNT(attr){

    array_months = {
        0: 'enero',
        1: 'febrero',
        2: 'marzo',
        3: 'abril',
        4: 'mayo',
        5: 'junio',
        6: 'julio',
        7: 'agosto',
        8: 'septiembre',
        9: 'octubre',
        10: 'noviembre',
        11: 'diciembre'
    }

    let search_date = getLocalDateFormat(document.getElementById('main-search-search-month').value);

    createExcelReport({
        data: attr,
        url_service_file: 'templates/excel/report_nt_xlsx.php',
        file_name: 'norma_tecnica_'+array_months[search_date.getMonth()]+'_'+search_date.getFullYear()
    });
}