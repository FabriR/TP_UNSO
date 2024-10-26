
<?php
class UserController {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function register() {
        // Logic for registering users would go here
        return registerUser($this->pdo);
    }

    public function viewProfile() {
        // Secure logic to show user profile
        if (isset($_SESSION['user_id'])) {
            try {
                $user_id = filter_var($_SESSION['user_id'], FILTER_SANITIZE_NUMBER_INT);
                $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = :id");
                $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
                $stmt->execute();
                return $stmt->fetch();
            } catch (PDOException $e) {
                error_log("Profile fetch error: " . $e->getMessage(), 3, '/path/to/php-error.log');
                return null;
            }
        }
        return null;
    }
}
?>
