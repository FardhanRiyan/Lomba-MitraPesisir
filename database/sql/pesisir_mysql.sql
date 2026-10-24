-- ============================================================================
-- MitraPesisir — DDL MySQL 8 (utf8mb4) + data awal
-- Setara dengan migrasi Laravel di database/migrations. Gunakan SALAH SATU:
--   (a) php artisan migrate --seed          (disarankan)
--   (b) mysql -u USER -p pesisir_db < database/sql/pesisir_mysql.sql
-- Opsi (b) tidak mengisi tabel `migrations`; jangan dicampur dengan opsi (a).
-- ============================================================================
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
SET time_zone = '+07:00';

DROP TABLE IF EXISTS `alerts`, `reports`, `community_posts`, `catch_logs`, `fish`, `users`, `locations`;

CREATE TABLE `locations` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(120) NOT NULL,
  `region` ENUM('JAWA','SUMATRA','KALIMANTAN','SULAWESI','NTT') NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `locations_name_unique` (`name`),
  KEY `locations_region_index` (`region`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `users` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(120) NOT NULL,
  `whatsapp_number` VARCHAR(20) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('ADMIN','USER') NOT NULL DEFAULT 'USER',
  `location_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `notif_kondisi` TINYINT(1) NOT NULL DEFAULT 1,
  `whatsapp_opt_in` TINYINT(1) NOT NULL DEFAULT 0,
  `mode_offline` TINYINT(1) NOT NULL DEFAULT 0,
  `remember_token` VARCHAR(100) NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_whatsapp_number_unique` (`whatsapp_number`),
  KEY `users_role_index` (`role`),
  KEY `users_location_id_foreign` (`location_id`),
  CONSTRAINT `users_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `fish` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(80) NOT NULL,
  `description` TEXT NOT NULL,
  `rarity` ENUM('UMUM','CUKUP_UMUM','JARANG','SANGAT_JARANG') NOT NULL,
  `region` ENUM('JAWA','SUMATRA','KALIMANTAN','SULAWESI','NTT') NOT NULL,
  `image_url` VARCHAR(500) NULL DEFAULT NULL,
  `image_path` VARCHAR(500) NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `fish_name_region_unique` (`name`,`region`),
  KEY `fish_region_index` (`region`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `catch_logs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `location_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `region` ENUM('JAWA','SUMATRA','KALIMANTAN','SULAWESI','NTT') NULL DEFAULT NULL,
  `fish_name` VARCHAR(80) NOT NULL,
  `quantity` VARCHAR(60) NOT NULL,
  `note` TEXT NULL,
  `image_path` VARCHAR(500) NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `catch_logs_user_id_created_at_index` (`user_id`,`created_at`),
  KEY `catch_logs_location_id_fish_name_index` (`location_id`,`fish_name`),
  KEY `catch_logs_region_fish_name_index` (`region`,`fish_name`),
  CONSTRAINT `catch_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `catch_logs_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `community_posts` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `parent_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `name` VARCHAR(80) NOT NULL,
  `area` VARCHAR(120) NOT NULL,
  `message` TEXT NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `community_posts_created_at_index` (`created_at`),
  KEY `community_posts_parent_id_created_at_index` (`parent_id`,`created_at`),
  KEY `community_posts_user_id_foreign` (`user_id`),
  CONSTRAINT `community_posts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `community_posts_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `community_posts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `reports` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `location_id` BIGINT UNSIGNED NOT NULL,
  `title` VARCHAR(150) NOT NULL,
  `category` ENUM('KONDISI_AIR','CUACA_EKSTREM','PENCEMARAN','ABRASI','LAINNYA') NOT NULL,
  `severity` ENUM('RENDAH','SEDANG','TINGGI') NOT NULL,
  `status` ENUM('BARU','DIPROSES','SELESAI') NOT NULL DEFAULT 'BARU',
  `description` TEXT NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reports_status_index` (`status`),
  KEY `reports_category_index` (`category`),
  KEY `reports_severity_index` (`severity`),
  KEY `reports_created_at_index` (`created_at`),
  KEY `reports_user_id_foreign` (`user_id`),
  KEY `reports_location_id_foreign` (`location_id`),
  CONSTRAINT `reports_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `reports_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `alerts` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `location_id` BIGINT UNSIGNED NOT NULL,
  `source` ENUM('KONDISI','LAPORAN') NOT NULL,
  `level` ENUM('WASPADA','BAHAYA') NOT NULL,
  `water_level` DECIMAL(4,2) NULL DEFAULT NULL,
  `title` VARCHAR(150) NOT NULL,
  `message` VARCHAR(500) NOT NULL,
  `read_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `alerts_user_id_read_at_index` (`user_id`,`read_at`),
  KEY `alerts_dedupe_idx` (`user_id`,`location_id`,`source`,`created_at`),
  KEY `alerts_location_id_foreign` (`location_id`),
  CONSTRAINT `alerts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `alerts_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------------
-- DATA AWAL
-- ---------------------------------------------------------------------------

INSERT INTO `locations` (`name`,`region`,`created_at`,`updated_at`) VALUES
  ('Kenjeran, Surabaya', 'JAWA', NOW(), NOW()),
  ('Tanjung Perak, Surabaya', 'JAWA', NOW(), NOW()),
  ('Muara Angke, Jakarta', 'JAWA', NOW(), NOW()),
  ('Pelabuhan Ratu, Sukabumi', 'JAWA', NOW(), NOW()),
  ('Padang, Sumatra Barat', 'SUMATRA', NOW(), NOW()),
  ('Balikpapan, Kalimantan Timur', 'KALIMANTAN', NOW(), NOW()),
  ('Bitung, Sulawesi Utara', 'SULAWESI', NOW(), NOW()),
  ('Kupang, NTT', 'NTT', NOW(), NOW());

INSERT INTO `fish` (`name`,`description`,`rarity`,`region`,`created_at`,`updated_at`) VALUES
  ('Tenggiri', 'Banyak ditangkap sepanjang tahun di perairan pantai utara dan selatan Jawa.', 'UMUM', 'JAWA', NOW(), NOW()),
  ('Pelagis', 'Sering muncul di dekat rumpon, terutama saat air bergerak dari pasang menuju surut.', 'UMUM', 'JAWA', NOW(), NOW()),
  ('Asin', 'Biasa dijumpai di perairan agak dalam dekat muara sungai.', 'CUKUP_UMUM', 'JAWA', NOW(), NOW()),
  ('Layang', 'Lebih sering didapat dengan jaring dasar di perairan yang lebih dalam.', 'JARANG', 'JAWA', NOW(), NOW()),
  ('Selar', 'Peka terhadap kebisingan mesin dan perubahan cahaya, sehingga mereka cepat berpencar dari pancing.', 'SANGAT_JARANG', 'JAWA', NOW(), NOW()),
  ('Kembung', 'Ikan kembung banyak dijumpai di perairan pesisir barat Sumatra sepanjang musim.', 'UMUM', 'SUMATRA', NOW(), NOW()),
  ('Bawal Hitam', 'Cukup sering ditemukan di perairan agak dalam dekat muara.', 'CUKUP_UMUM', 'SUMATRA', NOW(), NOW()),
  ('Kerapu Karang', 'Jarang muncul, biasanya bersembunyi di celah terumbu karang.', 'JARANG', 'SUMATRA', NOW(), NOW()),
  ('Kakap Merah', 'Banyak ditangkap nelayan di perairan pesisir timur Kalimantan.', 'UMUM', 'KALIMANTAN', NOW(), NOW()),
  ('Tenggiri Papan', 'Cukup umum dijumpai saat air mulai surut.', 'CUKUP_UMUM', 'KALIMANTAN', NOW(), NOW()),
  ('Pari Kembang', 'Jarang terlihat, biasanya di dasar perairan berlumpur.', 'JARANG', 'KALIMANTAN', NOW(), NOW()),
  ('Cakalang', 'Salah satu hasil tangkapan utama nelayan Sulawesi Utara.', 'UMUM', 'SULAWESI', NOW(), NOW()),
  ('Tuna Sirip Kuning', 'Cukup umum ditemukan di perairan agak jauh dari pantai.', 'CUKUP_UMUM', 'SULAWESI', NOW(), NOW()),
  ('Napoleon', 'Sangat jarang, populasinya dilindungi dan dibatasi penangkapannya.', 'SANGAT_JARANG', 'SULAWESI', NOW(), NOW()),
  ('Kerapu Karang', 'Umum dijumpai di sekitar terumbu karang perairan NTT.', 'UMUM', 'NTT', NOW(), NOW()),
  ('Ikan Terbang', 'Cukup umum, sering terlihat melompat di permukaan air.', 'CUKUP_UMUM', 'NTT', NOW(), NOW()),
  ('Tenggiri Papan', 'Jarang didapat, biasanya hanya saat musim tertentu.', 'JARANG', 'NTT', NOW(), NOW());

-- Akun demo (GANTI kata sandi sebelum produksi): admin 081200000001 / admin12345 · user 082336478234 / pesisir123 · user 081234567890 / pesisir123
INSERT INTO `users` (`name`,`whatsapp_number`,`password`,`role`,`location_id`,`created_at`,`updated_at`) VALUES
  ('Admin MitraPesisir','081200000001','$2y$12$euwYJ3IuhLrL41DxJQr7h..YJ3CxD5pWGenVIkyGhNbI9txzDR.UW','ADMIN',(SELECT id FROM locations WHERE name='Kenjeran, Surabaya'),NOW(),NOW()),
  ('Yudistira','082336478234','$2y$12$NCY2lB.IuIN3jWXbMOwxsO0hdCuYsQGGq1HlukUMemDUmLtq79KCK','USER',(SELECT id FROM locations WHERE name='Kenjeran, Surabaya'),NOW(),NOW()),
  ('Siti User','081234567890','$2y$12$NCY2lB.IuIN3jWXbMOwxsO0hdCuYsQGGq1HlukUMemDUmLtq79KCK','USER',(SELECT id FROM locations WHERE name='Muara Angke, Jakarta'),NOW(),NOW());

INSERT INTO `catch_logs` (`user_id`,`location_id`,`region`,`fish_name`,`quantity`,`note`,`created_at`,`updated_at`) VALUES
  ((SELECT id FROM users WHERE whatsapp_number='082336478234'),(SELECT id FROM locations WHERE name='Kenjeran, Surabaya'),'JAWA','Tenggiri','5 kg','Didapat dari tengah laut, air sedang tinggi dan ombak besar',NOW(),NOW()),
  ((SELECT id FROM users WHERE whatsapp_number='082336478234'),(SELECT id FROM locations WHERE name='Kenjeran, Surabaya'),'JAWA','Selar','12 ekor',NULL,NOW(),NOW());

INSERT INTO `community_posts` (`user_id`,`name`,`area`,`message`,`created_at`,`updated_at`) VALUES
  (NULL,'Pak Yudistira','Kenjeran, Surabaya','Pagi ini air surut lebih cepat dari biasanya, kapal kecil sebaiknya berangkat sebelum jam 6.',NOW(),NOW()),
  (NULL,'Pak Jeki','Pelabuhan Ratu, Sukabumi','Alhamdulillah tangkapan lumayan pas air mulai pasang tadi malam.',NOW(),NOW()),
  (NULL,'Bu Yuli','Muara Angke, Jakarta','Ombak cukup tinggi di sekitar tanjung, nelayan perahu kecil harap hati-hati.',NOW(),NOW()),
  (NULL,'Kelompok Nelayan Kuta','Kuta, Bali','Jadwal melaut bersama besok pagi disesuaikan dengan surut terendah pukul 05.30.',NOW(),NOW());

INSERT INTO `reports` (`user_id`,`location_id`,`title`,`category`,`severity`,`status`,`description`,`created_at`,`updated_at`) VALUES
  ((SELECT id FROM users WHERE whatsapp_number='082336478234'),(SELECT id FROM locations WHERE name='Kenjeran, Surabaya'),'Air pasang melewati dermaga kecil','KONDISI_AIR','SEDANG','DIPROSES','Sejak pagi air pasang naik hingga menutup dermaga kayu di sisi timur. Perahu kecil kesulitan sandar dan tambatan perlu diperkuat.',NOW(),NOW()),
  ((SELECT id FROM users WHERE whatsapp_number='082336478234'),(SELECT id FROM locations WHERE name='Muara Angke, Jakarta'),'Sampah plastik menumpuk di muara','PENCEMARAN','TINGGI','BARU','Tumpukan sampah plastik terbawa arus dan menutup alur keluar masuk perahu. Mohon bantuan pembersihan bersama.',NOW(),NOW()),
  ((SELECT id FROM users WHERE whatsapp_number='082336478234'),(SELECT id FROM locations WHERE name='Pelabuhan Ratu, Sukabumi'),'Abrasi mengikis tepi pantai warga','ABRASI','TINGGI','DIPROSES','Garis pantai mundur sekitar dua meter sejak bulan lalu, beberapa warung di tepi pantai terancam roboh.',NOW(),NOW()),
  ((SELECT id FROM users WHERE whatsapp_number='082336478234'),(SELECT id FROM locations WHERE name='Padang, Sumatra Barat'),'Angin kencang sore hari','CUACA_EKSTREM','RENDAH','SELESAI','Angin kencang berlangsung dua jam di sore hari dan sudah mereda. Tidak ada kerusakan perahu yang dilaporkan.',NOW(),NOW());
