<?php
session_start();
include '../php/database/conexion.php';

// Consulta para contar estudiantes activos
$query_estudiantes = "SELECT COUNT(*) as total FROM estudiantes";
$result_estudiantes = $conexion->query($query_estudiantes);
$total_estudiantes = $result_estudiantes->fetch_assoc()['total'];

// Consulta para sumar los pagos (ganancias)
$query_ganancias = "SELECT SUM(pagos) as total FROM estudiantes";
$result_ganancias = $conexion->query($query_ganancias);
$total_ganancias = $result_ganancias->fetch_assoc()['total'];

// Formatear ganancias como dinero
$ganancias_formateadas = '$' . number_format($total_ganancias, 2);

// Devolver datos como JSON
header('Content-Type: application/json');
echo json_encode([
    'estudiantes' => $total_estudiantes,
    'ganancias' => $ganancias_formateadas
]);

$conexion->close();
?>