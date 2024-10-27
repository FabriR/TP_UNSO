<?php
// No llamamos a session_start() acá, ya que se inicia en index.php

function loginUser($pdo) {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Sanitizar las entradas del usuario
        $username = htmlspecialchars($_POST['username'], ENT_QUOTES, 'UTF-8');
        $password = $_POST['password'];  // No sanitizamos la contraseña, solo validación
        
        // Preparar y ejecutar la consulta para obtener el usuario
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch();

        // Verificar si el usuario existe y la contraseña es correcta
        if ($user && password_verify($password, $user['password'])) {
            // Regenerar el ID de sesión para evitar fijación de sesión
            session_regenerate_id(true);

            // Iniciar sesión
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['username'] = $user['username']; // Guardar el nombre de usuario

            // Registrar el acceso en la tabla access_log para cualquier usuario
            $stmt = $pdo->prepare("INSERT INTO access_log (user_id, ip_address) VALUES (:user_id, :ip_address)");
            $stmt->execute([
                'user_id' => $_SESSION['user_id'],
                'ip_address' => $_SERVER['REMOTE_ADDR']
            ]);

            // Redirigir según el rol del usuario
            return $_SESSION['role']; // Devuelve el rol para redirigir después
        } else {
            return 'Nombre de usuario o contraseña incorrectos.'; // Mensaje de error
        }
    }
}

function isValidPassword($password) {
    // Verificar si la contraseña es válida: al menos 8 caracteres, al menos una letra mayúscula, un número y un símbolo
    return preg_match('/^(?=.*[A-Z])(?=.*[0-9])(?=.*[\W_]).{8,}$/', $password);
}

function registerUser($pdo, $username, $password, $email, $role) {
    // Sanitizar las entradas del usuario
    $username = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);

    if (isValidPassword($password)) {
        // Encriptar la contraseña
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Preparar la consulta de inserción
        $stmt = $pdo->prepare("INSERT INTO users (username, password, email, role) VALUES (:username, :password, :email, :role)");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':role', $role);

        // Ejecutar la consulta y verificar si se insertó correctamente
        if ($stmt->execute()) {
            return 'Registro exitoso. Puedes iniciar sesión ahora.';
        } else {
            return 'Error al registrar el usuario. Inténtalo de nuevo.';
        }
    } else {
        return 'La contraseña debe tener al menos 8 caracteres, incluyendo una letra mayúscula, un número y un símbolo.';
    }
}
?>
