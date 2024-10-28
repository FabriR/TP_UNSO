Al momento de importación por ejemplo en debian u OS similar, tiene que estar los paquetes necesarios para el funcionamiento, como php, php-mysql y mysql

La instalación es sencilla:

sudo apt update

sudo apt php

sudo apt mysql-server

En el caso de mysql al no estar de manera nativa en el repositorio, hacer los siguientes pasos: 

wget https://dev.mysql.com/get/mysql-apt-config_0.8.33-1_all.deb (u otra version mas actual)

Estas son las dependencias que necesita mysql-server

sudo apt install lsb-release

sudo apt install gnupg

sudo dpkg -i mysql-apt-config_0.8.22-1_all.deb (seguir los pasos que indica)

sudo apt update 

sudo apt install mysql-server

#Instalar el controlador PDO para MySQL

sudo apt install php-mysql

#Importación desde terminal

sudo mysql -uroot -p(password) < /ruta/loginsystem.sql (automaticamente se crea el usuario "test" con la contraseña y los privilegios necesarios)

#Visualizacion de logs. 

#Visualizacion de usuarios creados

USE loginsystem_db;
SELECT action, affected_username AS username, timestamp AS created_at
FROM audit_log
WHERE action = 'CREATE_USER'
ORDER BY timestamp DESC;

#Usuarios eliminados

USE loginsystem_db;
SELECT action, affected_username AS username, timestamp AS deleted_at
FROM audit_log
WHERE action = 'DELETE_USER'
ORDER BY timestamp DESC;

#Usuarios con detalles

USE loginsystem_db;
SELECT id, username, email, role, friend_name, mother_name, nickname, created_at
FROM users
ORDER BY created_at DESC;

#Filtrar por rol

USE loginsystem_db;
SELECT id, username, email, role, friend_name, mother_name, nickname, created_at
FROM users
WHERE role = 'admin'
ORDER BY created_at DESC;

#Visualizar usuarios con más detalles desde audit_log

SELECT action, affected_username AS username, timestamp AS created_at, details
FROM audit_log
WHERE action = 'CREATE_USER'
ORDER BY timestamp DESC;


Windows: 
Simplemente importar la base de datos en import/importar

Los usuarios como admin o test se encuentran en el archivo usuarios.txt
