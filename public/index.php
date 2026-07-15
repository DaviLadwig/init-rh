<?php

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));

require_once BASE_PATH . '/config/env.php';
require_once BASE_PATH . '/config/session.php';

$metodoHttp = strtoupper(
    $_SERVER['REQUEST_METHOD'] ?? 'GET'
);

$caminhoSolicitado = parse_url(
    $_SERVER['REQUEST_URI'] ?? '/',
    PHP_URL_PATH
);

if (!is_string($caminhoSolicitado)) {
    $caminhoSolicitado = '/';
}

/*
|--------------------------------------------------------------------------
| Remove /rh-sistema/public da URL
|--------------------------------------------------------------------------
*/

$diretorioPublico = str_replace(
    '\\',
    '/',
    dirname($_SERVER['SCRIPT_NAME'] ?? '')
);

if (
    $diretorioPublico !== '/'
    && $diretorioPublico !== '.'
    && str_starts_with(
        $caminhoSolicitado,
        $diretorioPublico
    )
) {
    $caminhoSolicitado = substr(
        $caminhoSolicitado,
        strlen($diretorioPublico)
    );
}

$rota = '/' . trim($caminhoSolicitado, '/');

if ($rota === '//') {
    $rota = '/';
}

$rotas = require BASE_PATH . '/routes/web.php';

$acao = $rotas[$metodoHttp][$rota] ?? null;

if (!is_callable($acao)) {
    http_response_code(404);

    header('Content-Type: text/html; charset=UTF-8');

    echo '<!DOCTYPE html>';
    echo '<html lang="pt-BR">';
    echo '<head>';
    echo '<meta charset="UTF-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
    echo '<title>Página não encontrada</title>';
    echo '</head>';
    echo '<body>';
    echo '<h1>404</h1>';
    echo '<p>A página solicitada não foi encontrada.</p>';
    echo '</body>';
    echo '</html>';

    exit;
}

$acao();