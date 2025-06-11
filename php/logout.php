<?php
session_start();
session_unset(); // Eliminar todas las variables de sesión
session_destroy(); // Destruir la sesión completamente

// Evitar problemas de caché
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Devolver JSON para AJAX
echo json_encode(["success" => true, "redirect" => "login.html"]); 
exit();
?>
