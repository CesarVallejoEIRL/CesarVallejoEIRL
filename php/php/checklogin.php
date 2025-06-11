<?php
session_start(); // Inicia la sesión

// Verifica si la sesión 'rainbow_uid' no está definida
if (!isset($_SESSION['rainbow_uid'])) {
    // Redirige al usuario a la página de login
    echo '<script type="text/javascript">window.location="login.php"; </script>';
    exit(); // Detiene la ejecución del script después de la redirección
}
?>
