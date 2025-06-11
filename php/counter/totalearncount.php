<?php
include "../php/database/conexion.php"; // Asegura que la ruta sea correcta

$query = "SELECT SUM(monto) AS total_ingresos FROM pagos";
$result = $conexion->query($query);

if ($result) {
    $row = $result->fetch_assoc();
    echo json_encode(["total_ingresos" => $row['total_ingresos'] ?? 0]);
} else {
    echo json_encode(["error" => "Error al calcular ingresos"]);
}
?>
