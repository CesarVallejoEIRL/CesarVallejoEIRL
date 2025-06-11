<?php
session_start();

include '../database/conexion.php';

$id = $_GET['id'];

$sql = "DELETE FROM grados WHERE id=$id";

if ($conexion->query($sql)) {
    header("Location: grade.php");
    exit();
} else {
    echo "Error al eliminar: " . $conexion->error;
}
?>
