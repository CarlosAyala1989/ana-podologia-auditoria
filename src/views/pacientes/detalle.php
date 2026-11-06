<?php
$tituloPagina = "Expediente del Paciente - " . e($paciente['nombre']) . " " . e($paciente['apellido']);
require_once dirname(__DIR__) . '/layout/header.php';
?>

<!-- ALERTAS DE OPERACIÓN -->
<?php if (!empty($mensajeExito)): ?>
    <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-start space-x-3 shadow-sm animate-fade-in">
        <i class="fa-solid fa-circle-check text-xl text-emerald-600 mt-0.5"></i>
        <div class="text-sm font-medium"><?= $mensajeExito ?></div>
    </div>
<?php endif; ?>

<?php if (!empty($mensajeError)): ?>
    <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 flex items-start space-x-3 shadow-sm animate-fade-in">
        <i class="fa-solid fa-triangle-exclamation text-xl text-rose-600 mt-0.5"></i>
        <div class="text-sm font-medium"><?= $mensajeError ?></div>
    </div>
<?php endif; ?>

<!-- BANNER FORENSE DE TRAZABILIDAD (QUIÉN CREÓ EL REGISTRO) -->
<div class="mb-6 p-4 rounded-2xl bg-gradient-to-r from-[#0A2540] to-[#005B94] text-white shadow-md flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div class="flex items-center space-x-3">
        <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center text-[#2EC4B6]">
            <i class="fa-solid fa-fingerprint text-xl"></i>
        </div>
        <div>
            <div class="text-[11px] uppercase tracking-wider text-[#2EC4B6] font-bold">Trazabilidad de Creación</div>
            <div class="text-sm font-semibold">
                Expediente abierto por: <span class="text-white underline decoration-[#2EC4B6] font-bold"><?= e($paciente['creado_por_nombre'] ?? 'Personal de Clínica') ?></span>
                <span class="text-xs text-slate-300">(<?= e($paciente['creado_por_rol'] ?? 'Especialista') ?>)</span>
            </div>
        </div>
    </div>
    <div class="text-xs text-slate-200 bg-white/10 px-3 py-1.5 rounded-xl border border-white/10 font-mono">
        <i class="fa-regular fa-clock mr-1 text-[#2EC4B6]"></i> <?= e($paciente['fecha_creacion']) ?> • <?= e($paciente['hora_creacion']) ?>
    </div>
</div>

<!-- ENCABEZADO DE FICHA Y ACCIONES RBAC -->
<div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
        <div class="flex items-center space-x-3">
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900">
                <?= e($paciente['apellido']) ?>, <?= e($paciente['nombre']) ?>
            </h1>
            <span class="px-2.5 py-0.5 text-xs font-bold rounded-full bg-slate-200 text-slate-700 font-mono">
                <?= e($paciente['tipo_documento']) ?>: <?= e($paciente['documento_identidad']) ?>
            </span>
        </div>
        <p class="text-xs text-slate-500 mt-1">
            Expediente Clínico #<?= $paciente['id'] ?> • Estado: 
            <span class="font-bold <?= $paciente['estado'] === 'activo' ? 'text-emerald-600' : 'text-rose-600' ?>">
                <?= ucfirst(e($paciente['estado'])) ?>
            </span>
        </p>
    </div>

    <!-- BOTONES DE ACCIÓN SEGÚN PERMISOS -->
    <div class="flex flex-wrap items-center gap-2">
        <a href="index.php?accion=listado" class="touch-target inline-flex items-center px-4 py-2 text-xs font-bold rounded-xl border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 transition shadow-sm">
            <i class="fa-solid fa-arrow-left mr-2"></i> Directorio
        </a>

        <?php if (puedeEditarPaciente()): ?>
            <!-- Botón de Impresión / Guardado PDF para Dueños y Administradores -->
            <a href="index.php?accion=imprimir_ficha&id=<?= $paciente['id'] ?>" target="_blank"
               class="touch-target inline-flex items-center px-4 py-2 text-xs font-bold rounded-xl border border-[#005B94] bg-white text-[#005B94] hover:bg-[#005B94] hover:text-white transition shadow-sm">
                <i class="fa-solid fa-file-pdf mr-2 text-rose-600"></i> Imprimir Ficha / PDF
            </a>

            <!-- Botón exclusivo para Admin / SuperAdmin -->
            <a href="index.php?accion=editar&id=<?= $paciente['id'] ?>" 
               class="touch-target inline-flex items-center px-4 py-2 text-xs font-bold rounded-xl bg-[#005B94] text-white hover:bg-[#0A2540] transition shadow-sm">
                <i class="fa-solid fa-pen-to-square mr-2 text-[#2EC4B6]"></i> Editar Datos Secundarios
            </a>

            <a href="index.php?accion=eliminar&id=<?= $paciente['id'] ?>" 
               onclick="return confirm('¿Está seguro de retirar este paciente del directorio activo? Se aplicará borrado lógico preservando la auditoría.');"
               class="touch-target inline-flex items-center px-3 py-2 text-xs font-bold rounded-xl border border-rose-300 bg-rose-50 text-rose-700 hover:bg-rose-100 transition shadow-sm">
                <i class="fa-solid fa-trash-can mr-1.5"></i> Retirar
            </a>
        <?php else: ?>
            <!-- AVISO DE RESTRICCIÓN PARA TRABAJADORES -->
            <div class="inline-flex items-center px-3 py-2 rounded-xl bg-amber-50 border border-amber-300 text-amber-900 text-xs">
                <i class="fa-solid fa-shield-halved text-amber-600 mr-2"></i>
                <span>Modificación bloqueada (Solicitar corrección a la Administración)</span>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- COLUMNA IZQUIERDA: RESUMEN CLÍNICO Y CONTACTO -->
    <div class="space-y-6">
        
        <!-- Tarjeta de Contacto -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm space-y-3">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Datos de Contacto</h3>
            <div class="text-sm space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-slate-500">Teléfono:</span>
                    <a href="tel:<?= e($paciente['telefono']) ?>" class="font-bold text-[#005B94] hover:underline flex items-center">
                        <i class="fa-solid fa-phone mr-1.5 text-xs text-[#2EC4B6]"></i> <?= e($paciente['telefono']) ?>
                    </a>
                </div>
                <?php if (!empty($paciente['telefono_emergencia'])): ?>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500">Emergencia:</span>
                        <span class="font-semibold text-slate-700"><?= e($paciente['telefono_emergencia']) ?> (<?= e($paciente['contacto_emergencia']) ?>)</span>
                    </div>
                <?php endif; ?>
                <?php if (!empty($paciente['email'])): ?>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500">Email:</span>
                        <span class="font-semibold text-slate-700"><?= e($paciente['email']) ?></span>
                    </div>
                <?php endif; ?>
                <?php if (!empty($paciente['direccion'])): ?>
                    <div class="pt-2 border-t border-slate-100 text-xs text-slate-600">
                        <i class="fa-solid fa-location-dot text-slate-400 mr-1"></i> <?= e($paciente['direccion']) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Tarjeta de Factores de Riesgo Clínico -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm space-y-3">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Factores de Riesgo Podológico</h3>
            <div class="space-y-2">
                <?php if (!empty($paciente['es_diabetico'])): ?>
                    <div class="p-3 rounded-xl bg-rose-50 border border-rose-200 flex items-start space-x-2.5">
                        <i class="fa-solid fa-triangle-exclamation text-rose-600 mt-0.5"></i>
                        <div class="text-xs text-rose-900 font-semibold">
                            <div>Paciente Diabético (Protocolo Pie Diabético)</div>
                            <div class="text-[11px] font-normal text-rose-700"><?= e($paciente['tipo_diabetes'] ?? 'Tipo no especificado') ?></div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($paciente['usa_anticoagulantes'])): ?>
                    <div class="p-3 rounded-xl bg-amber-50 border border-amber-200 flex items-start space-x-2.5">
                        <i class="fa-solid fa-droplet text-amber-600 mt-0.5"></i>
                        <div class="text-xs text-amber-900 font-semibold">
                            Terapia Anticoagulante (Alerta Sangrado)
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($paciente['problemas_circulatorios'])): ?>
                    <div class="p-2.5 rounded-xl bg-cyan-50 border border-cyan-200 text-xs text-cyan-900 font-semibold">
                        <i class="fa-solid fa-heart-pulse text-cyan-600 mr-1.5"></i> Insuficiencia Circulatoria
                    </div>
                <?php endif; ?>

                <?php if (!empty($paciente['tiene_marcapasos'])): ?>
                    <div class="p-2.5 rounded-xl bg-purple-50 border border-purple-200 text-xs text-purple-900 font-semibold">
                        <i class="fa-solid fa-bolt text-purple-600 mr-1.5"></i> Portador de Marcapasos
                    </div>
                <?php endif; ?>

                <?php if (empty($paciente['es_diabetico']) && empty($paciente['usa_anticoagulantes']) && empty($paciente['problemas_circulatorios'])): ?>
                    <div class="text-xs text-slate-400 italic">No registra patologías de alto riesgo podológico.</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Biomecánica y Calzado -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm space-y-3">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Biomecánica y Calzado</h3>
            <div class="text-xs text-slate-700 space-y-2">
                <div class="flex justify-between">
                    <span class="text-slate-500">Calzado Habitual:</span>
                    <span class="font-bold"><?= e($paciente['tipo_calzado_habitual'] ?? 'No especificado') ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Talla EUR:</span>
                    <span class="font-bold"><?= e($paciente['talla_calzado'] ?? '-') ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Uso de Plantillas:</span>
                    <span class="font-bold"><?= !empty($paciente['usa_plantillas']) ? 'Sí (Previa prescripción)' : 'No' ?></span>
                </div>
            </div>
        </div>

    </div>

    <!-- COLUMNA DERECHA: AUDITORÍA INMUTABLE DE CAMBIOS -->
    <div class="lg:col-span-2 space-y-6">

        <section id="atenciones" class="bg-white rounded-2xl border border-slate-200 p-5 sm:p-6 scroll-mt-48">
            <div class="flex flex-wrap justify-between gap-3 mb-4"><h2 class="text-lg font-bold">Atenciones y evolución clínica</h2>
                <div class="flex flex-wrap gap-3">
                    <?php if ($paciente['estado'] === 'activo' && tieneRol(['admin', 'superadmin', 'especialista'])): ?><a class="text-[#005B94] font-bold underline" href="index.php?accion=atencion&paciente_id=<?= (int) $paciente['id'] ?>">Registrar atención</a><?php endif; ?>
                    <?php if ($paciente['estado'] === 'activo' && tieneRol(['admin', 'superadmin', 'recepcionista'])): ?><a class="text-[#005B94] font-bold underline" href="index.php?accion=agenda&paciente_id=<?= (int) $paciente['id'] ?>">Reservar cita</a><?php endif; ?>
                </div>
            </div>
            <?php if (!$historiasClinicas): ?><p class="text-sm text-slate-600 py-4">Este expediente todavía no tiene atenciones registradas.</p><?php endif; ?>
            <?php foreach ($historiasClinicas as $hc): ?>
                <article class="border-t border-slate-200 py-5 space-y-3">
                    <div class="flex flex-wrap justify-between gap-2"><h3 class="font-bold"><?= e(date('d/m/Y H:i', strtotime($hc['fecha_atencion']))) ?> · <?= e($hc['especialista_nombre']) ?></h3><?php if (puedeEditarPaciente()): ?><a class="text-[#005B94] underline text-sm" href="index.php?accion=atencion&id=<?= (int) $hc['id'] ?>">Corregir atención</a><?php endif; ?></div>
                    <dl class="text-sm grid sm:grid-cols-2 gap-4">
                        <?php foreach (['motivo_consulta' => 'Motivo', 'diagnostico' => 'Diagnóstico', 'procedimiento_realizado' => 'Procedimiento', 'evolucion_lesion' => 'Evolución', 'prescripcion_recomendaciones' => 'Recomendaciones', 'tipo_pisada' => 'Tipo de pisada', 'evaluacion_marcha' => 'Marcha y apoyo', 'piel_unas' => 'Piel y uñas', 'exploracion_vascular' => 'Valoración vascular', 'exploracion_neurologica' => 'Valoración neurológica', 'proxima_visita_sugerida' => 'Próxima revisión'] as $campo => $nombre): ?>
                            <?php if (!empty($hc[$campo])): ?><div><dt class="font-bold text-slate-600"><?= e($nombre) ?></dt><dd class="mt-1 whitespace-pre-wrap break-words"><?= e($hc[$campo]) ?></dd></div><?php endif; ?>
                        <?php endforeach; ?>
                        <div><dt class="font-bold text-slate-600">Escala de dolor</dt><dd><?= (int) ($hc['dolor_escala'] ?? 0) ?>/10</dd></div>
                        <?php if (tieneRol(['superadmin', 'admin', 'especialista']) && !empty($hc['observaciones_privadas'])): ?><div><dt class="font-bold text-slate-600">Notas internas</dt><dd class="whitespace-pre-wrap break-words"><?= e($hc['observaciones_privadas']) ?></dd></div><?php endif; ?>
                    </dl>
                </article>
            <?php endforeach; ?>
        </section>

        <!-- TABLA DE HISTORIAL DE CAMBIOS AUDITADOS -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="bg-slate-50/80 px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                <div class="flex items-center space-x-2.5">
                    <div class="w-7 h-7 rounded-lg bg-[#005B94]/10 text-[#005B94] flex items-center justify-center font-bold text-xs">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-slate-800">Historial Inmutable de Auditoría de Cambios</h2>
                        <p class="text-[11px] text-slate-500">Trazabilidad de modificaciones de datos secundarios autorizadas</p>
                    </div>
                </div>
                <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-slate-200 text-slate-700">
                    <?= count($historialCambios) ?> registro(s)
                </span>
            </div>

            <?php if (empty($historialCambios)): ?>
                <div class="p-8 text-center text-slate-400 text-xs">
                    <i class="fa-solid fa-shield-check text-3xl text-emerald-500 mb-2"></i>
                    <p class="font-semibold text-slate-700">Sin modificaciones registradas</p>
                    <p class="mt-0.5">La ficha conserva íntegramente los datos asentados en su apertura original.</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50/60 border-b border-slate-200 text-[10px] font-bold uppercase tracking-wider text-slate-500">
                                <th class="py-3 px-4">Fecha / Hora</th>
                                <th class="py-3 px-4">Campo Modificado</th>
                                <th class="py-3 px-4">Valor Anterior</th>
                                <th class="py-3 px-4">Nuevo Valor</th>
                                <th class="py-3 px-4">Autorizado Por</th>
                                <th class="py-3 px-4">Motivo</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            <?php foreach ($historialCambios as $c): ?>
                                <tr class="hover:bg-slate-50/50 transition">
                                    <td class="py-3 px-4 font-mono text-[11px] text-slate-500 whitespace-nowrap">
                                        <?= e($c['fecha_hora']) ?>
                                    </td>
                                    <td class="py-3 px-4 font-bold text-[#005B94]">
                                        <?= e(str_replace('_', ' ', $c['campo_modificado'])) ?>
                                    </td>
                                    <td class="py-3 px-4 text-rose-700 bg-rose-50/40 font-mono text-[11px]">
                                        <?= e($c['valor_anterior'] ?: '(Vacío)') ?>
                                    </td>
                                    <td class="py-3 px-4 text-emerald-700 bg-emerald-50/40 font-mono text-[11px] font-bold">
                                        <?= e($c['valor_nuevo'] ?: '(Vacío)') ?>
                                    </td>
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        <div class="font-bold text-slate-800"><?= e($c['modificado_por_nombre']) ?></div>
                                        <div class="text-[10px] text-slate-400"><?= e($c['modificado_por_rol']) ?></div>
                                    </td>
                                    <td class="py-3 px-4 text-slate-500 italic max-w-xs truncate" title="<?= e($c['motivo_cambio'] ?? '') ?>">
                                        <?= e($c['motivo_cambio'] ?? 'Actualización clínica') ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

    </div>

</div>

<?php require_once dirname(__DIR__) . '/layout/footer.php'; ?>
