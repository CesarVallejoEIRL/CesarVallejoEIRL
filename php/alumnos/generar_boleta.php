<?php
session_start();

// Validar token CSRF
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die("Error de seguridad: Token CSRF no válido");
}

include '../database/conexion.php';

function clean_input($data)
{
    return trim(htmlspecialchars($data, ENT_QUOTES, 'UTF-8'));
}

// Obtener datos del formulario
$id_estudiante = clean_input($_POST['id_estudiante']);
$id_pago = clean_input($_POST['id_pago']);
$observaciones = isset($_POST['observaciones']) ? clean_input($_POST['observaciones']) : '';

// Validar datos requeridos
if (empty($id_estudiante) || empty($id_pago)) {
    die("Error: Faltan parámetros requeridos");
}

// Obtener información del pago
$query_pago = "SELECT p.*, e.nombre, e.contacto, e.nivel_educativo 
               FROM Pagos p
               JOIN Estudiantes e ON p.alumno_id = e.id
               WHERE p.id = ? AND e.id = ?";
$stmt_pago = mysqli_prepare($conexion, $query_pago);

if ($stmt_pago === false) {
    die("Error al preparar consulta de pago: " . mysqli_error($conexion));
}

mysqli_stmt_bind_param($stmt_pago, 'ii', $id_pago, $id_estudiante);
if (!mysqli_stmt_execute($stmt_pago)) {
    die("Error al ejecutar consulta de pago: " . mysqli_stmt_error($stmt_pago));
}

$result_pago = mysqli_stmt_get_result($stmt_pago);
$pago = mysqli_fetch_assoc($result_pago);

if (!$pago) {
    die("Error: Pago no encontrado");
}

// Generar número de boleta
$query_ultima_boleta = "SELECT MAX(numero_boleta) as ultima FROM boletas";
$result_ultima = mysqli_query($conexion, $query_ultima_boleta);

if (!$result_ultima) {
    die("Error al obtener última boleta: " . mysqli_error($conexion));
}

$ultima_boleta = mysqli_fetch_assoc($result_ultima);
$nuevo_numero = $ultima_boleta['ultima'] ? intval(substr($ultima_boleta['ultima'], -4)) + 1 : 1;
$numero_boleta = 'B-' . date('Y') . '-' . str_pad($nuevo_numero, 4, '0', STR_PAD_LEFT);

// Registrar boleta en la base de datos
$query_insert = "INSERT INTO boletas (numero_boleta, id_estudiante, id_pago, fecha_emision, observaciones) 
                 VALUES (?, ?, ?, NOW(), ?)";
$stmt_insert = mysqli_prepare($conexion, $query_insert);

if ($stmt_insert === false) {
    die("Error al preparar inserción: " . mysqli_error($conexion));
}

mysqli_stmt_bind_param($stmt_insert, 'siis', $numero_boleta, $id_estudiante, $id_pago, $observaciones);
if (!mysqli_stmt_execute($stmt_insert)) {
    die("Error al ejecutar inserción: " . mysqli_stmt_error($stmt_insert));
}

// Generar HTML de la boleta
ob_start();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Boleta de Pago - <?= $numero_boleta ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }

        .boleta-container {
            max-width: 800px;
            margin: 0 auto;
            border: 1px solid #ddd;
            padding: 30px;
        }

        .header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 15px;
        }

        .logo {
            width: 80px;
            height: 80px;
            margin-right: 20px;
        }

        .header-text {
            flex-grow: 1;
            text-align: center;
        }

        .header-text h1 {
            margin: 0;
            color: #2c3e50;
        }

        .header-text h2 {
            margin: 5px 0;
            color: #3498db;
        }

        .info-boleta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .total-row {
            font-weight: bold;
            background-color: #f2f2f2;
        }

        .firma {
            margin-top: 50px;
            text-align: right;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 0.9em;
            color: #7f8c8d;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }

        /* Estilo para la vista previa de impresión */
        .print-preview {
            max-width: 800px;
            margin: 20px auto;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        /* Barra de acciones */
        .print-actions {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
            display: flex;
            gap: 10px;
            background: white;
            padding: 10px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
        }

        .print-actions button {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }

        .print-actions .print-btn {
            background: #28a745;
            color: white;
        }

        .print-actions .close-btn {
            background: #dc3545;
            color: white;
        }

        @media print {
            @page {
                size: A4;
                margin: 10mm;
            }

            body {
                margin: 0;
                padding: 0;
            }

            .boleta-container {
                border: none;
                padding: 0;
            }
        }
    </style>
</head>

<body>
    <div class="boleta-container">
        <div class="header">
            <img src="../img/cesar.png" class="logo" alt="Logo CEBA Cesar Vallejo">
            <div class="header-text">
                <h1>CEBA Cesar Vallejo</h1>
                <h2>BOLETA DE PAGO</h2>
                <p>RUC: 20571502110</p>
                <p>calle. Francisco Vidal Nro 205 INT 3, Barranca-Peru</p>
            </div>
        </div>

        <div class="info-boleta">
            <div>
                <p><strong>Fecha Emisión:</strong> <?= date('d/m/Y') ?></p>
                <p><strong>Hora:</strong> <?= date('H:i') ?></p>
                <p><strong>Nº Boleta:</strong> <?= htmlspecialchars($numero_boleta) ?></p>
            </div>
            <div>
                <p><strong>Alumno:</strong> <?= htmlspecialchars($pago['nombre']) ?></p>
                <p><strong>Contacto:</strong> <?= htmlspecialchars($pago['contacto']) ?></p>
                <p><strong>Grado:</strong> <?= htmlspecialchars($pago['nivel_educativo']) ?></p>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Descripción</th>
                    <th>Fecha Pago</th>
                    <th>Método</th>
                    <th>Monto (S/)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?= htmlspecialchars($pago['concepto']) ?></td>
                    <td><?= date('d/m/Y', strtotime($pago['fecha_pago'])) ?></td>
                    <td><?= htmlspecialchars($pago['metodo_pago']) ?></td>
                    <td>S/ <?= number_format($pago['monto'], 2) ?></td>
                </tr>
                <tr class="total-row">
                    <td colspan="3"><strong>TOTAL:</strong></td>
                    <td>S/ <?= number_format($pago['monto'], 2) ?></td>
                </tr>
            </tbody>
        </table>

        <?php if (!empty($observaciones)): ?>
            <div class="observaciones">
                <p><strong>Observaciones:</strong> <?= htmlspecialchars($observaciones) ?></p>
            </div>
        <?php endif; ?>

        <div class="firma">
            <p>_________________________</p>
            <p>Firma Autorizada</p>
        </div>

        <div class="footer">
            <p>© CEBA Cesar Vallejo - <?= date('Y') ?></p>
        </div>
    </div>

    <script>
        // Imprimir automáticamente al cargar
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>

</html>
<?php
$html = ob_get_clean();

// Configurar headers para mostrar HTML
header('Content-Type: text/html; charset=UTF-8');
echo $html;

// Cerrar conexiones
mysqli_stmt_close($stmt_pago);
mysqli_stmt_close($stmt_insert);
mysqli_close($conexion);
?>