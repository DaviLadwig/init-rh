<?php

declare(strict_types=1);

/**
 * Armazena uma mensagem temporária na sessão.
 */
function definirFlash(
    string $tipo,
    string $mensagem
): void {
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
 * Gera o campo oculto CSRF.
 */
function campoCsrf(): string
{
    return sprintf(
        '<input type="hidden" name="csrf_token" value="%s">',
        escapar(csrfToken())
    );
}

/**
 * Valida o token enviado pelo formulário.
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
 * Retorna o usuário autenticado.
 */
function usuarioAutenticado(): ?array
{
    $usuario = $_SESSION['usuario'] ?? null;

    return is_array($usuario)
        ? $usuario
        : null;
}

/**
 * Verifica se existe usuário autenticado.
 */
function estaAutenticado(): bool
{
    return usuarioAutenticado() !== null;
}

/**
 * Verifica se o usuário autenticado possui uma permissão.
 */
function temPermissao(string $codigo): bool
{
    $usuario = usuarioAutenticado();

    if (!$usuario) {
        return false;
    }

    $permissoes = $usuario['permissoes'] ?? [];

    if (!is_array($permissoes)) {
        return false;
    }

    return in_array(
        $codigo,
        $permissoes,
        true
    );
}