<?php
/**
 * Importer auto depuis le wiki Fandom Risk of Rain 2.
 *
 * Sources :
 *   - https://riskofrain2.fandom.com/Module:Items/Data
 *   - https://riskofrain2.fandom.com/Module:Equipment/Data
 *
 * Usage CLI :
 *   php scripts/import_from_wiki.php [options]
 *
 * Options :
 *   --dry-run         Ne touche pas à la DB et ne télécharge pas les images
 *   --no-images       Importe les données mais ne télécharge pas les icones
 *   --items-only      Importe seulement les items (pas les equipments)
 *   --equipments-only Importe seulement les equipments
 *   --quiet           Logs minimalistes
 *
 * Inclusion depuis admin/import.php :
 *   $argv = ['import_from_wiki.php'];          // ou avec options
 *   require __DIR__ . '/../scripts/import_from_wiki.php';
 */

require_once __DIR__ . '/lua_parser.php';
require_once __DIR__ . '/../api/Model/db_connect.php';

// ----------------------------------------------------------------
// CLI parsing
// ----------------------------------------------------------------

$opts = [
    'dry_run'         => false,
    'no_images'       => false,
    'items_only'      => false,
    'equipments_only' => false,
    'quiet'           => false,
];
$args = $argv ?? [];
foreach (array_slice($args, 1) as $a) {
    switch ($a) {
        case '--dry-run':         $opts['dry_run']         = true; break;
        case '--no-images':       $opts['no_images']       = true; break;
        case '--items-only':      $opts['items_only']      = true; break;
        case '--equipments-only': $opts['equipments_only'] = true; break;
        case '--quiet':           $opts['quiet']           = true; break;
        default:
            fwrite(STDERR, "Unknown option: $a\n");
            exit(2);
    }
}

function logmsg(string $msg, bool $forceShow = false): void {
    global $opts;
    if ($opts['quiet'] && !$forceShow) return;
    echo $msg . "\n";
    @ob_flush(); @flush();
}

function err(string $msg): void {
    // Sur le NAS en mode web il n'y a pas de tty pour STDERR utile → on écrit
    // aussi sur stdout pour que ça apparaisse dans la page admin.
    if (defined('STDERR')) {
        @fwrite(STDERR, "[ERR] $msg\n");
    }
    echo "[ERR] $msg\n";
    @ob_flush(); @flush();
}

// ----------------------------------------------------------------
// HTTP helpers
// ----------------------------------------------------------------

const WIKI_BASE = 'https://riskofrain2.fandom.com';
const USER_AGENT = 'riskof-importer/1.0 (personal NAS site)';

function http_get(string $url): string {
    // Préfère cURL (gère HTTPS sans dépendre de l'extension openssl PHP).
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_USERAGENT      => USER_AGENT,
            CURLOPT_HTTPHEADER     => ['Accept: application/json,*/*'],
        ]);
        $raw  = curl_exec($ch);
        $err  = curl_error($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($raw === false) {
            throw new RuntimeException("HTTP GET failed ($url): $err");
        }
        if ($code >= 400) {
            throw new RuntimeException("HTTP $code on $url");
        }
        return $raw;
    }

    // Fallback file_get_contents (nécessite allow_url_fopen + openssl pour HTTPS).
    $ctx = stream_context_create([
        'http' => [
            'header'  => "User-Agent: " . USER_AGENT . "\r\n",
            'timeout' => 30,
            'ignore_errors' => true,
        ]
    ]);
    $raw = @file_get_contents($url, false, $ctx);
    if ($raw === false) {
        throw new RuntimeException(
            "HTTP GET failed for $url. Install ext-curl or enable openssl+allow_url_fopen in php.ini."
        );
    }
    return $raw;
}

function fetch_module(string $title): string {
    $url = WIKI_BASE . '/api.php'
         . '?action=query'
         . '&titles='   . rawurlencode($title)
         . '&prop=revisions'
         . '&rvprop=content'
         . '&rvslots=main'
         . '&format=json'
         . '&formatversion=2';
    $raw  = http_get($url);
    $data = json_decode($raw, true);
    if (!isset($data['query']['pages'][0]['revisions'][0]['slots']['main']['content'])) {
        throw new RuntimeException("unexpected API response for $title");
    }
    return $data['query']['pages'][0]['revisions'][0]['slots']['main']['content'];
}

function fetch_image_url(string $filename): ?string {
    $url = WIKI_BASE . '/api.php'
         . '?action=query'
         . '&titles=' . rawurlencode('File:' . $filename)
         . '&prop=imageinfo'
         . '&iiprop=url'
         . '&format=json'
         . '&formatversion=2';
    try {
        $raw  = http_get($url);
        $data = json_decode($raw, true);
        $page = $data['query']['pages'][0] ?? null;
        if (!$page || isset($page['missing'])) return null;
        return $page['imageinfo'][0]['url'] ?? null;
    } catch (Throwable $e) {
        return null;
    }
}

// ----------------------------------------------------------------
// Wiki description → HTML / plain text
// ----------------------------------------------------------------

const COLOR_CLASS = [
    'h'    => 'ror-heal',
    'd'    => 'ror-damage',
    'u'    => 'ror-utility',
    'void' => 'ror-void',
];

function render_template_html(string $name, array $args): string {
    if (($name === 'Color' || $name === 'C') && count($args) >= 2) {
        $code = trim($args[0]);
        $text = $args[1];
        if (isset(COLOR_CLASS[$code])) {
            return '<span class="' . COLOR_CLASS[$code] . '">' . $text . '</span>';
        }
        if ($code !== '' && $code[0] === '#') {
            return '<span style="color:' . htmlspecialchars($code, ENT_QUOTES) . '">' . $text . '</span>';
        }
        return $text;
    }
    if ($name === 'Stack' && count($args) >= 1) {
        return '<span class="ror-stack">' . $args[0] . '</span>';
    }
    return template_fallback_text($args);
}

function template_fallback_text(array $args): string {
    if (empty($args)) return '';
    $last = $args[count($args) - 1];
    // Pour 'key=value' (StatusEffectLink etc.), on garde la valeur
    if (is_string($last) && strpos($last, '=') !== false) {
        $kv = explode('=', $last, 2);
        $last = $kv[1];
    }
    return is_string($last) ? $last : '';
}

function strip_templates(string $s, bool $asHtml): string {
    // On boucle pour traiter les templates imbriqués (les plus internes d'abord).
    $guard = 20;
    while ($guard-- > 0 && preg_match('/\{\{([^{}]+)\}\}/', $s)) {
        $s = preg_replace_callback('/\{\{([^{}]+)\}\}/', function($m) use ($asHtml) {
            $parts = explode('|', $m[1]);
            $name  = trim($parts[0]);
            $args  = array_slice($parts, 1);
            return $asHtml
                ? render_template_html($name, $args)
                : template_fallback_text($args);
        }, $s);
    }
    return $s;
}

function render_desc_html(string $desc): string  { return strip_templates($desc, true); }
function render_desc_plain(string $desc): string { return strip_templates($desc, false); }

// ----------------------------------------------------------------
// Mapping wiki → DB
// ----------------------------------------------------------------

/**
 * Rarity name attendu en DB pour un nom de rarity wiki donné.
 * Le mapping retourne 2 infos : le label canonique + une couleur hex par défaut
 * (utilisé si on doit créer la rarity dans la table `rarity`).
 */
const RARITY_DEF = [
    'Common'           => ['name' => 'Common',           'color' => '#FFFFFF'],
    'Uncommon'         => ['name' => 'Uncommon',         'color' => '#77FF7F'],
    'Legendary'        => ['name' => 'Legendary',        'color' => '#FF7F7F'],
    'Boss'             => ['name' => 'Boss',             'color' => '#FFD969'],
    'Lunar'            => ['name' => 'Lunar',            'color' => '#5BC5F2'],
    'Void'             => ['name' => 'Void',             'color' => '#9572D6'],
    'Untiered'         => ['name' => 'Untiered',         'color' => '#CCCCCC'],
    'Equipment'        => ['name' => 'Equipment',        'color' => '#E08E47'],
    'Lunar Equipment'  => ['name' => 'Lunar Equipment',  'color' => '#5BC5F2'],
    'Elite Equipment'  => ['name' => 'Elite Equipment',  'color' => '#9C0700'],
];

function map_rarity(PDO $db, string $wikiRarity): ?int {
    static $cache = [];
    if (isset($cache[$wikiRarity])) return $cache[$wikiRarity];

    $def = RARITY_DEF[$wikiRarity] ?? ['name' => $wikiRarity, 'color' => '#CCCCCC'];

    $sel = $db->prepare('SELECT `id` FROM `rarity` WHERE `name` = :n LIMIT 1');
    $sel->execute([':n' => $def['name']]);
    $id = $sel->fetchColumn();

    if ($id === false) {
        $ins = $db->prepare('INSERT INTO `rarity` (`name`, `color`) VALUES (:n, :c)');
        $ins->execute([':n' => $def['name'], ':c' => $def['color']]);
        $id = (int)$db->lastInsertId();
    } else {
        $id = (int)$id;
    }
    return $cache[$wikiRarity] = $id;
}

function ensure_tag(PDO $db, string $tagName): int {
    static $cache = [];
    if (isset($cache[$tagName])) return $cache[$tagName];

    $sel = $db->prepare('SELECT `id` FROM `tag` WHERE `name` = :n LIMIT 1');
    $sel->execute([':n' => $tagName]);
    $id = $sel->fetchColumn();

    if ($id === false) {
        $ins = $db->prepare('INSERT INTO `tag` (`name`) VALUES (:n)');
        $ins->execute([':n' => $tagName]);
        $id = (int)$db->lastInsertId();
    }
    return $cache[$tagName] = (int)$id;
}

function set_item_tags(PDO $db, int $itemId, array $tagNames): void {
    $del = $db->prepare('DELETE FROM `link_item_tag` WHERE `id_item` = :id');
    $del->execute([':id' => $itemId]);
    if (empty($tagNames)) return;

    $ins = $db->prepare('INSERT INTO `link_item_tag` (`id_item`, `id_tag`) VALUES (:i, :t)');
    foreach ($tagNames as $tn) {
        $ins->execute([':i' => $itemId, ':t' => ensure_tag($db, $tn)]);
    }
}

function detect_orig(string $wikiRarity, array $categories): int {
    // Convention historique du front-end :
    //   1 = vanilla, 3 = SOTV, 4 = SOTS, 2 = réservé
    if ($wikiRarity === 'Void') return 3; // SOTV
    $sotsHints = ['HalcyoniteShrine', 'DevotionBlacklist', 'RebirthBlacklist', 'SacrificeBlacklist'];
    foreach ($sotsHints as $h) {
        if (in_array($h, $categories, true)) return 4;
    }
    return 1;
}

// ----------------------------------------------------------------
// Upserts
// ----------------------------------------------------------------

function upsert_item(PDO $db, array $row): array {
    // $row : ['game_internal_name', 'name', 'rarity_id', 'orig', 'description',
    //        'description_html', 'effect', 'image', 'lore', 'unlock_requirement',
    //        'corrupt_pair', 'is_hidden', 'base1'..'stack3']
    $sel = $db->prepare('SELECT `id` FROM `item` WHERE `game_internal_name` = :n LIMIT 1');
    $sel->execute([':n' => $row['game_internal_name']]);
    $existing = $sel->fetchColumn();

    $cols = [
        'game_internal_name', 'name', 'rarity', 'orig', 'description',
        'description_html', 'effect', 'image', 'lore', 'unlock_requirement',
        'corrupt_pair', 'is_hidden',
        'base1', 'stack1', 'base2', 'stack2', 'base3', 'stack3',
    ];
    $rowDb = [
        'game_internal_name' => $row['game_internal_name'],
        'name'               => $row['name'],
        'rarity'             => $row['rarity_id'],
        'orig'               => $row['orig'],
        'description'        => $row['description'],
        'description_html'   => $row['description_html'],
        'effect'             => $row['effect'],
        'image'              => $row['image'],
        'lore'               => $row['lore'],
        'unlock_requirement' => $row['unlock_requirement'],
        'corrupt_pair'       => $row['corrupt_pair'],
        'is_hidden'          => $row['is_hidden'] ? 1 : 0,
        'base1'  => $row['base1'],  'stack1' => $row['stack1'],
        'base2'  => $row['base2'],  'stack2' => $row['stack2'],
        'base3'  => $row['base3'],  'stack3' => $row['stack3'],
    ];

    if ($existing === false) {
        $placeholders = implode(', ', array_map(fn($c) => ":$c", $cols));
        $colList      = implode(', ', array_map(fn($c) => "`$c`", $cols));
        $sql = "INSERT INTO `item` ($colList) VALUES ($placeholders)";
        $stmt = $db->prepare($sql);
        $stmt->execute(array_combine(array_map(fn($c) => ":$c", $cols), array_values($rowDb)));
        return ['id' => (int)$db->lastInsertId(), 'added' => true];
    } else {
        $setClause = implode(', ', array_map(fn($c) => "`$c` = :$c", $cols));
        $sql = "UPDATE `item` SET $setClause WHERE `id` = :__id";
        $stmt = $db->prepare($sql);
        $params = array_combine(array_map(fn($c) => ":$c", $cols), array_values($rowDb));
        $params[':__id'] = $existing;
        $stmt->execute($params);
        return ['id' => (int)$existing, 'added' => false];
    }
}

function upsert_equipment(PDO $db, array $row): array {
    $sel = $db->prepare('SELECT `id` FROM `equipment` WHERE `game_internal_name` = :n LIMIT 1');
    $sel->execute([':n' => $row['game_internal_name']]);
    $existing = $sel->fetchColumn();

    $cols = [
        'game_internal_name', 'name', 'description', 'description_html', 'lore',
        'image', 'cooldown_seconds', 'duration_seconds', 'rarity_label',
        'unlock_requirement', 'orig', 'is_hidden',
    ];
    $rowDb = [
        'game_internal_name' => $row['game_internal_name'],
        'name'               => $row['name'],
        'description'        => $row['description'],
        'description_html'   => $row['description_html'],
        'lore'               => $row['lore'],
        'image'              => $row['image'],
        'cooldown_seconds'   => $row['cooldown_seconds'],
        'duration_seconds'   => $row['duration_seconds'],
        'rarity_label'       => $row['rarity_label'],
        'unlock_requirement' => $row['unlock_requirement'],
        'orig'               => $row['orig'],
        'is_hidden'          => $row['is_hidden'] ? 1 : 0,
    ];

    if ($existing === false) {
        $placeholders = implode(', ', array_map(fn($c) => ":$c", $cols));
        $colList      = implode(', ', array_map(fn($c) => "`$c`", $cols));
        $sql = "INSERT INTO `equipment` ($colList) VALUES ($placeholders)";
        $stmt = $db->prepare($sql);
        $stmt->execute(array_combine(array_map(fn($c) => ":$c", $cols), array_values($rowDb)));
        return ['id' => (int)$db->lastInsertId(), 'added' => true];
    } else {
        $setClause = implode(', ', array_map(fn($c) => "`$c` = :$c", $cols));
        $sql = "UPDATE `equipment` SET $setClause WHERE `id` = :__id";
        $stmt = $db->prepare($sql);
        $params = array_combine(array_map(fn($c) => ":$c", $cols), array_values($rowDb));
        $params[':__id'] = $existing;
        $stmt->execute($params);
        return ['id' => (int)$existing, 'added' => false];
    }
}

// ----------------------------------------------------------------
// Image download + PNG → WebP
// ----------------------------------------------------------------

const IMAGE_DIR = __DIR__ . '/../Content/image/item';

function download_image(string $displayName, string $internalName): ?string {
    // Tente d'abord File:<DisplayName>.png puis File:<InternalName>.png.
    $candidates = [
        $displayName . '.png',
        $internalName . '.png',
        str_replace(' ', '_', $displayName) . '.png',
    ];

    $url = null;
    foreach ($candidates as $cand) {
        $url = fetch_image_url($cand);
        if ($url) break;
    }
    if (!$url) return null;

    try {
        $png = http_get($url);
    } catch (Throwable $e) {
        return null;
    }
    if (strlen($png) < 100) return null;

    // Nom de fichier sanitisé (sans extension).
    $safe = preg_replace('/[^A-Za-z0-9_\-]/', '_', $displayName);
    if ($safe === '') $safe = $internalName;

    // Si l'extension GD est dispo → conversion PNG → WebP (plus petit).
    // Sinon, fallback : on écrit les octets PNG bruts en .png.
    if (function_exists('imagecreatefromstring') && function_exists('imagewebp')) {
        $img = @imagecreatefromstring($png);
        if ($img) {
            $outFile = IMAGE_DIR . '/' . $safe . '.webp';
            if (function_exists('imagepalettetotruecolor')) imagepalettetotruecolor($img);
            imagealphablending($img, false);
            imagesavealpha($img, true);
            imagewebp($img, $outFile, 85);
            imagedestroy($img);
            return $safe . '.webp';
        }
    }

    // Fallback PNG brut (pas de conversion)
    $outFile = IMAGE_DIR . '/' . $safe . '.png';
    if (@file_put_contents($outFile, $png) === false) return null;
    return $safe . '.png';
}

// ----------------------------------------------------------------
// Transformations
// ----------------------------------------------------------------

function transform_item(PDO $db, string $name, array $wiki): array {
    $stats = $wiki['Stats'] ?? [];
    if (!is_array($stats)) $stats = [];

    [$b1, $s1, $b2, $s2, $b3, $s3] = [null, null, null, null, null, null];
    if (isset($stats[1])) { $b1 = $stats[1]['Value'] ?? null; $s1 = $stats[1]['Add'] ?? null; }
    if (isset($stats[2])) { $b2 = $stats[2]['Value'] ?? null; $s2 = $stats[2]['Add'] ?? null; }
    if (isset($stats[3])) { $b3 = $stats[3]['Value'] ?? null; $s3 = $stats[3]['Add'] ?? null; }

    $desc = $wiki['Desc'] ?? '';
    $categories = [];
    if (isset($wiki['Category']) && is_array($wiki['Category'])) {
        foreach ($wiki['Category'] as $k => $v) {
            if (is_int($k) && is_string($v)) $categories[] = $v;
        }
    }

    $isHidden = in_array('Hidden', $categories, true);

    return [
        'game_internal_name' => $wiki['InternalName'] ?? $name,
        'name'               => $name,
        'rarity_id'          => map_rarity($db, $wiki['Rarity'] ?? 'Untiered'),
        'orig'               => detect_orig($wiki['Rarity'] ?? '', $categories),
        'description'        => $wiki['Quote'] ?? null,
        'description_html'   => $desc ? render_desc_html($desc) : null,
        'effect'             => $desc ? render_desc_plain($desc) : null,
        'image'              => null, // rempli ensuite (phase image)
        'lore'               => null, // optionnel : LogbookData
        'unlock_requirement' => $wiki['Unlock']  ?? null,
        'corrupt_pair'       => $wiki['Corrupt'] ?? null,
        'is_hidden'          => $isHidden,
        'base1' => $b1, 'stack1' => $s1,
        'base2' => $b2, 'stack2' => $s2,
        'base3' => $b3, 'stack3' => $s3,
        '_categories'        => $categories,
    ];
}

function transform_equipment(string $name, array $wiki): array {
    $desc = $wiki['Desc'] ?? '';
    $rarityLabel = $wiki['Rarity'] ?? 'Equipment';

    $orig = 1; // vanilla par défaut
    if ($rarityLabel === 'Elite Equipment') $orig = 3; // SOTV pour les Elite Equipment

    return [
        'game_internal_name' => $wiki['InternalName'] ?? $name,
        'name'               => $name,
        'description'        => $wiki['Quote'] ?? null,
        'description_html'   => $desc ? render_desc_html($desc) : null,
        'lore'               => null,
        'image'              => null,
        'cooldown_seconds'   => isset($wiki['Cooldown']) ? (int)$wiki['Cooldown']     : null,
        'duration_seconds'   => isset($wiki['Duration']) ? (float)$wiki['Duration']   : null,
        'rarity_label'       => $rarityLabel,
        'unlock_requirement' => $wiki['Unlock'] ?? null,
        'orig'               => $orig,
        'is_hidden'          => false,
    ];
}

// ----------------------------------------------------------------
// Main
// ----------------------------------------------------------------

$startTs = microtime(true);
logmsg("=== riskof import from wiki ===");
logmsg("dry_run=" . ($opts['dry_run']?'yes':'no') . " no_images=" . ($opts['no_images']?'yes':'no'));

// DB
$db = null;
if (!$opts['dry_run']) {
    try {
        $db = Model::getModel()->getPdo();
    } catch (Throwable $e) {
        err("DB connection failed: " . $e->getMessage());
        exit(1);
    }
}

$stats = [
    'items_added' => 0, 'items_updated' => 0,
    'equip_added' => 0, 'equip_updated' => 0,
    'images_ok'   => 0, 'images_fail'   => 0,
    'errors'      => [],
];

// --- Items ---
$items = [];
if (!$opts['equipments_only']) {
    logmsg("Fetch Module:Items/Data ...");
    $lua = fetch_module('Module:Items/Data');
    logmsg("  " . strlen($lua) . " bytes");
    $items = lua_parse_module($lua, 'items');
    logmsg("  " . count($items) . " items parsed");
}

// --- Equipments ---
$equipments = [];
if (!$opts['items_only']) {
    logmsg("Fetch Module:Equipment/Data ...");
    $lua2 = fetch_module('Module:Equipment/Data');
    logmsg("  " . strlen($lua2) . " bytes");
    $equipments = lua_parse_module($lua2, 'equipment');
    logmsg("  " . count($equipments) . " equipments parsed");
}

if ($opts['dry_run']) {
    logmsg("DRY-RUN — no DB writes, no downloads.");
    logmsg("Items keys (5 first): " . implode(', ', array_slice(array_keys($items), 0, 5)));
    logmsg("Equipments keys (5 first): " . implode(', ', array_slice(array_keys($equipments), 0, 5)));
    logmsg("\nDone in " . round((microtime(true) - $startTs), 2) . "s");
    logmsg("IMPORT DONE", true);
    exit(0);
}

// --- DB writes (transaction) ---
logmsg("");
logmsg(">> Phase 1/2 : upsert DB (items + equipments)");
$db->beginTransaction();
try {
    $nItems = count($items);
    $idx = 0;
    foreach ($items as $name => $wiki) {
        $row = transform_item($db, $name, $wiki);
        $res = upsert_item($db, $row);
        set_item_tags($db, $res['id'], $row['_categories']);
        if ($res['added']) $stats['items_added']++;
        else                $stats['items_updated']++;
        $idx++;
        if ($idx % 25 === 0 || $idx === $nItems) {
            logmsg(sprintf("  items %d/%d (%d%%)", $idx, $nItems, round($idx * 100 / max(1, $nItems))));
        }
    }

    $nEquip = count($equipments);
    $idx = 0;
    foreach ($equipments as $name => $wiki) {
        $row = transform_equipment($name, $wiki);
        $res = upsert_equipment($db, $row);
        if ($res['added']) $stats['equip_added']++;
        else                $stats['equip_updated']++;
        $idx++;
        if ($idx % 10 === 0 || $idx === $nEquip) {
            logmsg(sprintf("  equipments %d/%d (%d%%)", $idx, $nEquip, round($idx * 100 / max(1, $nEquip))));
        }
    }

    $db->commit();
    logmsg("DB transaction committed.");
} catch (Throwable $e) {
    $db->rollBack();
    err("Transaction rolled back: " . $e->getMessage());
    $stats['errors'][] = $e->getMessage();
    logmsg("IMPORT FAILED", true);
    exit(1);
}

// --- Images (hors transaction, plus tolérant) ---
if (!$opts['no_images']) {
    if (!is_dir(IMAGE_DIR)) @mkdir(IMAGE_DIR, 0775, true);

    $all = [];
    foreach ($items as $n => $w)      $all[] = ['name' => $n, 'wiki' => $w, 'table' => 'item'];
    foreach ($equipments as $n => $w) $all[] = ['name' => $n, 'wiki' => $w, 'table' => 'equipment'];

    $total = count($all);
    logmsg("");
    logmsg(">> Phase 2/2 : téléchargement de $total icones (~200ms/icone)");
    $started = microtime(true);

    foreach ($all as $i => $entry) {
        $internal  = $entry['wiki']['InternalName'] ?? $entry['name'];
        $imgFile   = download_image($entry['name'], $internal);
        if ($imgFile) {
            $stmt = $db->prepare(
                "UPDATE `{$entry['table']}` SET `image` = :img WHERE `game_internal_name` = :n"
            );
            $stmt->execute([':img' => $imgFile, ':n' => $internal]);
            $stats['images_ok']++;
        } else {
            $stats['images_fail']++;
            $stats['errors'][] = "image missing: " . $entry['name'];
        }
        $done = $i + 1;
        if ($done % 10 === 0 || $done === $total) {
            $elapsed = microtime(true) - $started;
            $rate    = $elapsed > 0 ? $done / $elapsed : 0;
            $eta     = $rate > 0 ? round(($total - $done) / $rate) : 0;
            logmsg(sprintf("  %d/%d (%d%%) — ok=%d fail=%d — ETA %ds",
                $done, $total, round($done * 100 / $total),
                $stats['images_ok'], $stats['images_fail'], $eta));
        }
        usleep(200000); // 200ms : respect du wiki
    }
    logmsg("Images: {$stats['images_ok']} ok, {$stats['images_fail']} fail.");
}

logmsg("");
logmsg("==== SUMMARY ====", true);
logmsg("Items     : {$stats['items_added']} added, {$stats['items_updated']} updated", true);
logmsg("Equipments: {$stats['equip_added']} added, {$stats['equip_updated']} updated", true);
logmsg("Images    : {$stats['images_ok']} ok, {$stats['images_fail']} fail", true);
if (!empty($stats['errors'])) {
    logmsg("Errors    : " . count($stats['errors']), true);
    foreach (array_slice($stats['errors'], 0, 20) as $e) logmsg("  - $e", true);
}
logmsg("Total time: " . round((microtime(true) - $startTs), 2) . "s", true);
logmsg("IMPORT DONE", true);
