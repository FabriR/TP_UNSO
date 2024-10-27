<?php
define('SECURE_PAGE', true);

// Configurar opciones de la sesión para proteger contra hijacking de cookies y ataques XSS
session_set_cookie_params([
    'lifetime' => 0,           
    'path' => '/',
    'domain' => '',
    'secure' => false,          // Cambia a true cuando uses HTTPS
    'httponly' => true,         // No accesible desde JavaScript
    'samesite' => 'Strict'      
]);

session_start();
session_regenerate_id(true);    // Previene la fijación de sesión

require '../includes/db.php';

// Generar un token CSRF si no existe
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error_message = '';
$success_message = '';

// Función para validar caracteres especiales no permitidos
function validar_entrada($data) {
    // Solo permitir letras, números, y algunos caracteres como guiones bajos y puntos
    if (!preg_match('/^[a-zA-Z0-9_.-]*$/', $data)) {
        return false;
    }
    return true;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verificar token CSRF
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error_message = 'Solicitud no válida.';
    } else {
        // Sanitizar y validar las entradas del usuario
        $username = htmlspecialchars($_POST['username'], ENT_QUOTES, 'UTF-8');
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password']; // Validar en el próximo paso
        $friend_name = htmlspecialchars($_POST['friend_name'], ENT_QUOTES, 'UTF-8');
        $mother_name = htmlspecialchars($_POST['mother_name'], ENT_QUOTES, 'UTF-8');
        $nickname = htmlspecialchars($_POST['nickname'], ENT_QUOTES, 'UTF-8');

        // Validar caracteres permitidos en los campos sensibles
        if (!validar_entrada($username) || !validar_entrada($friend_name) || !validar_entrada($mother_name) || !validar_entrada($nickname)) {
            $error_message = 'Los campos no deben contener caracteres especiales como comillas o símbolos.';
        } elseif (strlen($password) < 8) {
            // Validar la longitud mínima de la contraseña
            $error_message = 'La contraseña debe tener al menos 8 caracteres.';
        } else {
            // Verificar si el nombre de usuario o el correo ya están en uso
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username OR email = :email");
            $stmt->execute([':username' => $username, ':email' => $email]);

            if ($stmt->rowCount() > 0) {
                $error_message = 'El nombre de usuario o correo ya está en uso.';
            } else {
                // Encriptar la contraseña
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);

                // Insertar el nuevo usuario en la base de datos
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password, friend_name, mother_name, nickname) VALUES (:username, :email, :password, :friend_name, :mother_name, :nickname)");
                $stmt->execute([
                    ':username' => $username,
                    ':email' => $email,
                    ':password' => $hashed_password,
                    ':friend_name' => $friend_name,
                    ':mother_name' => $mother_name,
                    ':nickname' => $nickname
                ]);

                $success_message = 'Registro exitoso. Ahora puedes iniciar sesión.';
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
    <title>Registro - Gestión de Usuarios</title>
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
                <h2>Registro</h2>
                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php elseif (!empty($success_message)): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success_message, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endif; ?>
                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <div class="form-group">
                        <label for="username">Nombre de usuario</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Correo electrónico</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Contraseña</label>
                        <input type="password" name="password" class="form-control" required>
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
                    <button type="submit" class="btn btn-primary btn-block">Registrar</button>
                    <p class="mt-3">¿Ya tienes una cuenta? <a href="../index.php">Inicia sesión aquí</a></p>
                </form>
                <a href="../index.php"><button>Volver a Inicio</button></a>
            </div>
        </div>
    </div>
</body>
</html>
