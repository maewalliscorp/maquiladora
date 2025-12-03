-- ============================================================
-- CONTROL DE BULTOS - SQL COMPLETO
-- Módulo de seguimiento de producción por operación
-- ============================================================

-- Tabla 1: control_bultos
-- Información general del control de bultos
CREATE TABLE IF NOT EXISTS `control_bultos` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `idmaquiladora` INT(11) NOT NULL COMMENT 'FK a maquiladora',
  `ordenProduccionId` INT(11) NOT NULL COMMENT 'FK a orden_produccion - REQUERIDO para calcular progreso',
  `inspeccionId` INT(11) NULL COMMENT 'FK a inspeccion - opcional',
  `estilo` VARCHAR(100) NOT NULL COMMENT 'Estilo de la prenda',
  `orden` VARCHAR(100) NOT NULL COMMENT 'Número de orden',
  `cantidad_total` INT(11) NOT NULL COMMENT 'Total de prendas en la orden',
  `estado` ENUM('en_proceso', 'listo_armado', 'completado') NOT NULL DEFAULT 'en_proceso' COMMENT 'Estado del control',
  `fecha_creacion` DATETIME NULL,
  `usuario_creacion` INT(11) NULL COMMENT 'FK a usuario',
  `created_at` DATETIME NULL,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_maquiladora` (`idmaquiladora`),
  INDEX `idx_orden_produccion` (`ordenProduccionId`),
  INDEX `idx_inspeccion` (`inspeccionId`),
  INDEX `idx_estado` (`estado`),
  CONSTRAINT `fk_control_bultos_maquiladora` 
    FOREIGN KEY (`idmaquiladora`) 
    REFERENCES `maquiladora` (`idmaquiladora`) 
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_control_bultos_orden` 
    FOREIGN KEY (`ordenProduccionId`) 
    REFERENCES `orden_produccion` (`id`) 
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
COMMENT='Control principal de bultos con seguimiento de producción';

-- Tabla 2: bultos
-- Detalle de cada bulto individual
CREATE TABLE IF NOT EXISTS `bultos` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `controlBultoId` INT(11) UNSIGNED NOT NULL COMMENT 'FK a control_bultos',
  `numero_bulto` VARCHAR(50) NOT NULL COMMENT 'Número del bulto',
  `talla` VARCHAR(20) NOT NULL COMMENT 'Talla del bulto',
  `cantidad` INT(11) NOT NULL COMMENT 'Cantidad de prendas en el bulto',
  `observaciones` TEXT NULL,
  `created_at` DATETIME NULL,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_control_bulto` (`controlBultoId`),
  CONSTRAINT `fk_bultos_control` 
    FOREIGN KEY (`controlBultoId`) 
    REFERENCES `control_bultos` (`id`) 
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
COMMENT='Detalle de bultos individuales';

-- Tabla 3: operaciones_control
-- Operaciones del control con seguimiento de progreso
CREATE TABLE IF NOT EXISTS `operaciones_control` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `controlBultoId` INT(11) UNSIGNED NOT NULL COMMENT 'FK a control_bultos',
  `nombre_operacion` VARCHAR(200) NOT NULL COMMENT 'Nombre de la operación, ej: PEGAR PUÑO',
  `piezas_requeridas` INT(11) NOT NULL COMMENT 'Total de piezas requeridas, ej: 40 puños para 20 camisas',
  `piezas_completadas` INT(11) NOT NULL DEFAULT 0 COMMENT 'Piezas completadas hasta ahora',
  `porcentaje_completado` DECIMAL(5,2) NOT NULL DEFAULT 0.00 COMMENT 'Porcentaje calculado automáticamente',
  `es_componente` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1 si es componente pre-armado, 0 si es armado final',
  `orden` INT(11) NOT NULL DEFAULT 0 COMMENT 'Orden de la operación en el proceso',
  `created_at` DATETIME NULL,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_control_bulto` (`controlBultoId`),
  INDEX `idx_es_componente` (`es_componente`),
  INDEX `idx_orden` (`orden`),
  CONSTRAINT `fk_operaciones_control` 
    FOREIGN KEY (`controlBultoId`) 
    REFERENCES `control_bultos` (`id`) 
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
COMMENT='Operaciones del control con seguimiento de progreso';

-- Tabla 4: registros_produccion
-- Registro de producción por empleado y operación
CREATE TABLE IF NOT EXISTS `registros_produccion` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `operacionControlId` INT(11) UNSIGNED NOT NULL COMMENT 'FK a operaciones_control',
  `empleadoId` INT(11) NOT NULL COMMENT 'FK a empleado',
  `cantidad_producida` INT(11) NOT NULL COMMENT 'Cantidad producida, ej: 20 puños',
  `fecha_registro` DATE NOT NULL COMMENT 'Fecha del registro',
  `hora_inicio` TIME NULL COMMENT 'Hora de inicio del trabajo',
  `hora_fin` TIME NULL COMMENT 'Hora de fin del trabajo',
  `tiempo_empleado` INT(11) NULL COMMENT 'Tiempo empleado en minutos (calculado)',
  `registrado_por` INT(11) NULL COMMENT 'FK a usuario que registró',
  `observaciones` TEXT NULL,
  `created_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_operacion_control` (`operacionControlId`),
  INDEX `idx_empleado` (`empleadoId`),
  INDEX `idx_fecha_registro` (`fecha_registro`),
  CONSTRAINT `fk_registros_operacion` 
    FOREIGN KEY (`operacionControlId`) 
    REFERENCES `operaciones_control` (`id`) 
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_registros_empleado` 
    FOREIGN KEY (`empleadoId`) 
    REFERENCES `empleado` (`id`) 
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
COMMENT='Registros de producción por empleado';

-- Tabla 5: plantillas_operaciones
-- Plantillas de operaciones por tipo de prenda
CREATE TABLE IF NOT EXISTS `plantillas_operaciones` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `idmaquiladora` INT(11) NOT NULL COMMENT 'FK a maquiladora',
  `tipo_prenda` VARCHAR(100) NOT NULL COMMENT 'Tipo de prenda: CAMISA, PANTALÓN, etc.',
  `nombre_plantilla` VARCHAR(200) NOT NULL COMMENT 'Nombre descriptivo de la plantilla',
  `operaciones` JSON NOT NULL COMMENT 'Array JSON de operaciones con piezas_por_prenda',
  `activo` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1 = activa, 0 = inactiva',
  `created_at` DATETIME NULL,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_maquiladora` (`idmaquiladora`),
  INDEX `idx_tipo_prenda` (`tipo_prenda`),
  INDEX `idx_activo` (`activo`),
  CONSTRAINT `fk_plantillas_maquiladora` 
    FOREIGN KEY (`idmaquiladora`) 
    REFERENCES `maquiladora` (`idmaquiladora`) 
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
COMMENT='Plantillas de operaciones por tipo de prenda';

-- ============================================================
-- DATOS DE EJEMPLO - Plantilla para CAMISA
-- ============================================================

-- Insertar una plantilla de ejemplo para tipo CAMISA
-- NOTA: Ajusta el idmaquiladora según tu base de datos
INSERT INTO `plantillas_operaciones` 
  (`idmaquiladora`, `tipo_prenda`, `nombre_plantilla`, `operaciones`, `activo`, `created_at`) 
VALUES 
  (1, 'CAMISA', 'Plantilla Camisa Estándar', 
   '[
     {"nombre": "PREPARAR ETIQUETA", "piezas_por_prenda": 1, "es_componente": true, "orden": 1},
     {"nombre": "PEGAR PUÑO", "piezas_por_prenda": 2, "es_componente": true, "orden": 2},
     {"nombre": "S/H ALETILLA", "piezas_por_prenda": 1, "es_componente": true, "orden": 3},
     {"nombre": "PEGAR ALETILLA", "piezas_por_prenda": 1, "es_componente": true, "orden": 4},
     {"nombre": "H/COST DE CARGA", "piezas_por_prenda": 1, "es_componente": true, "orden": 5},
     {"nombre": "HACER CUADRO", "piezas_por_prenda": 1, "es_componente": true, "orden": 6},
     {"nombre": "ENCAJONAR CUELLO", "piezas_por_prenda": 1, "es_componente": true, "orden": 7},
     {"nombre": "PEGAR CUELLO", "piezas_por_prenda": 1, "es_componente": true, "orden": 8},
     {"nombre": "UNIR HOMBROS", "piezas_por_prenda": 1, "es_componente": true, "orden": 9},
     {"nombre": "PEGAR MANGAS", "piezas_por_prenda": 2, "es_componente": true, "orden": 10},
     {"nombre": "CERRAR COSTADOS", "piezas_por_prenda": 2, "es_componente": true, "orden": 11},
     {"nombre": "HACER OJALES", "piezas_por_prenda": 6, "es_componente": true, "orden": 12},
     {"nombre": "PEGAR BOTONES", "piezas_por_prenda": 6, "es_componente": true, "orden": 13},
     {"nombre": "ARMADO DE PRENDA", "piezas_por_prenda": 1, "es_componente": false, "orden": 14}
   ]',
   1,
   NOW()
  );

-- ============================================================
-- VERIFICACIÓN
-- ============================================================

-- Verificar que las tablas se crearon correctamente
SELECT 
  TABLE_NAME, 
  TABLE_ROWS, 
  CREATE_TIME 
FROM 
  information_schema.TABLES 
WHERE 
  TABLE_SCHEMA = DATABASE() 
  AND TABLE_NAME IN (
    'control_bultos', 
    'bultos', 
    'operaciones_control', 
    'registros_produccion', 
    'plantillas_operaciones'
  );

-- Ver la plantilla de ejemplo insertada
SELECT 
  id,
  idmaquiladora,
  tipo_prenda,
  nombre_plantilla,
  JSON_PRETTY(operaciones) as operaciones_formateadas,
  activo,
  created_at
FROM 
  plantillas_operaciones
WHERE 
  tipo_prenda = 'CAMISA';
