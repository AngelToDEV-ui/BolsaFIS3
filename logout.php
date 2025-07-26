<?php
require_once 'includes/config.php';

// Destruir la sesión
session_destroy();

// Limpiar las variables de sesión
$_SESSION = array();

// Eliminar la cookie de sesión si existe
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Redirigir a la página principal
header("Location: index.html");
exit();
?>
