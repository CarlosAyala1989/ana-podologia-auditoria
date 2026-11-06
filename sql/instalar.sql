-- Instalación en la base seleccionada por DB_NAME. No borra tablas existentes.
-- ==============================================================================
-- SISTEMA DE GESTIÓN CLÍNICA: PODOLOGÍA ANA RAMÍREZ (v2.1)
-- Autenticación basada estrictamente en DNI / Cédula
-- Cuentas de demostración aprovisionadas por docker/seed-demo.php con claves de entorno
-- ==============================================================================


-- ------------------------------------------------------------------------------
-- 1. TABLA: roles
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `roles` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `slug` VARCHAR(30) NOT NULL UNIQUE COMMENT 'superadmin, admin, especialista, recepcionista',
  `nombre` VARCHAR(50) NOT NULL UNIQUE,
  `nivel_jerarquia` TINYINT UNSIGNED NOT NULL DEFAULT 3,
  `descripcion` VARCHAR(255) NULL,
  `creado_en` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 2. TABLA: usuarios
-- Autenticación exclusiva por DNI; contraseñas almacenadas como hash.
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `rol_id` INT UNSIGNED NOT NULL,
  `dni` VARCHAR(30) NOT NULL UNIQUE COMMENT 'Número de DNI / Cédula para inicio de sesión',
  `nombre` VARCHAR(100) NOT NULL,
  `apellido` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `debe_cambiar_password` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1: Obliga a cambiar clave al iniciar por primera vez',
  `password_cambiado_en` DATETIME NULL,
  `telefono` VARCHAR(30) NULL,
  `especialidad` VARCHAR(100) NULL,
  `numero_colegiado` VARCHAR(50) NULL,
  `estado` ENUM('activo', 'inactivo') NOT NULL DEFAULT 'activo',
  `ultimo_acceso` DATETIME NULL,
  `creado_en` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_usuarios_rol`
    FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  INDEX `idx_usuarios_dni` (`dni`),
  INDEX `idx_usuarios_rol_estado` (`rol_id`, `estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 3. TABLA: pacientes
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `pacientes` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `documento_identidad` VARCHAR(30) NOT NULL UNIQUE,
  `tipo_documento` ENUM('DNI', 'Cedula', 'Pasaporte', 'Otro') NOT NULL DEFAULT 'Cedula',
  `nombre` VARCHAR(100) NOT NULL,
  `apellido` VARCHAR(100) NOT NULL,
  `fecha_nacimiento` DATE NOT NULL,
  `genero` ENUM('Femenino', 'Masculino', 'Otro') NOT NULL,
  `telefono` VARCHAR(30) NOT NULL,
  `telefono_emergencia` VARCHAR(30) NULL,
  `contacto_emergencia` VARCHAR(150) NULL,
  `email` VARCHAR(150) NULL,
  `direccion` VARCHAR(255) NULL,
  `ocupacion` VARCHAR(120) NULL,
  `es_diabetico` TINYINT(1) NOT NULL DEFAULT 0,
  `tipo_diabetes` VARCHAR(50) NULL,
  `tiene_hipertension` TINYINT(1) NOT NULL DEFAULT 0,
  `problemas_circulatorios` TINYINT(1) NOT NULL DEFAULT 0,
  `usa_anticoagulantes` TINYINT(1) NOT NULL DEFAULT 0,
  `tiene_marcapasos` TINYINT(1) NOT NULL DEFAULT 0,
  `enfermedades_renales` TINYINT(1) NOT NULL DEFAULT 0,
  `otras_patologias` TEXT NULL,
  `alergias_medicamentos` TEXT NULL,
  `alergias_contacto` TEXT NULL,
  `tipo_calzado_habitual` VARCHAR(100) NULL,
  `talla_calzado` VARCHAR(10) NULL,
  `usa_plantillas` TINYINT(1) NOT NULL DEFAULT 0,
  `actividad_fisica` VARCHAR(100) NULL,
  `creado_por_usuario_id` INT UNSIGNED NOT NULL,
  `fecha_creacion` DATE NOT NULL,
  `hora_creacion` TIME NOT NULL,
  `estado` ENUM('activo', 'inactivo') NOT NULL DEFAULT 'activo',
  `eliminado_en` DATETIME NULL,
  `eliminado_por_usuario_id` INT UNSIGNED NULL,
  `creado_en` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_pacientes_creador`
    FOREIGN KEY (`creado_por_usuario_id`) REFERENCES `usuarios` (`id`)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_pacientes_eliminador`
    FOREIGN KEY (`eliminado_por_usuario_id`) REFERENCES `usuarios` (`id`)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  INDEX `idx_pacientes_documento` (`documento_identidad`),
  INDEX `idx_pacientes_apellidos_nombres` (`apellido`, `nombre`),
  INDEX `idx_pacientes_telefono` (`telefono`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 4. TABLA: historial_cambios_pacientes
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `historial_cambios_pacientes` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `paciente_id` INT UNSIGNED NOT NULL,
  `campo_modificado` VARCHAR(100) NOT NULL,
  `valor_anterior` TEXT NULL,
  `valor_nuevo` TEXT NULL,
  `modificado_por_usuario_id` INT UNSIGNED NOT NULL,
  `fecha_hora` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `motivo_cambio` VARCHAR(255) NULL,
  CONSTRAINT `fk_historial_paciente`
    FOREIGN KEY (`paciente_id`) REFERENCES `pacientes` (`id`)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_historial_usuario`
    FOREIGN KEY (`modificado_por_usuario_id`) REFERENCES `usuarios` (`id`)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  INDEX `idx_historial_paciente_fecha` (`paciente_id`, `fecha_hora` DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 5. TABLA: historias_clinicas
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `historias_clinicas` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `paciente_id` INT UNSIGNED NOT NULL,
  `especialista_id` INT UNSIGNED NOT NULL,
  `creado_por_usuario_id` INT UNSIGNED NOT NULL,
  `fecha_creacion` DATE NOT NULL,
  `hora_creacion` TIME NOT NULL,
  `fecha_atencion` DATETIME NOT NULL,
  `motivo_consulta` VARCHAR(255) NOT NULL,
  `diagnostico` TEXT NOT NULL,
  `procedimiento_realizado` TEXT NOT NULL,
  `evolucion_lesion` TEXT NULL,
  `prescripcion_recomendaciones` TEXT NULL,
  `observaciones_privadas` TEXT NULL,
  `proxima_visita_sugerida` DATE NULL,
  `dolor_escala` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `tipo_pisada` VARCHAR(50) NULL,
  `evaluacion_marcha` TEXT NULL,
  `exploracion_vascular` TEXT NULL,
  `exploracion_neurologica` TEXT NULL,
  `piel_unas` TEXT NULL,
  `estado` ENUM('activo', 'inactivo') NOT NULL DEFAULT 'activo',
  `eliminado_en` DATETIME NULL,
  `eliminado_por_usuario_id` INT UNSIGNED NULL,
  `creado_en` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_historia_paciente`
    FOREIGN KEY (`paciente_id`) REFERENCES `pacientes` (`id`)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_historia_especialista`
    FOREIGN KEY (`especialista_id`) REFERENCES `usuarios` (`id`)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_historia_creador`
    FOREIGN KEY (`creado_por_usuario_id`) REFERENCES `usuarios` (`id`)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  INDEX `idx_historia_paciente_fecha` (`paciente_id`, `fecha_atencion` DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 6. TABLA: citas
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `citas` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `paciente_id` INT UNSIGNED NOT NULL,
  `especialista_id` INT UNSIGNED NOT NULL,
  `creado_por_usuario_id` INT UNSIGNED NOT NULL,
  `fecha_creacion` DATE NOT NULL,
  `hora_creacion` TIME NOT NULL,
  `fecha_cita` DATE NOT NULL,
  `hora_inicio` TIME NOT NULL,
  `hora_fin` TIME NOT NULL,
  `motivo` VARCHAR(255) NOT NULL,
  `estado` ENUM('pendiente', 'en_atencion', 'finalizado', 'cancelado') NOT NULL DEFAULT 'pendiente',
  `notas` VARCHAR(255) NULL,
  `creado_en` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_citas_paciente`
    FOREIGN KEY (`paciente_id`) REFERENCES `pacientes` (`id`)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_citas_especialista`
    FOREIGN KEY (`especialista_id`) REFERENCES `usuarios` (`id`)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_citas_creador`
    FOREIGN KEY (`creado_por_usuario_id`) REFERENCES `usuarios` (`id`)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  INDEX `idx_citas_agenda_especialista` (`especialista_id`, `fecha_cita`, `hora_inicio`),
  INDEX `idx_citas_paciente` (`paciente_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 7. TABLA: logs_sistema
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `logs_sistema` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `usuario_id` INT UNSIGNED NULL,
  `nivel` ENUM('INFO', 'WARNING', 'SECURITY', 'ERROR') NOT NULL DEFAULT 'INFO',
  `modulo` VARCHAR(50) NOT NULL,
  `accion` VARCHAR(100) NOT NULL,
  `descripcion` TEXT NOT NULL,
  `ip_origen` VARCHAR(45) NOT NULL,
  `user_agent` VARCHAR(255) NULL,
  `fecha_hora` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_logs_usuario`
    FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE,
  INDEX `idx_logs_nivel_fecha` (`nivel`, `fecha_hora` DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Auditoría de citas, atenciones y cuentas del personal.
CREATE TABLE IF NOT EXISTS `auditoria_modulos` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `entidad` VARCHAR(40) NOT NULL,
  `registro_id` INT UNSIGNED NOT NULL,
  `campo` VARCHAR(80) NOT NULL,
  `valor_anterior` TEXT NULL,
  `valor_nuevo` TEXT NULL,
  `usuario_id` INT UNSIGNED NOT NULL,
  `motivo` VARCHAR(255) NOT NULL,
  `fecha_hora` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_auditoria_modulos_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  INDEX `idx_auditoria_entidad` (`entidad`, `registro_id`, `fecha_hora`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ==============================================================================
-- DATOS SEMILLA: ROLES DE LA APLICACIÓN
-- Las cuentas se aprovisionan de forma privada; no incluir credenciales ni pacientes.
-- ==============================================================================

-- 1. Roles
INSERT INTO `roles` (`id`, `slug`, `nombre`, `nivel_jerarquia`, `descripcion`) VALUES
  (1, 'superadmin', 'SuperAdmin / Creador Técnico', 1, 'Acceso exclusivo al panel de salud de BD y logs'),
  (2, 'admin', 'Dueña / Administradora', 2, 'Gestión del negocio y edición de datos secundarios'),
  (3, 'especialista', 'Podólogo / Especialista', 3, 'Registro de atenciones clínicas y pacientes'),
  (4, 'recepcionista', 'Recepcionista', 3, 'Gestión de turnos y registro de pacientes')
ON DUPLICATE KEY UPDATE `slug` = VALUES(`slug`);

-- Las cuentas demo se crean con docker/seed-demo.php y las claves del entorno.
-- El esquema distribuido no incluye usuarios, pacientes ni historiales de ejemplo.
