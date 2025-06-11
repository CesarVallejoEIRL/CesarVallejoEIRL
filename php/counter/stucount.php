<?php
include "../php/database/conexion.php"; // Verifica la ruta de conexión

$query = "SELECT COUNT(*) AS total FROM estudiantes";
$result = $conexion->query($query);

if ($result) {
    $row = $result->fetch_assoc();
    echo json_encode(["total_estudiantes" => $row['total']]);
} else {
    echo json_encode(["error" => "Error al contar estudiantes"]);
}
?>
