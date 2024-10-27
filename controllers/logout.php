<?php
session_start(); // Inicia la sesión
session_unset(); // Limpia todas las variables de sesión
session_destroy(); // Destruye la sesión

// Elimina la cookie de sesión (si es necesario)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Redirige al login
header('Location: ../index.php');
exit;
?>
