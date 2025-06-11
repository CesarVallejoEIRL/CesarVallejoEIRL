<?php
session_start();
include '../php/database/conexion.php';

// Verificar ID válido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "ID de estudiante no válido.";
    header("Location: /php/alumnos/student.php");
    exit();
}

$id = intval($_GET['id']);

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $contacto = $_POST['contacto'];
    $nivel_educativo = $_POST['nivel_educativo'];
    $aula = $_POST['aula'];
    $seccion = $_POST['seccion'];
    $pagos = floatval($_POST['pagos']);
    $balance = floatval($_POST['balance']);
    $fecha_ingreso = $_POST['fecha_ingreso'];
    $email = $_POST['email'] ?? '';
    $observaciones = $_POST['observaciones'] ?? '';

    if (empty($nombre) || empty($contacto) || empty($nivel_educativo) || empty($aula) || empty($seccion) || empty($fecha_ingreso) || empty($pagos)) {
        $_SESSION['error'] = "Todos los campos obligatorios deben ser completados.";
    } else {
        $sql_update = "UPDATE estudiantes SET 
                        nombre = ?, 
                        contacto = ?, 
                        nivel_educativo = ?, 
                        aula = ?,
                        seccion = ?,
                        fecha_ingreso = ?,
                        pagos = ?, 
                        balance = ?, 
                        email = ?, 
                        observaciones = ? 
                        WHERE id = ?";
        $stmt = $conexion->prepare($sql_update);
        $stmt->bind_param(
            "ssssssddssi",
            $nombre,
            $contacto,
            $nivel_educativo,
            $aula,
            $seccion,
            $fecha_ingreso,
            $pagos,
            $balance,
            $email,
            $observaciones,
            $id
        );

        // Reemplazar este fragmento
        if ($stmt->execute()) {
            $_SESSION['mensaje'] = "Estudiante actualizado correctamente";
            header("Location: editar.php?id=$id");
            exit();
        } else {
            $_SESSION['error'] = "Error al actualizar: " . $stmt->error;  // Mostrar el error específico del statement
        }

        // Si la conexión a la base de datos está fallando, puedes revisar este mensaje
        if ($conexion->connect_error) {
            die("Error de conexión a la base de datos: " . $conexion->connect_error);
        }
    }
}

// Consulta de datos actualizados o iniciales
$sql = "SELECT id, nombre, contacto, nivel_educativo, aula, seccion, fecha_ingreso, pagos, balance, email, observaciones 
        FROM estudiantes 
        WHERE id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows == 0) {
    $_SESSION['error'] = "Estudiante no encontrado.";
    header("Location: /php/alumnos/student.php");
    exit();
}
$fila = $resultado->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Estudiantes CEBA Cesar Vallejo</title>
    <link rel="stylesheet" href="../php/css/editar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div id="wrapper">
        <nav class="navbar navbar-default navbar-cls-top" role="navigation"></nav>
        <nav class="navbar-default navbar-side">
            <div class="sidebar-collapse">
                <h3 class="sidebar-title">CEBA Cesar Vallejo</h3>
                <ul class="nav" id="main-menu">
                    <li>
                        <div class="user-img-div text-center">
                            <img src="img/cesar.png" class="img" />
                            <h5 style="color:white;"><?php echo $_SESSION['usuario']; ?></h5>
                        </div>
                    </li>
                    <li><a href="dashboard.php" class="active-menu"><i class="fa fa-home"></i> Inicio</a></li>
                    <li><a href="/php/alumnos/student.php"><i class="fa fa-users"></i> Estudiantes</a></li>
                    <li><a href="/php/alumnos/inactivestd.php"><i class="fa fa-toggle-off"></i> Estudiantes Inactivos</a></li>
                    <li><a href="/php/alumnos/grade.php"><i class="fa fa-th-large"></i> Grado Escolar</a></li>
                    <li><a href="/php/alumnos/fees.php"><i class="fa fa-credit-card"></i> Pagos</a></li>
                    <li><a href="/php/alumnos/report.php"><i class="fa fa-file-pdf"></i> Reportes</a></li>
                    <li><a href="/php/alumnos/setting.html"><i class="fa fa-cogs"></i> Cuenta</a></li>
                    <li><a href="../login.html" id="logoutBtn"><i class="fa fa-power-off"></i> Cerrar Sesión</a></li>
                </ul>
            </div>
        </nav>

        <div id="content">
            <div class="container">
                <div class="header-container">
                    <h2><i class="fas fa-user-edit"></i> Gestionar Estudiantes</h2>
                    <div class="header-actions">
                        <a href="../php/alumnos/student.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Volver</a>
                    </div>
                </div>
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger" id="alerta"><?= htmlspecialchars($_SESSION['error']) ?></div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>
                <?php if (isset($_SESSION['mensaje'])): ?>
                    <div class="alert alert-success" id="alerta"><?= htmlspecialchars($_SESSION['mensaje']) ?></div>
                    <?php unset($_SESSION['mensaje']); ?>
                <?php endif; ?>
                <div class="student-form-container">
                    <h3><i class="fas fa-info-circle"></i> Editor Información de Estudiante</h3>
                    <form method="POST">
                        <div class="form-section">
                            <h4><i class="fas fa-user"></i> Información Personal</h4>
                            <div class="form-group">
                                <label>Nombre Completo*</label>
                                <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($fila['nombre'] ?? '') ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Teléfono*</label>
                                <input type="text" name="contacto" class="form-control" value="<?= htmlspecialchars($fila['contacto'] ?? '') ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Nivel Educativo*</label>
                                <input type="text" name="nivel_educativo" class="form-control" value="<?= htmlspecialchars($fila['nivel_educativo'] ?? '') ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Aula*</label>
                                <input type="text" name="aula" class="form-control" value="<?= htmlspecialchars($fila['aula'] ?? '') ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Sección*</label>
                                <input type="text" name="seccion" class="form-control" value="<?= htmlspecialchars($fila['seccion'] ?? '') ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Fecha Ingreso*</label>
                                <?php
                                $fecha_ingreso = ($fila['fecha_ingreso'] == '0000-00-00') ? date('Y-m-d') : $fila['fecha_ingreso'];
                                ?>
                                <input type="date" name="fecha_ingreso" class="form-control" value="<?= htmlspecialchars($fecha_ingreso ?? '') ?>" required>
                            </div>
                        </div>
                        <hr>
                        <div class="form-section">
                            <h4><i class="fas fa-money-bill-wave"></i> Información de Pagos</h4>
                            <div class="form-group">
                                <label>Monto Total*</label>
                                <input type="number" step="0.01" name="pagos" class="form-control" value="<?= htmlspecialchars($fila['pagos'] ?? 0) ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Monto Pendiente</label>
                                <input type="number" step="0.01" name="balance" class="form-control" value="<?= htmlspecialchars($fila['balance'] ?? 0) ?>">
                            </div>
                        </div>
                        <hr>
                        <div class="form-section">
                            <h4><i class="fas fa-info-circle"></i> Información Adicional</h4>
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($fila['email'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label>Observaciones</label>
                                <textarea name="observaciones" class="form-control"><?= htmlspecialchars($fila['observaciones'] ?? '') ?></textarea>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Guardar
                            </button>
                            <a href="/php/alumnos/student.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Script para ocultar alertas -->
    <script>
        setTimeout(() => {
            const alerta = document.getElementById("alerta");
            if (alerta) {
                alerta.style.transition = "opacity 0.5s ease-out";
                alerta.style.opacity = 0;
                setTimeout(() => alerta.remove(), 500);
            }
        }, 3000);
    </script>
</body>
</html>
