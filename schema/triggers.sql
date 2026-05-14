USE cashpoint;

DELIMITER //

-- =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
-- AWARD TRIGGERS
-- =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

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

CREATE TRIGGER trg_award_prevent_product_change
BEFORE UPDATE ON `award`
FOR EACH ROW
BEGIN
    DECLARE redemption_count INT;

    IF NEW.product_id != OLD.product_id THEN
        SELECT COUNT(id) INTO redemption_count
        FROM `redemption`
        WHERE award_id = OLD.id;

        IF redemption_count > 0 THEN
            SIGNAL SQLSTATE '45001'
            SET MESSAGE_TEXT = 'It is not possible to change the product of a prize with redemptions.';
        END IF;
    END IF;
END//

DELIMITER ;
