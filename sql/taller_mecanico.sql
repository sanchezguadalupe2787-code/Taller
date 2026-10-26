-- =====================================================================
-- Sistema de Gestión para Taller Mecánico — D'Juniors
-- Base de datos MySQL / MariaDB
-- =====================================================================

CREATE DATABASE IF NOT EXISTS taller_mecanico
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE taller_mecanico;

-- ---------------------------------------------------------------------
-- Módulo de usuarios (niveles de acceso)
-- ---------------------------------------------------------------------
CREATE TABLE usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  usuario VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  rol ENUM('administrador','recepcionista','mecanico') NOT NULL DEFAULT 'recepcionista',
  estado ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
  fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Módulo de clientes
-- ---------------------------------------------------------------------
CREATE TABLE clientes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(150) NOT NULL,
  telefono VARCHAR(20),
  email VARCHAR(150),
  direccion VARCHAR(255),
  acepta_aviso_privacidad TINYINT(1) NOT NULL DEFAULT 0,
  fecha_aceptacion_privacidad DATETIME NULL,
  fecha_registro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  estado ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
  usuario VARCHAR(50) NULL UNIQUE,
  password VARCHAR(255) NULL,
  portal_activo TINYINT(1) NOT NULL DEFAULT 0,
  fecha_ultimo_acceso DATETIME NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Módulo de vehículos
-- ---------------------------------------------------------------------
CREATE TABLE vehiculos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  cliente_id INT NOT NULL,
  marca VARCHAR(60) NOT NULL,
  modelo VARCHAR(60) NOT NULL,
  anio INT,
  placas VARCHAR(20),
  color VARCHAR(40),
  numero_serie VARCHAR(60),
  notas TEXT,
  fecha_registro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Módulo de órdenes de servicio
-- ---------------------------------------------------------------------
CREATE TABLE ordenes_servicio (
  id INT AUTO_INCREMENT PRIMARY KEY,
  vehiculo_id INT NOT NULL,
  usuario_id INT,
  problema_reportado TEXT NOT NULL,
  diagnostico TEXT,
  fecha_ingreso DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_entrega_estimada DATE,
  fecha_entrega_real DATETIME,
  estado ENUM('recibido','en_diagnostico','en_reparacion','esperando_refacciones','listo','entregado','cancelado') NOT NULL DEFAULT 'recibido',
  FOREIGN KEY (vehiculo_id) REFERENCES vehiculos(id) ON DELETE CASCADE,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Módulo de servicios y reparaciones
-- ---------------------------------------------------------------------
CREATE TABLE servicios_reparaciones (
  id INT AUTO_INCREMENT PRIMARY KEY,
  orden_id INT NOT NULL,
  descripcion VARCHAR(255) NOT NULL,
  observaciones TEXT,
  recomendaciones TEXT,
  costo_mano_obra DECIMAL(10,2) NOT NULL DEFAULT 0,
  fecha_registro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (orden_id) REFERENCES ordenes_servicio(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Módulo de refacciones (inventario)
-- ---------------------------------------------------------------------
CREATE TABLE refacciones (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(150) NOT NULL,
  descripcion VARCHAR(255),
  stock INT NOT NULL DEFAULT 0,
  precio_unitario DECIMAL(10,2) NOT NULL DEFAULT 0,
  fecha_registro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE refacciones_usadas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  orden_id INT NOT NULL,
  refaccion_id INT NOT NULL,
  cantidad INT NOT NULL DEFAULT 1,
  precio_unitario DECIMAL(10,2) NOT NULL DEFAULT 0,
  FOREIGN KEY (orden_id) REFERENCES ordenes_servicio(id) ON DELETE CASCADE,
  FOREIGN KEY (refaccion_id) REFERENCES refacciones(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Módulo de cotizaciones y pagos
-- ---------------------------------------------------------------------
CREATE TABLE cotizaciones (
  id INT AUTO_INCREMENT PRIMARY KEY,
  orden_id INT NOT NULL,
  monto_estimado DECIMAL(10,2) NOT NULL DEFAULT 0,
  detalle TEXT,
  fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  estado ENUM('pendiente','aprobada','rechazada') NOT NULL DEFAULT 'pendiente',
  FOREIGN KEY (orden_id) REFERENCES ordenes_servicio(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE pagos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  orden_id INT NOT NULL,
  monto DECIMAL(10,2) NOT NULL,
  metodo_pago ENUM('efectivo','tarjeta','transferencia','otro') NOT NULL DEFAULT 'efectivo',
  fecha_pago DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  notas VARCHAR(255),
  FOREIGN KEY (orden_id) REFERENCES ordenes_servicio(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Datos iniciales
-- ---------------------------------------------------------------------

-- Usuario administrador por defecto -> usuario: admin  /  contraseña: admin123
INSERT INTO usuarios (nombre, usuario, password, rol) VALUES
('Administrador', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'administrador');
-- Hash generado con password_hash('admin123', PASSWORD_BCRYPT)

-- Datos de ejemplo
-- Cliente "Juan Pérez" con acceso al portal -> usuario: juan.perez  /  contraseña: cliente123
INSERT INTO clientes (nombre, telefono, email, direccion, acepta_aviso_privacidad, fecha_aceptacion_privacidad, usuario, password, portal_activo) VALUES
('Juan Pérez', '555-123-4567', 'juan.perez@correo.com', 'Av. Reforma 123', 1, NOW(), 'juan.perez', '$2y$10$XKdRsIco/CqsZNiWb1Fd9e0f2BGkIwmARwmjhJwMmFWan6Dku41P6', 1);
-- Hash generado con password_hash('cliente123', PASSWORD_BCRYPT)

INSERT INTO clientes (nombre, telefono, email, direccion) VALUES
('María López', '555-987-6543', 'maria.lopez@correo.com', 'Calle Hidalgo 45');

INSERT INTO vehiculos (cliente_id, marca, modelo, anio, placas, color) VALUES
(1, 'Nissan', 'Sentra', 2018, 'ABC-123', 'Gris'),
(2, 'Chevrolet', 'Aveo', 2020, 'XYZ-789', 'Rojo');

INSERT INTO refacciones (nombre, descripcion, stock, precio_unitario) VALUES
('Filtro de aceite', 'Filtro de aceite estándar', 25, 120.00),
('Balatas delanteras', 'Juego de balatas delanteras', 10, 450.00),
('Bujía', 'Bujía de encendido', 40, 65.00);
