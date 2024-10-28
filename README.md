# Docente: Germán Gabriel Gianotti 
## Grupo B 
## Alumnos: 
Cortes Nahuel	ncortesguiet@gmail.com	

Fabricio	Robaina Buchwitz robaina.buchwitz@gmail.com	

Landro Nahuel	nahue.landro321@gmail.com	

Fernández de la Cruz	Federico Gustavo	federicofernandezdelacruz@gmail.com	

Malsenido Bruno	bruno8080808080808080@gmail.com

# Proyecto de Sistema de Autenticación de Usuarios

Este proyecto es un sistema de autenticación de usuarios desarrollado en PHP y MySQL, que incluye funcionalidades como registro, inicio de sesión, auditoría de accesos, y gestión de usuarios con roles.

## Características del Proyecto

- **Registro de Usuarios**: Permite a los usuarios crear una cuenta.
- **Inicio de Sesión**: Los usuarios pueden iniciar sesión y ser redirigidos según su rol.
- **Roles de Usuario**: Roles de `admin` y `user` para administrar los permisos.
- **Auditoría de Accesos**: Se registra cada inicio de sesión con detalles como IP y hora.
- **Gestión de Usuarios**: Los administradores pueden ver, editar y eliminar usuarios.

## Requisitos

- **Servidor Web**: Apache (XAMPP o WAMP recomendado para desarrollo local), debian que se encuentra detallado mas abajo
- **PHP**: Versión 7.4 o superior
- **MySQL**: Base de datos para gestionar los usuarios y registros de acceso
- **phpMyAdmin** (opcional): Para facilitar la administración de la base de datos.

## Configuración del Proyecto

1. **Clonar el Repositorio**

   ```bash
   git clone https://github.com/FabriR/TP_UNSO.git

## Instalación de Paquetes en Debian/OS Similar

## Para ejecutar el proyecto en Debian o sistemas similares, asegúrate de instalar los paquetes necesarios:

sudo apt update

sudo apt php

sudo apt mysql-server

## Descargar y configurar el repositorio MySQL

wget https://dev.mysql.com/get/mysql-apt-config_0.8.33-1_all.deb (u otra version mas actual)

Estas son las dependencias que necesita mysql-server

sudo apt install lsb-release

sudo apt install gnupg

sudo dpkg -i mysql-apt-config_0.8.22-1_all.deb (seguir los pasos que indica)

sudo apt update 

## Instalar MySQL

sudo apt install mysql-server

## Instalar el controlador PDO para MySQL

sudo apt install php-mysql

# Importación de la Base de Datos

sudo mysql -uroot -p(password) < /ruta/loginsystem.sql 
#### Nota: Este script de importación crea automáticamente el usuario test con los privilegios necesarios.

## Consultas de Auditoría y Visualización de Usuarios

USE loginsystem_db;
SELECT action, affected_username AS username, timestamp AS created_at
FROM audit_log
WHERE action = 'CREATE_USER'
ORDER BY timestamp DESC;

## Usuarios eliminados

USE loginsystem_db;
SELECT action, affected_username AS username, timestamp AS deleted_at
FROM audit_log
WHERE action = 'DELETE_USER'
ORDER BY timestamp DESC;

## Usuarios con detalles

USE loginsystem_db;
SELECT id, username, email, role, friend_name, mother_name, nickname, created_at
FROM users
ORDER BY created_at DESC;

## Filtrar por rol

USE loginsystem_db;
SELECT id, username, email, role, friend_name, mother_name, nickname, created_at
FROM users
WHERE role = 'admin'
ORDER BY created_at DESC;

## Visualizar usuarios con más detalles desde audit_log

SELECT action, affected_username AS username, timestamp AS created_at, details
FROM audit_log
WHERE action = 'CREATE_USER'
ORDER BY timestamp DESC;


# Importación de la Base de Datos en Windows: 
Si estás usando XAMPP o WAMP en Windows, simplemente importa la base de datos desde phpMyAdmin a través de la opción Importar.

#### Nota: Los usuarios "admin" y "test" están detallados en el archivo usuarios.txt.
