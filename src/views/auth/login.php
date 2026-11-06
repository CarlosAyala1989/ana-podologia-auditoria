<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Acceso al Sistema - Podología Ana Ramírez</title>
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
        
        <!-- TARJETA PRINCIPAL DE LOGIN -->
        <div class="bg-white rounded-3xl border border-slate-200/90 shadow-2xl overflow-hidden transition-all">
            
            <!-- ENCABEZADO CON EL EMBLEMA CENTRAL MEJORADO -->
            <div class="bg-gradient-to-br from-[#0A2540] via-[#005B94] to-[#198182] p-8 text-center text-white relative">
                
                <!-- Emblema Central Circular Pulido -->
                <div class="flex justify-center mb-3">
                    <div class="w-24 h-24 rounded-full border-2 border-[#2EC4B6] shadow-xl p-1 bg-[#0A2540] flex items-center justify-center ring-4 ring-white/10 hover:scale-105 transition duration-300">
                        <img src="assets/img/logo-badge.png" alt="Emblema Podología Ana Ramírez" class="w-full h-full object-contain">
                    </div>
                </div>

                <h1 class="text-2xl font-black tracking-tight text-white">Podología Ana Ramírez</h1>
                <p class="text-xs uppercase tracking-widest text-[#2EC4B6] font-bold mt-0.5">Centro Especializado de Salud Podológica</p>
                
                <div class="mt-3 inline-flex items-center px-3 py-1 rounded-full bg-white/10 backdrop-blur-sm text-[11px] font-medium text-slate-200 border border-white/10">
                    <span class="w-2 h-2 rounded-full bg-[#2EC4B6] mr-2 animate-pulse"></span>
                    Acceso con DNI • Sesión Médica
                </div>
            </div>

            <div class="p-6 sm:p-8 space-y-6">
                
                <!-- ALERTAS -->
                <?php if (!empty($mensajeError)): ?>
                    <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-start space-x-3 animate-fade-in">
                        <i class="fa-solid fa-circle-exclamation text-base text-rose-600 mt-0.5"></i>
                        <div><?= $mensajeError ?></div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($mensajeExito)): ?>
                    <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-start space-x-3 animate-fade-in">
                        <i class="fa-solid fa-circle-check text-base text-emerald-600 mt-0.5"></i>
                        <div><?= $mensajeExito ?></div>
                    </div>
                <?php endif; ?>

                <!-- FORMULARIO DE ACCESO EXCLUSIVO POR DNI -->
                <form action="index.php?accion=login_procesar" method="POST" class="space-y-4">
                    
                    <div>
                        <label for="dni" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                            Número de DNI / Cédula *
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i class="fa-solid fa-id-card"></i>
                            </span>
                            <input type="text" name="dni" id="dni" required placeholder="Ingrese su DNI"
                                   class="touch-target w-full pl-10 rounded-xl border border-slate-300 px-3.5 py-3 text-slate-800 placeholder-slate-400 focus:border-[#005B94] focus:ring-2 focus:ring-[#005B94]/20 transition text-sm font-mono font-bold">
                        </div>
                    </div>

                    <div>
                    <div class="flex items-center justify-between mb-1.5">
                            <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-600">
                                Contraseña *
                            </label>
                            <span class="text-[11px] text-[#005B94] font-semibold">Use la clave asignada por administración</span>
                        </div>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i class="fa-solid fa-lock"></i>
                            </span>
                            <input type="password" name="password" id="password" required placeholder="••••••••"
                                   class="touch-target w-full pl-10 rounded-xl border border-slate-300 px-3.5 py-3 text-slate-800 placeholder-slate-400 focus:border-[#005B94] focus:ring-2 focus:ring-[#005B94]/20 transition text-sm">
                        </div>
                    </div>

                    <button type="submit" 
                            class="touch-target w-full mt-2 py-3.5 rounded-xl bg-[#005B94] hover:bg-[#0A2540] active:scale-[0.98] text-white font-bold text-sm shadow-md hover:shadow-lg transition flex items-center justify-center space-x-2">
                        <i class="fa-solid fa-right-to-bracket text-[#2EC4B6]"></i>
                        <span>Ingresar con DNI</span>
                    </button>
                </form>

            </div>
        </div>

        <div class="text-center text-xs text-slate-400">
            © <?= date('Y') ?> <strong>Podología Ana Ramírez</strong> • Salud Podológica
        </div>

    </div>

</body>
</html>
