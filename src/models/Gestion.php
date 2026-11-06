<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/config/database.php';

class Gestion
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::pdo();
    }

    public function pacientes(): array
    {
        return $this->db->query("SELECT id, documento_identidad, nombre, apellido FROM pacientes WHERE estado = 'activo' ORDER BY apellido, nombre")->fetchAll();
    }

    public function especialistas(): array
    {
        return $this->db->query("SELECT u.id, u.nombre, u.apellido FROM usuarios u JOIN roles r ON r.id = u.rol_id WHERE u.estado = 'activo' AND r.slug IN ('especialista', 'admin') ORDER BY u.apellido")->fetchAll();
    }

    public function roles(): array
    {
        return $this->db->query('SELECT * FROM roles ORDER BY id')->fetchAll();
    }

    public function usuarios(): array
    {
        return $this->db->query('SELECT u.id, u.rol_id, u.dni, u.nombre, u.apellido, u.email, u.telefono, u.especialidad, u.numero_colegiado, u.estado, u.ultimo_acceso, r.slug, r.nombre AS rol_nombre FROM usuarios u JOIN roles r ON u.rol_id = r.id ORDER BY u.apellido')->fetchAll();
    }

    public function usuario(int $id): ?array
    {
        $s = $this->db->prepare('SELECT u.*, r.slug FROM usuarios u JOIN roles r ON r.id = u.rol_id WHERE u.id = ?');
        $s->execute([$id]);
        return $s->fetch() ?: null;
    }

    public function pacienteActivo(int $id): bool
    {
        $s = $this->db->prepare("SELECT id FROM pacientes WHERE id = ? AND estado = 'activo'");
        $s->execute([$id]);
        return (bool) $s->fetchColumn();
    }

    public function citas(string $fecha): array
    {
        $s = $this->db->prepare('SELECT c.*, CONCAT(p.apellido, ", ", p.nombre) AS paciente_nombre, p.documento_identidad, CONCAT(u.nombre, " ", u.apellido) AS especialista_nombre FROM citas c JOIN pacientes p ON p.id = c.paciente_id JOIN usuarios u ON u.id = c.especialista_id WHERE c.fecha_cita = ? ORDER BY c.hora_inicio, c.id');
        $s->execute([$fecha]);
        return $s->fetchAll();
    }

    public function cita(int $id): ?array
    {
        $s = $this->db->prepare('SELECT * FROM citas WHERE id = ?');
        $s->execute([$id]);
        return $s->fetch() ?: null;
    }

    public function guardarCita(array $d, int $autor, int $id = 0): int
    {
        $this->db->beginTransaction();
        try {
            // Un mismo profesional no puede reservar dos turnos que se solapen.
            $s = $this->db->prepare('SELECT id FROM usuarios WHERE id = ? FOR UPDATE');
            $s->execute([$d['especialista_id']]);
            $s = $this->db->prepare("SELECT id FROM citas WHERE especialista_id = ? AND fecha_cita = ? AND hora_inicio < ? AND hora_fin > ? AND estado <> 'cancelado' AND id <> ? LIMIT 1");
            $s->execute([$d['especialista_id'], $d['fecha_cita'], $d['hora_fin'], $d['hora_inicio'], $id]);
            if ($d['estado'] !== 'cancelado' && $s->fetchColumn()) {
                throw new DomainException('El especialista ya tiene una cita en ese horario. Elija otro turno.');
            }
            if ($id) {
                $anterior = $this->cita($id);
                if (!$anterior) {
                    throw new DomainException('La cita no existe.');
                }
                $this->actualizar('citas', $id, $d, $anterior, $autor, 'Actualización de agenda');
            } else {
                $s = $this->db->prepare('INSERT INTO citas (paciente_id, especialista_id, fecha_cita, hora_inicio, hora_fin, motivo, notas, estado, creado_por_usuario_id, fecha_creacion, hora_creacion) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, CURDATE(), CURTIME())');
                $s->execute([$d['paciente_id'], $d['especialista_id'], $d['fecha_cita'], $d['hora_inicio'], $d['hora_fin'], $d['motivo'], $d['notas'], $d['estado'], $autor]);
                $id = (int) $this->db->lastInsertId();
                $this->auditar('citas', $id, 'creacion', null, 'Cita registrada', $autor, 'Reserva de turno');
            }
            $this->db->commit();
            return $id;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function cambiarEstadoCita(int $id, string $estado, int $autor): void
    {
        $c = $this->cita($id);
        if (!$c) {
            throw new DomainException('La cita no existe.');
        }
        $d = array_intersect_key($c, array_flip(['paciente_id', 'especialista_id', 'fecha_cita', 'hora_inicio', 'hora_fin', 'motivo', 'notas', 'estado']));
        $d['estado'] = $estado;
        $this->guardarCita($d, $autor, $id);
    }

    public function atencion(int $id): ?array
    {
        $s = $this->db->prepare('SELECT * FROM historias_clinicas WHERE id = ?');
        $s->execute([$id]);
        return $s->fetch() ?: null;
    }

    public function guardarAtencion(array $d, int $autor, int $id = 0, string $motivo = ''): int
    {
        $this->db->beginTransaction();
        try {
            if ($id) {
                $anterior = $this->atencion($id);
                if (!$anterior || $anterior['estado'] !== 'activo') {
                    throw new DomainException('La atención no está disponible para editar.');
                }
                $this->actualizar('historias_clinicas', $id, $d, $anterior, $autor, $motivo);
            } else {
                $d['creado_por_usuario_id'] = $autor;
                $d['fecha_creacion'] = date('Y-m-d');
                $d['hora_creacion'] = date('H:i:s');
                $columnas = implode(', ', array_keys($d));
                $marcas = implode(', ', array_fill(0, count($d), '?'));
                $s = $this->db->prepare("INSERT INTO historias_clinicas ($columnas) VALUES ($marcas)");
                $s->execute(array_values($d));
                $id = (int) $this->db->lastInsertId();
                $this->auditar('historias_clinicas', $id, 'creacion', null, 'Atención registrada', $autor, 'Consulta podológica');
            }
            $this->db->commit();
            return $id;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function retirarAtencion(int $id, int $autor, string $motivo): void
    {
        $this->db->beginTransaction();
        try {
            $a = $this->atencion($id);
            if (!$a || $a['estado'] !== 'activo') {
                throw new DomainException('La atención no está activa.');
            }
            $this->actualizar('historias_clinicas', $id, ['estado' => 'inactivo', 'eliminado_en' => date('Y-m-d H:i:s'), 'eliminado_por_usuario_id' => $autor], $a, $autor, $motivo);
            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function guardarUsuario(array $d, string $password, int $autor, int $id = 0): int
    {
        $this->db->beginTransaction();
        try {
            if ($password !== '') {
                $d['password_hash'] = password_hash($password, PASSWORD_BCRYPT);
                $d['debe_cambiar_password'] = 1;
            }
            if ($id) {
                $anterior = $this->usuario($id);
                if (!$anterior) {
                    throw new DomainException('El usuario no existe.');
                }
                $this->actualizar('usuarios', $id, $d, $anterior, $autor, 'Gestión de personal');
            } else {
                $columnas = implode(', ', array_keys($d));
                $marcas = implode(', ', array_fill(0, count($d), '?'));
                $s = $this->db->prepare("INSERT INTO usuarios ($columnas) VALUES ($marcas)");
                $s->execute(array_values($d));
                $id = (int) $this->db->lastInsertId();
                $this->auditar('usuarios', $id, 'creacion', null, 'Cuenta registrada', $autor, 'Alta de personal');
            }
            $this->db->commit();
            return $id;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function auditoria(string $entidad, int $id): array
    {
        $s = $this->db->prepare('SELECT a.*, CONCAT(u.nombre, " ", u.apellido) AS autor FROM auditoria_modulos a JOIN usuarios u ON u.id = a.usuario_id WHERE a.entidad = ? AND a.registro_id = ? ORDER BY a.id DESC');
        $s->execute([$entidad, $id]);
        return $s->fetchAll();
    }

    private function actualizar(string $tabla, int $id, array $d, array $anterior, int $autor, string $motivo): void
    {
        // Los nombres de columnas provienen de listas del controlador, nunca del formulario completo.
        $asignaciones = [];
        foreach ($d as $campo => $valor) {
            $asignaciones[] = "$campo = ?";
            if ((string) ($anterior[$campo] ?? '') !== (string) ($valor ?? '')) {
                $privado = $campo === 'password_hash';
                $this->auditar($tabla, $id, $campo, $privado ? '[oculto]' : ($anterior[$campo] ?? null), $privado ? '[renovada]' : $valor, $autor, $motivo);
            }
        }
        $sql = 'UPDATE ' . $tabla . ' SET ' . implode(', ', $asignaciones) . ' WHERE id = ?';
        $s = $this->db->prepare($sql);
        $s->execute([...array_values($d), $id]);
    }

    private function auditar(string $entidad, int $id, string $campo, mixed $anterior, mixed $nuevo, int $autor, string $motivo): void
    {
        $s = $this->db->prepare('INSERT INTO auditoria_modulos (entidad, registro_id, campo, valor_anterior, valor_nuevo, usuario_id, motivo) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $s->execute([$entidad, $id, $campo, $anterior, $nuevo, $autor, $motivo]);
    }
}
