<?php
define('SECURE_PAGE', true);

// Configurar opciones de sesión para proteger contra hijacking de cookies y ataques XSS
session_set_cookie_params([
    'lifetime' => 0,           // La sesión dura hasta que el navegador se cierra
    'path' => '/',
    'domain' => '',
    'secure' => false,          // Cambia a true en HTTPS
    'httponly' => true,         // No accesible desde JavaScript
    'samesite' => 'Strict'      // Evita el envío de cookies en solicitudes de otros sitios
]);

session_start();
session_regenerate_id(true);    // Regenera el ID de sesión para prevenir fijación de sesión

require 'includes/db.php';
require 'controllers/AuthController.php';

// Genera un token CSRF si no existe
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verificar el token CSRF
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error_message = 'Solicitud no válida.';
    } else {
        $role = loginUser($pdo);

        if ($role === 'admin') {
            header("Location: views/admin.php");
            exit;
        } elseif ($role === 'user') {
            header("Location: views/user.php");
            exit;
        } else {
            $error_message = 'Nombre de usuario o contraseña incorrectos.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Gestión de Usuarios</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .login-container {
            display: flex;
            height: 100vh;
        }
        .login-image {
            background-image: url('assets/img/login-image.png'); 
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
                <h2>Iniciar Sesión</h2>
                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger">
                        <?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>
                <form method="POST" action="index.php">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <div class="form-group">
                        <label for="username">Nombre de usuario</label>
                        <input type="text" name="username" id="username" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Contraseña</label>
                        <input type="password" name="password" id="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Ingresar</button>
                    <p class="mt-3">¿No tienes una cuenta? <a href="views/register.php">Regístrate aquí</a></p>
                    <p class="mt-3"><a href="views/recover.php">¿Olvidaste tu contraseña?</a></p>
                </form>
            </div>
        </div>
    </div>
</body>
</html>


// Sanitizing and validating inputs
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Assuming input fields: 'email', 'url', 'name', 'password' exist in the form
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $url = filter_input(INPUT_POST, 'url', FILTER_SANITIZE_URL);
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    
    // Passwords should not be sanitized but hashed for security purposes
    $password = $_POST['password'];
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    
    // Implement CSRF token validation again here if necessary
}

// Error handling
ini_set('display_errors', 0);  // Disable error display
ini_set('log_errors', 1);      // Enable error logging
ini_set('error_log', '/path/to/php-error.log');  // Set path for the error log

// Error logging function for critical errors (optional)
function log_error($error_message) {
    error_log($error_message, 3, '/path/to/custom-error-log.log');
}

// Example of password validation and verification for secure login
if (isset($hashed_password)) {
    if (password_verify($password, $hashed_password)) {
        // Password matches
    } else {
        // Password does not match
        log_error('Failed login attempt: invalid password');
    }
}
