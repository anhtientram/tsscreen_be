-- Seed demo (chạy sau khi tạo bảng)
-- Admin app: username admin / password admin123 (app gửi MD5, DB lưu bcrypt của MD5)
-- Phone: customer@tsscreen.local / 123456

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_08_20_000001_create_tb_tables', 1),
(5, '2026_08_20_000002_add_limits_to_tb_orders', 1)
ON DUPLICATE KEY UPDATE `migration` = VALUES(`migration`);

-- Đổi API_SERVER thành URL public hosting (https://....wasmer.app, không slash cuối)
INSERT INTO `tb_configs` (`config_key`, `config_value`) VALUES
('COMPANY_NAME', 'TS Screen'),
('COMPANY_ADDRESS', ''),
('HOTLINE', ''),
('REPRESENTATIVE', ''),
('EMAIL', 'hello@example.com'),
('TAX_CODE', ''),
('API_SERVER', 'https://tsscreen-be.wasmer.app'),
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
ON DUPLICATE KEY UPDATE `email` = VALUES(`email`);

INSERT INTO `tb_users` (`customer_name`, `phone_number`, `email`, `password`, `customer_token`, `login_with`, `status`, `deleted`, `created_date`) VALUES
('Demo Customer', '0900000000', 'customer@tsscreen.local', '$2y$12$/Hwi2J4HaP2o0ETEmwfKhem/WLwUWL8ucZBh0t5aG9qbJht/I5rsi', '01demo0customer0token0tsscreen', 'email', 'y', 'n', NOW())
ON DUPLICATE KEY UPDATE `customer_name` = VALUES(`customer_name`);

INSERT INTO `tb_packets` (`name_packet`, `price`, `price_6_month`, `price_12_month`, `day_qty`, `month_qty`, `year_qty`, `is_trial`, `is_business`, `detail`, `description`, `picture`, `limit_qty`, `limit_capacity`, `deleted`, `created_date`) VALUES
('Gói dùng thử', '0', '0', '0', '7', '0', '0', '1', '0', 'Dùng thử 7 ngày, 1 TV', 'Gói dùng thử', '', '1', '104857600', 'n', NOW()),
('Gói cơ bản', '99000', '499000', '899000', '0', '1', '0', '0', '0', '2 TV, 1GB media', 'Gói cơ bản', '', '2', '1073741824', 'n', NOW()),
('Gói doanh nghiệp', '299000', '1599000', '2999000', '0', '1', '0', '0', '1', '10 TV, 10GB media', 'Gói doanh nghiệp', '', '10', '10737418240', 'n', NOW());
