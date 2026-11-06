<?php
declare(strict_types=1);

/**
 * Front Controller y Enrutador Central con Soporte RBAC
 * Podología Ana Ramírez
 */

require_once dirname(__DIR__) . '/config/app.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/auth.php';
require_once dirname(__DIR__) . '/src/controllers/AuthController.php';
require_once dirname(__DIR__) . '/src/controllers/PacienteController.php';
require_once dirname(__DIR__) . '/src/controllers/GestionController.php';

$accion = $_GET['accion'] ?? '';

// Si no está autenticado y no está solicitando login, redirigir a login
if (!estaAutenticado() && !in_array($accion, ['login', 'login_procesar'], true)) {
    header('Location: index.php?accion=login');
    exit;
}

// Si está autenticado y va a la raíz o a login, redirigir a listado
if (estaAutenticado() && empty($accion)) {
    $accion = 'listado';
}

$authControlador = new AuthController();
$pacienteControlador = new PacienteController();

switch ($accion) {
    case 'agenda':
        (new GestionController())->agenda();
        break;

    case 'atencion':
        (new GestionController())->atencion();
        break;

    case 'usuarios':
        (new GestionController())->usuarios();
        break;
    // 1. Módulo de Autenticación
    case 'login':
        $authControlador->mostrarLogin();
        break;

    case 'login_procesar':
        $authControlador->procesarLogin();
        break;

    case 'logout':
        $authControlador->cerrar();
        break;

    case 'cambiar_password':
        $authControlador->mostrarCambiarPassword();
        break;

    case 'guardar_password':
        $authControlador->guardarNuevoPassword();
        break;

    // 2. Módulo de Pacientes
    case 'registro':
        $pacienteControlador->mostrarFormularioRegistro();
        break;

    case 'guardar':
        $pacienteControlador->guardar();
        break;

    case 'listado':
        $pacienteControlador->listar();
        break;

    case 'detalle':
        $pacienteControlador->detalle();
        break;

    case 'editar':
        $pacienteControlador->editar();
        break;

    case 'actualizar':
        $pacienteControlador->actualizar();
        break;

    case 'eliminar':
        $pacienteControlador->eliminar();
        break;

    case 'imprimir_ficha':
        $pacienteControlador->imprimirFicha();
        break;

    // 3. Panel Técnico & Reportes Exclusivos SuperAdmin
    case 'sistema':
        requerirRol('superadmin');
        require_once dirname(__DIR__) . '/src/views/admin/sistema.php';
        break;

    case 'imprimir_directorio':
        requerirRol('superadmin');
        require_once dirname(__DIR__) . '/src/views/admin/imprimir_directorio.php';
        break;

    default:
        $pacienteControlador->listar();
        break;
}
