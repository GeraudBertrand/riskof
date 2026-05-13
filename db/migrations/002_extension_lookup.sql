-- ============================================================
-- Migration 002 : table de lookup pour les extensions (DLC).
--
-- À exécuter UNE FOIS, après que l'import initial est terminé.
-- ============================================================

START TRANSACTION;

CREATE TABLE IF NOT EXISTS `extension` (
    `id`         INT          NOT NULL,
    `name`       VARCHAR(64)  NOT NULL,
    `short_code` VARCHAR(16)  NULL,
    `icon`       VARCHAR(128) NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_extension_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `extension` (`id`, `name`, `short_code`, `icon`) VALUES
    (1, 'Vanilla',                NULL,  NULL),
    (2, 'Vanilla (reserved)',     NULL,  NULL),
    (3, 'Survivors of the Void',  'SOTV', 'SotV_Icon.webp'),
    (4, 'Seekers of the Storm',   'SOTS', 'SotS_Icon.webp')
ON DUPLICATE KEY UPDATE
    `name`       = VALUES(`name`),
    `short_code` = VALUES(`short_code`),
    `icon`       = VALUES(`icon`);

-- FK optionnelles : commente les deux ALTER ci-dessous si tu préfères
-- garder `orig` lâche (utile si tu veux pouvoir mettre orig=99 pour un
-- DLC futur avant d'avoir ajouté la ligne dans `extension`).

ALTER TABLE `item`
    ADD CONSTRAINT `fk_item_extension`
        FOREIGN KEY (`orig`) REFERENCES `extension` (`id`)
        ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE `equipment`
    ADD CONSTRAINT `fk_equipment_extension`
        FOREIGN KEY (`orig`) REFERENCES `extension` (`id`)
        ON DELETE RESTRICT ON UPDATE CASCADE;

COMMIT;

-- Vérif rapide :
--   SELECT i.name, e.short_code FROM item i LEFT JOIN extension e ON e.id = i.orig LIMIT 20;
