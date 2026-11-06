<?php
require_once dirname(__DIR__, 3) . '/config/auth.php';
$usuarioActivo = usuarioActual();
?>
<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($tituloPagina) ? e($tituloPagina) . ' - ' : '' ?>Podología Ana Ramírez</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        podo: {
                            blue: '#005B94',
                            dark: '#0A2540',
                            mint: '#2EC4B6',
                            cyan: '#00B4D8',
                            light: '#F8FAFC'
                        }
                    }
                }
            }
        }
    </script>
    <!-- Iconos FontAwesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="assets/css/custom.css">
    <style>
        input, select, textarea, button {
            font-size: 16px !important;
            touch-action: manipulation;
        }
        .touch-target {
            min-height: 48px;
        }
    </style>
</head>
<body class="flex flex-col min-h-full font-sans antialiased text-slate-800 bg-[#F8FAFC]">

    <!-- BARRA SUPERIOR DE NAVEGACIÓN -->
    <header class="sticky top-0 z-40 bg-[#0A2540] border-b border-[#005B94]/30 shadow-md">
        <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 sm:h-20">
                
                <!-- Logo Oficial y Nombre -->
                <div class="flex items-center space-x-3">
                    <a href="index.php" class="flex items-center space-x-3 focus:outline-none">
                        <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-full overflow-hidden border-2 border-[#2EC4B6] shadow p-0.5 bg-[#0A2540] flex items-center justify-center">
                            <img src="assets/img/logo-badge.png" alt="Emblema Podología Ana Ramírez" class="w-full h-full object-contain">
                        </div>
                        <div>
                            <span class="block text-base sm:text-lg font-black tracking-tight text-white leading-tight">
                                Podología Ana Ramírez
                            </span>
                            <span class="block text-[11px] font-bold text-[#2EC4B6] uppercase tracking-wider">
                                Salud Podológica & Biomecánica
                            </span>
                        </div>
                    </a>
                </div>

                <!-- Menú Principal Tablet / Desktop -->
                <nav class="hidden md:flex items-center space-x-1 lg:space-x-2">
                    <a href="index.php?accion=registro" 
                       class="inline-flex items-center px-3.5 py-2 text-xs font-bold rounded-xl transition <?= (!isset($_GET['accion']) || $_GET['accion'] === 'registro') ? 'bg-[#005B94] text-white shadow' : 'text-slate-300 hover:text-white hover:bg-white/10' ?>">
                        <i class="mr-2 fa-solid fa-user-plus text-[#2EC4B6]"></i>
                        Registrar Paciente
                    </a>

                    <a href="index.php?accion=listado" 
                       class="inline-flex items-center px-3.5 py-2 text-xs font-bold rounded-xl transition <?= (isset($_GET['accion']) && $_GET['accion'] === 'listado') ? 'bg-[#005B94] text-white shadow' : 'text-slate-300 hover:text-white hover:bg-white/10' ?>">
                        <i class="mr-2 fa-solid fa-users text-[#2EC4B6]"></i>
                        Directorio de Pacientes
                    </a>

                    <!-- Panel Técnico Exclusivo para SuperAdmin -->
                    <?php if (esSuperAdmin()): ?>
                        <a href="index.php?accion=sistema" 
                           class="inline-flex items-center px-3.5 py-2 text-xs font-bold rounded-xl transition <?= (isset($_GET['accion']) && $_GET['accion'] === 'sistema') ? 'bg-purple-700 text-white shadow' : 'text-purple-300 hover:text-white hover:bg-purple-900/40' ?>">
                            <i class="mr-2 fa-solid fa-terminal text-purple-400"></i>
                            Panel Técnico / Logs
                        </a>
                    <?php endif; ?>
                </nav>

                <!-- Perfil Activo, Badge de Rol y Cierre de Sesión -->
                <div class="flex items-center space-x-3">
                    <?php if ($usuarioActivo): ?>
                        <div class="hidden sm:flex flex-col text-right">
                            <span class="text-xs font-bold text-white"><?= e($usuarioActivo['nombre_completo']) ?></span>
                            <div>
                                <?php if ($usuarioActivo['rol_slug'] === 'superadmin'): ?>
                                    <span class="inline-flex items-center text-[10px] font-bold px-2 py-0.5 rounded-full bg-purple-900/80 text-purple-200 border border-purple-500/30">
                                        <i class="fa-solid fa-shield-halved mr-1"></i> SuperAdmin
                                    </span>
                                <?php elseif ($usuarioActivo['rol_slug'] === 'admin'): ?>
                                    <span class="inline-flex items-center text-[10px] font-bold px-2 py-0.5 rounded-full bg-[#005B94] text-white border border-[#2EC4B6]/40">
                                        <i class="fa-solid fa-crown mr-1 text-[#2EC4B6]"></i> Dueña / Admin
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-800 text-slate-300 border border-slate-700">
                                        <i class="fa-solid fa-id-badge mr-1 text-[#2EC4B6]"></i> <?= e($usuarioActivo['rol_nombre']) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Botón Cerrar Sesión -->
                        <a href="index.php?accion=logout" title="Cerrar Sesión"
                           class="touch-target inline-flex items-center justify-center w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-white/10 hover:bg-rose-600/80 text-slate-200 hover:text-white transition shadow">
                            <i class="fa-solid fa-arrow-right-from-bracket text-sm"></i>
                        </a>
                    <?php else: ?>
                        <a href="index.php?accion=login" class="text-xs font-bold px-3 py-2 rounded-lg bg-[#005B94] text-white hover:bg-[#2EC4B6] transition">
                            Iniciar Sesión
                        </a>
                    <?php endif; ?>
                </div>

            </div>
        </div>

        <!-- Barra Móvil Secundaria para Tablets / Móviles pequeños -->
        <div class="flex md:hidden border-t border-white/10 bg-[#0A2540]/90 px-3 py-2 justify-around">
            <a href="index.php?accion=registro" class="flex items-center py-1.5 px-3 rounded-lg text-xs font-bold <?= (!isset($_GET['accion']) || $_GET['accion'] === 'registro') ? 'bg-[#005B94] text-white' : 'text-slate-300' ?>">
                <i class="mr-1.5 fa-solid fa-user-plus text-[#2EC4B6]"></i> Nuevo
            </a>
            <a href="index.php?accion=listado" class="flex items-center py-1.5 px-3 rounded-lg text-xs font-bold <?= (isset($_GET['accion']) && $_GET['accion'] === 'listado') ? 'bg-[#005B94] text-white' : 'text-slate-300' ?>">
                <i class="mr-1.5 fa-solid fa-users text-[#2EC4B6]"></i> Directorio
            </a>
            <?php if (esSuperAdmin()): ?>
                <a href="index.php?accion=sistema" class="flex items-center py-1.5 px-3 rounded-lg text-xs font-bold <?= (isset($_GET['accion']) && $_GET['accion'] === 'sistema') ? 'bg-purple-700 text-white' : 'text-purple-300' ?>">
                    <i class="mr-1.5 fa-solid fa-terminal"></i> Sistema
                </a>
            <?php endif; ?>
        </div>
        <?php if ($usuarioActivo): ?>
        <nav aria-label="Módulos de gestión" class="flex flex-wrap gap-2 px-4 sm:px-6 lg:px-8 py-2 border-t border-white/10 max-w-7xl mx-auto text-sm font-bold text-white">
            <a class="px-3 py-2 rounded-lg hover:bg-white/10 <?= ($_GET['accion'] ?? '') === 'agenda' ? 'bg-[#005B94]' : '' ?>" href="index.php?accion=agenda"><i aria-hidden="true" class="fa-regular fa-calendar mr-2 text-[#2EC4B6]"></i>Agenda</a>
            <?php if (tieneRol(['admin', 'superadmin', 'especialista'])): ?><a class="px-3 py-2 rounded-lg hover:bg-white/10 <?= ($_GET['accion'] ?? '') === 'atencion' ? 'bg-[#005B94]' : '' ?>" href="index.php?accion=atencion"><i aria-hidden="true" class="fa-solid fa-notes-medical mr-2 text-[#2EC4B6]"></i>Registrar atención</a><?php endif; ?>
            <?php if (tieneRol(['admin', 'superadmin'])): ?><a class="px-3 py-2 rounded-lg hover:bg-white/10 <?= ($_GET['accion'] ?? '') === 'usuarios' ? 'bg-[#005B94]' : '' ?>" href="index.php?accion=usuarios"><i aria-hidden="true" class="fa-solid fa-user-gear mr-2 text-[#2EC4B6]"></i>Personal y accesos</a><?php endif; ?>
        </nav>
        <?php endif; ?>
    </header>

    <!-- CONTENEDOR PRINCIPAL -->
    <main class="flex-1 py-6 sm:py-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto w-full">
