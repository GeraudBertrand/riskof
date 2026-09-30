-- Icônes SotS + jeu de base ajoutées à la main (2026-09-30)
START TRANSACTION;
UPDATE `item` SET `image` = 'Halcyon_Seed.webp' WHERE `id` = 76; -- Halcyon Seed
UPDATE `item` SET `image` = 'Little_Disciple.webp' WHERE `id` = 77; -- Little Disciple
UPDATE `item` SET `image` = 'Eulogy_Zero.webp' WHERE `id` = 131; -- Eulogy Zero
UPDATE `item` SET `image` = 'Growth_Nectar.webp' WHERE `id` = 155; -- Growth Nectar
UPDATE `item` SET `image` = 'Sonorous_Whispers.webp' WHERE `id` = 163; -- Sonorous Whispers
UPDATE `item` SET `image` = 'Electric_Boomerang.webp' WHERE `id` = 164; -- Electric Boomerang
UPDATE `item` SET `image` = 'War_Bonds.webp' WHERE `id` = 167; -- War Bonds
UPDATE `item` SET `image` = 'Runic_Lens.webp' WHERE `id` = 172; -- Runic Lens
UPDATE `item` SET `image` = 'Longstanding_Solitude.webp' WHERE `id` = 173; -- Longstanding Solitude
COMMIT;
