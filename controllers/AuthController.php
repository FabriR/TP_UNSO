
<?php
// No llamamos a session_start() aca, ya que se inicia en index.php

function loginUser($pdo) {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Sanitize username input
        $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
        $password = $_POST['password'];  // Password should not be sanitized

        // Protect against brute-force attacks by limiting login attempts (example logic)
        if (!isset($_SESSION['login_attempts'])) {
            $_SESSION['login_attempts'] = 0;
        }
        if ($_SESSION['login_attempts'] >= 5) {
            error_log("Too many login attempts for user: " . $username, 3, '/path/to/php-error.log'); //ruta de php-error 
            die('Too many login attempts. Please try again later.');
        }

        // Prepare and execute the query to fetch user
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $user = $stmt->fetch();

        // Verify user and password
        if ($user && password_verify($password, $user['password'])) {
            // Reset login attempts on successful login
            $_SESSION['login_attempts'] = 0;

            // Start the session for the user
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['username'] = $user['username'];

            // Log access
            $stmt = $pdo->prepare("INSERT INTO access_log (user_id, ip_address) VALUES (:user_id, :ip_address)");
            $stmt->bindParam(':user_id', $user['id']);
            $stmt->bindParam(':ip_address', $_SERVER['REMOTE_ADDR']);
            $stmt->execute();

        } else {
            // Increment login attempts on failure
            $_SESSION['login_attempts'] += 1;
            error_log("Failed login attempt for user: " . $username, 3, '/path/to/php-error.log');
            die('Invalid login credentials.');
        }
    }
}
?>
