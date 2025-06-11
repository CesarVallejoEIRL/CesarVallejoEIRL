<?php
session_start();
include '../database/conexion.php';  // Conexión a la base de datos
// Verificar que el usuario está autenticado
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}
$current_page = basename($_SERVER['PHP_SELF']);
// Verificar si hay un ID en la URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int) $_GET['id']; // Sanitizar el ID
    // Marcar el estudiante como inactivo
    $sql_inactivar = "UPDATE estudiantes SET estado = 'inactivo', fecha_desactivacion = NOW() WHERE id = ?";
    $stmt = $conexion->prepare($sql_inactivar);
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $_SESSION['mensaje'] = "Estudiante marcado como inactivo.";
        header("Location: inactivestd.php");
        exit;
    } else {
        $_SESSION['mensaje_error'] = "Error al marcar el estudiante como inactivo.";
        header("Location: inactivestd.php");
        exit;
    }
}

// Obtener estudiantes inactivos
$sql_inactivos = "SELECT * FROM estudiantes WHERE estado = 'inactivo'";
$result_inactivos = $conexion->query($sql_inactivos);
// Contar total de estudiantes
$sql_count = "SELECT COUNT(*) AS total_estudiantes FROM estudiantes";
$result_count = $conexion->query($sql_count);
$total_estudiantes = ($result_count->num_rows > 0) ? $result_count->fetch_assoc()['total_estudiantes'] : 0;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="/php/img/cesar.png" sizes="16x16" href="/php/img/cesar.png">
    <title>Estudiantes Inactivos</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../alumnos/css/inactivestd.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
</head>

<body>
    <div id="wrapper">
        <!-- Barra superior -->
        <nav class="navbar navbar-default navbar-cls-top" role="navigation">
            <div class="navbar-header"></div>
        </nav>
        <!-- Barra lateral -->
        <nav class="navbar-default navbar-side">
            <div class="sidebar-collapse">
                <h3 class="sidebar-title">CEBA Cesar Vallejo</h3>
                <ul class="nav" id="main-menu">
                    <li>
                        <div class="user-img-div text-center">
                            <img src="../img/cesar.png" class="img" />
                            <h5 style="color:white;"><?php echo $_SESSION['usuario']; ?></h5>
                        </div>
                    </li>
                    <li><a href="../dashboard.php"><i class="fa fa-home"></i> Inicio</a></li>
                    <li><a href="../alumnos/student.php" class="active-menu"><i class="fa fa-users"></i> Estudiantes</a></li>
                    <li><a href="../alumnos/inactivestd.php"><i class="fa fa-toggle-off"></i> Estudiantes Inactivos</a></li>
                    <li><a href="../alumnos/grade.php"><i class="fa fa-th-large"></i> Grado Escolar</a></li>
                    <li><a href="../alumnos/fees.php"><i class="fa fa-credit-card"></i> Pagos</a></li>
                    <li><a href="../alumnos/report.php"><i class="fa fa-file-pdf"></i> Reportes</a></li>
                    <li><a href="../alumnos/setting.html"><i class="fa fa-cogs"></i> Cuenta</a></li>
                    <li><a href="../login.html" id="logoutBtn"><i class="fa fa-power-off"></i> Cerrar Sesión</a></li>
                </ul>
            </div>
        </nav>

        <div class="container">
            <h2>Estudiantes Inactivos</h2>

            <!-- Mostrar mensajes de éxito o error -->
            <?php
            if (isset($_SESSION['mensaje'])) {
                echo "<div class='alert success'>" . $_SESSION['mensaje'] . "</div>";
                unset($_SESSION['mensaje']);
            }

            if (isset($_SESSION['mensaje_error'])) {
                echo "<div class='alert error'>" . $_SESSION['mensaje_error'] . "</div>";
                unset($_SESSION['mensaje_error']);
            }
            ?>

            <!-- Tarjeta elegante para el subtítulo -->
            <div class="card">
                <p>Gestionar Estudiantes Inactivos</p>
            </div>

            <table id="tabla_inactivos">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Nivel Educativo</th>
                        <th>Aula</th>
                        <th>Seccion</th>
                        <th>Motivo de Inactividad</th>
                        <th>Fecha de Desactivación</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result_inactivos->num_rows > 0) {
                        while ($row = $result_inactivos->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . $row['id'] . "</td>";
                            echo "<td>" . $row['nombre'] . "</td>";
                            echo "<td>" . $row['nivel_educativo'] . "</td>";
                            echo "<td>" . $row['aula'] . "</td>";
                            echo "<td>" . $row['seccion'] . "</td>";
                            echo "<td>" . $row['motivo_inactividad'] . "</td>";
                            echo "<td>" . $row['fecha_desactivacion'] . "</td>";
                            echo "<td class='acciones-td'>
                                    <a href='reactivar_estudiantes.php?id=" . $row['id'] . "' class='btn reactivar-btn'>
                                        <i class='fas fa-undo-alt'></i> Reactivar
                                    </a>
                                    <a href='eliminar_estudiante.php?id=" . $row['id'] . "' class='btn eliminar-btn' onclick='return confirm(\"¿Estás seguro de eliminar al estudiante?\")'>
                                        <i class='fas fa-trash-alt'></i> Eliminar
                                    </a>
                                </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6'>No hay estudiantes inactivos</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
     <!-- Agregar jQuery antes de tu script -->
     <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Ahora carga el script de DataTables y luego student.js -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>

    <!-- Finalmente, el script de student.js -->
    <script src="../alumnos/js/student.js"></script>
</body>
</html>