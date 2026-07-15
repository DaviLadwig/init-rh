<?php

declare(strict_types=1);

require_once BASE_PATH . '/app/helpers/url.php';
require_once BASE_PATH . '/app/helpers/view.php';
require_once BASE_PATH . '/app/helpers/session.php';

class SetorController
{
    public function __construct(
        private SetorService $setorService
    ) {
    }

    /**
     * Exibe a listagem de setores.
     */
    public function index(): void
    {
        $usuario = usuarioAutenticado();

        if (!$usuario) {
            redirecionar('login');
        }

        $organizacaoId = (int) (
            $usuario['organizacao_id']
            ?? 0
        );

        $busca = trim(
            (string) ($_GET['busca'] ?? '')
        );

        $situacao = strtoupper(
            trim(
                (string) (
                    $_GET['situacao']
                    ?? 'TODOS'
                )
            )
        );

        try {
            $setores = $this->setorService->listar(
                $organizacaoId,
                $busca,
                $situacao
            );

            renderizarView(
                'setores/index',
                [
                    'tituloPagina' => 'Setores',
                    'usuario' => $usuario,
                    'setores' => $setores,
                    'busca' => $busca,
                    'situacao' => $situacao,
                    'sucesso' => obterFlash('sucesso'),
                    'erro' => obterFlash('erro'),
                ],
                'layouts/main'
            );
        } catch (Throwable $erro) {
            renderizarView(
                'setores/index',
                [
                    'tituloPagina' => 'Setores',
                    'usuario' => $usuario,
                    'setores' => [],
                    'busca' => $busca,
                    'situacao' => $situacao,
                    'sucesso' => null,
                    'erro' => env('APP_DEBUG', false)
                        ? $erro->getMessage()
                        : 'Não foi possível carregar os setores.',
                ],
                'layouts/main'
            );
        }
    }

    /*
     * Exibe o formulário de cadastro de setor.
     */
    public function criar(): void
    {
        $usuario = usuarioAutenticado();

        if (!$usuario) {
            redirecionar('login');
        }

        $dadosFormulario =
            $_SESSION['setor_formulario'] ?? [];

        $errosFormulario =
            $_SESSION['setor_erros'] ?? [];

        unset(
            $_SESSION['setor_formulario'],
            $_SESSION['setor_erros']
        );

        renderizarView(
            'setores/create',
            [
                'tituloPagina' => 'Novo setor',
                'usuario' => $usuario,

                'dadosFormulario' =>
                    is_array($dadosFormulario)
                        ? $dadosFormulario
                        : [],

                'errosFormulario' =>
                    is_array($errosFormulario)
                        ? $errosFormulario
                        : [],

                'erro' => obterFlash('erro'),
            ],
            'layouts/main'
        );
    }
    /**
     * Processa o cadastro do setor.
     */
    public function salvar(): void
    {
        if (!csrfValido($_POST['csrf_token'] ?? null)) {
            definirFlash(
                'erro',
                'A solicitação expirou. Tente novamente.'
            );

            redirecionar('setores/criar');
        }

        $usuario = usuarioAutenticado();

        if (!$usuario) {
            redirecionar('login');
        }

        $organizacaoId = (int) (
            $usuario['organizacao_id']
            ?? 0
        );

        try {
            $resultado = $this->setorService->criar(
                $organizacaoId,
                $_POST
            );

            if (!$resultado['sucesso']) {
                $_SESSION['setor_formulario'] =
                    $resultado['dados'] ?? $_POST;

                $_SESSION['setor_erros'] =
                    $resultado['erros'] ?? [];

                definirFlash(
                    'erro',
                    (string) (
                        $resultado['mensagem']
                        ?? 'Verifique os dados informados.'
                    )
                );

                redirecionar('setores/criar');
            }

            definirFlash(
                'sucesso',
                (string) $resultado['mensagem']
            );

            redirecionar('setores');
        } catch (Throwable $erro) {
            $_SESSION['setor_formulario'] = $_POST;

            definirFlash(
                'erro',
                env('APP_DEBUG', false)
                    ? $erro->getMessage()
                    : 'Não foi possível cadastrar o setor.'
            );

            redirecionar('setores/criar');
        }
    }

    /**
     * Exibe o formulário de edição.
     */
    public function editar(): void
    {
        $usuario = usuarioAutenticado();

        if (!$usuario) {
            redirecionar('login');
        }

        $setorId = filter_input(
            INPUT_GET,
            'id',
            FILTER_VALIDATE_INT
        );

        if (!$setorId || $setorId <= 0) {
            definirFlash(
                'erro',
                'O setor informado é inválido.'
            );

            redirecionar('setores');
        }

        $organizacaoId = (int) (
            $usuario['organizacao_id']
            ?? 0
        );

        $setor = $this->setorService->buscarPorId(
            $setorId,
            $organizacaoId
        );

        if (!$setor) {
            definirFlash(
                'erro',
                'O setor informado não foi encontrado.'
            );

            redirecionar('setores');
        }

        $dadosTemporarios =
            $_SESSION['setor_formulario'] ?? null;

        $errosFormulario =
            $_SESSION['setor_erros'] ?? [];

        unset(
            $_SESSION['setor_formulario'],
            $_SESSION['setor_erros']
        );

        if (is_array($dadosTemporarios)) {
            $setor = array_merge(
                $setor,
                $dadosTemporarios
            );
        }

        renderizarView(
            'setores/edit',
            [
                'tituloPagina' => 'Editar setor',
                'usuario' => $usuario,
                'setor' => $setor,

                'errosFormulario' =>
                    is_array($errosFormulario)
                        ? $errosFormulario
                        : [],

                'erro' => obterFlash('erro'),
            ],
            'layouts/main'
        );
    }

    /**
     * Processa a atualização do setor.
     */
    public function atualizar(): void
    {
        if (!csrfValido($_POST['csrf_token'] ?? null)) {
            definirFlash(
                'erro',
                'A solicitação expirou. Tente novamente.'
            );

            redirecionar('setores');
        }

        $usuario = usuarioAutenticado();

        if (!$usuario) {
            redirecionar('login');
        }

        $setorId = filter_var(
            $_POST['setor_id'] ?? null,
            FILTER_VALIDATE_INT
        );

        if (!$setorId || $setorId <= 0) {
            definirFlash(
                'erro',
                'O setor informado é inválido.'
            );

            redirecionar('setores');
        }

        $organizacaoId = (int) (
            $usuario['organizacao_id']
            ?? 0
        );

        try {
            $resultado =
                $this->setorService->atualizar(
                    $setorId,
                    $organizacaoId,
                    $_POST
                );

            if (!$resultado['sucesso']) {
                $_SESSION['setor_formulario'] =
                    $resultado['dados'] ?? $_POST;

                $_SESSION['setor_erros'] =
                    $resultado['erros'] ?? [];

                definirFlash(
                    'erro',
                    (string) (
                        $resultado['mensagem']
                        ?? 'Verifique os dados informados.'
                    )
                );

                redirecionar(
                    'setores/editar?id=' . $setorId
                );
            }

            definirFlash(
                'sucesso',
                (string) $resultado['mensagem']
            );

            redirecionar('setores');
        } catch (Throwable $erro) {
            $_SESSION['setor_formulario'] = $_POST;

            definirFlash(
                'erro',
                env('APP_DEBUG', false)
                    ? $erro->getMessage()
                    : 'Não foi possível atualizar o setor.'
            );

            redirecionar(
                'setores/editar?id=' . $setorId
            );
        }
    }

    /**
     * Ativa ou desativa um setor.
     */
    public function alterarStatus(): void
    {
        if (!csrfValido($_POST['csrf_token'] ?? null)) {
            definirFlash(
                'erro',
                'A solicitação expirou. Tente novamente.'
            );

            redirecionar('setores');
        }

        $usuario = usuarioAutenticado();

        if (!$usuario) {
            redirecionar('login');
        }

        $setorId = filter_var(
            $_POST['setor_id'] ?? null,
            FILTER_VALIDATE_INT
        );

        if (!$setorId || $setorId <= 0) {
            definirFlash(
                'erro',
                'O setor informado é inválido.'
            );

            redirecionar('setores');
        }

        $organizacaoId = (int) (
            $usuario['organizacao_id']
            ?? 0
        );

        try {
            $resultado =
                $this->setorService->alterarStatus(
                    $setorId,
                    $organizacaoId
                );

            definirFlash(
                $resultado['sucesso']
                    ? 'sucesso'
                    : 'erro',
                (string) $resultado['mensagem']
            );
        } catch (Throwable $erro) {
            definirFlash(
                'erro',
                env('APP_DEBUG', false)
                    ? $erro->getMessage()
                    : 'Não foi possível alterar a situação do setor.'
            );
        }

        redirecionar('setores');
    }
}