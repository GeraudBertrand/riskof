<?php
/**
 * Copier ce fichier vers `auth_credentials.php` (gitignoré) puis remplir.
 *
 *   cp admin/auth_credentials.example.php admin/auth_credentials.php
 *   nano admin/auth_credentials.php
 *
 * `php_binary` est optionnel : chemin vers le binaire php-cli si `php` n'est
 * pas dans le PATH du process FPM. Sur Synology, c'est typiquement :
 *   /usr/local/bin/php74              (lien créé par Web Station)
 *   /volume1/@appstore/PHP7.4/usr/local/bin/php
 *   /volume1/@appstore/PHP8.2/usr/local/bin/php
 */
return [
    'user'       => 'change-me',
    'pass'       => 'change-me-too',
    // 'php_binary' => '/usr/local/bin/php74',
];
