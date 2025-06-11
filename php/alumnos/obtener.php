<?php
include '../php/database/conexion.php';

$sql = "SELECT * FROM alumnos ORDER BY nombre ASC";
$result = $conexion->query($sql);

$alumnos = [];
while ($row = $result->fetch_assoc()) {
    $alumnos[] = $row;
}

echo json_encode($alumnos);
$conexion->close();
?>
