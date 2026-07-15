<?php

declare(strict_types=1);

/**
 * Gera uma URL completa a partir do APP_URL definido no .env.
 *
 * Exemplo:
 * appUrl('login')
 *
 * Resultado:
 * http://localhost/rh-sistema/public/login
 */
function appUrl(string $caminho = ''): string
{
    $urlBase = rtrim(
        (string) env(
            'APP_URL',
            'http://localhost/rh-sistema/public'
        ),
        '/'
    );

    if ($caminho === '') {
        return $urlBase;
    }

    return $urlBase . '/' . ltrim($caminho, '/');
}

/**
 * Escapa um conteúdo antes de exibi-lo no HTML.
 */
function escapar(
    string|int|float|null $valor
): string {
    return htmlspecialchars(
        (string) ($valor ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

/**
 * Redireciona para uma rota da aplicação.
 */
function redirecionar(string $caminho): never
{
    header('Location: ' . appUrl($caminho));
    exit;
}