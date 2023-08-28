function searchMicrodata(){

    microdata_op = {
        'Homicidio': {
            service_file: 'report_microdata_homicide_xlsx.php',
            file_name: 'Microdato_Homicidio'
        },
        'Feminicidio': {
            service_file: 'report_microdata_femicide_xlsx.php',
            file_name: 'Microdato_Feminicidio'
        },
        'Secuestro': {
            service_file: 'report_microdata_kidnapping_xlsx.php',
            file_name: 'Microdato_Secuestro'
        }
    }

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
            },
            search_option: {
                element_id: 'main-search-option',
                element_type: 'text'
            }
        }
    })){

        let search_date = getLocalDateFormat(document.getElementById('main-search-search-month').value);
        let serach_option = document.getElementById('main-search-option').value;
        
        get_retransmission_service({
            url_service_file: 'service/get_microdata_procedure_data.php',
            parameters: {
                month: search_date.getMonth()+1,
                year: search_date.getFullYear(),
                procedure_op: serach_option,
                procedure: 2
            },
            show_loading: true,
            on_success: {
                functions: [
                    {
                        function: createExcelReport,
                        attr: {
                            data: null,
                            url_service_file: 'templates/excel/'+microdata_op[serach_option].service_file,
                            file_name: microdata_op[serach_option].file_name+'_'+array_months[search_date.getMonth()]+'_'+search_date.getFullYear()+'.xlsx'
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