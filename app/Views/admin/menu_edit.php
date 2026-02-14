<h1 class="text-2xl font-bold mb-4">עריכת תפריט</h1>
<div class="bg-white p-4 rounded shadow mb-6">
    <div>תקופה: <?php echo e($menu['period_start']); ?> עד <?php echo e($menu['period_end']); ?></div>
    <div>גרסה: <?php echo e($draft['version_number']); ?> (DRAFT)</div>
</div>
<?php if (!empty($versions)): ?>
<div class="bg-white p-4 rounded shadow mb-6">
    <div class="font-bold mb-2">היסטוריית גרסאות</div>
    <ul class="space-y-1 text-sm">
        <?php foreach ($versions as $v): ?>
            <li>
                גרסה <?php echo e($v['version_number']); ?> — <?php echo e($v['status']); ?> <?php echo e($v['published_at']); ?>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<form method="post" class="bg-white p-4 rounded shadow" x-data="{ rows: <?php echo json_encode($items ?: [['dish_id'=>'','planned_portions'=>0]]); ?> }">
    <?php echo csrf_field(); ?>
    <div class="space-y-2">
        <template x-for="(row, index) in rows" :key="index">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                <select class="border p-2" :name="'dish_id['+index+']'" x-model="row.dish_id">
                    <option value="">מנה</option>
                    <?php foreach ($dishes as $d): ?>
                        <option value="<?php echo (int)$d['id']; ?>"><?php echo e($d['name_he']); ?></option>
                    <?php endforeach; ?>
                </select>
                <input class="border p-2" type="number" min="0" :name="'planned_portions['+index+']'" x-model="row.planned_portions" placeholder="מנות מתוכננות">
                <button type="button" class="bg-red-600 text-white rounded" @click="rows.splice(index,1)">הסר</button>
            </div>
        </template>
    </div>

    <div class="mt-4 flex gap-2">
        <button type="button" class="bg-slate-600 text-white px-4 py-2 rounded" @click="rows.push({dish_id:'', planned_portions:0})">הוסף שורה</button>
        <button class="bg-blue-600 text-white px-4 py-2 rounded">שמור</button>
    </div>
</form>

<form method="post" action="<?php echo e(app_url('/admin/menus/' . (int)$menu['id'] . '/publish')); ?>" class="mt-4">
    <?php echo csrf_field(); ?>
    <button class="bg-green-600 text-white px-4 py-2 rounded">פרסום תפריט</button>
</form>
