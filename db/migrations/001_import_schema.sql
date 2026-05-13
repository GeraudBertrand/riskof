-- ============================================================
-- riskof — schéma complet, à exécuter sur une base `riskof` VIDE.
--
-- Pré-requis : la base `riskof` existe et est vide (sinon les
-- CREATE échoueront sur les tables déjà présentes).
--
--   1. Dans phpMyAdmin, sélectionner la base `riskof`
--   2. Onglet "SQL"
--   3. Coller ce script entier, exécuter
--   4. Vérifier dans la sidebar : item, equipment, rarity, tag, link_item_tag
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------
-- rarity
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `rarity`;
CREATE TABLE `rarity` (
    `id`    INT          NOT NULL AUTO_INCREMENT,
    `name`  VARCHAR(64)  NOT NULL,
    `color` VARCHAR(16)  NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_rarity_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- IDs explicites : le front-end suppose 1..9 dans cet ordre (cf. index.html).
INSERT INTO `rarity` (`id`, `name`, `color`) VALUES
    (1, 'Common',           '#FFFFFF'),
    (2, 'Uncommon',         '#77FF7F'),
    (3, 'Legendary',        '#FF7F7F'),
    (4, 'Boss',             '#FFD969'),
    (5, 'Lunar',            '#5BC5F2'),
    (6, 'Void',             '#9572D6'),
    (7, 'Equipment',        '#E08E47'),
    (8, 'Lunar Equipment',  '#5BC5F2'),
    (9, 'Elite Equipment',  '#9C0700');

-- ------------------------------------------------------------
-- tag
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `tag`;
CREATE TABLE `tag` (
    `id`   INT          NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(64)  NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_tag_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- item
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `item`;
CREATE TABLE `item` (
    `id`                  INT          NOT NULL AUTO_INCREMENT,
    `game_internal_name`  VARCHAR(64)  NULL,
    `name`                VARCHAR(128) NOT NULL,
    `rarity`              INT          NULL,
    `orig`                INT          NOT NULL DEFAULT 1,
    `description`         TEXT         NULL,
    `description_html`    TEXT         NULL,
    `lore`                TEXT         NULL,
    `image`               VARCHAR(256) NULL,
    `effect`              TEXT         NULL,
    `unlock_requirement`  VARCHAR(255) NULL,
    `corrupt_pair`        VARCHAR(128) NULL,
    `is_hidden`           BOOLEAN      NOT NULL DEFAULT FALSE,
    `base1`               VARCHAR(32)  NULL,
    `stack1`              VARCHAR(32)  NULL,
    `base2`               VARCHAR(32)  NULL,
    `stack2`              VARCHAR(32)  NULL,
    `base3`               VARCHAR(32)  NULL,
    `stack3`              VARCHAR(32)  NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_item_internal_name` (`game_internal_name`),
    KEY `idx_item_rarity` (`rarity`),
    KEY `idx_item_orig`   (`orig`),
    CONSTRAINT `fk_item_rarity` FOREIGN KEY (`rarity`) REFERENCES `rarity` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- link_item_tag
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `link_item_tag`;
CREATE TABLE `link_item_tag` (
    `id_item` INT NOT NULL,
    `id_tag`  INT NOT NULL,
    PRIMARY KEY (`id_item`, `id_tag`),
    KEY `idx_link_tag` (`id_tag`),
    CONSTRAINT `fk_link_item` FOREIGN KEY (`id_item`) REFERENCES `item` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_link_tag`  FOREIGN KEY (`id_tag`)  REFERENCES `tag`  (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- equipment
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `equipment`;
CREATE TABLE `equipment` (
    `id`                  INT          NOT NULL AUTO_INCREMENT,
    `game_internal_name`  VARCHAR(64)  NOT NULL,
    `name`                VARCHAR(128) NOT NULL,
    `description`         TEXT         NULL,
    `description_html`    TEXT         NULL,
    `lore`                TEXT         NULL,
    `image`               VARCHAR(256) NULL,
    `cooldown_seconds`    INT          NULL,
    `duration_seconds`    DECIMAL(6,2) NULL,
    `rarity_label`        VARCHAR(64)  NULL,
    `unlock_requirement`  VARCHAR(255) NULL,
    `orig`                INT          NOT NULL DEFAULT 1,
    `is_hidden`           BOOLEAN      NOT NULL DEFAULT FALSE,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_equipment_internal_name` (`game_internal_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- À ce stade : tables prêtes, raretés seedées, le reste est vide.
-- Lance ensuite l'import depuis le wiki :
--   https://riskof.gerios.fr/admin/import.php
-- ============================================================
