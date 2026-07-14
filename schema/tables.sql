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

-- =========================================================
-- PONTUAÇÕES
-- =========================================================

CREATE TABLE `score` (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    transaction_code CHAR(32) UNIQUE NOT NULL,
    customer_id INT UNSIGNED NOT NULL,
    base_points DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    multiplier_factor DECIMAL(10,2) NOT NULL DEFAULT 1.00,
    final_points DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    user_id INT UNSIGNED NOT NULL,
    is_manual TINYINT UNSIGNED NOT NULL DEFAULT 1,
    supply_id INT UNSIGNED UNIQUE DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT chk_score_base_points_min CHECK (base_points >= 0.00),
    CONSTRAINT chk_score_multiplier_factor_min CHECK (multiplier_factor >= 0.00),
    CONSTRAINT chk_score_final_points_min CHECK (final_points >= 0.00),
    CONSTRAINT fk_score_customer FOREIGN KEY (customer_id) REFERENCES `customer`(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_score_user FOREIGN KEY (user_id) REFERENCES `user`(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

-- =========================================================
-- RESGATES
-- =========================================================

CREATE TABLE `redemption` (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    transaction_code CHAR(32) UNIQUE NOT NULL,
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

-- =========================================================
-- FRENTISTAS
-- =========================================================

CREATE TABLE `attendant` (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    rfid CHAR(16) UNIQUE NOT NULL,
    fullname VARCHAR(60) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =========================================================
-- ABASTECIMENTOS
-- =========================================================

CREATE TABLE `supply` (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id INT UNSIGNED NOT NULL,
    codigo INT UNSIGNED NOT NULL,
    bico TINYINT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    quantidade DECIMAL(10,3) NOT NULL DEFAULT 0.00,
    preco_unit DECIMAL(10,3) NOT NULL DEFAULT 0.00,
    valor DECIMAL(10,3) NOT NULL DEFAULT 0.00,
    hora DATETIME NOT NULL,
    attendant_id INT UNSIGNED NOT NULL,
    ip VARCHAR(45) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT chk_supply_quantidade CHECK (quantidade >= 0.00),
    CONSTRAINT chk_supply_preco_unit CHECK (preco_unit >= 0.00),
    CONSTRAINT chk_supply_valor CHECK (valor >= 0.00),
    CONSTRAINT fk_supply_company FOREIGN KEY (company_id) REFERENCES `company`(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_supply_product FOREIGN KEY (product_id) REFERENCES `product`(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_supply_attendant FOREIGN KEY (attendant_id) REFERENCES `attendant`(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    UNIQUE KEY unique_company_codigo (company_id, codigo)
) ENGINE=InnoDB;
