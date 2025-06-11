<?php
header('Content-Type: application/json; charset=utf-8');

// Parámetros DB
$host = "127.0.0.1";
$user = "root";
$password = "";
$database = "cesarvallejo";
$port = 3307;

// Crear conexión
$conexion = new mysqli($host, $user, $password, $database, $port);
if ($conexion->connect_error) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Error al conectar con la base de datos: " . $conexion->connect_error
    ]);
    exit();
}
$conexion->set_charset("utf8");

// Obtener datos POST y sanitizar (usando mysqli real_escape_string para seguridad)
$dni = isset($_POST['dni']) ? $conexion->real_escape_string(trim($_POST['dni'])) : '';
$nombre = isset($_POST['nombre']) ? $conexion->real_escape_string(trim($_POST['nombre'])) : '';
$fecha = isset($_POST['fecha']) ? $conexion->real_escape_string(trim($_POST['fecha'])) : '';
$pagos = isset($_POST['pagos']) ? $_POST['pagos'] : ''; // JSON string, validar luego
$observaciones = isset($_POST['observaciones']) ? $conexion->real_escape_string(trim($_POST['observaciones'])) : '';

// Validar campos obligatorios
if (empty($dni) || empty($nombre) || empty($fecha) || empty($pagos)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Faltan datos obligatorios."
    ]);
    exit();
}

// Validar que $pagos sea JSON válido
if (!json_decode($pagos)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Formato inválido en pagos."
    ]);
    exit();
}

// Aquí puedes decidir si insertar o actualizar. 
// Por ejemplo, actualizar si ya existe registro con ese dni y fecha, sino insertar:

// Verificar si ya existe el reporte para este dni y fecha
$sql_check = "SELECT COUNT(*) as count FROM reportes WHERE dni = '$dni' AND fecha = '$fecha'";
$result = $conexion->query($sql_check);
$row = $result->fetch_assoc();
$exists = $row['count'] > 0;

if ($exists) {
    // Actualizar reporte
    $sql_update = "UPDATE reportes SET 
        nombre = '$nombre',
        pagos = '$pagos',
        observaciones = '$observaciones',
        validado = 1
        WHERE dni = '$dni' AND fecha = '$fecha'";

    if ($conexion->query($sql_update)) {
        echo json_encode([
            "success" => true,
            "message" => "Reporte actualizado correctamente."
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Error al actualizar reporte: " . $conexion->error
        ]);
    }

} else {
    // Insertar nuevo reporte
    $sql_insert = "INSERT INTO reportes (dni, nombre, fecha, pagos, observaciones, validado) VALUES (
        '$dni', '$nombre', '$fecha', '$pagos', '$observaciones', 1
    )";

    if ($conexion->query($sql_insert)) {
        echo json_encode([
            "success" => true,
            "message" => "Reporte insertado correctamente."
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Error al insertar reporte: " . $conexion->error
        ]);
    }
}

$conexion->close();
?>
