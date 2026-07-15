<?php

declare(strict_types=1);

require_once BASE_PATH . '/app/helpers/session.php';
require_once BASE_PATH . '/app/helpers/url.php';

class PermissionMiddleware
{
    /**
     * Verifica se o usuário autenticado possui a permissão exigida.
     */
    public static function executar(
        string $permissao,
        callable $proximo
    ): void {
        if (!estaAutenticado()) {
            definirFlash(
                'erro',
                'Sua sessão expirou. Entre novamente.'
            );

            redirecionar('login');
        }

        if (!temPermissao($permissao)) {
            definirFlash(
                'erro',
                'Você não possui permissão para acessar esse recurso.'
            );

            redirecionar('dashboard');
        }

        $proximo();
    }
}