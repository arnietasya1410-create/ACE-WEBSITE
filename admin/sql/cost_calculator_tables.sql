-- Cost Calculator persistence tables
-- Run this in your `ace` database.

CREATE TABLE IF NOT EXISTS cost_calculator_records (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    calc_name VARCHAR(150) NOT NULL,
    created_by_admin_id INT UNSIGNED NULL,
    created_by_username VARCHAR(100) NOT NULL,
    participants DECIMAL(10,2) NOT NULL DEFAULT 0,
    suggested_fee DECIMAL(12,2) NOT NULL DEFAULT 0,
    profit_margin DECIMAL(5,2) NOT NULL DEFAULT 25,

    subtotal_a DECIMAL(14,2) NOT NULL DEFAULT 0,
    subtotal_b DECIMAL(14,2) NOT NULL DEFAULT 0,
    subtotal_c DECIMAL(14,2) NOT NULL DEFAULT 0,
    subtotal_d DECIMAL(14,2) NOT NULL DEFAULT 0,
    subtotal_e DECIMAL(14,2) NOT NULL DEFAULT 0,

    expected_total_expenses DECIMAL(14,2) NOT NULL DEFAULT 0,
    contingency DECIMAL(14,2) NOT NULL DEFAULT 0,
    subtotal_after_contingency DECIMAL(14,2) NOT NULL DEFAULT 0,
    management_service_charges DECIMAL(14,2) NOT NULL DEFAULT 0,
    subtotal_after_service_charges DECIMAL(14,2) NOT NULL DEFAULT 0,
    profit_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
    subtotal_after_profit_margin DECIMAL(14,2) NOT NULL DEFAULT 0,
    hrd_corp_charges DECIMAL(14,2) NOT NULL DEFAULT 0,
    subtotal_after_hrd_charges DECIMAL(14,2) NOT NULL DEFAULT 0,
    minimum_fee_per_participant DECIMAL(14,2) NOT NULL DEFAULT 0,
    minimum_participants_to_cover_cost INT UNSIGNED NOT NULL DEFAULT 0,

    calculation_payload LONGTEXT NULL,
    summary_payload LONGTEXT NULL,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    KEY idx_calc_created_at (created_at),
    KEY idx_calc_created_by (created_by_username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS cost_calculator_access_logs (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    record_id INT UNSIGNED NULL,
    admin_id INT UNSIGNED NULL,
    admin_username VARCHAR(100) NOT NULL,
    action_type ENUM('view','list','save') NOT NULL,
    ip_address VARCHAR(45) NULL,
    accessed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    KEY idx_access_record (record_id),
    KEY idx_access_admin (admin_username),
    KEY idx_accessed_at (accessed_at),
    CONSTRAINT fk_calc_access_record
        FOREIGN KEY (record_id) REFERENCES cost_calculator_records(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
