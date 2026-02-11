<div class="max-w-md mx-auto bg-white p-6 rounded shadow">
    <h1 class="text-2xl font-bold mb-4">החלפת סיסמה</h1>
    <form method="post">
        <?php echo csrf_field(); ?>
        <label class="block mb-2">סיסמה חדשה</label>
        <input class="w-full border p-2 mb-4" type="password" name="password" required>
        <label class="block mb-2">אימות סיסמה</label>
        <input class="w-full border p-2 mb-4" type="password" name="password2" required>
        <button class="w-full bg-blue-600 text-white py-2 rounded">שמור</button>
    </form>
</div>