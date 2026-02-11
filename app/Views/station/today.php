<?php
$menuItems = $menu['items'] ?? [];
$menuVersionId = $menu['version']['id'] ?? 0;
$polling = (int)($settings['polling_seconds'] ?? 60);
$stepGrams = (int)($settings['weight_step_grams'] ?? 100);
$stepKg = $stepGrams / 1000;
$today = date('Y-m-d');
$wasteReasons = $waste_reasons ?? [];
$wasteItems = $waste_items ?? [];
$wasteByDish = [];
foreach ($wasteItems as $wi) {
    $wasteByDish[$wi['dish_id']] = $wi;
}
$report = $report ?? null;
?>
<div class="max-w-md mx-auto">
    <div class="bg-white rounded shadow p-4 mb-4">
        <h1 class="text-2xl font-bold">היום</h1>
        <?php if (isset($_GET['success'])): ?>
            <div class="mt-3 bg-green-100 text-green-800 p-2 rounded">הדוח נשמר</div>
        <?php endif; ?>
        <?php if (!$menu): ?>
            <div class="mt-3 text-red-700">אין תפריט מפורסם לתאריך היום.</div>
        <?php else: ?>
            <div id="menu-update" class="hidden mt-3 bg-yellow-100 text-yellow-800 p-2 rounded">התפריט עודכן - רענן</div>
        <?php endif; ?>
    </div>

    <?php if ($menu): ?>
    <form id="report-form" method="post" class="space-y-4">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="menu_version_id" value="<?php echo (int)$menuVersionId; ?>">

        <div class="bg-white rounded shadow p-4" x-data="{ sibus_ok: <?php echo (int)($report['sibus_ok'] ?? 0); ?>, sibus_manual: <?php echo (int)($report['sibus_manual'] ?? 0); ?>, detainees: <?php echo (int)($report['detainees'] ?? 0); ?> }">
            <h2 class="text-lg font-bold mb-2">כמה אכלו</h2>

            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <div>סיבוס עבר</div>
                    <div class="flex items-center">
                        <button type="button" class="px-3 py-1 bg-slate-200 rounded" @click="if(sibus_ok>0)sibus_ok--">-</button>
                        <input class="w-20 text-center mx-2 border rounded" type="number" min="0" name="sibus_ok" x-model.number="sibus_ok">
                        <button type="button" class="px-3 py-1 bg-slate-200 rounded" @click="sibus_ok++">+</button>
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <div>סיבוס לא עבר (ידני)</div>
                    <div class="flex items-center">
                        <button type="button" class="px-3 py-1 bg-slate-200 rounded" @click="if(sibus_manual>0)sibus_manual--">-</button>
                        <input class="w-20 text-center mx-2 border rounded" type="number" min="0" name="sibus_manual" x-model.number="sibus_manual">
                        <button type="button" class="px-3 py-1 bg-slate-200 rounded" @click="sibus_manual++">+</button>
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <div>עצורים</div>
                    <div class="flex items-center">
                        <button type="button" class="px-3 py-1 bg-slate-200 rounded" @click="if(detainees>0)detainees--">-</button>
                        <input class="w-20 text-center mx-2 border rounded" type="number" min="0" name="detainees" x-model.number="detainees">
                        <button type="button" class="px-3 py-1 bg-slate-200 rounded" @click="detainees++">+</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-3">
            <?php foreach ($menuItems as $item): ?>
                <div class="bg-white rounded shadow p-4">
                    <div class="font-bold mb-3"><?php echo e($item['name_he']); ?></div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm mb-1">שאריות (ק"ג)</label>
                            <input class="w-full border rounded p-2" type="number" step="<?php echo e((string)$stepKg); ?>" min="0" name="waste[<?php echo (int)$item['dish_id']; ?>][leftover_kg]" value="<?php echo e(isset($wasteByDish[$item['dish_id']]) ? $wasteByDish[$item['dish_id']]['leftover_grams']/1000 : 0); ?>">
                        </div>
                        <div>
                            <label class="block text-sm mb-1">נזרק (ק"ג)</label>
                            <input class="w-full border rounded p-2" type="number" step="<?php echo e((string)$stepKg); ?>" min="0" name="waste[<?php echo (int)$item['dish_id']; ?>][thrown_kg]" value="<?php echo e(isset($wasteByDish[$item['dish_id']]) ? $wasteByDish[$item['dish_id']]['thrown_grams']/1000 : 0); ?>">
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="block text-sm mb-1">סיבה</label>
                        <select class="w-full border rounded p-2" name="waste[<?php echo (int)$item['dish_id']; ?>][waste_reason_id]">
                            <option value="">בחר</option>
                            <?php foreach ($wasteReasons as $reason): ?>
                                <option value="<?php echo (int)$reason['id']; ?>" <?php echo (isset($wasteByDish[$item['dish_id']]) && (int)$wasteByDish[$item['dish_id']]['waste_reason_id'] === (int)$reason['id']) ? 'selected' : ''; ?>><?php echo e($reason['name_he']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mt-3">
                        <label class="block text-sm mb-1">הערה</label>
                        <input class="w-full border rounded p-2" type="text" name="waste[<?php echo (int)$item['dish_id']; ?>][note]" value="<?php echo e($wasteByDish[$item['dish_id']]['note'] ?? ''); ?>">
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="bg-white rounded shadow p-4">
            <label class="block text-sm mb-1">הערה כללית</label>
            <textarea class="w-full border rounded p-2" name="comment" rows="2"><?php echo e($report['comment'] ?? ''); ?></textarea>
        </div>

        <div class="sticky bottom-0 bg-white p-3 shadow">
            <button class="w-full bg-green-600 text-white py-3 rounded text-lg">שלח דוח</button>
        </div>
    </form>
    <?php endif; ?>
</div>

<script>
(function(){
    var form = document.getElementById('report-form');
    if (!form) return;
    var key = 'report-<?php echo $today; ?>';

    function loadDraft(){
        var raw = localStorage.getItem(key);
        if (!raw) return;
        try {
            var data = JSON.parse(raw);
            Object.keys(data).forEach(function(name){
                var el = form.querySelector('[name="'+name+'"]');
                if (el) el.value = data[name];
            });
        } catch(e){}
    }

    function saveDraft(){
        var data = {};
        var elements = form.querySelectorAll('input, select, textarea');
        elements.forEach(function(el){
            if (el.name) data[el.name] = el.value;
        });
        localStorage.setItem(key, JSON.stringify(data));
    }

    form.addEventListener('input', function(){
        saveDraft();
    });

    loadDraft();

    <?php if ($menuVersionId > 0): ?>
    setInterval(function(){
        fetch('/station/today?check=1')
            .then(function(r){ return r.json(); })
            .then(function(data){
                if (data.version_id && data.version_id !== <?php echo (int)$menuVersionId; ?>) {
                    var banner = document.getElementById('menu-update');
                    if (banner) banner.classList.remove('hidden');
                }
            });
    }, <?php echo (int)$polling * 1000; ?>);
    <?php endif; ?>
})();
</script>
