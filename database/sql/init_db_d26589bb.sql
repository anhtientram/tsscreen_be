-- TS Screen schema + seed for hosting DB db_d26589bb (không CREATE DATABASE)
-- Import phpMyAdmin / mysql client, chọn database db_d26589bb trước hoặc giữ USE bên dưới.
-- DROP TABLE IF EXISTS: xóa bảng trùng tên rồi tạo lại.
-- Sửa API_SERVER trong phần seed thành URL public (https://..., không slash cuối).
-- Bảng Phase 3–5 đã có trong file này (tb_dirs, tb_devices, tb_campaigns, tb_resources, …).
-- Chỉ seed: import tiếp database/sql/seed.sql nếu đã tạo bảng rồi.

USE `db_d26589bb`;

/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` smallint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tb_account_notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_account_notifications` (
  `id_notify` bigint unsigned NOT NULL AUTO_INCREMENT,
  `account_id` bigint unsigned NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `descript` text COLLATE utf8mb4_unicode_ci,
  `detail` text COLLATE utf8mb4_unicode_ci,
  `picture` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `seen` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `created_date` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_notify`),
  KEY `tb_account_notifications_account_id_index` (`account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tb_accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_accounts` (
  `account_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_type` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '2',
  `fcm_token` text COLLATE utf8mb4_unicode_ci,
  `deleted` varchar(1) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'n',
  `created_date` timestamp NULL DEFAULT NULL,
  `last_MDF_date` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`account_id`),
  UNIQUE KEY `tb_accounts_username_unique` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tb_campaign_run_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_campaign_run_profiles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` bigint unsigned DEFAULT NULL,
  `customer_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `campaign_id` bigint unsigned DEFAULT NULL,
  `campaign_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `url` text COLLATE utf8mb4_unicode_ci,
  `computer_id` bigint unsigned DEFAULT NULL,
  `seri_computer` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `computer_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `run_time` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `run_time_server` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tb_campaign_run_profiles_customer_id_index` (`customer_id`),
  KEY `tb_campaign_run_profiles_campaign_id_index` (`campaign_id`),
  KEY `tb_campaign_run_profiles_computer_id_index` (`computer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tb_campaign_time_runs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_campaign_time_runs` (
  `id_run` bigint unsigned NOT NULL AUTO_INCREMENT,
  `campaign_id` bigint unsigned NOT NULL,
  `from_time` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `to_time` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id_run`),
  KEY `tb_campaign_time_runs_campaign_id_index` (`campaign_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tb_campaigns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_campaigns` (
  `campaign_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `campaign_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `video_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `from_date` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `to_date` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `from_time` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `to_time` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `days_of_week` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `video_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `url_youtobe` text COLLATE utf8mb4_unicode_ci,
  `url_usp` text COLLATE utf8mb4_unicode_ci,
  `customer_id` bigint unsigned NOT NULL,
  `computer_id` bigint unsigned DEFAULT NULL,
  `id_dir` bigint unsigned DEFAULT NULL,
  `id_computer` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `video_duration` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approved_yn` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `default_yn` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `run_by_default_yn` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `default_campaign_id` bigint unsigned DEFAULT NULL,
  `accept_count` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `accept_customers` text COLLATE utf8mb4_unicode_ci,
  `deleted` varchar(1) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'n',
  `created_date` timestamp NULL DEFAULT NULL,
  `last_MDF_date` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`campaign_id`),
  KEY `tb_campaigns_customer_id_index` (`customer_id`),
  KEY `tb_campaigns_computer_id_index` (`computer_id`),
  KEY `tb_campaigns_id_dir_index` (`id_dir`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tb_commands`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_commands` (
  `cmd_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `sn` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cmd_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` text COLLATE utf8mb4_unicode_ci,
  `is_imme` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `second_wait` int unsigned NOT NULL DEFAULT '10',
  `commit_time` timestamp NULL DEFAULT NULL,
  `return_time` timestamp NULL DEFAULT NULL,
  `return_value` text COLLATE utf8mb4_unicode_ci,
  `sync` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `done` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`cmd_id`),
  KEY `tb_commands_sn_index` (`sn`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tb_configs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_configs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `config_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `config_value` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tb_configs_config_key_unique` (`config_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tb_device_shares`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_device_shares` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `computer_id` bigint unsigned NOT NULL,
  `id_dir` bigint unsigned DEFAULT NULL,
  `customer_idfrom` bigint unsigned NOT NULL,
  `customer_idto` bigint unsigned NOT NULL,
  `checkOwner` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `created_date` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tb_device_shares_computer_id_index` (`computer_id`),
  KEY `tb_device_shares_customer_idfrom_index` (`customer_idfrom`),
  KEY `tb_device_shares_customer_idto_index` (`customer_idto`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tb_devices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_devices` (
  `computer_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `computer_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `seri_computer` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1',
  `provinces` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `district` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `wards` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `center_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `actived_date` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ultraviewPW` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ultraviewID` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_id` bigint unsigned NOT NULL,
  `customer_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_dir` bigint unsigned DEFAULT NULL,
  `time_end` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `turn_on` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `turn_off` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `user` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pass` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `isCheckOnProjector` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `isCheckOffProjector` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `computer_token` text COLLATE utf8mb4_unicode_ci,
  `rom_memory_total` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rom_memory_used` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lasted_alive_time` timestamp NULL DEFAULT NULL,
  `deleted` varchar(1) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'n',
  `created_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_date` timestamp NULL DEFAULT NULL,
  `last_MDF_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_MDF_date` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`computer_id`),
  KEY `tb_devices_seri_computer_index` (`seri_computer`),
  KEY `tb_devices_customer_id_index` (`customer_id`),
  KEY `tb_devices_id_dir_index` (`id_dir`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tb_dir_shares`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_dir_shares` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_dir` bigint unsigned NOT NULL,
  `customer_idfrom` bigint unsigned NOT NULL,
  `customer_idto` bigint unsigned NOT NULL,
  `checkOwner` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `created_date` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tb_dir_shares_id_dir_index` (`id_dir`),
  KEY `tb_dir_shares_customer_idfrom_index` (`customer_idfrom`),
  KEY `tb_dir_shares_customer_idto_index` (`customer_idto`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tb_dirs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_dirs` (
  `id_dir` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name_dir` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_id` bigint unsigned NOT NULL,
  `type_dir` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `turnon_time` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `turnoff_time` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted` varchar(1) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'n',
  `created_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_date` timestamp NULL DEFAULT NULL,
  `last_MDF_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_MDF_date` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_dir`),
  KEY `tb_dirs_customer_id_index` (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tb_notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_notifications` (
  `id_notify` bigint unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` bigint unsigned NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `descript` text COLLATE utf8mb4_unicode_ci,
  `detail` text COLLATE utf8mb4_unicode_ci,
  `picture` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `seen` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `created_date` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_notify`),
  KEY `tb_notifications_customer_id_index` (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tb_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_orders` (
  `paid_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `packet_id` bigint unsigned DEFAULT NULL,
  `customer_id` bigint unsigned NOT NULL,
  `packet_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reg_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name_packet` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price_6_month` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price_12_month` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `day_qty` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `month_qty` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `year_qty` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pay_month` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_trial` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `is_business` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `detail` text COLLATE utf8mb4_unicode_ci,
  `description` text COLLATE utf8mb4_unicode_ci,
  `picture` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pay` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type_pay` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `register_date` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_date` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `valid_date` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expire_date` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_due_date` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `limit_capacity` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `limit_qty` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted` varchar(1) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'n',
  `created_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_date` timestamp NULL DEFAULT NULL,
  `last_MDF_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_MDF_date` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`paid_id`),
  KEY `tb_orders_packet_id_index` (`packet_id`),
  KEY `tb_orders_customer_id_index` (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tb_otp_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_otp_codes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code_authen` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `purpose` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` timestamp NOT NULL,
  `used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tb_otp_codes_email_purpose_index` (`email`,`purpose`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tb_packets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_packets` (
  `packet_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name_packet` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price_6_month` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price_12_month` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `day_qty` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `month_qty` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `year_qty` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_trial` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `is_business` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `detail` text COLLATE utf8mb4_unicode_ci,
  `description` text COLLATE utf8mb4_unicode_ci,
  `picture` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expire_date` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `limit_capacity` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `limit_qty` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `account_id` bigint unsigned DEFAULT NULL,
  `deleted` varchar(1) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'n',
  `created_date` timestamp NULL DEFAULT NULL,
  `last_MDF_date` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`packet_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tb_resources`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_resources` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` bigint unsigned NOT NULL,
  `name_dir` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_size` bigint unsigned NOT NULL DEFAULT '0',
  `file_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `creation_time` timestamp NULL DEFAULT NULL,
  `modification_time` timestamp NULL DEFAULT NULL,
  `deleted` varchar(1) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'n',
  PRIMARY KEY (`id`),
  KEY `tb_resources_customer_id_index` (`customer_id`),
  KEY `tb_resources_name_dir_index` (`name_dir`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tb_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_transactions` (
  `transaction_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `paid_id` bigint unsigned DEFAULT NULL,
  `packet_id` bigint unsigned DEFAULT NULL,
  `customer_id` bigint unsigned NOT NULL,
  `reg_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name_packet` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_date` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ref_transaction_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_date` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`transaction_id`),
  KEY `tb_transactions_paid_id_index` (`paid_id`),
  KEY `tb_transactions_customer_id_index` (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tb_upload_chunks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_upload_chunks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` bigint unsigned DEFAULT NULL,
  `name_dir` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `filename` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `chunk_index` int unsigned DEFAULT NULL,
  `total_chunks` int unsigned DEFAULT NULL,
  `part_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tb_upload_chunks_name_dir_filename_index` (`name_dir`,`filename`),
  KEY `tb_upload_chunks_customer_id_index` (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tb_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_users` (
  `customer_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `customer_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_of_birth` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sex` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `chu_tk` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stk` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nganhang` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `chinhanh` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fcm_token` text COLLATE utf8mb4_unicode_ci,
  `login_with` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'email',
  `status` varchar(1) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'y',
  `deleted` varchar(1) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'n',
  `created_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_date` timestamp NULL DEFAULT NULL,
  `last_MDF_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_MDF_date` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`customer_id`),
  UNIQUE KEY `tb_users_email_unique` (`email`),
  UNIQUE KEY `tb_users_customer_token_unique` (`customer_token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (1,'0001_01_01_000000_create_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (2,'0001_01_01_000001_create_cache_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (3,'0001_01_01_000002_create_jobs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (4,'2026_08_20_000001_create_tb_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (5,'2026_08_20_000002_add_limits_to_tb_orders',1);

-- ========== SEED (cùng nội dung database/sql/seed.sql) ==========
-- Sửa API_SERVER thành URL public. Admin: admin/admin123. Phone: customer@tsscreen.local/123456
-- Phase 3–5: dir Demo + đơn Gói cơ bản pay=1. Không seed device/campaign/file.

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
) VALUES
('Gói dùng thử', '0', '0', '0', '7', '0', '0', '1', '0', 'Dùng thử 7 ngày, 1 TV', 'Gói dùng thử', '', '1', '104857600', 'n', NOW()),
('Gói cơ bản', '99000', '499000', '899000', '0', '1', '0', '0', '0', '2 TV, 1GB media', 'Gói cơ bản', '', '2', '1073741824', 'n', NOW()),
('Gói doanh nghiệp', '299000', '1599000', '2999000', '0', '1', '0', '0', '1', '10 TV, 10GB media', 'Gói doanh nghiệp', '', '10', '10737418240', 'n', NOW());

SET @cid := (SELECT `customer_id` FROM `tb_users` WHERE `email` = 'customer@tsscreen.local' LIMIT 1);
SET @pid := (SELECT `packet_id` FROM `tb_packets` WHERE `name_packet` = 'Gói cơ bản' LIMIT 1);

INSERT INTO `tb_dirs` (`name_dir`, `customer_id`, `type_dir`, `deleted`, `created_by`, `created_date`)
VALUES ('Demo', @cid, 'group', 'n', CAST(@cid AS CHAR), NOW());

INSERT INTO `tb_orders` (
  `packet_id`, `customer_id`, `packet_code`, `name_packet`, `price`,
  `price_6_month`, `price_12_month`, `day_qty`, `month_qty`, `year_qty`,
  `pay_month`, `is_trial`, `is_business`, `detail`, `description`,
  `pay`, `register_date`, `payment_date`, `valid_date`, `expire_date`,
  `limit_qty`, `limit_capacity`, `deleted`, `created_date`
) VALUES (
  @pid, @cid, CONCAT('PK', @pid), 'Gói cơ bản', '99000',
  '499000', '899000', '0', '1', '0',
  '1', '0', '0', '2 TV, 1GB media', 'Gói cơ bản',
  '1', CURDATE(), CURDATE(), CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 MONTH),
  '2', '1073741824', 'n', NOW()
);
