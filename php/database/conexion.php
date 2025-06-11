<?php
$host = "127.0.0.1";
$user = "root";
$password = ""; 
$database = "cesarvallejo";
$port = 3307; // Puerto MySQL

$conexion = new mysqli($host, $user, $password, $database, $port);

if ($conexion->connect_error) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "Error al conectar con la base de datos: " . $conexion->connect_error
    ]);
    exit();
}

$conexion->set_charset("utf8");
?>
