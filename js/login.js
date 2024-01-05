$(document).ready(function(){ 

    jQuery('#login-form').submit(login);
	
});

function login(){

    $.ajax({  
        type: "POST",  
        url: "service/login.php", 
        dataType : 'json', 
        data: {
            auth: JSON.stringify({
                username: $("#user").val(),
                password: $("#pass").val()
            })
        },
    }).done(function(response){

        if(response.state != "fail"){

            if(response.data != null){
                setSessionVariables('user', response.data.user);
                //showLoading(true);

                switch(response.data.user.type){
                    case 1:
                        redirectTo('src/admin/admin.php');
                        break;
                    case 2:
                        redirectTo('src/main/main.php');
                        break;
                    case 4:
                        redirectTo('src/admin/admin.php');
                        break;
                    default:
                        redirectTo('src/main/main.php');
                        break;
                }
            }
            else{
                Swal.fire('Usuario incorrecto', 'Intentelo de nuevo!', 'warning');
            }
        }
        else{
            Swal.fire('Oops...', 'Ha fallado la conexión!', 'error');
        }
        
    });  
    
    return false;
}