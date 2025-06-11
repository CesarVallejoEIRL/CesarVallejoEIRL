<?php
session_start();
header('Content-Type: application/json');

// Verificar autenticación
if (!isset($_SESSION['usuario'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

include '../database/conexion.php';

// Verificar conexión a la base de datos
if (!$conexion) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión a la base de datos']);
    exit;
}

// Obtener parámetros de filtrado (si existen)
$fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : null;
$fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : null;
$estudiante_id = isset($_GET['estudiante_id']) ? (int)$_GET['estudiante_id'] : null;

// Construir consulta base
$query = "SELECT e.id as estudiante_id, e.nombre, e.contacto, e.nivel_educativo,
                 p.id as pago_id, p.concepto, p.fecha_pago, p.monto, p.metodo_pago,
                 b.fecha_emision as fecha_generacion, b.observaciones, b.id as boleta_id
          FROM estudiantes e
          JOIN pagos p ON e.id = p.alumno_id
          JOIN boletas b ON p.id = b.id_pago
          WHERE e.delete_status = 0 
          AND p.estado = 'Validado'";

// Añadir condiciones de filtrado
if ($estudiante_id) {
    $query .= " AND e.id = $estudiante_id";
}

if ($fecha_inicio && $fecha_fin) {
    $query .= " AND b.fecha_emision BETWEEN '$fecha_inicio' AND '$fecha_fin 23:59:59'";
} elseif ($fecha_inicio) {
    $query .= " AND b.fecha_emision >= '$fecha_inicio'";
} elseif ($fecha_fin) {
    $query .= " AND b.fecha_emision <= '$fecha_fin 23:59:59'";
}

$query .= " ORDER BY b.fecha_emision DESC";

$result = mysqli_query($conexion, $query);

if ($result === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en consulta de historial: ' . mysqli_error($conexion)]);
    exit;
}

$historial = [];
while ($row = mysqli_fetch_assoc($result)) {
    $historial[] = [
        'boleta_id' => $row['boleta_id'],
        'estudiante' => [
            'id' => $row['estudiante_id'],
            'nombre' => $row['nombre'],
            'contacto' => $row['contacto'],
            'nivel_educativo' => $row['nivel_educativo']
        ],
        'pago' => [
            'id' => $row['pago_id'],
            'concepto' => $row['concepto'],
            'fecha_pago' => $row['fecha_pago'],
            'monto' => $row['monto'],
            'metodo_pago' => $row['metodo_pago']
        ],
        'boleta' => [
            'fecha_generacion' => $row['fecha_generacion'],
            'observaciones' => $row['observaciones']
        ]
    ];
}

echo json_encode([
    'success' => true,
    'total' => count($historial),
    'data' => $historial
]);

mysqli_close($conexion);
?>