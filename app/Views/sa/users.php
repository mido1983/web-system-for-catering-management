<?php
$roleLabels = [
    'SUPERADMIN' => 'SUPERADMIN',
    'ADMIN' => 'מנהל תחנה',
    'STATION_USER' => 'עובד',
];
?>
<section class="mb-8">
    <h1 class="text-3xl font-extrabold mb-2">משתמשים</h1>
    <p class="text-slate-500">ניהול הרשאות, תפקידים ופרטי עובד בממשק אחד.</p>
</section>

<section class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-8">
    <div class="surface p-6">
        <div class="text-sm text-slate-500">סה"כ משתמשים</div>
        <div class="text-3xl font-extrabold"><?php echo count($users); ?></div>
    </div>
    <div class="surface p-6">
        <div class="text-sm text-slate-500">עובדים</div>
        <div class="text-3xl font-extrabold"><?php echo count(array_filter($users, fn($u) => $u['role'] === 'STATION_USER')); ?></div>
    </div>
    <div class="surface p-6">
        <div class="text-sm text-slate-500">מנהלי תחנה</div>
        <div class="text-3xl font-extrabold"><?php echo count(array_filter($users, fn($u) => $u['role'] === 'ADMIN')); ?></div>
    </div>
</section>

<section class="surface p-6 mb-8">
    <h2 class="text-xl font-bold mb-3">יצירת משתמש חדש</h2>
    <form method="post" action="<?php echo e(app_url('/sa/users/create')); ?>" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 2xl:grid-cols-6 gap-4">
        <?php echo csrf_field(); ?>
        <input class="input-modern" type="text" name="first_name" placeholder="שם פרטי" required>
        <input class="input-modern" type="text" name="last_name" placeholder="שם משפחה" required>
        <input class="input-modern" type="text" name="phone" placeholder="טלפון" required>
        <input class="input-modern" type="text" name="work_hours" placeholder="שעות עבודה" required>
        <input class="input-modern" type="email" name="email" placeholder="אימייל" required>
        <input class="input-modern" type="text" name="temp_password" placeholder="סיסמה זמנית" required>
        <select class="select-modern" name="role" required>
            <option value="STATION_USER">עובד</option>
            <option value="ADMIN">מנהל תחנה</option>
            <option value="SUPERADMIN">SUPERADMIN</option>
        </select>
        <select class="select-modern" name="admin_id">
            <option value="">מנהל (אופציונלי)</option>
            <?php foreach ($admins as $a): ?>
                <option value="<?php echo (int)$a['id']; ?>"><?php echo e($a['email']); ?></option>
            <?php endforeach; ?>
        </select>
        <select class="select-modern" name="station_id">
            <option value="">תחנה (אופציונלי)</option>
            <?php foreach ($stations as $s): ?>
                <option value="<?php echo (int)$s['id']; ?>"><?php echo e($s['name']); ?></option>
            <?php endforeach; ?>
        </select>
        <select class="select-modern" name="job_title">
            <option value="">תפקיד (לעובד)</option>
            <?php foreach ($job_titles as $title): ?>
                <option value="<?php echo e($title); ?>"><?php echo e($title); ?></option>
            <?php endforeach; ?>
        </select>
        <button class="btn-primary xl:col-span-2">צור משתמש</button>
    </form>
</section>

<section class="surface overflow-x-auto">
    <table class="w-full text-sm min-w-[1450px]">
        <thead class="bg-slate-50">
            <tr>
                <th class="p-4 text-right">שם</th>
                <th class="p-4 text-right">אימייל</th>
                <th class="p-4 text-right">הרשאה</th>
                <th class="p-4 text-right">תפקיד</th>
                <th class="p-4 text-right">טלפון</th>
                <th class="p-4 text-right">שעות</th>
                <th class="p-4 text-right">מנהל</th>
                <th class="p-4 text-right">תחנה</th>
                <th class="p-4 text-right">סטטוס</th>
                <th class="p-4 text-right">עריכה</th>
                <th class="p-4 text-right">מחיקה</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
                <tr class="border-t align-top">
                    <td class="p-4"><?php echo e(trim(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? ''))); ?></td>
                    <td class="p-4"><?php echo e($u['email']); ?></td>
                    <td class="p-4"><?php echo e($roleLabels[$u['role']] ?? $u['role']); ?></td>
                    <td class="p-4"><?php echo e($u['job_title'] ?? '-'); ?></td>
                    <td class="p-4"><?php echo e($u['phone'] ?? '-'); ?></td>
                    <td class="p-4"><?php echo e($u['work_hours'] ?? '-'); ?></td>
                    <td class="p-4"><?php echo e($u['admin_email'] ?? $u['admin_id']); ?></td>
                    <td class="p-4"><?php echo e($u['station_name'] ?? $u['station_id']); ?></td>
                    <td class="p-4">
                        <?php if ((int)$u['is_active'] === 1): ?>
                            <span class="badge badge-ok">פעיל</span>
                        <?php else: ?>
                            <span class="badge badge-off">לא פעיל</span>
                        <?php endif; ?>
                    </td>
                    <td class="p-4">
                        <button
                            type="button"
                            class="btn-neutral open-edit-user"
                            data-id="<?php echo (int)$u['id']; ?>"
                            data-first-name="<?php echo e($u['first_name'] ?? ''); ?>"
                            data-last-name="<?php echo e($u['last_name'] ?? ''); ?>"
                            data-phone="<?php echo e($u['phone'] ?? ''); ?>"
                            data-work-hours="<?php echo e($u['work_hours'] ?? ''); ?>"
                            data-email="<?php echo e($u['email']); ?>"
                            data-role="<?php echo e($u['role']); ?>"
                            data-admin-id="<?php echo e((string)($u['admin_id'] ?? '')); ?>"
                            data-station-id="<?php echo e((string)($u['station_id'] ?? '')); ?>"
                            data-job-title="<?php echo e($u['job_title'] ?? ''); ?>"
                            data-is-active="<?php echo (int)$u['is_active']; ?>"
                        >ערוך</button>
                    </td>
                    <td class="p-4">
                        <form method="post" action="<?php echo e(app_url('/sa/users/delete')); ?>" onsubmit="return confirm('למחוק משתמש?')">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="user_id" value="<?php echo (int)$u['id']; ?>">
                            <button class="btn-danger">מחק</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>

<div id="editUserModal" class="hidden fixed inset-0 z-40 bg-slate-900/40 backdrop-blur-sm p-4">
    <div class="surface max-w-3xl mx-auto mt-8 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-2xl font-bold">עריכת משתמש</h3>
            <button type="button" class="btn-danger" id="closeEditUserModal">סגור</button>
        </div>
        <form method="post" action="<?php echo e(app_url('/sa/users/update')); ?>" class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="user_id" id="edit_user_id">
            <input class="input-modern" type="text" name="first_name" id="edit_first_name" required>
            <input class="input-modern" type="text" name="last_name" id="edit_last_name" required>
            <input class="input-modern" type="text" name="phone" id="edit_phone" required>
            <input class="input-modern" type="text" name="work_hours" id="edit_work_hours" required>
            <input class="input-modern md:col-span-2" type="email" name="email" id="edit_email" required>
            <select class="select-modern" name="role" id="edit_role" required>
                <option value="SUPERADMIN">SUPERADMIN</option>
                <option value="ADMIN">מנהל תחנה</option>
                <option value="STATION_USER">עובד</option>
            </select>
            <select class="select-modern" name="admin_id" id="edit_admin_id">
                <option value="">None</option>
                <?php foreach ($admins as $a): ?>
                    <option value="<?php echo (int)$a['id']; ?>"><?php echo e($a['email']); ?></option>
                <?php endforeach; ?>
            </select>
            <select class="select-modern" name="station_id" id="edit_station_id">
                <option value="">None</option>
                <?php foreach ($stations as $s): ?>
                    <option value="<?php echo (int)$s['id']; ?>"><?php echo e($s['name']); ?></option>
                <?php endforeach; ?>
            </select>
            <select class="select-modern" name="job_title" id="edit_job_title">
                <option value="">תפקיד (לעובד)</option>
                <?php foreach ($job_titles as $title): ?>
                    <option value="<?php echo e($title); ?>"><?php echo e($title); ?></option>
                <?php endforeach; ?>
            </select>
            <select class="select-modern" name="is_active" id="edit_is_active">
                <option value="1">פעיל</option>
                <option value="0">לא פעיל</option>
            </select>
            <button class="btn-primary md:col-span-2">שמור שינויים</button>
        </form>
    </div>
</div>

<script>
(function () {
    const modal = document.getElementById('editUserModal');
    const closeBtn = document.getElementById('closeEditUserModal');
    if (!modal || !closeBtn) return;

    function openModal(dataset) {
        document.getElementById('edit_user_id').value = dataset.id || '';
        document.getElementById('edit_first_name').value = dataset.firstName || '';
        document.getElementById('edit_last_name').value = dataset.lastName || '';
        document.getElementById('edit_phone').value = dataset.phone || '';
        document.getElementById('edit_work_hours').value = dataset.workHours || '';
        document.getElementById('edit_email').value = dataset.email || '';
        document.getElementById('edit_role').value = dataset.role || 'STATION_USER';
        document.getElementById('edit_admin_id').value = dataset.adminId || '';
        document.getElementById('edit_station_id').value = dataset.stationId || '';
        document.getElementById('edit_job_title').value = dataset.jobTitle || '';
        document.getElementById('edit_is_active').value = dataset.isActive || '1';
        modal.classList.remove('hidden');
    }

    function closeModal() {
        modal.classList.add('hidden');
    }

    document.querySelectorAll('.open-edit-user').forEach((btn) => {
        btn.addEventListener('click', () => openModal(btn.dataset));
    });

    closeBtn.addEventListener('click', closeModal);
    modal.addEventListener('click', (e) => {
        if (e.target === modal) closeModal();
    });
})();
</script>