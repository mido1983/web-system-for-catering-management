-- schema.sql
SET NAMES utf8mb4;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('SUPERADMIN','ADMIN','STATION_USER') NOT NULL,
    admin_id INT NULL,
    station_id INT NULL,
    job_title VARCHAR(120) NULL,
    must_change_password TINYINT(1) NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    last_login_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE stations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    admin_id INT NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_stations_admin_id (admin_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE dishes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name_he VARCHAR(255) NOT NULL,
    category ENUM('PROTEIN','SIDE','SALAD','SOUP','BAKERY','OTHER') NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    owner_scope ENUM('GLOBAL','ADMIN') NOT NULL DEFAULT 'GLOBAL',
    owner_admin_id INT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_dishes_owner_admin_id (owner_admin_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE ingredients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name_he VARCHAR(255) NOT NULL,
    unit ENUM('GRAM','ML','UNIT') NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE recipes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    dish_id INT NOT NULL UNIQUE,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE recipe_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    recipe_id INT NOT NULL,
    ingredient_id INT NOT NULL,
    qty_per_portion INT NOT NULL,
    INDEX idx_recipe_items_recipe_id (recipe_id),
    INDEX idx_recipe_items_ingredient_id (ingredient_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE waste_reasons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name_he VARCHAR(255) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE menus (
    id INT AUTO_INCREMENT PRIMARY KEY,
    station_id INT NOT NULL,
    period_start DATE NOT NULL,
    period_end DATE NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_menus_station_period (station_id, period_start, period_end),
    INDEX idx_menus_station_period (station_id, period_start)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE menu_versions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    menu_id INT NOT NULL,
    version_number INT NOT NULL,
    status ENUM('DRAFT','PUBLISHED','ARCHIVED') NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    published_at DATETIME NULL,
    published_by_user_id INT NULL,
    INDEX idx_menu_versions_menu_id (menu_id),
    INDEX idx_menu_versions_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE menu_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    menu_version_id INT NOT NULL,
    dish_id INT NOT NULL,
    planned_portions INT NOT NULL,
    UNIQUE KEY uq_menu_items_version_dish (menu_version_id, dish_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE daily_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    station_id INT NOT NULL,
    date DATE NOT NULL,
    menu_version_id INT NOT NULL,
    sibus_ok INT NOT NULL,
    sibus_manual INT NOT NULL,
    detainees INT NOT NULL,
    comment TEXT NULL,
    submitted_at DATETIME NOT NULL,
    submitted_by_user_id INT NOT NULL,
    UNIQUE KEY uq_daily_reports_station_date (station_id, date),
    INDEX idx_daily_reports_station_date (station_id, date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE daily_waste_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    daily_report_id INT NOT NULL,
    dish_id INT NOT NULL,
    leftover_grams INT NOT NULL DEFAULT 0,
    thrown_grams INT NOT NULL DEFAULT 0,
    waste_reason_id INT NULL,
    note TEXT NULL,
    UNIQUE KEY uq_daily_waste_report_dish (daily_report_id, dish_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    key_name VARCHAR(120) NOT NULL UNIQUE,
    value_text TEXT NOT NULL,
    updated_at DATETIME NULL,
    updated_by_user_id INT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE audit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    actor_user_id INT NULL,
    action VARCHAR(80) NOT NULL,
    entity_type VARCHAR(80) NOT NULL,
    entity_id VARCHAR(80) NOT NULL,
    before_json MEDIUMTEXT NULL,
    after_json MEDIUMTEXT NULL,
    ip VARCHAR(64) NULL,
    user_agent VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_audit_actor_created (actor_user_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE stations
    ADD CONSTRAINT fk_stations_admin FOREIGN KEY (admin_id) REFERENCES users(id);

ALTER TABLE users
    ADD CONSTRAINT fk_users_admin FOREIGN KEY (admin_id) REFERENCES users(id),
    ADD CONSTRAINT fk_users_station FOREIGN KEY (station_id) REFERENCES stations(id);

ALTER TABLE dishes
    ADD CONSTRAINT fk_dishes_owner_admin FOREIGN KEY (owner_admin_id) REFERENCES users(id);

ALTER TABLE recipes
    ADD CONSTRAINT fk_recipes_dish FOREIGN KEY (dish_id) REFERENCES dishes(id);

ALTER TABLE recipe_items
    ADD CONSTRAINT fk_recipe_items_recipe FOREIGN KEY (recipe_id) REFERENCES recipes(id),
    ADD CONSTRAINT fk_recipe_items_ingredient FOREIGN KEY (ingredient_id) REFERENCES ingredients(id);

ALTER TABLE menus
    ADD CONSTRAINT fk_menus_station FOREIGN KEY (station_id) REFERENCES stations(id);

ALTER TABLE menu_versions
    ADD CONSTRAINT fk_menu_versions_menu FOREIGN KEY (menu_id) REFERENCES menus(id),
    ADD CONSTRAINT fk_menu_versions_publisher FOREIGN KEY (published_by_user_id) REFERENCES users(id);

ALTER TABLE menu_items
    ADD CONSTRAINT fk_menu_items_version FOREIGN KEY (menu_version_id) REFERENCES menu_versions(id),
    ADD CONSTRAINT fk_menu_items_dish FOREIGN KEY (dish_id) REFERENCES dishes(id);

ALTER TABLE daily_reports
    ADD CONSTRAINT fk_daily_reports_station FOREIGN KEY (station_id) REFERENCES stations(id),
    ADD CONSTRAINT fk_daily_reports_menu_version FOREIGN KEY (menu_version_id) REFERENCES menu_versions(id),
    ADD CONSTRAINT fk_daily_reports_user FOREIGN KEY (submitted_by_user_id) REFERENCES users(id);

ALTER TABLE daily_waste_items
    ADD CONSTRAINT fk_daily_waste_report FOREIGN KEY (daily_report_id) REFERENCES daily_reports(id),
    ADD CONSTRAINT fk_daily_waste_dish FOREIGN KEY (dish_id) REFERENCES dishes(id),
    ADD CONSTRAINT fk_daily_waste_reason FOREIGN KEY (waste_reason_id) REFERENCES waste_reasons(id);

ALTER TABLE settings
    ADD CONSTRAINT fk_settings_user FOREIGN KEY (updated_by_user_id) REFERENCES users(id);

ALTER TABLE audit_log
    ADD CONSTRAINT fk_audit_actor FOREIGN KEY (actor_user_id) REFERENCES users(id);

CREATE INDEX idx_menu_versions_menu_status ON menu_versions (menu_id, status);
CREATE INDEX idx_menus_station_period_start ON menus (station_id, period_start);
CREATE INDEX idx_daily_reports_station_date2 ON daily_reports (station_id, date);
