-- --------------------------------------------------------
-- Host:                         localhost
-- Server version:               5.5.35 - MySQL Community Server (GPL)
-- Server OS:                    Win64
-- HeidiSQL Version:             12.5.0.6677
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for taller_mecanico
CREATE DATABASE IF NOT EXISTS `taller_mecanico` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;
USE `taller_mecanico`;

-- Dumping structure for table taller_mecanico.clientes
CREATE TABLE IF NOT EXISTS `clientes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefono` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `direccion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `acepta_aviso_privacidad` tinyint(1) NOT NULL DEFAULT '0',
  `fecha_aceptacion_privacidad` datetime DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `estado` enum('activo','inactivo') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'activo',
  `usuario` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `portal_activo` tinyint(1) NOT NULL DEFAULT '0',
  `fecha_ultimo_acceso` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuario` (`usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table taller_mecanico.clientes: ~2 rows (approximately)
DELETE FROM `clientes`;
INSERT INTO `clientes` (`id`, `nombre`, `telefono`, `email`, `direccion`, `acepta_aviso_privacidad`, `fecha_aceptacion_privacidad`, `fecha_registro`, `estado`, `usuario`, `password`, `portal_activo`, `fecha_ultimo_acceso`) VALUES
	(1, 'Juan Perez', '092210803', 'abisan994@gmail.com', 'av.veracruz', 0, NULL, '2026-10-11 05:38:09', 'activo', NULL, NULL, 0, NULL),
	(2, 'María López', '555-987-6543', 'maria.lopez@correo.com', 'Calle Hidalgo 45', 0, NULL, '2026-10-11 05:38:09', 'activo', NULL, NULL, 0, NULL);

-- Dumping structure for table taller_mecanico.cotizaciones
CREATE TABLE IF NOT EXISTS `cotizaciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `orden_id` int(11) NOT NULL,
  `monto_estimado` decimal(10,2) NOT NULL DEFAULT '0.00',
  `detalle` text COLLATE utf8mb4_unicode_ci,
  `fecha` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `estado` enum('pendiente','aprobada','rechazada') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendiente',
  PRIMARY KEY (`id`),
  KEY `idx_cotizaciones_orden` (`orden_id`),
  CONSTRAINT `fk_cotizaciones_ordenes` FOREIGN KEY (`orden_id`) REFERENCES `ordenes_servicio` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table taller_mecanico.cotizaciones: ~0 rows (approximately)
DELETE FROM `cotizaciones`;

-- Dumping structure for table taller_mecanico.ordenes_servicio
CREATE TABLE IF NOT EXISTS `ordenes_servicio` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vehiculo_id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `problema_reportado` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `diagnostico` text COLLATE utf8mb4_unicode_ci,
  `fecha_ingreso` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_entrega_estimada` date DEFAULT NULL,
  `fecha_entrega_real` datetime DEFAULT NULL,
  `estado` enum('recibido','en_diagnostico','en_reparacion','esperando_refacciones','listo','entregado','cancelado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'recibido',
  PRIMARY KEY (`id`),
  KEY `idx_orden_vehiculo` (`vehiculo_id`),
  KEY `idx_orden_usuario` (`usuario_id`),
  CONSTRAINT `fk_ordenes_usuarios` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_ordenes_vehiculos` FOREIGN KEY (`vehiculo_id`) REFERENCES `vehiculos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table taller_mecanico.ordenes_servicio: ~0 rows (approximately)
DELETE FROM `ordenes_servicio`;

-- Dumping structure for table taller_mecanico.pagos
CREATE TABLE IF NOT EXISTS `pagos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `orden_id` int(11) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `metodo_pago` enum('efectivo','tarjeta','transferencia','otro') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'efectivo',
  `fecha_pago` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `notas` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_pagos_orden` (`orden_id`),
  CONSTRAINT `fk_pagos_ordenes` FOREIGN KEY (`orden_id`) REFERENCES `ordenes_servicio` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table taller_mecanico.pagos: ~0 rows (approximately)
DELETE FROM `pagos`;

-- Dumping structure for table taller_mecanico.refacciones
CREATE TABLE IF NOT EXISTS `refacciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stock` int(11) NOT NULL DEFAULT '0',
  `precio_unitario` decimal(10,2) NOT NULL DEFAULT '0.00',
  `fecha_registro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table taller_mecanico.refacciones: ~3 rows (approximately)
DELETE FROM `refacciones`;
INSERT INTO `refacciones` (`id`, `nombre`, `descripcion`, `stock`, `precio_unitario`, `fecha_registro`) VALUES
	(1, 'Filtro de aceite', 'Filtro de aceite estándar', 25, 120.00, '2026-10-11 05:38:09'),
	(2, 'Balatas delanteras', 'Juego de balatas delanteras', 10, 450.00, '2026-10-11 05:38:09'),
	(3, 'Bujía', 'Bujía de encendido', 40, 65.00, '2026-10-11 05:38:09');

-- Dumping structure for table taller_mecanico.refacciones_usadas
CREATE TABLE IF NOT EXISTS `refacciones_usadas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `orden_id` int(11) NOT NULL,
  `refaccion_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL DEFAULT '1',
  `precio_unitario` decimal(10,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`),
  KEY `idx_refacciones_orden` (`orden_id`),
  KEY `idx_refacciones_usadas` (`refaccion_id`),
  CONSTRAINT `fk_refacciones_usadas_ordenes` FOREIGN KEY (`orden_id`) REFERENCES `ordenes_servicio` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_refacciones_usadas_refacciones` FOREIGN KEY (`refaccion_id`) REFERENCES `refacciones` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table taller_mecanico.refacciones_usadas: ~0 rows (approximately)
DELETE FROM `refacciones_usadas`;

-- Dumping structure for table taller_mecanico.servicios_reparaciones
CREATE TABLE IF NOT EXISTS `servicios_reparaciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `orden_id` int(11) NOT NULL,
  `descripcion` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `recomendaciones` text COLLATE utf8mb4_unicode_ci,
  `costo_mano_obra` decimal(10,2) NOT NULL DEFAULT '0.00',
  `fecha_registro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_servicios_orden` (`orden_id`),
  CONSTRAINT `fk_servicios_ordenes` FOREIGN KEY (`orden_id`) REFERENCES `ordenes_servicio` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table taller_mecanico.servicios_reparaciones: ~0 rows (approximately)
DELETE FROM `servicios_reparaciones`;

-- Dumping structure for table taller_mecanico.usuarios
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `usuario` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rol` enum('administrador','recepcionista','mecanico') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'recepcionista',
  `estado` enum('activo','inactivo') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'activo',
  `fecha_creacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuario` (`usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table taller_mecanico.usuarios: ~2 rows (approximately)
DELETE FROM `usuarios`;
INSERT INTO `usuarios` (`id`, `nombre`, `usuario`, `password`, `rol`, `estado`, `fecha_creacion`) VALUES
	(1, 'Administrador', 'admin', '$2y$10$3PAftv707GnbzbnhpxHBtefRFSf4vI4BvqbKaeDR06YiDz7JCbu.y', 'administrador', 'activo', '2026-10-11 05:54:17'),
	(2, 'Vicente', 'Mecanico', '$2y$10$sP7daVBjiZNWpqzA5scl0usV1fsR28xp66kNoMRXiXUSay0xbtKGm', 'mecanico', 'activo', '2026-10-11 06:37:57');

-- Dumping structure for table taller_mecanico.vehiculos
CREATE TABLE IF NOT EXISTS `vehiculos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cliente_id` int(11) NOT NULL,
  `marca` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `modelo` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `anio` int(11) DEFAULT NULL,
  `placas` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `color` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numero_serie` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notas` text COLLATE utf8mb4_unicode_ci,
  `fecha_registro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_vehiculo_cliente` (`cliente_id`),
  CONSTRAINT `fk_vehiculos_clientes` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table taller_mecanico.vehiculos: ~2 rows (approximately)
DELETE FROM `vehiculos`;
INSERT INTO `vehiculos` (`id`, `cliente_id`, `marca`, `modelo`, `anio`, `placas`, `color`, `numero_serie`, `notas`, `fecha_registro`) VALUES
	(1, 1, 'Nissan', 'Sentra', 2018, 'ABC-123', 'Gris', NULL, NULL, '2026-10-11 05:38:09'),
	(2, 2, 'Chevrolet', 'Aveo', 2020, 'XYZ-789', 'Rojo', NULL, NULL, '2026-10-11 05:38:09');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
