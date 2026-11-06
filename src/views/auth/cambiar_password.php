<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Actualización de Contraseña Inicial - Podología Ana Ramírez</title>
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="assets/css/custom.css">
</head>
<body class="flex min-h-full items-center justify-center p-4 sm:p-6 bg-slate-100/90 font-sans antialiased text-slate-800">

    <div class="w-full max-w-md space-y-6">
        
        <div class="bg-white rounded-3xl border border-slate-200/90 shadow-2xl overflow-hidden">
            
            <!-- ENCABEZADO CON EMBLEMA CORPORATIVO -->
            <div class="bg-gradient-to-br from-[#0A2540] via-[#005B94] to-[#198182] p-8 text-center text-white relative">
                <div class="flex justify-center mb-3">
                    <div class="w-20 h-20 rounded-full border-2 border-[#2EC4B6] shadow-xl p-1 bg-[#0A2540] flex items-center justify-center">
                        <img src="assets/img/logo-badge.png" alt="Emblema Ana Ramírez" class="w-full h-full object-contain">
                    </div>
                </div>
                <h1 class="text-xl font-black text-white">Actualización de Contraseña</h1>
                <p class="text-xs text-[#2EC4B6] font-semibold mt-1 uppercase tracking-wider">Protocolo de Seguridad Inicial</p>
            </div>

            <div class="p-6 sm:p-8 space-y-5">
                
                <!-- MENSAJE DE BIENVENIDA Y AVISO -->
                <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 text-xs flex items-start space-x-3">
                    <i class="fa-solid fa-key text-amber-600 text-base mt-0.5"></i>
                    <div>
                        <strong class="font-bold">Hola, <?= e($usuario['nombre_completo']) ?></strong> (DNI: <span class="font-mono"><?= e($usuario['dni']) ?></span>)
                        <p class="mt-1 text-amber-800">
                            Por política de privacidad médica, debe sustituir su contraseña inicial por una contraseña personal y segura.
                        </p>
                    </div>
                </div>

                <?php if (!empty($mensajeError)): ?>
                    <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-start space-x-3 animate-fade-in">
                        <i class="fa-solid fa-circle-exclamation text-base text-rose-600 mt-0.5"></i>
                        <div><?= $mensajeError ?></div>
                    </div>
                <?php endif; ?>

                <!-- FORMULARIO -->
                <form action="index.php?accion=guardar_password" method="POST" class="space-y-4">
                    
                    <div>
                        <label for="nueva_password" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                            Nueva Contraseña Personal *
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i class="fa-solid fa-lock"></i>
                            </span>
                            <input type="password" name="nueva_password" id="nueva_password" required minlength="6" placeholder="Mínimo 6 caracteres"
                                   class="touch-target w-full pl-10 rounded-xl border border-slate-300 px-3.5 py-3 text-slate-800 focus:border-[#005B94] focus:ring-2 focus:ring-[#005B94]/20 transition text-sm">
                        </div>
                    </div>

                    <div>
                        <label for="confirmar_password" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                            Confirmar Nueva Contraseña *
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i class="fa-solid fa-shield-check"></i>
                            </span>
                            <input type="password" name="confirmar_password" id="confirmar_password" required minlength="6" placeholder="Repita la contraseña"
                                   class="touch-target w-full pl-10 rounded-xl border border-slate-300 px-3.5 py-3 text-slate-800 focus:border-[#005B94] focus:ring-2 focus:ring-[#005B94]/20 transition text-sm">
                        </div>
                    </div>

                    <div class="text-[11px] text-slate-500 space-y-1 bg-slate-50 p-3 rounded-xl border border-slate-200">
                        <div class="flex items-center text-slate-600">
                            <i class="fa-solid fa-check text-[#2EC4B6] mr-1.5 text-xs"></i> Mínimo 6 caracteres
                        </div>
                        <div class="flex items-center text-slate-600">
                            <i class="fa-solid fa-check text-[#2EC4B6] mr-1.5 text-xs"></i> No puede ser igual a su número de DNI
                        </div>
                    </div>

                    <button type="submit" 
                            class="touch-target w-full py-3.5 rounded-xl bg-[#005B94] hover:bg-[#0A2540] active:scale-[0.98] text-white font-bold text-sm shadow-md hover:shadow-lg transition flex items-center justify-center space-x-2">
                        <i class="fa-solid fa-floppy-disk text-[#2EC4B6]"></i>
                        <span>Guardar Contraseña y Continuar</span>
                    </button>
                </form>

                <div class="text-center pt-2">
                    <a href="index.php?accion=logout" class="text-xs text-slate-400 hover:text-rose-600 transition">
                        <i class="fa-solid fa-arrow-left mr-1"></i> Salir de la sesión
                    </a>
                </div>

            </div>
        </div>

    </div>

</body>
</html>
