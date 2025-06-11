<?php
include("/php/php/dbconnect.php");

$sql = "SELECT * FROM estudiantes WHERE activo = '1'";
$query = $conexion->query($sql);

if ($query !== false) {
    echo $query->num_rows;
} else {
    echo "Error en la consulta: " . $conexion->error;
}
?>
