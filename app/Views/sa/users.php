<h1 class="text-2xl font-bold mb-4">משתמשים</h1>

<div class="bg-white p-4 rounded shadow mb-6">
    <h2 class="font-bold mb-2">יצירת משתמש</h2>
    <form method="post" action="<?php echo e(app_url('/sa/users/create')); ?>" class="grid grid-cols-1 md:grid-cols-6 gap-2">
        <?php echo csrf_field(); ?>
        <input class="border p-2" type="email" name="email" placeholder="אימייל" required>
        <input class="border p-2" type="text" name="temp_password" placeholder="סיסמה זמנית" required>
        <select class="border p-2" name="role" required>
            <option value="STATION_USER">STATION_USER</option>
            <option value="ADMIN">ADMIN</option>
            <option value="SUPERADMIN">SUPERADMIN</option>
        </select>
        <select class="border p-2" name="admin_id">
            <option value="">Admin (optional)</option>
            <?php foreach ($admins as $a): ?>
                <option value="<?php echo (int)$a['id']; ?>"><?php echo e($a['email']); ?></option>
            <?php endforeach; ?>
        </select>
        <select class="border p-2" name="station_id">
            <option value="">Station (optional)</option>
            <?php foreach ($stations as $s): ?>
                <option value="<?php echo (int)$s['id']; ?>"><?php echo e($s['name']); ?></option>
            <?php endforeach; ?>
        </select>
        <select class="border p-2" name="job_title">
            <option value="">תפקיד (למשתמש תחנה)</option>
            <?php foreach ($job_titles as $title): ?>
                <option value="<?php echo e($title); ?>"><?php echo e($title); ?></option>
            <?php endforeach; ?>
        </select>
        <button class="bg-blue-600 text-white rounded px-4">צור</button>
    </form>
</div>

<div class="bg-white rounded shadow">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b">
                <th class="p-2 text-right">אימייל</th>
                <th class="p-2 text-right">הרשאה</th>
                <th class="p-2 text-right">תפקיד</th>
                <th class="p-2 text-right">מנהל</th>
                <th class="p-2 text-right">תחנה</th>
                <th class="p-2 text-right">סטטוס</th>
                <th class="p-2 text-right">עריכה</th>
                <th class="p-2 text-right">מחיקה</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
                <tr class="border-b align-top">
                    <td class="p-2"><?php echo e($u['email']); ?></td>
                    <td class="p-2"><?php echo e($u['role']); ?></td>
                    <td class="p-2"><?php echo e($u['job_title'] ?? '-'); ?></td>
                    <td class="p-2"><?php echo e($u['admin_email'] ?? $u['admin_id']); ?></td>
                    <td class="p-2"><?php echo e($u['station_name'] ?? $u['station_id']); ?></td>
                    <td class="p-2"><?php echo (int)$u['is_active'] === 1 ? 'פעיל' : 'לא פעיל'; ?></td>
                    <td class="p-2">
                        <form method="post" action="<?php echo e(app_url('/sa/users/update')); ?>" class="grid grid-cols-1 gap-2">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="user_id" value="<?php echo (int)$u['id']; ?>">
                            <input class="border p-2" type="email" name="email" value="<?php echo e($u['email']); ?>" required>
                            <select class="border p-2" name="role" required>
                                <option value="SUPERADMIN" <?php echo $u['role'] === 'SUPERADMIN' ? 'selected' : ''; ?>>SUPERADMIN</option>
                                <option value="ADMIN" <?php echo $u['role'] === 'ADMIN' ? 'selected' : ''; ?>>ADMIN</option>
                                <option value="STATION_USER" <?php echo $u['role'] === 'STATION_USER' ? 'selected' : ''; ?>>STATION_USER</option>
                            </select>
                            <select class="border p-2" name="admin_id">
                                <option value="">None</option>
                                <?php foreach ($admins as $a): ?>
                                    <option value="<?php echo (int)$a['id']; ?>" <?php echo ((int)$u['admin_id'] === (int)$a['id']) ? 'selected' : ''; ?>><?php echo e($a['email']); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <select class="border p-2" name="station_id">
                                <option value="">None</option>
                                <?php foreach ($stations as $s): ?>
                                    <option value="<?php echo (int)$s['id']; ?>" <?php echo ((int)$u['station_id'] === (int)$s['id']) ? 'selected' : ''; ?>><?php echo e($s['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <select class="border p-2" name="job_title">
                                <option value="">תפקיד (למשתמש תחנה)</option>
                                <?php foreach ($job_titles as $title): ?>
                                    <option value="<?php echo e($title); ?>" <?php echo (($u['job_title'] ?? '') === $title) ? 'selected' : ''; ?>><?php echo e($title); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <select class="border p-2" name="is_active">
                                <option value="1" <?php echo (int)$u['is_active'] === 1 ? 'selected' : ''; ?>>פעיל</option>
                                <option value="0" <?php echo (int)$u['is_active'] === 0 ? 'selected' : ''; ?>>לא פעיל</option>
                            </select>
                            <button class="bg-slate-700 text-white rounded px-3 py-1">שמור</button>
                        </form>
                    </td>
                    <td class="p-2">
                        <form method="post" action="<?php echo e(app_url('/sa/users/delete')); ?>" onsubmit="return confirm('למחוק משתמש?')">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="user_id" value="<?php echo (int)$u['id']; ?>">
                            <button class="bg-red-600 text-white rounded px-3 py-1">מחק</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>