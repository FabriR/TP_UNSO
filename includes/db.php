
<?php
// Use environment variables to store sensitive database credentials
$host = getenv('DB_HOST') ?: 'localhost';
$db = getenv('DB_NAME') ?: 'loginsystem_db';
$user = getenv('DB_USER') ?: 'test';
$pass = getenv('DB_PASS') ?: 'Login12345@';

try {
    // Establish a secure PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Log errors to a file instead of displaying them to the user
    error_log("Database connection error: " . $e->getMessage(), 3, '/path/to/php-error.log');
    die("Error: Unable to connect to the database.");
}
?>
