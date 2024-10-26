
<?php
session_start(); // Inicia la sesión

// Unset all session variables
session_unset();

// Destroy the session
session_destroy();

// Ensure session cookie is removed
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Redirect to login page securely
header('Location: ../index.php');
exit;
?>
