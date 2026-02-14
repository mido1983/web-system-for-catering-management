<div class="max-w-md mx-auto bg-white p-6 rounded shadow">
    <h1 class="text-2xl font-bold mb-4">התחברות</h1>
    <form method="post">
        <?php echo csrf_field(); ?>
        <label class="block mb-2">אימייל</label>
        <input class="w-full border p-2 mb-4" type="email" name="email" required>
        <label class="block mb-2">סיסמה</label>
        <input class="w-full border p-2 mb-4" type="password" name="password" required>
        <button class="w-full bg-blue-600 text-white py-2 rounded">התחבר</button>
    </form>
</div>