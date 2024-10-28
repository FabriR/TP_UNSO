<?php
// Configurar opciones de la sesión para proteger contra hijacking de cookies y ataques XSS
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => false,          // Cambia a true cuando uses HTTPS
    'httponly' => true,         // No accesible desde JavaScript
    'samesite' => 'Strict'      // Evita el envío de cookies en solicitudes de otros sitios
]);

session_start(); // Iniciar sesión después de configurar los parámetros de la cookie

define('SECURE_PAGE', true);
session_regenerate_id(true);    // Previene la fijación de sesión

require '../includes/db.php';

// Generar un token CSRF si no existe
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Validación de sesión y rol
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php'); // Redirigir al login si no está autenticado
    exit;
}

$message = '';

// Si se recibe una solicitud para eliminar un usuario
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_user_id'])) {
    // Verificar el token CSRF
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('Solicitud no válida.');
    }

    $delete_user_id = intval($_POST['delete_user_id']);

    // Verificar que el ID del usuario sea válido y no sea admin
    if ($delete_user_id > 0) {
        try {
            // Eliminar registros en access_log primero
            $stmt = $pdo->prepare("DELETE FROM access_log WHERE user_id = :user_id");
            $stmt->execute([':user_id' => $delete_user_id]);

            // Ahora eliminar al usuario de la tabla users
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id AND role != 'admin'");
            if ($stmt->execute([':id' => $delete_user_id])) {
                $message = 'Usuario eliminado correctamente.';
            } else {
                $message = 'Error al eliminar el usuario.';
            }
        } catch (PDOException $e) {
            $message = 'Error en la eliminación: ' . $e->getMessage();
        }
    } else {
        $message = 'ID de usuario no válido.';
    }

    header("Location: admin.php?message=" . urlencode($message)); // Redirigir de vuelta a la página de administración
    exit;
}

// Obtener los usuarios que no son administradores
$stmt = $pdo->prepare("SELECT id, username, email FROM users WHERE role != 'admin'");
$stmt->execute();
$users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand">Sistema de Gestión de Usuarios</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav mr-auto"></ul>
            <form method="POST" action="../controllers/logout.php" class="form-inline">
                <button type="submit" class="btn btn-danger my-2 my-sm-0">Cerrar sesión</button>
            </form>
        </div>
    </nav>

    <div class="container mt-5">
        <h1>Bienvenido al Panel de Administración</h1>
        <h2 class="mt-5">Gestión de Usuarios</h2>

        <!-- Mostrar mensajes de éxito o error -->
        <?php if (isset($_GET['message'])): ?>
            <div class="alert alert-info">
                <?php echo htmlspecialchars($_GET['message'], ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Usuario</th>
                    <th>Email</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['id']); ?></td>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td>
                        <form method="POST" action="admin.php" onsubmit="return confirm('¿Estás seguro de eliminar este usuario?');">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            <input type="hidden" name="delete_user_id" value="<?php echo $user['id']; ?>">
                            <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <footer class="text-center mt-5">
        <p>&copy; 2024 Sistema de Gestión de Usuarios. Todos los derechos reservados. Grupo B2</p>
    </footer>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
