<?php
$tituloPagina = "Registro de Ficha Clínica";
require_once dirname(__DIR__) . '/layout/header.php';
?>

<!-- BANNER DE CONEXIÓN O ALERTAS DEL SISTEMA -->
<?php if (!empty($mensajeExito)): ?>
    <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-start space-x-3 shadow-sm animate-fade-in">
        <i class="fa-solid fa-circle-check text-xl text-emerald-600 mt-0.5"></i>
        <div>
            <h4 class="font-bold text-sm">Operación Exitosa</h4>
            <div class="text-sm"><?= $mensajeExito ?></div>
        </div>
    </div>
<?php endif; ?>

<?php if (!empty($mensajeError)): ?>
    <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 flex items-start space-x-3 shadow-sm">
        <i class="fa-solid fa-triangle-exclamation text-xl text-rose-600 mt-0.5"></i>
        <div>
            <h4 class="font-bold text-sm">Atención requerida</h4>
            <div class="text-sm"><?= $mensajeError ?></div>
        </div>
    </div>
<?php endif; ?>

<?php if (!$dbDisponible): ?>
    <div class="mb-6 p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 flex items-start space-x-3 shadow-sm">
        <i class="fa-solid fa-database text-xl text-amber-600 mt-0.5"></i>
        <div>
            <h4 class="font-bold text-sm">Modo de Demostración Local (Base de Datos Desconectada)</h4>
            <p class="text-xs text-amber-800 mt-0.5">
                El sistema se encuentra en modo visualización interactiva. Para persistir datos reales, ejecute el script 
                <code class="px-1.5 py-0.5 bg-amber-100 rounded text-amber-900 font-mono">sql/schema.sql</code> en su servidor MySQL/MariaDB.
            </p>
        </div>
    </div>
<?php endif; ?>

<!-- ALERTA DINÁMICA DE RIESGO CLÍNICO (ACTUALIZADA POR JAVASCRIPT) -->
<div id="alertaRiesgo" class="hidden mb-6 p-4 rounded-xl bg-amber-100 border-2 border-amber-400 text-amber-950 flex items-center space-x-3 shadow-md animate-pulse">
    <i class="fa-solid fa-heart-pulse text-2xl text-amber-700"></i>
    <div>
        <p class="font-bold text-sm sm:text-base">ALERTA CLÍNICA: Paciente con Factores de Alto Riesgo Podológico</p>
        <p class="text-xs text-amber-900 mt-0.5" id="alertaRiesgoTexto">
            Se han seleccionado antecedentes que requieren protocolo preventivo de quiropodia conservadora y antisepsia reforzada.
        </p>
    </div>
</div>

<!-- ENCABEZADO DE VISTA -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">
            Ficha Clínica del Paciente
        </h1>
        <p class="text-sm text-slate-500 mt-1">
            Registro inicial, antecedentes patológicos y valoración biomecánica.
        </p>
    </div>
    <div class="flex items-center space-x-2">
        <a href="index.php?accion=listado" class="touch-target inline-flex items-center justify-center px-4 py-2 text-sm font-semibold text-slate-700 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 shadow-sm transition">
            <i class="fa-solid fa-arrow-left mr-2 text-slate-500"></i> Ver Directorio
        </a>
    </div>
</div>

<!-- FORMULARIO TÁCTIL PRINCIPAL -->
<form action="index.php?accion=guardar" method="POST" id="formRegistroPaciente" class="space-y-6">

    <!-- SECCIÓN 1: DATOS PERSONALES Y FILIACIÓN -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="bg-slate-50/80 px-6 py-4 border-b border-slate-200 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-clinic-100 text-clinic-700 font-bold text-sm">1</span>
                <h2 class="text-base sm:text-lg font-bold text-slate-800">Datos Personales y Filiación</h2>
            </div>
            <span class="text-xs font-semibold text-clinic-700 bg-clinic-50 px-2.5 py-1 rounded-full border border-clinic-200">Requerido</span>
        </div>

        <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            
            <!-- Tipo y Número de Documento -->
            <div>
                <label for="tipo_documento" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                    Tipo de Documento *
                </label>
                <select name="tipo_documento" id="tipo_documento" class="touch-target w-full rounded-xl border border-slate-300 bg-slate-50 px-3.5 py-2.5 text-slate-800 focus:bg-white focus:border-clinic-600 focus:ring-2 focus:ring-clinic-500/20 transition">
                    <option value="Cedula">Cédula de Identidad</option>
                    <option value="DNI">DNI</option>
                    <option value="Pasaporte">Pasaporte</option>
                    <option value="Otro">Otro</option>
                </select>
            </div>

            <div>
                <label for="documento_identidad" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                    Número de Documento *
                </label>
                <input type="text" name="documento_identidad" id="documento_identidad" required placeholder="Ej: 1712345678"
                       value="<?= e($datosPrevios['documento_identidad'] ?? '') ?>"
                       class="touch-target w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-slate-800 placeholder-slate-400 focus:border-clinic-600 focus:ring-2 focus:ring-clinic-500/20 transition">
            </div>

            <!-- Nombres y Apellidos -->
            <div>
                <label for="nombre" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                    Nombres del Paciente *
                </label>
                <input type="text" name="nombre" id="nombre" required placeholder="Ej: Roberto Carlos"
                       value="<?= e($datosPrevios['nombre'] ?? '') ?>"
                       class="touch-target w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-slate-800 placeholder-slate-400 focus:border-clinic-600 focus:ring-2 focus:ring-clinic-500/20 transition">
            </div>

            <div>
                <label for="apellido" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                    Apellidos del Paciente *
                </label>
                <input type="text" name="apellido" id="apellido" required placeholder="Ej: Villacís Mendoza"
                       value="<?= e($datosPrevios['apellido'] ?? '') ?>"
                       class="touch-target w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-slate-800 placeholder-slate-400 focus:border-clinic-600 focus:ring-2 focus:ring-clinic-500/20 transition">
            </div>

            <!-- Fecha de Nacimiento y Género -->
            <div>
                <label for="fecha_nacimiento" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                    Fecha de Nacimiento *
                </label>
                <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" required
                       value="<?= e($datosPrevios['fecha_nacimiento'] ?? '') ?>"
                       class="touch-target w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-slate-800 focus:border-clinic-600 focus:ring-2 focus:ring-clinic-500/20 transition">
            </div>

            <div>
                <label for="genero" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                    Género *
                </label>
                <select name="genero" id="genero" class="touch-target w-full rounded-xl border border-slate-300 bg-slate-50 px-3.5 py-2.5 text-slate-800 focus:bg-white focus:border-clinic-600 focus:ring-2 focus:ring-clinic-500/20 transition">
                    <option value="Femenino">Femenino</option>
                    <option value="Masculino">Masculino</option>
                    <option value="Otro">Otro</option>
                </select>
            </div>

            <!-- Teléfono y Correo Electrónico -->
            <div>
                <label for="telefono" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                    Teléfono Celular / WhatsApp *
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-phone"></i>
                    </span>
                    <input type="tel" name="telefono" id="telefono" required placeholder="Ej: 0998765432"
                           value="<?= e($datosPrevios['telefono'] ?? '') ?>"
                           class="touch-target w-full pl-10 rounded-xl border border-slate-300 px-3.5 py-2.5 text-slate-800 placeholder-slate-400 focus:border-clinic-600 focus:ring-2 focus:ring-clinic-500/20 transition">
                </div>
            </div>

            <div>
                <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                    Correo Electrónico
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-regular fa-envelope"></i>
                    </span>
                    <input type="email" name="email" id="email" placeholder="paciente@correo.com"
                           value="<?= e($datosPrevios['email'] ?? '') ?>"
                           class="touch-target w-full pl-10 rounded-xl border border-slate-300 px-3.5 py-2.5 text-slate-800 placeholder-slate-400 focus:border-clinic-600 focus:ring-2 focus:ring-clinic-500/20 transition">
                </div>
            </div>

            <!-- Ocupación habitual -->
            <div>
                <label for="ocupacion" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                    Ocupación / Actividad Diaria
                </label>
                <input type="text" name="ocupacion" id="ocupacion" placeholder="Ej: Docente, Oficinista, Construcción"
                       value="<?= e($datosPrevios['ocupacion'] ?? '') ?>"
                       class="touch-target w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-slate-800 placeholder-slate-400 focus:border-clinic-600 focus:ring-2 focus:ring-clinic-500/20 transition">
            </div>

            <!-- Contacto de Emergencia -->
            <div class="md:col-span-2 lg:col-span-1">
                <label for="contacto_emergencia" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                    Contacto de Emergencia (Nombre y Parentesco)
                </label>
                <input type="text" name="contacto_emergencia" id="contacto_emergencia" placeholder="Ej: Carmen Gómez (Esposa)"
                       value="<?= e($datosPrevios['contacto_emergencia'] ?? '') ?>"
                       class="touch-target w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-slate-800 placeholder-slate-400 focus:border-clinic-600 focus:ring-2 focus:ring-clinic-500/20 transition">
            </div>

            <div>
                <label for="telefono_emergencia" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                    Teléfono de Emergencia
                </label>
                <input type="tel" name="telefono_emergencia" id="telefono_emergencia" placeholder="Ej: 0981122334"
                       value="<?= e($datosPrevios['telefono_emergencia'] ?? '') ?>"
                       class="touch-target w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-slate-800 placeholder-slate-400 focus:border-clinic-600 focus:ring-2 focus:ring-clinic-500/20 transition">
            </div>

            <!-- Dirección Domiciliaria -->
            <div class="md:col-span-2 lg:col-span-1">
                <label for="direccion" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                    Dirección Domiciliaria
                </label>
                <input type="text" name="direccion" id="direccion" placeholder="Calle principal, número y sector"
                       value="<?= e($datosPrevios['direccion'] ?? '') ?>"
                       class="touch-target w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-slate-800 placeholder-slate-400 focus:border-clinic-600 focus:ring-2 focus:ring-clinic-500/20 transition">
            </div>

        </div>
    </div>

    <!-- SECCIÓN 2: FACTORES DE RIESGO Y ANTECEDENTES PATOLÓGICOS (CRÍTICO EN PODOLOGÍA) -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="bg-slate-50/80 px-6 py-4 border-b border-slate-200 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-rose-100 text-rose-700 font-bold text-sm">2</span>
                <div>
                    <h2 class="text-base sm:text-lg font-bold text-slate-800">Factores de Riesgo y Antecedentes Patológicos</h2>
                    <p class="text-xs text-slate-500">Toque sobre las tarjetas para activar o desactivar condiciones de salud</p>
                </div>
            </div>
            <i class="fa-solid fa-stethoscope text-slate-400 text-xl hidden sm:block"></i>
        </div>

        <div class="p-6 space-y-6">
            
            <!-- Grid de Tarjetas Táctiles de Patologías (Optimizado para Tablet) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                
                <!-- Tarjeta: Diabetes Mellitus -->
                <label class="cursor-pointer flex items-start p-4 rounded-2xl border-2 transition-all select-none hover:border-clinic-400 bg-white shadow-sm has-[:checked]:border-rose-500 has-[:checked]:bg-rose-50/40">
                    <input type="checkbox" name="es_diabetico" id="chk_diabetes" value="1" 
                           class="w-6 h-6 mt-0.5 text-rose-600 rounded-lg border-slate-300 focus:ring-rose-500"
                           onchange="actualizarAlertaRiesgo()">
                    <div class="ml-3.5 flex-1">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-bold text-slate-900">Diabetes Mellitus</span>
                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-rose-100 text-rose-700">Riesgo Alto</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-1">Neuropatía sensitiva / Pie Diabético.</p>
                    </div>
                </label>

                <!-- Tarjeta: Anticoagulantes -->
                <label class="cursor-pointer flex items-start p-4 rounded-2xl border-2 transition-all select-none hover:border-clinic-400 bg-white shadow-sm has-[:checked]:border-rose-500 has-[:checked]:bg-rose-50/40">
                    <input type="checkbox" name="usa_anticoagulantes" id="chk_anticoagulantes" value="1" 
                           class="w-6 h-6 mt-0.5 text-rose-600 rounded-lg border-slate-300 focus:ring-rose-500"
                           onchange="actualizarAlertaRiesgo()">
                    <div class="ml-3.5 flex-1">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-bold text-slate-900">Terapia Anticoagulante</span>
                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-rose-100 text-rose-700">Sangrado</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-1">Warfarina, Acenocumarol, Clopidogrel, AAS.</p>
                    </div>
                </label>

                <!-- Tarjeta: Problemas Circulatorios -->
                <label class="cursor-pointer flex items-start p-4 rounded-2xl border-2 transition-all select-none hover:border-clinic-400 bg-white shadow-sm has-[:checked]:border-clinic-500 has-[:checked]:bg-clinic-50/50">
                    <input type="checkbox" name="problemas_circulatorios" id="chk_circulatorios" value="1" 
                           class="w-6 h-6 mt-0.5 text-clinic-600 rounded-lg border-slate-300 focus:ring-clinic-500"
                           onchange="actualizarAlertaRiesgo()">
                    <div class="ml-3.5 flex-1">
                        <span class="text-sm font-bold text-slate-900">Mala Circulación</span>
                        <p class="text-xs text-slate-500 mt-1">Insuficiencia venosa, frialdad distal, várices.</p>
                    </div>
                </label>

                <!-- Tarjeta: Hipertensión Arterial -->
                <label class="cursor-pointer flex items-start p-4 rounded-2xl border-2 transition-all select-none hover:border-clinic-400 bg-white shadow-sm has-[:checked]:border-clinic-500 has-[:checked]:bg-clinic-50/50">
                    <input type="checkbox" name="tiene_hipertension" id="chk_hipertension" value="1" 
                           class="w-6 h-6 mt-0.5 text-clinic-600 rounded-lg border-slate-300 focus:ring-clinic-500">
                    <div class="ml-3.5 flex-1">
                        <span class="text-sm font-bold text-slate-900">Hipertensión Arterial</span>
                        <p class="text-xs text-slate-500 mt-1">Presión alta bajo tratamiento o sin control.</p>
                    </div>
                </label>

                <!-- Tarjeta: Marcapasos / Implantes -->
                <label class="cursor-pointer flex items-start p-4 rounded-2xl border-2 transition-all select-none hover:border-clinic-400 bg-white shadow-sm has-[:checked]:border-clinic-500 has-[:checked]:bg-clinic-50/50">
                    <input type="checkbox" name="tiene_marcapasos" id="chk_marcapasos" value="1" 
                           class="w-6 h-6 mt-0.5 text-clinic-600 rounded-lg border-slate-300 focus:ring-clinic-500">
                    <div class="ml-3.5 flex-1">
                        <span class="text-sm font-bold text-slate-900">Portador de Marcapasos</span>
                        <p class="text-xs text-slate-500 mt-1">Precaución con aparatología de electrocirugía.</p>
                    </div>
                </label>

                <!-- Tarjeta: Enfermedad Renal -->
                <label class="cursor-pointer flex items-start p-4 rounded-2xl border-2 transition-all select-none hover:border-clinic-400 bg-white shadow-sm has-[:checked]:border-clinic-500 has-[:checked]:bg-clinic-50/50">
                    <input type="checkbox" name="enfermedades_renales" id="chk_renal" value="1" 
                           class="w-6 h-6 mt-0.5 text-clinic-600 rounded-lg border-slate-300 focus:ring-clinic-500">
                    <div class="ml-3.5 flex-1">
                        <span class="text-sm font-bold text-slate-900">Enfermedad Renal</span>
                        <p class="text-xs text-slate-500 mt-1">Diálisis o insuficiencia renal crónica.</p>
                    </div>
                </label>

            </div>

            <!-- Campos descriptivos complementarios -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 pt-2">
                <div id="campoTipoDiabetes" class="hidden">
                    <label for="tipo_diabetes" class="block text-xs font-bold uppercase tracking-wider text-rose-700 mb-1.5">
                        Especificación de Diabetes
                    </label>
                    <input type="text" name="tipo_diabetes" id="tipo_diabetes" placeholder="Ej: Tipo 2 insulinodependiente / Metformina"
                           class="touch-target w-full rounded-xl border border-rose-300 bg-rose-50/20 px-3.5 py-2.5 text-slate-800 placeholder-slate-400 focus:border-rose-600 focus:ring-2 focus:ring-rose-500/20 transition">
                </div>

                <div class="md:col-span-2">
                    <label for="otras_patologias" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Otras Patologías Relevantes / Medicación Continua
                    </label>
                    <textarea name="otras_patologias" id="otras_patologias" rows="2" placeholder="Ej: Artritis reumatoide, psoriasis ungular, tiroidismo, corticoides..."
                              class="w-full rounded-xl border border-slate-300 p-3 text-slate-800 placeholder-slate-400 focus:border-clinic-600 focus:ring-2 focus:ring-clinic-500/20 transition"></textarea>
                </div>
            </div>

        </div>
    </div>

    <!-- SECCIÓN 3: ALERGIAS Y BIOMECÁNICA / CALZADO -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="bg-slate-50/80 px-6 py-4 border-b border-slate-200 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-teal-100 text-teal-700 font-bold text-sm">3</span>
                <h2 class="text-base sm:text-lg font-bold text-slate-800">Alergias, Biomecánica y Hábitos de Calzado</h2>
            </div>
            <i class="fa-solid fa-shoe-prints text-slate-400 text-xl hidden sm:block"></i>
        </div>

        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <!-- Bloque Alergias -->
            <div class="space-y-4">
                <h3 class="text-sm font-bold text-slate-800 flex items-center">
                    <i class="fa-solid fa-shield-virus text-amber-500 mr-2"></i> Reacciones Alérgicas Conocidas
                </h3>

                <div>
                    <label for="alergias_medicamentos" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Alergia a Medicamentos
                    </label>
                    <input type="text" name="alergias_medicamentos" id="alergias_medicamentos" placeholder="Ej: Penicilina, Ibuprofeno, Lidocaína..."
                           class="touch-target w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-slate-800 placeholder-slate-400 focus:border-clinic-600 focus:ring-2 focus:ring-clinic-500/20 transition">
                </div>

                <div>
                    <label for="alergias_contacto" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Alergia de Contacto o Antisépticos
                    </label>
                    <input type="text" name="alergias_contacto" id="alergias_contacto" placeholder="Ej: Látex, Adhesivo de esparadrapo, Yodo..."
                           class="touch-target w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-slate-800 placeholder-slate-400 focus:border-clinic-600 focus:ring-2 focus:ring-clinic-500/20 transition">
                </div>
            </div>

            <!-- Bloque Calzado y Soporte -->
            <div class="space-y-4">
                <h3 class="text-sm font-bold text-slate-800 flex items-center">
                    <i class="fa-solid fa-person-walking text-clinic-600 mr-2"></i> Evaluación de Calzado y Marcha
                </h3>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="talla_calzado" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                            Talla de Calzado (EUR)
                        </label>
                        <input type="text" name="talla_calzado" id="talla_calzado" placeholder="Ej: 38, 41.5"
                               class="touch-target w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-slate-800 placeholder-slate-400 focus:border-clinic-600 focus:ring-2 focus:ring-clinic-500/20 transition">
                    </div>

                    <div>
                        <label for="tipo_calzado_habitual" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                            Calzado Habitual
                        </label>
                        <select name="tipo_calzado_habitual" id="tipo_calzado_habitual" class="touch-target w-full rounded-xl border border-slate-300 bg-slate-50 px-3.5 py-2.5 text-slate-800 focus:bg-white focus:border-clinic-600 focus:ring-2 focus:ring-clinic-500/20 transition">
                            <option value="">Seleccione tipo...</option>
                            <option value="Deportivo">Calzado Deportivo</option>
                            <option value="Trabajo/Seguridad">Botas de Seguridad / Trabajo pesado</option>
                            <option value="Formal/Cuero">Formal de Cuero / Rígido</option>
                            <option value="Tacon">Calzado de Tacón / Punta estrecha</option>
                            <option value="Plano/Casual">Plano / Casual / Sandalias</option>
                            <option value="Ortopedico">Calzado Ancho / Ortopédico</option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center space-x-3 pt-2">
                    <label class="cursor-pointer flex items-center p-3 rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100 transition w-full has-[:checked]:border-clinic-500 has-[:checked]:bg-clinic-50/50">
                        <input type="checkbox" name="usa_plantillas" id="usa_plantillas" value="1" class="w-5 h-5 text-clinic-600 rounded border-slate-300">
                        <span class="ml-3 text-xs sm:text-sm font-semibold text-slate-800">¿Utiliza o ha utilizado soportes plantares (plantillas)?</span>
                    </label>
                </div>

                <div>
                    <label for="actividad_fisica" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Nivel de Actividad Física
                    </label>
                    <input type="text" name="actividad_fisica" id="actividad_fisica" placeholder="Ej: Sedentario / Camina 5 km / Corredor habitual"
                           class="touch-target w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-slate-800 placeholder-slate-400 focus:border-clinic-600 focus:ring-2 focus:ring-clinic-500/20 transition">
                </div>
            </div>

        </div>
    </div>

    <!-- BOTONES DE ACCIÓN PRINCIPALES (ERGO-TÁCTIL PARA TABLETS) -->
    <div class="pt-4 pb-8 flex flex-col-reverse sm:flex-row items-center justify-end gap-4">
        <button type="reset" class="touch-target w-full sm:w-auto px-6 py-3.5 rounded-xl border border-slate-300 bg-white text-slate-700 font-semibold text-base hover:bg-slate-50 transition shadow-sm">
            <i class="fa-solid fa-eraser mr-2 text-slate-400"></i> Limpiar Campos
        </button>

        <button type="submit" id="btnGuardar" class="touch-target w-full sm:w-auto px-8 py-3.5 rounded-xl bg-[#005B94] text-white font-bold text-base hover:bg-[#0A2540] active:scale-[0.98] transition shadow-md hover:shadow-lg flex items-center justify-center">
            <i class="fa-solid fa-floppy-disk mr-2.5 text-lg text-[#2EC4B6]"></i> Guardar Ficha Clínica
        </button>
    </div>

</form>

<script>
    // Dinamismo táctil e interactivo para detección de riesgo
    function actualizarAlertaRiesgo() {
        const esDiabetico = document.getElementById('chk_diabetes').checked;
        const usaAnticoagulantes = document.getElementById('chk_anticoagulantes').checked;
        const malaCirculacion = document.getElementById('chk_circulatorios').checked;
        const banner = document.getElementById('alertaRiesgo');
        const campoTipo = document.getElementById('campoTipoDiabetes');

        if (esDiabetico) {
            campoTipo.classList.remove('hidden');
        } else {
            campoTipo.classList.add('hidden');
        }

        if (esDiabetico || usaAnticoagulantes || malaCirculacion) {
            banner.classList.remove('hidden');
            let razones = [];
            if (esDiabetico) razones.push('Diabetes Mellitus (Pie de Riesgo)');
            if (usaAnticoagulantes) razones.push('Terapia Anticoagulante (Sangrado)');
            if (malaCirculacion) razones.push('Insuficiencia Vascular');
            document.getElementById('alertaRiesgoTexto').textContent = 'Factores detectados: ' + razones.join(' • ');
        } else {
            banner.classList.add('hidden');
        }
    }
</script>

<?php require_once dirname(__DIR__) . '/layout/footer.php'; ?>
