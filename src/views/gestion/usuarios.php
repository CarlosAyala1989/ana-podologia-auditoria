<?php
$tituloPagina = 'Personal y accesos';
require dirname(__DIR__) . '/layout/header.php';
require __DIR__ . '/avisos.php';
?>
<div class="mb-6"><h1 class="text-2xl sm:text-3xl font-black">Personal y accesos</h1><p class="text-sm text-slate-600 mt-1">Cree cuentas, asigne perfiles y desactive accesos del personal.</p></div>
<details class="mb-8 rounded-2xl border border-slate-200 bg-white p-5" <?= ($cuenta || $errores) ? 'open' : '' ?>>
    <summary class="font-bold text-[#005B94] cursor-pointer py-2"><?= $id ? 'Editar cuenta' : 'Agregar usuario' ?></summary>
    <form method="post" class="mt-4 grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <input type="hidden" name="token" value="<?= e(tokenFormulario()) ?>"><input type="hidden" name="id" value="<?= $id ?>">
        <?php foreach (['dni' => 'DNI *', 'nombre' => 'Nombre *', 'apellido' => 'Apellido *', 'email' => 'Correo electrónico', 'telefono' => 'Teléfono', 'especialidad' => 'Especialidad o cargo', 'numero_colegiado' => 'Número de colegiatura'] as $campo => $nombre): ?>
            <div><label for="<?= $campo ?>" class="etiqueta"><?= e($nombre) ?></label><input id="<?= $campo ?>" name="<?= $campo ?>" type="<?= $campo === 'email' ? 'email' : 'text' ?>" class="campo" value="<?= e($cuenta[$campo] ?? '') ?>" <?= in_array($campo, ['dni', 'nombre', 'apellido'], true) ? 'required' : '' ?> maxlength="<?= ['dni' => 12, 'nombre' => 100, 'apellido' => 100, 'email' => 150, 'telefono' => 30, 'especialidad' => 100, 'numero_colegiado' => 50][$campo] ?>"></div>
        <?php endforeach; ?>
        <div><label for="rol_id" class="etiqueta">Perfil *</label><select id="rol_id" name="rol_id" class="campo" required><option value="">Seleccione un perfil</option><?php foreach ($roles as $r): ?><?php if (!esSuperAdmin() && !in_array($r['slug'], ['especialista', 'recepcionista'], true)) continue; ?><option value="<?= (int) $r['id'] ?>" <?= (int) ($cuenta['rol_id'] ?? 0) === (int) $r['id'] ? 'selected' : '' ?>><?= e($r['nombre']) ?></option><?php endforeach; ?></select></div>
        <div><label for="estado" class="etiqueta">Estado</label><select id="estado" name="estado" class="campo"><?php foreach (['activo', 'inactivo'] as $v): ?><option value="<?= $v ?>" <?= ($cuenta['estado'] ?? 'activo') === $v ? 'selected' : '' ?>><?= ucfirst($v) ?></option><?php endforeach; ?></select></div>
        <div class="sm:col-span-2"><label for="password" class="etiqueta"><?= $id ? 'Nueva contraseña (opcional)' : 'Contraseña inicial *' ?></label><input id="password" name="password" type="password" autocomplete="new-password" minlength="6" maxlength="72" class="campo" <?= $id ? '' : 'required' ?>><p class="text-sm text-slate-600 mt-2"><?= $id ? 'Deje el campo vacío para conservar la contraseña.' : 'El usuario deberá cambiarla al ingresar por primera vez.' ?></p></div>
        <div class="sm:col-span-2 lg:col-span-3 flex flex-wrap gap-3"><button type="submit" class="boton">Guardar usuario</button><a class="boton-secundario" href="index.php?accion=usuarios">Cancelar</a></div>
    </form>
</details>
<div class="overflow-x-auto bg-white border border-slate-200 rounded-2xl"><table class="w-full text-sm text-left"><thead class="bg-slate-100"><tr><th class="p-4">Personal</th><th class="p-4">Perfil y contacto</th><th class="p-4">Estado</th><th class="p-4">Acciones</th></tr></thead><tbody><?php foreach ($usuarios as $u): ?><tr class="border-t border-slate-200"><td class="p-4"><span class="font-bold"><?= e($u['nombre'] . ' ' . $u['apellido']) ?></span><p class="text-slate-600">DNI <?= e($u['dni']) ?></p></td><td class="p-4"><?= e($u['rol_nombre']) ?><p class="text-slate-600"><?= e($u['email']) ?></p></td><td class="p-4"><?= e(ucfirst($u['estado'])) ?></td><td class="p-4"><?php if (esSuperAdmin() || in_array($u['slug'], ['especialista', 'recepcionista'], true)): ?><a href="index.php?accion=usuarios&editar=<?= (int) $u['id'] ?>" class="text-[#005B94] font-bold underline">Editar cuenta</a><?php endif; ?></td></tr><?php endforeach; ?></tbody></table></div>
<?php require __DIR__ . '/auditoria.php'; require dirname(__DIR__) . '/layout/footer.php'; ?>
