-- database/gestion.sql: esquema de la base y datos de prueba (todos ficticios)
-- Se carga desde bash con:   sudo mysql < database/gestion.sql
-- Ojo: si la base gestion ya existe, la borra y la vuelve a crear.

DROP DATABASE IF EXISTS gestion;
-- utf8mb4: guarda bien tildes, ñ y cualquier carácter
-- unicode_ci: funciona en MySQL y en MariaDB (la de XAMPP); utf8mb4_0900_ai_ci existe solo en MySQL 8
CREATE DATABASE gestion CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE gestion;

-- Usuarios del sistema (se usan para el login)
CREATE TABLE usuarios (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,     -- número que pone MySQL solo
  usuario       VARCHAR(50)  NOT NULL,
  password_hash VARCHAR(255) NOT NULL,                    -- el HASH, nunca la contraseña
  rol           ENUM('admin','operador') NOT NULL DEFAULT 'operador',
  creado_en     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_usuarios_usuario (usuario)                -- no hay dos usuarios iguales
) ENGINE=InnoDB;

-- Docentes
CREATE TABLE docentes (
  id        INT UNSIGNED NOT NULL AUTO_INCREMENT,
  dni       VARCHAR(10)  NOT NULL,                        -- texto: con el DNI no se hacen cuentas
  apellido  VARCHAR(60)  NOT NULL,
  nombre    VARCHAR(60)  NOT NULL,
  email     VARCHAR(120) NULL,                            -- NULL = puede quedar sin dato
  telefono  VARCHAR(20)  NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_docentes_dni (dni)                        -- el DNI no se repite
) ENGINE=InnoDB;

-- Títulos
CREATE TABLE titulos (
  id      INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nombre  VARCHAR(150) NOT NULL,
  tipo    ENUM('docente','habilitante','supletorio') NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

-- Qué títulos tiene cada docente (relación muchos a muchos)
CREATE TABLE docente_titulo (
  docente_id    INT UNSIGNED NOT NULL,
  titulo_id     INT UNSIGNED NOT NULL,
  fecha_egreso  DATE NOT NULL,
  PRIMARY KEY (docente_id, titulo_id),                    -- el mismo título no se carga dos veces
  CONSTRAINT fk_dt_docente FOREIGN KEY (docente_id)
    REFERENCES docentes(id) ON DELETE CASCADE,            -- si se borra el docente, se borran sus títulos
  CONSTRAINT fk_dt_titulo FOREIGN KEY (titulo_id)
    REFERENCES titulos(id) ON DELETE RESTRICT             -- no se borra un título que alguien tiene
) ENGINE=InnoDB;

-- Inscripciones a cargos o materias, con su puntaje
CREATE TABLE inscripciones (
  id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  docente_id     INT UNSIGNED NOT NULL,
  cargo_materia  VARCHAR(100) NOT NULL,
  anio           YEAR NOT NULL,
  puntaje        DECIMAL(6,2) NOT NULL,                   -- exacto: FLOAT redondea
  PRIMARY KEY (id),
  UNIQUE KEY uq_insc (docente_id, cargo_materia, anio),   -- una inscripción por materia y año
  CONSTRAINT fk_insc_docente FOREIGN KEY (docente_id)
    REFERENCES docentes(id) ON DELETE RESTRICT,           -- no se borra un docente con inscripciones
  CONSTRAINT chk_puntaje CHECK (puntaje >= 0)             -- no hay puntajes negativos
) ENGINE=InnoDB;

-- ---------- Datos de prueba (ficticios) ----------

INSERT INTO docentes (dni, apellido, nombre, email, telefono) VALUES
  ('28456123', 'Gómez',     'Laura',     'lgomez@mail.com',     '2901 451234'),
  ('31987654', 'Fernández', 'Martín',    'mfernandez@mail.com', '2901 462345'),
  ('35123789', 'Ríos',      'Carolina',  'crios@mail.com',      '2901 473456'),
  ('27654321', 'Paz',       'Diego',     NULL,                  '2901 484567'),
  ('38765432', 'Medina',    'Sofía',     'smedina@mail.com',    NULL),
  ('33221100', 'Benítez',   'Damián',    'dbenitez@mail.com',   '2901 495678'),
  ('40112233', 'Sosa',      'Valentina', 'vsosa@mail.com',      '2901 406789'),
  ('29887766', 'Luna',      'Ricardo',   'rluna@mail.com',      '2901 417890');

INSERT INTO titulos (nombre, tipo) VALUES
  ('Profesor/a de Educación Secundaria en Informática', 'docente'),
  ('Profesor/a de Matemática', 'docente'),
  ('Ingeniero/a en Sistemas de Información', 'habilitante'),
  ('Analista de Sistemas', 'habilitante'),
  ('Técnico/a Superior en Desarrollo de Software', 'habilitante'),
  ('Técnico/a en Computación (nivel secundario)', 'supletorio');

INSERT INTO docente_titulo (docente_id, titulo_id, fecha_egreso) VALUES
  (1, 1, '2008-12-15'), (1, 4, '2005-07-20'), (2, 3, '2012-03-10'), (3, 5, '2016-12-01'),
  (4, 2, '2003-11-28'), (4, 6, '1997-12-10'), (5, 6, '2014-12-12'), (6, 1, '2011-12-09'),
  (7, 5, '2021-07-15'), (8, 4, '2000-06-30');

INSERT INTO inscripciones (docente_id, cargo_materia, anio, puntaje) VALUES
  (1, 'Redes I',        2027, 48.50), (1, 'Base de Datos',  2027, 48.50),
  (2, 'Redes I',        2027, 31.75), (2, 'Software II',    2027, 31.75),
  (3, 'Software II',    2027, 24.40), (3, 'Redes I',        2027, 22.40),
  (4, 'Base de Datos',  2027, 12.00), (5, 'Redes I',        2027,  9.25),
  (6, 'Software II',    2027, 41.10), (6, 'Base de Datos',  2027, 41.10),
  (6, 'Programación I', 2027, 41.10), (8, 'Programación I', 2027, 35.60),
  (8, 'Redes I',        2027, 31.75);

-- ---------- Usuario de la aplicación ----------
-- La aplicación NO se conecta como root. Crear el usuario a mano, en mysql>,
-- con una contraseña propia (la misma que después va en config.php):
--   CREATE USER 'app_gestion'@'localhost' IDENTIFIED BY 'una-contraseña-segura';
--   GRANT SELECT, INSERT, UPDATE, DELETE ON gestion.* TO 'app_gestion'@'localhost';
