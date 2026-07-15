<?php

declare(strict_types=1);

require_once BASE_PATH . '/app/helpers/url.php';
require_once BASE_PATH . '/app/helpers/view.php';
require_once BASE_PATH . '/app/helpers/session.php';

class DashboardController
{
    /**
     * Exibe o painel principal.
     */
    public function index(): void
    {
        $usuario = usuarioAutenticado();

        if (!$usuario) {
            redirecionar('login');
        }

        $permissoes = $usuario['permissoes'] ?? [];

        if (!is_array($permissoes)) {
            $permissoes = [];
        }

        renderizarView(
            'dashboard/index',
            [
                'tituloPagina' => 'Dashboard',
                'usuario' => $usuario,
                'totalPermissoes' => count($permissoes),
                'sucesso' => obterFlash('sucesso'),
            ],
            'layouts/main'
        );
    }
}