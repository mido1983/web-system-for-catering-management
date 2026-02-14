<h1 class="text-2xl font-bold mb-4">×“×•×—×•×ª</h1>

<form method="get" class="bg-white p-4 rounded shadow mb-4 grid grid-cols-1 md:grid-cols-4 gap-2">
    <select class="border p-2" name="station_id">
        <option value="">×ª×—× ×”</option>
        <?php foreach ($stations as $s): ?>
            <option value="<?php echo (int)$s['id']; ?>" <?php echo ($filters['station_id'] == $s['id']) ? 'selected' : ''; ?>><?php echo e($s['name']); ?></option>
        <?php endforeach; ?>
    </select>
    <input class="border p-2" type="date" name="date_from" value="<?php echo e($filters['date_from']); ?>">
    <input class="border p-2" type="date" name="date_to" value="<?php echo e($filters['date_to']); ?>">
    <button class="bg-blue-600 text-white rounded">×¡× ×Ÿ</button>
</form>

<div class="bg-white rounded shadow">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b">
                <th class="p-2 text-right">×ª×—× ×”</th>
                <th class="p-2 text-right">×ª××¨×™×š</th>
                <th class="p-2 text-right">×¡×™×‘×•×¡ ×¢×‘×¨</th>
                <th class="p-2 text-right">×¡×™×‘×•×¡ ×œ× ×¢×‘×¨</th>
                <th class="p-2 text-right">×¢×¦×•×¨×™×</th>
                <th class="p-2"></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($reports as $r): ?>
                <tr class="border-b">
                    <td class="p-2"><?php echo e($r['station_name']); ?></td>
                    <td class="p-2"><?php echo e($r['date']); ?></td>
                    <td class="p-2"><?php echo e($r['sibus_ok']); ?></td>
                    <td class="p-2"><?php echo e($r['sibus_manual']); ?></td>
                    <td class="p-2"><?php echo e($r['detainees']); ?></td>
                    <td class="p-2"><a class="text-blue-600" href="<?php echo e(app_url('/admin/reports/' . (int)$r['id'])); ?>">×¢×¨×™×›×”</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="surface p-4 mt-4">
    <div class="flex items-center justify-between mb-3">
        <h2 class="text-lg font-bold">מגמת דיווחים</h2>
        <span class="text-xs text-slate-500">Chart.js מוכן לדוחות עתידיים</span>
    </div>
    <canvas id="reportsChart" height="110"></canvas>
</div>

<script>
(function () {
    if (!window.Chart) return;
    const rows = Array.from(document.querySelectorAll('table tbody tr'));
    const labels = [];
    const totalMeals = [];
    rows.forEach((row) => {
        const cells = row.querySelectorAll('td');
        if (cells.length < 5) return;
        const date = (cells[1].textContent || '').trim();
        const sibusOk = Number((cells[2].textContent || '0').trim()) || 0;
        const sibusManual = Number((cells[3].textContent || '0').trim()) || 0;
        const detainees = Number((cells[4].textContent || '0').trim()) || 0;
        labels.push(date);
        totalMeals.push(sibusOk + sibusManual + detainees);
    });
    if (!labels.length) return;

    const ctx = document.getElementById('reportsChart');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels.reverse(),
            datasets: [{
                label: 'סה"כ סועדים',
                data: totalMeals.reverse(),
                borderColor: '#0f4c81',
                backgroundColor: 'rgba(17,138,178,0.18)',
                borderWidth: 2,
                fill: true,
                tension: 0.28,
                pointRadius: 2.5,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: true } },
            scales: {
                x: { grid: { display: false } },
                y: { beginAtZero: true }
            }
        }
    });
})();
</script>