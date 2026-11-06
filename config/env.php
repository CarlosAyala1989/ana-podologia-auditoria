<?php
declare(strict_types=1);

// Carga sencilla para ejecutar también con php -S, sin dependencias externas.
$archivoEnv = dirname(__DIR__) . '/.env';
if (is_file($archivoEnv)) {
    foreach (file($archivoEnv, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $linea) {
        if (str_starts_with(trim($linea), '#') || !str_contains($linea, '=')) {
            continue;
        }
        [$nombre, $valor] = explode('=', $linea, 2);
        $nombre = trim($nombre);
        if (getenv($nombre) === false) {
            putenv($nombre . '=' . trim($valor));
        }
    }
}
