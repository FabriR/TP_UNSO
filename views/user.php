
<?php
// Ensure session cookies are secure
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '', 
    'secure' => false,  // Must be true on HTTPS
    'httponly' => true, 
    'samesite' => 'Strict'
]);

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php'); // Redirect to login
    exit;
}
?>


<?php
// Error handling
ini_set('display_errors', 0);  // Disable error display in production
ini_set('log_errors', 1);      // Enable error logging
ini_set('error_log', '/path/to/php-error.log');  // Set path for the error log
?>
<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php'); // Redirigir al login si no está autenticado
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuario - Gestión de Usuarios</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" >Sistema de Gestión de Usuarios</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav mr-auto">
            </ul>
            <form method="POST" action="../controllers/logout.php" class="form-inline">
                <button type="submit" class="btn btn-danger my-2 my-sm-0">Cerrar sesión</button>
            </form>
        </div>
    </nav>

    <div class="container mt-5">
        <h1>Bienvenido, <?php echo htmlspecialchars($_SESSION['username']); ?></h1>
        <p>Contenido de la página de usuario.</p>
    </div>

    <footer class="text-center mt-5">
        <p>&copy; 2024 Sistema de Gestión de Usuarios. Todos los derechos reservados. Grupo B2</p>
    </footer>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>








