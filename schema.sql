CREATE DATABASE cashpoint
CHARACTER SET utf8mb4
COLLATE utf8mb4_0900_ai_ci;

USE cashpoint;

-- =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
-- LEVEL
-- =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

CREATE TABLE `level` (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(60) NOT NULL,
    hierarchy INT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL,
    CONSTRAINT chk_hierarchy_min
        CHECK (hierarchy >= 1)
);

INSERT INTO `level` (name, hierarchy)
VALUES
    ('Administrador', 1),
    ('Diretor', 2),
    ('Gerente', 3),
    ('Supervisor', 4);

-- =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
-- COMPANY
-- =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

CREATE TABLE `company` (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cpf VARCHAR(14) UNIQUE NOT NULL,
    name VARCHAR(60) NOT NULL,
    is_active TINYINT UNSIGNED NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL
);

INSERT INTO `company` (cpf, name)
VALUES
    ('08072308000669', 'Posto Cruzeiro VII'),
    ('08072308000316', 'Posto Cruzeiro IV');

-- =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
-- GROUP
-- =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

CREATE TABLE `group` (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(60) NOT NULL,
    multiplier_factor DECIMAL(10,2) NOT NULL,
    is_active TINYINT UNSIGNED NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL,
    CONSTRAINT chk_multiplier_factor_min
        CHECK (multiplier_factor >= 0.01)
);

INSERT INTO `group` (name, multiplier_factor, is_active)
VALUES
    ('Funcionários', 1.5, 1),
    ('Motoristas de aplicativo', 1.5, 1),
    ('Atiaia Renovaveis', 1.3, 0);

-- =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
-- PRODUCT
-- =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

CREATE TABLE `product` (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(60) NOT NULL,
    barcode VARCHAR(13) UNIQUE NOT NULL,
    is_active TINYINT UNSIGNED NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL
);

INSERT INTO `product` (name, barcode)
VALUES
    ('Refri Coca Cola Lata 350ml', '1234567890123'),
    ('Salg Coxinha de Frango', '3650');

-- =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
-- USER
-- =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

CREATE TABLE `user` (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(60) UNIQUE NOT NULL,
    password VARCHAR(60) NOT NULL,
    fullname VARCHAR(60) NOT NULL,
    level_id INT UNSIGNED NOT NULL,
    company_id INT UNSIGNED DEFAULT NULL,
    is_active TINYINT UNSIGNED NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL,
    CONSTRAINT fk_user_level
        FOREIGN KEY (level_id)
        REFERENCES `level`(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,
    CONSTRAINT fk_user_company
        FOREIGN KEY (company_id)
        REFERENCES `company`(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
);

INSERT INTO `user` (username, password, fullname, level_id)
VALUES
    ('admin', '$2y$10$DYw2kb5QjPOeb5QLqBp6XutGq0QkF/dV9dB234bdZ6Oqr73.f3hvi', 'Administrador do Sistema', 1),
    ('wladimir', '$2y$10$DYw2kb5QjPOeb5QLqBp6XutGq0QkF/dV9dB234bdZ6Oqr73.f3hvi', 'Wladimir Neves da Costa', 3),
    ('bruno', '$2y$10$DYw2kb5QjPOeb5QLqBp6XutGq0QkF/dV9dB234bdZ6Oqr73.f3hvi', 'Bruno Filipe Vasconcelos de Freitas', 4);

-- =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
-- CUSTOMER
-- =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

CREATE TABLE `customer` (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cpf VARCHAR(14) UNIQUE NOT NULL,
    fullname VARCHAR(60) DEFAULT NULL,
    email VARCHAR(254) UNIQUE DEFAULT NULL,
    phone VARCHAR(11) UNIQUE DEFAULT NULL,
    group_id INT UNSIGNED DEFAULT NULL,
    is_active TINYINT UNSIGNED NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL,
    CONSTRAINT fk_customer_group
        FOREIGN KEY (group_id)
        REFERENCES `group`(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
);

INSERT INTO `customer` (cpf, fullname, group_id)
VALUES
    ('11122233344', 'Ailly Mayane de Brito Nunes', 1),
    ('17823975315', NULL, NULL),
    ('22779955042', NULL, 2),
    ('15975324860', 'José Gilmar de Freitas', NULL);

-- =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
-- AWARD
-- =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

CREATE TABLE `award` (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(60) NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    required_points DECIMAL(10,2) NOT NULL,
    max_redemptions_total INT UNSIGNED NOT NULL,
    max_redemptions_per_customer INT UNSIGNED NOT NULL,
    start_date DATETIME NOT NULL,
    end_date DATETIME NOT NULL,
    is_active TINYINT UNSIGNED NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL,
    CONSTRAINT chk_required_points_min
        CHECK (required_points >= 0.01),
    CONSTRAINT chk_max_redemptions_total_min
        CHECK (max_redemptions_total >= 1),
    CONSTRAINT chk_max_redemptions_per_customer_min
        CHECK (max_redemptions_per_customer >= 1),
    CONSTRAINT fk_award_product
        FOREIGN KEY (product_id)
        REFERENCES `product`(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
);

DELIMITER //
    CREATE TRIGGER trg_award_normalize_dates_insert
    BEFORE INSERT ON `award`
    FOR EACH ROW
    BEGIN
        SET NEW.start_date = DATE(NEW.start_date);
        SET NEW.end_date = DATE(NEW.end_date) + INTERVAL 1 DAY - INTERVAL 1 SECOND;
    END//

    CREATE TRIGGER trg_award_normalize_dates_update
    BEFORE UPDATE ON `award`
    FOR EACH ROW
    BEGIN
        SET NEW.start_date = DATE(NEW.start_date);
        SET NEW.end_date = DATE(NEW.end_date) + INTERVAL 1 DAY - INTERVAL 1 SECOND;
    END//
DELIMITER ;

INSERT INTO `award` (name, product_id, required_points, max_redemptions_total, max_redemptions_per_customer, start_date, end_date)
VALUES ('Troque 800 pontos por uma Coca-Cola Lata 350ml', 1, 800, 1000, 2, '2026-05-01', '2026-05-31');
