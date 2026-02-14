<h1 class="text-3xl font-bold mb-4">מנהלי מערכת</h1>

<div class="surface p-4 mb-6">
    <h2 class="font-bold mb-2">יצירת מנהל תחנה</h2>
    <form id="createAdminForm" method="post" action="<?php echo e(app_url('/sa/admins')); ?>" class="grid grid-cols-1 md:grid-cols-6 gap-2">
        <?php echo csrf_field(); ?>
        <input class="input-modern" type="text" name="first_name" placeholder="שם פרטי" required>
        <input class="input-modern" type="text" name="last_name" placeholder="שם משפחה" required>
        <input class="input-modern" type="text" name="phone" placeholder="טלפון" required>
        <input class="input-modern" type="time" name="work_start" required>
        <input class="input-modern" type="time" name="work_end" required>
        <input type="hidden" name="work_hours" id="create_admin_work_hours">
        <input class="input-modern" type="email" name="email" placeholder="אימייל" required>
        <input class="input-modern" type="text" name="temp_password" placeholder="סיסמה זמנית" required>
        <button class="btn-primary">צור מנהל</button>
    </form>
</div>

<div class="surface overflow-x-auto">
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

<script>
(function () {
    const form = document.getElementById('createAdminForm');
    if (!form) return;

    form.addEventListener('submit', (e) => {
        const start = form.querySelector('input[name="work_start"]').value;
        const end = form.querySelector('input[name="work_end"]').value;
        if (!start || !end || start >= end) {
            e.preventDefault();
            alert('יש להזין שעות עבודה תקינות: התחלה מוקדמת מסיום.');
            return;
        }
        document.getElementById('create_admin_work_hours').value = `${start} - ${end}`;
    });
})();
</script>
