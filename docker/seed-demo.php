<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once dirname(__DIR__) . '/config/database.php';

// Este inicializador se ejecuta únicamente en el contenedor demo-init.
$cuentas = [
    ['90000001', 'superadmin', 'Alberto', 'Peña', 'DEMO_SUPERADMIN_PASSWORD'],
    ['90000002', 'admin', 'Ana', 'Ramírez', 'DEMO_ADMIN_PASSWORD'],
    ['90000003', 'especialista', 'Valeria', 'Quiroz', 'DEMO_ESPECIALISTA_PASSWORD'],
    ['90000004', 'recepcionista', 'Marisol', 'Torres', 'DEMO_RECEPCIONISTA_PASSWORD'],
];

$db = Database::pdo();
$db->beginTransaction();

try {
    $buscar = $db->prepare('SELECT id FROM usuarios WHERE dni = :dni');
    $buscarRol = $db->prepare('SELECT id FROM roles WHERE slug = :slug');
    $insertar = $db->prepare(
        'INSERT INTO usuarios
            (rol_id, dni, nombre, apellido, email, password_hash, debe_cambiar_password, estado)
         VALUES (:rol, :dni, :nombre, :apellido, :email, :hash, 0, :estado)'
    );

    foreach ($cuentas as [$dni, $rol, $nombre, $apellido, $variable]) {
        $buscar->execute([':dni' => $dni]);
        if ($buscar->fetchColumn() !== false) {
            echo "Cuenta demo {$rol} existente; se conserva su contraseña.\n";
            continue;
        }

        $password = getenv($variable);
        if ($password === false || strlen($password) < 12) {
            throw new RuntimeException("{$variable} debe contener al menos 12 caracteres.");
        }

        $buscarRol->execute([':slug' => $rol]);
        $rolId = $buscarRol->fetchColumn();
        if ($rolId === false) {
            throw new RuntimeException("No existe el rol {$rol}; revise la inicialización del esquema.");
        }

        $insertar->execute([
            ':rol' => (int) $rolId,
            ':dni' => $dni,
            ':nombre' => $nombre,
            ':apellido' => $apellido,
            ':email' => "{$rol}@demo.example.test",
            ':hash' => password_hash($password, PASSWORD_BCRYPT),
            ':estado' => 'activo',
        ]);
        echo "Cuenta demo {$rol} creada: DNI {$dni}.\n";
    }

    $db->commit();
} catch (Throwable $error) {
    $db->rollBack();
    fwrite(STDERR, $error->getMessage() . "\n");
    exit(1);
}
