<?php

declare(strict_types=1);

require_once BASE_PATH . '/app/helpers/url.php';
require_once BASE_PATH . '/app/helpers/view.php';
require_once BASE_PATH . '/app/helpers/session.php';

class FuncaoController
{
    public function __construct(
        private FuncaoService $funcaoService
    ) {
    }

    /**
     * Exibe a listagem de funções.
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
            $funcoes = $this->funcaoService->listar(
                $organizacaoId,
                $busca,
                $situacao
            );

            renderizarView(
                'funcoes/index',
                [
                    'tituloPagina' => 'Funções',
                    'usuario' => $usuario,
                    'funcoes' => $funcoes,
                    'busca' => $busca,
                    'situacao' => $situacao,
                    'sucesso' => obterFlash('sucesso'),
                    'erro' => obterFlash('erro'),
                ],
                'layouts/main'
            );
        } catch (Throwable $erro) {
            renderizarView(
                'funcoes/index',
                [
                    'tituloPagina' => 'Funções',
                    'usuario' => $usuario,
                    'funcoes' => [],
                    'busca' => $busca,
                    'situacao' => $situacao,
                    'sucesso' => null,

                    'erro' => env('APP_DEBUG', false)
                        ? $erro->getMessage()
                        : 'Não foi possível carregar as funções.',
                ],
                'layouts/main'
            );
        }
    }

    /**
     * Exibe o formulário de cadastro.
     */
    public function criar(): void
    {
        $usuario = usuarioAutenticado();

        if (!$usuario) {
            redirecionar('login');
        }

        $dadosFormulario =
            $_SESSION['funcao_formulario'] ?? [];

        $errosFormulario =
            $_SESSION['funcao_erros'] ?? [];

        unset(
            $_SESSION['funcao_formulario'],
            $_SESSION['funcao_erros']
        );

        renderizarView(
            'funcoes/create',
            [
                'tituloPagina' => 'Nova função',
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
     * Processa o cadastro.
     */
    public function salvar(): void
    {
        if (!csrfValido($_POST['csrf_token'] ?? null)) {
            definirFlash(
                'erro',
                'A solicitação expirou. Tente novamente.'
            );

            redirecionar('funcoes/criar');
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
            $resultado = $this->funcaoService->criar(
                $organizacaoId,
                $_POST
            );

            if (!$resultado['sucesso']) {
                $_SESSION['funcao_formulario'] =
                    $resultado['dados'] ?? $_POST;

                $_SESSION['funcao_erros'] =
                    $resultado['erros'] ?? [];

                definirFlash(
                    'erro',
                    (string) (
                        $resultado['mensagem']
                        ?? 'Verifique os dados informados.'
                    )
                );

                redirecionar('funcoes/criar');
            }

            definirFlash(
                'sucesso',
                (string) $resultado['mensagem']
            );

            redirecionar('funcoes');
        } catch (Throwable $erro) {
            $_SESSION['funcao_formulario'] = $_POST;

            definirFlash(
                'erro',
                env('APP_DEBUG', false)
                    ? $erro->getMessage()
                    : 'Não foi possível cadastrar a função.'
            );

            redirecionar('funcoes/criar');
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

        $funcaoId = filter_input(
            INPUT_GET,
            'id',
            FILTER_VALIDATE_INT
        );

        if (!$funcaoId || $funcaoId <= 0) {
            definirFlash(
                'erro',
                'A função informada é inválida.'
            );

            redirecionar('funcoes');
        }

        $organizacaoId = (int) (
            $usuario['organizacao_id']
            ?? 0
        );

        $funcao = $this->funcaoService->buscarPorId(
            $funcaoId,
            $organizacaoId
        );

        if (!$funcao) {
            definirFlash(
                'erro',
                'A função informada não foi encontrada.'
            );

            redirecionar('funcoes');
        }

        $dadosTemporarios =
            $_SESSION['funcao_formulario'] ?? null;

        $errosFormulario =
            $_SESSION['funcao_erros'] ?? [];

        unset(
            $_SESSION['funcao_formulario'],
            $_SESSION['funcao_erros']
        );

        if (is_array($dadosTemporarios)) {
            $funcao = array_merge(
                $funcao,
                $dadosTemporarios
            );
        }

        renderizarView(
            'funcoes/edit',
            [
                'tituloPagina' => 'Editar função',
                'usuario' => $usuario,
                'funcao' => $funcao,

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
     * Processa a atualização.
     */
    public function atualizar(): void
    {
        if (!csrfValido($_POST['csrf_token'] ?? null)) {
            definirFlash(
                'erro',
                'A solicitação expirou. Tente novamente.'
            );

            redirecionar('funcoes');
        }

        $usuario = usuarioAutenticado();

        if (!$usuario) {
            redirecionar('login');
        }

        $funcaoId = filter_var(
            $_POST['funcao_id'] ?? null,
            FILTER_VALIDATE_INT
        );

        if (!$funcaoId || $funcaoId <= 0) {
            definirFlash(
                'erro',
                'A função informada é inválida.'
            );

            redirecionar('funcoes');
        }

        $organizacaoId = (int) (
            $usuario['organizacao_id']
            ?? 0
        );

        try {
            $resultado =
                $this->funcaoService->atualizar(
                    $funcaoId,
                    $organizacaoId,
                    $_POST
                );

            if (!$resultado['sucesso']) {
                $_SESSION['funcao_formulario'] =
                    $resultado['dados'] ?? $_POST;

                $_SESSION['funcao_erros'] =
                    $resultado['erros'] ?? [];

                definirFlash(
                    'erro',
                    (string) (
                        $resultado['mensagem']
                        ?? 'Verifique os dados informados.'
                    )
                );

                redirecionar(
                    'funcoes/editar?id=' . $funcaoId
                );
            }

            definirFlash(
                'sucesso',
                (string) $resultado['mensagem']
            );

            redirecionar('funcoes');
        } catch (Throwable $erro) {
            $_SESSION['funcao_formulario'] = $_POST;

            definirFlash(
                'erro',
                env('APP_DEBUG', false)
                    ? $erro->getMessage()
                    : 'Não foi possível atualizar a função.'
            );

            redirecionar(
                'funcoes/editar?id=' . $funcaoId
            );
        }
    }

    /**
     * Ativa ou desativa uma função.
     */
    public function alterarStatus(): void
    {
        if (!csrfValido($_POST['csrf_token'] ?? null)) {
            definirFlash(
                'erro',
                'A solicitação expirou. Tente novamente.'
            );

            redirecionar('funcoes');
        }

        $usuario = usuarioAutenticado();

        if (!$usuario) {
            redirecionar('login');
        }

        $funcaoId = filter_var(
            $_POST['funcao_id'] ?? null,
            FILTER_VALIDATE_INT
        );

        if (!$funcaoId || $funcaoId <= 0) {
            definirFlash(
                'erro',
                'A função informada é inválida.'
            );

            redirecionar('funcoes');
        }

        $organizacaoId = (int) (
            $usuario['organizacao_id']
            ?? 0
        );

        try {
            $resultado =
                $this->funcaoService->alterarStatus(
                    $funcaoId,
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
                    : 'Não foi possível alterar a situação da função.'
            );
        }

        redirecionar('funcoes');
    }
}