<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/session.php';

/**
 * Monta uma URL completa utilizando APP_URL.
 */
function appUrl(string $caminho = ''): string
{
    $baseUrl = rtrim(
        (string) env(
            'APP_URL',
            'http://localhost/sistema_rh/public'
        ),
        '/'
    );

    if ($caminho === '') {
        return $baseUrl;
    }

    return $baseUrl . '/' . ltrim($caminho, '/');
}

/**
 * Escapa textos antes de exibi-los no HTML.
 */
function escapar(?string $valor): string
{
    return htmlspecialchars(
        $valor ?? '',
        ENT_QUOTES,
        'UTF-8'
    );
}

/**
 * Cria uma mensagem temporária na sessão.
 */
function definirFlash(string $tipo, string $mensagem): void
{
    $_SESSION['flash'][$tipo] = $mensagem;
}

/**
 * Obtém e remove uma mensagem temporária.
 */
function obterFlash(string $tipo): ?string
{
    $mensagem = $_SESSION['flash'][$tipo] ?? null;

    unset($_SESSION['flash'][$tipo]);

    return is_string($mensagem)
        ? $mensagem
        : null;
}

/**
 * Retorna ou cria o token CSRF da sessão.
 */
function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(
            random_bytes(32)
        );
    }

    return (string) $_SESSION['csrf_token'];
}

/**
 * Gera o campo HTML do token CSRF.
 */
function campoCsrf(): string
{
    return sprintf(
        '<input type="hidden" name="csrf_token" value="%s">',
        escapar(csrfToken())
    );
}

/**
 * Valida o token CSRF enviado pelo formulário.
 */
function csrfValido(?string $token): bool
{
    if (
        $token === null
        || empty($_SESSION['csrf_token'])
    ) {
        return false;
    }

    return hash_equals(
        (string) $_SESSION['csrf_token'],
        $token
    );
}

/**
 * Retorna os dados do usuário autenticado.
 */
function usuarioAutenticado(): ?array
{
    $usuario = $_SESSION['usuario'] ?? null;

    return is_array($usuario)
        ? $usuario
        : null;
}

/**
 * Redireciona o usuário para sua área correspondente.
 */
function redirecionarAreaUsuario(): void
{
    $usuario = usuarioAutenticado();

    if (!$usuario) {
        header('Location: ' . appUrl('index.php'));
        exit;
    }

    if (!empty($usuario['trocar_senha'])) {
        header(
            'Location: ' .
            appUrl('alterar-senha.php')
        );
        exit;
    }

    header('Location: ' . appUrl('dashboard.php'));
    exit;
}

/**
 * Impede que usuários autenticados retornem ao login.
 */
function exigirVisitante(): void
{
    if (usuarioAutenticado()) {
        redirecionarAreaUsuario();
    }
}

/**
 * Exige autenticação para acessar uma página.
 */
function exigirAutenticacao(
    bool $permitirTrocaSenhaPendente = false
): void {
    $usuario = usuarioAutenticado();

    if (!$usuario) {
        definirFlash(
            'erro',
            'Sua sessão expirou. Entre novamente.'
        );

        header('Location: ' . appUrl('index.php'));
        exit;
    }

    if (
        !$permitirTrocaSenhaPendente
        && !empty($usuario['trocar_senha'])
    ) {
        header(
            'Location: ' .
            appUrl('alterar-senha.php')
        );
        exit;
    }
}