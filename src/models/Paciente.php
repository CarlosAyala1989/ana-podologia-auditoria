<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/config/database.php';

/**
 * Modelo: Paciente
 * Gestión integral de fichas clínicas, antecedentes patológicos y auditoría inmutable
 * Sistema: Podología Ana Ramírez
 */
class Paciente
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::pdo();
    }

    /**
     * Obtiene el listado de pacientes activos con soporte de paginación y búsqueda
     *
     * @param string|null $busqueda Término de búsqueda (Cédula/DNI, nombres, apellidos o teléfono)
     * @param bool $incluirInactivos Si es false, filtra solo estado = 'activo'
     * @param int $limite Límite de registros a recuperar
     * @param int $offset Desplazamiento para paginación
     * @return array<int, array<string, mixed>>
     */
    public function obtenerTodos(?string $busqueda = null, bool $incluirInactivos = false, int $limite = 50, int $offset = 0): array
    {
        $sql = "SELECT p.id, p.documento_identidad, p.tipo_documento, p.nombre, p.apellido, 
                       p.fecha_nacimiento, p.genero, p.telefono, p.email,
                       p.es_diabetico, p.usa_anticoagulantes, p.problemas_circulatorios,
                       p.tipo_calzado_habitual, p.estado, p.fecha_creacion, p.hora_creacion,
                       CONCAT(u.nombre, ' ', u.apellido) AS creado_por_nombre,
                       r.nombre AS creado_por_rol
                FROM pacientes p
                LEFT JOIN usuarios u ON p.creado_por_usuario_id = u.id
                LEFT JOIN roles r ON u.rol_id = r.id";

        $condiciones = [];
        $params = [];

        if (!$incluirInactivos) {
            $condiciones[] = "p.estado = 'activo'";
        }

        if (!empty($busqueda)) {
            $condiciones[] = "(p.documento_identidad LIKE :b1 
                             OR p.nombre LIKE :b2 
                             OR p.apellido LIKE :b3 
                             OR p.telefono LIKE :b4)";
            $termino = '%' . trim($busqueda) . '%';
            $params[':b1'] = $termino;
            $params[':b2'] = $termino;
            $params[':b3'] = $termino;
            $params[':b4'] = $termino;
        }

        if (!empty($condiciones)) {
            $sql .= " WHERE " . implode(" AND ", $condiciones);
        }

        $sql .= " ORDER BY p.apellido ASC, p.nombre ASC LIMIT :limite OFFSET :offset";

        $stmt = $this->db->prepare($sql);

        foreach ($params as $clave => $valor) {
            $stmt->bindValue($clave, $valor, PDO::PARAM_STR);
        }
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Obtiene la ficha clínica completa de un paciente con los datos de quién lo creó
     *
     * @param int $id ID único del paciente
     * @return array<string, mixed>|null
     */
    public function obtenerPorId(int $id): ?array
    {
        $sql = "SELECT p.*,
                       CONCAT(uc.nombre, ' ', uc.apellido) AS creado_por_nombre,
                       rc.nombre AS creado_por_rol,
                       CONCAT(ue.nombre, ' ', ue.apellido) AS eliminado_por_nombre
                FROM pacientes p
                LEFT JOIN usuarios uc ON p.creado_por_usuario_id = uc.id
                LEFT JOIN roles rc ON uc.rol_id = rc.id
                LEFT JOIN usuarios ue ON p.eliminado_por_usuario_id = ue.id
                WHERE p.id = :id LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $paciente = $stmt->fetch();
        return $paciente ?: null;
    }

    /**
     * Registra un nuevo paciente guardando obligatoriamente el usuario creador, fecha y hora
     *
     * @param array<string, mixed> $datos Información filiatoria y antecedentes
     * @param int $usuarioCreadorId ID del usuario de la sesión actual
     * @return int|false ID del paciente registrado o false en caso de fallo
     */
    public function crear(array $datos, int $usuarioCreadorId): int|false
    {
        $sql = "INSERT INTO pacientes (
            documento_identidad, tipo_documento, nombre, apellido, fecha_nacimiento, genero,
            telefono, telefono_emergencia, contacto_emergencia, email, direccion, ocupacion,
            es_diabetico, tipo_diabetes, tiene_hipertension, problemas_circulatorios,
            usa_anticoagulantes, tiene_marcapasos, enfermedades_renales, otras_patologias,
            alergias_medicamentos, alergias_contacto,
            tipo_calzado_habitual, talla_calzado, usa_plantillas, actividad_fisica,
            creado_por_usuario_id, fecha_creacion, hora_creacion, estado
        ) VALUES (
            :documento_identidad, :tipo_documento, :nombre, :apellido, :fecha_nacimiento, :genero,
            :telefono, :telefono_emergencia, :contacto_emergencia, :email, :direccion, :ocupacion,
            :es_diabetico, :tipo_diabetes, :tiene_hipertension, :problemas_circulatorios,
            :usa_anticoagulantes, :tiene_marcapasos, :enfermedades_renales, :otras_patologias,
            :alergias_medicamentos, :alergias_contacto,
            :tipo_calzado_habitual, :talla_calzado, :usa_plantillas, :actividad_fisica,
            :creado_por_usuario_id, :fecha_creacion, :hora_creacion, :estado
        )";

        $stmt = $this->db->prepare($sql);

        $params = [
            ':documento_identidad'     => trim($datos['documento_identidad'] ?? ''),
            ':tipo_documento'          => $datos['tipo_documento'] ?? 'Cedula',
            ':nombre'                  => trim($datos['nombre'] ?? ''),
            ':apellido'                => trim($datos['apellido'] ?? ''),
            ':fecha_nacimiento'        => $datos['fecha_nacimiento'] ?? '1990-01-01',
            ':genero'                  => $datos['genero'] ?? 'Otro',
            ':telefono'                => trim($datos['telefono'] ?? ''),
            ':telefono_emergencia'     => !empty($datos['telefono_emergencia']) ? trim($datos['telefono_emergencia']) : null,
            ':contacto_emergencia'     => !empty($datos['contacto_emergencia']) ? trim($datos['contacto_emergencia']) : null,
            ':email'                   => !empty($datos['email']) ? trim($datos['email']) : null,
            ':direccion'               => !empty($datos['direccion']) ? trim($datos['direccion']) : null,
            ':ocupacion'               => !empty($datos['ocupacion']) ? trim($datos['ocupacion']) : null,

            ':es_diabetico'            => !empty($datos['es_diabetico']) ? 1 : 0,
            ':tipo_diabetes'           => !empty($datos['tipo_diabetes']) ? trim($datos['tipo_diabetes']) : null,
            ':tiene_hipertension'      => !empty($datos['tiene_hipertension']) ? 1 : 0,
            ':problemas_circulatorios' => !empty($datos['problemas_circulatorios']) ? 1 : 0,
            ':usa_anticoagulantes'     => !empty($datos['usa_anticoagulantes']) ? 1 : 0,
            ':tiene_marcapasos'        => !empty($datos['tiene_marcapasos']) ? 1 : 0,
            ':enfermedades_renales'    => !empty($datos['enfermedades_renales']) ? 1 : 0,
            ':otras_patologias'        => !empty($datos['otras_patologias']) ? trim($datos['otras_patologias']) : null,

            ':alergias_medicamentos'   => !empty($datos['alergias_medicamentos']) ? trim($datos['alergias_medicamentos']) : null,
            ':alergias_contacto'       => !empty($datos['alergias_contacto']) ? trim($datos['alergias_contacto']) : null,

            ':tipo_calzado_habitual'   => !empty($datos['tipo_calzado_habitual']) ? trim($datos['tipo_calzado_habitual']) : null,
            ':talla_calzado'           => !empty($datos['talla_calzado']) ? trim($datos['talla_calzado']) : null,
            ':usa_plantillas'          => !empty($datos['usa_plantillas']) ? 1 : 0,
            ':actividad_fisica'        => !empty($datos['actividad_fisica']) ? trim($datos['actividad_fisica']) : null,

            // Trazabilidad de creación
            ':creado_por_usuario_id'   => $usuarioCreadorId,
            ':fecha_creacion'          => date('Y-m-d'),
            ':hora_creacion'           => date('H:i:s'),
            ':estado'                  => 'activo'
        ];

        try {
            $stmt->execute($params);
            return (int) $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("[Paciente::crear Error] " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza datos secundarios del paciente y REGISTRA EL HISTORIAL INMUTABLE DE CAMBIOS.
     * RESTRICCIÓN DE INTEGRIDAD: No permite modificar documento_identidad, nombre ni apellido.
     *
     * @param int $id ID del paciente
     * @param array<string, mixed> $nuevosDatos Nuevos valores
     * @param int $usuarioModificadorId ID del Admin que ejecuta la edición
     * @param string|null $motivo Motivo justificado del cambio
     * @return bool True si se actualizó y auditó correctamente
     */
    public function actualizarSeguroConAuditoria(int $id, array $nuevosDatos, int $usuarioModificadorId, ?string $motivo = null): bool
    {
        $actual = $this->obtenerPorId($id);
        if (!$actual) {
            return false;
        }

        // Lista de campos secundarios editables por Admin
        $camposAuditables = [
            'telefono', 'telefono_emergencia', 'contacto_emergencia', 'email', 'direccion', 'ocupacion',
            'es_diabetico', 'tipo_diabetes', 'tiene_hipertension', 'problemas_circulatorios',
            'usa_anticoagulantes', 'tiene_marcapasos', 'enfermedades_renales', 'otras_patologias',
            'alergias_medicamentos', 'alergias_contacto',
            'tipo_calzado_habitual', 'talla_calzado', 'usa_plantillas', 'actividad_fisica'
        ];

        $cambiosDetectados = [];

        foreach ($camposAuditables as $campo) {
            $valViejo = (string) ($actual[$campo] ?? '');
            
            // Tratamiento de valores booleanos para comparación uniforme
            if (in_array($campo, ['es_diabetico', 'tiene_hipertension', 'problemas_circulatorios', 'usa_anticoagulantes', 'tiene_marcapasos', 'enfermedades_renales', 'usa_plantillas'], true)) {
                $valNuevo = !empty($nuevosDatos[$campo]) ? '1' : '0';
            } else {
                $valNuevo = isset($nuevosDatos[$campo]) ? trim((string)$nuevosDatos[$campo]) : '';
            }

            if ($valViejo !== $valNuevo) {
                $cambiosDetectados[$campo] = [
                    'anterior' => $valViejo,
                    'nuevo'    => $valNuevo
                ];
            }
        }

        // Si no hay diferencias, no es necesario actualizar ni auditar
        if (empty($cambiosDetectados)) {
            return true;
        }

        try {
            $this->db->beginTransaction();

            // 1. Registrar cada campo modificado en la tabla historial_cambios_pacientes
            $sqlAudit = "INSERT INTO historial_cambios_pacientes (
                paciente_id, campo_modificado, valor_anterior, valor_nuevo, modificado_por_usuario_id, motivo_cambio
            ) VALUES (
                :paciente_id, :campo, :anterior, :nuevo, :usuario_id, :motivo
            )";
            $stmtAudit = $this->db->prepare($sqlAudit);

            foreach ($cambiosDetectados as $campo => $detalle) {
                $stmtAudit->execute([
                    ':paciente_id' => $id,
                    ':campo'       => $campo,
                    ':anterior'    => $detalle['anterior'],
                    ':nuevo'       => $detalle['nuevo'],
                    ':usuario_id'  => $usuarioModificadorId,
                    ':motivo'      => $motivo ?: 'Actualización autorizada por Administración'
                ]);
            }

            // 2. Actualizar el registro en la tabla pacientes (Protegiendo DNI, Nombre y Apellido)
            $sqlUp = "UPDATE pacientes SET
                telefono = :telefono,
                telefono_emergencia = :telefono_emergencia,
                contacto_emergencia = :contacto_emergencia,
                email = :email,
                direccion = :direccion,
                ocupacion = :ocupacion,
                es_diabetico = :es_diabetico,
                tipo_diabetes = :tipo_diabetes,
                tiene_hipertension = :tiene_hipertension,
                problemas_circulatorios = :problemas_circulatorios,
                usa_anticoagulantes = :usa_anticoagulantes,
                tiene_marcapasos = :tiene_marcapasos,
                enfermedades_renales = :enfermedades_renales,
                otras_patologias = :otras_patologias,
                alergias_medicamentos = :alergias_medicamentos,
                alergias_contacto = :alergias_contacto,
                tipo_calzado_habitual = :tipo_calzado_habitual,
                talla_calzado = :talla_calzado,
                usa_plantillas = :usa_plantillas,
                actividad_fisica = :actividad_fisica
            WHERE id = :id";

            $stmtUp = $this->db->prepare($sqlUp);
            $stmtUp->execute([
                ':id'                      => $id,
                ':telefono'                => trim($nuevosDatos['telefono'] ?? $actual['telefono']),
                ':telefono_emergencia'     => !empty($nuevosDatos['telefono_emergencia']) ? trim($nuevosDatos['telefono_emergencia']) : null,
                ':contacto_emergencia'     => !empty($nuevosDatos['contacto_emergencia']) ? trim($nuevosDatos['contacto_emergencia']) : null,
                ':email'                   => !empty($nuevosDatos['email']) ? trim($nuevosDatos['email']) : null,
                ':direccion'               => !empty($nuevosDatos['direccion']) ? trim($nuevosDatos['direccion']) : null,
                ':ocupacion'               => !empty($nuevosDatos['ocupacion']) ? trim($nuevosDatos['ocupacion']) : null,
                ':es_diabetico'            => !empty($nuevosDatos['es_diabetico']) ? 1 : 0,
                ':tipo_diabetes'           => !empty($nuevosDatos['tipo_diabetes']) ? trim($nuevosDatos['tipo_diabetes']) : null,
                ':tiene_hipertension'      => !empty($nuevosDatos['tiene_hipertension']) ? 1 : 0,
                ':problemas_circulatorios' => !empty($nuevosDatos['problemas_circulatorios']) ? 1 : 0,
                ':usa_anticoagulantes'     => !empty($nuevosDatos['usa_anticoagulantes']) ? 1 : 0,
                ':tiene_marcapasos'        => !empty($nuevosDatos['tiene_marcapasos']) ? 1 : 0,
                ':enfermedades_renales'    => !empty($nuevosDatos['enfermedades_renales']) ? 1 : 0,
                ':otras_patologias'        => !empty($nuevosDatos['otras_patologias']) ? trim($nuevosDatos['otras_patologias']) : null,
                ':alergias_medicamentos'   => !empty($nuevosDatos['alergias_medicamentos']) ? trim($nuevosDatos['alergias_medicamentos']) : null,
                ':alergias_contacto'       => !empty($nuevosDatos['alergias_contacto']) ? trim($nuevosDatos['alergias_contacto']) : null,
                ':tipo_calzado_habitual'   => !empty($nuevosDatos['tipo_calzado_habitual']) ? trim($nuevosDatos['tipo_calzado_habitual']) : null,
                ':talla_calzado'           => !empty($nuevosDatos['talla_calzado']) ? trim($nuevosDatos['talla_calzado']) : null,
                ':usa_plantillas'          => !empty($nuevosDatos['usa_plantillas']) ? 1 : 0,
                ':actividad_fisica'        => !empty($nuevosDatos['actividad_fisica']) ? trim($nuevosDatos['actividad_fisica']) : null,
            ]);

            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            $this->db->rollBack();
            error_log("[Paciente::actualizarSeguro Error] " . $e->getMessage());
            return false;
        }
    }

    /**
     * Borrado lógico (Soft Delete). No destruye datos físicos de la base de datos.
     *
     * @param int $id ID del paciente
     * @param int $usuarioId ID del Administrador que ejecuta la acción
     * @return bool
     */
    public function eliminarLogico(int $id, int $usuarioId): bool
    {
        $sql = "UPDATE pacientes 
                SET estado = 'inactivo', 
                    eliminado_en = NOW(), 
                    eliminado_por_usuario_id = :uid 
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id'  => $id,
            ':uid' => $usuarioId
        ]);
    }

    /**
     * Obtiene el historial inmutable de auditoría para un paciente específico
     *
     * @param int $pacienteId
     * @return array<int, array<string, mixed>>
     */
    public function obtenerHistorialCambios(int $pacienteId): array
    {
        $sql = "SELECT h.*, 
                       CONCAT(u.nombre, ' ', u.apellido) AS modificado_por_nombre,
                       r.nombre AS modificado_por_rol
                FROM historial_cambios_pacientes h
                INNER JOIN usuarios u ON h.modificado_por_usuario_id = u.id
                INNER JOIN roles r ON u.rol_id = r.id
                WHERE h.paciente_id = :pacienteId
                ORDER BY h.fecha_hora DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':pacienteId', $pacienteId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Comprueba si un documento de identidad ya existe
     */
    public function existeDocumento(string $documento, ?int $excluirId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM pacientes WHERE documento_identidad = :doc";
        $params = [':doc' => trim($documento)];

        if ($excluirId !== null) {
            $sql .= " AND id != :excluirId";
            $params[':excluirId'] = $excluirId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return ((int) $stmt->fetchColumn()) > 0;
    }

    /**
     * Obtiene el historial de atenciones clínicas asociadas a un paciente
     */
    public function obtenerHistoriasClinicas(int $pacienteId): array
    {
        $sql = "SELECT hc.*, 
                       CONCAT(u.nombre, ' ', u.apellido) AS especialista_nombre,
                       u.especialidad AS especialista_cargo
                FROM historias_clinicas hc
                INNER JOIN usuarios u ON hc.especialista_id = u.id
                WHERE hc.paciente_id = :pacienteId AND hc.estado = 'activo'
                ORDER BY hc.fecha_atencion DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':pacienteId', $pacienteId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
