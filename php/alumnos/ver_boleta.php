<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: ../login.html');
    exit;
}

// Verificar token CSRF para acciones sensibles
if (isset($_POST['action'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Token CSRF inválido");
    }
}

include '../database/conexion.php';

// Verificar conexión a la base de datos
if (!$conexion) {
    die("Error de conexión a la base de datos: " . mysqli_connect_error());
}

// Obtener ID del pago desde la URL
$id_pago = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_pago <= 0) {
    die("ID de pago inválido");
}

// Consulta para obtener los datos de la boleta
$query = "SELECT 
            e.nombre, e.contacto, e.nivel_educativo,
            p.concepto, p.fecha_pago, p.monto, p.metodo_pago,
            b.fecha_emision, b.observaciones, b.numero_boleta
          FROM estudiantes e
          JOIN pagos p ON e.id = p.alumno_id
          JOIN boletas b ON p.id = b.id_pago
          WHERE p.id = ? AND e.delete_status = 0";

$stmt = mysqli_prepare($conexion, $query);
mysqli_stmt_bind_param($stmt, "i", $id_pago);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    die("No se encontró la boleta solicitada");
}

$boleta = mysqli_fetch_assoc($result);

// Formatear fechas
$fecha_pago = date('d/m/Y', strtotime($boleta['fecha_pago']));
$fecha_emision = date('d/m/Y H:i', strtotime($boleta['fecha_emision']));

// Cerrar conexión
mysqli_close($conexion);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Boleta de Pago - CEBA Cesar Vallejo</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../img/cesar.png">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .boleta-container {
            max-width: 800px;
            margin: 0 auto;
            border: 2px solid #ddd;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #ddd;
            padding-bottom: 10px;
        }
        .logo {
            max-width: 100px;
            height: auto;
        }
        .title {
            color: #2c3e50;
            margin: 10px 0 5px;
        }
        .subtitle {
            color: #7f8c8d;
            margin: 0 0 15px;
            font-size: 16px;
        }
        .boleta-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .boleta-number {
            font-weight: bold;
            font-size: 18px;
        }
        .student-info, .payment-info {
            margin-bottom: 20px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        .info-item h4 {
            margin: 0 0 5px;
            color: #2c3e50;
        }
        .info-item p {
            margin: 0;
            padding: 8px;
            background: #f9f9f9;
            border-radius: 4px;
        }
        .observations {
            margin-top: 20px;
            padding: 15px;
            background: #f5f5f5;
            border-radius: 4px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 14px;
            color: #7f8c8d;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }
        .signature {
            margin-top: 50px;
            text-align: right;
        }
        @media print {
            body {
                padding: 0;
            }
            .boleta-container {
                border: none;
                box-shadow: none;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="boleta-container">
        <div class="header">
            <img src="../img/cesar.png" alt="Logo CEBA" class="logo">
            <h1 class="title">CEBA "Cesar Vallejo"</h1>
            <p class="subtitle">Educación secundaria para jóvenes y adultos</p>
            <h2>BOLETA DE PAGO</h2>
        </div>
        
        <div class="boleta-info">
            <div>
                <p><strong>Fecha de emisión:</strong> <?= $fecha_emision ?></p>
            </div>
            <div>
                <p class="boleta-number"><strong>Boleta N°:</strong> <?= htmlspecialchars($boleta['numero_boleta']) ?></p>
            </div>
        </div>
        
        <div class="student-info">
            <h3>Datos del Estudiante</h3>
            <div class="info-grid">
                <div class="info-item">
                    <h4>Nombre completo</h4>
                    <p><?= htmlspecialchars($boleta['nombre']) ?></p>
                </div>
                <div class="info-item">
                    <h4>Contacto</h4>
                    <p><?= htmlspecialchars($boleta['contacto']) ?></p>
                </div>
                <div class="info-item">
                    <h4>Nivel educativo</h4>
                    <p><?= htmlspecialchars($boleta['nivel_educativo']) ?></p>
                </div>
            </div>
        </div>
        
        <div class="payment-info">
            <h3>Detalles del Pago</h3>
            <div class="info-grid">
                <div class="info-item">
                    <h4>Concepto</h4>
                    <p><?= htmlspecialchars($boleta['concepto']) ?></p>
                </div>
                <div class="info-item">
                    <h4>Fecha de pago</h4>
                    <p><?= $fecha_pago ?></p>
                </div>
                <div class="info-item">
                    <h4>Monto</h4>
                    <p>S/ <?= number_format($boleta['monto'], 2) ?></p>
                </div>
                <div class="info-item">
                    <h4>Método de pago</h4>
                    <p><?= htmlspecialchars($boleta['metodo_pago']) ?></p>
                </div>
            </div>
        </div>
        
        <?php if (!empty($boleta['observaciones'])): ?>
        <div class="observations">
            <h4>Observaciones:</h4>
            <p><?= htmlspecialchars($boleta['observaciones']) ?></p>
        </div>
        <?php endif; ?>
        
        <div class="signature">
            <p>_________________________</p>
            <p>Firma del Administrador</p>
        </div>
        
        <div class="footer">
            <p>CEBA "Cesar Vallejo" - <?= date('Y') ?></p>
            <p>Esta boleta es un comprobante oficial de pago</p>
        </div>
    </div>
    
    <div class="no-print" style="text-align: center; margin-top: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #2c3e50; color: white; border: none; border-radius: 4px; cursor: pointer;">
            <i class="fas fa-print"></i> Imprimir Boleta
        </button>
        <button onclick="window.close()" style="padding: 10px 20px; margin-left: 10px; background: #e74c3c; color: white; border: none; border-radius: 4px; cursor: pointer;">
            <i class="fas fa-times"></i> Cerrar Ventana
        </button>
    </div>
    
    <script>
        // Auto-print option (optional)
        window.onload = function() {
            // Uncomment to auto-print
            // setTimeout(function(){ window.print(); }, 1000);
        };
    </script>
</body>
</html>