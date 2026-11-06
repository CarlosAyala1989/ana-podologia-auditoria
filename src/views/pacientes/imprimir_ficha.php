<?php
// Vista de Impresión Oficial y Archivo PDF: Ficha Clínica del Paciente
// Podología Ana Ramírez
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expediente_Clinico_<?= e($paciente['documento_identidad']) ?>_<?= e($paciente['apellido']) ?></title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: white !important;
                color: black !important;
                font-size: 11pt;
            }
            .page-container {
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
                max-width: 100% !important;
            }
            @page {
                size: A4 portrait;
                margin: 12mm 15mm 15mm 15mm;
            }
        }
        .page-break-avoid {
            page-break-inside: avoid;
        }
    </style>
</head>
<body class="bg-slate-100 font-sans text-slate-800 antialiased p-4 sm:p-8">

    <!-- BARRA FLOTANTE DE ACCIONES (NO SE IMPRIME) -->
    <div class="no-print max-w-4xl mx-auto mb-6 bg-white p-4 rounded-2xl border border-slate-200 shadow-lg flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 rounded-xl bg-[#005B94] text-white flex items-center justify-center text-lg">
                <i class="fa-solid fa-print"></i>
            </div>
            <div>
                <h3 class="text-sm font-bold text-slate-900">Vista de Impresión & Exportación PDF</h3>
                <p class="text-xs text-slate-500">Formato estandarizado para archivo físico o entrega formal al paciente.</p>
            </div>
        </div>
        <div class="flex items-center space-x-2">
            <a href="index.php?accion=detalle&id=<?= $paciente['id'] ?>" class="px-4 py-2 text-xs font-bold rounded-xl border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 transition">
                <i class="fa-solid fa-arrow-left mr-1.5"></i> Volver a la Ficha
            </a>
            <button onclick="window.print()" class="px-5 py-2 text-xs font-bold rounded-xl bg-[#005B94] hover:bg-[#0A2540] text-white shadow-md transition flex items-center">
                <i class="fa-solid fa-file-pdf mr-2 text-[#2EC4B6]"></i> Imprimir / Guardar en PDF
            </button>
        </div>
    </div>

    <!-- DOCUMENTO MÉDICO FORMAL (HOJA A4) -->
    <div class="page-container max-w-4xl mx-auto bg-white p-8 sm:p-12 rounded-3xl border border-slate-200 shadow-xl space-y-6">
        
        <!-- ENCABEZADO CLÍNICO CON EMBLEMA -->
        <div class="border-b-2 border-[#005B94] pb-6 flex items-start justify-between">
            <div class="flex items-center space-x-4">
                <div class="w-20 h-20 rounded-full border-2 border-[#2EC4B6] p-0.5 bg-[#0A2540] flex items-center justify-center shadow-md">
                    <img src="assets/img/logo-badge.png" alt="Logo" class="w-full h-full object-contain">
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-black text-[#0A2540] tracking-tight">PODOLOGÍA ANA RAMÍREZ</h1>
                    <p class="text-xs font-bold text-[#005B94] uppercase tracking-wider">Centro Especializado de Salud Podológica & Biomecánica</p>
                    <p class="text-[11px] text-slate-500 mt-1">
                        Dra. Ana Ramírez • Registro Profesional: <span class="font-mono font-bold">POD-09823</span><br>
                        Atención Clínica Integral, Quiropodia Avanzada y Pie Diabético
                    </p>
                </div>
            </div>

            <div class="text-right">
                <div class="inline-block bg-slate-100 border border-slate-200 px-3 py-1.5 rounded-xl text-center">
                    <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">EXPEDIENTE N°</div>
                    <div class="text-lg font-black font-mono text-[#005B94]">#<?= str_pad((string)$paciente['id'], 6, '0', STR_PAD_LEFT) ?></div>
                </div>
                <div class="text-[10px] text-slate-400 mt-2 font-mono">
                    Emisión: <?= date('d/m/Y H:i') ?>
                </div>
            </div>
        </div>

        <!-- 1. DATOS FILIATORIOS DEL PACIENTE -->
        <div class="space-y-3">
            <h2 class="text-xs font-black uppercase tracking-wider text-white bg-[#0A2540] px-3 py-1.5 rounded-md flex items-center justify-between">
                <span>1. DATOS FILIATORIOS Y DE IDENTIFICACIÓN</span>
                <span class="text-[10px] font-normal text-slate-300">Confidencial • Uso Médico</span>
            </h2>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs bg-slate-50 p-4 rounded-xl border border-slate-200">
                <div class="col-span-2">
                    <span class="block text-[10px] font-bold text-slate-500 uppercase">Apellidos y Nombres</span>
                    <span class="font-bold text-sm text-slate-900"><?= e($paciente['apellido']) ?>, <?= e($paciente['nombre']) ?></span>
                </div>
                <div>
                    <span class="block text-[10px] font-bold text-slate-500 uppercase">Documento (<?= e($paciente['tipo_documento']) ?>)</span>
                    <span class="font-mono font-bold text-slate-900"><?= e($paciente['documento_identidad']) ?></span>
                </div>
                <div>
                    <span class="block text-[10px] font-bold text-slate-500 uppercase">Género</span>
                    <span class="font-medium text-slate-800"><?= e($paciente['genero']) ?></span>
                </div>
                <div>
                    <span class="block text-[10px] font-bold text-slate-500 uppercase">Fecha Nacimiento</span>
                    <span class="font-mono text-slate-800"><?= e($paciente['fecha_nacimiento']) ?></span>
                </div>
                <div>
                    <span class="block text-[10px] font-bold text-slate-500 uppercase">Teléfono Móvil</span>
                    <span class="font-mono font-bold text-slate-900"><?= e($paciente['telefono']) ?></span>
                </div>
                <div class="col-span-2">
                    <span class="block text-[10px] font-bold text-slate-500 uppercase">Contacto de Emergencia</span>
                    <span class="text-slate-800"><?= e($paciente['contacto_emergencia'] ?: 'No registrado') ?> <?= !empty($paciente['telefono_emergencia']) ? '• ' . e($paciente['telefono_emergencia']) : '' ?></span>
                </div>
                <div class="col-span-2 sm:col-span-4 border-t border-slate-200 pt-2">
                    <span class="block text-[10px] font-bold text-slate-500 uppercase">Domicilio Habitual</span>
                    <span class="text-slate-700"><?= e($paciente['direccion'] ?: 'No especificado') ?></span>
                </div>
            </div>
        </div>

        <!-- 2. ANTECEDENTES PATOLÓGICOS Y ALERTAS DE RIESGO -->
        <div class="space-y-3">
            <h2 class="text-xs font-black uppercase tracking-wider text-white bg-[#005B94] px-3 py-1.5 rounded-md">
                2. ANTECEDENTES MÉDICOS & VALORACIÓN DE RIESGO PODOLÓGICO
            </h2>

            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 text-xs">
                
                <div class="p-2.5 rounded-lg border <?= !empty($paciente['es_diabetico']) ? 'bg-rose-50 border-rose-300 text-rose-900 font-bold' : 'bg-slate-50 border-slate-200 text-slate-500' ?>">
                    <span class="block text-[10px] uppercase">Diabetes Mellitus</span>
                    <span><?= !empty($paciente['es_diabetico']) ? 'SÍ • ' . e($paciente['tipo_diabetes'] ?: 'Alto Riesgo') : 'No refiere' ?></span>
                </div>

                <div class="p-2.5 rounded-lg border <?= !empty($paciente['usa_anticoagulantes']) ? 'bg-amber-50 border-amber-300 text-amber-900 font-bold' : 'bg-slate-50 border-slate-200 text-slate-500' ?>">
                    <span class="block text-[10px] uppercase">Terapia Anticoagulante</span>
                    <span><?= !empty($paciente['usa_anticoagulantes']) ? 'SÍ • Alerta Sangrado' : 'No refiere' ?></span>
                </div>

                <div class="p-2.5 rounded-lg border <?= !empty($paciente['problemas_circulatorios']) ? 'bg-cyan-50 border-cyan-300 text-cyan-900 font-bold' : 'bg-slate-50 border-slate-200 text-slate-500' ?>">
                    <span class="block text-[10px] uppercase">Insuficiencia Circulatoria</span>
                    <span><?= !empty($paciente['problemas_circulatorios']) ? 'SÍ (Patología vascular)' : 'No refiere' ?></span>
                </div>

                <div class="p-2.5 rounded-lg border <?= !empty($paciente['tiene_hipertension']) ? 'bg-blue-50 border-blue-300 text-blue-900 font-bold' : 'bg-slate-50 border-slate-200 text-slate-500' ?>">
                    <span class="block text-[10px] uppercase">Hipertensión Arterial</span>
                    <span><?= !empty($paciente['tiene_hipertension']) ? 'SÍ (Bajo control)' : 'No refiere' ?></span>
                </div>

                <div class="p-2.5 rounded-lg border <?= !empty($paciente['tiene_marcapasos']) ? 'bg-purple-50 border-purple-300 text-purple-900 font-bold' : 'bg-slate-50 border-slate-200 text-slate-500' ?>">
                    <span class="block text-[10px] uppercase">Marcapasos / Implante</span>
                    <span><?= !empty($paciente['tiene_marcapasos']) ? 'SÍ (Precaución electrocirugía)' : 'No' ?></span>
                </div>

                <div class="p-2.5 rounded-lg border <?= !empty($paciente['enfermedades_renales']) ? 'bg-orange-50 border-orange-300 text-orange-900 font-bold' : 'bg-slate-50 border-slate-200 text-slate-500' ?>">
                    <span class="block text-[10px] uppercase">Enfermedad Renal</span>
                    <span><?= !empty($paciente['enfermedades_renales']) ? 'SÍ (Seguimiento)' : 'No' ?></span>
                </div>
            </div>

            <!-- Alergias y Biomecánica -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs bg-slate-50 p-3.5 rounded-xl border border-slate-200">
                <div>
                    <span class="font-bold text-[#005B94]">Alergias Medicamentosas:</span>
                    <span class="text-slate-800"><?= e($paciente['alergias_medicamentos'] ?: 'Ninguna conocida') ?></span>
                </div>
                <div>
                    <span class="font-bold text-[#005B94]">Alergia de Contacto / Látex:</span>
                    <span class="text-slate-800"><?= e($paciente['alergias_contacto'] ?: 'Ninguna conocida') ?></span>
                </div>
                <div>
                    <span class="font-bold text-[#005B94]">Calzado Habitual:</span>
                    <span class="text-slate-800"><?= e($paciente['tipo_calzado_habitual'] ?: 'No especificado') ?> (Talla EUR: <?= e($paciente['talla_calzado'] ?: '-') ?>)</span>
                </div>
                <div>
                    <span class="font-bold text-[#005B94]">Soportes Plantares (Plantillas):</span>
                    <span class="text-slate-800"><?= !empty($paciente['usa_plantillas']) ? 'Sí (Uso previo)' : 'No utiliza' ?></span>
                </div>
            </div>
        </div>

        <!-- 3. HISTORIAL DE ATENCIONES / PROCEDIMIENTOS -->
        <div class="space-y-3 page-break-avoid">
            <h2 class="text-xs font-black uppercase tracking-wider text-white bg-[#0A2540] px-3 py-1.5 rounded-md">
                3. EVOLUCIÓN CLÍNICA Y PROCEDIMIENTOS REALIZADOS
            </h2>

            <?php if (empty($historiasClinicas)): ?>
                <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl text-center text-xs text-slate-400 italic">
                    Expediente inicial. Sin procedimientos previos registrados en este centro.
                </div>
            <?php else: ?>
                <div class="overflow-hidden border border-slate-200 rounded-xl">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="bg-slate-100 text-[10px] font-bold uppercase text-slate-600 border-b border-slate-200">
                            <tr>
                                <th class="p-2.5">Fecha</th>
                                <th class="p-2.5">Diagnóstico Podológico</th>
                                <th class="p-2.5">Procedimiento Aplicado</th>
                                <th class="p-2.5">Facultativo</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach ($historiasClinicas as $hc): ?>
                                <tr>
                                    <td class="p-2.5 font-mono whitespace-nowrap text-slate-500 font-bold"><?= substr($hc['fecha_atencion'], 0, 10) ?></td>
                                    <td class="p-2.5 font-bold text-slate-900"><?= e($hc['diagnostico']) ?></td>
                                    <td class="p-2.5 text-slate-700"><?= e($hc['procedimiento_realizado']) ?></td>
                                    <td class="p-2.5 text-slate-600 whitespace-nowrap"><?= e($hc['especialista_nombre']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- 4. FIRMAS Y SELLOS LEGALES -->
        <div class="pt-8 border-t border-slate-200 page-break-avoid">
            <div class="grid grid-cols-2 gap-12 text-center text-xs">
                <div>
                    <div class="border-b border-slate-400 h-16 mb-2"></div>
                    <span class="font-bold text-slate-900 block">Dra. Ana Ramírez</span>
                    <span class="text-[10px] text-slate-500 block">Podóloga Especialista • Matrícula POD-09823</span>
                    <span class="text-[9px] text-slate-400">Firma y Sello Facultativo</span>
                </div>

                <div>
                    <div class="border-b border-slate-400 h-16 mb-2"></div>
                    <span class="font-bold text-slate-900 block"><?= e($paciente['nombre']) ?> <?= e($paciente['apellido']) ?></span>
                    <span class="text-[10px] text-slate-500 block">DNI: <?= e($paciente['documento_identidad']) ?></span>
                    <span class="text-[9px] text-slate-400">Firma del Paciente / Consentimiento Informado</span>
                </div>
            </div>

            <!-- PIE LEGAL -->
            <div class="mt-8 text-center text-[9px] text-slate-400 border-t border-slate-100 pt-3">
                Documento clínico oficial confidencial amparado por el secreto médico profesional. Prohibida su reproducción no autorizada.<br>
                Centro de Podología Ana Ramírez • Documento emitido mediante sistema automatizado de gestión clínica.
            </div>
        </div>

    </div>

</body>
</html>
