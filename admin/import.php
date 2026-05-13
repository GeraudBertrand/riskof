<?php
/**
 * Page admin — lance l'import wiki via exec() en background.
 *
 * Flux :
 *   - POST  : pose un lock, spawn `php scripts/import_from_wiki.php`
 *             en background via exec/nohup, redirige vers ?status=running.
 *             La requête HTTP se ferme immédiatement.
 *   - GET   : affiche le bouton + log + auto-refresh tant que le lock existe.
 *
 * Pré-requis serveur :
 *   - `exec` retiré de disable_functions dans le profil PHP de Web Station
 *   - php-cli accessible dans le PATH (ou via $creds['php_binary'])
 */

// ---------------- Auth ----------------

$credsPath = __DIR__ . '/auth_credentials.php';
if (!file_exists($credsPath)) {
    http_response_code(500);
    echo "Auth misconfigured: " . htmlspecialchars(basename($credsPath)) . " missing. ";
    echo "Copiez auth_credentials.example.php → auth_credentials.php et remplissez.";
    exit;
}
$creds = require $credsPath;

if (!isset($_SERVER['PHP_AUTH_USER']) ||
    !hash_equals((string)$creds['user'], (string)$_SERVER['PHP_AUTH_USER']) ||
    !hash_equals((string)$creds['pass'], (string)$_SERVER['PHP_AUTH_PW'])) {
    header('WWW-Authenticate: Basic realm="riskof admin"');
    http_response_code(401);
    echo "Authentication required";
    exit;
}

$logFile        = realpath(__DIR__ . '/../Content') . '/import_log.txt';
$lockFile       = realpath(__DIR__ . '/../Content') . '/import.lock';
$importerScript = realpath(__DIR__ . '/../scripts/import_from_wiki.php');
$phpBinary      = $creds['php_binary'] ?? 'php';

function isRunning(string $lockFile): bool {
    if (!file_exists($lockFile)) return false;
    // Si le lock a plus de 10 minutes, on considère qu'il est obsolète.
    return (time() - @filemtime($lockFile)) < 600;
}

function execAvailable(): bool {
    if (!function_exists('exec')) return false;
    $disabled = array_map('trim', explode(',', (string)ini_get('disable_functions')));
    return !in_array('exec', $disabled, true);
}

// ---------------- POST : spawn background ----------------

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'run') {

    if (isRunning($lockFile)) {
        header('Location: import.php?status=already-running');
        exit;
    }

    if (!execAvailable()) {
        header('Location: import.php?status=exec-disabled');
        exit;
    }

    // Tentative d'écriture du lock + log, en capturant l'erreur réelle.
    $writeErr = null;
    if (@file_put_contents($lockFile, (string)getmypid()) === false) {
        $writeErr = 'lock: ' . (error_get_last()['message'] ?? '?');
    }
    if ($writeErr === null && @file_put_contents($logFile, "[" . date('c') . "] start\n") === false) {
        $writeErr = 'log: ' . (error_get_last()['message'] ?? '?');
    }

    if ($writeErr !== null) {
        header('Location: import.php?status=write-failed&err=' . urlencode($writeErr));
        exit;
    }

    // Commande shell : exécute le script, puis supprime le lock à la fin.
    $innerCmd = sprintf(
        '%s %s > %s 2>&1; rm -f %s',
        escapeshellcmd($phpBinary),
        escapeshellarg($importerScript),
        escapeshellarg($logFile),
        escapeshellarg($lockFile)
    );
    $cmd = sprintf(
        'nohup sh -c %s > /dev/null 2>&1 < /dev/null &',
        escapeshellarg($innerCmd)
    );

    $output = [];
    $ret    = -1;
    exec($cmd, $output, $ret);

    // Append diagnostic au log
    @file_put_contents(
        $logFile,
        sprintf("[exec ret=%d] output=%s\ncmd: %s\n",
            $ret,
            json_encode($output),
            $cmd
        ),
        FILE_APPEND
    );

    header('Location: import.php?status=running');
    exit;
}

// ---------------- GET : page d'accueil + log ----------------

$running    = isRunning($lockFile);
$logContent = @file_get_contents($logFile) ?: '';
$lastDone   = strpos($logContent, 'IMPORT DONE')   !== false;
$lastFail   = strpos($logContent, 'IMPORT FAILED') !== false
           || strpos($logContent, '[EXCEPTION]')   !== false
           || strpos($logContent, '[PHP FATAL]')   !== false;
$status     = $_GET['status'] ?? null;

?><!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>riskof — Import wiki</title>
    <?php if ($running): ?>
    <meta http-equiv="refresh" content="3">
    <?php endif; ?>
    <style>
        body { font-family: system-ui, sans-serif; background:#1c1c1c; color:#ddd; padding:24px; max-width:1100px; margin:0 auto; }
        h1 { margin-top:0; }
        button { background:#4caf50; color:white; border:0; padding:12px 18px; font-size:15px; cursor:pointer; border-radius:4px; }
        button:hover { background:#5bc25b; }
        button:disabled { background:#666; cursor:not-allowed; }
        .badge { display:inline-block; padding:3px 10px; border-radius:3px; font-size:13px; font-weight:bold; }
        .running { background:#ffb300; color:#000; }
        .done    { background:#4caf50; color:#000; }
        .failed  { background:#e53935; color:#fff; }
        .warn    { color:#ffb300; }
        pre { background:#000; color:#9cdcfe; padding:14px; overflow:auto; max-height:65vh; font-size:13px; line-height:1.4; border-radius:4px; white-space:pre-wrap; }
        code { background:#000; padding:2px 6px; border-radius:3px; color:#9cdcfe; }
        small { color:#999; }
    </style>
</head>
<body>
    <h1>Import items &amp; equipments depuis le wiki Risk of Rain 2</h1>

    <p>
        État :
        <?php if ($running): ?>
            <span class="badge running">EN COURS</span>
            <small>— page rafraîchie toutes les 3 s</small>
        <?php elseif ($lastFail): ?>
            <span class="badge failed">ÉCHEC</span>
        <?php elseif ($lastDone): ?>
            <span class="badge done">OK</span>
        <?php else: ?>
            <small>jamais lancé</small>
        <?php endif; ?>
    </p>

    <?php if ($status === 'already-running'): ?>
        <p class="warn">Un import est déjà en cours, attends qu'il finisse (ou supprime <code>Content/import.lock</code> si tu es sûr qu'il n'y a plus rien qui tourne).</p>
    <?php endif; ?>

    <?php if ($status === 'exec-disabled'): ?>
        <p class="warn">
            <strong>exec() est désactivé sur ce PHP.</strong>
            Va dans DSM → Web Station → Script Language Settings → édite le profil PHP
            utilisé par le site → retire <code>exec</code> de la liste "Disable functions".
        </p>
    <?php endif; ?>

    <?php if ($status === 'write-failed'): ?>
        <p class="warn">
            <strong>Échec d'écriture dans <code>Content/</code></strong> (permissions).
            Erreur PHP : <code><?php echo htmlspecialchars($_GET['err'] ?? ''); ?></code><br>
            Voir Diagnostic ci-dessous pour le détail.
        </p>
    <?php endif; ?>

    <form method="post" style="margin-bottom: 24px;">
        <input type="hidden" name="action" value="run">
        <button type="submit" <?php if ($running) echo 'disabled'; ?>>
            Lancer l'import depuis le wiki
        </button>
    </form>

    <p><small>
        L'import dure ~1 minute. Tu peux fermer l'onglet et revenir plus tard :
        l'exécution continue en arrière-plan, le log reste sur disque.
    </small></p>

    <?php if ($logContent !== ''): ?>
        <h2>Log</h2>
        <pre><?php echo htmlspecialchars($logContent); ?></pre>
    <?php endif; ?>

    <details style="margin-top: 24px; color:#999;" <?php if ($status === 'write-failed') echo 'open'; ?>>
        <summary>Diagnostic</summary>
        <?php
            $contentDir   = realpath(__DIR__ . '/../Content');
            $contentDirOk = $contentDir && is_dir($contentDir);
            $contentWrite = $contentDirOk && is_writable($contentDir);

            // Test concret d'écriture
            $testWriteRes = 'n/a';
            if ($contentDirOk) {
                $tmp = $contentDir . '/.write_test_' . uniqid();
                if (@file_put_contents($tmp, 'x') === false) {
                    $testWriteRes = 'ÉCHEC : ' . (error_get_last()['message'] ?? '?');
                } else {
                    $testWriteRes = 'OK';
                    @unlink($tmp);
                }
            }

            $whoami = 'n/a';
            if (function_exists('posix_geteuid')) {
                $uid = posix_geteuid();
                $gid = posix_getegid();
                $pw  = function_exists('posix_getpwuid') ? @posix_getpwuid($uid) : null;
                $gr  = function_exists('posix_getgrgid') ? @posix_getgrgid($gid) : null;
                $whoami = sprintf('uid=%d(%s) gid=%d(%s)',
                    $uid, $pw['name'] ?? '?',
                    $gid, $gr['name'] ?? '?');
            }

            $contentPerms = $contentDirOk ? substr(sprintf('%o', fileperms($contentDir)), -4) : 'n/a';
            $contentOwner = 'n/a';
            if ($contentDirOk && function_exists('posix_getpwuid')) {
                $own = @posix_getpwuid(fileowner($contentDir));
                $grp = @posix_getgrgid(filegroup($contentDir));
                $contentOwner = ($own['name'] ?? fileowner($contentDir)) . ':' . ($grp['name'] ?? filegroup($contentDir));
            }

            // Test du binaire PHP CLI : version + extensions critiques
            $phpBinTest = 'n/a';
            $phpExts    = 'n/a';
            if (execAvailable()) {
                $verCmd = escapeshellcmd($phpBinary) . ' -v 2>&1';
                $out    = [];
                @exec($verCmd, $out, $ret);
                $phpBinTest = $ret === 0
                    ? trim($out[0] ?? '?')
                    : 'ÉCHEC (ret=' . $ret . ') : ' . implode(' / ', $out);

                $extCmd = escapeshellcmd($phpBinary) . ' -r ' . escapeshellarg(
                    'echo (extension_loaded("pdo_mysql")?"pdo_mysql ":"-pdo_mysql ").'
                  . '(extension_loaded("curl")?"curl ":"-curl ").'
                  . '(extension_loaded("gd")?"gd":"-gd");'
                ) . ' 2>&1';
                $out2 = [];
                @exec($extCmd, $out2, $ret2);
                $phpExts = $ret2 === 0 ? implode(' ', $out2) : 'ÉCHEC : ' . implode(' / ', $out2);
            }
        ?>
        <pre style="font-size:12px;">
exec dispo         : <?php echo execAvailable() ? 'oui' : 'NON (retire-le de disable_functions)'; ?>

PHP_SAPI           : <?php echo php_sapi_name(); ?>

PHP binary cmd     : <?php echo htmlspecialchars($phpBinary); ?>

  → -v             : <?php echo htmlspecialchars($phpBinTest); ?>

  → extensions     : <?php echo htmlspecialchars($phpExts); ?>


Importer path      : <?php echo htmlspecialchars((string)$importerScript); ?>

Log path           : <?php echo htmlspecialchars($logFile); ?>

Lock path          : <?php echo htmlspecialchars($lockFile); ?>

Lock présent       : <?php echo file_exists($lockFile) ? 'oui (age '.(time()-@filemtime($lockFile)).'s)' : 'non'; ?>


Process user       : <?php echo htmlspecialchars($whoami); ?>

Content dir        : <?php echo htmlspecialchars((string)$contentDir); ?>

Content owner:group: <?php echo htmlspecialchars($contentOwner); ?>

Content perms      : <?php echo htmlspecialchars($contentPerms); ?>

is_writable()      : <?php echo $contentWrite ? 'oui' : 'NON'; ?>

Test écriture réel : <?php echo htmlspecialchars($testWriteRes); ?>


disable_functions  : <?php echo htmlspecialchars((string)ini_get('disable_functions') ?: '(vide)'); ?>
        </pre>
    </details>
</body>
</html>
