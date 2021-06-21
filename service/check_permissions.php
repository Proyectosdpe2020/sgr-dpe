<?php

session_start(); 
    $permissions = array(
        '404' => array(
        )
    );
	
	if(isset($_SESSION['user_data']) && isset($permissions[$_SESSION['user_data']['type']])){
		echo json_encode(
			array(
				'state' => 'success',
				'data' => false,
				'type' => $_SESSION['user_data']['type'],
                'permissions' => $permissions[$_SESSION['user_data']['type']]
			),
			JSON_FORCE_OBJECT
		);
	}
	else{
		echo json_encode(
			array(
				'state' => 'failed',
				'data' => false,
				'type' => false
			),
			JSON_FORCE_OBJECT
		);
	}
?>