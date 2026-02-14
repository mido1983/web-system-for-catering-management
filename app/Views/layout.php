<?php
$user = current_user();
?>
<!doctype html>
<html lang="he" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e($title ?? 'מערכת'); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/gridjs/dist/theme/mermaid.min.css" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.6/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/luxon@3.5.0/build/global/luxon.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-luxon@1.3.1/dist/chartjs-adapter-luxon.umd.min.js"></script>
    <script src="https://unpkg.com/gridjs/dist/gridjs.umd.js"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        :root {
            --bg: #eef2f7;
            --ink: #0f172a;
            --muted: #64748b;
            --surface: #ffffff;
            --surface-soft: #f8fafc;
            --line: #dbe4ef;
            --brand: #0f4c81;
            --brand-2: #118ab2;
            --danger: #dc2626;
            --ok: #15803d;
            --ring: 0 0 0 3px rgba(17,138,178,.22);
            --shadow: 0 10px 25px rgba(15, 23, 42, .08);
            --radius: 14px;
        }
        body {
            font-family: 'Heebo', sans-serif;
            color: var(--ink);
            background:
                radial-gradient(1200px 400px at 80% -50px, rgba(17,138,178,.16), transparent 65%),
                radial-gradient(900px 300px at 10% -100px, rgba(15,76,129,.14), transparent 65%),
                var(--bg);
        }
        .shell { max-width: 1680px; margin-inline: auto; }
        .surface { background: var(--surface); border: 1px solid var(--line); border-radius: var(--radius); box-shadow: var(--shadow); }
        .input-modern, .select-modern, .textarea-modern {
            width: 100%; border: 1px solid var(--line); background: #fff; border-radius: 12px; padding: .78rem .95rem;
        }
        .input-modern:focus, .select-modern:focus, .textarea-modern:focus { outline: none; box-shadow: var(--ring); border-color: #60a5fa; }
        .btn-primary { background: linear-gradient(135deg, var(--brand), var(--brand-2)); color: #fff; border-radius: 12px; padding: .78rem 1.1rem; font-weight: 700; }
        .btn-primary:hover { filter: brightness(1.05); }
        .btn-neutral { background: #334155; color: #fff; border-radius: 12px; padding: .62rem 1rem; }
        .btn-danger { background: var(--danger); color: #fff; border-radius: 12px; padding: .62rem 1rem; }
        .badge { display:inline-flex; align-items:center; border-radius:999px; padding:.12rem .55rem; font-size:.78rem; font-weight:700; }
        .badge-ok { background:#dcfce7; color:#166534; }
        .badge-off { background:#fee2e2; color:#991b1b; }
        .nav-pill { border: 1px solid var(--line); background: #fff; border-radius: 999px; padding: .32rem .85rem; font-size: .9rem; }
        .nav-pill:hover { background: var(--surface-soft); }
    </style>
</head>
<body>
    <div class="min-h-screen">
        <?php if ($user): ?>
            <header class="sticky top-0 z-30 border-b border-slate-200/80 bg-white/85 backdrop-blur">
                <div class="shell px-4 py-3 flex flex-wrap items-center justify-between gap-2">
                    <div class="text-xl font-extrabold tracking-tight"><?php echo e($title ?? ''); ?></div>
                    <nav class="flex flex-wrap items-center gap-2">
                        <?php if ($user['role'] === 'SUPERADMIN'): ?>
                            <a class="nav-pill" href="<?php echo e(app_url('/sa/admins')); ?>">מנהלים</a>
                            <a class="nav-pill" href="<?php echo e(app_url('/sa/stations')); ?>">תחנות</a>
                            <a class="nav-pill" href="<?php echo e(app_url('/sa/users')); ?>">משתמשים</a>
                            <a class="nav-pill" href="<?php echo e(app_url('/sa/settings')); ?>">הגדרות</a>
                            <a class="nav-pill" href="<?php echo e(app_url('/sa/audit')); ?>">לוג</a>
                        <?php elseif (in_array($user['role'], ['ADMIN', 'STATION_MANAGER'], true)): ?>
                            <a class="nav-pill" href="<?php echo e(app_url('/admin/dashboard')); ?>">דשבורד</a>
                            <a class="nav-pill" href="<?php echo e(app_url('/admin/stations')); ?>">תחנות</a>
                            <a class="nav-pill" href="<?php echo e(app_url('/admin/users')); ?>">משתמשים</a>
                            <a class="nav-pill" href="<?php echo e(app_url('/admin/menus')); ?>">תפריטים</a>
                            <a class="nav-pill" href="<?php echo e(app_url('/admin/reports')); ?>">דוחות</a>
                            <a class="nav-pill" href="<?php echo e(app_url('/admin/planner')); ?>">תכנון</a>
                            <a class="nav-pill" href="<?php echo e(app_url('/admin/audit')); ?>">לוג</a>
                        <?php elseif (in_array($user['role'], ['DISTRICT_MANAGER', 'AREA_MANAGER'], true)): ?>
                            <a class="nav-pill" href="<?php echo e(app_url('/sa/users')); ?>">משתמשים</a>
                        <?php else: ?>
                            <a class="nav-pill" href="<?php echo e(app_url('/station/today')); ?>">היום</a>
                            <a class="nav-pill" href="<?php echo e(app_url('/station/history')); ?>">היסטוריה</a>
                        <?php endif; ?>
                        <a class="nav-pill text-red-700 border-red-200" href="<?php echo e(app_url('/logout')); ?>">יציאה</a>
                    </nav>
                </div>
            </header>
        <?php endif; ?>

        <main class="shell px-8 py-8">
            <?php if (!empty($error)): ?>
                <div class="surface mb-4 p-3 text-red-800 bg-red-50 border-red-200"><?php echo e($error); ?></div>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <div class="surface mb-4 p-3 text-green-800 bg-green-50 border-green-200"><?php echo e($success); ?></div>
            <?php endif; ?>

            <?php require $viewFile; ?>
        </main>
    </div>
</body>
</html>
