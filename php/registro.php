<?php
session_start();
include "../php/database/conexion.php";

// Configurar cabeceras para respuesta JSON
header("Content-Type: application/json");
header("X-Content-Type-Options: nosniff");

// Verificar método de solicitud
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["success" => false, "error" => "Método no permitido"]);
    exit();
}

// Obtener datos del cuerpo de la solicitud (JSON)
$input = json_decode(file_get_contents('php://input'), true) ?? [];
$usuario = trim($input["usuario"] ?? '');
$password = trim($input["password"] ?? '');
$email = trim($input["email"] ?? ''); // Campo adicional para email

// Validaciones básicas
$errores = [];

if (empty($usuario)) {
    $errores[] = "El campo usuario es obligatorio";
}

if (empty($password)) {
    $errores[] = "El campo contraseña es obligatorio";
}

if (empty($email)) {
    $errores[] = "El campo email es obligatorio";
}

// Validaciones de formato
if (!empty($usuario) && !preg_match('/^[a-zA-Z0-9_]{4,20}$/', $usuario)) {
    $errores[] = "El usuario debe tener 4-20 caracteres (solo letras, números y _)";
}

if (!empty($password) && strlen($password) < 8) {
    $errores[] = "La contraseña debe tener al menos 8 caracteres";
}

if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errores[] = "El email no tiene un formato válido";
}

// Si hay errores, devolverlos
if (!empty($errores)) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => implode(". ", $errores)]);
    exit();
}

// Verificar si el usuario o email ya existen
$sqlCheck = "SELECT id FROM usuarios WHERE usuario = ? OR email = ?";
$stmtCheck = $conexion->prepare($sqlCheck);

if (!$stmtCheck) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Error en la preparación de la consulta"]);
    exit();
}

$stmtCheck->bind_param("ss", $usuario, $email);
$stmtCheck->execute();
$stmtCheck->store_result();

if ($stmtCheck->num_rows > 0) {
    http_response_code(409);
    echo json_encode(["success" => false, "error" => "El usuario o email ya están registrados"]);
    $stmtCheck->close();
    exit();
}

$stmtCheck->close();

// Encriptar la contraseña
$password_hash = password_hash($password, PASSWORD_BCRYPT, ["cost" => 12]);

// Insertar el nuevo usuario con estado pendiente
$sqlInsert = "INSERT INTO usuarios 
              (usuario, password, email, estado, fecha_registro) 
              VALUES (?, ?, ?, 'pendiente', NOW())";

$stmtInsert = $conexion->prepare($sqlInsert);

if (!$stmtInsert) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Error al preparar la consulta de inserción"]);
    exit();
}

$stmtInsert->bind_param("sss", $usuario, $password_hash, $email);

if ($stmtInsert->execute()) {
    // Registrar en logs
    $ip = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $usuario_id = $conexion->insert_id;
    
    $sqlLog = "INSERT INTO logs_registro (usuario_id, ip, user_agent) VALUES (?, ?, ?)";
    $stmtLog = $conexion->prepare($sqlLog);
    $stmtLog->bind_param("iss", $usuario_id, $ip, $user_agent);
    $stmtLog->execute();
    $stmtLog->close();
    
    // Respuesta exitosa
    echo json_encode([
        "success" => true, 
        "message" => "Registro exitoso. Pendiente de aprobación.",
        "requires_approval" => true
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        "success" => false, 
        "error" => "Error al registrar el usuario",
        "db_error" => $conexion->error
    ]);
}

$stmtInsert->close();
$conexion->close();
?>