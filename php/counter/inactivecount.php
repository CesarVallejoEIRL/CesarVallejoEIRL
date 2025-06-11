<?php
include("/php/php/dbconnect.php");

$sql = "SELECT * FROM estudiantes WHERE activo = '0'";
$query = $conexion->query($sql);

if ($query !== false) {
    echo $query->num_rows;
} else {
    echo "Error en la consulta: " . $conexion->error;
}
?>
