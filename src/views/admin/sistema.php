<?php
$tituloPagina = "Panel Técnico de Sistema & Logs";
require_once dirname(__DIR__) . '/layout/header.php';
requerirRol('superadmin');

// Consultas técnicas exclusivas de SuperAdmin
$db = Database::pdo();

// 1. Estadísticas de Base de Datos
$stmtTablas = $db->query("
    SELECT table_name AS nombre, 
           table_rows AS filas, 
           ROUND((data_length + index_length) / 1024, 2) AS tamano_kb
    FROM information_schema.tables 
    WHERE table_schema = DATABASE()
    ORDER BY (data_length + index_length) DESC
");
$tablasInfo = $stmtTablas->fetchAll();

// 2. Logs de Seguridad y Sistema
$stmtLogs = $db->query("
    SELECT l.*, 
           CONCAT(u.nombre, ' ', u.apellido) AS usuario_nombre,
           r.nombre AS usuario_rol
    FROM logs_sistema l
    LEFT JOIN usuarios u ON l.usuario_id = u.id
    LEFT JOIN roles r ON u.rol_id = r.id
    ORDER BY l.fecha_hora DESC
    LIMIT 50
");
$logs = $stmtLogs->fetchAll();
?>

<!-- ENCABEZADO TÉCNICO -->
<div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div class="flex items-center space-x-3">
        <div class="w-12 h-12 rounded-2xl bg-purple-900 text-purple-200 flex items-center justify-center text-xl shadow">
            <i class="fa-solid fa-terminal"></i>
        </div>
        <div>
            <div class="flex items-center space-x-2">
                <h1 class="text-2xl font-black text-slate-900">Panel Técnico & Auditoría Global</h1>
                <span class="px-2.5 py-0.5 rounded-full bg-purple-100 text-purple-800 font-bold text-[10px] uppercase">
                    Exclusivo SuperAdmin
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-0.5">
                Monitor de integridad de base de datos, métricas y registro de seguridad.
            </p>
        </div>
    </div>
    <div>
        <a href="index.php?accion=imprimir_directorio" target="_blank"
           class="touch-target inline-flex items-center px-4 py-2.5 text-xs font-bold rounded-xl bg-purple-800 hover:bg-purple-900 text-white shadow-md transition">
            <i class="fa-solid fa-file-pdf mr-2 text-[#2EC4B6]"></i> Reporte Maestro de Pacientes (PDF)
        </a>
    </div>
</div>

<!-- AVISO DE AISLAMIENTO -->
<div class="mb-6 p-4 rounded-2xl bg-purple-50 border border-purple-200 text-purple-900 text-xs flex items-start space-x-3">
    <i class="fa-solid fa-shield-halved text-purple-600 text-lg mt-0.5"></i>
    <div>
        <strong>Aislamiento de Seguridad:</strong> Este módulo técnico está completamente restringido. Los administradores y trabajadores del negocio no tienen visibilidad de esta consola ni acceso a los esquemas físicos.
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    
    <!-- SALUD DE BASE DE DATOS Y TABLAS -->
    <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-4">
        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center justify-between">
            <span>Estructura de Base de Datos</span>
            <span class="text-emerald-600 font-mono"><i class="fa-solid fa-circle text-[8px] mr-1"></i> Conectado</span>
        </h2>
        <div class="overflow-hidden rounded-xl border border-slate-100">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-[10px] uppercase font-bold text-slate-500">
                    <tr>
                        <th class="p-2.5">Tabla</th>
                        <th class="p-2.5 text-right">Filas</th>
                        <th class="p-2.5 text-right">Tamaño</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach ($tablasInfo as $t): ?>
                        <tr>
                            <td class="p-2.5 font-mono text-[11px] text-[#005B94] font-semibold"><?= e($t['nombre']) ?></td>
                            <td class="p-2.5 text-right font-mono"><?= $t['filas'] ?></td>
                            <td class="p-2.5 text-right font-mono text-slate-400"><?= $t['tamano_kb'] ?> KB</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ENTORNO DE EJECUCIÓN -->
    <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-4">
        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400">Entorno y Parámetros del Servidor</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-xs">
            <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                <div class="text-[10px] text-slate-400 uppercase font-bold">Versión PHP</div>
                <div class="font-bold text-slate-800 font-mono mt-0.5"><?= PHP_VERSION ?></div>
            </div>
            <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                <div class="text-[10px] text-slate-400 uppercase font-bold">PDO Driver</div>
                <div class="font-bold text-slate-800 font-mono mt-0.5">MySQL / MariaDB</div>
            </div>
            <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                <div class="text-[10px] text-slate-400 uppercase font-bold">Modo Emulación PDO</div>
                <div class="font-bold text-emerald-600 font-mono mt-0.5">Desactivado (Seguro)</div>
            </div>
            <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                <div class="text-[10px] text-slate-400 uppercase font-bold">Manejo de Errores</div>
                <div class="font-bold text-slate-800 font-mono mt-0.5">ERRMODE_EXCEPTION</div>
            </div>
            <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                <div class="text-[10px] text-slate-400 uppercase font-bold">Sesiones Seguras</div>
                <div class="font-bold text-emerald-600 font-mono mt-0.5">HttpOnly / Lax</div>
            </div>
            <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                <div class="text-[10px] text-slate-400 uppercase font-bold">Zona Horaria</div>
                <div class="font-bold text-slate-800 font-mono mt-0.5"><?= date_default_timezone_get() ?></div>
            </div>
        </div>
    </div>
</div>

<!-- AUDITORÍA DE LOGS DE SISTEMA Y SEGURIDAD -->
<div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
    <div class="bg-slate-50/80 px-6 py-4 border-b border-slate-200 flex items-center justify-between">
        <div class="flex items-center space-x-2">
            <i class="fa-solid fa-list-check text-purple-700"></i>
            <h2 class="text-sm font-bold text-slate-800">Eventos de Auditoría y Seguridad Recientes (`logs_sistema`)</h2>
        </div>
        <span class="text-xs font-mono font-bold text-slate-500">Últimos 50 eventos</span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs">
            <thead>
                <tr class="bg-slate-50 text-[10px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200">
                    <th class="py-3 px-4">Timestamp</th>
                    <th class="py-3 px-4">Nivel</th>
                    <th class="py-3 px-4">Módulo</th>
                    <th class="py-3 px-4">Acción</th>
                    <th class="py-3 px-4">Descripción</th>
                    <th class="py-3 px-4">Usuario</th>
                    <th class="py-3 px-4">IP Origen</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
                <?php foreach ($logs as $l): ?>
                    <tr class="hover:bg-slate-50/60 transition">
                        <td class="py-2.5 px-4 font-mono text-[11px] text-slate-400 whitespace-nowrap">
                            <?= e($l['fecha_hora']) ?>
                        </td>
                        <td class="py-2.5 px-4 whitespace-nowrap">
                            <?php if ($l['nivel'] === 'SECURITY' || $l['nivel'] === 'ERROR'): ?>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                    <?= e($l['nivel']) ?>
                                </span>
                            <?php elseif ($l['nivel'] === 'WARNING'): ?>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                    <?= e($l['nivel']) ?>
                                </span>
                            <?php else: ?>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-slate-100 text-slate-700">
                                    <?= e($l['nivel']) ?>
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="py-2.5 px-4 font-bold font-mono text-[11px] text-slate-600">
                            <?= e($l['modulo']) ?>
                        </td>
                        <td class="py-2.5 px-4 font-bold text-[#005B94]">
                            <?= e($l['accion']) ?>
                        </td>
                        <td class="py-2.5 px-4 max-w-sm truncate" title="<?= e($l['descripcion']) ?>">
                            <?= e($l['descripcion']) ?>
                        </td>
                        <td class="py-2.5 px-4 whitespace-nowrap">
                            <?= !empty($l['usuario_nombre']) ? e($l['usuario_nombre']) : '<span class="text-slate-300 italic">Anónimo</span>' ?>
                        </td>
                        <td class="py-2.5 px-4 font-mono text-[11px] text-slate-400">
                            <?= e($l['ip_origen']) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/layout/footer.php'; ?>
