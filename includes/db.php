<?php
$host = 'localhost';
$db = 'loginsystem_db';  // Nombre de la base de datos
$user = 'test';  // Usuario de MySQL
$pass = 'Login12345@';  // Contraseña de MySQL

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
