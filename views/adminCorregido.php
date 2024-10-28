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

session_start(); // Inicia la sesión después de configurar los parámetros
session_regenerate_id(true);    // Previene la fijación de sesión

define('SECURE_PAGE', true);

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
