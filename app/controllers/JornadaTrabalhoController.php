<?php

declare(strict_types=1);

require_once BASE_PATH . '/app/helpers/url.php';
require_once BASE_PATH . '/app/helpers/view.php';
require_once BASE_PATH . '/app/helpers/session.php';

class JornadaTrabalhoController
{
    public function __construct(
        private JornadaTrabalhoService $jornadaTrabalhoService
    ) {
    }

    /**
     * Exibe a listagem de jornadas.
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
            $jornadas =
                $this->jornadaTrabalhoService->listar(
                    $organizacaoId,
                    $busca,
                    $situacao
                );

            renderizarView(
                'jornadas/index',
                [
                    'tituloPagina' =>
                        'Jornadas de trabalho',

                    'usuario' =>
                        $usuario,

                    'jornadas' =>
                        $jornadas,

                    'busca' =>
                        $busca,

                    'situacao' =>
                        $situacao,

                    'sucesso' =>
                        obterFlash('sucesso'),

                    'erro' =>
                        obterFlash('erro'),
                ],
                'layouts/main'
            );
        } catch (Throwable $erro) {
            renderizarView(
                'jornadas/index',
                [
                    'tituloPagina' =>
                        'Jornadas de trabalho',

                    'usuario' =>
                        $usuario,

                    'jornadas' =>
                        [],

                    'busca' =>
                        $busca,

                    'situacao' =>
                        $situacao,

                    'sucesso' =>
                        null,

                    'erro' =>
                        env('APP_DEBUG', false)
                            ? $erro->getMessage()
                            : 'Não foi possível carregar as jornadas de trabalho.',
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
            $_SESSION['jornada_formulario']
            ?? [];

        $errosFormulario =
            $_SESSION['jornada_erros']
            ?? [];

        unset(
            $_SESSION['jornada_formulario'],
            $_SESSION['jornada_erros']
        );

        renderizarView(
            'jornadas/create',
            [
                'tituloPagina' =>
                    'Nova jornada de trabalho',

                'usuario' =>
                    $usuario,

                'dadosFormulario' =>
                    is_array($dadosFormulario)
                        ? $dadosFormulario
                        : [],

                'errosFormulario' =>
                    is_array($errosFormulario)
                        ? $errosFormulario
                        : [],

                'erro' =>
                    obterFlash('erro'),
            ],
            'layouts/main'
        );
    }

    /**
     * Processa o cadastro.
     */
    public function salvar(): void
    {
        if (
            !csrfValido(
                $_POST['csrf_token'] ?? null
            )
        ) {
            definirFlash(
                'erro',
                'A solicitação expirou. Tente novamente.'
            );

            redirecionar(
                'jornadas/criar'
            );
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
            $resultado =
                $this->jornadaTrabalhoService->criar(
                    $organizacaoId,
                    $_POST
                );

            if (!$resultado['sucesso']) {
                $_SESSION[
                    'jornada_formulario'
                ] =
                    $resultado['dados']
                    ?? $_POST;

                $_SESSION[
                    'jornada_erros'
                ] =
                    $resultado['erros']
                    ?? [];

                definirFlash(
                    'erro',
                    (string) (
                        $resultado['mensagem']
                        ?? 'Verifique os dados informados.'
                    )
                );

                redirecionar(
                    'jornadas/criar'
                );
            }

            definirFlash(
                'sucesso',
                (string) (
                    $resultado['mensagem']
                    ?? 'Jornada cadastrada com sucesso.'
                )
            );

            redirecionar(
                'jornadas'
            );
        } catch (Throwable $erro) {
            $_SESSION[
                'jornada_formulario'
            ] = $_POST;

            definirFlash(
                'erro',
                env('APP_DEBUG', false)
                    ? $erro->getMessage()
                    : 'Não foi possível cadastrar a jornada.'
            );

            redirecionar(
                'jornadas/criar'
            );
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

        $jornadaId = filter_input(
            INPUT_GET,
            'id',
            FILTER_VALIDATE_INT
        );

        if (
            !$jornadaId
            || $jornadaId <= 0
        ) {
            definirFlash(
                'erro',
                'A jornada informada é inválida.'
            );

            redirecionar(
                'jornadas'
            );
        }

        $organizacaoId = (int) (
            $usuario['organizacao_id']
            ?? 0
        );

        $jornada =
            $this->jornadaTrabalhoService->buscarPorId(
                $jornadaId,
                $organizacaoId
            );

        if (!$jornada) {
            definirFlash(
                'erro',
                'A jornada informada não foi encontrada.'
            );

            redirecionar(
                'jornadas'
            );
        }

        $dadosTemporarios =
            $_SESSION['jornada_formulario']
            ?? null;

        $errosFormulario =
            $_SESSION['jornada_erros']
            ?? [];

        unset(
            $_SESSION['jornada_formulario'],
            $_SESSION['jornada_erros']
        );

        if (is_array($dadosTemporarios)) {
            $jornada = array_merge(
                $jornada,
                $dadosTemporarios
            );
        }

        renderizarView(
            'jornadas/edit',
            [
                'tituloPagina' =>
                    'Editar jornada de trabalho',

                'usuario' =>
                    $usuario,

                'jornada' =>
                    $jornada,

                'errosFormulario' =>
                    is_array($errosFormulario)
                        ? $errosFormulario
                        : [],

                'erro' =>
                    obterFlash('erro'),
            ],
            'layouts/main'
        );
    }

    /**
     * Processa a atualização.
     */
    public function atualizar(): void
    {
        if (
            !csrfValido(
                $_POST['csrf_token'] ?? null
            )
        ) {
            definirFlash(
                'erro',
                'A solicitação expirou. Tente novamente.'
            );

            redirecionar(
                'jornadas'
            );
        }

        $usuario = usuarioAutenticado();

        if (!$usuario) {
            redirecionar('login');
        }

        $jornadaId = filter_var(
            $_POST['jornada_id']
            ?? null,
            FILTER_VALIDATE_INT
        );

        if (
            !$jornadaId
            || $jornadaId <= 0
        ) {
            definirFlash(
                'erro',
                'A jornada informada é inválida.'
            );

            redirecionar(
                'jornadas'
            );
        }

        $organizacaoId = (int) (
            $usuario['organizacao_id']
            ?? 0
        );

        try {
            $resultado =
                $this->jornadaTrabalhoService->atualizar(
                    $jornadaId,
                    $organizacaoId,
                    $_POST
                );

            if (!$resultado['sucesso']) {
                $_SESSION[
                    'jornada_formulario'
                ] =
                    $resultado['dados']
                    ?? $_POST;

                $_SESSION[
                    'jornada_erros'
                ] =
                    $resultado['erros']
                    ?? [];

                definirFlash(
                    'erro',
                    (string) (
                        $resultado['mensagem']
                        ?? 'Verifique os dados informados.'
                    )
                );

                redirecionar(
                    'jornadas/editar?id='
                    . $jornadaId
                );
            }

            definirFlash(
                'sucesso',
                (string) (
                    $resultado['mensagem']
                    ?? 'Jornada atualizada com sucesso.'
                )
            );

            redirecionar(
                'jornadas'
            );
        } catch (Throwable $erro) {
            $_SESSION[
                'jornada_formulario'
            ] = $_POST;

            definirFlash(
                'erro',
                env('APP_DEBUG', false)
                    ? $erro->getMessage()
                    : 'Não foi possível atualizar a jornada.'
            );

            redirecionar(
                'jornadas/editar?id='
                . $jornadaId
            );
        }
    }

    /**
     * Ativa ou desativa uma jornada.
     */
    public function alterarStatus(): void
    {
        if (
            !csrfValido(
                $_POST['csrf_token'] ?? null
            )
        ) {
            definirFlash(
                'erro',
                'A solicitação expirou. Tente novamente.'
            );

            redirecionar(
                'jornadas'
            );
        }

        $usuario = usuarioAutenticado();

        if (!$usuario) {
            redirecionar('login');
        }

        $jornadaId = filter_var(
            $_POST['jornada_id']
            ?? null,
            FILTER_VALIDATE_INT
        );

        if (
            !$jornadaId
            || $jornadaId <= 0
        ) {
            definirFlash(
                'erro',
                'A jornada informada é inválida.'
            );

            redirecionar(
                'jornadas'
            );
        }

        $organizacaoId = (int) (
            $usuario['organizacao_id']
            ?? 0
        );

        try {
            $resultado =
                $this->jornadaTrabalhoService->alterarStatus(
                    $jornadaId,
                    $organizacaoId
                );

            definirFlash(
                $resultado['sucesso']
                    ? 'sucesso'
                    : 'erro',
                (string) (
                    $resultado['mensagem']
                    ?? 'Não foi possível alterar a situação.'
                )
            );
        } catch (Throwable $erro) {
            definirFlash(
                'erro',
                env('APP_DEBUG', false)
                    ? $erro->getMessage()
                    : 'Não foi possível alterar a situação da jornada.'
            );
        }

        redirecionar(
            'jornadas'
        );
    }
}