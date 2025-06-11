<?php
session_start();
include "../php/database/conexion.php";

// Habilitar errores solo en desarrollo
if (isset($_SERVER['APPLICATION_ENV']) && $_SERVER['APPLICATION_ENV'] === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);  // Deshabilitar errores en producción
}

// Configurar la respuesta como JSON
header("Content-Type: application/json");

// Obtener los datos de la solicitud
$input = file_get_contents("php://input");
$data = json_decode($input, true);

// Validación de los campos de entrada
$usuario = isset($data["usuario"]) ? trim($data["usuario"]) : (isset($_POST["usuario"]) ? trim($_POST["usuario"]) : "");
$password = isset($data["password"]) ? trim($data["password"]) : (isset($_POST["password"]) ? trim($_POST["password"]) : "");

// Validar que los campos no estén vacíos
if (empty($usuario) || empty($password)) {
    echo json_encode(["success" => false, "error" => "El usuario o la contraseña están vacíos"]);
    exit();
}

// Preparar la consulta SQL
$sql = "SELECT id, usuario, password FROM usuarios WHERE usuario = ?";
$stmt = $conexion->prepare($sql);

if (!$stmt) {
    error_log("Error en la preparación de la consulta: " . $conexion->error);
    echo json_encode(["success" => false, "error" => "Error en la consulta de la base de datos"]);
    exit();
}

// Ejecutar la consulta
$stmt->bind_param("s", $usuario);
$stmt->execute();
$result = $stmt->get_result();

// Verificar si el usuario existe
if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();

    // Verificar la contraseña
    if (password_verify($password, $user["password"])) {
        // Regenerar ID de sesión por seguridad
        session_regenerate_id(true);

        // Guardar el nombre de usuario y ID en la sesión
        $_SESSION["usuario"] = $user["usuario"];
        $_SESSION["usuario_id"] = $user["id"];

        // Redirigir al dashboard
        echo json_encode(["success" => true, "redirect" => "dashboard.php"]);
    } else {
        echo json_encode(["success" => false, "error" => "Usuario o contraseña incorrectos"]);
    }
} else {
    echo json_encode(["success" => false, "error" => "Usuario o contraseña incorrectos"]);
}

// Cerrar conexiones
$stmt->close();
$conexion->close();
?>
