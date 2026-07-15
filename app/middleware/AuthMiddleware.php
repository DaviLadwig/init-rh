<?php

declare(strict_types=1);

require_once BASE_PATH . '/app/helpers/url.php';
require_once BASE_PATH . '/app/helpers/session.php';

class AuthMiddleware
{
    /**
     * Exige que exista um usuário autenticado.
     */
    public static function executar(
        callable $proximo,
        bool $permitirTrocaSenhaPendente = false
    ): void {
        $usuario = usuarioAutenticado();

        if (!$usuario) {
            definirFlash(
                'erro',
                'Sua sessão expirou. Entre novamente.'
            );

            redirecionar('login');
        }

        if (
            !$permitirTrocaSenhaPendente
            && !empty($usuario['trocar_senha'])
        ) {
            redirecionar('alterar-senha');
        }

        $proximo();
    }
}