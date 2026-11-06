<?php
declare(strict_types=1);

/**
 * Configuración General y Utilidades del Sistema
 * Podología Ana Ramírez
 */

// Zona horaria estándar
date_default_timezone_set('America/Guayaquil');

// Constantes de directorios base
define('ROOT_PATH', dirname(__DIR__));
define('CONFIG_PATH', ROOT_PATH . '/config');
define('SRC_PATH', ROOT_PATH . '/src');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('UPLOADS_PATH', ROOT_PATH . '/uploads');

/**
 * Inicia una sesión con directivas de cookies seguras (anti-hijacking y anti-XSS)
 */
function iniciarSesionSegura(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        // Directivas de seguridad para cookies de sesión
        ini_set('session.cookie_httponly', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.cookie_samesite', 'Lax');

        // Si se ejecuta bajo HTTPS (ej: en VPS con SSL)
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            ini_set('session.cookie_secure', '1');
        }

        session_start();

        // Regenerar ID periódicamente para prevenir session fixation
        if (!isset($_SESSION['ultima_regeneracion'])) {
            $_SESSION['ultima_regeneracion'] = time();
        } elseif (time() - $_SESSION['ultima_regeneracion'] > 1800) { // cada 30 min
            session_regenerate_id(true);
            $_SESSION['ultima_regeneracion'] = time();
        }
    }
}

/**
 * Función de escape contextual para prevenir ataques Cross-Site Scripting (XSS)
 */
function e(?string $cadena): string
{
    if ($cadena === null) {
        return '';
    }
    return htmlspecialchars($cadena, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function tokenFormulario(): string
{
    iniciarSesionSegura();
    if (empty($_SESSION['token_formulario'])) {
        $_SESSION['token_formulario'] = bin2hex(random_bytes(24));
    }
    return $_SESSION['token_formulario'];
}

function validarTokenFormulario(): void
{
    if (!is_string($_POST['token'] ?? null) || !hash_equals(tokenFormulario(), $_POST['token'])) {
        http_response_code(403);
        exit('El formulario venció. Regrese, recargue la página y vuelva a intentarlo.');
    }
}

/**
 * Establece o lee mensajes flash de sesión (alerta de éxito, advertencia o error)
 */
function flash(string $clave, ?string $mensaje = null): ?string
{
    iniciarSesionSegura();
    if ($mensaje !== null) {
        $_SESSION['flash'][$clave] = $mensaje;
        return null;
    }

    if (isset($_SESSION['flash'][$clave])) {
        $msg = $_SESSION['flash'][$clave];
        unset($_SESSION['flash'][$clave]);
        return $msg;
    }

    return null;
}
