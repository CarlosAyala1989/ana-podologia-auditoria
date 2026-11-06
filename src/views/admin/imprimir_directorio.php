<?php
// Reporte Maestro Consolidado de Pacientes (Exclusivo SuperAdmin)
// Podología Ana Ramírez
requerirRol('superadmin');

$db = Database::pdo();
$sql = "SELECT p.*, 
               CONCAT(u.nombre, ' ', u.apellido) AS creado_por_nombre,
               r.nombre AS creado_por_rol
        FROM pacientes p
        LEFT JOIN usuarios u ON p.creado_por_usuario_id = u.id
        LEFT JOIN roles r ON u.rol_id = r.id
        ORDER BY p.apellido ASC, p.nombre ASC";
$pacientes = $db->query($sql)->fetchAll();

// Métricas de auditoría
$totalPacientes = count($pacientes);
$totalDiabeticos = count(array_filter($pacientes, fn($p) => !empty($p['es_diabetico'])));
$totalAnticoagulados = count(array_filter($pacientes, fn($p) => !empty($p['usa_anticoagulantes'])));
$totalCirculatorios = count(array_filter($pacientes, fn($p) => !empty($p['problemas_circulatorios'])));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte_Maestro_Pacientes_Podologia_Ana_Ramirez_<?= date('Ymd') ?></title>
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
                font-size: 9pt;
            }
            .page-container {
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
                max-width: 100% !important;
            }
            @page {
                size: A4 landscape;
                margin: 10mm;
            }
            tr {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body class="bg-slate-100 font-sans text-slate-800 antialiased p-4 sm:p-8">

    <!-- BARRA FLOTANTE DE ACCIONES -->
    <div class="no-print max-w-6xl mx-auto mb-6 bg-white p-4 rounded-2xl border border-slate-200 shadow-lg flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 rounded-xl bg-purple-900 text-purple-200 flex items-center justify-center text-lg">
                <i class="fa-solid fa-file-contract"></i>
            </div>
            <div>
                <h3 class="text-sm font-bold text-slate-900">Reporte Maestro de Pacientes (Auditoría Global)</h3>
                <p class="text-xs text-slate-500">Módulo exclusivo de SuperAdmin para exportación en PDF o archivado técnico.</p>
            </div>
        </div>
        <div class="flex items-center space-x-2">
            <a href="index.php?accion=sistema" class="px-4 py-2 text-xs font-bold rounded-xl border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 transition">
                <i class="fa-solid fa-arrow-left mr-1.5"></i> Volver al Panel Técnico
            </a>
            <button onclick="window.print()" class="px-5 py-2 text-xs font-bold rounded-xl bg-purple-800 hover:bg-purple-900 text-white shadow-md transition flex items-center">
                <i class="fa-solid fa-print mr-2 text-[#2EC4B6]"></i> Imprimir / Guardar Listado en PDF
            </button>
        </div>
    </div>

    <!-- DOCUMENTO EN FORMATO APAISADO (LANDSCAPE) -->
    <div class="page-container max-w-6xl mx-auto bg-white p-8 rounded-3xl border border-slate-200 shadow-xl space-y-5">
        
        <!-- ENCABEZADO -->
        <div class="border-b-2 border-purple-900 pb-4 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-14 h-14 rounded-full border-2 border-[#2EC4B6] p-0.5 bg-[#0A2540] flex items-center justify-center">
                    <img src="assets/img/logo-badge.png" alt="Logo" class="w-full h-full object-contain">
                </div>
                <div>
                    <h1 class="text-lg font-black text-slate-900 tracking-tight">PODOLOGÍA ANA RAMÍREZ</h1>
                    <p class="text-xs font-bold text-purple-800 uppercase tracking-wider">Reporte Maestro Consolidado de Pacientes & Trazabilidad Forense</p>
                </div>
            </div>

            <div class="text-right text-xs">
                <div class="font-mono font-bold text-slate-800">Fecha de Emisión: <?= date('d/m/Y H:i:s') ?></div>
                <div class="text-[10px] text-purple-700 font-bold">Generado por: SuperAdmin (Auditoría de Base de Datos)</div>
            </div>
        </div>

        <!-- TARJETAS DE MÉTRICAS -->
        <div class="grid grid-cols-4 gap-3 text-xs">
            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 text-center">
                <div class="text-[10px] font-bold uppercase text-slate-500">Total Expedientes</div>
                <div class="text-xl font-black text-slate-900 font-mono mt-0.5"><?= $totalPacientes ?></div>
            </div>
            <div class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-center">
                <div class="text-[10px] font-bold uppercase text-rose-700">Pacientes Diabéticos</div>
                <div class="text-xl font-black text-rose-900 font-mono mt-0.5"><?= $totalDiabeticos ?></div>
            </div>
            <div class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-center">
                <div class="text-[10px] font-bold uppercase text-amber-700">Anticoagulados</div>
                <div class="text-xl font-black text-amber-900 font-mono mt-0.5"><?= $totalAnticoagulados ?></div>
            </div>
            <div class="p-3 rounded-xl bg-cyan-50 border border-cyan-200 text-center">
                <div class="text-[10px] font-bold uppercase text-cyan-700">Problemas Vasculares</div>
                <div class="text-xl font-black text-cyan-900 font-mono mt-0.5"><?= $totalCirculatorios ?></div>
            </div>
        </div>

        <!-- TABLA MAESTRA CONSOLIDADA -->
        <div class="overflow-x-auto border border-slate-200 rounded-xl">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-slate-100 text-[9px] font-bold uppercase text-slate-600 border-b border-slate-200">
                    <tr>
                        <th class="p-2">#</th>
                        <th class="p-2">DNI / Documento</th>
                        <th class="p-2">Apellidos y Nombres</th>
                        <th class="p-2">Teléfono</th>
                        <th class="p-2">Género</th>
                        <th class="p-2">Factores de Riesgo</th>
                        <th class="p-2">Calzado Habitual</th>
                        <th class="p-2">Registrado Por</th>
                        <th class="p-2">Fecha Apertura</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-[11px] text-slate-700">
                    <?php foreach ($pacientes as $idx => $p): ?>
                        <tr class="hover:bg-slate-50">
                            <td class="p-2 font-mono text-slate-400"><?= $idx + 1 ?></td>
                            <td class="p-2 font-mono font-bold text-slate-900"><?= e($p['documento_identidad']) ?></td>
                            <td class="p-2 font-bold text-slate-900"><?= e($p['apellido']) ?>, <?= e($p['nombre']) ?></td>
                            <td class="p-2 font-mono text-slate-800"><?= e($p['telefono']) ?></td>
                            <td class="p-2"><?= e($p['genero']) ?></td>
                            <td class="p-2">
                                <div class="flex flex-wrap gap-1">
                                    <?php if (!empty($p['es_diabetico'])): ?>
                                        <span class="px-1.5 py-0.2 rounded bg-rose-100 text-rose-800 font-bold text-[9px]">Diabético</span>
                                    <?php endif; ?>
                                    <?php if (!empty($p['usa_anticoagulantes'])): ?>
                                        <span class="px-1.5 py-0.2 rounded bg-amber-100 text-amber-800 font-bold text-[9px]">Anticoag.</span>
                                    <?php endif; ?>
                                    <?php if (!empty($p['problemas_circulatorios'])): ?>
                                        <span class="px-1.5 py-0.2 rounded bg-cyan-100 text-cyan-800 text-[9px]">Circulación</span>
                                    <?php endif; ?>
                                    <?php if (empty($p['es_diabetico']) && empty($p['usa_anticoagulantes']) && empty($p['problemas_circulatorios'])): ?>
                                        <span class="text-slate-300 italic">-</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="p-2 text-slate-600"><?= e($p['tipo_calzado_habitual'] ?: '-') ?></td>
                            <td class="p-2 whitespace-nowrap">
                                <span class="font-semibold text-slate-800"><?= e($p['creado_por_nombre'] ?? 'Personal') ?></span>
                                <span class="text-[9px] text-slate-400 block"><?= e($p['creado_por_rol'] ?? '') ?></span>
                            </td>
                            <td class="p-2 font-mono text-slate-500 whitespace-nowrap"><?= e($p['fecha_creacion']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- PIE TÉCNICO -->
        <div class="pt-4 border-t border-slate-200 flex justify-between items-center text-[9px] text-slate-400">
            <div>
                Documento de auditoría técnica general generado desde el sistema centralizado de Podología Ana Ramírez.
            </div>
            <div class="font-mono">
                Página 1 de 1 • Fin de Registro
            </div>
        </div>

    </div>

</body>
</html>
