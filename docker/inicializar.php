<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once dirname(__DIR__) . '/config/database.php';
$db = Database::pdo();
$cantidad = (int) $db->query('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE()')->fetchColumn();
if ($cantidad !== 0) {
    fwrite(STDERR, "La base ya contiene tablas. Se cancela la instalación para conservar sus datos.\n");
    exit(1);
}
$db->exec(file_get_contents(dirname(__DIR__) . '/sql/instalar.sql'));
echo "Esquema creado en la base configurada.\n";
require __DIR__ . '/seed-demo.php';
require __DIR__ . '/datos-ficticios.php';
