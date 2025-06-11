<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: /php/login.php");
    exit();
}
include '../database/conexion.php';
$current_page = basename($_SERVER['PHP_SELF']);

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
    <title>Gestionar Estudiantes</title>
    <link rel="icon" type="image/png" sizes="16x16" href="/php/img/cesar.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../alumnos/css/student.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.dataTables.min.css">
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
        <div class="container">
            <?php if (isset($_SESSION['mensaje'])): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['mensaje']); ?></div>
                <?php unset($_SESSION['mensaje']); ?>
            <?php endif; ?>
            <?php if (isset($_SESSION['mensaje_error'])): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($_SESSION['mensaje_error']); ?></div>
                <?php unset($_SESSION['mensaje_error']); ?>
            <?php endif; ?>

            <div class="header-container">
                <h2>Gestionar Estudiantes</h2>
                <button class="add-btn" onclick="location.href='../alumnos/agg_student.php'">
                    <i class="fa fa-plus"></i> Agregar Nuevo Estudiante
                </button>
            </div>

            <div class="card-subtitle">Lista de Estudiantes</div>
            <div class="table-responsive-wrapper">
                <table id="tablaEstudiantes" class="compact-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Contacto</th>
                            <th>Nivel Educativo</th>
                            <th>Aula</th>
                            <th>Sección</th>
                            <th>Ingreso</th>
                            <th>Pagos</th>
                            <th>Balance</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT * FROM estudiantes";
                        $result = $conexion->query($sql);
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $balanceStyle = $row['balance'] < 0 ? "style='color:red;'" : "";
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['nombre']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['contacto']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['nivel_educativo']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['aula']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['seccion']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['fecha_ingreso']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['pagos']) . "</td>";
                                echo "<td $balanceStyle>" . htmlspecialchars($row['balance']) . "</td>";
                                echo "<td class='actions'>
                                        <div class='icon-group'>
                                            <a href='/php/editar.php?id=" . urlencode($row['id']) . "' class='icon-btn edit' title='Editar'><i class='fas fa-edit'></i></a>
                                            <a href='delete_student.php?id=" . urlencode($row['id']) . "' class='icon-btn delete' title='Eliminar' onclick='return confirm(\"¿Estás seguro de eliminar este estudiante?\")'><i class='fas fa-trash'></i></a>
                                            <a href='#' class='icon-btn inactive' title='Inactivar' onclick='abrirModal(" . $row['id'] . ")'><i class='fas fa-user-slash'></i></a>
                                        </div>
                                      </td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='10' class='no-data'><i class='fa fa-info-circle'></i> No hay estudiantes registrados</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal para inactivar -->
        <div class="modal" id="modalInactivar">
            <div class="modal-content">
                <h3><strong>Motivo de Inactividad</strong></h3>
                <input type="hidden" id="id_estudiante">
                <textarea id="motivo" placeholder="Escribe el motivo..."></textarea>
                <div class="modal-buttons">
                    <button class="confirm" onclick="confirmarInactivacion()">Confirmar</button>
                    <button class="cancel" onclick="cerrarModal()">Cancelar</button>
                </div>
            </div>
        </div>

        <!-- Scripts -->
        <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
        <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
        <script>
            $(document).ready(function() {
                $('#tablaEstudiantes').DataTable({
                    responsive: true,
                    paging: false,
                    searching: true,
                    ordering: false,
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json'
                    }
                });
            });

            function abrirModal(id) {
                document.getElementById('id_estudiante').value = id;
                document.getElementById('modalInactivar').style.display = 'flex';
            }

            function cerrarModal() {
                document.getElementById('modalInactivar').style.display = 'none';
                document.getElementById('motivo').value = "";
            }

            function confirmarInactivacion() {
                const id = document.getElementById('id_estudiante').value;
                const motivo = document.getElementById('motivo').value.trim();
                if (!motivo) {
                    alert('Por favor escribe un motivo.');
                    return;
                }
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'procesar_inactives.php';
                form.innerHTML = `<input type="hidden" name="id" value="${id}"><input type="hidden" name="motivo" value="${motivo}">`;
                document.body.appendChild(form);
                form.submit();
            }
        </script>
    </div>
</body>
</html>
