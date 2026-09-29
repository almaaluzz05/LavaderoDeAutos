<?php
$servidor = "localhost";
$usuario = "root";
$password = "";
$base_de_datos ="lavaderodeautos";

$conn = new mysqli( $servidor, $usuario, $password, $base_de_datos);

if ($conn->connect_error){
    die("Error de conexion: " .
$conn->connect_error);
}

$conn->set_charset("utf8");
echo "conexion exitosa a la base de datos";

?>