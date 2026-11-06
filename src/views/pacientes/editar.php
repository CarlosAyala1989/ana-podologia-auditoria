<?php
$tituloPagina = "Edición de Datos Secundarios - " . e($paciente['nombre']) . " " . e($paciente['apellido']);
require_once dirname(__DIR__) . '/layout/header.php';
?>

<!-- BANNER DE RESTRICCIÓN DE INTEGRIDAD LEGAL -->
<div class="mb-6 p-4 rounded-2xl bg-amber-50 border border-amber-300 text-amber-950 flex items-start space-x-3 shadow-sm">
    <i class="fa-solid fa-lock text-xl text-amber-700 mt-0.5"></i>
    <div>
        <h4 class="font-bold text-sm">Política de Inmutabilidad Legal (DNI y Nombres Protegidos)</h4>
        <p class="text-xs text-amber-900 mt-0.5">
            Por integridad jurídica del centro médico, el Número de Documento (DNI/Cédula) y los Nombres/Apellidos <strong>no pueden alterarse</strong>. 
            Solo está permitido actualizar datos secundarios (contacto, patologías, calzado) y cada cambio quedará sellado en la auditoría inmutable.
        </p>
    </div>
</div>

<?php if (!empty($mensajeError)): ?>
    <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-start space-x-3">
        <i class="fa-solid fa-circle-exclamation text-base text-rose-600 mt-0.5"></i>
        <div><?= $mensajeError ?></div>
    </div>
<?php endif; ?>

<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-black text-slate-900">Modificar Datos del Paciente</h1>
        <p class="text-xs text-slate-500 mt-0.5">Expediente #<?= $paciente['id'] ?> • Acción autorizada para Administración</p>
    </div>
    <a href="index.php?accion=detalle&id=<?= $paciente['id'] ?>" class="touch-target inline-flex items-center px-4 py-2 rounded-xl border border-slate-300 bg-white text-xs font-bold text-slate-700 hover:bg-slate-50 transition">
        <i class="fa-solid fa-arrow-left mr-2"></i> Cancelar
    </a>
</div>

<form action="index.php?accion=actualizar" method="POST" class="space-y-6">
    <input type="hidden" name="id" value="<?= $paciente['id'] ?>">

    <!-- BLOQUE 1: IDENTIFICACIÓN (BLOQUEADO) -->
    <div class="bg-slate-100/80 rounded-2xl border border-slate-200 p-6 space-y-4">
        <div class="flex items-center space-x-2 text-xs font-bold text-slate-500 uppercase tracking-wider">
            <i class="fa-solid fa-lock text-slate-400"></i>
            <span>Datos Filiatorios Sellados (Solo Lectura)</span>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 opacity-75">
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Documento Identidad</label>
                <input type="text" disabled value="<?= e($paciente['documento_identidad']) ?>" class="w-full bg-slate-200 border border-slate-300 rounded-xl px-3.5 py-2 text-slate-600 text-xs font-mono font-bold cursor-not-allowed">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Nombres</label>
                <input type="text" disabled value="<?= e($paciente['nombre']) ?>" class="w-full bg-slate-200 border border-slate-300 rounded-xl px-3.5 py-2 text-slate-600 text-xs font-bold cursor-not-allowed">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Apellidos</label>
                <input type="text" disabled value="<?= e($paciente['apellido']) ?>" class="w-full bg-slate-200 border border-slate-300 rounded-xl px-3.5 py-2 text-slate-600 text-xs font-bold cursor-not-allowed">
            </div>
        </div>
    </div>

    <!-- BLOQUE 2: DATOS SECUNDARIOS EDITABLES -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-5 shadow-sm">
        <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider text-[#005B94]">
            Datos de Contacto y Domicilio (Editables)
        </h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <div>
                <label for="telefono" class="block text-xs font-bold text-slate-600 mb-1">Teléfono Principal *</label>
                <input type="tel" name="telefono" id="telefono" required value="<?= e($paciente['telefono']) ?>" class="touch-target w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-800 focus:border-[#005B94] focus:ring-2 focus:ring-[#005B94]/20 transition">
            </div>
            <div>
                <label for="telefono_emergencia" class="block text-xs font-bold text-slate-600 mb-1">Teléfono de Emergencia</label>
                <input type="tel" name="telefono_emergencia" id="telefono_emergencia" value="<?= e($paciente['telefono_emergencia'] ?? '') ?>" class="touch-target w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-800 focus:border-[#005B94] focus:ring-2 focus:ring-[#005B94]/20 transition">
            </div>
            <div>
                <label for="contacto_emergencia" class="block text-xs font-bold text-slate-600 mb-1">Contacto Emergencia (Parentesco)</label>
                <input type="text" name="contacto_emergencia" id="contacto_emergencia" value="<?= e($paciente['contacto_emergencia'] ?? '') ?>" class="touch-target w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-800 focus:border-[#005B94] focus:ring-2 focus:ring-[#005B94]/20 transition">
            </div>
            <div>
                <label for="email" class="block text-xs font-bold text-slate-600 mb-1">Correo Electrónico</label>
                <input type="email" name="email" id="email" value="<?= e($paciente['email'] ?? '') ?>" class="touch-target w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-800 focus:border-[#005B94] focus:ring-2 focus:ring-[#005B94]/20 transition">
            </div>
            <div class="sm:col-span-2">
                <label for="direccion" class="block text-xs font-bold text-slate-600 mb-1">Dirección Domiciliaria</label>
                <input type="text" name="direccion" id="direccion" value="<?= e($paciente['direccion'] ?? '') ?>" class="touch-target w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-800 focus:border-[#005B94] focus:ring-2 focus:ring-[#005B94]/20 transition">
            </div>
        </div>
    </div>

    <!-- BLOQUE 3: ANTECEDENTES Y CALZADO -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-5 shadow-sm">
        <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider text-[#005B94]">
            Patologías, Calzado y Biomecánica
        </h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            <label class="flex items-center p-3 rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100 transition cursor-pointer">
                <input type="checkbox" name="es_diabetico" value="1" <?= !empty($paciente['es_diabetico']) ? 'checked' : '' ?> class="w-5 h-5 text-rose-600 rounded border-slate-300">
                <span class="ml-2.5 text-xs font-bold text-slate-800">Diabetes Mellitus</span>
            </label>
            <label class="flex items-center p-3 rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100 transition cursor-pointer">
                <input type="checkbox" name="usa_anticoagulantes" value="1" <?= !empty($paciente['usa_anticoagulantes']) ? 'checked' : '' ?> class="w-5 h-5 text-amber-600 rounded border-slate-300">
                <span class="ml-2.5 text-xs font-bold text-slate-800">Anticoagulantes</span>
            </label>
            <label class="flex items-center p-3 rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100 transition cursor-pointer">
                <input type="checkbox" name="problemas_circulatorios" value="1" <?= !empty($paciente['problemas_circulatorios']) ? 'checked' : '' ?> class="w-5 h-5 text-cyan-600 rounded border-slate-300">
                <span class="ml-2.5 text-xs font-bold text-slate-800">Problemas Circulatorios</span>
            </label>
            <label class="flex items-center p-3 rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100 transition cursor-pointer">
                <input type="checkbox" name="tiene_hipertension" value="1" <?= !empty($paciente['tiene_hipertension']) ? 'checked' : '' ?> class="w-5 h-5 text-clinic-600 rounded border-slate-300">
                <span class="ml-2.5 text-xs font-bold text-slate-800">Hipertensión Arterial</span>
            </label>
            <label class="flex items-center p-3 rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100 transition cursor-pointer">
                <input type="checkbox" name="usa_plantillas" value="1" <?= !empty($paciente['usa_plantillas']) ? 'checked' : '' ?> class="w-5 h-5 text-clinic-600 rounded border-slate-300">
                <span class="ml-2.5 text-xs font-bold text-slate-800">Usa Plantillas Previas</span>
            </label>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
            <div>
                <label for="tipo_calzado_habitual" class="block text-xs font-bold text-slate-600 mb-1">Tipo de Calzado Habitual</label>
                <input type="text" name="tipo_calzado_habitual" id="tipo_calzado_habitual" value="<?= e($paciente['tipo_calzado_habitual'] ?? '') ?>" class="touch-target w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-800 focus:border-[#005B94] focus:ring-2 focus:ring-[#005B94]/20 transition">
            </div>
            <div>
                <label for="talla_calzado" class="block text-xs font-bold text-slate-600 mb-1">Talla de Calzado (EUR)</label>
                <input type="text" name="talla_calzado" id="talla_calzado" value="<?= e($paciente['talla_calzado'] ?? '') ?>" class="touch-target w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-xs text-slate-800 focus:border-[#005B94] focus:ring-2 focus:ring-[#005B94]/20 transition">
            </div>
        </div>
    </div>

    <!-- BLOQUE 4: JUSTIFICACIÓN OBLIGATORIA DEL CAMBIO (AUDITORÍA) -->
    <div class="bg-amber-50/60 rounded-2xl border border-amber-200 p-6 space-y-2">
        <label for="motivo_cambio" class="block text-xs font-bold uppercase tracking-wider text-amber-950">
            Justificación Administrativa del Cambio (Audit Log) *
        </label>
        <p class="text-[11px] text-amber-900">
            Explique el motivo por el cual se actualizan estos datos (ej: <em>"Paciente solicitó actualización de número telefónico por cambio de operador"</em>).
        </p>
        <input type="text" name="motivo_cambio" id="motivo_cambio" required placeholder="Ingrese el motivo de la modificación..."
               class="touch-target w-full rounded-xl border border-amber-300 bg-white px-3.5 py-2.5 text-xs text-slate-800 focus:border-[#005B94] focus:ring-2 focus:ring-[#005B94]/20 transition">
    </div>

    <!-- BOTONES DE ACCIÓN -->
    <div class="flex items-center justify-end space-x-3 pt-4">
        <a href="index.php?accion=detalle&id=<?= $paciente['id'] ?>" class="touch-target px-6 py-3 rounded-xl border border-slate-300 bg-white text-xs font-bold text-slate-700 hover:bg-slate-50 transition">
            Cancelar
        </a>
        <button type="submit" class="touch-target px-6 py-3 rounded-xl bg-[#005B94] hover:bg-[#0A2540] text-white text-xs font-bold shadow-md hover:shadow-lg transition flex items-center space-x-2">
            <i class="fa-solid fa-floppy-disk text-[#2EC4B6]"></i>
            <span>Guardar Cambios y Registrar Auditoría</span>
        </button>
    </div>
</form>

<?php require_once dirname(__DIR__) . '/layout/footer.php'; ?>
