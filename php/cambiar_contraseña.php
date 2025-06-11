<?php
include '../php/database/conexion.php'; // Ajusta la ruta si es necesario
session_start();

if (!isset($_SESSION['usuario_id'])) {
    die("Error: No has iniciado sesión.");
}

$user_id = $_SESSION['usuario_id'];
$current_password = $_POST['current_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Verificar que los campos no estén vacíos
if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
    die("Error: Todos los campos son obligatorios.");
}

// Verificar que las nuevas contraseñas coincidan
if ($new_password !== $confirm_password) {
    die("Error: Las nuevas contraseñas no coinciden.");
}

// Obtener la contraseña actual del usuario
$sql = "SELECT password FROM usuarios WHERE id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->bind_result($hashed_password);
    $stmt->fetch();

    // Verificar si la contraseña actual ingresada es correcta
    if (password_verify($current_password, $hashed_password)) {
        // Encriptar la nueva contraseña
        $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // Actualizar la contraseña en la base de datos
        $update_sql = "UPDATE usuarios SET password = ? WHERE id = ?";
        $update_stmt = $conexion->prepare($update_sql);
        $update_stmt->bind_param("si", $new_hashed_password, $user_id);

        if ($update_stmt->execute()) {
            session_destroy(); // Cierra la sesión actual
            header("Location: ../php/login.html"); // Redirige al usuario a la página de inicio de sesión
            exit();
            // Asegura que el script se detenga 
        } else {
            echo "Error: No se pudo actualizar la contraseña.";
        }
    } else {
        echo "Error: La contraseña actual es incorrecta.";
    }
} else {
    echo "Error: Usuario no encontrado.";
}

$stmt->close();
$conexion->close();
?>
