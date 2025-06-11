<?php
session_start();

header('Content-Type: application/json');

// Verifica si la sesión está iniciada y envía el usuario
if (isset($_SESSION['usuario'])) {
    echo json_encode(["usuario" => $_SESSION['usuario']]);
} else {
    echo json_encode(["usuario" => null]);
}
?>
