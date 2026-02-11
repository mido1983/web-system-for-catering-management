<h1 class="text-2xl font-bold mb-4">תפריטים</h1>
<?php if (($_GET['error'] ?? '') === 'dates'): ?>
    <div class="bg-red-100 text-red-800 p-3 rounded mb-4">טווח תאריכים לא תקין.</div>
<?php endif; ?>

<div class="bg-white p-4 rounded shadow mb-6">
    <h2 class="font-bold mb-2">יצירת תפריט</h2>
    <form method="post" class="grid grid-cols-1 md:grid-cols-4 gap-2">
        <?php echo csrf_field(); ?>
        <select class="border p-2" name="station_id" required>
            <option value="">תחנה</option>
            <?php foreach ($stations as $s): ?>
                <option value="<?php echo (int)$s['id']; ?>"><?php echo e($s['name']); ?></option>
            <?php endforeach; ?>
        </select>
        <input class="border p-2" type="date" name="period_start" required>
        <input class="border p-2" type="date" name="period_end" required>
        <button class="bg-blue-600 text-white rounded px-4">צור/פתח</button>
    </form>
</div>

<div class="bg-white rounded shadow">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b">
                <th class="p-2 text-right">תחנה</th>
                <th class="p-2 text-right">תחילה</th>
                <th class="p-2 text-right">סיום</th>
                <th class="p-2"></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($menus as $m): ?>
                <tr class="border-b">
                    <td class="p-2"><?php echo e($m['station_name']); ?></td>
                    <td class="p-2"><?php echo e($m['period_start']); ?></td>
                    <td class="p-2"><?php echo e($m['period_end']); ?></td>
                    <td class="p-2"><a class="text-blue-600" href="/admin/menus/<?php echo (int)$m['id']; ?>">עריכה</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>