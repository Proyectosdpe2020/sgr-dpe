function validateElementsByIdContent(attr){

    let validated = true;

    if(validateExistantElementsById({
        elements: attr.elements
    })){
        for(element in attr.elements){

            if(validated){

                switch(attr.elements[element].type){
                
                    case 'date':
                        validated = attr.elements[element].required ? (
                            document.getElementById(attr.elements[element].id).value == '' ? false : true
                        ) : true;
                        break;
                    case 'text':
                        validated = attr.elements[element].required ? (
                            document.getElementById(attr.elements[element].id).value == '' ? false : true
                        ) : true;
                        break;
                    default:
                }
            }
            else{
                break;
            }
        }
    }

    return validated;
}

function validateExistantElementsById(attr){

    let validated = true;

    for(element in attr.elements){

        console.log('attr id: ',attr.elements[element].id);
        if(!document.getElementById(attr.elements[element].id)){
            validated = false;
            //break;
            console.log('si');
        }
        else{
            console.log('no');
        }
    }

    return validated;
}

function formJsonPostDataByID(attr){

    let json_data = {};

    for(element in attr.elements){

        json_data = {
            ...json_data,
            [attr.elements[element].json_key]: document.getElementById(attr.elements[element].id).value
        }
    }

    return json_data;
}

function generateTempName(){

    let today = new Date();
    let date = today.getFullYear()+''+(today.getMonth()+1)+''+today.getDate();
    var time = today.getHours() + "" + today.getMinutes() + "" + today.getSeconds();
    return 'request_'+date+''+time;
}

function downloadExcel(attr){

    var uri = 'data:application/vnd.ms-excel;base64,'
      , template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><meta cha' + 'rset="UTF-8"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>{worksheet}</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head><body>{table}</body></html>'
      , base64 = function(s) { return window.btoa(unescape(encodeURIComponent(s))) }
      , format = function(s, c) { return s.replace(/{(\w+)}/g, function(m, p) { return c[p]; }) }
    
    var ctx = {worksheet: name || 'Worksheet', table: attr.table}
    window.location.href = uri + base64(format(template, ctx));
}

function formJsonFromFormElements(attr){
    
    let json_data = {};

    for(element in attr.elements){

        json_data = {
           ...json_data,
            [attr.elements[element].json_key]: document.getElementById(attr.elements[element].id).value
        }
    }

    return json_data;
}

function checkJSONProperties(attr){
    
    let validated = true;

    if(attr.hasOwnProperty('json_properties')){
        for(property in attr.json_properties){
            if(!attr.json_data.hasOwnProperty(attr.json_properties[property])){
                validated = false;
                break;
            }
        }
    }

    return validated;
}

function setDateField(attr){

    switch(attr.set_date){

        case 'today':

            let today = new Date();
            var date_input = document.getElementById(attr.element_id);
            today = new Date(today.getFullYear(), today.getMonth(), today.getDate());
            date_input.valueAsDate = today;
            break;

        default:
    }
}

function getCurrentMonthDateRange(){

    let d = new Date();
    
    return {
        initial_date: d.getFullYear()+'-'+(d.getMonth()+1)+'-01',
        finish_date: d.getFullYear()+'-'+(d.getMonth()+1)+'-'+d.getDate()
    }
}

function displaySwalMessage(attr){

    if(attr != null){

        if(attr.hasOwnProperty('message_op')){

            if(victim_handle_data.swal_messages.hasOwnProperty(attr.message_op)){

                Swal.fire(victim_handle_data.swal_messages[attr.message_op].title, 
                    victim_handle_data.swal_messages[attr.message_op].text, 
                    victim_handle_data.swal_messages[attr.message_op].type
                );
            }
        }
    }
}

function loadDashboardAlert(attr){

    if(attr.template_file != undefined 
        && attr.element_attr != undefined 
        && attr.placement_element_id != undefined){

        $.ajax({
            url: attr.template_file,
            type:'POST',
            dataType: "html",
            data: attr.element_attr,
            cache:false
        }).done(function(response){
            if(document.getElementById(attr.placement_element_id)){
                $('#'+attr.placement_element_id).html(response);
            }
        });
    }	
}

function setTemplateElment(attr){

    if(attr.service_url != undefined 
        && attr.post_data != undefined 
        && attr.placement_element_id != undefined){

        $.ajax({
            url: attr.service_url,
            type: 'POST',
            dataType: 'html',
            data: attr.post_data,
            cache:false
        }).done(function(response){
            if(document.getElementById(attr.placement_element_id)){
                $('#'+attr.placement_element_id).html(response);
            }
        });
    }	
}

function resetDashboardAlert(attr){
    if(document.getElementById(attr.element_id)){
        $('#'+attr.element_id).html('');
    }
}

function disableBtn(attr){

    if(document.getElementById(attr.btn_id)){
        document.getElementById(attr.btn_id).disabled = attr.disable;
    }
}

function showLoading(ind){
    if(ind)
        $(".loader-div").addClass("loader");
    else
        $(".loader-div").removeClass("loader");
}

function showLoadingTable(ind){
    if(ind)
        $(".table-loader-div").addClass("table-loader");
    else
        $(".table-loader-div").removeClass("table-loader");
}

function clearDiv(attr){
    if(attr.element_id != undefined){
        if(document.getElementById(attr.element_id)){
            $('#'+attr.element_id).html('');
        }
    }
}