<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: ../login.html');
    exit;
}
include '../database/conexion.php';

// Procesar formulario de nuevo pago
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrar_pago'])) {
    $alumno_id = intval($_POST['alumno_id']);
    $monto = floatval($_POST['monto']);
    $concepto = trim($_POST['concepto']);
    $fecha_pago = trim($_POST['fecha_pago']);
    $metodo_pago = trim($_POST['metodo_pago']);
    $observaciones = trim($_POST['observaciones']);

    // Extraer mes y día de la fecha de pago
    $fecha_dt = new DateTime($fecha_pago);
    $mes_pago = $fecha_dt->format('m'); // Mes en formato numérico (01-12)
    $dia_pago = $fecha_dt->format('d'); // Día en formato numérico (01-31)

    // Validar datos
    if ($alumno_id > 0 && $monto > 0 && validarFecha($fecha_pago)) {
        $query = "INSERT INTO Pagos (alumno_id, monto, concepto, fecha_pago, mes_pago, dia_pago, metodo_pago, observaciones, estado) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Validado')";
        
        if ($stmt = mysqli_prepare($conexion, $query)) {
            mysqli_stmt_bind_param($stmt, 'idssiiss', $alumno_id, $monto, $concepto, $fecha_pago, $mes_pago, $dia_pago, $metodo_pago, $observaciones);
            
            if (mysqli_stmt_execute($stmt)) {
                $mensaje = "Pago registrado exitosamente";
                $tipo_mensaje = "success";
            } else {
                $mensaje = "Error al registrar el pago: " . mysqli_error($conexion);
                $tipo_mensaje = "error";
            }
            mysqli_stmt_close($stmt);
        } else {
            $mensaje = "Error en la preparación de la consulta: " . mysqli_error($conexion);
            $tipo_mensaje = "error";
        }
    } else {
        $mensaje = "Datos de pago inválidos";
        $tipo_mensaje = "error";
    }
}

// Función para validar fecha
function validarFecha($fecha) {
    return preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha);
}

// Obtener nombre del alumno si está seleccionado
$nombre_alumno = "";
$alumno_id = isset($_GET['alumno_id']) ? intval($_GET['alumno_id']) : 0;

if ($alumno_id > 0) {
    $query_nombre = "SELECT nombre FROM Estudiantes WHERE id = ?";
    if ($stmt = mysqli_prepare($conexion, $query_nombre)) {
        mysqli_stmt_bind_param($stmt, 'i', $alumno_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $nombre);
        mysqli_stmt_fetch($stmt);
        $nombre_alumno = $nombre;
        mysqli_stmt_close($stmt);
    }
}

// Obtener lista de alumnos para búsqueda
$result_alumnos = null;
if (isset($_GET['busqueda']) && !empty($_GET['busqueda'])) {
    $busqueda = '%' . trim($_GET['busqueda']) . '%';
    $query_alumnos = "SELECT id, nombre FROM Estudiantes WHERE delete_status = 0 AND nombre LIKE ? ORDER BY nombre LIMIT 10";
    
    if ($stmt = mysqli_prepare($conexion, $query_alumnos)) {
        mysqli_stmt_bind_param($stmt, 's', $busqueda);
        mysqli_stmt_execute($stmt);
        $result_alumnos = mysqli_stmt_get_result($stmt);
    }
}

// Obtener historial de pagos (si se seleccionó un alumno)
$historial_pagos = [];
if ($alumno_id > 0) {
    $query_pagos = "SELECT p.id, p.monto, p.concepto, p.fecha_pago, p.mes_pago, p.dia_pago, p.metodo_pago, p.observaciones, p.estado, 
                    e.nombre as alumno_nombre 
                    FROM Pagos p
                    JOIN Estudiantes e ON p.alumno_id = e.id
                    WHERE p.alumno_id = ?
                    ORDER BY p.fecha_pago DESC";
    
    if ($stmt = mysqli_prepare($conexion, $query_pagos)) {
        mysqli_stmt_bind_param($stmt, 'i', $alumno_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $result_pagos = mysqli_stmt_get_result($stmt);
            
            while ($row = mysqli_fetch_assoc($result_pagos)) {
                $historial_pagos[] = $row;
            }
        } else {
            $mensaje = "Error al obtener el historial de pagos: " . mysqli_error($conexion);
            $tipo_mensaje = "error";
        }
        mysqli_stmt_close($stmt);
    } else {
        $mensaje = "Error en la preparación de la consulta: " . mysqli_error($conexion);
        $tipo_mensaje = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Pagos - Cesar Vallejo</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="/php/img/cesar.png">
    <link rel="stylesheet" href="../alumnos/css/report.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .payment-container {
            display: flex;
            gap: 20px;
            margin-top: 20px;
        }
        
        .payment-form, .payment-history {
            flex: 1;
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .btn-submit {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .payment-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .payment-table th, .payment-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        
        .payment-table th {
            background-color: #a94442;
        }
        
        .payment-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .status-validado {
            color: green;
            font-weight: bold;
        }
        
        .status-pendiente {
            color: orange;
            font-weight: bold;
        }
        
        .alert {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        
        .alert-success {
            background-color: #dff0d8;
            color: #3c763d;
        }
        
        .alert-error {
            background-color: #f2dede;
            color: #a94442;
        }
        
        .alumno-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #4CAF50;
        }
        
        .change-student {
            margin-top: 10px;
        }
        
        .search-results {
            margin-top: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            max-height: 200px;
            overflow-y: auto;
        }
        
        .search-results a {
            display: block;
            padding: 8px;
            text-decoration: none;
            color: #333;
        }
        
        .search-results a:hover {
            background-color: #f0f0f0;
        }
    </style>
</head>
<body>
    <div id="wrapper">
        <!-- Barra superior -->
        <nav class="navbar navbar-default navbar-cls-top" role="navigation">
            <div class="navbar-header">
                <h2 style="color:white; padding-left: 20px;">Sistema de Pagos</h2>
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

        <!-- Contenido principal -->
        <div class="main-content">
            <h1>Registro de Pagos</h1>
            
            <?php if (isset($mensaje)): ?>
                <div class="alert alert-<?php echo $tipo_mensaje; ?>">
                    <?php echo $mensaje; ?>
                </div>
            <?php endif; ?>
            
            <div class="panel">
                <?php if (!empty($nombre_alumno)): ?>
                    <div class="alumno-info">
                        <h3>Alumno: <?php echo htmlspecialchars($nombre_alumno); ?></h3>
                        <input type="hidden" name="alumno_id" value="<?php echo $alumno_id; ?>">
                        <a href="fees.php" class="change-student">Cambiar de alumno</a>
                    </div>
                <?php else: ?>
                    <h2>Buscar Alumno</h2>
                    <form method="GET">
                        <div class="form-group">
                            <label for="busqueda">Nombre del Alumno:</label>
                            <input type="text" name="busqueda" id="busqueda" value="<?php echo isset($_GET['busqueda']) ? htmlspecialchars($_GET['busqueda']) : ''; ?>" placeholder="Escribe el nombre del alumno" required>
                            <button type="submit" class="btn-submit">Buscar</button>
                        </div>
                    </form>
                    
                    <?php if (isset($_GET['busqueda']) && $result_alumnos && mysqli_num_rows($result_alumnos) > 0): ?>
                        <div class="search-results">
                            <?php while ($alumno = mysqli_fetch_assoc($result_alumnos)): ?>
                                <a href="?alumno_id=<?php echo $alumno['id']; ?>&busqueda=<?php echo urlencode($_GET['busqueda']); ?>">
                                    <?php echo htmlspecialchars($alumno['nombre']); ?>
                                </a>
                            <?php endwhile; ?>
                        </div>
                    <?php elseif (isset($_GET['busqueda'])): ?>
                        <p>No se encontraron alumnos con ese nombre.</p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            
            <?php if (!empty($nombre_alumno)): ?>
            <div class="payment-container">
                <div class="payment-form">
                    <h2>Registrar Nuevo Pago</h2>
                    <form method="POST">
                        <input type="hidden" name="alumno_id" value="<?php echo $alumno_id; ?>">
                        
                        <div class="form-group">
                            <label for="monto">Monto (S/):</label>
                            <input type="number" name="monto" id="monto" step="0.01" min="0" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="concepto">Concepto:</label>
                            <select name="concepto" id="concepto" required>
                                <option value="">-- Seleccione concepto --</option>
                                <option value="Matrícula">Matrícula</option>
                                <option value="Mensualidad">Mensualidad</option>
                                <option value="Materiales">Materiales</option>
                                <option value="Actividades">Actividades</option>
                                <option value="Otros">Otros</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="fecha_pago">Fecha de Pago:</label>
                            <input type="date" name="fecha_pago" id="fecha_pago" required 
                                   value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="metodo_pago">Método de Pago:</label>
                            <select name="metodo_pago" id="metodo_pago" required>
                                <option value="">-- Seleccione método --</option>
                                <option value="Efectivo">Efectivo</option>
                                <option value="Transferencia">Transferencia</option>
                                <option value="Tarjeta">Tarjeta de Crédito/Débito</option>
                                <option value="Yape">Yape</option>
                                <option value="Plin">Plin</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="observaciones">Observaciones:</label>
                            <textarea name="observaciones" id="observaciones" rows="3"></textarea>
                        </div>
                        
                        <button type="submit" name="registrar_pago" class="btn-submit">
                            <i class="fas fa-save"></i> Registrar Pago
                        </button>
                    </form>
                </div>
                
                <div class="payment-history">
                    <h2>Historial de Pagos</h2>
                    <?php if (!empty($historial_pagos)): ?>
                        <table class="payment-table">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Concepto</th>
                                    <th>Monto</th>
                                    <th>Método</th>
                                    <th>Mes</th>
                                    <th>Día</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($historial_pagos as $pago): ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y', strtotime($pago['fecha_pago'])); ?></td>
                                        <td><?php echo htmlspecialchars($pago['concepto']); ?></td>
                                        <td>S/ <?php echo number_format($pago['monto'], 2); ?></td>
                                        <td><?php echo htmlspecialchars($pago['metodo_pago']); ?></td>
                                        <td><?php echo $pago['mes_pago']; ?></td>
                                        <td><?php echo $pago['dia_pago']; ?></td>
                                        <td class="status-<?php echo strtolower($pago['estado']); ?>">
                                            <?php echo htmlspecialchars($pago['estado']); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>No se encontraron registros de pagos para este alumno.</p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>