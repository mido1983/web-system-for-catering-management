-- seed.sql
SET NAMES utf8mb4;

INSERT INTO users (email, password_hash, role, admin_id, station_id, must_change_password, is_active, created_at)
VALUES ('superadmin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'SUPERADMIN', NULL, NULL, 1, 1, NOW());

INSERT INTO waste_reasons (name_he, is_active) VALUES
('הוגש יותר מדי', 1),
('הזמנה לא מדויקת', 1),
('בעיית איכות', 1),
('שינוי בכמות עצורים', 1),
('אחר', 1);

INSERT INTO settings (key_name, value_text) VALUES
('deadline_time', '20:00'),
('polling_seconds', '60'),
('weight_step_grams', '100'),
('app_name_he', 'מערכת ניהול הסעדה'),
('support_phone', '');