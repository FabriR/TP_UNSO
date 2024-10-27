<?php
class UserController {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function register() {
        // Asegúrate de que la función registerUser realiza la sanitización y validación adecuadas
        return registerUser($this->pdo);
    }

    public function viewProfile() {
        // Verificar si la sesión del usuario está activa antes de proceder
        if (isset($_SESSION['user_id'])) {
            // Preparar la consulta para obtener el perfil del usuario
            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = :id");
            // Asegurarse de sanitizar la entrada de la sesión (en este caso, no es necesario ya que es de servidor)
            $stmt->bindParam(':id', $_SESSION['user_id'], PDO::PARAM_INT);
            $stmt->execute();
            // Devolver los datos del perfil del usuario
            return $stmt->fetch();
        }
        return null;  // Devolver null si no hay sesión activa
    }
}
?>
