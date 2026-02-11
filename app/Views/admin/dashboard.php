<h1 class="text-2xl font-bold mb-4">דשבורד</h1>
<div class="bg-white rounded shadow p-4">
    <h2 class="text-lg font-bold mb-2">תחנות</h2>
    <ul class="space-y-2">
        <?php foreach ($stations as $s): ?>
            <li class="border p-2 rounded">
                <?php echo e($s['name']); ?>
            </li>
        <?php endforeach; ?>
    </ul>
</div>