<div class="max-w-md mx-auto">
    <h1 class="text-2xl font-bold mb-4">היסטוריה</h1>
    <div class="space-y-3">
        <?php foreach ($reports as $r): ?>
            <div class="bg-white p-4 rounded shadow">
                <div class="font-bold"><?php echo e($r['date']); ?></div>
                <div class="text-sm mt-2">סיבוס עבר: <?php echo e($r['sibus_ok']); ?></div>
                <div class="text-sm">סיבוס לא עבר: <?php echo e($r['sibus_manual']); ?></div>
                <div class="text-sm">עצורים: <?php echo e($r['detainees']); ?></div>
            </div>
        <?php endforeach; ?>
    </div>
</div>