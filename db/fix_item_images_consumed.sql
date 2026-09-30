-- Icônes des objets consommés / cassés (2026-09-30)
START TRANSACTION;
UPDATE `item` SET `image` = 'Dios_Best_Friend_Consumed.webp' WHERE `id` = 61; -- Dio's Best Friend (Consumed)
UPDATE `item` SET `image` = 'Tonic_Affliction.webp' WHERE `id` = 75; -- Tonic Affliction
UPDATE `item` SET `image` = 'Empty_Bottle.webp' WHERE `id` = 125; -- Empty Bottle
UPDATE `item` SET `image` = 'Regenerating_Scrap_Consumed.webp' WHERE `id` = 134; -- Regenerating Scrap (Consumed)
UPDATE `item` SET `image` = 'Pluripotent_Larva_Consumed.webp' WHERE `id` = 144; -- Pluripotent Larva (Consumed)
UPDATE `item` SET `image` = 'Delicate_Watch_Broken.webp' WHERE `id` = 146; -- Delicate Watch (Broken)
UPDATE `item` SET `image` = 'Sale_Star_Consumed.webp' WHERE `id` = 161; -- Sale Star (Consumed)
UPDATE `item` SET `image` = 'Unstable_Transmitter_Consumed.webp' WHERE `id` = 170; -- Unstable Transmitter (Consumed)
COMMIT;
