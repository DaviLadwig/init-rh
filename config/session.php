<?php

declare(strict_types=1);

require_once __DIR__ . '/env.php';

if (session_status() === PHP_SESSION_NONE) {
    $usaHttps =
        (!empty($_SERVER['HTTPS'])
            && $_SERVER['HTTPS'] !== 'off')
        || ((int) ($_SERVER['SERVER_PORT'] ?? 0) === 443);

    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_httponly', '1');

    session_name('rh_sistema_session');

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $usaHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}

if (!isset($_SESSION['_sessao_criada_em'])) {
    session_regenerate_id(true);

    $_SESSION['_sessao_criada_em'] = time();
}