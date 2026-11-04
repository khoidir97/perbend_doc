/*
 Navicat Premium Data Transfer

 Source Server         : db_local_mysql
 Source Server Type    : MySQL
 Source Server Version : 80030
 Source Host           : localhost:3306
 Source Schema         : perbend_doc

 Target Server Type    : MySQL
 Target Server Version : 80030
 File Encoding         : 65001

 Date: 04/11/2026 08:19:23
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for cache
-- ----------------------------
DROP TABLE IF EXISTS `cache`;
CREATE TABLE `cache`  (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of cache
-- ----------------------------

-- ----------------------------
-- Table structure for cache_locks
-- ----------------------------
DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE `cache_locks`  (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of cache_locks
-- ----------------------------

-- ----------------------------
-- Table structure for dokumen
-- ----------------------------
DROP TABLE IF EXISTS `dokumen`;
CREATE TABLE `dokumen`  (
  `dok_id` int NOT NULL AUTO_INCREMENT,
  `dok_skpd` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `dok_file` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `dok_status` tinyint NULL DEFAULT NULL,
  `dok_tanggal_verif` datetime NULL DEFAULT NULL,
  `dok_created_at` timestamp NULL DEFAULT NULL,
  `dok_created_by` int NULL DEFAULT NULL,
  `dok_updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `dok_updated_by` int NULL DEFAULT NULL,
  `dok_deleted_at` timestamp NULL DEFAULT NULL,
  `dok_deleted_by` int NULL DEFAULT NULL,
  PRIMARY KEY (`dok_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of dokumen
-- ----------------------------
INSERT INTO `dokumen` VALUES (1, '035', 'dokumen_pdf/1793625393_PERTEK_BKN_KHOIDIR_KHONOFI.pdf', 0, '2026-11-03 08:09:36', '2026-11-02 13:16:33', 1, '2026-11-03 08:37:21', 1, '2026-11-03 08:37:21', 1);
INSERT INTO `dokumen` VALUES (2, '026', 'dokumen_pdf/1793625446_Honorarium_20260629152244.pdf', 0, '2026-11-03 13:56:24', '2026-11-02 13:17:26', 1, '2026-11-03 13:59:29', 1, NULL, NULL);
INSERT INTO `dokumen` VALUES (3, '057', 'dokumen_pdf/1793628069_spk.pdf', 0, '2026-11-03 08:23:28', '2026-11-02 21:01:09', 1, '2026-11-03 08:23:31', 1, '2026-11-03 08:23:31', 1);
INSERT INTO `dokumen` VALUES (4, '004', 'dokumen_pdf/1793669856_Absensi_rapat_Internal.pdf', 0, '2026-11-03 13:53:18', '2026-11-03 08:37:37', 1, '2026-11-03 13:53:18', 1, NULL, NULL);
INSERT INTO `dokumen` VALUES (5, '009', 'dokumen_pdf/1793669908_aktivasi_user_ppk_ta._2024_1_.pdf', 0, '2026-11-03 13:48:36', '2026-11-03 08:38:28', 1, '2026-11-03 13:48:36', 1, NULL, NULL);

-- ----------------------------
-- Table structure for failed_jobs
-- ----------------------------
DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE `failed_jobs`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `failed_jobs_uuid_unique`(`uuid` ASC) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of failed_jobs
-- ----------------------------

-- ----------------------------
-- Table structure for job_batches
-- ----------------------------
DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE `job_batches`  (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `cancelled_at` int NULL DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of job_batches
-- ----------------------------

-- ----------------------------
-- Table structure for jobs
-- ----------------------------
DROP TABLE IF EXISTS `jobs`;
CREATE TABLE `jobs`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED NULL DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `jobs_queue_index`(`queue` ASC) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of jobs
-- ----------------------------

-- ----------------------------
-- Table structure for migrations
-- ----------------------------
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of migrations
-- ----------------------------
INSERT INTO `migrations` VALUES (1, '0001_01_01_000000_create_users_table', 1);
INSERT INTO `migrations` VALUES (2, '0001_01_01_000001_create_cache_table', 1);
INSERT INTO `migrations` VALUES (3, '0001_01_01_000002_create_jobs_table', 1);

-- ----------------------------
-- Table structure for password_reset_tokens
-- ----------------------------
DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE `password_reset_tokens`  (
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of password_reset_tokens
-- ----------------------------

-- ----------------------------
-- Table structure for sessions
-- ----------------------------
DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions`  (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED NULL DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `sessions_user_id_index`(`user_id` ASC) USING BTREE,
  INDEX `sessions_last_activity_index`(`last_activity` ASC) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of sessions
-- ----------------------------
INSERT INTO `sessions` VALUES ('al6MDBU1QZoKrYjTQnej5nq7t4BHN44piDMQTkDy', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36 Edg/156.0.0.0', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiMlBlUTF3R1Y1cHJCSUFjWFNKZk1vN1ZRMXI3QTlQbzZxOG42cU5LTyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjk6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9kb2t1bWVuIjt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTt9', 1793689191);
INSERT INTO `sessions` VALUES ('eAXFzsTEa0883o1GmvqQExFTCwiLEVc5yjMJupDV', 1, '192.168.120.36', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36 Edg/156.0.0.0', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiaDFwMUNFUk41MmhtY2g4M3M3TXNlVkNOZVZ3eEo5UXpzMEVRWDRncyI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjM1OiJodHRwOi8vMTkyLjE2OC4xMjAuMTQ1OjgwMDAvZG9rdW1lbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7fQ==', 1793687854);

-- ----------------------------
-- Table structure for skpd
-- ----------------------------
DROP TABLE IF EXISTS `skpd`;
CREATE TABLE `skpd`  (
  `opd_id` int NOT NULL AUTO_INCREMENT,
  `opd_kode` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `opd_nama` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `opd_created_at` timestamp NULL DEFAULT NULL,
  `opd_created_by` int NULL DEFAULT NULL,
  `opd_updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `opd_updated_by` int NULL DEFAULT NULL,
  `opd_deleted_at` timestamp NULL DEFAULT NULL,
  `opd_deleted_by` int NULL DEFAULT NULL,
  PRIMARY KEY (`opd_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 58 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of skpd
-- ----------------------------
INSERT INTO `skpd` VALUES (1, '001', 'KEPALA DAERAH DAN WAKIL KEPALA DAERAH  KOTA PEKANBARU', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `skpd` VALUES (2, '002', 'SEKRETARIAT DAERAH KOTA PEKANBARU', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `skpd` VALUES (3, '003', 'BAPPEDA KOTA PEKANBARU', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `skpd` VALUES (4, '004', 'BADAN PENDAPATAN DAERAH KOTA PEKANBARU', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `skpd` VALUES (5, '005', 'DINAS KESEHATAN KOTA PEKANBARU', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `skpd` VALUES (6, '006', 'DINAS PEMADAM KEBAKARAN DAN PENYELAMATAN KOTA PEKANBARU', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `skpd` VALUES (7, '007', 'DINAS PERTANIAN DAN PERIKANAN KOTA PEKANBARU', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `skpd` VALUES (8, '008', 'DINAS PEMBERDAYAAN PEREMPUAN DAN PERLINDUNGAN ANAK KOTA PEKANBARU', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `skpd` VALUES (9, '009', 'DINAS PERHUBUNGAN KOTA PEKANBARU', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `skpd` VALUES (10, '010', 'BADAN PENANGGULANGAN BENCANA DAERAH KOTA PEKANBARU', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `skpd` VALUES (12, '012', 'DINAS KEBUDAYAAN DAN PARIWISATA KOTA PEKANBARU', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `skpd` VALUES (13, '013', 'DINAS KETAHANAN PANGAN KOTA PEKANBARU', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `skpd` VALUES (14, '014', 'DINAS KOPERASI DAN UKM KOTA PEKANBARU', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `skpd` VALUES (15, '015', 'DINAS LINGKUNGAN HIDUP DAN KEBERSIHAN KOTA PEKANBARU', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `skpd` VALUES (16, '016', 'DINAS KEPENDUDUKAN DAN PENCATATAN SIPIL KOTA PEKANBARU', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `skpd` VALUES (17, '017', 'SATUAN POLISI PAMONG PRAJA KOTA PEKANBARU', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `skpd` VALUES (18, '018', 'DINAS PENANAMAN MODAL DAN PTSP KOTA PEKANBARU', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `skpd` VALUES (19, '019', 'DINAS PERPUSTAKAAN DAN KEARSIPAN KOTA PEKANBARU', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `skpd` VALUES (20, '020', 'DINAS SOSIAL KOTA PEKANBARU', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `skpd` VALUES (21, '021', 'DINAS PERUMAHAN RAKYAT DAN KAWASAN PERMUKIMAN KOTA PEKANBARU', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `skpd` VALUES (22, '022', 'SEKRETARIAT DPRD KOTA PEKANBARU', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `skpd` VALUES (23, '023', 'INSPEKTORAT KOTA PEKANBARU', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `skpd` VALUES (24, '024', 'DINAS PEKERJAAN UMUM DAN PENATAAN RUANG KOTA PEKANBARU', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `skpd` VALUES (25, '025', 'DINAS PERDAGANGAN DAN PERINDUSTRIAN KOTA PEKANBARU', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `skpd` VALUES (26, '026', 'BADAN KESATUAN BANGSA DAN POLITIK KOTA PEKANBARU', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `skpd` VALUES (27, '027', 'DINAS PERTANAHAN KOTA PEKANBARU', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `skpd` VALUES (28, '028', 'DINAS PENDIDIKAN KOTA PEKANBARU', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `skpd` VALUES (29, '029', 'SD NEGERI SE KOTA PEKANBARU', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `skpd` VALUES (30, '030', 'SD SWASTA SE KOTA PEKANBARU                                           ', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `skpd` VALUES (31, '031', 'SMP NEGERI SE KOTA PEKANBARU', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `skpd` VALUES (32, '032', 'SMA NEGERI SE KOTA PEKANBARU                                          ', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `skpd` VALUES (33, '033', 'SMK NEGERI SE KOTA PEKANBARU                                          ', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `skpd` VALUES (34, '034', 'DINAS TENAGA KERJA KOTA PEKANBARU', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `skpd` VALUES (35, '035', 'BADAN KEPEGAWAIAN DAN PENGEMBANGAN SDM KOTA PEKANBARU', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `skpd` VALUES (36, '036', 'BPKAD KOTA PEKANBARU', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `skpd` VALUES (37, '037', 'DINAS KEPEMUDAAN DAN OLAHRAGA KOTA PEKANBARU', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `skpd` VALUES (38, '038', 'KANTOR KECAMATAN PEKANBARU KOTA', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `skpd` VALUES (39, '039', 'KANTOR KECAMATAN SENAPELAN KOTA PEKANBARU', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `skpd` VALUES (40, '040', 'KANTOR KECAMATAN LIMA PULUH KOTA PEKANBARU', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `skpd` VALUES (41, '041', 'KANTOR KECAMATAN SUKAJADI KOTA PEKANBARU', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `skpd` VALUES (42, '042', 'KANTOR KECAMATAN SAIL KOTA PEKANBARU', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `skpd` VALUES (43, '043', 'KANTOR KECAMATAN RUMBAI BARAT KOTA PEKANBARU', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `skpd` VALUES (44, '044', 'KANTOR KECAMATAN BINAWIDYA KOTA PEKANBARU', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `skpd` VALUES (45, '045', 'KANTOR KECAMATAN BUKIT RAYA KOTA PEKANBARU', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `skpd` VALUES (46, '046', 'KANTOR KECAMATAN RUMBAI KOTA PEKANBARU', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `skpd` VALUES (47, '047', 'KANTOR KECAMATAN MARPOYAN DAMAI KOTA PEKANBARU', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `skpd` VALUES (48, '048', 'KANTOR KECAMATAN PAYUNG SEKAKI KOTA PEKANBARU', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `skpd` VALUES (49, '049', 'KANTOR KECAMATAN TENAYAN RAYA KOTA PEKANBARU', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `skpd` VALUES (51, '051', 'DINAS KEHUTANAN KOTA PEKANBARU                                        ', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `skpd` VALUES (52, '052', 'BADAN PENELITIAN DAN PENGEMBANGAN KOTA PEKANBARU', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `skpd` VALUES (53, '053', 'DISKOMINFO STATISTIK DAN PERSANDIAN KOTA PEKANBARU', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `skpd` VALUES (54, '054', 'DINAS PENGENDALIAN PENDUDUK DAN KELUARGA BERENCANA KOTA PEKANBARU', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `skpd` VALUES (55, '055', 'KANTOR KECAMATAN KULIM KOTA PEKANBARU', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `skpd` VALUES (56, '056', 'KANTOR KECAMATAN RUMBAI TIMUR KOTA PEKANBARU', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `skpd` VALUES (57, '057', 'KANTOR KECAMATAN TUAH MADANI KOTA PEKANBARU', NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `skpd` VALUES (58, '058', 'RUMAH SAKIT DAERAH MADANI KOTA PEKANBARU', NULL, NULL, NULL, NULL, NULL, NULL);

-- ----------------------------
-- Table structure for users
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `kd_skpd` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `role` tinyint NULL DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `users_email_unique`(`email` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 500 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of users
-- ----------------------------
INSERT INTO `users` VALUES (1, 'admin', NULL, 0, 'admin@bpkad.com', NULL, '$2y$12$la53g0ifi16jNZQJeptuo.X7pPGhD9LfxmEMcWUp3qXrnsEHhfH66', NULL, '2026-11-02 08:33:50', '2026-11-02 09:45:23');
INSERT INTO `users` VALUES (229, 'dinkes2', '005', 1, 'dinkes2@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (230, 'atenayan', '049', 1, 'atenayan@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (231, 'perkim', '021', 1, 'perkim@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (232, 'disdik9', '028', 1, 'disdik9@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (233, 'disdik8', '028', 1, 'disdik8@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (234, 'distankan', '007', 1, 'distankan@gmail.com', NULL, '$2y$12$gRHElt.HJkQa2mioerZ3ieq.ypyOL5vUM4qAgcjvkYlOWOpYDt7Uq', NULL, NULL, '2026-04-14 10:22:45');
INSERT INTO `users` VALUES (235, 'rendy', '036', 1, 'rendy@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (236, 'kec50', '040', 1, 'kec50@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (237, 'asetwan', '022', 1, 'asetwan@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (239, 'pkmmuarafajar', '005', 1, 'pkmmuarafajar@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (241, 'dpmptsp', '018', 1, 'dpmptsp@gmail.com', NULL, '$2y$12$KLyZXqdVOr1bcsDJ9W7RCOU7Hi3lAIYZJE8REvE1B2pJvSFw0C82a', NULL, NULL, '2026-04-10 09:13:56');
INSERT INTO `users` VALUES (242, 'satpolpp', '017', 1, 'satpolpp@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (243, 'disdik5', '028', 1, 'disdik5@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (244, 'kecsenapelan', '039', 1, 'kecsenapelan@gmail.com', NULL, '$2y$12$sV/5nw0t1/vUgvJIqWUAoO43IyUstsLgSFfVFoGeyFIhEOGgdCSZS', NULL, NULL, '2026-04-14 11:28:17');
INSERT INTO `users` VALUES (245, 'kecsail', '042', 1, 'kecsail@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (247, 'dispusip', '019', 1, 'dispusip@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (248, 'dyna', '036', 0, 'dyna@gmail.com', NULL, '$2y$12$hdiFQb8bJ7Ev6Mo7rXj4.OUy1aAcF7gcMNglM4nfrQGavfj2KYTEK', NULL, '2026-04-10 08:23:47', '2026-04-10 08:23:47');
INSERT INTO `users` VALUES (249, 'iflk', '005', 1, 'iflk@gmail.com', NULL, '$2y$12$kZtLNmAPEHFqCRd7Ov3B4uooER12RAl.wdYCKY27d0gVPdvmciumS', NULL, '2026-04-16 09:17:49', '2026-04-16 09:17:49');
INSERT INTO `users` VALUES (250, 'labkes', '005', 1, 'labkes@gmail.com', NULL, '$2y$12$3IhpHIpdQGeHa0tn3EoTp.gtbxQ0lAPADCpRfJ0EKHkvaWct2lXTe', NULL, '2026-04-16 09:38:46', '2026-04-16 09:38:46');
INSERT INTO `users` VALUES (251, 'khoidirnew', '036', 0, 'khoidirnew@gmail.com', NULL, '$2y$12$fx083paGCNT/aLDLkCCSKukawdT.xbxGqfwLd7dTzcPgKvmhXAGuK', NULL, '2026-06-19 18:17:50', '2026-06-19 18:17:50');
INSERT INTO `users` VALUES (252, 'abappeda', '003', 1, 'abappeda@gmail.com', NULL, '$2y$12$LzD4/Gbsy7q0yhvW4peHauhMf/cz.BXj4gY9niz7BYZNSZ/yfyYEu', NULL, '2026-06-22 12:14:20', '2026-06-22 12:14:20');
INSERT INTO `users` VALUES (253, 'khoidir', '036', 0, 'khoidir@gmail.com', NULL, '$2y$12$L/F1Ga5lcFHS7.jh3x7rDuJz18tHBfIAEtyUIROqS4CZABhk6Rm.G', NULL, '2025-12-15 01:43:50', '2026-06-19 18:18:28');
INSERT INTO `users` VALUES (254, 'abaltbang', '052', 1, 'abaltbang@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (255, 'dpmptsp-', '018', 1, 'dpmptsp-@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (256, 'asetda', '002', 1, 'asetda@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (257, 'disdaldukkb-', '054', 1, 'disdaldukkb-@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (258, 'dinkes3', '005', 1, 'dinkes3@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (259, 'bapenda-', '004', 1, 'bapenda-@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (260, 'setdako', '002', 1, 'setdako@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (261, 'pkmsail', '005', 1, 'pkmsail@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (262, 'pkmsimpbaru', '005', 1, 'pkmsimpbaru@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (263, 'disdik2', '028', 1, 'disdik2@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (264, 'kecmarpdamai', '047', 1, 'kecmarpdamai@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (265, 'aketapang', '013', 1, 'aketapang@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (266, 'adispusip', '019', 1, 'adispusip@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (267, 'kecrumbai', '046', 1, 'kecrumbai@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (268, 'pkmsimp3', '005', 1, 'pkmsimp3@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (269, 'pkmgaruda', '005', 1, 'pkmgaruda@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (270, 'adistan', '007', 1, 'adistan@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (271, 'kecrumbaitimur-', '056', 1, 'kecrumbaitimur-@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (272, 'adinsos', '020', 1, 'adinsos@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (273, 'disdukcapil', '016', 1, 'disdukcapil@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (274, 'pkmsido', '005', 1, 'pkmsido@gmail.com', NULL, '$2y$12$hFi7aeS7LOFkoTn6Pe4x/.QMEaOFxC32A1.34GDa7QTvFv17Dw6Ku', NULL, NULL, '2026-04-14 10:52:05');
INSERT INTO `users` VALUES (275, 'dp3apm', '008', 1, 'dp3apm@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (276, 'pkmlangsat', '005', 1, 'pkmlangsat@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (277, 'dinkes4', '005', 1, 'dinkes4@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (278, 'disnaker-', '034', 1, 'disnaker-@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (279, 'pkmmelur', '005', 1, 'pkmmelur@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (280, 'pkmhrpraya', '005', 1, 'pkmhrpraya@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (281, 'pkm50', '005', 1, 'pkm50@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (282, 'dinkes1', '005', 1, 'dinkes1@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (283, 'abpkad', '036', 1, 'abpkad@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (284, 'pkmumbansari', '005', 1, 'pkmumbansari@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (285, 'dinsos', '020', 1, 'dinsos@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (286, 'pkmpkukota', '005', 1, 'pkmpkukota@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (287, 'pkmpygskk', '005', 1, 'pkmpygskk@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (288, 'pkmrumbaibukit', '005', 1, 'pkmrumbaibukit@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (289, 'pkmrejosari', '005', 1, 'pkmrejosari@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (290, 'dinkes', '005', 1, 'dinkes@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (291, 'sekwan', '022', 1, 'sekwan@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (292, 'pkmsaptataruna', '005', 1, 'pkmsaptataruna@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (293, 'bpbd', '010', 1, 'bpbd@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (294, 'bapenda', '004', 1, 'bapenda@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (295, 'pkmsenapelan', '005', 1, 'pkmsenapelan@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (296, 'Sekda', '002', 1, 'Sekda@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (297, 'kecbukitraya', '045', 1, 'kecbukitraya@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (298, 'andri', '036', 0, 'andri@gmail.com', NULL, '$2y$12$k8QcLWwkAdUG0.l/5gA/HO2tpc3lV3z0EYEHuE5nAIikc7ZtLEucC', NULL, NULL, '2026-04-10 09:03:48');
INSERT INTO `users` VALUES (299, 'pkmrumbai', '005', 1, 'pkmrumbai@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (300, 'perkim-', '021', 1, 'perkim-@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (301, 'ruyanick', '036', 1, 'ruyanick@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (302, 'bkpsdm', '035', 1, 'bkpsdm@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (303, 'kesbangpol', '026', 1, 'kesbangpol@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (304, 'kectenayanraya', '049', 1, 'kectenayanraya@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (305, 'disperindag', '025', 1, 'disperindag@gmail.com', NULL, '$2y$12$XaW1qNuaOxSlK2uznlPTT.rYStmET7KGEQLItZEKPvUINppZg1ChG', NULL, NULL, '2026-04-14 11:36:48');
INSERT INTO `users` VALUES (306, 'pkmkw', '005', 1, 'pkmkw@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (307, 'pusmelur', '005', 1, 'pusmelur@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (308, 'DISDIK-', '028', 1, 'DISDIK-@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (309, 'pkmrisido', '005', 1, 'pkmrisido@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (310, 'kecpygskk', '048', 1, 'kecpygskk@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (311, 'asatpolpp', '017', 1, 'asatpolpp@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (312, 'anto', '036', 1, 'anto@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (313, 'ketapang', '013', 1, 'ketapang@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (314, 'dispora', '037', 1, 'dispora@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (315, 'keckulim', '055', 1, 'keckulim@gmail.com', NULL, '$2y$12$qjio5.4ShkWfKtE3JsLl5.KxULGKQsHitKFfWi7ru3yzJQPUFsDHa', NULL, NULL, '2026-06-22 13:31:09');
INSERT INTO `users` VALUES (316, 'apertanahan', '027', 1, 'apertanahan@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (317, 'puspkukota', '005', 1, 'puspkukota@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (318, 'diperindag-', '026', 1, 'diperindag-@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (319, 'pkmtenayanraya', '005', 1, 'pkmtenayanraya@gmail.com', NULL, '$2y$12$qiz7orSi1V1eLHuJpOUuwu4G8DEFVJnp14vdL4jTX5YPyfw5G/8BW', NULL, NULL, '2026-04-13 09:10:54');
INSERT INTO `users` VALUES (320, 'disdik', '028', 1, 'disdik@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (321, 'akecrumbaibr', '043', 1, 'akecrumbaibr@gmail.com', NULL, '$2y$12$FeTXu0/7PhpunyX4nB0/EeK/vZ003D6bfqaXK4Kqhqz.z/BPNAiaO', NULL, NULL, '2026-05-12 09:48:24');
INSERT INTO `users` VALUES (322, 'kecrumbaibr', '043', 1, 'kecrumbaibr@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (323, 'kectenayanraya-', '049', 1, 'kectenayanraya-@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (324, 'kecbinawidya', '044', 1, 'kecbinawidya@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (325, 'PUPR', '024', 1, 'PUPR@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (326, 'disbudpar', '012', 1, 'disbudpar@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (327, 'dhena', '036', 1, 'dhena@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (328, 'balitbang', '052', 1, 'balitbang@gmail.com', NULL, '$2y$12$6oFNnjaqa7lJ5I1bIyYuMuVMWc5Bx2OzBCQf04ZBTuyvrb9DDh5Xi', NULL, NULL, '2026-04-14 10:37:38');
INSERT INTO `users` VALUES (329, 'adisbupar', '012', 1, 'adisbupar@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (330, 'kecpkukota', '038', 1, 'kecpkukota@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (331, 'akesbangpol', '026', 1, 'akesbangpol@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (332, 'pertanahan', '027', 1, 'pertanahan@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (333, 'abinawidya', '044', 1, 'abinawidya@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (334, 'adispora', '037', 1, 'adispora@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (335, 'disdik1', '028', 1, 'disdik1@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (336, 'setda', '002', 1, 'setda@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (337, 'adishub', '009', 1, 'adishub@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (338, 'inspektorat', '023', 1, 'inspektorat@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (339, 'dishub', '009', 1, 'dishub@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (340, 'disdaldukkb', '054', 1, 'disdaldukkb@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (341, 'bappeda', '003', 1, 'bappeda@gmail.com', NULL, '$2y$12$LYh.QL9s2H5516nS5bvJuuIFLHKscwS9WD8PP1ztcu7/xtAlwiX1q', NULL, NULL, '2026-04-13 10:10:35');
INSERT INTO `users` VALUES (342, 'kectuahmadani', '057', 1, 'kectuahmadani@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (343, 'diskominfo', '053', 1, 'diskominfo@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (344, 'damkar', '006', 1, 'damkar@gmail.com', NULL, '$2y$12$eDVnPW1zVVtSAHBMw7vf8.HsohgFOV0YaCMyk1k0VHRfDE44.L8Em', NULL, NULL, '2026-05-08 13:44:11');
INSERT INTO `users` VALUES (345, 'rsdmadani', '005', 1, 'rsdmadani@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, '2026-05-01 06:39:40');
INSERT INTO `users` VALUES (346, 'adisperindag', '025', 1, 'adisperindag@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (347, 'dispusip-', '019', 1, 'dispusip-@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (348, 'kecrumbaitimur', '056', 1, 'kecrumbaitimur@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (349, 'diskopukm', '014', 1, 'diskopukm@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (350, 'adistankan', '007', 1, 'adistankan@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (351, 'dlhk', '015', 1, 'dlhk@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);
INSERT INTO `users` VALUES (352, 'kecsukajadi', '041', 1, 'kecsukajadi@gmail.com', NULL, '$2y$12$IwmZwk6OO54WYucGS5xWCOgjKxIbD7noaacgKLWBPrwQO0hyXwo5q', NULL, NULL, NULL);

SET FOREIGN_KEY_CHECKS = 1;
