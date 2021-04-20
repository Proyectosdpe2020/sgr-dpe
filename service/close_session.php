<?php
	session_start(); 
	session_destroy();
	
    echo json_encode(
		array(
			'state' => 'success',
			'data' => false
		), 
		JSON_FORCE_OBJECT
	);  
?>