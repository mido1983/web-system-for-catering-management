<?php
$wasteByDish = [];
foreach ($waste_items as $wi) {
    $wasteByDish[$wi['dish_id']] = $wi;
}
$stepKg = $step_kg ?? 0.1;
?>
<h1 class="text-2xl font-bold mb-4">עריכת דוח</h1>
<form method="post" class="bg-white p-4 rounded shadow space-y-4">
    <?php echo csrf_field(); ?>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
        <div>
            <label class="block mb-1">סיבוס עבר</label>
            <input class="border p-2 w-full" type="number" name="sibus_ok" value="<?php echo e($report['sibus_ok']); ?>">
        </div>
        <div>
            <label class="block mb-1">סיבוס לא עבר</label>
            <input class="border p-2 w-full" type="number" name="sibus_manual" value="<?php echo e($report['sibus_manual']); ?>">
        </div>
        <div>
            <label class="block mb-1">עצורים</label>
            <input class="border p-2 w-full" type="number" name="detainees" value="<?php echo e($report['detainees']); ?>">
        </div>
    </div>

    <div class="space-y-3">
        <?php foreach ($menu_items as $item): ?>
            <?php $w = $wasteByDish[$item['dish_id']] ?? null; ?>
            <div class="border rounded p-3">
                <div class="font-bold mb-2"><?php echo e($item['name_he']); ?></div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                    <div>
                        <label class="block mb-1">שאריות (ק"ג)</label>
                        <input class="border p-2 w-full" type="number" step="<?php echo e((string)$stepKg); ?>" min="0" name="waste[<?php echo (int)$item['dish_id']; ?>][leftover_kg]" value="<?php echo e($w ? $w['leftover_grams']/1000 : 0); ?>">
                    </div>
                    <div>
                        <label class="block mb-1">נזרק (ק"ג)</label>
                        <input class="border p-2 w-full" type="number" step="<?php echo e((string)$stepKg); ?>" min="0" name="waste[<?php echo (int)$item['dish_id']; ?>][thrown_kg]" value="<?php echo e($w ? $w['thrown_grams']/1000 : 0); ?>">
                    </div>
                </div>
                <div class="mt-2">
                    <label class="block mb-1">סיבה</label>
                    <select class="border p-2 w-full" name="waste[<?php echo (int)$item['dish_id']; ?>][waste_reason_id]">
                        <option value="">בחר</option>
                        <?php foreach ($waste_reasons as $reason): ?>
                            <option value="<?php echo (int)$reason['id']; ?>" <?php echo ($w && (int)$w['waste_reason_id'] === (int)$reason['id']) ? 'selected' : ''; ?>><?php echo e($reason['name_he']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mt-2">
                    <label class="block mb-1">הערה</label>
                    <input class="border p-2 w-full" type="text" name="waste[<?php echo (int)$item['dish_id']; ?>][note]" value="<?php echo e($w['note'] ?? ''); ?>">
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div>
        <label class="block mb-1">הערה כללית</label>
        <textarea class="border p-2 w-full" name="comment" rows="2"><?php echo e($report['comment'] ?? ''); ?></textarea>
    </div>

    <button class="bg-blue-600 text-white px-4 py-2 rounded">שמור</button>
</form>