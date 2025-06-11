<?php
session_start();
include '../database/conexion.php'; // Incluir la conexión a la base de datos

// Verificar si el parámetro 'id' está presente en la URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];

    // Preparar la consulta SQL para eliminar al estudiante
    $sql = "DELETE FROM estudiantes WHERE id = ?";

    // Preparar la declaración
    if ($stmt = $conexion->prepare($sql)) {
        // Vincular el parámetro
        $stmt->bind_param("i", $id);

        // Ejecutar la consulta
        if ($stmt->execute()) {
            // Redirigir al listado de estudiantes con un mensaje de éxito
            $_SESSION['mensaje'] = "Estudiante eliminado correctamente";
            header("Location: student.php");
            exit();
        } else {
            // Error en la eliminación
            $_SESSION['error'] = "Error al eliminar el estudiante: " . $conexion->error;
            header("Location: student.php");
            exit();
        }
    } else {
        // Error al preparar la consulta
        $_SESSION['error'] = "Error en la preparación de la consulta";
        header("Location: student.php");
        exit();
    }
} else {
    // Si no se encuentra el ID, redirigir con mensaje de error
    $_SESSION['error'] = "ID no válido";
    header("Location: student.php");
    exit();
}
?>
