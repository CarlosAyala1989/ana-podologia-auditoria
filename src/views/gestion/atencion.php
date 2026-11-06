<?php
$tituloPagina = $id ? 'Editar atención' : 'Registrar atención';
require dirname(__DIR__) . '/layout/header.php';
require __DIR__ . '/avisos.php';
?>
<div class="mb-6"><h1 class="text-2xl sm:text-3xl font-black"><?= e($tituloPagina) ?></h1><p class="text-sm text-slate-600 mt-1">Registre la consulta, la valoración biomecánica y la evolución del paciente.</p></div>
<form method="post" class="space-y-6">
    <input type="hidden" name="token" value="<?= e(tokenFormulario()) ?>"><input type="hidden" name="id" value="<?= $id ?>">
    <section class="bg-white border border-slate-200 rounded-2xl p-5 sm:p-6">
        <h2 class="text-lg font-bold mb-4">Datos de la consulta</h2>
        <div class="grid sm:grid-cols-2 gap-4">
            <div><label for="paciente_id" class="etiqueta">Paciente *</label><select id="paciente_id" name="paciente_id" class="campo" required <?= $id ? 'disabled' : '' ?>><option value="">Seleccione un paciente</option><?php foreach ($pacientes as $p): ?><option value="<?= (int) $p['id'] ?>" <?= $pacienteId === (int) $p['id'] ? 'selected' : '' ?>><?= e($p['apellido'] . ', ' . $p['nombre'] . ' · ' . $p['documento_identidad']) ?></option><?php endforeach; ?></select></div>
            <div><label for="fecha_atencion" class="etiqueta">Fecha y hora *</label><input id="fecha_atencion" name="fecha_atencion" type="datetime-local" required class="campo" value="<?= e(substr(str_replace(' ', 'T', $atencion['fecha_atencion'] ?? date('Y-m-d H:i')), 0, 16)) ?>"></div>
            <?php if (!tieneRol('especialista')): ?><div><label for="especialista_id" class="etiqueta">Profesional responsable *</label><select id="especialista_id" name="especialista_id" class="campo" required><option value="">Seleccione un profesional</option><?php foreach ($especialistas as $u): ?><option value="<?= (int) $u['id'] ?>" <?= (int) ($atencion['especialista_id'] ?? 0) === (int) $u['id'] ? 'selected' : '' ?>><?= e($u['nombre'] . ' ' . $u['apellido']) ?></option><?php endforeach; ?></select></div><?php endif; ?>
            <div><label for="motivo_consulta" class="etiqueta">Motivo de consulta *</label><input id="motivo_consulta" name="motivo_consulta" required maxlength="255" class="campo" value="<?= e($atencion['motivo_consulta'] ?? '') ?>"></div>
        </div>
    </section>
    <section class="bg-white border border-slate-200 rounded-2xl p-5 sm:p-6">
        <h2 class="text-lg font-bold mb-4">Valoración clínica y biomecánica</h2>
        <div class="grid sm:grid-cols-2 gap-4">
            <div><label for="dolor_escala" class="etiqueta">Dolor: escala de 0 a 10 *</label><input id="dolor_escala" name="dolor_escala" type="number" min="0" max="10" required class="campo" value="<?= (int) ($atencion['dolor_escala'] ?? 0) ?>"></div>
            <div><label for="tipo_pisada" class="etiqueta">Tipo de pisada</label><input id="tipo_pisada" name="tipo_pisada" maxlength="50" class="campo" value="<?= e($atencion['tipo_pisada'] ?? '') ?>" placeholder="Neutra, pronadora, supinadora..."></div>
            <?php foreach (['evaluacion_marcha' => 'Evaluación de marcha y apoyo', 'piel_unas' => 'Exploración de piel y uñas', 'exploracion_vascular' => 'Exploración vascular', 'exploracion_neurologica' => 'Exploración neurológica'] as $campo => $nombre): ?>
                <div><label for="<?= $campo ?>" class="etiqueta"><?= e($nombre) ?></label><textarea id="<?= $campo ?>" name="<?= $campo ?>" rows="3" class="campo"><?= e($atencion[$campo] ?? '') ?></textarea></div>
            <?php endforeach; ?>
        </div>
    </section>
    <section class="bg-white border border-slate-200 rounded-2xl p-5 sm:p-6">
        <h2 class="text-lg font-bold mb-4">Diagnóstico, procedimiento y seguimiento</h2>
        <div class="grid sm:grid-cols-2 gap-4">
            <?php foreach (['diagnostico' => 'Diagnóstico *', 'procedimiento_realizado' => 'Procedimiento realizado *', 'evolucion_lesion' => 'Evolución de la lesión', 'prescripcion_recomendaciones' => 'Prescripción y recomendaciones', 'observaciones_privadas' => 'Notas internas del profesional'] as $campo => $nombre): ?>
                <div><label for="<?= $campo ?>" class="etiqueta"><?= e($nombre) ?></label><textarea id="<?= $campo ?>" name="<?= $campo ?>" rows="4" class="campo" <?= in_array($campo, ['diagnostico', 'procedimiento_realizado'], true) ? 'required' : '' ?>><?= e($atencion[$campo] ?? '') ?></textarea></div>
            <?php endforeach; ?>
            <div><label for="proxima_visita_sugerida" class="etiqueta">Próxima visita sugerida</label><input id="proxima_visita_sugerida" name="proxima_visita_sugerida" type="date" class="campo" value="<?= e($atencion['proxima_visita_sugerida'] ?? '') ?>"><p class="text-sm text-slate-600 mt-2">La reserva del turno se realiza desde Agenda.</p></div>
        </div>
    </section>
    <?php if ($id): ?><div><label for="motivo_cambio" class="etiqueta">Motivo de la corrección o retirada *</label><input id="motivo_cambio" name="motivo_cambio" required maxlength="255" class="campo" value="<?= e($_POST['motivo_cambio'] ?? '') ?>"></div><?php endif; ?>
    <div class="flex flex-wrap gap-3"><button class="boton" type="submit">Guardar atención</button><a class="boton-secundario" href="index.php?accion=<?= $pacienteId ? 'detalle&id=' . $pacienteId : 'listado' ?>"><?= $pacienteId ? 'Volver al expediente' : 'Volver al directorio' ?></a></div>
</form>
<?php if ($id): ?>
<form method="post" class="mt-6 border-t border-slate-200 pt-6" onsubmit="return confirm('¿Retirar esta atención? Se conservarán el registro y su auditoría.');">
    <input type="hidden" name="token" value="<?= e(tokenFormulario()) ?>"><input type="hidden" name="id" value="<?= $id ?>"><input type="hidden" name="retirar" value="1">
    <label for="motivo_retiro" class="etiqueta">Motivo de retirada</label><div class="flex flex-wrap gap-3"><input id="motivo_retiro" name="motivo_cambio" required maxlength="255" class="campo sm:max-w-lg"><button type="submit" class="boton-secundario text-rose-800">Retirar atención</button></div>
</form>
<?php endif; ?>
<?php require __DIR__ . '/auditoria.php'; require dirname(__DIR__) . '/layout/footer.php'; ?>
