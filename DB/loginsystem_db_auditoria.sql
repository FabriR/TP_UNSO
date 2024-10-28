-- Crear la base de datos si no existe y usarla
CREATE DATABASE IF NOT EXISTS `loginsystem_db`;
USE `loginsystem_db`;

-- Eliminar las tablas si ya existen
DROP TABLE IF EXISTS `access_log`;
DROP TABLE IF EXISTS `users_audit`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `system_logs`;

-- Configuraciones de phpMyAdmin
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- Crear tabla de registro de accesos
CREATE TABLE `access_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `login_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Crear tabla de usuarios con columna deleted_at para indicar usuarios eliminados
CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `friend_name` varchar(100) DEFAULT NULL,
  `mother_name` varchar(100) DEFAULT NULL,
  `nickname` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Crear tabla de auditoría para mantener registro de los usuarios eliminados
CREATE TABLE `users_audit` (
  `id` int(11),
  `username` varchar(100),
  `email` varchar(100),
  `password` varchar(255),
  `role` enum('user','admin'),
  `friend_name` varchar(100),
  `mother_name` varchar(100),
  `nickname` varchar(100),
  `created_at` timestamp,
  `deleted_at` timestamp
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Crear tabla de auditoría general de sistema
CREATE TABLE `system_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `action` varchar(50) NOT NULL,  -- Ejemplo: 'CREATE_USER', 'DELETE_USER', 'LOGIN'
  `user_id` int(11) DEFAULT NULL, -- El ID del usuario afectado
  `affected_username` varchar(100) DEFAULT NULL, -- El nombre del usuario afectado
  `action_by` int(11) DEFAULT NULL, -- El ID del administrador o usuario que realizó la acción
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(), -- Fecha y hora de la acción
  `details` text DEFAULT NULL,  -- Detalles adicionales de la acción (como IP, etc.)
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Procedimientos para auditoría
DELIMITER $$

CREATE PROCEDURE create_user(IN username VARCHAR(100), IN email VARCHAR(100), IN password VARCHAR(255), IN role ENUM('user', 'admin'), IN action_by INT)
BEGIN
    INSERT INTO users (username, email, password, role, created_at) VALUES (username, email, password, role, NOW());
    INSERT INTO system_logs (action, affected_username, action_by, timestamp, details)
    VALUES ('CREATE_USER', username, action_by, NOW(), CONCAT('User created with email: ', email));
END $$

CREATE PROCEDURE delete_user(IN user_id INT, IN action_by INT)
BEGIN
    INSERT INTO users_audit SELECT *, NOW() FROM users WHERE id = user_id;
    INSERT INTO system_logs (action, affected_username, action_by, timestamp, details)
    SELECT 'DELETE_USER', username, action_by, NOW(), CONCAT('User ID: ', user_id, ' was deleted') FROM users WHERE id = user_id;
    UPDATE users SET deleted_at = NOW() WHERE id = user_id;
END $$

CREATE PROCEDURE log_user_login(IN user_id INT, IN ip_address VARCHAR(45))
BEGIN
    INSERT INTO system_logs (action, user_id, affected_username, timestamp, details)
    SELECT 'LOGIN', id, username, NOW(), CONCAT('IP Address: ', ip_address) FROM users WHERE id = user_id;
    INSERT INTO access_log (user_id, ip_address, login_time) VALUES (user_id, ip_address, NOW());
END $$

DELIMITER ;

-- Eliminar el usuario 'test' si existe
DROP USER IF EXISTS 'test'@'localhost';
FLUSH PRIVILEGES;

-- Crear nuevamente el usuario 'test' con la contraseña y permisos correctos
CREATE USER 'test'@'localhost' IDENTIFIED BY 'Login12345@';
GRANT ALL PRIVILEGES ON `loginsystem_db`.* TO 'test'@'localhost';
FLUSH PRIVILEGES;

-- Restablecer la contraseña por si existía algún conflicto y asegurar permisos
ALTER USER 'test'@'localhost' IDENTIFIED BY 'Login12345@';
GRANT ALL PRIVILEGES ON `loginsystem_db`.* TO 'test'@'localhost';
FLUSH PRIVILEGES;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
