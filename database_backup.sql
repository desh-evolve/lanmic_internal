/*
SQLyog Community v13.3.1 (64 bit)
MySQL - 10.4.32-MariaDB : Database - lanmic_internal
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
/*Table structure for table `cache` */

DROP TABLE IF EXISTS `cache`;

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `cache` */

/*Table structure for table `cache_locks` */

DROP TABLE IF EXISTS `cache_locks`;

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `cache_locks` */

/*Table structure for table `department_sub_department` */

DROP TABLE IF EXISTS `department_sub_department`;

CREATE TABLE `department_sub_department` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `department_id` bigint(20) unsigned NOT NULL,
  `sub_department_id` bigint(20) unsigned NOT NULL,
  `status` varchar(255) DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `department_sub_department_department_id_sub_department_id_unique` (`department_id`,`sub_department_id`),
  KEY `department_sub_department_sub_department_id_foreign` (`sub_department_id`),
  CONSTRAINT `department_sub_department_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `department_sub_department_sub_department_id_foreign` FOREIGN KEY (`sub_department_id`) REFERENCES `sub_departments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `department_sub_department` */

/*Table structure for table `departments` */

DROP TABLE IF EXISTS `departments`;

CREATE TABLE `departments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `short_code` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` varchar(255) DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `departments` */

/*Table structure for table `division_sub_department` */

DROP TABLE IF EXISTS `division_sub_department`;

CREATE TABLE `division_sub_department` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sub_department_id` bigint(20) unsigned NOT NULL,
  `division_id` bigint(20) unsigned NOT NULL,
  `status` varchar(255) DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `division_sub_department_sub_department_id_division_id_unique` (`sub_department_id`,`division_id`),
  KEY `division_sub_department_division_id_foreign` (`division_id`),
  CONSTRAINT `division_sub_department_division_id_foreign` FOREIGN KEY (`division_id`) REFERENCES `divisions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `division_sub_department_sub_department_id_foreign` FOREIGN KEY (`sub_department_id`) REFERENCES `sub_departments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `division_sub_department` */

/*Table structure for table `divisions` */

DROP TABLE IF EXISTS `divisions`;

CREATE TABLE `divisions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `short_code` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` varchar(255) DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `divisions` */

/*Table structure for table `failed_jobs` */

DROP TABLE IF EXISTS `failed_jobs`;

CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `failed_jobs` */

/*Table structure for table `grn_items` */

DROP TABLE IF EXISTS `grn_items`;

CREATE TABLE `grn_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `return_id` bigint(20) unsigned NOT NULL,
  `return_item_id` bigint(20) unsigned NOT NULL,
  `item_code` varchar(255) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `item_category` varchar(255) DEFAULT NULL,
  `unit` varchar(255) DEFAULT NULL,
  `location_code` varchar(255) DEFAULT NULL,
  `unit_price` decimal(10,2) DEFAULT NULL,
  `total_price` decimal(10,2) DEFAULT NULL,
  `grn_quantity` int(11) NOT NULL,
  `reference_number_1` varchar(255) DEFAULT NULL,
  `reference_number_2` varchar(255) DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'active',
  `processed_by` bigint(20) unsigned DEFAULT NULL,
  `processed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `grn_items_return_id_foreign` (`return_id`),
  KEY `grn_items_return_item_id_foreign` (`return_item_id`),
  CONSTRAINT `grn_items_return_id_foreign` FOREIGN KEY (`return_id`) REFERENCES `returns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `grn_items_return_item_id_foreign` FOREIGN KEY (`return_item_id`) REFERENCES `return_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `grn_items` */

/*Table structure for table `job_batches` */

DROP TABLE IF EXISTS `job_batches`;

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `job_batches` */

/*Table structure for table `jobs` */

DROP TABLE IF EXISTS `jobs`;

CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `jobs` */

/*Table structure for table `migrations` */

DROP TABLE IF EXISTS `migrations`;

CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `migrations` */

insert  into `migrations`(`id`,`migration`,`batch`) values 
(1,'0001_01_01_000000_create_users_table',1),
(2,'0001_01_01_000001_create_cache_table',1),
(3,'0001_01_01_000002_create_jobs_table',1),
(4,'2025_11_21_064225_create_roles_and_permissions_tables',1),
(5,'2025_11_21_085651_create_departments_table',1),
(6,'2025_11_21_085651_create_sub_departments_table',1),
(7,'2025_11_21_085652_create_divisions_table',1),
(8,'2025_11_21_090334_create_department_sub_department_table',1),
(9,'2025_11_21_090337_create_sub_department_division_table',1),
(10,'2025_11_24_051228_create_requisitions_table',1),
(11,'2025_11_24_051229_create_requisition_items_table',1),
(12,'2025_11_26_051019_create_purchase_order_items_table',1),
(13,'2025_11_26_051059_create_requisition_issued_items_table',1),
(14,'2025_11_28_042014_create_returns_table',1),
(15,'2025_11_28_042025_create_return_items_table',1),
(16,'2025_11_28_042036_create_grn_items_table',1),
(17,'2025_11_28_042047_create_scrap_items_table',1),
(18,'2025_11_28_043923_create_permission_user_table',1),
(19,'2025_12_01_063708_add_module_to_permissions_table',1),
(20,'2025_12_02_084633_add_admin_note_to_return_items_table',1),
(21,'2025_12_10_045514_remove_price_columns_from_requisition_items_table',1),
(22,'2025_12_10_050420_add_location_id_to_requisition_items_table',1),
(23,'2025_12_10_052452_modify_purchase_order_items_table',1),
(24,'2025_12_10_053113_add_location_id_to_requisition_issued_items_table',1),
(25,'2025_12_11_044120_add_ref_numbers_to_requisition_issued_items_table',1),
(26,'2025_12_22_055716_update_return_items_table_for_requisition_based_returns',1),
(27,'2025_12_22_110131_update_grn_scrap_tables_for_return_approval',1);

/*Table structure for table `password_reset_tokens` */

DROP TABLE IF EXISTS `password_reset_tokens`;

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `password_reset_tokens` */

/*Table structure for table `permission_role` */

DROP TABLE IF EXISTS `permission_role`;

CREATE TABLE `permission_role` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `role_id` bigint(20) unsigned NOT NULL,
  `permission_id` bigint(20) unsigned NOT NULL,
  `status` varchar(255) DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `permission_role_role_id_foreign` (`role_id`),
  KEY `permission_role_permission_id_foreign` (`permission_id`),
  CONSTRAINT `permission_role_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `permission_role_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=71 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `permission_role` */

insert  into `permission_role`(`id`,`role_id`,`permission_id`,`status`,`created_at`,`created_by`,`updated_at`,`updated_by`) values 
(1,1,1,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(2,1,2,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(3,1,3,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(4,1,4,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(5,1,5,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(6,1,6,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(7,1,7,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(8,1,8,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(9,1,9,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(10,1,10,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(11,1,11,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(12,1,12,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(13,1,13,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(14,1,14,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(15,1,15,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(16,1,16,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(17,1,17,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(18,1,18,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(19,1,19,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(20,1,20,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(21,1,21,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(22,1,22,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(23,1,23,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(24,1,24,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(25,1,25,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(26,1,26,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(27,1,27,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(28,1,28,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(29,1,29,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(30,1,30,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(31,1,31,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(32,1,32,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(33,1,33,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(34,1,34,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(35,1,35,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(36,1,36,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(37,1,37,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(38,1,38,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(39,1,39,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(40,1,40,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(41,1,41,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(42,1,42,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(43,1,43,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(44,2,1,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(45,2,15,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(46,2,16,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(47,2,17,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(48,2,19,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(49,2,20,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(50,2,21,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(51,2,23,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(52,2,24,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(53,2,25,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(54,2,27,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(55,2,31,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(56,2,32,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(57,2,33,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(58,2,34,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(59,2,35,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(60,2,39,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(61,2,40,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(62,2,41,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(63,3,1,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(64,3,27,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(65,3,28,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(66,3,29,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(67,3,35,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(68,3,36,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(69,3,37,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(70,3,40,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0);

/*Table structure for table `permission_user` */

DROP TABLE IF EXISTS `permission_user`;

CREATE TABLE `permission_user` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `permission_id` bigint(20) unsigned NOT NULL,
  `status` varchar(255) DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permission_user_user_id_permission_id_unique` (`user_id`,`permission_id`),
  KEY `permission_user_permission_id_foreign` (`permission_id`),
  CONSTRAINT `permission_user_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `permission_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `permission_user` */

insert  into `permission_user`(`id`,`user_id`,`permission_id`,`status`,`created_at`,`created_by`,`updated_at`,`updated_by`) values 
(1,1,1,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(2,1,2,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(3,1,3,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(4,1,4,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(5,1,5,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(6,1,6,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(7,1,7,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(8,1,8,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(9,1,9,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(10,1,10,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(11,1,11,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(12,1,12,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(13,1,13,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(14,1,14,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(15,1,15,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(16,1,16,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(17,1,17,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(18,1,18,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(19,1,19,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(20,1,20,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(21,1,21,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(22,1,22,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(23,1,23,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(24,1,24,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(25,1,25,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(26,1,26,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(27,1,27,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(28,1,28,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(29,1,29,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(30,1,30,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(31,1,31,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(32,1,32,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(33,1,33,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(34,1,34,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(35,1,35,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(36,1,36,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(37,1,37,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(38,1,38,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(39,1,39,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(40,1,40,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(41,1,41,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(42,1,42,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(43,1,43,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0);

/*Table structure for table `permissions` */

DROP TABLE IF EXISTS `permissions`;

CREATE TABLE `permissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `module` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `status` varchar(255) DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `permissions` */

insert  into `permissions`(`id`,`module`,`name`,`description`,`status`,`created_at`,`created_by`,`updated_at`,`updated_by`) values 
(1,'dashboard','view-dashboard','View dashboard','active','2026-01-27 05:15:42',0,'2026-01-27 05:15:42',0),
(2,'users','view-users','View users list','active','2026-01-27 05:15:42',0,'2026-01-27 05:15:42',0),
(3,'users','create-users','Create new users','active','2026-01-27 05:15:42',0,'2026-01-27 05:15:42',0),
(4,'users','edit-users','Edit existing users','active','2026-01-27 05:15:42',0,'2026-01-27 05:15:42',0),
(5,'users','delete-users','Delete users','active','2026-01-27 05:15:42',0,'2026-01-27 05:15:42',0),
(6,'users','assign-user-permissions','Assign permissions to users','active','2026-01-27 05:15:43',0,'2026-01-27 05:15:43',0),
(7,'roles','view-roles','View roles list','active','2026-01-27 05:15:43',0,'2026-01-27 05:15:43',0),
(8,'roles','create-roles','Create new roles','active','2026-01-27 05:15:43',0,'2026-01-27 05:15:43',0),
(9,'roles','edit-roles','Edit existing roles','active','2026-01-27 05:15:43',0,'2026-01-27 05:15:43',0),
(10,'roles','delete-roles','Delete roles','active','2026-01-27 05:15:43',0,'2026-01-27 05:15:43',0),
(11,'permissions','view-permissions','View permissions list','active','2026-01-27 05:15:43',0,'2026-01-27 05:15:43',0),
(12,'permissions','create-permissions','Create new permissions','active','2026-01-27 05:15:43',0,'2026-01-27 05:15:43',0),
(13,'permissions','edit-permissions','Edit existing permissions','active','2026-01-27 05:15:43',0,'2026-01-27 05:15:43',0),
(14,'permissions','delete-permissions','Delete permissions','active','2026-01-27 05:15:43',0,'2026-01-27 05:15:43',0),
(15,'departments','view-departments','View departments list','active','2026-01-27 05:15:43',0,'2026-01-27 05:15:43',0),
(16,'departments','create-departments','Create new departments','active','2026-01-27 05:15:43',0,'2026-01-27 05:15:43',0),
(17,'departments','edit-departments','Edit existing departments','active','2026-01-27 05:15:43',0,'2026-01-27 05:15:43',0),
(18,'departments','delete-departments','Delete departments','active','2026-01-27 05:15:43',0,'2026-01-27 05:15:43',0),
(19,'sub-departments','view-sub-departments','View sub-departments list','active','2026-01-27 05:15:43',0,'2026-01-27 05:15:43',0),
(20,'sub-departments','create-sub-departments','Create new sub-departments','active','2026-01-27 05:15:43',0,'2026-01-27 05:15:43',0),
(21,'sub-departments','edit-sub-departments','Edit existing sub-departments','active','2026-01-27 05:15:43',0,'2026-01-27 05:15:43',0),
(22,'sub-departments','delete-sub-departments','Delete sub-departments','active','2026-01-27 05:15:43',0,'2026-01-27 05:15:43',0),
(23,'divisions','view-divisions','View divisions list','active','2026-01-27 05:15:43',0,'2026-01-27 05:15:43',0),
(24,'divisions','create-divisions','Create new divisions','active','2026-01-27 05:15:43',0,'2026-01-27 05:15:43',0),
(25,'divisions','edit-divisions','Edit existing divisions','active','2026-01-27 05:15:43',0,'2026-01-27 05:15:43',0),
(26,'divisions','delete-divisions','Delete divisions','active','2026-01-27 05:15:43',0,'2026-01-27 05:15:43',0),
(27,'requisitions','view-requisitions','View requisitions','active','2026-01-27 05:15:43',0,'2026-01-27 05:15:43',0),
(28,'requisitions','create-requisitions','Create requisitions','active','2026-01-27 05:15:43',0,'2026-01-27 05:15:43',0),
(29,'requisitions','edit-requisitions','Edit requisitions','active','2026-01-27 05:15:43',0,'2026-01-27 05:15:43',0),
(30,'requisitions','delete-requisitions','Delete requisitions','active','2026-01-27 05:15:43',0,'2026-01-27 05:15:43',0),
(31,'requisitions','approve-requisitions','Approve/reject requisitions','active','2026-01-27 05:15:43',0,'2026-01-27 05:15:43',0),
(32,'requisitions','issue-requisitions','Issue items for requisitions','active','2026-01-27 05:15:43',0,'2026-01-27 05:15:43',0),
(33,'purchase-orders','view-purchase-orders','View purchase orders','active','2026-01-27 05:15:43',0,'2026-01-27 05:15:43',0),
(34,'purchase-orders','clear-purchase-orders','Clear purchase orders','active','2026-01-27 05:15:43',0,'2026-01-27 05:15:43',0),
(35,'returns','view-returns','View returns','active','2026-01-27 05:15:43',0,'2026-01-27 05:15:43',0),
(36,'returns','create-returns','Create returns','active','2026-01-27 05:15:43',0,'2026-01-27 05:15:43',0),
(37,'returns','edit-returns','Edit returns','active','2026-01-27 05:15:43',0,'2026-01-27 05:15:43',0),
(38,'returns','delete-returns','Delete returns','active','2026-01-27 05:15:43',0,'2026-01-27 05:15:43',0),
(39,'returns','approve-returns','Approve return items (GRN/Scrap)','active','2026-01-27 05:15:43',0,'2026-01-27 05:15:43',0),
(40,'reports','view-reports','View reports','active','2026-01-27 05:15:43',0,'2026-01-27 05:15:43',0),
(41,'reports','export-reports','Export reports','active','2026-01-27 05:15:43',0,'2026-01-27 05:15:43',0),
(42,'settings','view-settings','View system settings','active','2026-01-27 05:15:43',0,'2026-01-27 05:15:43',0),
(43,'settings','edit-settings','Edit system settings','active','2026-01-27 05:15:43',0,'2026-01-27 05:15:43',0);

/*Table structure for table `purchase_order_items` */

DROP TABLE IF EXISTS `purchase_order_items`;

CREATE TABLE `purchase_order_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `location_code` varchar(50) DEFAULT NULL,
  `requisition_id` bigint(20) unsigned NOT NULL,
  `item_code` varchar(255) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `item_category` varchar(255) DEFAULT NULL,
  `unit` varchar(255) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'pending',
  `cleared_by` bigint(20) unsigned DEFAULT NULL,
  `cleared_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `purchase_order_items_requisition_id_foreign` (`requisition_id`),
  KEY `purchase_order_items_cleared_by_foreign` (`cleared_by`),
  CONSTRAINT `purchase_order_items_cleared_by_foreign` FOREIGN KEY (`cleared_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `purchase_order_items_requisition_id_foreign` FOREIGN KEY (`requisition_id`) REFERENCES `requisitions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `purchase_order_items` */

/*Table structure for table `requisition_issued_items` */

DROP TABLE IF EXISTS `requisition_issued_items`;

CREATE TABLE `requisition_issued_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `location_code` varchar(50) DEFAULT NULL,
  `reference_number_1` varchar(255) DEFAULT NULL,
  `reference_number_2` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `requisition_id` bigint(20) unsigned NOT NULL,
  `requisition_item_id` bigint(20) unsigned NOT NULL,
  `item_code` varchar(255) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `item_category` varchar(255) DEFAULT NULL,
  `unit` varchar(255) DEFAULT NULL,
  `unit_price` decimal(10,2) DEFAULT NULL,
  `total_price` decimal(10,2) DEFAULT NULL,
  `issued_quantity` int(11) NOT NULL,
  `issued_by` bigint(20) unsigned NOT NULL,
  `issued_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` varchar(50) NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `requisition_issued_items_requisition_id_foreign` (`requisition_id`),
  KEY `requisition_issued_items_requisition_item_id_foreign` (`requisition_item_id`),
  KEY `requisition_issued_items_issued_by_foreign` (`issued_by`),
  CONSTRAINT `requisition_issued_items_issued_by_foreign` FOREIGN KEY (`issued_by`) REFERENCES `users` (`id`) ON DELETE NO ACTION,
  CONSTRAINT `requisition_issued_items_requisition_id_foreign` FOREIGN KEY (`requisition_id`) REFERENCES `requisitions` (`id`) ON DELETE NO ACTION,
  CONSTRAINT `requisition_issued_items_requisition_item_id_foreign` FOREIGN KEY (`requisition_item_id`) REFERENCES `requisition_items` (`id`) ON DELETE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `requisition_issued_items` */

/*Table structure for table `requisition_items` */

DROP TABLE IF EXISTS `requisition_items`;

CREATE TABLE `requisition_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `location_code` varchar(50) DEFAULT NULL,
  `requisition_id` bigint(20) unsigned NOT NULL,
  `item_code` varchar(255) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `item_category` varchar(255) DEFAULT NULL,
  `unit` varchar(255) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `specifications` text DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `requisition_items_requisition_id_foreign` (`requisition_id`),
  CONSTRAINT `requisition_items_requisition_id_foreign` FOREIGN KEY (`requisition_id`) REFERENCES `requisitions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `requisition_items` */

/*Table structure for table `requisitions` */

DROP TABLE IF EXISTS `requisitions`;

CREATE TABLE `requisitions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `requisition_number` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `department_id` bigint(20) unsigned DEFAULT NULL,
  `sub_department_id` bigint(20) unsigned DEFAULT NULL,
  `division_id` bigint(20) unsigned DEFAULT NULL,
  `approve_status` varchar(50) NOT NULL DEFAULT 'pending',
  `approved_by` bigint(20) unsigned DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `clear_status` varchar(50) NOT NULL DEFAULT 'pending',
  `cleared_by` bigint(20) unsigned DEFAULT NULL,
  `cleared_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `requisitions_requisition_number_unique` (`requisition_number`),
  KEY `requisitions_department_id_foreign` (`department_id`),
  KEY `requisitions_sub_department_id_foreign` (`sub_department_id`),
  KEY `requisitions_division_id_foreign` (`division_id`),
  KEY `requisitions_user_id_foreign` (`user_id`),
  KEY `requisitions_approved_by_foreign` (`approved_by`),
  KEY `requisitions_cleared_by_foreign` (`cleared_by`),
  CONSTRAINT `requisitions_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE NO ACTION,
  CONSTRAINT `requisitions_cleared_by_foreign` FOREIGN KEY (`cleared_by`) REFERENCES `users` (`id`) ON DELETE NO ACTION,
  CONSTRAINT `requisitions_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `requisitions_division_id_foreign` FOREIGN KEY (`division_id`) REFERENCES `divisions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `requisitions_sub_department_id_foreign` FOREIGN KEY (`sub_department_id`) REFERENCES `sub_departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `requisitions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `requisitions` */

/*Table structure for table `return_items` */

DROP TABLE IF EXISTS `return_items`;

CREATE TABLE `return_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `return_id` bigint(20) unsigned NOT NULL,
  `requisition_issued_item_id` bigint(20) unsigned DEFAULT NULL,
  `return_type` varchar(50) NOT NULL,
  `location_code` varchar(255) DEFAULT NULL,
  `item_code` varchar(255) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `item_category` varchar(255) DEFAULT NULL,
  `unit` varchar(255) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `approve_status` varchar(50) NOT NULL DEFAULT 'pending',
  `admin_note` text DEFAULT NULL,
  `approved_by` bigint(20) unsigned DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `return_items_return_id_foreign` (`return_id`),
  KEY `return_items_approved_by_foreign` (`approved_by`),
  KEY `return_items_requisition_issued_item_id_foreign` (`requisition_issued_item_id`),
  CONSTRAINT `return_items_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE NO ACTION,
  CONSTRAINT `return_items_requisition_issued_item_id_foreign` FOREIGN KEY (`requisition_issued_item_id`) REFERENCES `requisition_issued_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `return_items_return_id_foreign` FOREIGN KEY (`return_id`) REFERENCES `returns` (`id`) ON DELETE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `return_items` */

/*Table structure for table `returns` */

DROP TABLE IF EXISTS `returns`;

CREATE TABLE `returns` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `returned_by` bigint(20) unsigned NOT NULL,
  `requisition_id` bigint(20) unsigned DEFAULT NULL,
  `returned_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` varchar(50) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `returns_returned_by_foreign` (`returned_by`),
  KEY `returns_requisition_id_foreign` (`requisition_id`),
  CONSTRAINT `returns_requisition_id_foreign` FOREIGN KEY (`requisition_id`) REFERENCES `requisitions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `returns_returned_by_foreign` FOREIGN KEY (`returned_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `returns` */

/*Table structure for table `role_user` */

DROP TABLE IF EXISTS `role_user`;

CREATE TABLE `role_user` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `role_id` bigint(20) unsigned NOT NULL,
  `status` varchar(255) DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `role_user_user_id_foreign` (`user_id`),
  KEY `role_user_role_id_foreign` (`role_id`),
  CONSTRAINT `role_user_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `role_user` */

insert  into `role_user`(`id`,`user_id`,`role_id`,`status`,`created_at`,`created_by`,`updated_at`,`updated_by`) values 
(1,1,1,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(2,2,2,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0),
(3,3,3,'active','2026-01-27 10:45:43',0,'2026-01-27 10:45:43',0);

/*Table structure for table `roles` */

DROP TABLE IF EXISTS `roles`;

CREATE TABLE `roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `status` varchar(255) DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `roles` */

insert  into `roles`(`id`,`name`,`description`,`status`,`created_at`,`created_by`,`updated_at`,`updated_by`) values 
(1,'admin','Administrator with full access','active','2026-01-27 05:15:42',0,'2026-01-27 05:15:42',0),
(2,'manager','Manager with limited administrative access','active','2026-01-27 05:15:42',0,'2026-01-27 05:15:42',0),
(3,'user','Regular User','active','2026-01-27 05:15:42',0,'2026-01-27 05:15:42',0);

/*Table structure for table `scrap_items` */

DROP TABLE IF EXISTS `scrap_items`;

CREATE TABLE `scrap_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `return_id` bigint(20) unsigned NOT NULL,
  `return_item_id` bigint(20) unsigned NOT NULL,
  `item_code` varchar(255) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `item_category` varchar(255) DEFAULT NULL,
  `unit` varchar(255) DEFAULT NULL,
  `location_code` varchar(255) DEFAULT NULL,
  `unit_price` decimal(10,2) DEFAULT NULL,
  `total_price` decimal(10,2) DEFAULT NULL,
  `scrap_quantity` int(11) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'active',
  `processed_by` bigint(20) unsigned DEFAULT NULL,
  `processed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `scrap_items_return_id_foreign` (`return_id`),
  KEY `scrap_items_return_item_id_foreign` (`return_item_id`),
  CONSTRAINT `scrap_items_return_id_foreign` FOREIGN KEY (`return_id`) REFERENCES `returns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `scrap_items_return_item_id_foreign` FOREIGN KEY (`return_item_id`) REFERENCES `return_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `scrap_items` */

/*Table structure for table `sessions` */

DROP TABLE IF EXISTS `sessions`;

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `sessions` */

/*Table structure for table `sub_departments` */

DROP TABLE IF EXISTS `sub_departments`;

CREATE TABLE `sub_departments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `short_code` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` varchar(255) DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `sub_departments` */

/*Table structure for table `users` */

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `status` varchar(255) DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `users` */

insert  into `users`(`id`,`name`,`email`,`email_verified_at`,`password`,`remember_token`,`status`,`created_at`,`created_by`,`updated_at`,`updated_by`) values 
(1,'Admin User','admin@lanmic.com',NULL,'$2y$12$79ymMDzTFn5nLMIeBJ.RU.BgFCw/qc7C7clSzXJCSygASp2h8OmMi',NULL,'active','2026-01-27 05:15:43',0,'2026-01-27 05:15:43',0),
(2,'Manager User','manager@lanmic.com',NULL,'$2y$12$47JkGYXuaSOxxSgu1gcJU.BQXwxyoYIGIfKxUdxfBKTOS5jtUgLgK',NULL,'active','2026-01-27 05:15:43',0,'2026-01-27 05:15:43',0),
(3,'Regular User','user@lanmic.com',NULL,'$2y$12$/MleYV18GoWjskkuyfgV0.N0wOoogJrHjsL0B4zWVQ6TsSIzDBjZ.',NULL,'active','2026-01-27 05:15:43',0,'2026-01-27 05:15:43',0);

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
