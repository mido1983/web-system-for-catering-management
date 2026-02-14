<h1 class="text-2xl font-bold mb-4">משתמשי תחנה</h1>

<div class="surface p-4 mb-6">
    <h2 class="font-bold mb-2">יצירת עובד</h2>
    <form id="adminCreateUserForm" method="post" action="<?php echo e(app_url('/admin/users/create')); ?>" class="grid grid-cols-1 md:grid-cols-8 gap-2">
        <?php echo csrf_field(); ?>
        <input class="input-modern" type="text" name="first_name" placeholder="שם פרטי" required>
        <input class="input-modern" type="text" name="last_name" placeholder="שם משפחה" required>
        <input class="input-modern" type="text" name="phone" placeholder="טלפון" required>
        <input class="input-modern" type="time" name="work_start" required>
        <input class="input-modern" type="time" name="work_end" required>
        <input type="hidden" name="work_hours" id="admin_create_work_hours">
        <input class="input-modern" type="email" name="email" placeholder="אימייל" required>
        <input class="input-modern" type="text" name="temp_password" placeholder="סיסמה זמנית" required>
        <select class="select-modern" name="station_id" required>
            <option value="">תחנה</option>
            <?php foreach ($stations as $s): ?>
                <option value="<?php echo (int)$s['id']; ?>"><?php echo e($s['name']); ?></option>
            <?php endforeach; ?>
        </select>
        <select class="select-modern" name="job_title" required>
            <option value="">תפקיד</option>
            <?php foreach ($job_titles as $title): ?>
                <option value="<?php echo e($title); ?>"><?php echo e($title); ?></option>
            <?php endforeach; ?>
        </select>
        <button class="btn-primary md:col-span-2">צור עובד</button>
    </form>
</div>

<div class="space-y-3">
    <?php foreach ($users as $u): ?>
        <div class="surface p-4">
            <div class="font-bold"><?php echo e(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? '')); ?> - <?php echo e($u['email']); ?></div>
            <div class="text-sm mt-1">תפקיד: <?php echo e($u['job_title'] ?? '-'); ?></div>
            <div class="text-sm mt-1">טלפון: <?php echo e($u['phone'] ?? '-'); ?></div>
            <div class="text-sm mt-1">שעות עבודה: <?php echo e($u['work_hours'] ?? '-'); ?></div>
            <div class="text-sm mt-1">תחנה: <?php echo e($u['station_name']); ?></div>
            <div class="text-sm mt-1">הרשאה: עובד</div>
            <div class="text-sm mt-1">סטטוס: <?php echo (int)$u['is_active'] === 1 ? 'פעיל' : 'לא פעיל'; ?></div>
            <div class="mt-3 flex flex-wrap gap-2">
                <form method="post" action="<?php echo e(app_url('/admin/users/reset')); ?>" class="flex gap-2">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="user_id" value="<?php echo (int)$u['id']; ?>">
                    <input class="input-modern" type="text" name="temp_password" placeholder="סיסמה זמנית" required>
                    <button class="btn-neutral">איפוס סיסמה</button>
                </form>
                <form method="post" action="<?php echo e(app_url('/admin/users/toggle')); ?>">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="user_id" value="<?php echo (int)$u['id']; ?>">
                    <input type="hidden" name="is_active" value="<?php echo (int)$u['is_active'] === 1 ? 0 : 1; ?>">
                    <button class="btn-danger"><?php echo (int)$u['is_active'] === 1 ? 'השבת' : 'הפעל'; ?></button>
                </form>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<script>
(function () {
    const form = document.getElementById('adminCreateUserForm');
    if (!form) return;

    form.addEventListener('submit', (e) => {
        const start = form.querySelector('input[name="work_start"]').value;
        const end = form.querySelector('input[name="work_end"]').value;
        if (!start || !end || start >= end) {
            e.preventDefault();
            alert('יש להזין שעות עבודה תקינות: התחלה מוקדמת מסיום.');
            return;
        }
        document.getElementById('admin_create_work_hours').value = `${start} - ${end}`;
    });
})();
</script>
