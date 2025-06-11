<?php
session_start();
include '../database/conexion.php';

// Verificar conexión
if (!$conexion) {
    die(json_encode(['error' => 'Error de conexión a la base de datos']));
}

// Consulta para obtener ganancias totales de pagos validados
$query = "SELECT SUM(monto) as total_ganancias 
          FROM pagos 
          WHERE estado = 'Validado'";

$result = mysqli_query($conexion, $query);

if ($result) {
    $row = mysqli_fetch_assoc($result);
    $ganancias = number_format($row['total_ganancias'], 2);
    echo json_encode(['ganancias' => $ganancias]);
} else {
    echo json_encode(['error' => 'Error al calcular ganancias']);
}

mysqli_close($conexion);
?>