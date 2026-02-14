<h1 class="text-3xl font-extrabold mb-6">תחנות</h1>

<div class="surface p-6 mb-8">
    <h2 class="text-xl font-bold mb-4">יצירת תחנה</h2>
    <form method="post" action="<?php echo e(app_url('/sa/stations')); ?>" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
        <?php echo csrf_field(); ?>
        <input class="input-modern" type="text" name="name" placeholder="שם תחנה" required>
        <select class="select-modern" name="admin_id" required>
            <option value="">מנהל תחנה</option>
            <?php foreach ($admins as $a): ?>
                <option value="<?php echo (int)$a['id']; ?>"><?php echo e($a['email']); ?></option>
            <?php endforeach; ?>
        </select>
        <select class="select-modern" name="is_cooking_kitchen">
            <option value="0">מטבח רגיל</option>
            <option value="1">מטבח מבשל</option>
        </select>
        <button class="btn-primary">צור תחנה</button>
        <div class="md:col-span-2 xl:col-span-4">
            <label class="block text-sm font-semibold mb-2">תחנות שמקבלות אוכל מהמטבח הזה</label>
            <select class="select-modern" name="target_station_ids[]" multiple size="5">
                <?php foreach ($stations as $s): ?>
                    <option value="<?php echo (int)$s['id']; ?>"><?php echo e($s['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </form>
</div>

<div class="surface overflow-x-auto">
    <table class="w-full min-w-[1200px] text-sm">
        <thead class="bg-slate-50">
            <tr>
                <th class="p-3 text-right">תחנה</th>
                <th class="p-3 text-right">מנהל</th>
                <th class="p-3 text-right">סטטוס</th>
                <th class="p-3 text-right">סוג מטבח</th>
                <th class="p-3 text-right">תחנות מקבלות</th>
                <th class="p-3 text-right">עריכה</th>
                <th class="p-3 text-right">מחיקה</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($stations as $s): ?>
                <?php $targets = $supply_targets[(int)$s['id']] ?? []; ?>
                <tr class="border-t align-top">
                    <td class="p-3"><?php echo e($s['name']); ?></td>
                    <td class="p-3"><?php echo e($s['admin_email']); ?></td>
                    <td class="p-3"><?php echo (int)$s['is_active'] === 1 ? 'פעיל' : 'לא פעיל'; ?></td>
                    <td class="p-3"><?php echo (int)$s['is_cooking_kitchen'] === 1 ? 'מטבח מבשל' : 'רגיל'; ?></td>
                    <td class="p-3">
                        <?php
                            $targetNames = [];
                            foreach ($stations as $stationOpt) {
                                if (in_array((int)$stationOpt['id'], $targets, true)) {
                                    $targetNames[] = $stationOpt['name'];
                                }
                            }
                            echo e(!empty($targetNames) ? implode(', ', $targetNames) : '-');
                        ?>
                    </td>
                    <td class="p-3">
                        <form method="post" action="<?php echo e(app_url('/sa/stations/update')); ?>" class="grid grid-cols-1 gap-2">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="station_id" value="<?php echo (int)$s['id']; ?>">
                            <input class="input-modern" type="text" name="name" value="<?php echo e($s['name']); ?>" required>
                            <select class="select-modern" name="admin_id" required>
                                <?php foreach ($admins as $a): ?>
                                    <option value="<?php echo (int)$a['id']; ?>" <?php echo ((int)$a['id'] === (int)$s['admin_id']) ? 'selected' : ''; ?>>
                                        <?php echo e($a['email']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <select class="select-modern" name="is_active">
                                <option value="1" <?php echo (int)$s['is_active'] === 1 ? 'selected' : ''; ?>>פעיל</option>
                                <option value="0" <?php echo (int)$s['is_active'] === 0 ? 'selected' : ''; ?>>לא פעיל</option>
                            </select>
                            <select class="select-modern" name="is_cooking_kitchen">
                                <option value="0" <?php echo (int)$s['is_cooking_kitchen'] === 0 ? 'selected' : ''; ?>>מטבח רגיל</option>
                                <option value="1" <?php echo (int)$s['is_cooking_kitchen'] === 1 ? 'selected' : ''; ?>>מטבח מבשל</option>
                            </select>
                            <select class="select-modern" name="target_station_ids[]" multiple size="5">
                                <?php foreach ($stations as $stationOpt): ?>
                                    <?php if ((int)$stationOpt['id'] === (int)$s['id']) continue; ?>
                                    <option value="<?php echo (int)$stationOpt['id']; ?>" <?php echo in_array((int)$stationOpt['id'], $targets, true) ? 'selected' : ''; ?>>
                                        <?php echo e($stationOpt['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button class="btn-neutral">שמור</button>
                        </form>
                    </td>
                    <td class="p-3">
                        <form method="post" action="<?php echo e(app_url('/sa/stations/delete')); ?>" onsubmit="return confirm('למחוק תחנה?')">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="station_id" value="<?php echo (int)$s['id']; ?>">
                            <button class="btn-danger">מחק</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
