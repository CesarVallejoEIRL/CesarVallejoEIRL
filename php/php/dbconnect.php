<?php
// Datos de conexión
$host = "127.0.0.1";   // Dirección IP del servidor
$user = "root";         // Usuario de la base de datos
$password = "";         // Deja vacío si no tienes contraseña
$database = "cesarvallejo"; // Nombre de la base de datos
$port = 3307;           // Puerto MySQL, asegurate de que sea correcto

// Crear conexión a MySQL
$conexion = new mysqli($host, $user, $password, $database, $port);

// Verificar la conexión
if ($conexion->connect_error) {
    // Si hay un error de conexión, mostrarlo y detener la ejecución
    http_response_code(500); // Error interno del servidor
    echo json_encode([
        "success" => false,
        "error" => "Error al conectar con la base de datos: " . $conexion->connect_error
    ]);
    exit();
}

// Establecer el conjunto de caracteres para evitar problemas con caracteres especiales
$conexion->set_charset("utf8");
?>
