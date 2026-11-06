<?php
$tituloPagina = 'Agenda de citas';
require dirname(__DIR__) . '/layout/header.php';
require __DIR__ . '/avisos.php';
?>
<div class="mb-6 flex flex-wrap justify-between items-end gap-4">
    <div><h1 class="text-2xl sm:text-3xl font-black text-slate-900">Agenda de citas</h1><p class="text-sm text-slate-600 mt-1">Organice turnos y registre el estado de cada visita.</p></div>
    <form method="get" class="flex flex-wrap items-end gap-2">
        <input type="hidden" name="accion" value="agenda">
        <div><label for="fecha" class="block text-sm font-bold mb-1">Consultar día</label><input id="fecha" name="fecha" type="date" value="<?= e($fecha) ?>" class="campo" required></div>
        <button class="boton" type="submit">Ver agenda</button>
    </form>
</div>
<?php if (tieneRol(['admin', 'superadmin', 'recepcionista'])): ?>
<details class="mb-8 rounded-2xl border border-slate-200 bg-white p-5" <?= ($cita || $errores || isset($_GET['paciente_id'])) ? 'open' : '' ?>>
    <summary class="font-bold text-[#005B94] cursor-pointer py-2"><?= !empty($cita['id']) ? 'Editar cita #' . (int) $cita['id'] : 'Reservar nueva cita' ?></summary>
    <form method="post" action="index.php?accion=agenda&fecha=<?= e($fecha) ?>" class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <input type="hidden" name="token" value="<?= e(tokenFormulario()) ?>"><input type="hidden" name="id" value="<?= (int) ($cita['id'] ?? 0) ?>">
        <div><label for="paciente_id" class="etiqueta">Paciente *</label><select id="paciente_id" name="paciente_id" class="campo" required><option value="">Seleccione un paciente</option><?php foreach ($pacientes as $p): ?><option value="<?= (int) $p['id'] ?>" <?= (int) ($cita['paciente_id'] ?? $_GET['paciente_id'] ?? 0) === (int) $p['id'] ? 'selected' : '' ?>><?= e($p['apellido'] . ', ' . $p['nombre'] . ' · ' . $p['documento_identidad']) ?></option><?php endforeach; ?></select></div>
        <div><label for="especialista_id" class="etiqueta">Especialista *</label><select id="especialista_id" name="especialista_id" class="campo" required><option value="">Seleccione un profesional</option><?php foreach ($especialistas as $u): ?><option value="<?= (int) $u['id'] ?>" <?= (int) ($cita['especialista_id'] ?? 0) === (int) $u['id'] ? 'selected' : '' ?>><?= e($u['nombre'] . ' ' . $u['apellido']) ?></option><?php endforeach; ?></select></div>
        <div><label for="fecha_cita" class="etiqueta">Fecha de la cita *</label><input id="fecha_cita" name="fecha_cita" type="date" required class="campo" value="<?= e($cita['fecha_cita'] ?? $fecha) ?>"></div>
        <div><label for="hora_inicio" class="etiqueta">Hora de inicio *</label><input id="hora_inicio" name="hora_inicio" type="time" required class="campo" value="<?= e(substr($cita['hora_inicio'] ?? '09:00', 0, 5)) ?>"></div>
        <div><label for="hora_fin" class="etiqueta">Hora de fin *</label><input id="hora_fin" name="hora_fin" type="time" required class="campo" value="<?= e(substr($cita['hora_fin'] ?? '09:30', 0, 5)) ?>"></div>
        <div><label for="estado" class="etiqueta">Estado</label><select id="estado" name="estado" class="campo"><?php foreach (['pendiente' => 'Pendiente', 'en_atencion' => 'En atención', 'finalizado' => 'Finalizado', 'cancelado' => 'Cancelado'] as $valor => $nombre): ?><option value="<?= e($valor) ?>" <?= ($cita['estado'] ?? 'pendiente') === $valor ? 'selected' : '' ?>><?= e($nombre) ?></option><?php endforeach; ?></select></div>
        <div class="sm:col-span-2"><label for="motivo" class="etiqueta">Motivo de consulta *</label><input id="motivo" name="motivo" required maxlength="255" class="campo" value="<?= e($cita['motivo'] ?? '') ?>"></div>
        <div><label for="notas" class="etiqueta">Notas de la reserva</label><input id="notas" name="notas" maxlength="255" class="campo" value="<?= e($cita['notas'] ?? '') ?>"></div>
        <div class="sm:col-span-2 lg:col-span-3 flex flex-wrap gap-3"><button type="submit" class="boton">Guardar cita</button><a class="boton-secundario" href="index.php?accion=agenda&fecha=<?= e($fecha) ?>">Cancelar</a></div>
    </form>
</details>
<?php endif; ?>
<div class="rounded-2xl border border-slate-200 bg-white overflow-x-auto">
    <table class="w-full text-sm text-left"><caption class="text-left p-5 font-bold text-slate-800"><?= count($citas) ?> citas para <?= e(date('d/m/Y', strtotime($fecha))) ?></caption><thead class="bg-slate-100 text-slate-700"><tr><th class="p-4">Horario</th><th class="p-4">Paciente y motivo</th><th class="p-4">Profesional</th><th class="p-4">Estado y acciones</th></tr></thead><tbody>
    <?php foreach ($citas as $c): ?>
        <tr class="border-t border-slate-200 align-top"><td class="p-4 whitespace-nowrap tabular-nums font-bold"><?= e(substr($c['hora_inicio'], 0, 5)) ?>–<?= e(substr($c['hora_fin'], 0, 5)) ?></td>
        <td class="p-4"><a class="font-bold text-[#005B94] underline" href="index.php?accion=detalle&id=<?= (int) $c['paciente_id'] ?>"><?= e($c['paciente_nombre']) ?></a><p class="mt-1"><?= e($c['motivo']) ?></p><p class="text-slate-600 mt-1"><?= e($c['notas'] ?? '') ?></p></td>
        <td class="p-4"><?= e($c['especialista_nombre']) ?></td><td class="p-4"><p class="font-bold mb-2"><?= e(ucfirst(str_replace('_', ' ', $c['estado']))) ?></p>
        <?php if (!tieneRol('especialista') || (int) $c['especialista_id'] === (int) usuarioActual()['id']): ?>
            <form method="post" class="flex flex-wrap gap-2"><input type="hidden" name="token" value="<?= e(tokenFormulario()) ?>"><input type="hidden" name="id" value="<?= (int) $c['id'] ?>"><input type="hidden" name="cambiar_estado" value="1"><label class="sr-only" for="estado-<?= (int) $c['id'] ?>">Estado de la cita de <?= e($c['paciente_nombre']) ?></label><select name="estado" id="estado-<?= (int) $c['id'] ?>" class="campo max-w-44"><?php foreach (['pendiente', 'en_atencion', 'finalizado', 'cancelado'] as $v): ?><option value="<?= e($v) ?>" <?= $v === $c['estado'] ? 'selected' : '' ?>><?= e(ucfirst(str_replace('_', ' ', $v))) ?></option><?php endforeach; ?></select><button class="boton-secundario" type="submit">Actualizar</button></form>
        <?php endif; ?>
        <?php if (tieneRol(['admin', 'superadmin', 'recepcionista'])): ?><a class="inline-block text-[#005B94] underline mt-3" href="index.php?accion=agenda&fecha=<?= e($fecha) ?>&editar=<?= (int) $c['id'] ?>">Editar reserva</a><?php endif; ?>
        <?php if (tieneRol(['admin', 'superadmin', 'especialista'])): ?><a class="inline-block text-[#005B94] underline mt-3 ml-2" href="index.php?accion=atencion&paciente_id=<?= (int) $c['paciente_id'] ?>">Registrar atención</a><?php endif; ?></td></tr>
    <?php endforeach; ?>
    <?php if (!$citas): ?><tr><td colspan="4" class="p-10 text-center text-slate-600">No hay citas para este día. Puede reservar un turno desde el formulario superior.</td></tr><?php endif; ?>
    </tbody></table>
</div>
<?php require __DIR__ . '/auditoria.php'; require dirname(__DIR__) . '/layout/footer.php'; ?>
