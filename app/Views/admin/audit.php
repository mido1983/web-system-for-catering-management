<h1 class="text-2xl font-bold mb-4">לוג פעולות</h1>

<form method="get" class="bg-white p-4 rounded shadow mb-4 grid grid-cols-1 md:grid-cols-4 gap-2">
    <input class="border p-2" type="number" name="user_id" placeholder="מזהה משתמש" value="<?php echo e($filters['user_id']); ?>">
    <input class="border p-2" type="text" name="action" placeholder="פעולה" value="<?php echo e($filters['action']); ?>">
    <input class="border p-2" type="date" name="date_from" value="<?php echo e($filters['date_from']); ?>">
    <input class="border p-2" type="date" name="date_to" value="<?php echo e($filters['date_to']); ?>">
    <button class="bg-blue-600 text-white rounded">סנן</button>
</form>

<div class="bg-white rounded shadow">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b">
                <th class="p-2 text-right">זמן</th>
                <th class="p-2 text-right">משתמש</th>
                <th class="p-2 text-right">פעולה</th>
                <th class="p-2 text-right">ישות</th>
                <th class="p-2 text-right">מזהה</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($logs as $log): ?>
                <tr class="border-b">
                    <td class="p-2"><?php echo e($log['created_at']); ?></td>
                    <td class="p-2"><?php echo e($log['actor_user_id']); ?></td>
                    <td class="p-2"><?php echo e($log['action']); ?></td>
                    <td class="p-2"><?php echo e($log['entity_type']); ?></td>
                    <td class="p-2"><?php echo e($log['entity_id']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>