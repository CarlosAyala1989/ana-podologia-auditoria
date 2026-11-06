<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/config/auth.php';
require_once dirname(__DIR__) . '/models/Gestion.php';

class GestionController
{
    private Gestion $modelo;

    public function __construct()
    {
        $this->modelo = new Gestion();
    }

    public function agenda(): void
    {
        requerirAutenticacion();
        $fecha = $_GET['fecha'] ?? date('Y-m-d');
        if (!$this->fecha($fecha)) {
            $fecha = date('Y-m-d');
        }
        $cita = isset($_GET['editar']) ? $this->modelo->cita((int) $_GET['editar']) : null;
        if ($cita) {
            requerirRol(['superadmin', 'admin', 'recepcionista']);
        }
        $errores = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            validarTokenFormulario();
            $id = (int) ($_POST['id'] ?? 0);
            try {
                if (isset($_POST['cambiar_estado'])) {
                    $actual = $this->modelo->cita($id);
                    if (!$actual || (tieneRol('especialista') && (int) $actual['especialista_id'] !== (int) usuarioActual()['id'])) {
                        requerirRol(['admin', 'superadmin', 'recepcionista']);
                        throw new DomainException('No se encontró la cita.');
                    }
                    $estado = $_POST['estado'] ?? '';
                    if (!in_array($estado, ['pendiente', 'en_atencion', 'finalizado', 'cancelado'], true)) {
                        throw new DomainException('Seleccione un estado válido.');
                    }
                    $this->modelo->cambiarEstadoCita($id, $estado, (int) usuarioActual()['id']);
                    $fecha = $actual['fecha_cita'];
                } else {
                    requerirRol(['superadmin', 'admin', 'recepcionista']);
                    $cita = [
                        'paciente_id' => (int) ($_POST['paciente_id'] ?? 0),
                        'especialista_id' => (int) ($_POST['especialista_id'] ?? 0),
                        'fecha_cita' => trim($_POST['fecha_cita'] ?? ''),
                        'hora_inicio' => trim($_POST['hora_inicio'] ?? ''),
                        'hora_fin' => trim($_POST['hora_fin'] ?? ''),
                        'motivo' => trim($_POST['motivo'] ?? ''),
                        'notas' => trim($_POST['notas'] ?? ''),
                        'estado' => $_POST['estado'] ?? 'pendiente',
                    ];
                    if (!$this->modelo->pacienteActivo($cita['paciente_id']) || !$this->especialista($cita['especialista_id'])) {
                        throw new DomainException('Seleccione un paciente y un especialista activos.');
                    }
                    if (!$this->fecha($cita['fecha_cita']) || !preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9]$/', $cita['hora_inicio']) || !preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9]$/', $cita['hora_fin']) || $cita['hora_fin'] <= $cita['hora_inicio']) {
                        throw new DomainException('Indique una fecha y un intervalo de horas válidos.');
                    }
                    if ($cita['motivo'] === '' || strlen($cita['motivo']) > 255 || strlen($cita['notas']) > 255 || !in_array($cita['estado'], ['pendiente', 'en_atencion', 'finalizado', 'cancelado'], true)) {
                        throw new DomainException('Revise el motivo, las notas (máximo 255 caracteres) y el estado.');
                    }
                    $this->modelo->guardarCita($cita, (int) usuarioActual()['id'], $id);
                    $fecha = $cita['fecha_cita'];
                }
                registrarLog('INFO', 'CITAS', 'GUARDAR_CITA', 'Agenda actualizada por ' . usuarioActual()['nombre_completo']);
                flash('exito', 'Cita guardada correctamente.');
                header('Location: index.php?accion=agenda&fecha=' . urlencode($fecha));
                exit;
            } catch (Throwable $e) {
                $errores[] = $this->mensajeError($e);
                if ($cita) {
                    $cita['id'] = $id;
                }
            }
        }
        $pacientes = $this->modelo->pacientes();
        $especialistas = $this->modelo->especialistas();
        $citas = $this->modelo->citas($fecha);
        $auditoria = !empty($cita['id']) ? $this->modelo->auditoria('citas', (int) $cita['id']) : [];
        $mensajeExito = flash('exito');
        require dirname(__DIR__) . '/views/gestion/agenda.php';
    }

    public function atencion(): void
    {
        requerirRol(['superadmin', 'admin', 'especialista']);
        $id = (int) ($_POST['id'] ?? $_GET['id'] ?? 0);
        $atencion = $id ? $this->modelo->atencion($id) : null;
        if ($id && (!$atencion || $atencion['estado'] !== 'activo')) {
            flash('error', 'La atención no existe o ha sido retirada.');
            header('Location: index.php?accion=listado');
            exit;
        }
        if ($id) {
            requerirRol(['superadmin', 'admin']);
        }
        $pacienteId = (int) ($atencion['paciente_id'] ?? $_POST['paciente_id'] ?? $_GET['paciente_id'] ?? 0);
        $errores = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            validarTokenFormulario();
            try {
                $motivo = trim($_POST['motivo_cambio'] ?? '');
                if ($id && ($motivo === '' || strlen($motivo) > 255)) {
                    throw new DomainException('Explique el motivo del cambio (máximo 255 caracteres).');
                }
                if (isset($_POST['retirar'])) {
                    $this->modelo->retirarAtencion($id, (int) usuarioActual()['id'], $motivo);
                } else {
                    $d = [];
                    foreach (['motivo_consulta', 'diagnostico', 'procedimiento_realizado', 'evolucion_lesion', 'prescripcion_recomendaciones', 'observaciones_privadas', 'tipo_pisada', 'evaluacion_marcha', 'exploracion_vascular', 'exploracion_neurologica', 'piel_unas'] as $campo) {
                        $d[$campo] = trim($_POST[$campo] ?? '');
                        if (strlen($d[$campo]) > 60000) {
                            throw new DomainException('El texto de la atención es demasiado largo.');
                        }
                    }
                    $d['paciente_id'] = $pacienteId;
                    $d['especialista_id'] = tieneRol('especialista') ? (int) usuarioActual()['id'] : (int) ($_POST['especialista_id'] ?? 0);
                    $d['fecha_atencion'] = str_replace('T', ' ', $_POST['fecha_atencion'] ?? '');
                    $d['proxima_visita_sugerida'] = ($_POST['proxima_visita_sugerida'] ?? '') ?: null;
                    $dolor = filter_var($_POST['dolor_escala'] ?? null, FILTER_VALIDATE_INT);
                    $d['dolor_escala'] = $dolor;
                    if (!$this->modelo->pacienteActivo($pacienteId) || !$this->especialista($d['especialista_id'])) {
                        throw new DomainException('Seleccione un paciente y un profesional activos.');
                    }
                    if (!$this->fecha($d['fecha_atencion'], 'Y-m-d H:i') || ($d['proxima_visita_sugerida'] && !$this->fecha($d['proxima_visita_sugerida'])) || $dolor === false || $dolor < 0 || $dolor > 10) {
                        throw new DomainException('Revise las fechas y la escala de dolor (0 a 10).');
                    }
                    if ($d['motivo_consulta'] === '' || strlen($d['motivo_consulta']) > 255 || $d['diagnostico'] === '' || $d['procedimiento_realizado'] === '' || strlen($d['tipo_pisada']) > 50) {
                        throw new DomainException('Complete motivo (máximo 255 caracteres), diagnóstico y procedimiento; revise el tipo de pisada.');
                    }
                    $this->modelo->guardarAtencion($d, (int) usuarioActual()['id'], $id, $motivo);
                }
                registrarLog('INFO', 'ATENCIONES', 'GUARDAR_ATENCION', 'Atención del paciente #' . $pacienteId . ' guardada');
                flash('exito', 'Atención guardada y registrada en el expediente.');
                header('Location: index.php?accion=detalle&id=' . $pacienteId . '#atenciones');
                exit;
            } catch (Throwable $e) {
                $errores[] = $this->mensajeError($e);
                $atencion = array_merge($atencion ?? [], $d ?? []);
            }
        }
        $pacientes = $this->modelo->pacientes();
        $especialistas = $this->modelo->especialistas();
        $auditoria = $id ? $this->modelo->auditoria('historias_clinicas', $id) : [];
        require dirname(__DIR__) . '/views/gestion/atencion.php';
    }

    public function usuarios(): void
    {
        requerirRol(['superadmin', 'admin']);
        $id = (int) ($_POST['id'] ?? $_GET['editar'] ?? 0);
        $cuenta = $id ? $this->modelo->usuario($id) : null;
        if ($id && (!$cuenta || (!esSuperAdmin() && !in_array($cuenta['slug'], ['especialista', 'recepcionista'], true)))) {
            flash('error', 'No tiene permiso para administrar esa cuenta.');
            header('Location: index.php?accion=usuarios');
            exit;
        }
        $errores = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            validarTokenFormulario();
            try {
                $d = [];
                foreach (['dni', 'nombre', 'apellido', 'email', 'telefono', 'especialidad', 'numero_colegiado'] as $campo) {
                    $d[$campo] = trim($_POST[$campo] ?? '');
                }
                $d['email'] = $d['email'] ?: null;
                $d['rol_id'] = (int) ($_POST['rol_id'] ?? 0);
                $d['estado'] = $_POST['estado'] ?? 'activo';
                $rol = null;
                foreach ($this->modelo->roles() as $r) {
                    if ((int) $r['id'] === $d['rol_id']) {
                        $rol = $r;
                    }
                }
                if (!$rol || (!esSuperAdmin() && !in_array($rol['slug'], ['especialista', 'recepcionista'], true))) {
                    throw new DomainException('No puede asignar ese perfil.');
                }
                if ($id === (int) usuarioActual()['id'] && ($d['rol_id'] !== (int) $cuenta['rol_id'] || $d['estado'] !== 'activo')) {
                    throw new DomainException('No puede desactivar su propia cuenta ni cambiar su propio perfil.');
                }
                if (!preg_match('/^[0-9]{8,12}$/', $d['dni']) || $d['nombre'] === '' || $d['apellido'] === '' || strlen($d['nombre']) > 100 || strlen($d['apellido']) > 100 || strlen($d['telefono']) > 30 || strlen($d['especialidad']) > 100 || strlen($d['numero_colegiado']) > 50 || !in_array($d['estado'], ['activo', 'inactivo'], true) || ($d['email'] && (!filter_var($d['email'], FILTER_VALIDATE_EMAIL) || strlen($d['email']) > 150))) {
                    throw new DomainException('Revise el DNI (8 a 12 dígitos), nombre, apellido, datos de contacto y estado.');
                }
                $password = $_POST['password'] ?? '';
                if ((!$id || $password !== '') && (strlen($password) < 6 || strlen($password) > 72 || $password === $d['dni'])) {
                    throw new DomainException('Use una contraseña de 6 a 72 caracteres, diferente al DNI.');
                }
                $this->modelo->guardarUsuario($d, $password, (int) usuarioActual()['id'], $id);
                registrarLog('INFO', 'USUARIOS', 'GUARDAR_USUARIO', 'Cuenta del personal guardada: ' . $d['dni']);
                flash('exito', 'Usuario guardado. Las contraseñas nuevas deben cambiarse al iniciar sesión.');
                header('Location: index.php?accion=usuarios');
                exit;
            } catch (Throwable $e) {
                $errores[] = $this->mensajeError($e);
                $cuenta = array_merge($cuenta ?? [], $d ?? []);
            }
        }
        $usuarios = $this->modelo->usuarios();
        $roles = $this->modelo->roles();
        $auditoria = $id ? $this->modelo->auditoria('usuarios', $id) : [];
        $mensajeExito = flash('exito');
        $mensajeError = flash('error');
        require dirname(__DIR__) . '/views/gestion/usuarios.php';
    }

    private function fecha(string $valor, string $formato = 'Y-m-d'): bool
    {
        $fecha = DateTimeImmutable::createFromFormat('!' . $formato, $valor);
        return $fecha && $fecha->format($formato) === $valor;
    }

    private function especialista(int $id): bool
    {
        $u = $this->modelo->usuario($id);
        return $u && $u['estado'] === 'activo' && in_array($u['slug'], ['especialista', 'admin'], true);
    }

    private function mensajeError(Throwable $e): string
    {
        error_log('[Gestion] ' . $e->getMessage());
        if ($e instanceof DomainException) {
            return $e->getMessage();
        }
        if ($e instanceof PDOException && (string) $e->getCode() === '23000') {
            return 'El DNI o correo ya está registrado, o alguno de los registros relacionados no está disponible.';
        }
        return 'No se pudo guardar. Revise los datos y vuelva a intentarlo.';
    }
}
