USE cashpoint;

-- =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
-- GROUP CONSTRAINTS
-- =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

ALTER TABLE `group`
ADD CONSTRAINT chk_multiplier_factor_min
CHECK (multiplier_factor >= 0);

-- =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
-- USER CONSTRAINTS
-- =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

ALTER TABLE `user`
ADD CONSTRAINT fk_user_level
FOREIGN KEY (level_id)
REFERENCES `level`(id)
ON DELETE RESTRICT
ON UPDATE CASCADE;

ALTER TABLE `user`
ADD CONSTRAINT fk_user_company
FOREIGN KEY (company_id)
REFERENCES `company`(id)
ON DELETE RESTRICT
ON UPDATE CASCADE;

-- =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
-- CUSTOMER CONSTRAINTS
-- =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

ALTER TABLE `customer`
ADD CONSTRAINT fk_customer_group
FOREIGN KEY (group_id)
REFERENCES `group`(id)
ON DELETE RESTRICT
ON UPDATE CASCADE;

-- =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
-- AWARD CONSTRAINTS
-- =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

ALTER TABLE `award`
ADD CONSTRAINT chk_required_points_min
CHECK (required_points >= 0);

ALTER TABLE `award`
ADD CONSTRAINT fk_award_product
FOREIGN KEY (product_id)
REFERENCES `product`(id)
ON DELETE RESTRICT
ON UPDATE CASCADE;

-- =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
-- SCORE CONSTRAINTS
-- =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

ALTER TABLE `score`
ADD CONSTRAINT chk_score_base_points_min
CHECK (base_points >= 0);

ALTER TABLE `score`
ADD CONSTRAINT chk_score_multiplier_factor_min
CHECK (multiplier_factor >= 0);

ALTER TABLE `score`
ADD CONSTRAINT chk_score_final_points_min
CHECK (final_points >= 0);

ALTER TABLE `score`
ADD CONSTRAINT fk_score_customer
FOREIGN KEY (customer_id)
REFERENCES `customer`(id)
ON DELETE RESTRICT
ON UPDATE CASCADE;

ALTER TABLE `score`
ADD CONSTRAINT fk_score_user
FOREIGN KEY (user_id)
REFERENCES `user`(id)
ON DELETE RESTRICT
ON UPDATE CASCADE;

-- =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
-- REDEMPTION CONSTRAINTS
-- =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

ALTER TABLE `redemption`
ADD CONSTRAINT chk_redemption_points_used_min
CHECK (points_used >= 0);

ALTER TABLE `redemption`
ADD CONSTRAINT fk_redemption_customer
FOREIGN KEY (customer_id)
REFERENCES `customer`(id)
ON DELETE RESTRICT
ON UPDATE CASCADE;

ALTER TABLE `redemption`
ADD CONSTRAINT fk_redemption_award
FOREIGN KEY (award_id)
REFERENCES `award`(id)
ON DELETE RESTRICT
ON UPDATE CASCADE;

ALTER TABLE `redemption`
ADD CONSTRAINT fk_redemption_product
FOREIGN KEY (product_id)
REFERENCES `product`(id)
ON DELETE RESTRICT
ON UPDATE CASCADE;

ALTER TABLE `redemption`
ADD CONSTRAINT fk_redemption_user
FOREIGN KEY (user_id)
REFERENCES `user`(id)
ON DELETE RESTRICT
ON UPDATE CASCADE;
