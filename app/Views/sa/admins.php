<h1 class="text-2xl font-bold mb-4">מנהלי מערכת</h1>

<div class="bg-white p-4 rounded shadow mb-6">
    <h2 class="font-bold mb-2">יצירת מנהל</h2>
    <form method="post" class="grid grid-cols-1 md:grid-cols-3 gap-2">
        <?php echo csrf_field(); ?>
        <input class="border p-2" type="email" name="email" placeholder="אימייל" required>
        <input class="border p-2" type="text" name="temp_password" placeholder="סיסמה זמנית" required>
        <button class="bg-blue-600 text-white rounded px-4">צור</button>
    </form>
</div>

<div class="bg-white rounded shadow">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b">
                <th class="p-2 text-right">אימייל</th>
                <th class="p-2 text-right">סטטוס</th>
                <th class="p-2 text-right">נוצר</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($admins as $a): ?>
                <tr class="border-b">
                    <td class="p-2"><?php echo e($a['email']); ?></td>
                    <td class="p-2"><?php echo (int)$a['is_active'] === 1 ? 'פעיל' : 'לא פעיל'; ?></td>
                    <td class="p-2"><?php echo e($a['created_at']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>