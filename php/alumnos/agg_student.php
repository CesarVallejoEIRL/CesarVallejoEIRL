<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../database/conexion.php';

// Función para obtener el próximo ID disponible
function obtenerProximoID($conexion) {
    $sql_max = "SELECT MAX(id) AS max_id FROM estudiantes";
    $result_max = $conexion->query($sql_max);
    $max_id = $result_max->fetch_assoc()['max_id'];

    if ($max_id === null) return 1;

    $sql_huecos = "SELECT t1.id + 1 AS missing_id
                   FROM estudiantes t1
                   LEFT JOIN estudiantes t2 ON t1.id + 1 = t2.id
                   WHERE t2.id IS NULL AND t1.id < $max_id
                   ORDER BY t1.id
                   LIMIT 1";
    $result_huecos = $conexion->query($sql_huecos);
    if ($result_huecos->num_rows > 0) {
        return $result_huecos->fetch_assoc()['missing_id'];
    }
    return $max_id + 1;
}

$proximo_id = obtenerProximoID($conexion);

// Verificar si el formulario ha sido enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $proximo_id;
    $nombre = $conexion->real_escape_string($_POST['nombre']);
    $contacto = $conexion->real_escape_string($_POST['contacto']);
    $nivel_educativo = $conexion->real_escape_string($_POST['nivel_educativo']);
    $aula = $conexion->real_escape_string($_POST['aula']);
    $seccion = $conexion->real_escape_string($_POST['seccion']);
    $fecha_ingreso = $conexion->real_escape_string($_POST['fecha_ingreso']);
    $pagos = floatval($_POST['pagos']);
    $balance = floatval($_POST['balance']);
    $observaciones = $conexion->real_escape_string($_POST['observaciones']);
    $email = $conexion->real_escape_string($_POST['email']);

    // Validar campos obligatorios
    if (empty($nombre) || empty($contacto) || empty($nivel_educativo) || empty($aula) || empty($seccion) || empty($fecha_ingreso)) {
        $_SESSION['error'] = "Todos los campos obligatorios deben ser completados.";
    } else {
        $sql = "INSERT INTO estudiantes (id, nombre, contacto, nivel_educativo, aula, seccion, fecha_ingreso, pagos, balance, observaciones, email)
                VALUES ('$id', '$nombre', '$contacto', '$nivel_educativo', '$aula', '$seccion', '$fecha_ingreso', '$pagos', '$balance', '$observaciones', '$email')";
        if ($conexion->query($sql) === TRUE) {
            $_SESSION['mensaje'] = "Estudiante agregado correctamente con ID: $id";
            header("Location: student.php");
            exit();
        } else {
            $_SESSION['error'] = "Error al agregar estudiante: " . $conexion->error;
        }
    }
}
?>

<!-- HTML (igual al tuyo, solo se corrigió lo necesario) -->

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Estudiante</title>
    <link rel="icon" type="image/png" href="/php/img/cesar.png">
    <link rel="stylesheet" href="../alumnos/css/agg_student.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <!-- Barra lateral -->
    <nav class="navbar-side">
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

    <!-- Contenido Principal -->
    <div id="content">
        <div class="container">
            <div class="header-container">
                <h2><i class="fas fa-user-edit"></i> Agregar Nuevo Estudiante</h2>
                <a href="student.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>

            <!-- Mensajes -->
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
            <?php if (isset($_SESSION['mensaje'])): ?>
                <div class="alert alert-success"><?= $_SESSION['mensaje'] ?></div>
                <?php unset($_SESSION['mensaje']); ?>
            <?php endif; ?>

            <!-- Formulario -->
            <div class="student-form-container">
                <h3><i class="fas fa-info-circle"></i> Información de Estudiante</h3>
                <form method="POST" action="agg_student.php">
                    <div class="form-group">
                        <label>ID del Estudiante</label>
                        <input type="text" value="<?php echo $proximo_id; ?>" class="form-control" readonly>
                        <small class="form-text text-muted">ID asignado automáticamente</small>
                    </div>
                    <div class="form-section">
                        <h4><i class="fas fa-user"></i> Información Personal</h4>
                        <div class="form-group">
                            <label>Nombre Completo*</label>
                            <input type="text" name="nombre" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Teléfono*</label>
                            <input type="text" name="contacto" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Nivel Educativo*</label>
                            <input type="text" name="nivel_educativo" class="form-control" required placeholder="Ej: Inicial, Primaria, Secundaria">
                        </div>
                        <div class="form-group">
                            <label>Aula*</label>
                            <input type="text" name="aula" class="form-control" required placeholder="Ej: A, B, C">
                        </div>
                        <div class="form-group">
                            <label>Sección*</label>
                            <input type="text" name="seccion" class="form-control" required placeholder="Ej: 1, 2, 3">
                        </div>
                        <div class="form-group">
                            <label>Fecha Ingreso*</label>
                            <input type="date" name="fecha_ingreso" class="form-control" required>
                        </div>
                    </div>
                    <hr>
                    <div class="form-section">
                        <h4><i class="fas fa-money-bill-wave"></i> Información de Pagos</h4>
                        <div class="form-group">
                            <label>Monto Total*</label>
                            <input type="number" step="0.01" name="pagos" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Monto Pendiente</label>
                            <input type="number" step="0.01" name="balance" class="form-control" value="0">
                        </div>
                    </div>
                    <hr>
                    <div class="form-section">
                        <h4><i class="fas fa-info-circle"></i> Información Adicional</h4>
                        <div class="form-group">
                            <label>Observaciones</label>
                            <textarea name="observaciones" class="form-control"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control">
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar
                        </button>
                        <a href="student.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
