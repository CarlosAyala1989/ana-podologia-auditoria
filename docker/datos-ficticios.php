<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once dirname(__DIR__) . '/config/app.php';
require_once dirname(__DIR__) . '/src/models/Paciente.php';

$db = Database::pdo();
if ($db->query("SELECT id FROM logs_sistema WHERE accion = 'CARGA_FICTICIA_V1' LIMIT 1")->fetchColumn()) {
    echo "Los datos ficticios ya fueron cargados; se conservan los registros actuales.\n";
    return;
}
$personal = $db->query("SELECT u.id, r.slug FROM usuarios u JOIN roles r ON r.id = u.rol_id WHERE u.dni IN ('90000001', '90000002', '90000003', '90000004')")->fetchAll(PDO::FETCH_KEY_PAIR);
$ids = array_flip($personal);
foreach (['superadmin', 'admin', 'especialista', 'recepcionista'] as $rol) {
    if (!isset($ids[$rol])) {
        throw new RuntimeException('Ejecute primero docker/seed-demo.php.');
    }
}
$nombres = ['Rosa Elena', 'Luis Alberto', 'Carmen Julia', 'Jorge Enrique', 'Patricia Isabel', 'Miguel Ángel', 'Teresa del Pilar', 'Eduardo José', 'Mónica Gabriela', 'Raúl Fernando', 'Silvia Mercedes', 'Víctor Manuel', 'Gloria Beatriz', 'Pedro Antonio', 'Elena Maribel', 'Ricardo Daniel', 'Susana Milagros', 'Óscar Javier', 'Claudia Rocío', 'Héctor Andrés', 'Nelly Consuelo', 'Fernando Martín', 'María Luisa', 'Julio César', 'Lourdes Cecilia', 'Carlos Alfredo', 'Adriana Lucía', 'Sergio Iván', 'Isabel Cristina', 'Roberto Alonso', 'Diana Carolina', 'Manuel Ernesto', 'Verónica Liz', 'Arturo Benjamín', 'Pilar Eugenia', 'Renato Gabriel'];
$apellidos = ['Quispe Mendoza', 'Flores Vargas', 'Mamani Rojas', 'Castro Aguilar', 'Huamán Salinas', 'Vargas Paredes', 'Torres Medina', 'Rojas Cáceres', 'Chávez Condori', 'Gutiérrez León', 'Paredes Soto', 'Salazar Núñez'];
$calles = ['Jr. Los Jazmines', 'Av. Las Palmeras', 'Calle Los Olivos', 'Jr. Santa Rosa', 'Av. Los Sauces', 'Calle El Bosque'];
$diagnosticos = ['Onicocriptosis lateral del primer dedo', 'Hiperqueratosis plantar en antepié', 'Onicomicosis distal en evaluación', 'Heloma interdigital en cuarto espacio', 'Fisuras superficiales en talón', 'Dolor plantar asociado a sobrecarga'];
$procedimientos = ['Limpieza de surco y descarga del borde ungueal', 'Deslaminado superficial y descarga de presión', 'Corte técnico y limpieza ungueal', 'Enucleación conservadora y separador digital', 'Desbridamiento superficial e hidratación', 'Evaluación de apoyo y adaptación de descarga'];
$pacienteModelo = new Paciente($db);
$db->beginTransaction();
try {
    $historia = $db->prepare('INSERT INTO historias_clinicas (paciente_id, especialista_id, creado_por_usuario_id, fecha_creacion, hora_creacion, fecha_atencion, motivo_consulta, diagnostico, procedimiento_realizado, evolucion_lesion, prescripcion_recomendaciones, observaciones_privadas, proxima_visita_sugerida, dolor_escala, tipo_pisada, evaluacion_marcha, exploracion_vascular, exploracion_neurologica, piel_unas) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $cita = $db->prepare('INSERT INTO citas (paciente_id, especialista_id, creado_por_usuario_id, fecha_creacion, hora_creacion, fecha_cita, hora_inicio, hora_fin, motivo, estado, notas) VALUES (?, ?, ?, CURDATE(), CURTIME(), ?, ?, ?, ?, ?, ?)');
    $audit = $db->prepare('INSERT INTO historial_cambios_pacientes (paciente_id, campo_modificado, valor_anterior, valor_nuevo, modificado_por_usuario_id, motivo_cambio) VALUES (?, ?, ?, ?, ?, ?)');
    $modulo = $db->prepare('INSERT INTO auditoria_modulos (entidad, registro_id, campo, valor_anterior, valor_nuevo, usuario_id, motivo) VALUES (?, ?, ?, ?, ?, ?, ?)');
    foreach ($nombres as $i => $nombre) {
        $d = [
            'documento_identidad' => (string) (76001001 + $i), 'tipo_documento' => 'DNI',
            'nombre' => $nombre, 'apellido' => $apellidos[$i % count($apellidos)],
            'fecha_nacimiento' => sprintf('%04d-%02d-%02d', 1950 + ($i * 7 % 49), 1 + $i % 12, 1 + $i % 27),
            'genero' => $i % 2 ? 'Masculino' : 'Femenino', 'telefono' => (string) (951320101 + $i * 137),
            'telefono_emergencia' => (string) (952410201 + $i * 113), 'contacto_emergencia' => 'Familiar: ' . $apellidos[$i % 12],
            'email' => 'paciente.' . ($i + 1) . '@example.test', 'direccion' => $calles[$i % 6] . ' ' . (120 + $i * 9) . ', Tacna',
            'ocupacion' => ['Docente', 'Comerciante', 'Jubilado', 'Administrativo', 'Enfermera', 'Conductor'][$i % 6],
            'es_diabetico' => $i % 4 === 0, 'tipo_diabetes' => $i % 4 === 0 ? 'Tipo 2, en control médico' : '',
            'tiene_hipertension' => $i % 5 === 0, 'problemas_circulatorios' => $i % 6 === 0,
            'usa_anticoagulantes' => $i % 9 === 0, 'tiene_marcapasos' => $i === 18, 'enfermedades_renales' => $i === 24,
            'otras_patologias' => $i % 7 === 0 ? 'Artrosis de rodilla referida por el paciente' : 'No refiere otras patologías',
            'alergias_medicamentos' => $i % 8 === 0 ? 'Refiere alergia a penicilina' : 'No refiere',
            'alergias_contacto' => $i % 10 === 0 ? 'Sensibilidad a adhesivos' : 'No refiere',
            'tipo_calzado_habitual' => ['Deportivo', 'Cerrado de cuero', 'Zapato de trabajo'][$i % 3],
            'talla_calzado' => (string) (35 + $i % 10), 'usa_plantillas' => $i % 5 === 0,
            'actividad_fisica' => ['Caminatas 30 minutos', 'Actividad sedentaria', 'Trabajo de pie 6 horas'][$i % 3],
        ];
        $id = $pacienteModelo->crear($d, (int) $ids['recepcionista']);
        if (!$id) {
            throw new RuntimeException('No se pudo crear el paciente ficticio ' . ($i + 1));
        }
        for ($j = 0; $j < 2; $j++) {
            $fecha = (new DateTimeImmutable('today'))->modify('-' . (50 - $i + $j * 10) . ' days')->setTime(9 + $i % 8, 0);
            $historia->execute([$id, $ids['especialista'], $ids['especialista'], $fecha->format('Y-m-d'), $fecha->format('H:i:s'), $fecha->format('Y-m-d H:i:s'), 'Molestia en apoyo y revisión podológica', $diagnosticos[($i + $j) % 6], $procedimientos[($i + $j) % 6], $j ? 'Mejoría progresiva, sin signos de alarma' : 'Primera evaluación y registro de lesión', 'Higiene diaria, calzado amplio y control programado', 'Seguimiento de tolerancia al procedimiento', $fecha->modify('+30 days')->format('Y-m-d'), 2 + $i % 6, ['Neutra', 'Pronadora', 'Supinadora'][$i % 3], 'Apoyo conservado con leve sobrecarga del antepié', 'Pulsos pedios palpables; relleno capilar conservado', 'Sensibilidad superficial conservada', 'Piel seca y engrosamiento ungueal localizado']);
            $historiaId = (int) $db->lastInsertId();
            $modulo->execute(['historias_clinicas', $historiaId, 'creacion', null, 'Atención registrada', $ids['especialista'], 'Carga de datos ficticios']);
        }
        for ($j = 0; $j < 2; $j++) {
            $dia = (new DateTimeImmutable('today'))->modify(($j ? '-' : '+') . intdiv($i, 12) . ' days');
            $inicio = sprintf('%02d:%02d:00', 8 + intdiv($i % 12, 2), $i % 2 ? 30 : 0);
            $fin = (new DateTimeImmutable($inicio))->modify('+30 minutes')->format('H:i:s');
            $cita->execute([$id, $j ? $ids['admin'] : $ids['especialista'], $ids['recepcionista'], $dia->format('Y-m-d'), $inicio, $fin, $j ? 'Revisión de evolución' : 'Control podológico programado', $j ? 'finalizado' : 'pendiente', 'Paciente solicita confirmar el turno por teléfono']);
        }
        if ($i % 3 === 0) {
            $audit->execute([$id, 'telefono', (string) (951100000 + $i), $d['telefono'], $ids['admin'], 'Paciente comunica cambio de número de contacto']);
        }
    }
    $s = $db->prepare("INSERT INTO logs_sistema (usuario_id, nivel, modulo, accion, descripcion, ip_origen, user_agent) VALUES (?, 'INFO', 'DEMO', 'CARGA_FICTICIA_V1', 'Carga de 36 pacientes, 72 atenciones y 72 citas sintéticas', '127.0.0.1', 'Inicializador CLI')");
    $s->execute([$ids['superadmin']]);
    $db->commit();
    echo "Creados 36 pacientes, 72 atenciones, 72 citas y sus registros de auditoría. Todos son ficticios.\n";
} catch (Throwable $e) {
    $db->rollBack();
    throw $e;
}
