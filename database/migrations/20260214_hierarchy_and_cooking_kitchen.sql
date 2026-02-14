ALTER TABLE users
    MODIFY role ENUM('SUPERADMIN','DISTRICT_MANAGER','AREA_MANAGER','STATION_MANAGER','ADMIN','STATION_USER') NOT NULL;

ALTER TABLE stations
    ADD COLUMN is_cooking_kitchen TINYINT(1) NOT NULL DEFAULT 0 AFTER is_active;

CREATE TABLE IF NOT EXISTS station_supply_links (
    id INT AUTO_INCREMENT PRIMARY KEY,
    source_station_id INT NOT NULL,
    target_station_id INT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_station_supply_pair (source_station_id, target_station_id),
    INDEX idx_station_supply_source (source_station_id),
    INDEX idx_station_supply_target (target_station_id),
    CONSTRAINT fk_station_supply_source FOREIGN KEY (source_station_id) REFERENCES stations(id) ON DELETE CASCADE,
    CONSTRAINT fk_station_supply_target FOREIGN KEY (target_station_id) REFERENCES stations(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
