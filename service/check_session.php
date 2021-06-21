<?php

	session_start(); 

	if(isset($_SESSION['user_data'])){
		echo json_encode(
			array(
				'state' => 'success',
				'data' => false,
				'type' => $_SESSION['user_data']['type']
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