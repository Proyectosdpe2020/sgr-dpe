<?php
session_start();
include('connection.php');
$conn = $connections['incidencia_sicap']['conn'];
$db = $connections['incidencia_sicap']['db'];

$data = json_decode($_POST['auth'], true );

$user = $data['username'];
$pass = $data['password'];


if($conn){
    $sql = "SELECT TOP (1) [UsuarioBasesNacionalesID]
                ,[Nombre]
                ,[Paterno]
                ,[Materno]
                ,[Usuario]
                ,[Contrasena]
                ,[Estatus]
            FROM [EJERCICIOS2].[dbo].[UsuariosBasesNacionales]
            WHERE [Usuario] = '$user'
            AND [Contrasena] = '$pass'
            AND Estatus = 1";

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
                    'id' => $json['UsuarioBasesNacionalesID'],
                    'username' => $json['Usuario'],
                    'name' => $json['Nombre'],
                    'paternal_surname' => $json['Paterno'],
                    'maternal_surname' => $json['Materno'],
                    'type' => 1,
                    'permissions' => array(
                        'senap' => 1
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

    sqlsrv_close($conn);
}
?>