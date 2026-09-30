-- Icônes : aligne item.image sur les noms réels de Content/image/item (2026-09-30)
-- Items sans fichier : image = NULL, à compléter via l'admin.
START TRANSACTION;
UPDATE `item` SET `image` = 'CloverIcon.webp' WHERE `id` = 1; -- 57 Leaf Clover
UPDATE `item` SET `image` = 'SyringeIcon.webp' WHERE `id` = 2; -- Soldier's Syringe
UPDATE `item` SET `image` = 'BearIcon.webp' WHERE `id` = 3; -- Tougher Times
UPDATE `item` SET `image` = 'BehemothIcon.webp' WHERE `id` = 4; -- Brilliant Behemoth
UPDATE `item` SET `image` = 'MissileLauncherIcon.webp' WHERE `id` = 5; -- AtG Missile Mk. 1
UPDATE `item` SET `image` = 'WilloWispIcon.webp' WHERE `id` = 6; -- Will-o'-the-wisp
UPDATE `item` SET `image` = 'DaggerIcon.webp' WHERE `id` = 7; -- Ceremonial Dagger
UPDATE `item` SET `image` = 'ToothNecklaceIcon.webp' WHERE `id` = 8; -- Monster Tooth
UPDATE `item` SET `image` = 'GlassesIcon.webp' WHERE `id` = 9; -- Lens-Maker's Glasses
UPDATE `item` SET `image` = 'HoofIcon.webp' WHERE `id` = 10; -- Paul's Goat Hoof
UPDATE `item` SET `image` = 'FeatherIcon.webp' WHERE `id` = 11; -- Hopoo Feather
UPDATE `item` SET `image` = 'UkuleleIcon.webp' WHERE `id` = 12; -- Ukulele
UPDATE `item` SET `image` = 'SeedIcon.webp' WHERE `id` = 13; -- Leeching Seed
UPDATE `item` SET `image` = 'FrostRelicIcon.webp' WHERE `id` = 14; -- Frost Relic
UPDATE `item` SET `image` = 'MaskIcon.webp' WHERE `id` = 15; -- Happiest Mask
UPDATE `item` SET `image` = 'MushroomIcon.webp' WHERE `id` = 16; -- Bustling Fungus
UPDATE `item` SET `image` = 'CrowbarIcon.webp' WHERE `id` = 17; -- Crowbar
UPDATE `item` SET `image` = 'WolfPeltIcon.webp' WHERE `id` = 18; -- Predatory Instincts
UPDATE `item` SET `image` = 'TriTipIcon.webp' WHERE `id` = 19; -- Tri-Tip Dagger
UPDATE `item` SET `image` = 'WhipIcon.webp' WHERE `id` = 20; -- Red Whip
UPDATE `item` SET `image` = 'GravBootsIcon.webp' WHERE `id` = 21; -- H3AD-5T v2
UPDATE `item` SET `image` = 'WarbannerIcon.webp' WHERE `id` = 22; -- Warbanner
UPDATE `item` SET `image` = 'PauldronIcon.webp' WHERE `id` = 23; -- Berzerker's Pauldron
UPDATE `item` SET `image` = 'StealthkitIcon.webp' WHERE `id` = 24; -- Old War Stealthkit
UPDATE `item` SET `image` = 'ScytheIcon.webp' WHERE `id` = 25; -- Harvester's Scythe
UPDATE `item` SET `image` = 'SnailIcon.webp' WHERE `id` = 26; -- Cautious Slug
UPDATE `item` SET `image` = 'WaxBirdIcon.webp' WHERE `id` = 27; -- Wax Quail
UPDATE `item` SET `image` = 'PersonalShieldIcon.webp' WHERE `id` = 28; -- Personal Shield Generator
UPDATE `item` SET `image` = 'DevilHornsIcon.webp' WHERE `id` = 29; -- N'kuhana's Opinion
UPDATE `item` SET `image` = 'MedkitIcon.webp' WHERE `id` = 30; -- Medkit
UPDATE `item` SET `image` = 'BatteryIcon.webp' WHERE `id` = 31; -- Fuel Cell
UPDATE `item` SET `image` = 'InfusionIcon.webp' WHERE `id` = 32; -- Infusion
UPDATE `item` SET `image` = 'TeslaCoilIcon.webp' WHERE `id` = 33; -- Unstable Tesla Coil
UPDATE `item` SET `image` = 'GasolineIcon.webp' WHERE `id` = 34; -- Gasoline
UPDATE `item` SET `image` = NULL WHERE `id` = 35; -- BoostDamage
UPDATE `item` SET `image` = NULL WHERE `id` = 36; -- BoostHP
UPDATE `item` SET `image` = 'HookIcon.webp' WHERE `id` = 37; -- Sentient Meat Hook
UPDATE `item` SET `image` = 'FireworkIcon.webp' WHERE `id` = 38; -- Bundle of Fireworks
UPDATE `item` SET `image` = 'BandolierIcon.webp' WHERE `id` = 39; -- Bandolier
UPDATE `item` SET `image` = 'StunGrenadeIcon.webp' WHERE `id` = 40; -- Stun Grenade
UPDATE `item` SET `image` = 'LunarDaggerIcon.webp' WHERE `id` = 41; -- Shaped Glass
UPDATE `item` SET `image` = 'BoneCrownIcon.webp' WHERE `id` = 42; -- Brittle Crown
UPDATE `item` SET `image` = 'ShieldBugIcon.webp' WHERE `id` = 43; -- Transcendence
UPDATE `item` SET `image` = 'AlienHeadIcon.webp' WHERE `id` = 44; -- Alien Head
UPDATE `item` SET `image` = 'TalismanIcon.webp' WHERE `id` = 45; -- Soulbound Catalyst
UPDATE `item` SET `image` = 'KnurlIcon.webp' WHERE `id` = 46; -- Titanic Knurl
UPDATE `item` SET `image` = 'BeetleGlandIcon.webp' WHERE `id` = 47; -- Queen's Gland
UPDATE `item` SET `image` = NULL WHERE `id` = 48; -- BurnNearby
UPDATE `item` SET `image` = NULL WHERE `id` = 49; -- CritHeal
UPDATE `item` SET `image` = NULL WHERE `id` = 50; -- CrippleWardOnLevel
UPDATE `item` SET `image` = 'SodaIcon.webp' WHERE `id` = 51; -- Energy Drink
UPDATE `item` SET `image` = 'DoubleMagIcon.webp' WHERE `id` = 52; -- Backup Magazine
UPDATE `item` SET `image` = 'StickyBombIcon.webp' WHERE `id` = 53; -- Sticky Bomb
UPDATE `item` SET `image` = 'KeyIcon.webp' WHERE `id` = 54; -- Rusted Key
UPDATE `item` SET `image` = 'APRoundsIcon.webp' WHERE `id` = 55; -- Armor-Piercing Rounds
UPDATE `item` SET `image` = 'BucklerIcon.webp' WHERE `id` = 56; -- Rose Buckler
UPDATE `item` SET `image` = 'IceRingIcon.webp' WHERE `id` = 57; -- Runald's Band
UPDATE `item` SET `image` = 'FireRingIcon.webp' WHERE `id` = 58; -- Kjaro's Band
UPDATE `item` SET `image` = 'BaubleIcon.webp' WHERE `id` = 59; -- Chronobauble
UPDATE `item` SET `image` = 'HippoIcon.webp' WHERE `id` = 60; -- Dio's Best Friend
UPDATE `item` SET `image` = NULL WHERE `id` = 61; -- Dio's Best Friend (Consumed)
UPDATE `item` SET `image` = 'AfterburnerIcon.webp' WHERE `id` = 62; -- Hardlight Afterburner
UPDATE `item` SET `image` = 'SkullcrownIcon.webp' WHERE `id` = 63; -- Wake of Vultures
UPDATE `item` SET `image` = 'BrainstalkIcon.webp' WHERE `id` = 64; -- Brainstalks
UPDATE `item` SET `image` = 'AntlerIcon.webp' WHERE `id` = 65; -- Rejuvenation Rack
UPDATE `item` SET `image` = 'CorpseflowerIcon.webp' WHERE `id` = 66; -- Corpsebloom
UPDATE `item` SET `image` = 'FossilIcon.webp' WHERE `id` = 67; -- Gesture of the Drowned
UPDATE `item` SET `image` = 'GuillotineIcon.webp' WHERE `id` = 68; -- Old Guillotine
UPDATE `item` SET `image` = 'WarHornIcon.webp' WHERE `id` = 69; -- War Horn
UPDATE `item` SET `image` = 'AegisIcon.webp' WHERE `id` = 70; -- Aegis
UPDATE `item` SET `image` = NULL WHERE `id` = 71; -- DrizzlePlayerHelper
UPDATE `item` SET `image` = NULL WHERE `id` = 72; -- Ghost
UPDATE `item` SET `image` = NULL WHERE `id` = 73; -- HealthDecay
UPDATE `item` SET `image` = NULL WHERE `id` = 74; -- MageAttunement
UPDATE `item` SET `image` = NULL WHERE `id` = 75; -- Tonic Affliction
UPDATE `item` SET `image` = NULL WHERE `id` = 76; -- Halcyon Seed
UPDATE `item` SET `image` = NULL WHERE `id` = 77; -- Little Disciple
UPDATE `item` SET `image` = 'BroochIcon.webp' WHERE `id` = 78; -- Topaz Brooch
UPDATE `item` SET `image` = 'WarhammerIcon.webp' WHERE `id` = 79; -- Shattering Justice
UPDATE `item` SET `image` = 'GlowFlowerIcon.webp' WHERE `id` = 80; -- Lepton Daisy
UPDATE `item` SET `image` = 'DiamondIcon.webp' WHERE `id` = 81; -- Focus Crystal
UPDATE `item` SET `image` = 'BirdFootIcon.webp' WHERE `id` = 82; -- Strides of Heresy
UPDATE `item` SET `image` = NULL WHERE `id` = 83; -- MonsoonPlayerHelper
UPDATE `item` SET `image` = 'RazorwireIcon.webp' WHERE `id` = 84; -- Razorwire
UPDATE `item` SET `image` = 'SteakIcon.webp' WHERE `id` = 85; -- Bison Steak
UPDATE `item` SET `image` = 'PearlIcon.webp' WHERE `id` = 86; -- Pearl
UPDATE `item` SET `image` = 'ShinyPearlIcon.webp' WHERE `id` = 87; -- Irradiant Pearl
UPDATE `item` SET `image` = 'TomeIcon.webp' WHERE `id` = 88; -- Ghor's Tome
UPDATE `item` SET `image` = 'LaserTurbineIcon.webp' WHERE `id` = 89; -- Resonance Disc
UPDATE `item` SET `image` = 'BirdEyeIcon.webp' WHERE `id` = 90; -- Visions of Heresy
UPDATE `item` SET `image` = 'JellyGutsIcon.webp' WHERE `id` = 91; -- Genesis Loop
UPDATE `item` SET `image` = 'BeadsIcon.webp' WHERE `id` = 92; -- Beads of Fealty
UPDATE `item` SET `image` = 'ArtifactKeyIcon.webp' WHERE `id` = 93; -- Artifact Key
UPDATE `item` SET `image` = 'RepulsionArmorPlateIcon.webp' WHERE `id` = 94; -- Repulsion Armor Plate
UPDATE `item` SET `image` = 'SquidTurretIcon.webp' WHERE `id` = 95; -- Squid Polyp
UPDATE `item` SET `image` = 'DeathMarkIcon.webp' WHERE `id` = 96; -- Death Mark
UPDATE `item` SET `image` = 'InterstellarDeskPlantIcon.webp' WHERE `id` = 97; -- Interstellar Desk Plant
UPDATE `item` SET `image` = NULL WHERE `id` = 98; -- Ancestral Incubator
UPDATE `item` SET `image` = 'FocusedConvergenceIcon.webp' WHERE `id` = 99; -- Focused Convergence
UPDATE `item` SET `image` = 'FireballsOnHitIcon.webp' WHERE `id` = 100; -- Molten Perforator
UPDATE `item` SET `image` = 'LightningStrikeOnHit.webp' WHERE `id` = 101; -- Charged Perforator
UPDATE `item` SET `image` = 'BleedOnHitAndExplodeIcon.webp' WHERE `id` = 102; -- Shatterspleen
UPDATE `item` SET `image` = 'SiphonOnLowHealthIcon.webp' WHERE `id` = 103; -- Mired Urn
UPDATE `item` SET `image` = 'MonstersOnShrineUseIcon.webp' WHERE `id` = 104; -- Defiant Gouge
UPDATE `item` SET `image` = 'RandomDamageZoneIcon.webp' WHERE `id` = 105; -- Mercurial Rachis
UPDATE `item` SET `image` = 'CaptainDefenseMatrixIcon.webp' WHERE `id` = 106; -- Defensive Microbots
UPDATE `item` SET `image` = 'ScrapWhiteIcon.webp' WHERE `id` = 107; -- Item Scrap, White
UPDATE `item` SET `image` = 'ScrapGreenIcon.webp' WHERE `id` = 108; -- Item Scrap, Green
UPDATE `item` SET `image` = 'ScrapRedIcon.webp' WHERE `id` = 109; -- Item Scrap, Red
UPDATE `item` SET `image` = 'ScrapYellowIcon.webp' WHERE `id` = 110; -- Item Scrap, Yellow
UPDATE `item` SET `image` = 'StarSeedIcon.webp' WHERE `id` = 111; -- Purity
UPDATE `item` SET `image` = 'HooksOfHeresyIcon.webp' WHERE `id` = 112; -- Hooks of Heresy
UPDATE `item` SET `image` = 'EmpathyChip.webp' WHERE `id` = 113; -- Empathy Cores
UPDATE `item` SET `image` = 'ParentEggIcon.webp' WHERE `id` = 114; -- Planula
UPDATE `item` SET `image` = 'BirdHeart.webp' WHERE `id` = 115; -- Essence of Heresy
UPDATE `item` SET `image` = 'CoffeeIcon.webp' WHERE `id` = 116; -- Mocha
UPDATE `item` SET `image` = 'MissileLauncherVoidIcon.webp' WHERE `id` = 117; -- Plasma Shrimp
UPDATE `item` SET `image` = 'BearVoidIcon.webp' WHERE `id` = 118; -- Safer Spaces
UPDATE `item` SET `image` = 'ShurikenIcon.webp' WHERE `id` = 119; -- Shuriken
UPDATE `item` SET `image` = 'LaserSightIcon.webp' WHERE `id` = 120; -- Laser Scope
UPDATE `item` SET `image` = 'DroneWeaponsIcon.webp' WHERE `id` = 121; -- Spare Drone Parts
UPDATE `item` SET `image` = 'GrappleHookIcon.webp' WHERE `id` = 122; -- Hunter's Harpoon
UPDATE `item` SET `image` = 'GasTankIcon.webp' WHERE `id` = 123; -- Ignition Tank
UPDATE `item` SET `image` = 'HealingPotion.webp' WHERE `id` = 124; -- Power Elixir
UPDATE `item` SET `image` = NULL WHERE `id` = 125; -- Empty Bottle
UPDATE `item` SET `image` = 'ScorpionIcon.webp' WHERE `id` = 126; -- Symbiotic Scorpion
UPDATE `item` SET `image` = 'ICBMIcon.webp' WHERE `id` = 127; -- Pocket I.C.B.M.
UPDATE `item` SET `image` = 'MushroomVoidIcon.webp' WHERE `id` = 128; -- Weeping Fungus
UPDATE `item` SET `image` = 'LunarShoulderStoneIcon.webp' WHERE `id` = 129; -- Stone Flux Pauldron
UPDATE `item` SET `image` = 'LunarShoulderNatureIcon.webp' WHERE `id` = 130; -- Light Flux Pauldron
UPDATE `item` SET `image` = NULL WHERE `id` = 131; -- Eulogy Zero
UPDATE `item` SET `image` = 'RaincoatIcon.webp' WHERE `id` = 132; -- Ben's Raincoat
UPDATE `item` SET `image` = 'RegeneratingScrapIcon.webp' WHERE `id` = 133; -- Regenerating Scrap
UPDATE `item` SET `image` = NULL WHERE `id` = 134; -- Regenerating Scrap (Consumed)
UPDATE `item` SET `image` = 'CritGlassesVoidIcon.webp' WHERE `id` = 135; -- Lost Seer's Lenses
UPDATE `item` SET `image` = 'WilloWispVoidIcon.webp' WHERE `id` = 136; -- Voidsent Flame
UPDATE `item` SET `image` = 'TriTipVoidIcon.webp' WHERE `id` = 137; -- Needletick
UPDATE `item` SET `image` = 'UkuleleVoidIcon.webp' WHERE `id` = 138; -- Polylute
UPDATE `item` SET `image` = 'KeyVoidIcon.webp' WHERE `id` = 139; -- Encrusted Key
UPDATE `item` SET `image` = 'CloverVoidIcon.webp' WHERE `id` = 140; -- Benthic Bloom
UPDATE `item` SET `image` = 'BaubleVoidIcon.webp' WHERE `id` = 141; -- Tentabauble
UPDATE `item` SET `image` = 'FuelCellVoidIcon.webp' WHERE `id` = 142; -- Lysate Cell
UPDATE `item` SET `image` = 'HippoVoidIcon.webp' WHERE `id` = 143; -- Pluripotent Larva
UPDATE `item` SET `image` = NULL WHERE `id` = 144; -- Pluripotent Larva (Consumed)
UPDATE `item` SET `image` = 'DelicateWatchIcon.webp' WHERE `id` = 145; -- Delicate Watch
UPDATE `item` SET `image` = NULL WHERE `id` = 146; -- Delicate Watch (Broken)
UPDATE `item` SET `image` = 'OddlyShapedOpalIcon.webp' WHERE `id` = 147; -- Oddly-shaped Opal
UPDATE `item` SET `image` = 'RollOfPenniesIcon.webp' WHERE `id` = 148; -- Roll of Pennies
UPDATE `item` SET `image` = 'BottledChaosIcon.webp' WHERE `id` = 149; -- Bottled Chaos
UPDATE `item` SET `image` = 'ShippingRequestFormIcon.webp' WHERE `id` = 150; -- Shipping Request Form
UPDATE `item` SET `image` = 'VoidRingIcon.webp' WHERE `id` = 151; -- Singularity Band
UPDATE `item` SET `image` = 'SunHeadIcon.webp' WHERE `id` = 152; -- Egocentrism
UPDATE `item` SET `image` = 'MinorConstructOnKillIcon.webp' WHERE `id` = 153; -- Defense Nucleus
UPDATE `item` SET `image` = 'MegaCrabItem.webp' WHERE `id` = 154; -- Newly Hatched Zoea
UPDATE `item` SET `image` = NULL WHERE `id` = 155; -- Growth Nectar
UPDATE `item` SET `image` = 'ChanceDollIcon.webp' WHERE `id` = 156; -- Chance Doll
UPDATE `item` SET `image` = 'BreachingFinIcon.webp' WHERE `id` = 157; -- Breaching Fin
UPDATE `item` SET `image` = 'BolsteringLanternIcon.webp' WHERE `id` = 158; -- Bolstering Lantern
UPDATE `item` SET `image` = 'LuminousShotIcon.webp' WHERE `id` = 159; -- Luminous Shot
UPDATE `item` SET `image` = 'SaleStarIcon.webp' WHERE `id` = 160; -- Sale Star
UPDATE `item` SET `image` = NULL WHERE `id` = 161; -- Sale Star (Consumed)
UPDATE `item` SET `image` = 'ElusiveAntlersIcon.webp' WHERE `id` = 162; -- Elusive Antlers
UPDATE `item` SET `image` = NULL WHERE `id` = 163; -- Sonorous Whispers
UPDATE `item` SET `image` = NULL WHERE `id` = 164; -- Electric Boomerang
UPDATE `item` SET `image` = 'NoxiousThornIcon.webp' WHERE `id` = 165; -- Noxious Thorn
UPDATE `item` SET `image` = 'ChronicExpansionIcon.webp' WHERE `id` = 166; -- Chronic Expansion
UPDATE `item` SET `image` = NULL WHERE `id` = 167; -- War Bonds
UPDATE `item` SET `image` = 'WarpedEchoIcon.webp' WHERE `id` = 168; -- Warped Echo
UPDATE `item` SET `image` = 'UnstableTransmitterIcon.webp' WHERE `id` = 169; -- Unstable Transmitter
UPDATE `item` SET `image` = NULL WHERE `id` = 170; -- Unstable Transmitter (Consumed)
UPDATE `item` SET `image` = 'PrayerBeadsIcon.webp' WHERE `id` = 171; -- Prayer Beads
UPDATE `item` SET `image` = NULL WHERE `id` = 172; -- Runic Lens
UPDATE `item` SET `image` = NULL WHERE `id` = 173; -- Longstanding Solitude
UPDATE `item` SET `image` = NULL WHERE `id` = 174; -- Eclipse Lite
UPDATE `item` SET `image` = NULL WHERE `id` = 175; -- Hiker's Boots
UPDATE `item` SET `image` = NULL WHERE `id` = 176; -- Box of Dynamite
UPDATE `item` SET `image` = NULL WHERE `id` = 177; -- Collector's Compulsion
UPDATE `item` SET `image` = NULL WHERE `id` = 178; -- Faraday Spur
UPDATE `item` SET `image` = NULL WHERE `id` = 179; -- Kinetic Dampener
UPDATE `item` SET `image` = NULL WHERE `id` = 180; -- Networked Suffering
UPDATE `item` SET `image` = NULL WHERE `id` = 181; -- Orphaned Core
UPDATE `item` SET `image` = NULL WHERE `id` = 182; -- Substandard Duplicator
UPDATE `item` SET `image` = NULL WHERE `id` = 183; -- Encrypted Cerebellum
UPDATE `item` SET `image` = NULL WHERE `id` = 184; -- Exposed Cerebellum
UPDATE `item` SET `image` = NULL WHERE `id` = 185; -- Faulty Conductor
UPDATE `item` SET `image` = NULL WHERE `id` = 186; -- Functional Coupler
UPDATE `item` SET `image` = NULL WHERE `id` = 187; -- Prison Matrix
UPDATE `item` SET `image` = NULL WHERE `id` = 188; -- Sentry Key
UPDATE `item` SET `image` = NULL WHERE `id` = 189; -- Neutronium Weight
COMMIT;
