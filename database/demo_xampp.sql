-- database/demo_xampp.sql: usuario de la base y usuarios de demo, SOLO para probar en XAMPP
-- Nunca se carga en un servidor: las contraseñas de este archivo son públicas (están en el README).
-- Lo carga instalar-xampp.bat después de gestion.sql. A mano: phpMyAdmin, pestaña Importar.

-- El archivo está en UTF-8: que el cliente lo lea así
SET NAMES utf8mb4;
USE gestion;

-- Usuario de la base para la aplicación, con permisos mínimos
-- @'localhost': solo se puede conectar desde esta misma PC
CREATE USER IF NOT EXISTS 'app_gestion'@'localhost' IDENTIFIED BY 'Xampp-Demo-2026';
-- Si ya existía con otra contraseña, queda con la de config/config.xampp.php
ALTER USER 'app_gestion'@'localhost' IDENTIFIED BY 'Xampp-Demo-2026';
GRANT SELECT, INSERT, UPDATE, DELETE ON gestion.* TO 'app_gestion'@'localhost';

-- Usuarios de demo: en la base va el hash bcrypt (password_hash), nunca la contraseña
--   admin      Admin-Demo-2026
--   operador1  Operador-Demo-2026
INSERT INTO usuarios (usuario, password_hash, rol) VALUES
  ('admin',     '$2y$12$GZwKT8X.241s7BIAfJe1oeUDO9pWILFwB01lPlPxbPQzrrUmFopdG', 'admin'),
  ('operador1', '$2y$12$ftbYbbaFVdR0WMS75VjjDevQIx2JGIYmJ3qyh/g44OK.oFBNAzllK', 'operador');
