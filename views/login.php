
<?php
// Error handling
ini_set('display_errors', 0);  // Disable error display in production
ini_set('log_errors', 1);      // Enable error logging
ini_set('error_log', '/path/to/php-error.log');  // Set path for the error log
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Gestión de Usuarios</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/styles.css">
    <style>
        /* Estilos personalizados */
    </style>
</head>
<body>
    <!-- Contenido del formulario de inicio de sesión -->
    <?php include 'partials/header.php'; ?>
    <div class="container">
        <h2>Iniciar Sesión</h2>
        <!-- Mostrar mensajes de error -->
        <form method="POST" action="../index.php">
            <!-- Campos de formulario -->
        </form>
    </div>
    <?php include 'partials/footer.php'; ?>
</body>
</html>


<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize the inputs (assuming 'username' and 'password' fields)
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $password = $_POST['password']; // Passwords should not be sanitized, just hashed
    
    // Process the login (password_verify should be used if hash comparison is required)
}
?>
