<?php
session_start();
include '../database/conexion.php';

$id = $_GET['id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];

    $sql = "UPDATE grados SET nombre='$nombre', descripcion='$descripcion' WHERE id=$id";
    if ($conexion->query($sql)) {
        header("Location: grade.php");
        exit();
    } else {
        echo "Error al actualizar: " . $conexion->error;
    }
}

$sql = "SELECT * FROM grados WHERE id=$id";
$result = $conexion->query($sql);
$grado = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grado Escolar</title>
    <link rel="icon" type="/php/img/cesar.png" sizes="16x16" href="/php/img/cesar.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../css/grade.css"> <!-- Archivo CSS externo -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
</head>
<body>
    <h2>Editar Grado</h2>
    <form method="POST">
        <label>Nombre:</label><br>
        <input type="text" name="nombre" value="<?php echo $grado['nombre']; ?>" required><br><br>
        <label>Descripción:</label><br>
        <textarea name="descripcion" rows="4"><?php echo $grado['descripcion']; ?></textarea><br><br>
        <button type="submit">Actualizar</button>
        <a href="grade.php">Cancelar</a>
    </form>
</body>
</html>
