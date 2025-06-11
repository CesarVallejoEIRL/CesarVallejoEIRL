<?php
session_start();
include '../database/conexion.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

// Verificar si 'id' está presente y es un valor numérico
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];

    // Actualizar estado de estudiante
    $sql_reactivar = "UPDATE estudiantes SET estado = 'activo', fecha_desactivacion = NULL, motivo_inactividad = NULL WHERE id = ?";
    $stmt = $conexion->prepare($sql_reactivar);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        // Redirigir con un mensaje de éxito
        $_SESSION['mensaje'] = "Estudiante reactivado exitosamente.";
        header("Location: inactivestd.php");
        exit;
    } else {
        $_SESSION['mensaje_error'] = "Error al reactivar el estudiante.";
        header("Location: inactivestd.php");
        exit;
    }
} else {
    $_SESSION['mensaje_error'] = "ID no válido.";
    header("Location: inactivestd.php");
    exit;
}
?>
