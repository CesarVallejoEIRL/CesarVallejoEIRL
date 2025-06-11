<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: ../login.html');
    exit;
}

// Generar token CSRF si no existe
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

include '../database/conexion.php';

// Verificar conexión a la base de datos
if (!$conexion) {
    die("Error de conexión a la base de datos: " . mysqli_connect_error());
}

function clean_input($data) {
    if (is_array($data)) {
        return array_map('clean_input', $data);
    }
    return trim(htmlspecialchars($data, ENT_QUOTES, 'UTF-8'));
}

// CONSULTA PARA PAGOS VALIDADOS
$query_pagos = "SELECT e.id as estudiante_id, e.nombre, e.contacto, e.nivel_educativo,
                       p.id as pago_id, p.concepto, p.fecha_pago as fecha_pago, 
                       p.monto, p.metodo_pago as metodo_pago
                FROM estudiantes e
                JOIN pagos p ON e.id = p.alumno_id
                WHERE e.delete_status = 0 
                AND p.estado = 'Validado' 
                AND p.id NOT IN (SELECT id_pago FROM boletas)
                ORDER BY p.fecha_pago DESC";

$result_pagos = mysqli_query($conexion, $query_pagos);

if ($result_pagos === false) {
    die("Error en consulta de pagos: " . mysqli_error($conexion));
}

// CONSULTA PARA HISTORIAL DE BOLETAS
$query_historial = "SELECT e.id as estudiante_id, e.nombre, e.contacto, e.nivel_educativo,
                           p.id as pago_id, p.concepto, p.fecha_pago, p.monto, p.metodo_pago,
                           b.fecha_emision as fecha_generacion, b.observaciones
                    FROM estudiantes e
                    JOIN pagos p ON e.id = p.alumno_id
                    JOIN boletas b ON p.id = b.id_pago
                    WHERE e.delete_status = 0 
                    AND p.estado = 'Validado'
                    ORDER BY b.fecha_emision DESC";

$result_historial = mysqli_query($conexion, $query_historial);

if ($result_historial === false) {
    die("Error en consulta de historial: " . mysqli_error($conexion));
}

// Obtener conteo de registros de forma segura
$num_pagos = $result_pagos ? mysqli_num_rows($result_pagos) : 0;
$num_historial = $result_historial ? mysqli_num_rows($result_historial) : 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Generar Boletas - CEBA Cesar Vallejo</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../img/cesar.png">
    <link rel="stylesheet" href="../alumnos/css/report.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <div id="wrapper">
        <!-- Barra superior -->
        <nav class="navbar navbar-default navbar-cls-top" role="navigation">
            <div class="navbar-header">
                <h2 style="color:white; padding-left: 20px;">Sistema de Boletas de Pago</h2>
            </div>
        </nav>
        
        <!-- Barra lateral -->
        <nav class="navbar-default navbar-side">
            <div class="sidebar-collapse">
                <h3 class="sidebar-title">CEBA Cesar Vallejo</h3>
                <ul class="nav" id="main-menu">
                    <li>
                        <div class="user-img-div text-center">
                            <img src="../img/cesar.png" class="img" alt="Imagen del Colegio">
                            <h5 style="color:white;"><?= htmlspecialchars($_SESSION['usuario']) ?></h5>
                        </div>
                    </li>
                    <li><a href="../dashboard.php"><i class="fa fa-home"></i> Inicio</a></li>
                    <li><a href="../alumnos/student.php"><i class="fa fa-users"></i> Estudiantes</a></li>
                    <li><a href="../alumnos/inactivestd.php"><i class="fa fa-toggle-off"></i> Inactivos</a></li>
                    <li><a href="../alumnos/grade.php"><i class="fa fa-th-large"></i> Grados</a></li>
                    <li><a href="../alumnos/fees.php"><i class="fa fa-credit-card"></i> Pagos</a></li>
                    <li><a href="../alumnos/report.php" class="active-menu"><i class="fa fa-file-pdf"></i> Reportes</a></li>
                    <li><a href="../alumnos/setting.html"><i class="fa fa-cogs"></i> Cuenta</a></li>
                    <li><a href="../login.html" id="logoutBtn"><i class="fa fa-power-off"></i> Cerrar Sesión</a></li>
                </ul>
            </div>
        </nav>

        <!-- Contenido principal -->
        <div class="main-content">
            <h1>Generar Boletas de Pago</h1>
            
            <button id="toggleHistory" class="history-toggle">
                <i class="fas fa-history"></i> Mostrar Historial de Boletas
                <span class="badge" id="historyCount"><?= $num_historial ?></span>
            </button>
            
            <div class="panel">
                <h2>Pagos Validados</h2>
                <div class="table-responsive">
                    <table id="pagosTable">
                        <thead>
                            <tr>
                                <th>Estudiante</th>
                                <th>Concepto</th>
                                <th>Fecha Pago</th>
                                <th>Monto</th>
                                <th>Método</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($num_pagos > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($result_pagos)): ?>
                                    <tr data-pago-id="<?= $row['pago_id'] ?>">
                                        <td>
                                            <strong><?= htmlspecialchars($row['nombre']) ?></strong><br>
                                            <small><?= htmlspecialchars($row['contacto']) ?></small>
                                        </td>
                                        <td><?= htmlspecialchars($row['concepto']) ?></td>
                                        <td><?= date('d/m/Y', strtotime($row['fecha_pago'])) ?></td>
                                        <td>S/ <?= number_format($row['monto'], 2) ?></td>
                                        <td><?= htmlspecialchars($row['metodo_pago']) ?></td>
                                        <td>
                                            <button class="btn-generate"
                                                data-id="<?= $row['estudiante_id'] ?>"
                                                data-pago-id="<?= $row['pago_id'] ?>"
                                                data-nombre="<?= htmlspecialchars($row['nombre'], ENT_QUOTES) ?>"
                                                data-contacto="<?= htmlspecialchars($row['contacto'], ENT_QUOTES) ?>"
                                                data-grado="<?= htmlspecialchars($row['nivel_educativo'], ENT_QUOTES) ?>"
                                                data-concepto="<?= htmlspecialchars($row['concepto'], ENT_QUOTES) ?>"
                                                data-fecha-pago="<?= date('d/m/Y', strtotime($row['fecha_pago'])) ?>"
                                                data-monto="<?= number_format($row['monto'], 2) ?>"
                                                data-metodo="<?= htmlspecialchars($row['metodo_pago'], ENT_QUOTES) ?>">
                                                <i class="fas fa-file-invoice"></i> Generar Boleta
                                            </button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6">No hay pagos validados para mostrar</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Panel de historial (inicialmente oculto) -->
            <div class="panel history-panel" id="historyPanel">
                <h2><i class="fas fa-history"></i> Historial de Boletas Generadas</h2>
                <div class="table-responsive">
                    <table id="historialTable">
                        <thead>
                            <tr>
                                <th>Estudiante</th>
                                <th>Concepto</th>
                                <th>Fecha Pago</th>
                                <th>Monto</th>
                                <th>Método</th>
                                <th>Fecha Generación</th>
                                <th>Observaciones</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($num_historial > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($result_historial)): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($row['nombre']) ?></strong><br>
                                            <small><?= htmlspecialchars($row['contacto']) ?></small>
                                        </td>
                                        <td><?= htmlspecialchars($row['concepto']) ?></td>
                                        <td><?= date('d/m/Y', strtotime($row['fecha_pago'])) ?></td>
                                        <td>S/ <?= number_format($row['monto'], 2) ?></td>
                                        <td><?= htmlspecialchars($row['metodo_pago']) ?></td>
                                        <td><?= date('d/m/Y H:i', strtotime($row['fecha_generacion'])) ?></td>
                                        <td><?= !empty($row['observaciones']) ? htmlspecialchars($row['observaciones']) : 'Sin observaciones' ?></td>
                                        <td>
                                            <a href="ver_boleta.php?id=<?= $row['pago_id'] ?>" class="btn-generate" target="_blank">
                                                <i class="fas fa-eye"></i> Ver Boleta
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8">No hay boletas generadas en el historial</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal para generación de boleta -->
        <div id="boletaModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3><i class="fas fa-file-invoice-dollar"></i> Generar Boleta</h3>
                    <span class="close">&times;</span>
                </div>
                <form id="boletaForm" action="generar_boleta.php" method="POST" target="_blank">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <input type="hidden" name="id_estudiante" id="formIdEstudiante">
                    <input type="hidden" name="id_pago" id="formIdPago">
                    
                    <div class="student-info">
                        <div class="student-info-grid">
                            <div class="student-info-item">
                                <h4>Estudiante</h4>
                                <p id="modalNombre"></p>
                            </div>
                            <div class="student-info-item">
                                <h4>Contacto</h4>
                                <p id="modalContacto"></p>
                            </div>
                            <div class="student-info-item">
                                <h4>Grado</h4>
                                <p id="modalGrado"></p>
                            </div>
                            <div class="student-info-item">
                                <h4>Concepto</h4>
                                <p id="modalConcepto"></p>
                            </div>
                            <div class="student-info-item">
                                <h4>Fecha Pago</h4>
                                <p id="modalFechaPago"></p>
                            </div>
                            <div class="student-info-item">
                                <h4>Monto</h4>
                                <p id="modalMonto"></p>
                            </div>
                            <div class="student-info-item">
                                <h4>Método</h4>
                                <p id="modalMetodo"></p>
                            </div>
                        </div>
                        
                        <div class="observations-area">
                            <label for="observaciones"><i class="fas fa-edit"></i> Observaciones:</label>
                            <textarea name="observaciones" id="observaciones" 
                                      placeholder="Ingrese observaciones para la boleta..." 
                                      rows="3"></textarea>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="modal-btn btn-cancel">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                        <button type="submit" class="modal-btn btn-confirm">
                            <i class="fas fa-check-circle"></i> Generar Boleta
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../alumnos/js/report.js"></script>
</body>
</html>