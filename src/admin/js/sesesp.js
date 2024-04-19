function searchSESESP(){

    array_months = {
        0: 'Enero',
        1: 'Febrero',
        2: 'Marzo',
        3: 'Abril',
        4: 'Mayo',
        5: 'Junio',
        6: 'Julio',
        7: 'Agosto',
        8: 'Septiembre',
        9: 'Octubre',
        10: 'Noviembre',
        11: 'Diciembre'
    }
    
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
            url_service_file: 'service/get_sesesp_procedure_data.php',
            parameters: {
                month: search_date.getMonth()+1,
                year: search_date.getFullYear()
            },
            show_loading: true,
            on_success: {
                functions: [
                    {
                        function: createExcelReportCopy,
                        attr: {
                            data: null,
                            url_service_file: 'templates/excel/generic_report.php',
                            file_name: 'incidencia_sesesp_'+array_months[search_date.getMonth()]+'_'+search_date.getFullYear()+'.xlsx'
                        },
                        response: true
                    }
                ]
            }
        });
    }
    else{
        Swal.fire('Campos faltantes', 'Tiene que completar alguno de los campos para completar la busqueda', 'warning');
    }
}