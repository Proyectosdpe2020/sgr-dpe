<?php

$params = array();
$options = array( "Scrollable" => SQLSRV_CURSOR_KEYSET );

function createQuery($attr){

	$sql = "INSERT INTO $db_table
				( $columns )
				VALUES
				( $values )
            SELECT SCOPE_IDENTITY()";

	if($conn){
		$stmt = sqlsrv_query( $conn, $sql);

		sqlsrv_next_result($stmt); 
		sqlsrv_fetch($stmt); 

		$id = sqlsrv_get_field($stmt, 0);

		return array(
            'state' => 'success',
            'data' => array(
                'id' => $id
            )
        );
	}
	else{
		return array(
            'state' => 'fail',
            'data' => null
        );
	}

}


function executeDumpQuery($attr){

	$insert_table_param = $attr->db_query_params->db_insert_table;
	$insert_fields_param = $attr->db_query_params->db_insert_fields;
	$search_fields_param = $attr->db_query_params->db_search_fields;
	$search_table_param = $attr->db_query_params->db_search_table;
	$insert_conditions_param = $attr->db_query_params->db_insert_conditions;


	$sql = "INSERT INTO $insert_table_param $insert_fields_param SELECT $search_fields_param FROM $search_table_param WHERE $insert_conditions_param";



	if($attr->db_connection_params->conn){

		if(sqlsrv_begin_transaction($attr->db_connection_params->conn) === false) {

			die(print_r( sqlsrv_errors(), true));

			return array(
				'state' => 'fail',
				'data' => null
			);

	   	}
		else{

			$stmt = sqlsrv_query($attr->db_connection_params->conn, $sql);

			//sqlsrv_next_result($stmt); 

			if($stmt){
				
				sqlsrv_commit($attr->db_connection_params->conn);
				//echo "Transaccion consolidada.<br />";

				return array(
					'state' => 'success',
					'data' => null
				);
			} 
			else{

				sqlsrv_rollback($attr->db_connection_params->conn);
				//echo "Transaccion revertida.<br />";

				return array(
					'state' => 'fail',
					'data' => null
				);
			}

		}

		
	}
	else{

		return array(
            'state' => 'fail',
            'data' => null
        );
	}
}

function executeDumpQuery2($attr){

	$insert_table_param = $attr->db_query_params->db_insert_table;
	$insert_fields_param = $attr->db_query_params->db_insert_fields;
	$search_fields_param = $attr->db_query_params->db_search_fields;
	$search_table_param = $attr->db_query_params->db_search_table;
	$insert_conditions_param = $attr->db_query_params->db_insert_conditions;
	$db_query_param = $attr->db_query_params->db_query;


	$sql = "INSERT INTO $insert_table_param $db_query_param WHERE $insert_conditions_param";


	if($attr->db_connection_params->conn){

		if(sqlsrv_begin_transaction($attr->db_connection_params->conn) === false) {

			die(print_r( sqlsrv_errors(), true));

			return array(
				'state' => 'fail',
				'data' => null
			);

	   	}
		else{

			$stmt = sqlsrv_query($attr->db_connection_params->conn, $sql);

			//sqlsrv_next_result($stmt); 

			if($stmt){
				
				sqlsrv_commit($attr->db_connection_params->conn);
				//echo "Transaccion consolidada.<br />";

				return array(
					'state' => 'success',
					'data' => null
				);
			} 
			else{

				sqlsrv_rollback($attr->db_connection_params->conn);
				//echo "Transaccion revertida.<br />";

				return array(
					'state' => 'fail',
					'data' => null
				);
			}

		}

		
	}
	else{

		return array(
            'state' => 'fail',
            'data' => null
        );
	}
}

function getGenericData($attr){

	if($attr->conn){
		
		$stmt = sqlsrv_query($attr->conn, $attr->sql, $attr->params, $attr->options);
		$row_count = sqlsrv_num_rows($stmt);
		$return = array();

		if($row_count != 0){
			
			$fields = array();
			$data = array();

			foreach(sqlsrv_field_metadata($stmt) as $fieldMetadata){
				foreach( $fieldMetadata as $name => $value){
					if($name == 'Name'){
						array_push($fields, $value);
					}
				}
			}
			
			while($row = sqlsrv_fetch_array($stmt)){
				$current_row = array();
				for($i=0; $i<count($fields); $i++){
					$current_row += [$fields[$i] => $row[$fields[$i]]];
				}
				array_push($data, $current_row);
			}
				
			return array(
				'state' => 'success',
				'data' => $data
			);
		}
		else{
			return array(
				'state' => 'not_found',
				'data' => null
			);
		}
	}
	else{
		return array(
            'state' => 'fail',
            'data' => null
        );
	}
}

function updateGenericData($attr){

	if($attr->conn){
		
		$stmt = sqlsrv_query($attr->conn, $attr->sql);
		$return = array();

		sqlsrv_next_result($stmt); 
		sqlsrv_fetch($stmt); 

		return array(
			'state' => 'success',
			'data' => null
		);
	}
	else{
		return array(
            'state' => 'fail',
            'data' => null
        );
	}
}
?>