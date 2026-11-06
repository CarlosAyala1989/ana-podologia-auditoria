<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/config/app.php';
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/config/auth.php';
require_once dirname(__DIR__) . '/models/Paciente.php';

/**
 * Controlador de Pacientes y Fichas Clínicas con Controles RBAC y Auditoría
 * Podología Ana Ramírez
 */
class PacienteController
{
    private ?Paciente $pacienteModel = null;
    private bool $dbDisponible = false;

    public function __construct()
    {
        try {
            $this->pacienteModel = new Paciente();
            $this->dbDisponible = true;
        } catch (Throwable $e) {
            $this->dbDisponible = false;
        }
    }

    /**
     * Muestra el formulario táctil de registro de paciente
     */
    public function mostrarFormularioRegistro(): void
    {
        requerirAutenticacion();

        $dbDisponible = $this->dbDisponible;
        $mensajeExito = flash('exito');
        $mensajeError = flash('error');
        $datosPrevios = $_SESSION['form_data'] ?? [];
        unset($_SESSION['form_data']);

        require_once dirname(__DIR__) . '/views/pacientes/registro.php';
    }

    /**
     * Procesa la creación de un nuevo paciente registrando autoría forense
     */
    public function guardar(): void
    {
        requerirAutenticacion();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?accion=registro');
            exit;
        }

        if (!$this->dbDisponible || $this->pacienteModel === null) {
            flash('error', 'Base de datos no disponible.');
            header('Location: index.php?accion=registro');
            exit;
        }

        $usuario = usuarioActual();

        $datos = [
            'documento_identidad'     => trim($_POST['documento_identidad'] ?? ''),
            'tipo_documento'          => $_POST['tipo_documento'] ?? 'Cedula',
            'nombre'                  => trim($_POST['nombre'] ?? ''),
            'apellido'                => trim($_POST['apellido'] ?? ''),
            'fecha_nacimiento'        => $_POST['fecha_nacimiento'] ?? '',
            'genero'                  => $_POST['genero'] ?? 'Femenino',
            'telefono'                => trim($_POST['telefono'] ?? ''),
            'telefono_emergencia'     => trim($_POST['telefono_emergencia'] ?? ''),
            'contacto_emergencia'     => trim($_POST['contacto_emergencia'] ?? ''),
            'email'                   => trim($_POST['email'] ?? ''),
            'direccion'               => trim($_POST['direccion'] ?? ''),
            'ocupacion'               => trim($_POST['ocupacion'] ?? ''),
            'es_diabetico'            => isset($_POST['es_diabetico']) ? 1 : 0,
            'tipo_diabetes'           => trim($_POST['tipo_diabetes'] ?? ''),
            'tiene_hipertension'      => isset($_POST['tiene_hipertension']) ? 1 : 0,
            'problemas_circulatorios' => isset($_POST['problemas_circulatorios']) ? 1 : 0,
            'usa_anticoagulantes'     => isset($_POST['usa_anticoagulantes']) ? 1 : 0,
            'tiene_marcapasos'        => isset($_POST['tiene_marcapasos']) ? 1 : 0,
            'enfermedades_renales'    => isset($_POST['enfermedades_renales']) ? 1 : 0,
            'otras_patologias'        => trim($_POST['otras_patologias'] ?? ''),
            'alergias_medicamentos'   => trim($_POST['alergias_medicamentos'] ?? ''),
            'alergias_contacto'       => trim($_POST['alergias_contacto'] ?? ''),
            'tipo_calzado_habitual'   => trim($_POST['tipo_calzado_habitual'] ?? ''),
            'talla_calzado'           => trim($_POST['talla_calzado'] ?? ''),
            'usa_plantillas'          => isset($_POST['usa_plantillas']) ? 1 : 0,
            'actividad_fisica'        => trim($_POST['actividad_fisica'] ?? ''),
        ];

        // Validaciones
        $errores = [];
        if (empty($datos['documento_identidad'])) $errores[] = 'El documento de identidad es obligatorio.';
        if (empty($datos['nombre'])) $errores[] = 'El nombre es obligatorio.';
        if (empty($datos['apellido'])) $errores[] = 'El apellido es obligatorio.';
        if (empty($datos['fecha_nacimiento'])) $errores[] = 'La fecha de nacimiento es obligatoria.';
        if (empty($datos['telefono'])) $errores[] = 'El teléfono es obligatorio.';

        if (empty($errores) && $this->pacienteModel->existeDocumento($datos['documento_identidad'])) {
            $errores[] = "Ya existe un paciente registrado con el documento: {$datos['documento_identidad']}.";
        }

        if (!empty($errores)) {
            flash('error', implode('<br>', $errores));
            $_SESSION['form_data'] = $_POST;
            header('Location: index.php?accion=registro');
            exit;
        }

        $nuevoId = $this->pacienteModel->crear($datos, (int)$usuario['id']);

        if ($nuevoId) {
            registrarLog('INFO', 'PACIENTES', 'CREAR_PACIENTE', "Paciente #{$nuevoId} ({$datos['nombre']} {$datos['apellido']}) registrado por {$usuario['email']}");
            flash('exito', "¡Ficha clínica del paciente <strong>{$datos['nombre']} {$datos['apellido']}</strong> registrada con éxito (Expediente #{$nuevoId})!");
            header("Location: index.php?accion=detalle&id={$nuevoId}");
            exit;
        } else {
            flash('error', 'Error al guardar la ficha en la base de datos.');
            $_SESSION['form_data'] = $_POST;
            header('Location: index.php?accion=registro');
            exit;
        }
    }

    /**
     * Muestra la ficha integral del paciente con indicadores de autoría e historial de cambios
     */
    public function detalle(): void
    {
        requerirAutenticacion();

        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        if ($id <= 0) {
            flash('error', 'Identificador de paciente no válido.');
            header('Location: index.php?accion=listado');
            exit;
        }

        $paciente = $this->pacienteModel->obtenerPorId($id);
        if (!$paciente) {
            flash('error', 'El expediente solicitado no existe o ha sido retirado.');
            header('Location: index.php?accion=listado');
            exit;
        }

        $historialCambios = $this->pacienteModel->obtenerHistorialCambios($id);
        $mensajeExito = flash('exito');
        $mensajeError = flash('error');

        require_once dirname(__DIR__) . '/views/pacientes/detalle.php';
    }

    /**
     * Muestra la vista formal de impresión y guardado en PDF de la ficha clínica
     * Accesible para Dueños / Administradores y SuperAdmin
     */
    public function imprimirFicha(): void
    {
        requerirAutenticacion();

        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $paciente = $this->pacienteModel->obtenerPorId($id);

        if (!$paciente) {
            flash('error', 'Paciente no encontrado para impresión.');
            header('Location: index.php?accion=listado');
            exit;
        }

        $historiasClinicas = $this->pacienteModel->obtenerHistoriasClinicas($id);

        require_once dirname(__DIR__) . '/views/pacientes/imprimir_ficha.php';
    }

    /**
     * Muestra el formulario de edición de datos secundarios.
     * BLOQUEO RBAC: Si un trabajador intenta acceder, se le deniega el acceso.
     */
    public function editar(): void
    {
        requerirAutenticacion();

        // 1. RESTRICCIÓN DE SEGURIDAD CRÍTICA
        if (!puedeEditarPaciente()) {
            $u = usuarioActual();
            registrarLog('SECURITY', 'PACIENTES', 'INTENTO_EDICION_BLOQUEADO', 
                "El trabajador {$u['email']} intentó acceder al formulario de edición del paciente ID " . ($_GET['id'] ?? 'desconocido'));
            
            flash('error', 'Seguridad: Los trabajadores no tienen permiso para editar fichas clínicas creadas. Debe solicitar la corrección al Administrador.');
            header('Location: index.php?accion=detalle&id=' . ($_GET['id'] ?? 1));
            exit;
        }

        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $paciente = $this->pacienteModel->obtenerPorId($id);

        if (!$paciente) {
            flash('error', 'Paciente no encontrado.');
            header('Location: index.php?accion=listado');
            exit;
        }

        $mensajeError = flash('error');
        require_once dirname(__DIR__) . '/views/pacientes/editar.php';
    }

    /**
     * Procesa la actualización segura con auditoría inmutable
     * BLOQUEO RBAC: Exclusivo para Administrador / SuperAdmin
     */
    public function actualizar(): void
    {
        requerirAutenticacion();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?accion=listado');
            exit;
        }

        // 1. RESTRICCIÓN DE SEGURIDAD CRÍTICA
        if (!puedeEditarPaciente()) {
            $u = usuarioActual();
            registrarLog('SECURITY', 'PACIENTES', 'POST_EDICION_BLOQUEADO', 
                "El trabajador {$u['email']} intentó ejecutar un POST de actualización en paciente ID " . ($_POST['id'] ?? 'desconocido'));
            
            flash('error', 'Acción no autorizada: Solo el Administrador puede modificar datos clínicos.');
            header('Location: index.php?accion=listado');
            exit;
        }

        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        $motivo = trim($_POST['motivo_cambio'] ?? '');

        if ($id <= 0) {
            flash('error', 'ID de paciente inválido.');
            header('Location: index.php?accion=listado');
            exit;
        }

        $usuario = usuarioActual();

        $resultado = $this->pacienteModel->actualizarSeguroConAuditoria($id, $_POST, (int)$usuario['id'], $motivo);

        if ($resultado) {
            registrarLog('INFO', 'PACIENTES', 'ACTUALIZAR_PACIENTE', 
                "El Administrador {$usuario['email']} actualizó datos del paciente ID {$id}. Motivo: {$motivo}");
            
            flash('exito', 'Ficha del paciente actualizada con éxito. Todos los cambios fueron auditados en el historial.');
            header("Location: index.php?accion=detalle&id={$id}");
            exit;
        } else {
            flash('error', 'Ocurrió un error al actualizar el paciente o registrar la auditoría.');
            header("Location: index.php?accion=editar&id={$id}");
            exit;
        }
    }

    /**
     * Ejecuta el borrado lógico de un paciente (Soft Delete)
     * BLOQUEO RBAC: Exclusivo para Administrador / SuperAdmin
     */
    public function eliminar(): void
    {
        requerirAutenticacion();

        if (!puedeEliminarPaciente()) {
            $u = usuarioActual();
            registrarLog('SECURITY', 'PACIENTES', 'INTENTO_ELIMINACION_BLOQUEADO', 
                "El trabajador {$u['email']} intentó eliminar al paciente ID " . ($_GET['id'] ?? 'desconocido'));
            
            flash('error', 'Seguridad: Los trabajadores no pueden eliminar expedientes.');
            header('Location: index.php?accion=listado');
            exit;
        }

        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $usuario = usuarioActual();

        if ($id > 0 && $this->pacienteModel->eliminarLogico($id, (int)$usuario['id'])) {
            registrarLog('WARNING', 'PACIENTES', 'BORRADO_LOGICO', 
                "El paciente ID {$id} fue marcado como inactivo por {$usuario['email']}");
            flash('exito', 'El paciente ha sido retirado (borrado lógico) del directorio activo.');
        } else {
            flash('error', 'No se pudo retirar el expediente del paciente.');
        }

        header('Location: index.php?accion=listado');
        exit;
    }

    /**
     * Muestra el directorio de pacientes con buscador
     */
    public function listar(): void
    {
        requerirAutenticacion();

        $busqueda = isset($_GET['q']) ? trim($_GET['q']) : null;
        $pacientes = [];
        $dbDisponible = $this->dbDisponible;

        if ($dbDisponible && $this->pacienteModel !== null) {
            try {
                $pacientes = $this->pacienteModel->obtenerTodos($busqueda);
            } catch (Throwable $e) {
                flash('error', 'Error al consultar pacientes: ' . $e->getMessage());
            }
        }

        $mensajeExito = flash('exito');
        $mensajeError = flash('error');

        require_once dirname(__DIR__) . '/views/pacientes/listado.php';
    }
}
