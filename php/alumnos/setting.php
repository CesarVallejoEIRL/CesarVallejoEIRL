<?php
include 'php/database/conexion.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.html");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cuenta - Cambiar Contraseña</title>
    <link rel="stylesheet" href="../alumnos/setting.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
 <nav class="navbar-default navbar-side">
            <div class="sidebar-collapse">
                <h3 class="sidebar-title">CEBA Cesar Vallejo</h3>
                <ul class="nav" id="main-menu">
                    <li>
                        <div class="user-img-div text-center">
                            <img src="../img/cesar.png" class="img" alt="Logo CEBA" />
                            <h5 style="color:white;"><?php echo htmlspecialchars($_SESSION['usuario']); ?></h5>
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


        <!-- Contenido principal -->
        <div class="content">
            <div class="form-container">
                <div class="form-header">Cambiar Contraseña</div> <!-- Encabezado verde corregido -->
                <form action="cambiar_contraseña.php" method="POST">
                    <label for="current_password">Contraseña Previa</label>
                    <input type="password" id="current_password" name="current_password" required>

                    <label for="new_password">Nueva Contraseña</label>
                    <input type="password" id="new_password" name="new_password" required>

                    <label for="confirm_password">Confirmar Contraseña</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>

                    <button type="submit">Cambiar Contraseña</button>
                </form>
            </div>
        </div>

        <script>
            document.getElementById("logoutBtn").addEventListener("click", function(event) {
                event.preventDefault();
                alert("Cierre de sesión exitoso (simulado).");
                window.location.href = "../login.html";
            });
        </script>
</body>

</html>