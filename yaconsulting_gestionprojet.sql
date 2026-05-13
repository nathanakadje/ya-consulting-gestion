-- MySQL dump 10.13  Distrib 8.0.45, for Linux (x86_64)
--
-- Host: localhost    Database: ya_consulting_gestion
-- ------------------------------------------------------
-- Server version	8.0.45-0ubuntu0.22.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `activity_logs`
--

DROP TABLE IF EXISTS `activity_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `activity_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `loggable_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `loggable_id` bigint unsigned DEFAULT NULL,
  `action` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `old_values` json DEFAULT NULL,
  `new_values` json DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `activity_logs_user_id_foreign` (`user_id`),
  KEY `activity_logs_loggable_type_loggable_id_index` (`loggable_type`,`loggable_id`),
  CONSTRAINT `activity_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_logs`
--

LOCK TABLES `activity_logs` WRITE;
/*!40000 ALTER TABLE `activity_logs` DISABLE KEYS */;
INSERT INTO `activity_logs` VALUES (1,1,'App\\Models\\Project',6,'created_project','Projet \"Gestion de projet\" créé',NULL,NULL,'127.0.0.1','2026-05-07 12:32:34','2026-05-07 12:32:34');
/*!40000 ALTER TABLE `activity_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
INSERT INTO `cache` VALUES ('laravel-cache-49a34542cff86b427f1a0b4d96b74a74','i:1;',1778163824),('laravel-cache-49a34542cff86b427f1a0b4d96b74a74:timer','i:1778163824;',1778163824);
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clients`
--

DROP TABLE IF EXISTS `clients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `clients` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_person` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Côte d''Ivoire',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clients`
--

LOCK TABLES `clients` WRITE;
/*!40000 ALTER TABLE `clients` DISABLE KEYS */;
INSERT INTO `clients` VALUES (1,'Ministère des Infrastructures','contact@infrastructures.gouv.ci','+225 27 20 21 00 00','M. Bamba Coulibaly',NULL,'Abidjan','Côte d\'Ivoire',NULL,'2026-05-07 12:15:28','2026-05-07 12:15:28'),(2,'SODECI','dg@sodeci.ci','+225 27 20 25 25 25','Mme Adjoua Konan',NULL,'Abidjan','Côte d\'Ivoire',NULL,'2026-05-07 12:15:28','2026-05-07 12:15:28'),(3,'Orange Côte d\'Ivoire','procurement@orange.ci','+225 07 00 00 00 00','M. Diallo Ibrahim',NULL,'Abidjan','Côte d\'Ivoire',NULL,'2026-05-07 12:15:28','2026-05-07 12:15:28'),(4,'Groupe NSIA','info@groupensia.com','+225 27 20 31 90 00','Mme Traoré Fatoumata',NULL,'Abidjan','Côte d\'Ivoire',NULL,'2026-05-07 12:15:28','2026-05-07 12:15:28'),(5,'RTI','dg@rti.ci','+225 27 22 48 01 01','M. Koné Mamadou',NULL,'Abidjan','Côte d\'Ivoire',NULL,'2026-05-07 12:15:28','2026-05-07 12:15:28');
/*!40000 ALTER TABLE `clients` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `expense_categories`
--

DROP TABLE IF EXISTS `expense_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `expense_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `color` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#6366f1',
  `icon` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'receipt_long',
  `is_system` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `expense_categories`
--

LOCK TABLES `expense_categories` WRITE;
/*!40000 ALTER TABLE `expense_categories` DISABLE KEYS */;
INSERT INTO `expense_categories` VALUES (1,'Achat matériel','#3b82f6','inventory_2',1,'2026-05-07 12:15:28','2026-05-07 12:15:28'),(2,'Sous-traitance','#8b5cf6','engineering',1,'2026-05-07 12:15:28','2026-05-07 12:15:28'),(3,'Déplacement','#f59e0b','directions_car',1,'2026-05-07 12:15:28','2026-05-07 12:15:28'),(4,'Communication','#10b981','campaign',1,'2026-05-07 12:15:28','2026-05-07 12:15:28'),(5,'Impôts / taxes','#ef4444','account_balance',1,'2026-05-07 12:15:28','2026-05-07 12:15:28'),(6,'Main d\'œuvre','#0ea5e9','groups',1,'2026-05-07 12:15:28','2026-05-07 12:15:28'),(7,'Transport','#f97316','local_shipping',1,'2026-05-07 12:15:28','2026-05-07 12:15:28'),(8,'Divers','#6b7280','category',1,'2026-05-07 12:15:28','2026-05-07 12:15:28');
/*!40000 ALTER TABLE `expense_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `expenses`
--

DROP TABLE IF EXISTS `expenses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `expenses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `project_id` bigint unsigned NOT NULL,
  `category_id` bigint unsigned NOT NULL,
  `created_by` bigint unsigned NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `expense_date` date NOT NULL,
  `receipt_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `status` enum('pending','validated','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'validated',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `expenses_project_id_foreign` (`project_id`),
  KEY `expenses_category_id_foreign` (`category_id`),
  KEY `expenses_created_by_foreign` (`created_by`),
  CONSTRAINT `expenses_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `expense_categories` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `expenses_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `expenses_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `expenses`
--

LOCK TABLES `expenses` WRITE;
/*!40000 ALTER TABLE `expenses` DISABLE KEYS */;
INSERT INTO `expenses` VALUES (1,1,6,1,'Honoraires consultant principal',1875000.00,'2024-01-22',NULL,NULL,'validated','2026-05-07 12:15:28','2026-05-07 12:15:28',NULL),(2,1,1,2,'Achat équipements informatiques',1500000.00,'2024-01-29',NULL,NULL,'validated','2026-05-07 12:15:28','2026-05-07 12:15:28',NULL),(3,1,2,1,'Sous-traitance expertise réseau',1250000.00,'2024-02-05',NULL,NULL,'validated','2026-05-07 12:15:28','2026-05-07 12:15:28',NULL),(4,1,3,2,'Frais de déplacement terrain',500000.00,'2024-02-12',NULL,NULL,'validated','2026-05-07 12:15:28','2026-05-07 12:15:28',NULL),(5,1,4,1,'Communication et documentation',375000.00,'2024-02-19',NULL,NULL,'validated','2026-05-07 12:15:28','2026-05-07 12:15:28',NULL),(6,1,5,2,'TVA et droits de timbre',625000.00,'2024-02-26',NULL,NULL,'validated','2026-05-07 12:15:28','2026-05-07 12:15:28',NULL),(7,2,6,1,'Honoraires consultant principal',6750000.00,'2024-03-08',NULL,NULL,'validated','2026-05-07 12:15:28','2026-05-07 12:15:28',NULL),(8,2,1,2,'Achat équipements informatiques',5400000.00,'2024-03-15',NULL,NULL,'validated','2026-05-07 12:15:28','2026-05-07 12:15:28',NULL),(9,2,2,1,'Sous-traitance expertise réseau',4500000.00,'2024-03-22',NULL,NULL,'validated','2026-05-07 12:15:29','2026-05-07 12:15:29',NULL),(10,2,3,2,'Frais de déplacement terrain',1800000.00,'2024-03-29',NULL,NULL,'validated','2026-05-07 12:15:29','2026-05-07 12:15:29',NULL),(11,2,4,1,'Communication et documentation',1350000.00,'2024-04-05',NULL,NULL,'validated','2026-05-07 12:15:29','2026-05-07 12:15:29',NULL),(12,2,5,2,'TVA et droits de timbre',2250000.00,'2024-04-12',NULL,NULL,'validated','2026-05-07 12:15:30','2026-05-07 12:15:30',NULL),(13,3,6,1,'Honoraires consultant principal',1200000.00,'2024-02-08',NULL,NULL,'validated','2026-05-07 12:15:30','2026-05-07 12:15:30',NULL),(14,3,1,2,'Achat équipements informatiques',960000.00,'2024-02-15',NULL,NULL,'validated','2026-05-07 12:15:30','2026-05-07 12:15:30',NULL),(15,3,2,1,'Sous-traitance expertise réseau',800000.00,'2024-02-22',NULL,NULL,'validated','2026-05-07 12:15:30','2026-05-07 12:15:30',NULL),(16,3,3,2,'Frais de déplacement terrain',320000.00,'2024-02-29',NULL,NULL,'validated','2026-05-07 12:15:30','2026-05-07 12:15:30',NULL),(17,3,4,1,'Communication et documentation',240000.00,'2024-03-07',NULL,NULL,'validated','2026-05-07 12:15:30','2026-05-07 12:15:30',NULL),(18,3,5,2,'TVA et droits de timbre',400000.00,'2024-03-14',NULL,NULL,'validated','2026-05-07 12:15:30','2026-05-07 12:15:30',NULL),(19,4,6,1,'Honoraires consultant principal',3300000.00,'2024-04-22',NULL,NULL,'validated','2026-05-07 12:15:31','2026-05-07 12:15:31',NULL),(20,4,1,2,'Achat équipements informatiques',2640000.00,'2024-04-29',NULL,NULL,'validated','2026-05-07 12:15:31','2026-05-07 12:15:31',NULL),(21,4,2,1,'Sous-traitance expertise réseau',2200000.00,'2024-05-06',NULL,NULL,'validated','2026-05-07 12:15:31','2026-05-07 12:15:31',NULL),(22,4,3,2,'Frais de déplacement terrain',880000.00,'2024-05-13',NULL,NULL,'validated','2026-05-07 12:15:31','2026-05-07 12:15:31',NULL),(23,4,4,1,'Communication et documentation',660000.00,'2024-05-20',NULL,NULL,'validated','2026-05-07 12:15:31','2026-05-07 12:15:31',NULL),(24,4,5,2,'TVA et droits de timbre',1100000.00,'2024-05-27',NULL,NULL,'validated','2026-05-07 12:15:31','2026-05-07 12:15:31',NULL),(25,5,6,1,'Honoraires consultant principal',975000.00,'2024-05-08',NULL,NULL,'validated','2026-05-07 12:15:31','2026-05-07 12:15:31',NULL),(26,5,1,2,'Achat équipements informatiques',780000.00,'2024-05-15',NULL,NULL,'validated','2026-05-07 12:15:31','2026-05-07 12:15:31',NULL),(27,5,2,1,'Sous-traitance expertise réseau',650000.00,'2024-05-22',NULL,NULL,'validated','2026-05-07 12:15:31','2026-05-07 12:15:31',NULL),(28,5,3,2,'Frais de déplacement terrain',260000.00,'2024-05-29',NULL,NULL,'validated','2026-05-07 12:15:32','2026-05-07 12:15:32',NULL),(29,5,4,1,'Communication et documentation',195000.00,'2024-06-05',NULL,NULL,'validated','2026-05-07 12:15:32','2026-05-07 12:15:32',NULL),(30,5,5,2,'TVA et droits de timbre',325000.00,'2024-06-12',NULL,NULL,'validated','2026-05-07 12:15:32','2026-05-07 12:15:32',NULL);
/*!40000 ALTER TABLE `expenses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

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

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_04_09_111012_create_clients_table',1),(5,'2026_04_09_111014_create_projects_table',1),(6,'2026_04_09_112346_create_expense_categories_table',1),(7,'2026_04_09_112515_create_expenses_table',1),(8,'2026_04_09_112611_create_activity_logs_table',1),(9,'2026_04_10_121230_add_two_factor_columns_to_users_table',1),(10,'2026_04_10_121259_create_personal_access_tokens_table',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

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

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `projects`
--

DROP TABLE IF EXISTS `projects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `projects` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `reference` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `client_id` bigint unsigned NOT NULL,
  `created_by` bigint unsigned NOT NULL,
  `project_lead_id` bigint unsigned DEFAULT NULL,
  `budget` decimal(15,2) NOT NULL DEFAULT '0.00',
  `budget_main_oeuvre` decimal(15,2) NOT NULL DEFAULT '0.00',
  `budget_materiel` decimal(15,2) NOT NULL DEFAULT '0.00',
  `budget_transport` decimal(15,2) NOT NULL DEFAULT '0.00',
  `budget_autres` decimal(15,2) NOT NULL DEFAULT '0.00',
  `start_date` date NOT NULL,
  `end_date_planned` date NOT NULL,
  `end_date_actual` date DEFAULT NULL,
  `status` enum('en_cours','termine','en_pause') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'en_cours',
  `suppliers` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `projects_reference_unique` (`reference`),
  KEY `projects_client_id_foreign` (`client_id`),
  KEY `projects_created_by_foreign` (`created_by`),
  KEY `projects_project_lead_id_foreign` (`project_lead_id`),
  CONSTRAINT `projects_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `projects_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `projects_project_lead_id_foreign` FOREIGN KEY (`project_lead_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `projects`
--

LOCK TABLES `projects` WRITE;
/*!40000 ALTER TABLE `projects` DISABLE KEYS */;
INSERT INTO `projects` VALUES (1,'Audit Système Informatique SODECI','YA-2024-001','Audit complet du système d\'information et recommandations de sécurité.',2,1,2,12500000.00,5000000.00,3750000.00,1250000.00,2500000.00,'2024-01-15','2024-06-30','2024-06-25','termine',NULL,'2026-05-07 12:15:28','2026-05-07 12:15:28',NULL),(2,'Déploiement Réseau Fibre Optique RTI','YA-2024-002','Installation et configuration du réseau fibre optique pour les studios de la RTI.',5,1,2,45000000.00,18000000.00,13500000.00,4500000.00,9000000.00,'2024-03-01','2024-12-31',NULL,'en_cours',NULL,'2026-05-07 12:15:28','2026-05-07 12:15:28',NULL),(3,'Étude de faisabilité Ministère Infras.','YA-2024-003','Étude technico-économique pour la modernisation des infrastructures routières.',1,1,2,8000000.00,3200000.00,2400000.00,800000.00,1600000.00,'2024-02-01','2024-04-30','2024-05-02','termine',NULL,'2026-05-07 12:15:30','2026-05-07 12:15:30',NULL),(4,'Consulting Digital Orange CI','YA-2024-004','Accompagnement transformation digitale et conduite du changement.',3,1,2,22000000.00,8800000.00,6600000.00,2200000.00,4400000.00,'2024-04-15','2025-04-14',NULL,'en_cours',NULL,'2026-05-07 12:15:30','2026-05-07 12:15:30',NULL),(5,'Analyse Risques NSIA','YA-2024-005','Analyse et cartographie des risques opérationnels.',4,1,2,6500000.00,2600000.00,1950000.00,650000.00,1300000.00,'2024-05-01','2024-08-31',NULL,'en_pause',NULL,'2026-05-07 12:15:31','2026-05-07 12:15:31',NULL),(6,'Gestion de projet','YA-2026-006',NULL,1,1,2,50000000.00,10000000.00,2990000.00,2000000.00,2193000.00,'2026-04-23','2026-05-10',NULL,'termine',NULL,'2026-05-07 12:32:34','2026-05-07 12:32:34',NULL);
/*!40000 ALTER TABLE `projects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

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

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('GcPqMwITWZ6zM1MfbkJxDwAvG9UqNGGMX0lOvMEc',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiMHNPQkxObWNGMWlFVGRKajV6eEZuZFFBZ1YxbzRvWW40MnhDVEtRcSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1778163403),('oukYA5PRWmgUggvA9mAGfnGxYWFHGxg6xqlKVWa5',1,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','YTo1OntzOjY6Il90b2tlbiI7czo0MDoiSHlJeENZZFFOQkZLWlMxeWFnTEo3dDVPWjN1UTl5ZTN2ekZBYTkwYyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7czoyMToicGFzc3dvcmRfaGFzaF9zYW5jdHVtIjtzOjY0OiIyYzZjOGJhZDVmMWY0Mzg1ZWJhM2E5MTY2Mzk4MGNlM2Y3NDA3MjhhODBmNjRjN2M0N2JiNzAzMGFiMTVlODIyIjt9',1778164354);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `two_factor_secret` text COLLATE utf8mb4_unicode_ci,
  `two_factor_recovery_codes` text COLLATE utf8mb4_unicode_ci,
  `two_factor_confirmed_at` timestamp NULL DEFAULT NULL,
  `role` enum('admin','project_manager','staff_member') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'staff_member',
  `theme` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'light',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `current_team_id` bigint unsigned DEFAULT NULL,
  `profile_photo_path` varchar(2048) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Administrateur YA','admin@yaconsulting.ci','2026-05-07 12:15:14','$2y$12$MpxhGki5Zn.CwwZlFwPfAumEAAOdkb1h41ZhZbHPM2cE0mXvJjXRa',NULL,NULL,NULL,'admin','light',NULL,NULL,NULL,'2026-05-07 12:15:20','2026-05-07 12:15:20'),(2,'Kouadio Jean','manager@yaconsulting.ci','2026-05-07 12:15:21','$2y$12$s8BpAO.bchtK4bxi7MlSV.rTYJNG1UKy/aon7hTPC2mXCPz.alMdy',NULL,NULL,NULL,'project_manager','light',NULL,NULL,NULL,'2026-05-07 12:15:21','2026-05-07 12:15:21'),(3,'Amenan Sophie','staff@yaconsulting.ci','2026-05-07 12:15:28','$2y$12$HNxYO.LKHydTblnKpJ2LXO0gNaZa3i4zPCd4aS23i5dAURz7PgRfW',NULL,NULL,NULL,'staff_member','dark',NULL,NULL,NULL,'2026-05-07 12:15:28','2026-05-07 12:15:28');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-05-07 17:37:06
