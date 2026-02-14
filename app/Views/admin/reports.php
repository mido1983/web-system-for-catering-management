<h1 class="text-2xl font-bold mb-4">דוחות</h1>

<form method="get" class="bg-white p-4 rounded shadow mb-4 grid grid-cols-1 md:grid-cols-4 gap-2">
    <select class="border p-2" name="station_id">
        <option value="">תחנה</option>
        <?php foreach ($stations as $s): ?>
            <option value="<?php echo (int)$s['id']; ?>" <?php echo ($filters['station_id'] == $s['id']) ? 'selected' : ''; ?>><?php echo e($s['name']); ?></option>
        <?php endforeach; ?>
    </select>
    <input class="border p-2" type="date" name="date_from" value="<?php echo e($filters['date_from']); ?>">
    <input class="border p-2" type="date" name="date_to" value="<?php echo e($filters['date_to']); ?>">
    <button class="bg-blue-600 text-white rounded">סנן</button>
</form>

<div class="bg-white rounded shadow">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b">
                <th class="p-2 text-right">תחנה</th>
                <th class="p-2 text-right">תאריך</th>
                <th class="p-2 text-right">סיבוס עבר</th>
                <th class="p-2 text-right">סיבוס לא עבר</th>
                <th class="p-2 text-right">עצורים</th>
                <th class="p-2"></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($reports as $r): ?>
                <tr class="border-b">
                    <td class="p-2"><?php echo e($r['station_name']); ?></td>
                    <td class="p-2"><?php echo e($r['date']); ?></td>
                    <td class="p-2"><?php echo e($r['sibus_ok']); ?></td>
                    <td class="p-2"><?php echo e($r['sibus_manual']); ?></td>
                    <td class="p-2"><?php echo e($r['detainees']); ?></td>
                    <td class="p-2"><a class="text-blue-600" href="<?php echo e(app_url('/admin/reports/' . (int)$r['id'])); ?>">עריכה</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
