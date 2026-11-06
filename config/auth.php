<?php
declare(strict_types=1);

require_once __DIR__ . '/app.php';
require_once __DIR__ . '/database.php';

/**
 * Módulo de Autenticación por DNI, Control de Acceso (RBAC) y Seguridad
 * Podología Ana Ramírez
 */

iniciarSesionSegura();

/**
 * Verifica si existe un usuario con sesión activa válida
 */
function estaAutenticado(): bool
{
    return isset($_SESSION['usuario']) && is_array($_SESSION['usuario']) && !empty($_SESSION['usuario']['id']);
}

/**
 * Obtiene el arreglo del usuario actualmente autenticado
 */
function usuarioActual(): ?array
{
    return estaAutenticado() ? $_SESSION['usuario'] : null;
}

/**
 * Obliga a que la petición provenga de un usuario autenticado. Si no, redirige a login.
 * También verifica si el usuario tiene pendiente el cambio obligatorio de contraseña.
 */
function requerirAutenticacion(): void
{
    if (!estaAutenticado()) {
        flash('error', 'Por favor ingrese con su número de DNI para acceder al sistema.');
        header('Location: index.php?accion=login');
        exit;
    }

    // Interceptación de seguridad: Si debe cambiar la contraseña inicial, bloquear navegación hasta que la cambie
    if (!empty($_SESSION['usuario']['debe_cambiar_password'])) {
        $accionActual = $_GET['accion'] ?? '';
        if (!in_array($accionActual, ['cambiar_password', 'guardar_password', 'logout'], true)) {
            flash('error', 'Por seguridad, debe actualizar su contraseña inicial (su DNI) antes de continuar.');
            header('Location: index.php?accion=cambiar_password');
            exit;
        }
    }
}

/**
 * Autentica al usuario EXCLUSIVAMENTE mediante su número de DNI y contraseña
 */
function autenticarPorDni(string $dni, string $password): bool
{
    $db = Database::pdo();
    $sql = "SELECT u.id, u.rol_id, u.dni, u.nombre, u.apellido, u.email, u.password_hash, 
                   u.debe_cambiar_password, u.especialidad, u.estado, 
                   r.slug AS rol_slug, r.nombre AS rol_nombre, r.nivel_jerarquia
            FROM usuarios u
            INNER JOIN roles r ON u.rol_id = r.id
            WHERE u.dni = :dni AND u.estado = 'activo'
            LIMIT 1";

    $stmt = $db->prepare($sql);
    $stmt->execute([':dni' => trim($dni)]);
    $usuario = $stmt->fetch();

    if ($usuario && password_verify($password, $usuario['password_hash'])) {
        session_regenerate_id(true);
        $nombreCompleto = $usuario['nombre'] . ' ' . $usuario['apellido'];

        $_SESSION['usuario'] = [
            'id'                    => (int) $usuario['id'],
            'rol_id'                => (int) $usuario['rol_id'],
            'rol_slug'              => $usuario['rol_slug'],
            'rol_nombre'            => $usuario['rol_nombre'],
            'nivel_jerarquia'       => (int) $usuario['nivel_jerarquia'],
            'dni'                   => $usuario['dni'],
            'nombre'                => $usuario['nombre'],
            'apellido'              => $usuario['apellido'],
            'nombre_completo'       => $nombreCompleto,
            'email'                 => $usuario['email'],
            'especialidad'          => $usuario['especialidad'],
            'debe_cambiar_password' => (int) $usuario['debe_cambiar_password']
        ];

        // Actualizar último acceso
        $up = $db->prepare("UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = :id");
        $up->execute([':id' => $usuario['id']]);

        registrarLog('INFO', 'AUTH', 'LOGIN_DNI_EXITOSO', "Ingreso exitoso por DNI {$usuario['dni']} ({$nombreCompleto})", (int) $usuario['id']);
        return true;
    }

    registrarLog('WARNING', 'AUTH', 'LOGIN_DNI_FALLIDO', "Intento fallido de acceso con DNI: " . htmlspecialchars($dni));
    return false;
}

/**
 * Actualiza la contraseña personal del usuario y desactiva la bandera debe_cambiar_password
 */
function cambiarPasswordUsuario(int $usuarioId, string $nuevaPassword): bool
{
    try {
        $db = Database::pdo();
        $hash = password_hash($nuevaPassword, PASSWORD_BCRYPT);

        $sql = "UPDATE usuarios 
                SET password_hash = :hash, 
                    debe_cambiar_password = 0, 
                    password_cambiado_en = NOW() 
                WHERE id = :id";
        
        $stmt = $db->prepare($sql);
        $res = $stmt->execute([
            ':hash' => $hash,
            ':id'   => $usuarioId
        ]);

        if ($res) {
            if (isset($_SESSION['usuario'])) {
                $_SESSION['usuario']['debe_cambiar_password'] = 0;
            }
            registrarLog('INFO', 'AUTH', 'PASSWORD_ACTUALIZADO', "El usuario ID {$usuarioId} actualizó su contraseña inicial por una personalizada.", $usuarioId);
            return true;
        }
        return false;
    } catch (Throwable $e) {
        error_log("[CambiarPassword Error] " . $e->getMessage());
        return false;
    }
}

/**
 * Cierra la sesión activa de forma segura
 */
function cerrarSesion(): void
{
    $usuario = usuarioActual();
    if ($usuario) {
        registrarLog('INFO', 'AUTH', 'LOGOUT', "Cierre de sesión de DNI: {$usuario['dni']}", (int) $usuario['id']);
    }

    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}

/**
 * Comprueba si el usuario actual tiene alguno de los roles permitidos
 */
function tieneRol(string|array $roles): bool
{
    $u = usuarioActual();
    if (!$u) return false;
    $rolesArray = is_array($roles) ? $roles : [$roles];
    return in_array($u['rol_slug'], $rolesArray, true);
}

/**
 * Revisa el rol del usuario; si no tiene permiso, bloquea con 403 y registra alerta
 */
function requerirRol(string|array $roles): void
{
    requerirAutenticacion();
    if (!tieneRol($roles)) {
        $u = usuarioActual();
        $rolesStr = is_array($roles) ? implode(', ', $roles) : $roles;
        registrarLog('SECURITY', 'RBAC', 'ACCESO_DENEGADO', "El usuario DNI {$u['dni']} con rol [{$u['rol_slug']}] intentó acceder a módulo [{$rolesStr}].", (int) $u['id']);
        
        flash('error', 'Acceso denegado: Su perfil no cuenta con autorización para realizar esta acción.');
        header('Location: index.php?accion=listado');
        exit;
    }
}

function puedeEditarPaciente(): bool
{
    return tieneRol(['admin', 'superadmin']);
}

function puedeEliminarPaciente(): bool
{
    return tieneRol(['admin', 'superadmin']);
}

function esSuperAdmin(): bool
{
    return tieneRol('superadmin');
}

function esAdmin(): bool
{
    return tieneRol('admin');
}

/**
 * Registra un evento en la tabla `logs_sistema`
 */
function registrarLog(string $nivel, string $modulo, string $accion, string $descripcion, ?int $usuarioId = null): void
{
    try {
        $db = Database::pdo();
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $ua = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 250);

        if ($usuarioId === null && estaAutenticado()) {
            $usuarioId = $_SESSION['usuario']['id'];
        }

        $sql = "INSERT INTO logs_sistema (usuario_id, nivel, modulo, accion, descripcion, ip_origen, user_agent)
                VALUES (:uid, :nivel, :modulo, :accion, :desc, :ip, :ua)";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':uid'    => $usuarioId,
            ':nivel'  => $nivel,
            ':modulo' => $modulo,
            ':accion' => $accion,
            ':desc'   => $descripcion,
            ':ip'     => $ip,
            ':ua'     => $ua
        ]);
    } catch (Throwable $e) {
        error_log("[Audit Log Error] " . $e->getMessage());
    }
}
