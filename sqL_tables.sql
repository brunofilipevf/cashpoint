CREATE DATABASE cashpoint
CHARACTER SET utf8mb4
COLLATE utf8mb4_0900_ai_ci;

USE cashpoint;

-- =========================================================
-- NÍVEIS
-- =========================================================

CREATE TABLE `level` (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(60) NOT NULL,
    hierarchy TINYINT UNSIGNED UNIQUE NOT NULL
) ENGINE=InnoDB;

INSERT INTO `level` (name, hierarchy)
VALUES ('Administrador', 255), ('Diretor', 4), ('Gerente', 3), ('Supervisor', 2), ('Operador', 1);

-- =========================================================
-- EMPRESAS
-- =========================================================

CREATE TABLE `company` (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cpf VARCHAR(14) UNIQUE NOT NULL,
    name VARCHAR(60) NOT NULL,
    is_active TINYINT UNSIGNED NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL
) ENGINE=InnoDB;

INSERT INTO `company` (cpf, name)
VALUES ('08072308000669', 'Posto Cruzeiro VII');

-- =========================================================
-- USUÁRIOS
-- =========================================================

CREATE TABLE `user` (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(60) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    fullname VARCHAR(60) NOT NULL,
    level_id INT UNSIGNED NOT NULL,
    company_id INT UNSIGNED DEFAULT NULL,
    is_active TINYINT UNSIGNED NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL,
    CONSTRAINT fk_user_level FOREIGN KEY (level_id) REFERENCES `level`(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_user_company FOREIGN KEY (company_id) REFERENCES `company`(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

INSERT INTO `user` (username, password, fullname, level_id)
VALUES ('admin', '$2y$10$DYw2kb5QjPOeb5QLqBp6XutGq0QkF/dV9dB234bdZ6Oqr73.f3hvi', 'Administrador do Sistema', 1);

-- =========================================================
-- GRUPOS
-- =========================================================

CREATE TABLE `group` (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(60) NOT NULL,
    multiplier_factor DECIMAL(10,2) NOT NULL DEFAULT 1.00,
    is_active TINYINT UNSIGNED NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL,
    CONSTRAINT chk_multiplier_factor_min CHECK (multiplier_factor >= 0.00)
) ENGINE=InnoDB;

INSERT INTO `group` (name, multiplier_factor, is_active)
VALUES ('Funcionários', 1.5, 1), ('Motoristas de aplicativo', 1.5, 1), ('Atiaia Renovaveis', 1.3, 0);

-- =========================================================
-- CLIENTES
-- =========================================================

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
    CONSTRAINT fk_customer_group FOREIGN KEY (group_id) REFERENCES `group`(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

INSERT INTO `customer` (cpf, fullname, group_id)
VALUES ('11122233344', 'Ailly Mayane de Brito Nunes', 1), ('12345678910', NULL, NULL);

-- =========================================================
-- PRODUTOS
-- =========================================================

CREATE TABLE `product` (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(60) NOT NULL,
    barcode VARCHAR(13) UNIQUE NOT NULL,
    is_active TINYINT UNSIGNED NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL
) ENGINE=InnoDB;

INSERT INTO `product` (name, barcode)
VALUES ('Refri Coca Cola Lata 350ml', '7894900010015'), ('Salg Coxinha de Frango Cruzeiro', '1427');

-- =========================================================
-- PREMIAÇÕES
-- =========================================================

CREATE TABLE `award` (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(60) NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    required_points DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    max_redemption_total INT UNSIGNED NOT NULL DEFAULT 0,
    max_redemption_per_customer INT UNSIGNED NOT NULL DEFAULT 0,
    group_id INT UNSIGNED DEFAULT NULL,
    start_date DATETIME NOT NULL,
    end_date DATETIME NOT NULL,
    is_active TINYINT UNSIGNED NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL,
    CONSTRAINT chk_required_points_min CHECK (required_points >= 0.00),
    CONSTRAINT fk_award_product FOREIGN KEY (product_id) REFERENCES `product`(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_award_group FOREIGN KEY (group_id) REFERENCES `group`(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

INSERT INTO `award` (name, product_id, required_points, max_redemption_total, max_redemption_per_customer, start_date, end_date)
VALUES ('Troque 800 pontos por uma Coca-Cola Lata 350ml', 1, 800.00, 1000, 2, '2026-07-01 00:00:00', '2026-07-31 23:59:59');

-- =========================================================
-- PONTUAÇÕES
-- =========================================================

CREATE TABLE `score` (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    transaction_code CHAR(16) UNIQUE NOT NULL,
    customer_id INT UNSIGNED NOT NULL,
    base_points DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    multiplier_factor DECIMAL(10,2) NOT NULL DEFAULT 1.00,
    final_points DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    user_id INT UNSIGNED DEFAULT NULL,
    is_manual TINYINT UNSIGNED NOT NULL DEFAULT 1,
    company_id INT UNSIGNED DEFAULT NULL,
    supply_code INT UNSIGNED DEFAULT NULL,
    supply_json JSON DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT chk_score_base_points_min CHECK (base_points >= 0.00),
    CONSTRAINT chk_score_multiplier_factor_min CHECK (multiplier_factor >= 0.00),
    CONSTRAINT chk_score_final_points_min CHECK (final_points >= 0.00),
    CONSTRAINT fk_score_customer FOREIGN KEY (customer_id) REFERENCES `customer`(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_score_user FOREIGN KEY (user_id) REFERENCES `user`(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_score_company FOREIGN KEY (company_id) REFERENCES `company`(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT uq_score_company_supply UNIQUE (company_id, supply_code)
) ENGINE=InnoDB;

-- =========================================================
-- RESGATES
-- =========================================================

CREATE TABLE `redemption` (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    transaction_code CHAR(16) UNIQUE NOT NULL,
    customer_id INT UNSIGNED NOT NULL,
    award_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    points_used DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    user_id INT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT chk_redemption_points_used_min CHECK (points_used >= 0.00),
    CONSTRAINT fk_redemption_customer FOREIGN KEY (customer_id) REFERENCES `customer`(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_redemption_award FOREIGN KEY (award_id) REFERENCES `award`(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_redemption_product FOREIGN KEY (product_id) REFERENCES `product`(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_redemption_user FOREIGN KEY (user_id) REFERENCES `user`(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

-- =========================================================
-- SESSÕES ATIVAS DE USUÁRIOS
-- =========================================================

CREATE TABLE `activity` (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    token CHAR(64) UNIQUE NOT NULL,
    ip VARCHAR(45) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL,
    revoked_at DATETIME DEFAULT NULL,
    CONSTRAINT fk_activity_user FOREIGN KEY (user_id) REFERENCES `user`(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;
