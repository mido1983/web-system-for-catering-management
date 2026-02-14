<h1 class="text-2xl font-bold mb-4">מנהלי מערכת</h1>

<div class="bg-white p-4 rounded shadow mb-6">
    <h2 class="font-bold mb-2">יצירת מנהל תחנה</h2>
    <form method="post" action="<?php echo e(app_url('/sa/admins')); ?>" class="grid grid-cols-1 md:grid-cols-6 gap-2">
        <?php echo csrf_field(); ?>
        <input class="border p-2" type="text" name="first_name" placeholder="שם פרטי" required>
        <input class="border p-2" type="text" name="last_name" placeholder="שם משפחה" required>
        <input class="border p-2" type="text" name="phone" placeholder="טלפון" required>
        <input class="border p-2" type="text" name="work_hours" placeholder="שעות עבודה" required>
        <input class="border p-2" type="email" name="email" placeholder="אימייל" required>
        <input class="border p-2" type="text" name="temp_password" placeholder="סיסמה זמנית" required>
        <button class="bg-blue-600 text-white rounded px-4">צור מנהל</button>
    </form>
</div>

<div class="bg-white rounded shadow">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b">
                <th class="p-2 text-right">שם</th>
                <th class="p-2 text-right">אימייל</th>
                <th class="p-2 text-right">טלפון</th>
                <th class="p-2 text-right">שעות</th>
                <th class="p-2 text-right">סטטוס</th>
                <th class="p-2 text-right">נוצר</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($admins as $a): ?>
                <tr class="border-b">
                    <td class="p-2"><?php echo e(trim(($a['first_name'] ?? '') . ' ' . ($a['last_name'] ?? ''))); ?></td>
                    <td class="p-2"><?php echo e($a['email']); ?></td>
                    <td class="p-2"><?php echo e($a['phone'] ?? '-'); ?></td>
                    <td class="p-2"><?php echo e($a['work_hours'] ?? '-'); ?></td>
                    <td class="p-2"><?php echo (int)$a['is_active'] === 1 ? 'פעיל' : 'לא פעיל'; ?></td>
                    <td class="p-2"><?php echo e($a['created_at']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>