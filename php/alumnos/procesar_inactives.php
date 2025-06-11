<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

include '../database/conexion.php';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['id']) && isset($_POST['motivo'])) {
    $id = intval($_POST['id']); // Seguridad: forzar a entero
    $motivo = trim($conexion->real_escape_string($_POST['motivo'])); // Evitar inyecciones

    // Consulta preparada para desactivar estudiante
    $sql = "UPDATE estudiantes 
            SET estado = 'inactivo', 
                motivo_inactividad = ?, 
                fecha_desactivacion = NOW() 
            WHERE id = ?";

    if ($stmt = $conexion->prepare($sql)) {
        $stmt->bind_param("si", $motivo, $id);

        if ($stmt->execute()) {
            $_SESSION['mensaje'] = "Estudiante marcado como inactivo correctamente.";
        } else {
            $_SESSION['mensaje_error'] = "Error al ejecutar la consulta: " . $stmt->error;
        }

        $stmt->close();
    } else {
        $_SESSION['mensaje_error'] = "Error al preparar la consulta SQL.";
    }

    header("Location: inactivestd.php");
    exit;
} else {
    $_SESSION['mensaje_error'] = "Solicitud inválida o incompleta.";
    header("Location: inactivestd.php");
    exit;
}
