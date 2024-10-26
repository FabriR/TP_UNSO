
<?php
// Error handling
ini_set('display_errors', 0);  // Disable error display in production
ini_set('log_errors', 1);      // Enable error logging
ini_set('error_log', '/path/to/php-error.log');  // Set path for the error log
?>
<?php
define('SECURE_PAGE', true);

// Configurar opciones de la sesión para proteger contra hijacking de cookies y ataques XSS
session_set_cookie_params([
    'lifetime' => 0,           // La sesión dura hasta que el navegador se cierra
    'path' => '/',
    'domain' => '',
    'secure' => false,          // Cambia a true cuando uses HTTPS
    'httponly' => true,         // No accesible desde JavaScript
    'samesite' => 'Strict'      // Evita el envío de cookies en solicitudes de otros sitios
]);

session_start();
session_regenerate_id(true);    // Previene la fijación de sesión

require '../includes/db.php';

// Redirigir si no se ha iniciado el proceso de recuperación de contraseña
if (!isset($_SESSION['reset_username'])) {
    header("Location: recover.php");
    exit;
}

// Generar un token CSRF si no existe
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verificar token CSRF
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error_message = 'Solicitud no válida.';
    } else {
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        if ($new_password === $confirm_password) {
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

            $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE username = :username");
            $stmt->execute([
                ':password' => $hashed_password,
                ':username' => $_SESSION['reset_username']
            ]);

            // Eliminar la variable de sesión de recuperación de contraseña
            unset($_SESSION['reset_username']);
            header("Location: ../index.php?reset=success");
            exit;
        } else {
            $error_message = "Las contraseñas no coinciden. Inténtalo de nuevo.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .login-container {
            display: flex;
            height: 100vh;
        }
        .login-image {
            background-image: url('../assets/img/login-image.png'); 
            background-size: cover;
            background-position: center;
            width: 50%;
        }
        .login-form {
            width: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .login-box {
            width: 80%;
            max-width: 400px;
            background-color: rgba(255, 255, 255, 0.8); 
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-image"></div>
        <div class="login-form">
            <div class="login-box">
                <h2>Restablecer Contraseña</h2>
                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endif; ?>
                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <div class="form-group">
                        <label for="new_password">Nueva contraseña</label>
                        <input type="password" name="new_password" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirmar nueva contraseña</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Restablecer Contraseña</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>

<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize the new password input
    $new_password = filter_input(INPUT_POST, 'new_password', FILTER_SANITIZE_STRING);
    
    // Ensure password is hashed before storage
    $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
    
    // CSRF Token verification if applicable
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error_message = 'Invalid CSRF token';
        log_error($error_message);
        exit;
    }

    // Process password reset logic here (e.g., update the database with the hashed password)
}
