-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 23, 2025 at 11:02 AM
-- Server version: 12.0.1-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_mikbill`
--

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `companies`
--

CREATE TABLE `companies` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `address` text DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `bank_name` varchar(255) DEFAULT NULL,
  `account_number` varchar(255) DEFAULT NULL,
  `account_holder` varchar(255) DEFAULT NULL,
  `owner_name` varchar(255) DEFAULT NULL,
  `logo_path` varchar(255) DEFAULT NULL,
  `signature_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `companies`
--

INSERT INTO `companies` (`id`, `company_name`, `address`, `phone`, `bank_name`, `account_number`, `account_holder`, `owner_name`, `logo_path`, `signature_path`, `created_at`, `updated_at`) VALUES
(1, 'Chandela Lintas Media', 'Jl.Panglima Sudirman No 13\r\nDesa. Kertosono\r\nKec.Panggul, Kab.Trenggalek\r\nJawa Timur, INDONESIA', '081234084994', 'BRI', '6462-01-0128-755-36', 'Aang Wirawan', 'Muhammad Mahfudin', 'company_assets/tlQLTl3OzRDKaqkjaf1NytYtVf66l33CpYgSyHF5.png', 'company_assets/EGmGlGRsLCv9TsaVfxooQxC0CHaTmycU6MsAf8FT.png', '2025-11-22 17:04:28', '2025-11-22 17:26:12');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `operator_id` bigint(20) UNSIGNED DEFAULT NULL,
  `internet_number` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `pppoe_username` varchar(255) NOT NULL,
  `pppoe_password` varchar(255) NOT NULL,
  `profile` varchar(255) DEFAULT NULL,
  `comment` varchar(255) DEFAULT NULL,
  `monthly_price` int(11) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `operator_id`, `internet_number`, `name`, `phone`, `pppoe_username`, `pppoe_password`, `profile`, `comment`, `monthly_price`, `is_active`, `created_at`, `updated_at`) VALUES
(1, NULL, '48248938', 'Home 3', '081234084994', 'z_depan', 'rumah123', 'PROFIL_2_CL', NULL, 0, 1, '2025-11-21 08:31:16', '2025-11-22 22:45:38'),
(2, NULL, '73233019', 'Mbah', '081234084994', 'mbah', 'mbah123', 'PROFIL_FAMILY', NULL, 0, 1, '2025-11-21 17:30:29', '2025-11-22 16:34:11'),
(3, NULL, '89833279', 'Adib', '6281216125093', 'sondong', 'sondong123', 'PROFIL_3_CL', NULL, 100000, 1, '2025-11-21 17:30:29', '2025-11-22 16:34:19'),
(4, NULL, '55077302', 'Mbah Saniem', '6282142731206', 'dimas', 'dimas123', 'PROFIL_3_CL', NULL, 100000, 1, '2025-11-21 17:30:30', '2025-11-22 16:34:26'),
(5, NULL, '79762011', 'Pak Paimo', '6287751935799', 'paimo', 'paimo123', 'PROFIL_4_CL', NULL, 110000, 1, '2025-11-21 17:30:30', '2025-11-22 16:34:34'),
(6, NULL, '50310116', 'Supartin', '081234084994', 'supartin', 'supartin123', 'PROFIL_4_CL', NULL, 0, 1, '2025-11-21 17:30:31', '2025-11-22 16:34:45'),
(7, NULL, '92047452', 'Pak Arif', '085235641304', 'arif', 'arif123', 'PROFIL_2_CL', NULL, 135000, 1, '2025-11-21 17:30:31', '2025-11-22 16:34:51'),
(8, NULL, '67249794', 'Pak Gatot', '6281945398110', 'farhan', 'farhan123', 'PROFIL_3_CL', NULL, 110000, 1, '2025-11-21 17:30:31', '2025-11-22 16:34:58'),
(9, NULL, '47901650', 'Mbak Hika', '6285234153210', 'hika', 'hika123', 'PROFIL_3_CL', NULL, 110000, 1, '2025-11-21 17:30:32', '2025-11-22 16:35:05'),
(10, NULL, '28514843', 'Canggih', '082230409562', 'canggih', 'canggih123', 'PROFIL_2_CL', NULL, 135000, 1, '2025-11-21 17:30:32', '2025-11-22 16:35:11'),
(11, NULL, '87860335', 'Tegar', '6282335703880', 'tegar', 'tegar123', 'PROFIL_3_CL', NULL, 100000, 1, '2025-11-21 17:30:33', '2025-11-22 16:35:22'),
(12, NULL, '25561487', 'Mbak Yuni', '6282245470019', 'yuni', 'yuni123', 'PROFIL_3_CL', NULL, 100000, 1, '2025-11-21 17:30:33', '2025-11-22 16:35:30'),
(13, NULL, '30865048', 'Pak Bianto', '6282232531088', 'bianto', 'bianto123', 'PROFIL_4_CL', NULL, 110000, 1, '2025-11-21 17:30:34', '2025-11-22 16:35:37'),
(14, NULL, '83030529', 'Adelia Hargianti', '6287780838462', 'adel', 'adel123', 'PROFIL_3_CL', NULL, 110000, 1, '2025-11-21 17:30:34', '2025-11-22 16:35:43'),
(15, NULL, '90101953', 'Bu Prihatin', '6285213418428', 'abid', 'abid123', 'PROFIL_5_CL', NULL, 50000, 1, '2025-11-21 17:30:34', '2025-11-22 16:35:49'),
(16, NULL, '83081831', 'Mushola', '081234084994', 'mushola', 'mushola123', 'PROFIL_5_CL', NULL, 0, 1, '2025-11-21 17:30:35', '2025-11-22 16:35:54'),
(17, NULL, '41711263', 'Mbak Puji', '62859175449448', 'puji', 'puji123', 'PROFIL_4_CL', NULL, 110000, 1, '2025-11-21 17:30:35', '2025-11-22 16:36:01'),
(18, NULL, '53962032', 'Jumikan', '6281344143772', 'jumikan', 'jumikan123', 'PROFIL_3_CL', NULL, 100000, 1, '2025-11-21 17:30:36', '2025-11-22 16:36:10'),
(19, NULL, '36882834', 'Home 1', '081234084994', 'aangwi', 'aangwi123', 'PROFIL_HOME', NULL, 0, 1, '2025-11-21 17:30:36', '2025-11-22 16:36:21'),
(20, NULL, '68728852', 'Pak Barman', '6282335074515', 'barman', 'barman123', 'PROFIL_3_CL', NULL, 110000, 1, '2025-11-21 17:30:37', '2025-11-22 16:36:49'),
(21, NULL, '34285756', 'De Wiji Rumah', '6281234084994', 'dewiji1', 'dewiji123', 'PROFIL_3_CL', NULL, 100000, 1, '2025-11-21 17:30:37', '2025-11-22 16:36:54'),
(22, NULL, '68056882', 'De Wiji Kost', '6281234084994', 'dewiji2', 'dewiji123', 'PROFIL_4_CL', NULL, 100000, 1, '2025-11-21 17:30:37', '2025-11-22 16:36:59'),
(23, NULL, '53045973', 'Heri Purwanto', '081231308396', 'heri', 'heri123', 'PROFIL_3_CL', NULL, 110000, 1, '2025-11-21 17:30:38', '2025-11-22 16:37:06'),
(24, NULL, '88161987', 'Kecamatan', '081234084994', 'kecamatan', 'kecamatan', 'PROFIL_3_CL', NULL, 0, 1, '2025-11-21 17:30:38', '2025-11-22 16:37:12'),
(25, NULL, '98271459', 'Pak Roso', '6282232076641', 'roso', 'roso123', 'PROFIL_4_CL', NULL, 100000, 1, '2025-11-21 17:30:39', '2025-11-22 16:37:19'),
(26, NULL, '35671140', 'Wiwik', '6285231189603', 'wiwik', 'wiwik123', 'PROFIL_4_CL', NULL, 100000, 1, '2025-11-21 17:30:39', '2025-11-22 16:37:25'),
(27, NULL, '60550358', 'Lek Nensi', '6282330921666', 'sinyo', 'sinyo123', 'PROFIL_3_CL', NULL, 100000, 1, '2025-11-21 17:30:39', '2025-11-22 16:37:32'),
(28, 2, '14153613', 'Pak Jafar', '6283830243325', 'mozart', 'mozart123', 'PROFIL_5_CL', NULL, 50000, 1, '2025-11-21 17:30:40', '2025-11-22 20:08:28'),
(29, NULL, '72588870', 'Anugerah Sarjana (SAWDUST 1)', '085334309095', 'nunung', 'nunung123', 'PROFIL_4_CL', NULL, 100000, 1, '2025-11-21 17:30:40', '2025-11-22 16:36:43'),
(30, NULL, '32820409', 'Anugrah Sarjana (SAWDUST 2)', '085334309095', 'nunung1', 'nunung123', 'PROFIL_4_CL', NULL, 100000, 1, '2025-11-21 17:30:41', '2025-11-22 16:37:46'),
(31, NULL, '70707914', 'Pak Prayit', '081235503305', 'canggih1', 'canggih123', 'PROFIL_2_CL', NULL, 100000, 1, '2025-11-21 17:30:41', '2025-11-22 16:37:53'),
(32, NULL, '77425278', 'Mas Didik', '6287780838462', 'didik', 'didik123', 'PROFIL_4_CL', NULL, 100000, 1, '2025-11-21 17:30:42', '2025-11-22 16:37:59'),
(33, NULL, '50765392', 'Mas Endro', '6287780838462', 'endro', 'endro123', 'PROFIL_4_CL', NULL, 100000, 1, '2025-11-21 17:30:42', '2025-11-22 16:38:06'),
(34, NULL, '70896698', 'Galis', '6287780838462', 'galis', 'galis123', 'PROFIL_4_CL', NULL, 100000, 1, '2025-11-21 17:30:42', '2025-11-22 16:38:13'),
(35, NULL, '90947683', 'Pak Seno', '6287780838462', 'seno', 'seno123', 'PROFIL_3_CL', NULL, 100000, 1, '2025-11-21 17:30:43', '2025-11-22 16:42:16'),
(36, NULL, '83323001', 'Home 2', '081234084994', 'aangwi-home', 'aangwi123', 'PROFIL_HOME', NULL, 0, 1, '2025-11-21 17:30:43', '2025-11-22 16:42:32'),
(37, NULL, '48375966', 'RUMAH-BELAKANG', '081234084994', 'z_belakang', 'rumah123', 'PROFIL_2_CL', NULL, 0, 1, '2025-11-21 17:30:44', '2025-11-22 16:44:05'),
(39, NULL, '28848033', 'Puskesmas', '085791359105', 'puskesmas', 'puskesmas123', 'PROFIL_UNLIMITED', NULL, 500000, 1, '2025-11-21 18:30:33', '2025-11-22 16:44:25');

-- --------------------------------------------------------

--
-- Table structure for table `expenses`
--

CREATE TABLE `expenses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `description` varchar(255) NOT NULL,
  `amount` bigint(20) NOT NULL,
  `transaction_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `expenses`
--

INSERT INTO `expenses` (`id`, `description`, `amount`, `transaction_date`, `created_at`, `updated_at`) VALUES
(3, 'Mbayar Internet', 1250000, '2025-11-20', '2025-11-23 01:26:21', '2025-11-23 01:26:21'),
(4, 'Jasa Splicer', 500000, '2025-11-23', '2025-11-23 01:26:41', '2025-11-23 01:26:41');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `customer_id` bigint(20) UNSIGNED NOT NULL,
  `due_date` date NOT NULL,
  `status` enum('unpaid','paid') NOT NULL DEFAULT 'unpaid',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`id`, `customer_id`, `due_date`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, '2025-11-16', 'paid', '2025-11-21 08:31:16', '2025-11-22 22:45:38'),
(2, 1, '2025-11-25', 'paid', '2025-11-21 18:23:34', '2025-11-22 22:20:15'),
(3, 2, '2025-11-25', 'paid', '2025-11-21 18:23:34', '2025-11-21 18:24:05'),
(4, 3, '2025-11-25', 'paid', '2025-11-21 18:23:34', '2025-11-21 18:24:09'),
(5, 4, '2025-11-25', 'paid', '2025-11-21 18:23:34', '2025-11-21 18:25:03'),
(6, 5, '2025-11-25', 'paid', '2025-11-21 18:23:34', '2025-11-21 18:25:08'),
(7, 6, '2025-11-25', 'paid', '2025-11-21 18:23:34', '2025-11-21 18:25:13'),
(8, 7, '2025-11-25', 'paid', '2025-11-21 18:23:34', '2025-11-21 18:25:18'),
(9, 8, '2025-11-25', 'paid', '2025-11-21 18:23:34', '2025-11-21 18:25:22'),
(10, 9, '2025-11-25', 'paid', '2025-11-21 18:23:34', '2025-11-21 18:25:27'),
(11, 10, '2025-11-25', 'unpaid', '2025-11-21 18:23:34', '2025-11-21 18:23:34'),
(12, 11, '2025-11-25', 'paid', '2025-11-21 18:23:34', '2025-11-21 18:28:41'),
(13, 12, '2025-11-25', 'unpaid', '2025-11-21 18:23:34', '2025-11-21 18:23:34'),
(14, 13, '2025-11-25', 'paid', '2025-11-21 18:23:34', '2025-11-21 18:28:25'),
(15, 14, '2025-11-25', 'paid', '2025-11-21 18:23:34', '2025-11-21 18:25:48'),
(16, 15, '2025-11-25', 'paid', '2025-11-21 18:23:34', '2025-11-21 18:26:02'),
(17, 16, '2025-11-25', 'paid', '2025-11-21 18:23:34', '2025-11-21 18:27:50'),
(18, 17, '2025-11-25', 'unpaid', '2025-11-21 18:23:34', '2025-11-21 18:23:34'),
(19, 18, '2025-11-25', 'paid', '2025-11-21 18:23:34', '2025-11-21 18:26:58'),
(20, 19, '2025-11-25', 'paid', '2025-11-21 18:23:34', '2025-11-21 18:26:28'),
(21, 20, '2025-11-25', 'paid', '2025-11-21 18:23:34', '2025-11-21 18:28:34'),
(22, 21, '2025-11-25', 'unpaid', '2025-11-21 18:23:34', '2025-11-21 18:23:34'),
(23, 22, '2025-11-25', 'unpaid', '2025-11-21 18:23:34', '2025-11-21 18:23:34'),
(24, 23, '2025-11-25', 'unpaid', '2025-11-21 18:23:34', '2025-11-21 18:23:34'),
(25, 24, '2025-11-25', 'paid', '2025-11-21 18:23:34', '2025-11-21 18:24:18'),
(26, 25, '2025-11-25', 'paid', '2025-11-21 18:23:34', '2025-11-21 18:24:28'),
(27, 26, '2025-11-25', 'unpaid', '2025-11-21 18:23:34', '2025-11-21 18:23:34'),
(28, 27, '2025-11-25', 'paid', '2025-11-21 18:23:34', '2025-11-21 18:24:43'),
(29, 28, '2025-11-25', 'paid', '2025-11-21 18:23:34', '2025-11-21 18:26:16'),
(30, 29, '2025-11-25', 'paid', '2025-11-21 18:23:34', '2025-11-21 18:25:33'),
(31, 30, '2025-11-25', 'paid', '2025-11-21 18:23:34', '2025-11-21 18:25:56'),
(32, 31, '2025-11-25', 'paid', '2025-11-21 18:23:34', '2025-11-21 18:24:56'),
(33, 32, '2025-11-25', 'paid', '2025-11-21 18:23:34', '2025-11-21 18:24:49'),
(34, 33, '2025-11-25', 'unpaid', '2025-11-21 18:23:34', '2025-11-21 18:23:34'),
(35, 34, '2025-11-25', 'unpaid', '2025-11-21 18:23:34', '2025-11-21 18:23:34'),
(36, 35, '2025-11-25', 'paid', '2025-11-21 18:23:34', '2025-11-21 18:26:09'),
(37, 36, '2025-11-25', 'paid', '2025-11-21 18:23:34', '2025-11-21 18:25:41'),
(38, 37, '2025-11-25', 'paid', '2025-11-21 18:23:34', '2025-11-21 18:28:49'),
(39, 39, '2025-11-25', 'paid', '2025-11-21 18:35:55', '2025-11-21 18:36:02');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

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
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2025_11_21_142827_create_customers_table', 1),
(5, '2025_11_21_142831_create_invoices_table', 1),
(6, '2025_11_22_002352_add_phone_and_profile_to_customers_table', 2),
(7, '2025_11_22_154537_create_whatsapp_settings_table', 3),
(8, '2025_11_22_230550_add_internet_number_to_customers_table', 4),
(9, '2025_11_22_235520_create_companies_table', 5),
(10, '2025_11_23_002128_add_bank_details_to_companies_table', 6),
(11, '2025_11_23_012537_add_role_to_users_and_operator_to_customers', 7),
(12, '2025_11_23_073358_create_expenses_table', 8),
(13, '2025_11_23_080823_create_router_settings_table', 9);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `router_settings`
--

CREATE TABLE `router_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `host` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `port` int(11) NOT NULL DEFAULT 8728,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `router_settings`
--

INSERT INTO `router_settings` (`id`, `host`, `username`, `password`, `port`, `created_at`, `updated_at`) VALUES
(1, '223.23.23.1', 'azam', 'A4n6w!r4w4n', 7787, '2025-11-23 01:15:31', '2025-11-23 01:15:31');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('pOmzSniGZJS5qJM0i38J6rm3bFhXblZZUVAyKWUr', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiWlNVUXVvMThDZHJDU0tCMFE4YUZ5OWgzYUVxS3ZEcUxjZGhPR3RwZSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fX0=', 1763888252),
('T2IUrt0hto7blGzdvJlJGm4BnFEg5xHbKkJw2wMd', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiYjhXUlpEQ2V5M2s5dHVwM1YzV2xERDdrNE90U2w2RWg3UEtmQ09BSyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzY6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9yb3V0ZXItc2V0dGluZyI7czo1OiJyb3V0ZSI7czoxMjoicm91dGVyLmluZGV4Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTt9', 1763892122);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `role` enum('admin','operator') NOT NULL DEFAULT 'operator',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `role`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Administrator', 'admin@mikrotik.com', 'admin', NULL, '$2y$12$c650biHgVrENnXiymfqzn.q8yK8ktW5vg0JAR4DLmkn.3k/Tg7jCW', NULL, '2025-11-21 16:04:08', '2025-11-21 16:04:08'),
(2, 'Daras', 'daras@mikrotik.com', 'operator', NULL, '$2y$12$udGedw2oFX2/KFAMyCHySuJp3nuwQGFMG5DyhR594kWI6qQZXtvXa', NULL, '2025-11-22 20:07:51', '2025-11-22 20:07:51'),
(3, 'Wulan', 'wulan@mikrotik.com', 'operator', NULL, '$2y$12$YnBiGqzh7MBkIzFxzPYUn.tx31ZAQVFYOBQB8Pyg9WwjRO0nZOdmi', NULL, '2025-11-22 20:08:13', '2025-11-22 20:08:13');

-- --------------------------------------------------------

--
-- Table structure for table `whatsapp_settings`
--

CREATE TABLE `whatsapp_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `target_url` varchar(255) NOT NULL,
  `api_key` varchar(255) NOT NULL,
  `sender_number` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `whatsapp_settings`
--

INSERT INTO `whatsapp_settings` (`id`, `target_url`, `api_key`, `sender_number`, `created_at`, `updated_at`) VALUES
(1, 'https://wag.aangwi.my.id/send-message', 'ae4087xr', '6282143459930', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `companies`
--
ALTER TABLE `companies`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `customers_pppoe_username_unique` (`pppoe_username`),
  ADD KEY `customers_operator_id_foreign` (`operator_id`);

--
-- Indexes for table `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `invoices_customer_id_foreign` (`customer_id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `router_settings`
--
ALTER TABLE `router_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indexes for table `whatsapp_settings`
--
ALTER TABLE `whatsapp_settings`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `companies`
--
ALTER TABLE `companies`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `expenses`
--
ALTER TABLE `expenses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `router_settings`
--
ALTER TABLE `router_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `whatsapp_settings`
--
ALTER TABLE `whatsapp_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `customers`
--
ALTER TABLE `customers`
  ADD CONSTRAINT `customers_operator_id_foreign` FOREIGN KEY (`operator_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `invoices_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
