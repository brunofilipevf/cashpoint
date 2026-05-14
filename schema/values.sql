USE cashpoint;

-- =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
-- LEVEL VALUES
-- =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

INSERT INTO `level` (name, hierarchy)
VALUES
    ('Administrador', 1),
    ('Diretor', 2),
    ('Gerente', 3),
    ('Supervisor', 4);

-- =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
-- COMPANY VALUES
-- =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

INSERT INTO `company` (cpf, name)
VALUES
    ('08072308000669', 'Posto Cruzeiro VII'),
    ('08072308000316', 'Posto Cruzeiro IV');

-- =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
-- GROUP VALUES
-- =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

INSERT INTO `group` (name, multiplier_factor, is_active)
VALUES
    ('Funcionários', 1.5, 1),
    ('Motoristas de aplicativo', 1.5, 1),
    ('Atiaia Renovaveis', 1.3, 0);

-- =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
-- PRODUCT VALUES
-- =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

INSERT INTO `product` (name, barcode)
VALUES
    ('Refri Coca Cola Lata 350ml', '1234567890123'),
    ('Salg Coxinha de Frango', '3650');

-- =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
-- USER VALUES
-- =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

INSERT INTO `user` (username, password, fullname, level_id)
VALUES
    ('admin', '$2y$10$DYw2kb5QjPOeb5QLqBp6XutGq0QkF/dV9dB234bdZ6Oqr73.f3hvi',
        'Administrador do Sistema', 1),
    ('wladimir', '$2y$10$DYw2kb5QjPOeb5QLqBp6XutGq0QkF/dV9dB234bdZ6Oqr73.f3hvi',
        'Wladimir Neves da Costa', 3),
    ('bruno', '$2y$10$DYw2kb5QjPOeb5QLqBp6XutGq0QkF/dV9dB234bdZ6Oqr73.f3hvi',
        'Bruno Filipe Vasconcelos de Freitas', 4);

-- =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
-- CUSTOMER VALUES
-- =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

INSERT INTO `customer` (cpf, fullname, group_id)
VALUES
    ('11122233344', 'Ailly Mayane de Brito Nunes', 1),
    ('17823975315', NULL, NULL),
    ('22779955042', NULL, 2),
    ('15975324860', 'José Gilmar de Freitas', NULL);

-- =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
-- AWARD VALUES
-- =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

INSERT INTO `award` (
    name, product_id, required_points,
    max_redemptions_total, max_redemptions_per_customer, start_date, end_date)
VALUES (
    'Troque 800 pontos por uma Coca-Cola Lata 350ml',
    1, 800, 1000, 2, '2026-05-01', '2026-05-31');

-- =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
-- SCORE VALUES
-- =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=

INSERT INTO `score` (customer_id, user_id, base_points, multiplier_factor, final_points)
VALUES
    (1, 2, 100.00, 1, 100.00),
    (2, 3, 50.00, 1, 50.00);
