<?php
define('SECURE_PAGE', true);

// Configurar opciones de la sesión para proteger contra hijacking de cookies y ataques XSS
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => false,          // Cambia a true cuando uses HTTPS
    'httponly' => true,         // No accesible desde JavaScript
    'samesite' => 'Strict'      // Evita el envío de cookies en solicitudes de otros sitios
]);

session_start();
session_regenerate_id(true);    // Previene la fijación de sesión

require '../includes/db.php';

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
        // Validar y sanitizar las entradas del formulario
        $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
        $friend_name = filter_input(INPUT_POST, 'friend_name', FILTER_SANITIZE_STRING);
        $mother_name = filter_input(INPUT_POST, 'mother_name', FILTER_SANITIZE_STRING);
        $nickname = filter_input(INPUT_POST, 'nickname', FILTER_SANITIZE_STRING);

        // Validar que los campos no estén vacíos
        if (empty($username) || empty($friend_name) || empty($mother_name) || empty($nickname)) {
            $error_message = 'Todos los campos son obligatorios.';
        } else {
            // Consulta para verificar las respuestas de seguridad
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username AND friend_name = :friend_name AND mother_name = :mother_name AND nickname = :nickname");
            $stmt->execute([
                ':username' => $username,
                ':friend_name' => $friend_name,
                ':mother_name' => $mother_name,
                ':nickname' => $nickname
            ]);

            if ($stmt->rowCount() > 0) {
                $_SESSION['reset_username'] = $username;
                header("Location: reset_password.php");
                exit;
            } else {
                $error_message = 'Las respuestas de seguridad no coinciden. Inténtalo de nuevo.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperación de Contraseña</title>
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
                <h2>Recuperación de Contraseña</h2>
                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger">
                        <?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>
                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    <div class="form-group">
                        <label for="username">Nombre de usuario</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="friend_name">¿Cómo se llamaba tu amigo en la infancia?</label>
                        <input type="text" name="friend_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="mother_name">Primer nombre de tu madre</label>
                        <input type="text" name="mother_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="nickname">Apodo de la infancia</label>
                        <input type="text" name="nickname" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Verificar</button>
                </form>
                <a href="../index.php"><button>Volver a Inicio</button></a>
            </div>
        </div>
    </div>
</body>
</html>
