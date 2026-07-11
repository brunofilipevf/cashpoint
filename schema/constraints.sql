USE cashpoint;

-- =========================================================
-- CONSTRAINTS: USUÁRIOS
-- =========================================================

ALTER TABLE `user`
    ADD CONSTRAINT fk_user_level FOREIGN KEY (level_id) REFERENCES `level`(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    ADD CONSTRAINT fk_user_company FOREIGN KEY (company_id) REFERENCES `company`(id) ON DELETE RESTRICT ON UPDATE CASCADE;

-- =========================================================
-- CONSTRAINTS: CLIENTES
-- =========================================================

ALTER TABLE `customer`
    ADD CONSTRAINT fk_customer_group FOREIGN KEY (group_id) REFERENCES `group`(id) ON DELETE RESTRICT ON UPDATE CASCADE;

-- =========================================================
-- CONSTRAINTS: PREMIAÇÕES
-- =========================================================

ALTER TABLE `award`
    ADD CONSTRAINT chk_required_points_min CHECK (required_points >= 0.00),
    ADD CONSTRAINT fk_award_product FOREIGN KEY (product_id) REFERENCES `product`(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    ADD CONSTRAINT fk_award_group FOREIGN KEY (group_id) REFERENCES `group`(id) ON DELETE RESTRICT ON UPDATE CASCADE;

-- =========================================================
-- CONSTRAINTS: GRUPOS
-- =========================================================

ALTER TABLE `group`
    ADD CONSTRAINT chk_multiplier_factor_min CHECK (multiplier_factor >= 0.00);

-- =========================================================
-- CONSTRAINTS: PONTUAÇÕES
-- =========================================================

ALTER TABLE `score`
    ADD CONSTRAINT chk_score_base_points_min CHECK (base_points >= 0.00),
    ADD CONSTRAINT chk_score_multiplier_factor_min CHECK (multiplier_factor >= 0.00),
    ADD CONSTRAINT chk_score_final_points_min CHECK (final_points >= 0.00),
    ADD CONSTRAINT fk_score_customer FOREIGN KEY (customer_id) REFERENCES `customer`(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    ADD CONSTRAINT fk_score_user FOREIGN KEY (user_id) REFERENCES `user`(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    ADD CONSTRAINT fk_score_supply FOREIGN KEY (supply_id) REFERENCES `supply`(id) ON DELETE RESTRICT ON UPDATE CASCADE;

-- =========================================================
-- CONSTRAINTS: RESGATES
-- =========================================================

ALTER TABLE `redemption`
    ADD CONSTRAINT chk_redemption_points_used_min CHECK (points_used >= 0.00),
    ADD CONSTRAINT fk_redemption_customer FOREIGN KEY (customer_id) REFERENCES `customer`(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    ADD CONSTRAINT fk_redemption_award FOREIGN KEY (award_id) REFERENCES `award`(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    ADD CONSTRAINT fk_redemption_product FOREIGN KEY (product_id) REFERENCES `product`(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    ADD CONSTRAINT fk_redemption_user FOREIGN KEY (user_id) REFERENCES `user`(id) ON DELETE RESTRICT ON UPDATE CASCADE;

-- =========================================================
-- CONSTRAINTS: SESSÕES ATIVAS DE USUÁRIOS
-- =========================================================

ALTER TABLE `activity`
    ADD CONSTRAINT fk_activity_user FOREIGN KEY (user_id) REFERENCES `user`(id) ON DELETE RESTRICT ON UPDATE CASCADE;

-- =========================================================
-- CONSTRAINTS: ABASTECIMENTOS
-- =========================================================

ALTER TABLE `supply`
    ADD UNIQUE KEY unique_company_codigo (company_id, codigo),
    ADD CONSTRAINT chk_supply_quantidade CHECK (quantidade >= 0.00),
    ADD CONSTRAINT chk_supply_preco_unit CHECK (preco_unit >= 0.00),
    ADD CONSTRAINT chk_supply_valor CHECK (valor >= 0.00),
    ADD CONSTRAINT fk_supply_company FOREIGN KEY (company_id) REFERENCES `company`(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    ADD CONSTRAINT fk_supply_product FOREIGN KEY (product_id) REFERENCES `product`(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    ADD CONSTRAINT fk_supply_attendant FOREIGN KEY (attendant_id) REFERENCES `attendant`(id) ON DELETE RESTRICT ON UPDATE CASCADE;
