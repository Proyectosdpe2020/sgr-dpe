<?php
session_start();
include('connection.php');
$conn = $connections['sgr_dpe']['conn'];
$db = $connections['sgr_dpe']['db'];

$data = json_decode($_POST['auth'], true );

$user = $data['username'];
$pass = $data['password'];


if($conn){
    $sql = "SELECT TOP (1)
            u.[UsuarioID]
            ,[Usuario]
            ,[Nombre]
            ,[ApellidoPaterno]
            ,[ApellidoMaterno]
            ,[Tipo]
            ,[SENAP]
        FROM [sgr_dpe].[dbo].[Usuario] u INNER JOIN sgr_dpe.dbo.Permisos p ON u.UsuarioID = p.UsuarioID 
        WHERE [Usuario] = '$user'
        AND [Contrasena] = '$pass'";

    $params = array();
    $options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
    $result = sqlsrv_query( $conn, $sql , $params, $options );

    $row_count = sqlsrv_num_rows( $result );

    $json = '';
    $return = array();

    if($row_count != 0){
        while( $row = sqlsrv_fetch_array( $result) ) {
            $json = json_encode($row);
        }
        
        $json = json_decode($json, true);
            
        $return = array(
            'state' => 'success',
            'data' => array(
                'user' => array(
                    'id' => $json['UsuarioID'],
                    'username' => $json['Usuario'],
                    'name' => $json['Nombre'],
                    'paternal_surname' => $json['ApellidoPaterno'],
                    'maternal_surname' => $json['ApellidoMaterno'],
                    'type' => $json['Tipo'],
                    'permissions' => array(
                        'senap' => $json['SENAP']
                    )
                )
            )
        );
        
    }
    else{
        $return = array(
            'state' => 'not_found',
            'data' => null
        );
    }

    echo json_encode($return, JSON_FORCE_OBJECT);

    sqlsrv_close($conn);
}
else{
    $return = array(
        'state' => 'fail',
        'data' => null
    );

    echo json_encode($return, JSON_FORCE_OBJECT);
}

?>