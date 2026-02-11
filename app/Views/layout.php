<?php
$user = current_user();
?>
<!doctype html>
<html lang="he" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e($title ?? 'מערכת'); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-slate-100 text-slate-900">
    <div class="min-h-screen">
        <?php if ($user): ?>
            <nav class="bg-white border-b">
                <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between">
                    <div class="font-bold"><?php echo e($title ?? ''); ?></div>
                    <div class="text-sm">
                        <?php if ($user['role'] === 'SUPERADMIN'): ?>
                            <a class="ml-3" href="/sa/admins">מנהלים</a>
                            <a class="ml-3" href="/sa/stations">תחנות</a>
                            <a class="ml-3" href="/sa/users">משתמשים</a>
                            <a class="ml-3" href="/sa/settings">הגדרות</a>
                            <a class="ml-3" href="/sa/audit">לוג</a>
                        <?php elseif ($user['role'] === 'ADMIN'): ?>
                            <a class="ml-3" href="/admin/dashboard">דשבורד</a>
                            <a class="ml-3" href="/admin/stations">תחנות</a>
                            <a class="ml-3" href="/admin/users">משתמשים</a>
                            <a class="ml-3" href="/admin/menus">תפריטים</a>
                            <a class="ml-3" href="/admin/reports">דוחות</a>
                            <a class="ml-3" href="/admin/planner">תכנון</a>
                            <a class="ml-3" href="/admin/audit">לוג</a>
                        <?php else: ?>
                            <a class="ml-3" href="/station/today">היום</a>
                            <a class="ml-3" href="/station/history">היסטוריה</a>
                        <?php endif; ?>
                        <a class="ml-3 text-red-600" href="/logout">יציאה</a>
                    </div>
                </div>
            </nav>
        <?php endif; ?>

        <main class="max-w-6xl mx-auto p-4">
            <?php if (!empty($error)): ?>
                <div class="bg-red-100 text-red-800 p-3 rounded mb-4"><?php echo e($error); ?></div>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <div class="bg-green-100 text-green-800 p-3 rounded mb-4"><?php echo e($success); ?></div>
            <?php endif; ?>

            <?php require $viewFile; ?>
        </main>
    </div>
</body>
</html>