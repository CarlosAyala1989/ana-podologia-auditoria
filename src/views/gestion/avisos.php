<?php if (!empty($mensajeExito)): ?>
    <div role="status" class="mb-5 p-4 rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-900"><?= e($mensajeExito) ?></div>
<?php endif; ?>
<?php if (!empty($mensajeError)): ?>
    <div role="alert" class="mb-5 p-4 rounded-xl border border-rose-200 bg-rose-50 text-rose-900"><?= e($mensajeError) ?></div>
<?php endif; ?>
<?php if (!empty($errores)): ?>
    <div role="alert" class="mb-5 p-4 rounded-xl border border-rose-200 bg-rose-50 text-rose-900">
        <?php foreach ($errores as $error): ?><p><?= e($error) ?></p><?php endforeach; ?>
    </div>
<?php endif; ?>
