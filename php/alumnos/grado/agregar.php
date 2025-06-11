<?php
session_start();
include '../database/conexion.php'; // Incluye la conexión

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];

    $stmt = $conexion->prepare("INSERT INTO grados (nombre, descripcion) VALUES (?, ?)");
    $stmt->bind_param("ss", $nombre, $descripcion);

    if ($stmt->execute()) {
        header("Location: grade.php");
        exit();
    } else {
        echo "Error al insertar: " . $stmt->error;
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Estudiantes</title>
    <link rel="icon" type="/php/img/cesar.png" sizes="16x16" href="/php/img/cesar.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../css/agg.css"> <!-- Archivo CSS externo -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
</head>

<body>
    <!-- Barra lateral -->
    <div id="wrapper">
        <!-- Barra superior -->
        <nav class="navbar navbar-default navbar-cls-top" role="navigation">
            <div class="navbar-header">
                <!-- El estilo se carga correctamente -->
            </div>
        </nav>
        <!-- Barra lateral -->
        <nav class="navbar-default navbar-side">
            <div class="sidebar-collapse">
                <h3 class="sidebar-title">CEBA Cesar Vallejo</h3>

                <ul class="nav" id="main-menu">
                    <li>
                        <div class="user-img-div text-center">
                            <img src="../img/cesar.png" class="img" />
                            <h5 style="color:white;"><?php echo $_SESSION['usuario']; ?></h5> <!-- Mostrar el nombre del usuario -->
                        </div>
                    </li>
                    <li><a href="/php/dashboard.php" class="active-menu">
                            <i class="fa fa-home"></i> Inicio</a></li>
                    <li><a href="/php/alumnos/student.php"><i class="fa fa-users"></i> Estudiantes</a></li>
                    <li><a href="/php/alumnos/inactivestd.php"><i class="fa fa-toggle-off"></i> Estudiantes Inactivos</a></li>
                    <li><a href="/php/alumnos/grade.php"><i class="fa fa-th-large"></i> Grado Escolar</a></li>
                    <li><a href="../php/alumnos/fees.php"><i class="fa fa-credit-card"></i> Pagos</a></li> <!-- Icono de pagos -->
                    <li><a href="/php/alumnos/report.php"><i class="fa fa-file-pdf"></i> Reportes</a></li>
                    <li><a href="/php/alumnos/setting.html"><i class="fa fa-cogs"></i> Cuenta</a></li>
                    <li><a href="/php/logout.php" id="logoutBtn"><i class="fa fa-power-off"></i> Cerrar Sesión</a></li>
                </ul>
            </div>
        </nav>
        <!-- Contenido Principal -->
        <h2>Agregar Nuevo Grado</h2>
        <form method="POST">
            <label>Nombre del Grado:</label><br>
            <input type="text" name="nombre" required><br><br>
            <label>Descripción:</label><br>
            <textarea name="descripcion" rows="4"></textarea><br><br>
            <button type="submit">Guardar</button>
            <a href="grade.php">Cancelar</a>
        </form>



    </div>
</body>

</html>