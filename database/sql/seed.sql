-- TS Screen seed Phase 0–5 (chạy SAU khi đã có bảng).
-- phpMyAdmin: chọn database db_d26589bb rồi Import file này.
-- CLI: mysql -u USER -p db_d26589bb < database/sql/seed.sql
--
-- BẮT BUỘC: sửa API_SERVER thành URL public (https://..., không slash cuối).
--
-- Tài khoản demo
--   Admin app:  username admin / mật khẩu admin123  (app gửi MD5, DB = bcrypt của MD5)
--   Phone/TV:   customer@tsscreen.local / 123456
--
-- Phase 3–5 không thêm cột. Seed thêm:
--   1 dir demo, 1 đơn Gói cơ bản pay=1 (2 TV / 1GB) để CreateDevice + upload chạy được.
--   Không seed device / campaign / file (TV tự pairing, phone tự tạo camp).

USE `db_d26589bb`;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_08_20_000001_create_tb_tables', 1),
(5, '2026_08_20_000002_add_limits_to_tb_orders', 1)
ON DUPLICATE KEY UPDATE `migration` = VALUES(`migration`);

-- Đổi dòng API_SERVER trước khi import, hoặc UPDATE sau:
-- UPDATE tb_configs SET config_value = 'https://DOMAIN-CUA-BAN' WHERE config_key = 'API_SERVER';
INSERT INTO `tb_configs` (`config_key`, `config_value`) VALUES
('COMPANY_NAME', 'TS Screen'),
('COMPANY_ADDRESS', ''),
('HOTLINE', ''),
('REPRESENTATIVE', ''),
('EMAIL', 'hello@example.com'),
('TAX_CODE', ''),
('API_SERVER', 'https://YOUR-PUBLIC-HOST'),
('GUIDE_LINK', ''),
('ACTIVE_FLAG', '1'),
('show_payment', '1'),
('statement_date', '1'),
('APPUSERANDROID_VERSION', ''),
('APPUSERANDROID_BUILD_DATE', ''),
('APPUSERANDROID_UPDATE_URL', ''),
('APPUSERIOS_VERSION', ''),
('APPUSERIOS_BUILD_DATE', ''),
('APPUSERIOS_UPDATE_URL', ''),
('APPTVBOX_VERSION', ''),
('APPTVBOX_BUILD_DATE', ''),
('APPTVBOX_UPDATE_URL', ''),
('APPADMINANDROID_VERSION', ''),
('APPADMINANDROID_BUILD_DATE', ''),
('APPADMINANDROID_UPDATE_URL', ''),
('APPADMINIOS_VERSION', ''),
('APPADMINIOS_BUILD_DATE', ''),
('APPADMINIOS_UPDATE_URL', ''),
('VIETQR_BANK_BIN', ''),
('VIETQR_ACCOUNT', ''),
('VIETQR_ACCOUNT_NAME', 'TS Screen')
ON DUPLICATE KEY UPDATE `config_value` = VALUES(`config_value`);

INSERT INTO `tb_accounts` (`username`, `password`, `email`, `phone_number`, `user_type`, `deleted`, `created_date`) VALUES
('admin', '$2y$12$U1uK5DSkKC557gWp91Q.xexmKA8LGbGBgDPn8wA1Djh7ZIYl4GKne', 'admin@tsscreen.local', '', '1', 'n', NOW())
ON DUPLICATE KEY UPDATE
  `password` = VALUES(`password`),
  `email` = VALUES(`email`),
  `user_type` = VALUES(`user_type`),
  `deleted` = 'n';

INSERT INTO `tb_users` (`customer_name`, `phone_number`, `email`, `password`, `customer_token`, `login_with`, `status`, `deleted`, `created_date`) VALUES
('Demo Customer', '0900000000', 'customer@tsscreen.local', '$2y$12$/Hwi2J4HaP2o0ETEmwfKhem/WLwUWL8ucZBh0t5aG9qbJht/I5rsi', '01demo0customer0token0tsscreen', 'email', 'y', 'n', NOW())
ON DUPLICATE KEY UPDATE
  `customer_name` = VALUES(`customer_name`),
  `password` = VALUES(`password`),
  `status` = 'y',
  `deleted` = 'n';

INSERT INTO `tb_packets` (
  `name_packet`, `price`, `price_6_month`, `price_12_month`,
  `day_qty`, `month_qty`, `year_qty`, `is_trial`, `is_business`,
  `detail`, `description`, `picture`, `limit_qty`, `limit_capacity`, `deleted`, `created_date`
)
SELECT * FROM (
  SELECT 'Gói dùng thử' AS name_packet, '0' AS price, '0' AS price_6_month, '0' AS price_12_month,
    '7' AS day_qty, '0' AS month_qty, '0' AS year_qty, '1' AS is_trial, '0' AS is_business,
    'Dùng thử 7 ngày, 1 TV' AS detail, 'Gói dùng thử' AS description, '' AS picture,
    '1' AS limit_qty, '104857600' AS limit_capacity, 'n' AS deleted, NOW() AS created_date
) AS x
WHERE NOT EXISTS (SELECT 1 FROM `tb_packets` p WHERE p.name_packet = 'Gói dùng thử');

INSERT INTO `tb_packets` (
  `name_packet`, `price`, `price_6_month`, `price_12_month`,
  `day_qty`, `month_qty`, `year_qty`, `is_trial`, `is_business`,
  `detail`, `description`, `picture`, `limit_qty`, `limit_capacity`, `deleted`, `created_date`
)
SELECT * FROM (
  SELECT 'Gói cơ bản', '99000', '499000', '899000',
    '0', '1', '0', '0', '0',
    '2 TV, 1GB media', 'Gói cơ bản', '',
    '2', '1073741824', 'n', NOW()
) AS x
WHERE NOT EXISTS (SELECT 1 FROM `tb_packets` p WHERE p.name_packet = 'Gói cơ bản');

INSERT INTO `tb_packets` (
  `name_packet`, `price`, `price_6_month`, `price_12_month`,
  `day_qty`, `month_qty`, `year_qty`, `is_trial`, `is_business`,
  `detail`, `description`, `picture`, `limit_qty`, `limit_capacity`, `deleted`, `created_date`
)
SELECT * FROM (
  SELECT 'Gói doanh nghiệp', '299000', '1599000', '2999000',
    '0', '1', '0', '0', '1',
    '10 TV, 10GB media', 'Gói doanh nghiệp', '',
    '10', '10737418240', 'n', NOW()
) AS x
WHERE NOT EXISTS (SELECT 1 FROM `tb_packets` p WHERE p.name_packet = 'Gói doanh nghiệp');

SET @cid := (SELECT `customer_id` FROM `tb_users` WHERE `email` = 'customer@tsscreen.local' LIMIT 1);
SET @pid := (SELECT `packet_id` FROM `tb_packets` WHERE `name_packet` = 'Gói cơ bản' LIMIT 1);

INSERT INTO `tb_dirs` (`name_dir`, `customer_id`, `type_dir`, `deleted`, `created_by`, `created_date`)
SELECT 'Demo', @cid, 'group', 'n', CAST(@cid AS CHAR), NOW()
FROM DUAL
WHERE @cid IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM `tb_dirs` d
    WHERE d.customer_id = @cid AND d.name_dir = 'Demo' AND (d.deleted IS NULL OR d.deleted <> 'y')
  );

INSERT INTO `tb_orders` (
  `packet_id`, `customer_id`, `packet_code`, `name_packet`, `price`,
  `price_6_month`, `price_12_month`, `day_qty`, `month_qty`, `year_qty`,
  `pay_month`, `is_trial`, `is_business`, `detail`, `description`,
  `pay`, `register_date`, `payment_date`, `valid_date`, `expire_date`,
  `limit_qty`, `limit_capacity`, `deleted`, `created_date`
)
SELECT
  @pid, @cid, CONCAT('PK', @pid), 'Gói cơ bản', '99000',
  '499000', '899000', '0', '1', '0',
  '1', '0', '0', '2 TV, 1GB media', 'Gói cơ bản',
  '1', CURDATE(), CURDATE(), CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 MONTH),
  '2', '1073741824', 'n', NOW()
FROM DUAL
WHERE @cid IS NOT NULL AND @pid IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM `tb_orders` o
    WHERE o.customer_id = @cid AND o.pay = '1' AND (o.deleted IS NULL OR o.deleted <> 'y')
  );
