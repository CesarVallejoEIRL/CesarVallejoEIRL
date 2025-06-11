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

    // Eliminar al estudiante
    $sql_eliminar = "DELETE FROM estudiantes WHERE id = ?";
    $stmt = $conexion->prepare($sql_eliminar);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        // Redirigir con un mensaje de éxito
        $_SESSION['mensaje'] = "Estudiante eliminado exitosamente.";
        header("Location:  dashboard.php");
        exit;
    } else {
        $_SESSION['mensaje_error'] = "Error al eliminar el estudiante.";
        header("Location: dashboard.php");
        exit;
    }
} else {
    $_SESSION['mensaje_error'] = "ID no válido.";
    header("Location:  dashboard.php");
    exit;
}
?>
