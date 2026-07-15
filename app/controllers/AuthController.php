<?php

declare(strict_types=1);

require_once BASE_PATH . '/app/helpers/url.php';
require_once BASE_PATH . '/app/helpers/view.php';
require_once BASE_PATH . '/app/helpers/session.php';

class AuthController
{
    public function __construct(
        private AutenticacaoService $autenticacaoService
    ) {
    }

    /**
     * Exibe a tela de login.
     */
    public function login(): void
    {
        if (estaAutenticado()) {
            $usuario = usuarioAutenticado();

            if (!empty($usuario['trocar_senha'])) {
                redirecionar('alterar-senha');
            }

            redirecionar('dashboard');
        }

        $dadosAnteriores =
            $_SESSION['login_anterior'] ?? [];

        unset($_SESSION['login_anterior']);

        renderizarView(
            'auth/login',
            [
                'tituloPagina' => 'Entrar',

                'nomeSistema' => (string) env(
                    'APP_NAME',
                    'Sistema de RH'
                ),

                'codigoOrganizacao' =>
                    $dadosAnteriores['codigo_organizacao']
                    ?? 'saude-vitoria',

                'emailAnterior' =>
                    $dadosAnteriores['email']
                    ?? '',

                'erro' => obterFlash('erro'),
                'sucesso' => obterFlash('sucesso'),
            ]
        );
    }

    /**
     * Processa o formulário de login.
     */
    public function autenticar(): void
    {
        if (!csrfValido($_POST['csrf_token'] ?? null)) {
            definirFlash(
                'erro',
                'A solicitação expirou. Tente novamente.'
            );

            redirecionar('login');
        }

        $codigoOrganizacao = strtolower(
            trim(
                (string) (
                    $_POST['codigo_organizacao']
                    ?? ''
                )
            )
        );

        $email = strtolower(
            trim(
                (string) ($_POST['email'] ?? '')
            )
        );

        $senha = (string) ($_POST['senha'] ?? '');

        $_SESSION['login_anterior'] = [
            'codigo_organizacao' => $codigoOrganizacao,
            'email' => $email,
        ];

        if (
            $codigoOrganizacao === ''
            || $email === ''
            || $senha === ''
        ) {
            definirFlash(
                'erro',
                'Preencha todos os campos.'
            );

            redirecionar('login');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            definirFlash(
                'erro',
                'Informe um endereço de e-mail válido.'
            );

            redirecionar('login');
        }

        $resultado =
            $this->autenticacaoService->autenticar(
                $codigoOrganizacao,
                $email,
                $senha
            );

        if (!$resultado['sucesso']) {
            definirFlash(
                'erro',
                (string) $resultado['mensagem']
            );

            redirecionar('login');
        }

        session_regenerate_id(true);

        $_SESSION['usuario'] = $resultado['usuario'];

        unset(
            $_SESSION['login_anterior'],
            $_SESSION['csrf_token']
        );

        if (
            !empty(
                $_SESSION['usuario']['trocar_senha']
            )
        ) {
            redirecionar('alterar-senha');
        }

        redirecionar('dashboard');
    }

    /**
     * Exibe a tela de alteração obrigatória.
     */
    public function alterarSenha(): void
    {
        $usuario = usuarioAutenticado();

        if (empty($usuario['trocar_senha'])) {
            redirecionar('dashboard');
        }

        renderizarView(
            'auth/alterar-senha',
            [
                'tituloPagina' => 'Alterar senha',
                'usuario' => $usuario,
                'erro' => obterFlash('erro'),
            ]
        );
    }

    /**
     * Salva a nova senha.
     */
    public function salvarNovaSenha(): void
    {
        if (!csrfValido($_POST['csrf_token'] ?? null)) {
            definirFlash(
                'erro',
                'A solicitação expirou. Tente novamente.'
            );

            redirecionar('alterar-senha');
        }

        $senhaAtual =
            (string) ($_POST['senha_atual'] ?? '');

        $novaSenha =
            (string) ($_POST['nova_senha'] ?? '');

        $confirmacao =
            (string) ($_POST['confirmar_senha'] ?? '');

        if (
            $senhaAtual === ''
            || $novaSenha === ''
            || $confirmacao === ''
        ) {
            definirFlash(
                'erro',
                'Preencha todos os campos.'
            );

            redirecionar('alterar-senha');
        }

        $resultado =
            $this->autenticacaoService->alterarSenha(
                usuarioAutenticado(),
                $senhaAtual,
                $novaSenha,
                $confirmacao
            );

        if (!$resultado['sucesso']) {
            definirFlash(
                'erro',
                (string) $resultado['mensagem']
            );

            redirecionar('alterar-senha');
        }

        $_SESSION['usuario']['trocar_senha'] = false;

        unset($_SESSION['csrf_token']);

        session_regenerate_id(true);

        definirFlash(
            'sucesso',
            'Sua senha foi alterada com sucesso.'
        );

        redirecionar('dashboard');
    }

    /**
     * Encerra a sessão.
     */
    public function logout(): void
    {
        if (!csrfValido($_POST['csrf_token'] ?? null)) {
            redirecionar('dashboard');
        }

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $parametros =
                session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                time() - 42000,
                $parametros['path'],
                $parametros['domain'],
                $parametros['secure'],
                $parametros['httponly']
            );
        }

        session_destroy();

        redirecionar('login');
    }
}