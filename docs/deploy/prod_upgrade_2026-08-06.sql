-- ============================================================================
--  MISE À NIVEAU BASE DE DONNÉES PRODUCTION — opowmila43
-- ============================================================================
--  Base prod cible   : état du commit 37b1603 (2025-12-20)
--  Généré depuis     : base dev opowmila43 (mysql localhost, root) le 2026-08-06
--  Usage             :  mysql -u<user> -p <nom_base> < prod_upgrade_2026-08-06.sql
--
--  IMPORTANT
--  • Script idempotent pour les colonnes/index (garde via information_schema).
--  • Les contraintes FOREIGN KEY (fin de fichier) s'appliquent UNE fois.
--  • La table `payments` a été RESTRUCTURÉE en dev (Satim) : voir section 3.
--  • Ne PAS lancer `php artisan migrate` sur le fichier chargily cassé
--    (database/migrations/2025_12_25_082246_create_chargily_payments_table.php
--    contient deux méthodes up()). La table est créée ici via SQL ; pour la
--    cohérence artisan, insérer ensuite :
--      INSERT INTO migrations (migration, batch) VALUES
--        ('2026_08_05_000000_add_children_and_reservation_person_fields', 1);
-- ============================================================================

-- ---------------------------------------------------------------------------
-- 0) Helpers : ajout colonne / index uniquement s'ils n'existent pas
-- ---------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS `add_column_if_missing`;
DELIMITER $$
CREATE PROCEDURE `add_column_if_missing`(
    IN `tbl` VARCHAR(128),
    IN `col` VARCHAR(128),
    IN `ddl` VARCHAR(1024)
)
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME   = tbl
          AND COLUMN_NAME  = col
    ) THEN
        SET @s = CONCAT('ALTER TABLE `', tbl, '` ADD COLUMN ', ddl);
        PREPARE stmt FROM @s;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS `add_index_if_missing`;
DELIMITER $$
CREATE PROCEDURE `add_index_if_missing`(
    IN `tbl` VARCHAR(128),
    IN `idx` VARCHAR(128),
    IN `ddl` VARCHAR(1024)
)
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME   = tbl
          AND INDEX_NAME   = idx
    ) THEN
        SET @s = CONCAT('ALTER TABLE `', tbl, '` ADD ', ddl);
        PREPARE stmt FROM @s;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END$$
DELIMITER ;

-- ---------------------------------------------------------------------------
-- 1) Colonnes ajoutées sur les tables existantes
-- ---------------------------------------------------------------------------

-- ----- users : complex_id (profil "complex manager"), nin ---------------
CALL add_column_if_missing('users', 'complex_id', '`complex_id` int DEFAULT NULL');
CALL add_column_if_missing('users', 'nin', '`nin` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL');

-- ----- persons : enfants/assurance/profil enrichi ------------------------
CALL add_column_if_missing('persons', 'parent_id',      '`parent_id` bigint unsigned DEFAULT NULL AFTER `user_id`');
CALL add_column_if_missing('persons', 'guardian_docs',  '`guardian_docs` json DEFAULT NULL AFTER `parent_id`');
CALL add_column_if_missing('persons', 'complex_id',     '`complex_id` bigint unsigned DEFAULT NULL');
CALL add_column_if_missing('persons', 'blood_type',     '`blood_type` varchar(5) COLLATE utf8mb4_general_ci DEFAULT NULL');
CALL add_column_if_missing('persons', 'profession',     '`profession` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL');
CALL add_column_if_missing('persons', 'tuteur_fullname','`tuteur_fullname` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL');
CALL add_column_if_missing('persons', 'tuteur_phone',   '`tuteur_phone` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL');
CALL add_column_if_missing('persons', 'date_naissance', '`date_naissance` date DEFAULT NULL');
CALL add_column_if_missing('persons', 'sexe',           '`sexe` enum(''H'',''F'') COLLATE utf8mb4_general_ci DEFAULT NULL');
CALL add_column_if_missing('persons', 'sex',            '`sex` enum(''H'',''F'',''X'') COLLATE utf8mb4_general_ci DEFAULT NULL');
CALL add_column_if_missing('persons', 'etat_ass',       '`etat_ass` int DEFAULT 0');
CALL add_column_if_missing('persons', 'license_number', '`license_number` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL');
CALL add_column_if_missing('persons', 'assurance_status',    '`assurance_status` varchar(30) COLLATE utf8mb4_general_ci DEFAULT ''not_assured''');
CALL add_column_if_missing('persons', 'assurance_start_date','`assurance_start_date` date DEFAULT NULL');
CALL add_column_if_missing('persons', 'assurance_end_date',  '`assurance_end_date` date DEFAULT NULL');
CALL add_column_if_missing('persons', 'assured_at',     '`assured_at` timestamp NULL DEFAULT NULL');
CALL add_column_if_missing('persons', 'assured_on',     '`assured_on` date DEFAULT NULL');
CALL add_column_if_missing('persons', 'assured_expires_on', '`assured_expires_on` date DEFAULT NULL');
CALL add_column_if_missing('persons', 'assured_by',     '`assured_by` bigint unsigned DEFAULT NULL');
CALL add_column_if_missing('persons', 'attachments',    '`attachments` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin');

CALL add_index_if_missing('persons', 'persons_parent_id_index', 'INDEX `persons_parent_id_index` (`parent_id`)');

-- ----- reservations : personne ciblée (enfants), paiement, mise à jour ----
CALL add_column_if_missing('reservations', 'person_id', '`person_id` bigint unsigned DEFAULT NULL AFTER `user_id`');
CALL add_column_if_missing('reservations', 'payment_id', '`payment_id` bigint unsigned DEFAULT NULL');
CALL add_column_if_missing('reservations', 'updated_by', '`updated_by` bigint unsigned DEFAULT NULL');

CALL add_index_if_missing('reservations', 'reservations_person_id_index',   'INDEX `reservations_person_id_index` (`person_id`)');
CALL add_index_if_missing('reservations', 'reservations_payment_id_foreign','INDEX `reservations_payment_id_foreign` (`payment_id`)');
CALL add_index_if_missing('reservations', 'reservations_updated_by_foreign','INDEX `reservations_updated_by_foreign` (`updated_by`)');

-- ----- schedules : type de saison + bornes dates + actif ------------------
CALL add_column_if_missing('schedules', 'type_season', '`type_season` enum(''session'',''weekly'',''monthly'',''quarterly'',''semester'',''season'',''ticket'') COLLATE utf8mb4_general_ci DEFAULT NULL');
CALL add_column_if_missing('schedules', 'date_debut',  '`date_debut` date DEFAULT NULL');
CALL add_column_if_missing('schedules', 'date_fin',    '`date_fin` date DEFAULT NULL');
CALL add_column_if_missing('schedules', 'active',      '`active` tinyint(1) NOT NULL DEFAULT 1');

-- ----- activities : responsable user_id -----------------------------------
CALL add_column_if_missing('activities', 'user_id', '`user_id` int DEFAULT NULL');

-- ----- complexes : type / image / responsable -----------------------------
CALL add_column_if_missing('complexes', 'type',  '`type` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL');
CALL add_column_if_missing('complexes', 'image', '`image` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL');
CALL add_column_if_missing('complexes', 'user_id', '`user_id` int DEFAULT NULL');

-- ----- dossiers : validation admin ----------------------------------------
CALL add_column_if_missing('dossiers', 'validated_by', '`validated_by` bigint DEFAULT NULL');
CALL add_column_if_missing('dossiers', 'validated_at', '`validated_at` timestamp NULL DEFAULT NULL');

-- ---------------------------------------------------------------------------
-- 2) NOUVELLES TABLES
-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `chargily_payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `reservation_id` bigint unsigned DEFAULT NULL,
  `chargily_id` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(10) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'dzd',
  `status` enum('pending','paid','failed','canceled') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'pending',
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_chargily_payments_user` (`user_id`),
  KEY `fk_chargily_payments_reservation` (`reservation_id`),
  CONSTRAINT `fk_chargily_payments_reservation` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_chargily_payments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chargily_payments_chk_1` CHECK (json_valid(`metadata`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `seat_types` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `teams` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `matches` (
  `id` int NOT NULL AUTO_INCREMENT,
  `complex_id` bigint unsigned NOT NULL,
  `team_home_id` int NOT NULL,
  `team_away_id` int NOT NULL,
  `match_date` date NOT NULL,
  `match_time` time NOT NULL,
  `competition` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('scheduled','sold_out','finished','cancelled') DEFAULT 'scheduled',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `complex_id` (`complex_id`),
  KEY `team_home_id` (`team_home_id`),
  KEY `team_away_id` (`team_away_id`),
  CONSTRAINT `matches_ibfk_1` FOREIGN KEY (`complex_id`) REFERENCES `complexes` (`id`),
  CONSTRAINT `matches_ibfk_2` FOREIGN KEY (`team_home_id`) REFERENCES `teams` (`id`),
  CONSTRAINT `matches_ibfk_3` FOREIGN KEY (`team_away_id`) REFERENCES `teams` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `complex_seats` (
  `id` int NOT NULL AUTO_INCREMENT,
  `complex_id` bigint unsigned NOT NULL,
  `seat_type_id` int NOT NULL,
  `total_seats` int NOT NULL,
  `available_seats` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `complex_id` (`complex_id`),
  KEY `seat_type_id` (`seat_type_id`),
  CONSTRAINT `complex_seats_ibfk_1` FOREIGN KEY (`complex_id`) REFERENCES `complexes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `complex_seats_ibfk_2` FOREIGN KEY (`seat_type_id`) REFERENCES `seat_types` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `tickets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `match_id` int NOT NULL,
  `seat_type_id` int NOT NULL,
  `buyer_name` varchar(100) NOT NULL,
  `buyer_phone` varchar(20) NOT NULL,
  `qr_code` varchar(255) DEFAULT NULL,
  `status` enum('reserved','paid','cancelled','checked_in') DEFAULT 'reserved',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `match_id` (`match_id`),
  KEY `seat_type_id` (`seat_type_id`),
  CONSTRAINT `tickets_ibfk_1` FOREIGN KEY (`match_id`) REFERENCES `matches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tickets_ibfk_2` FOREIGN KEY (`seat_type_id`) REFERENCES `seat_types` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `devices` (
  `id` int NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `model` varchar(50) COLLATE utf8mb4_general_ci DEFAULT 'UFace 800',
  `ip` varchar(45) COLLATE utf8mb4_general_ci NOT NULL,
  `port` int DEFAULT 4370,
  `location` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `last_sync` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `person_assurances` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `person_id` bigint unsigned NOT NULL,
  `reservation_id` bigint unsigned DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('pending','assured','expired','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `operation_type` enum('new','renewal') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'new',
  `source` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'auto',
  `printed_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `printed_by` bigint unsigned DEFAULT NULL,
  `assured_at` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `cancelled_by` bigint unsigned DEFAULT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `assurance_month` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_person_assurances_person_id` (`person_id`),
  KEY `idx_person_assurances_reservation_id` (`reservation_id`),
  KEY `idx_person_assurances_status` (`status`),
  KEY `idx_person_assurances_dates` (`start_date`,`end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `pool_closures` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `complex_activity_id` bigint unsigned DEFAULT NULL,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `closure_date` datetime NOT NULL,
  `reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('active','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `reservation_credits` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `reservation_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `complex_activity_id` bigint unsigned DEFAULT NULL,
  `closure_date` date NOT NULL,
  `credited_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `status` enum('pending','used','cancelled') DEFAULT 'pending',
  `used_in_reservation_id` bigint unsigned DEFAULT NULL,
  `note` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ---------------------------------------------------------------------------
-- 3) RESTRUCTURATION de la table `payments` (paiement Satim/EDAHABIA)
-- ---------------------------------------------------------------------------
--  ⚠️ La table prod `payments` possède l'ancien schéma :
--     id, reservation_id, amount, methode, transaction_ref, payment_status, paid_at
--  En dev elle a été réécrite (order_id, status, payload, datetimesatim...).
--  → À migrer manuellement selon les données prod (ne pas perdre l'historique).
--  ORDRE IMPORTANT : renommer l'ancienne table AVANT d'exécuter ce script,
--  sinon la FK `reservations_payment_id_foreign` (créée au point 4) pointera
--  vers `payments_old` au lieu de la nouvelle table `payments`.
--
--  Procédure recommandée :
--    1. RENAME TABLE `payments` TO `payments_old`;
--    2. Sauvegarder/convertir l'historique (ex. reports dans `payload`,
--       colonnes `order_id`/`status` depuis l'ancien suivi).
--    3. Exécuter CE script complet (le CREATE ci-dessous crée la nouvelle
--       table, la FK du point 4 pointe alors correctement sur `payments`).
--
--  Si le script a déjà été lancé (payments pas encore renommée) :
--      ALTER TABLE `reservations` DROP FOREIGN KEY `reservations_payment_id_foreign`;
--      RENAME TABLE `payments` TO `payments_old`;
--      -- puis relancer le script : le CREATE ci-dessous crée la nouvelle table,
--      -- le point 4 recrée la FK sur la bonne table.

CREATE TABLE IF NOT EXISTS `payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` varchar(255) NOT NULL,
  `receipt_url` varchar(500) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'pending',
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  `datetimesatim` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_id` (`order_id`),
  CONSTRAINT `payments_chk_1` CHECK (json_valid(`payload`))
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ---------------------------------------------------------------------------
-- 4) Contraintes FOREIGN KEY (à appliquer UNE fois, après le point 3)
-- ---------------------------------------------------------------------------

ALTER TABLE `reservations`
  ADD CONSTRAINT `reservations_payment_id_foreign`
    FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE SET NULL;

ALTER TABLE `reservations`
  ADD CONSTRAINT `reservations_updated_by_foreign`
    FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

-- ---------------------------------------------------------------------------
-- 5) Nettoyage des helpers
-- ---------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS `add_column_if_missing`;
DROP PROCEDURE IF EXISTS `add_index_if_missing`;
