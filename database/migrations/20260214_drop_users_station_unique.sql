ALTER TABLE users ADD INDEX idx_users_station_id (station_id);
ALTER TABLE users DROP INDEX uq_users_station_id;
