function setMultiselectActionsBySection(section){

    let fields = sections[section].fields;

    for(field in fields){

        if(fields[field].type == 'multiselect'){

            console.log('set multiselect', {
                id: fields[field].id,
                name: fields[field].name,
                counter: 1,
                iterations: 50,
                delay: 500
            });

            setMultiselectActions({
                id: fields[field].id,
                name: fields[field].name,
                counter: 1,
                iterations: 50,
                delay: 500
            });
        }
    }
}

function saveMultiselectFieldsBySection(attr){

    console.log('save multi sections: ', attr);

    let has_multiselect = false;

    let fields = sections[attr.section].fields;

    for(field in fields){

        if(fields[field].type == 'multiselect'){

            if(fields[field].service.create_file != null){

                console.log('handle', sedena_handle_data);
                console.log('field name', fields[field].name);
                console.log('esistes o noooooooo', sedena_handle_data.current_multiselect[fields[field].name]);

                saveMultiselectField({
                    section: attr.section,
                    service_file: fields[field].service.create_file,
                    post_data: {
                        id: attr.id,
                        data: sedena_handle_data.current_multiselect[fields[field].name]
                    }
                });
            }
            else{
                console.log('no existe service file');
            }

            has_multiselect = true;
        }
    }

    if(!has_multiselect){
        getRecordsByMonth({
            section: attr.section
        });
    }
}

function saveMultiselectField(attr){

    console.log('save multiple: ', attr);

    $.ajax({
		url: 'service/'+attr.service_file,
        type: 'POST',
        dataType : 'json', 
		data: attr.post_data,
		cache: false
	}).done(function(response){

        console.log('si wasd');


        if(response.state == 'success'){
            
            Swal.fire('Correcto', 'Datos guardados correctamente', 'success');

            console.log('chido chido', response);

            resetSection(attr.section);

        }
        else{

            Swal.fire('Error', 'Ha ocurrido un error, vuelva a intentarlo', 'error');

            console.log('not chido', response);
        }

        getRecordsByMonth({
            section: attr.section
        });

        /*setLoader({
            add: false
        });*/

	}).fail(function (jqXHR, textStatus) {
        
        Swal.fire('Error', 'Ha ocurrido un error inesperado del servidor, Favor de nofificar a DPE.', 'error');

        getRecordsByMonth({
            section: attr.section
        });

        /*setLoader({
            add: false
        });*/
    });
}

function resetMultiselect(){
    for(element in document.getElementsByClassName('multiselect-element')){
        document.getElementsByClassName('multiselect-element')[element].checked = false;
    }

    for(element in sedena_handle_data.current_multiselect){
        sedena_handle_data.current_multiselect[element] = [];
    }
}

function setMultiselectActions(attr){

    if(attr.counter <= attr.iterations && !document.getElementById(attr.id)){
		setTimeout(
			function(){
				attr.counter++;
				setMultiselectActions(attr);
			}, attr.delay
		);
	}
    else{
        sedena_handle_data.current_multiselect = {
            ...sedena_handle_data.current_multiselect,
            [attr.name]: []
        };

        $( "#"+attr.id+" .dropdown-menu a" ).on( "click", function( event ) {
    
            var $target = $( event.currentTarget ),
                val = $target.attr( "data-value" ),
                $inp = $target.find( "input" ),
                idx;
            
            if( ( idx = sedena_handle_data.current_multiselect[attr.name].indexOf( val ) ) > -1 ){
                sedena_handle_data.current_multiselect[attr.name].splice( idx, 1 );
                setTimeout( function() { $inp.prop( "checked", false ) }, 0);
            } 
            else{
                sedena_handle_data.current_multiselect[attr.name].push( val );
                setTimeout( function() { $inp.prop( "checked", true ) }, 0);
            }
            
            $(event.target).blur();
                
            console.log(sedena_handle_data.current_multiselect[attr.name]);
            return false;
        });
    }
}