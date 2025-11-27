-- Crear tabla mrp_requerimiento
CREATE TABLE IF NOT EXISTS `mrp_requerimiento` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `mat` VARCHAR(255) NOT NULL COMMENT 'Material',
  `u` VARCHAR(50) NOT NULL COMMENT 'Unidad',
  `necesidad` DECIMAL(10,2) NOT NULL DEFAULT 0 COMMENT 'Necesidad',
  `stock` DECIMAL(10,2) NOT NULL DEFAULT 0 COMMENT 'Stock disponible',
  `comprar` DECIMAL(10,2) NOT NULL DEFAULT 0 COMMENT 'Cantidad a comprar',
  `created_at` DATETIME NULL DEFAULT NULL,
  `updated_at` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Crear tabla mrp_oc
CREATE TABLE IF NOT EXISTS `mrp_oc` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `prov` VARCHAR(255) NOT NULL COMMENT 'Proveedor',
  `mat` VARCHAR(255) NOT NULL COMMENT 'Material',
  `cant` DECIMAL(10,2) NOT NULL DEFAULT 0 COMMENT 'Cantidad',
  `u` VARCHAR(50) NOT NULL COMMENT 'Unidad',
  `eta` DATE NOT NULL COMMENT 'Fecha estimada de llegada',
  `created_at` DATETIME NULL DEFAULT NULL,
  `updated_at` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar datos de ejemplo
INSERT INTO `mrp_requerimiento` (`mat`, `u`, `necesidad`, `stock`, `comprar`) VALUES
('Tela Algodón 180g', 'm', 1200.00, 450.00, 750.00),
('Hilo 40/2', 'rollo', 35.00, 10.00, 25.00),
('Etiqueta talla', 'pz', 1000.00, 1200.00, 0.00);

INSERT INTO `mrp_oc` (`prov`, `mat`, `cant`, `u`, `eta`) VALUES
('Textiles MX', 'Tela Algodón 180g', 750.00, 'm', '2025-10-02'),
('Hilos del Norte', 'Hilo 40/2', 25.00, 'rollo', '2025-09-30');
