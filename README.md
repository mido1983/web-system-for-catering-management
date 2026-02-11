# מערכת ניהול הסעדה – הוראות התקנה

## דרישות
- PHP 8.x
- MySQL
- Apache/LiteSpeed

## התקנה
1. העלה את כל הקבצים לשרת (FTP).
2. צור בסיס נתונים חדש בממשק של חברת האחסון.
3. ייבא את `database/schema.sql` ולאחר מכן את `database/seed.sql`.
4. ערוך את `app/config.php` והכנס פרטי DB.
5. ודא שקיימת הרשאת כתיבה לתיקיית `logs/`.

## התחברות ראשונית
- אימייל: `superadmin@example.com`
- סיסמה זמנית: `password`

בעת ההתחברות הראשונה תידרש החלפת סיסמה.

## הערות אבטחה
- מומלץ לעבוד תמיד על HTTPS ולהגדיר `session.secure` ל־`true` בקובץ `app/config.php`.
- אין לחשוף את התיקיות `app/`, `database/`, `logs/` מה־web root.

## זרימת עבודה בסיסית
1. SuperAdmin יוצר Admin.
2. SuperAdmin יוצר תחנה ומשייך ל־Admin.
3. Admin יוצר משתמש תחנה עם סיסמה זמנית.
4. Admin יוצר תפריט ומפרסם.
5. Station User ממלא דוח יומי במסך "היום".
6. Admin צופה בדוחות ובתכנון הייצור.