<h1 class="text-2xl font-bold mb-4">מתכנן ייצור</h1>

<form method="get" class="bg-white p-4 rounded shadow mb-4 grid grid-cols-1 md:grid-cols-4 gap-2">
    <input class="border p-2" type="date" name="date_from" value="<?php echo e($date_from); ?>">
    <input class="border p-2" type="date" name="date_to" value="<?php echo e($date_to); ?>">
    <button class="bg-blue-600 text-white rounded">חשב</button>
    <a class="bg-slate-700 text-white rounded text-center py-2" href="<?php echo e(app_url('/admin/planner')); ?>?date_from=<?php echo e($date_from); ?>&date_to=<?php echo e($date_to); ?>&export=csv">ייצוא CSV</a>
</form>

<div class="bg-white rounded shadow">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b">
                <th class="p-2 text-right">מרכיב</th>
                <th class="p-2 text-right">יחידה</th>
                <th class="p-2 text-right">כמות כוללת</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data as $row): ?>
                <tr class="border-b">
                    <td class="p-2"><?php echo e($row['name_he']); ?></td>
                    <td class="p-2"><?php echo e($row['unit']); ?></td>
                    <td class="p-2"><?php echo e($row['total_qty']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="text-xs text-slate-500 mt-3">החישוב מניח שכמות המנות היא יומית לכל תקופת התפריט.</div>
