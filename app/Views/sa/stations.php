<h1 class="text-2xl font-bold mb-4">תחנות</h1>

<div class="bg-white p-4 rounded shadow mb-6">
    <h2 class="font-bold mb-2">יצירת תחנה</h2>
    <form method="post" action="<?php echo e(app_url('/sa/stations')); ?>" class="grid grid-cols-1 md:grid-cols-3 gap-2">
        <?php echo csrf_field(); ?>
        <input class="border p-2" type="text" name="name" placeholder="שם תחנה" required>
        <select class="border p-2" name="admin_id" required>
            <option value="">מנהל</option>
            <?php foreach ($admins as $a): ?>
                <option value="<?php echo (int)$a['id']; ?>"><?php echo e($a['email']); ?></option>
            <?php endforeach; ?>
        </select>
        <button class="bg-blue-600 text-white rounded px-4">צור</button>
    </form>
</div>

<div class="bg-white rounded shadow">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b">
                <th class="p-2 text-right">תחנה</th>
                <th class="p-2 text-right">מנהל</th>
                <th class="p-2 text-right">סטטוס</th>
                <th class="p-2 text-right">עריכה</th>
                <th class="p-2 text-right">מחיקה</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($stations as $s): ?>
                <tr class="border-b">
                    <td class="p-2"><?php echo e($s['name']); ?></td>
                    <td class="p-2"><?php echo e($s['admin_email']); ?></td>
                    <td class="p-2"><?php echo (int)$s['is_active'] === 1 ? 'פעיל' : 'לא פעיל'; ?></td>
                    <td class="p-2">
                        <form method="post" action="<?php echo e(app_url('/sa/stations/update')); ?>" class="grid grid-cols-1 gap-2">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="station_id" value="<?php echo (int)$s['id']; ?>">
                            <input class="border p-2" type="text" name="name" value="<?php echo e($s['name']); ?>" required>
                            <select class="border p-2" name="admin_id" required>
                                <?php foreach ($admins as $a): ?>
                                    <option value="<?php echo (int)$a['id']; ?>" <?php echo ((int)$a['id'] === (int)$s['admin_id']) ? 'selected' : ''; ?>><?php echo e($a['email']); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <select class="border p-2" name="is_active">
                                <option value="1" <?php echo (int)$s['is_active'] === 1 ? 'selected' : ''; ?>>פעיל</option>
                                <option value="0" <?php echo (int)$s['is_active'] === 0 ? 'selected' : ''; ?>>לא פעיל</option>
                            </select>
                            <button class="bg-slate-700 text-white rounded px-3 py-1">שמור</button>
                        </form>
                    </td>
                    <td class="p-2">
                        <form method="post" action="<?php echo e(app_url('/sa/stations/delete')); ?>" onsubmit="return confirm('למחוק תחנה?')">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="station_id" value="<?php echo (int)$s['id']; ?>">
                            <button class="bg-red-600 text-white rounded px-3 py-1">מחק</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>