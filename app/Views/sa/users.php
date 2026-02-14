<h1 class="text-2xl font-bold mb-4">משתמשים</h1>
<div class="bg-white rounded shadow">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b">
                <th class="p-2 text-right">אימייל</th>
                <th class="p-2 text-right">תפקיד</th>
                <th class="p-2 text-right">מנהל</th>
                <th class="p-2 text-right">תחנה</th>
                <th class="p-2 text-right">סטטוס</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
                <tr class="border-b">
                    <td class="p-2"><?php echo e($u['email']); ?></td>
                    <td class="p-2"><?php echo e($u['role']); ?></td>
                    <td class="p-2"><?php echo e($u['admin_id']); ?></td>
                    <td class="p-2"><?php echo e($u['station_id']); ?></td>
                    <td class="p-2"><?php echo (int)$u['is_active'] === 1 ? 'פעיל' : 'לא פעיל'; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>