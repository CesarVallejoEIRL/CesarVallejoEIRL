<?php
session_start();  // Iniciar la sesión

// Incluir la conexión a la base de datos
include '../php/database/conexion.php';  // Ajusta la ruta si es necesario

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");  // Redirigir al login si no está autenticado
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CEBA - Panel de Control</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../php/css/dashboard.css?<?php echo time(); ?>"> <!-- Corregido -->
</head>
<body>
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
                            <img src="img/cesar.png" class="img" />
                            <h5 style="color:white;"><?php echo $_SESSION['usuario']; ?></h5> <!-- Mostrar el nombre del usuario -->
                        </div>
                    </li>
                    <li><a href="dashboard.php" class="active-menu">
                            <i class="fa fa-home"></i> Inicio</a></li>
                    <li><a href="/php/alumnos/student.php"><i class="fa fa-users"></i> Estudiantes</a></li>
                    <li><a href="/php/alumnos/inactivestd.php"><i class="fa fa-toggle-off"></i> Estudiantes Inactivos</a></li>
                    <li><a href="/php/alumnos/grade.php"><i class="fa fa-th-large"></i> Grado Escolar</a></li>
                    <li><a href="../php/alumnos/fees.php"><i class="fa fa-credit-card"></i> Pagos</a></li> <!-- Icono de pagos -->
                    <li><a href="/php/alumnos/report.php"><i class="fa fa-file-pdf"></i> Reportes</a></li>
                    <li><a href="/php/alumnos/setting.html"><i class="fa fa-cogs"></i> Cuenta</a></li>
                    <li><a href="logout.php" id="logoutBtn"><i class="fa fa-power-off"></i> Cerrar Sesión</a></li>
                </ul>
            </div>
        </nav>

        <!-- Contenido Principal -->
        <div id="content">
            <div id="page-wrapper">
                <h2 class="title">Panel de Control</h2>
                <div class="dashboard">
                    <div class="card" onclick="window.location.href='/php/alumnos/student.php'">
                        <img src="img/graduado.png" alt="Estudiantes" class="icono-estudiantes">
                        <h3>Estudiantes <span id="total-estudiantes" class="counter">0</span></h3>
                    </div>

                    <div class="card" onclick="window.location.href='/php/alumnos/fees.php'">
                        <img src="img/salario.png" alt="Ganancias" class="icono-ganancias">
                        <h3>Ganancias <span id="total-ganancias" class="counter">$0.00</span></h3>
                    </div>

                    <div class="card" onclick="window.location.href='/php/alumnos/report.php'">
                        <img src="img/expediente.png" alt="Reportes" class="icono-reporte">
                        <h3>REPORTES</h3>
                    </div>
                </div>

                <h3>Lista de Estudiantes</h3>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nombre | Contacto</th>
                            <th>Nivel Educativo</th>
                            <th>Aula</th>
                            <th>Seccion</th>
                            <th>Fecha Ingreso</th>
                            <th>Pagos</th>
                            <th>Balance</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Obtener los estudiantes de la base de datos
                        $sql = "SELECT id, nombre, contacto, nivel_educativo, aula,seccion, fecha_ingreso, pagos, balance FROM estudiantes";
                        $resultado = $conexion->query($sql);

                        if ($resultado->num_rows > 0) {
                            while ($fila = $resultado->fetch_assoc()) {
                                echo "<tr>
                                        <td>{$fila['id']}</td>
                                        <td>{$fila['nombre']} <br> {$fila['contacto']}</td>
                                        <td>{$fila['nivel_educativo']}</td>
                                        <td>{$fila['aula']}</td>
                                        <td>{$fila['seccion']}</td>
                                        <td>{$fila['fecha_ingreso']}</td>
                                        <td>{$fila['pagos']}</td>
                                        <td>{$fila['balance']}</td>
                                        <td class='action-buttons'>
                                            <a href='editar.php?id={$fila['id']}' class='btn btn-primary'>
                                                <img src='img/alinear-texto.png' alt='Editar' width='20'> 
                                            </a>
                                            <a href='../php/alumnos/eliminar_estudiante.php?id={$fila['id']}' class='btn btn-danger' 
                                               onclick='return confirm(\"¿Estás seguro de eliminar este estudiante?\");'>
                                                <img src='img/eliminar.png' alt='Eliminar' width='20'> 
                                            </a>
                                        </td>
                                    </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='7'>No hay estudiantes registrados.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <script>
            // Agregar favicon dinámicamente
            const link = document.createElement('link');
            link.rel = 'icon';
            link.type = 'image/png';
            link.href = '/php/img/cesar.png';
            document.head.appendChild(link);

            // Logout con AJAX
            document.getElementById("logoutBtn").addEventListener("click", function(event) {
                event.preventDefault();

                fetch("logout.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json"
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.location.href = data.redirect;
                        }
                    })
                    .catch(error => console.error("Error al cerrar sesión:", error));
            });

            // Cargar los datos del dashboard (cantidad de estudiantes y ganancias)
            fetch('get_dashboard_data.php?nocache=' + new Date().getTime()) // Evita caché
                .then(response => response.json())
                .then(data => {
                    console.log("Datos recibidos:", data); // Verifica qué devuelve el servidor
                    if (data.estudiantes) {
                        document.getElementById('total-estudiantes').textContent = data.estudiantes;
                    } else {
                        console.error("Error en los datos:", data);
                    }

                    if (data.ganancias) {
                        document.getElementById('total-ganancias').textContent = data.ganancias;
                    } else {
                        console.error("Error en las ganancias:", data);
                    }
                })
                .catch(error => console.error('Error al cargar los datos del dashboard:', error));
        </script>

        <!-- Scripts de jQuery y Bootstrap -->
        <!-- jQuery -->
        <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>

        <!-- DataTables CSS -->
        <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">

        <!-- DataTables JS -->
        <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
        <script src="login.js"></script>
    </div>
</body>

</html>