<h1 class="text-2xl font-bold mb-4">תחנות</h1>
<div class="space-y-3">
    <?php foreach ($stations as $s): ?>
        <form method="post" class="bg-white p-4 rounded shadow flex items-center gap-3">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="station_id" value="<?php echo (int)$s['id']; ?>">
            <input class="flex-1 border p-2" name="name" value="<?php echo e($s['name']); ?>">
            <button class="bg-blue-600 text-white px-4 py-2 rounded">שמור</button>
        </form>
    <?php endforeach; ?>
</div>