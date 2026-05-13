<?php
/**
 * Test minimal du parser Lua.
 *
 * Usage (depuis SSH sur le NAS, dans le dossier racine du site) :
 *
 *     php scripts/tests/parser_test.php
 *
 * Le script télécharge le Module:Items/Data du wiki, le parse, et affiche
 * un échantillon. Aucune écriture en DB.
 */

require_once __DIR__ . '/../lua_parser.php';

function http_get(string $url): string {
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_USERAGENT      => 'riskof-importer/1.0 (test)',
        ]);
        $raw  = curl_exec($ch);
        $err  = curl_error($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($raw === false) throw new RuntimeException("curl failed: $err");
        if ($code >= 400)   throw new RuntimeException("HTTP $code on $url");
        return $raw;
    }
    $ctx = stream_context_create([
        'http' => ['header' => "User-Agent: riskof-importer/1.0 (test)\r\n", 'timeout' => 30]
    ]);
    $raw = @file_get_contents($url, false, $ctx);
    if ($raw === false) {
        throw new RuntimeException("HTTP fetch failed for $url (install ext-curl or enable openssl)");
    }
    return $raw;
}

function fetch_module(string $title): string {
    $url = 'https://riskofrain2.fandom.com/api.php'
         . '?action=query'
         . '&titles=' . rawurlencode($title)
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

echo "Fetch Module:Items/Data ...\n";
$lua = fetch_module('Module:Items/Data');
echo "  " . strlen($lua) . " bytes received\n";

echo "Parse ...\n";
$t0 = microtime(true);
$items = lua_parse_module($lua, 'items');
$dt = (microtime(true) - $t0) * 1000;
echo "  " . count($items) . " items parsed in " . number_format($dt, 1) . " ms\n";

echo "\nSample keys :\n";
$names = array_slice(array_keys($items), 0, 8);
foreach ($names as $n) echo "  - $n\n";

echo "\nTougher Times :\n";
if (isset($items['Tougher Times'])) {
    foreach ($items['Tougher Times'] as $k => $v) {
        if (is_array($v)) {
            echo "  $k => " . json_encode($v) . "\n";
        } else {
            echo "  $k => " . var_export($v, true) . "\n";
        }
    }
} else {
    echo "  /!\\ 'Tougher Times' NOT FOUND\n";
}

echo "\nFetch Module:Equipment/Data ...\n";
$lua2 = fetch_module('Module:Equipment/Data');
$equipments = lua_parse_module($lua2, 'equipment');
echo "  " . count($equipments) . " equipments parsed\n";
echo "Sample : " . implode(', ', array_slice(array_keys($equipments), 0, 5)) . "\n";

echo "\nOK\n";
