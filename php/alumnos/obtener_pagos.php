<?php
session_start();
if (!isset($_SESSION['usuario']) || empty($_POST['id_estudiante']) || $_POST['_token'] !== $_SESSION['csrf_token']) {
    header('HTTP/1.1 403 Forbidden');
    exit;
}

include '../database/conexion.php';

function clean_input($data) {
    return trim(htmlspecialchars($data, ENT_QUOTES, 'UTF-8'));
}

$id_estudiante = clean_input($_POST['id_estudiante']);

// Consulta para obtener los pagos del estudiante
$query = "SELECT fecha_pago, concepto, metodo_pago, monto, estado 
          FROM Pagos 
          WHERE alumno_id = ? AND estado = 'Validado'
          ORDER BY fecha_pago DESC";
$stmt = mysqli_prepare($conexion, $query);
mysqli_stmt_bind_param($stmt, 'i', $id_estudiante);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$pagos = [];
$total = 0;

while ($row = mysqli_fetch_assoc($result)) {
    $pagos[] = [
        'fecha' => date('d/m/Y', strtotime($row['fecha_pago'])),
        'concepto' => $row['concepto'],
        'metodo' => $row['metodo_pago'],
        'monto' => number_format($row['monto'], 2),
        'estado' => $row['estado']
    ];
    $total += $row['monto'];
}

header('Content-Type: application/json');
echo json_encode([
    'pagos' => $pagos,
    'total' => number_format($total, 2)
]);

mysqli_close($conexion);
?>