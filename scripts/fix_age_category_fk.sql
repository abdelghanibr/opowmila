-- =====================================================================
-- Correction FK persons_ibfk_3 (persons.age_category_id -> age_categories.id)
-- A executer dans phpMyAdmin sur la base opowmi48_opowmila43 (PROD)
-- =====================================================================

-- 1) Verifier l'etat actuel
SELECT id, name, min_age, max_age FROM age_categories ORDER BY id;

-- 2) Rendre la colonne nullable avec ON DELETE SET NULL
--    (si l'etape DROP echoue avec "check that it exists", passer directement au MODIFY)
ALTER TABLE `persons` DROP FOREIGN KEY `persons_ibfk_3`;

ALTER TABLE `persons`
    MODIFY `age_category_id` BIGINT UNSIGNED NULL DEFAULT NULL;

ALTER TABLE `persons`
    ADD CONSTRAINT `persons_ibfk_3`
    FOREIGN KEY (`age_category_id`) REFERENCES `age_categories` (`id`)
    ON DELETE SET NULL;

-- 3) Recreer les categories manquantes (INSERT IGNORE : n'ecrase pas celles qui existent)
INSERT IGNORE INTO `age_categories` (`id`, `name`, `min_age`, `max_age`, `created_at`, `updated_at`) VALUES
    (1, 'الأولاد أقل من 13 سنة',   6, 12,  NOW(), NOW()),
    (2, 'المراهقين من 13-17',      13, 17,  NOW(), NOW()),
    (3, 'البالغين أكبر من 18 سنة', 18, 100, NOW(), NOW());

-- 4) Controle final
SELECT id, name, min_age, max_age FROM age_categories ORDER BY id;
SHOW CREATE TABLE `persons`;
