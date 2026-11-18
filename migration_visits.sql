-- Run this once if you already imported database.sql earlier
USE portfolio_db;

CREATE TABLE IF NOT EXISTS visits (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    visitor_hash CHAR(64) NOT NULL,
    page VARCHAR(40) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (created_at),
    INDEX (page)
) ENGINE=InnoDB;
