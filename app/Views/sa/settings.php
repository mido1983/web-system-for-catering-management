<h1 class="text-2xl font-bold mb-4">הגדרות</h1>
<form method="post" class="bg-white p-4 rounded shadow grid grid-cols-1 md:grid-cols-2 gap-2">
    <?php echo csrf_field(); ?>
    <div>
        <label class="block mb-1">שעת דדליין</label>
        <input class="border p-2 w-full" type="time" name="deadline_time" value="<?php echo e($settings['deadline_time'] ?? '20:00'); ?>">
    </div>
    <div>
        <label class="block mb-1">רענון תפריט (שניות)</label>
        <input class="border p-2 w-full" type="number" name="polling_seconds" value="<?php echo e($settings['polling_seconds'] ?? '60'); ?>">
    </div>
    <div>
        <label class="block mb-1">צעד משקל (גרם)</label>
        <input class="border p-2 w-full" type="number" name="weight_step_grams" value="<?php echo e($settings['weight_step_grams'] ?? '100'); ?>">
    </div>
    <div>
        <label class="block mb-1">שם אפליקציה</label>
        <input class="border p-2 w-full" type="text" name="app_name_he" value="<?php echo e($settings['app_name_he'] ?? ''); ?>">
    </div>
    <div>
        <label class="block mb-1">טלפון תמיכה</label>
        <input class="border p-2 w-full" type="text" name="support_phone" value="<?php echo e($settings['support_phone'] ?? ''); ?>">
    </div>
    <div class="md:col-span-2">
        <button class="bg-blue-600 text-white px-4 py-2 rounded">שמור</button>
    </div>
</form>