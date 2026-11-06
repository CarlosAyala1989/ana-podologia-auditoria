<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/config/app.php';
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/config/auth.php';

/**
 * Controlador de Autenticación por DNI y Cambio de Contraseña Inicial
 * Podología Ana Ramírez
 */
class AuthController
{
    /**
     * Muestra la pantalla de inicio de sesión con el nuevo emblema central corporativo
     */
    public function mostrarLogin(): void
    {
        if (estaAutenticado()) {
            if (!empty($_SESSION['usuario']['debe_cambiar_password'])) {
                header('Location: index.php?accion=cambiar_password');
            } else {
                header('Location: index.php?accion=listado');
            }
            exit;
        }

        $mensajeExito = flash('exito');
        $mensajeError = flash('error');

        require_once dirname(__DIR__) . '/views/auth/login.php';
    }

    /**
     * Procesa la solicitud POST de acceso exclusivamente mediante DNI
     */
    public function procesarLogin(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?accion=login');
            exit;
        }

        $dni      = trim($_POST['dni'] ?? '');
        $password = (string) ($_POST['password'] ?? '');

        if (empty($dni) || empty($password)) {
            flash('error', 'Por favor ingrese su número de DNI / Cédula y su contraseña.');
            header('Location: index.php?accion=login');
            exit;
        }

        if (autenticarPorDni($dni, $password)) {
            $usuario = usuarioActual();

            // Si es su primer ingreso con DNI como contraseña, exigir el cambio de inmediato
            if (!empty($usuario['debe_cambiar_password'])) {
                flash('exito', "Bienvenido(a), <strong>{$usuario['nombre_completo']}</strong>. Por seguridad de la clínica, debe actualizar su contraseña inicial.");
                header('Location: index.php?accion=cambiar_password');
                exit;
            }

            flash('exito', "Bienvenido(a) de nuevo, <strong>{$usuario['nombre_completo']}</strong> ({$usuario['rol_nombre']}).");

            if (esSuperAdmin()) {
                header('Location: index.php?accion=sistema');
            } else {
                header('Location: index.php?accion=listado');
            }
            exit;
        } else {
            flash('error', 'DNI o contraseña incorrectos. Utilice la clave asignada por administración.');
            header('Location: index.php?accion=login');
            exit;
        }
    }

    /**
     * Muestra el formulario obligatorio de cambio de contraseña inicial
     */
    public function mostrarCambiarPassword(): void
    {
        if (!estaAutenticado()) {
            header('Location: index.php?accion=login');
            exit;
        }

        $usuario = usuarioActual();
        $mensajeError = flash('error');
        $mensajeExito = flash('exito');

        require_once dirname(__DIR__) . '/views/auth/cambiar_password.php';
    }

    /**
     * Procesa y valida el cambio de contraseña
     */
    public function guardarNuevoPassword(): void
    {
        if (!estaAutenticado()) {
            header('Location: index.php?accion=login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?accion=cambiar_password');
            exit;
        }

        $usuario = usuarioActual();
        $nueva     = (string) ($_POST['nueva_password'] ?? '');
        $confirmar = (string) ($_POST['confirmar_password'] ?? '');

        if (strlen($nueva) < 6) {
            flash('error', 'La nueva contraseña debe tener al menos 6 caracteres.');
            header('Location: index.php?accion=cambiar_password');
            exit;
        }

        if ($nueva !== $confirmar) {
            flash('error', 'Las contraseñas ingresadas no coinciden. Intente de nuevo.');
            header('Location: index.php?accion=cambiar_password');
            exit;
        }

        if ($nueva === $usuario['dni']) {
            flash('error', 'Por seguridad, la nueva contraseña no puede ser idéntica a su número de DNI.');
            header('Location: index.php?accion=cambiar_password');
            exit;
        }

        if (cambiarPasswordUsuario((int)$usuario['id'], $nueva)) {
            flash('exito', '¡Contraseña actualizada exitosamente! Su cuenta ha quedado protegida.');
            
            if (esSuperAdmin()) {
                header('Location: index.php?accion=sistema');
            } else {
                header('Location: index.php?accion=listado');
            }
            exit;
        } else {
            flash('error', 'Error al guardar la nueva contraseña en el sistema.');
            header('Location: index.php?accion=cambiar_password');
            exit;
        }
    }

    /**
     * Cierra la sesión activa
     */
    public function cerrar(): void
    {
        cerrarSesion();
        flash('exito', 'Ha cerrado sesión de forma segura.');
        header('Location: index.php?accion=login');
        exit;
    }
}
