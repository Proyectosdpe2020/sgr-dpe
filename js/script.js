function setSessionVariables(type, data){

    $.ajax({  
        type: "POST",  
        url: "service/set_session_variables.php", 
        data: {
            user_data: JSON.stringify({
                type: type,
                data: data
            })
        },
        dataType: 'json'
    }).done(function(response){
        return response;
    });  
    
    return false;
}

function showLoading(ind){
    if(ind)
        $(".loader-div").addClass("loader");
    else
        $(".loader-div").removeClass("loader");
}

function redirectTo(location){
    window.location = location;
}

function closeSession(){
    console.log('close');
    $.ajax({
		url: '../../service/close_session.php',
        type: 'POST',
        dataType : 'json',
		cache: false
	}).done(function(response){

        if(response.state == 'success'){
            redirectTo('../../index.html');
        }

	});
}

function checkSession(attr){
    $.ajax({
		url: attr.location,
        type: 'POST',
        dataType : 'json',
		cache: false
	}).done(function(response){

        if(response.state == 'success'){
            if(attr.success.function != null)
                attr.success.function(attr.success.attr);
        }
        else{
            if(attr.failed.function != null)
                attr.failed.function(attr.failed.attr);
        }

	});
}