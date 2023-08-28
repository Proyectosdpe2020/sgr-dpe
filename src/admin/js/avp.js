function executeAVP(){

    Swal.fire({
        title: 'Estas seguro?',
        text: 'Se exportaran los datos a base de datos histórica!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Si',
        cancelButtonText: 'No'
    }).then((result) => {
        if(result.isConfirmed){
            executeAVPService();
        }
    });
}

function executeAVPService(){
    if(checkValues({
        elements: {
            search_month: {
                element_id: 'main-search-search-month',
                element_type: 'date'
            }
        }
    })){

        let search_date = getLocalDateFormat(document.getElementById('main-search-search-month').value);
        
        get_retransmission_service({
            url_service_file: '../avp/controller.php',
            parameters: {
                selMes: search_date.getMonth()+1,
                selAnio: search_date.getFullYear()
            },
            show_loading: true,
            on_success: {
                functions: []
            }
        });
    }
    else{
        Swal.fire('Campos faltantes', 'Tiene que completar alguno de los campos para completar la busqueda', 'warning');
    }
}