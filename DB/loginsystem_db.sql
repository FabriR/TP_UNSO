SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Crear la base de datos si no existe y usarla
CREATE DATABASE IF NOT EXISTS `loginsystem_db`;
USE `loginsystem_db`;

-- Eliminar las tablas si ya existen
DROP TABLE IF EXISTS `access_log`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `audit_log`;

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

-- Crear tabla de usuarios con campos adicionales
CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `friend_name` varchar(100) DEFAULT NULL,
  `mother_name` varchar(100) DEFAULT NULL,
  `nickname` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Crear tabla de auditoría para registrar acciones
CREATE TABLE `audit_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `action` varchar(50) NOT NULL,  -- Ejemplo: 'CREATE_USER', 'DELETE_USER', 'LOGIN'
  `user_id` int(11) DEFAULT NULL, -- El ID del usuario afectado
  `affected_username` varchar(100) DEFAULT NULL, -- El nombre del usuario afectado
  `action_by` int(11) DEFAULT NULL, -- El ID del administrador o usuario que realizó la acción
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(), -- Fecha y hora de la acción
  `details` text DEFAULT NULL,  -- Detalles adicionales de la acción (como IP, etc.)
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insertar datos iniciales en la tabla de usuarios con campos adicionales
INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `friend_name`, `mother_name`, `nickname`, `created_at`) VALUES
(0, 'admin', 'admin@admin.com', '$2y$10$Y0dGFGGSeYUyUv22nSRtA..UpGLdAQ3qFhi5xI.knvWsNlvVs//7u', 'admin', 'test', 'test', 'test', '2024-10-03 00:11:59'),
(1, 'test', 'test@test.com', '$2y$10$Y0dGFGGSeYUyUv22nSRtA..UpGLdAQ3qFhi5xI.knvWsNlvVs//7u', 'user', 'test', 'test', 'test', '2024-10-03 00:11:59');

-- Índices para tablas
ALTER TABLE `access_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

-- Configurar AUTO_INCREMENT
ALTER TABLE `access_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

-- Clave foránea en access_log referenciando users
ALTER TABLE `access_log`
  ADD CONSTRAINT `access_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

-- Crear triggers para auditoría

-- Trigger para registrar usuarios creados
DELIMITER $$
CREATE TRIGGER after_user_insert
AFTER INSERT ON users
FOR EACH ROW
BEGIN
    INSERT INTO audit_log (action, affected_username, action_by, timestamp, details)
    VALUES ('CREATE_USER', NEW.username, NULL, NOW(), CONCAT('User created with email: ', NEW.email));
END$$
DELIMITER ;

-- Trigger para registrar usuarios eliminados
DELIMITER $$
CREATE TRIGGER after_user_delete
AFTER DELETE ON users
FOR EACH ROW
BEGIN
    INSERT INTO audit_log (action, affected_username, action_by, timestamp, details)
    VALUES ('DELETE_USER', OLD.username, NULL, NOW(), CONCAT('User with email: ', OLD.email, ' was deleted.'));
END$$
DELIMITER ;

-- Crear usuario de MySQL y asignar permisos sobre la base de datos
DROP USER IF EXISTS 'test'@'localhost';
CREATE USER 'test'@'localhost' IDENTIFIED BY 'Login12345@';
GRANT ALL PRIVILEGES ON `loginsystem_db`.* TO 'test'@'localhost';
FLUSH PRIVILEGES;

-- Confirmar los cambios realizados
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
