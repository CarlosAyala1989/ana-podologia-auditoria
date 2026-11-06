<?php if (!empty($auditoria)): ?>
<section class="mt-8">
    <h2 class="text-lg font-bold mb-3">Historial de cambios</h2>
    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
        <table class="w-full text-sm text-left">
            <thead class="bg-slate-100"><tr><th class="p-3">Fecha y autor</th><th class="p-3">Campo</th><th class="p-3">Anterior</th><th class="p-3">Nuevo</th><th class="p-3">Motivo</th></tr></thead>
            <tbody><?php foreach ($auditoria as $a): ?>
                <tr class="border-t border-slate-200"><td class="p-3"><?= e($a['fecha_hora']) ?><br><?= e($a['autor']) ?></td><td class="p-3"><?= e($a['campo']) ?></td><td class="p-3 whitespace-pre-wrap break-words"><?= e($a['valor_anterior'] ?? '—') ?></td><td class="p-3 whitespace-pre-wrap break-words"><?= e($a['valor_nuevo'] ?? '—') ?></td><td class="p-3"><?= e($a['motivo']) ?></td></tr>
            <?php endforeach; ?></tbody>
        </table>
    </div>
</section>
<?php endif; ?>
