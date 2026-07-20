<?php

declare(strict_types=1);

require_once BASE_PATH . '/app/helpers/url.php';
require_once BASE_PATH . '/app/helpers/view.php';
require_once BASE_PATH . '/app/helpers/session.php';

class TipoVinculoController
{
    public function __construct(
        private TipoVinculoService $tipoVinculoService
    ) {
    }

    /**
     * Exibe a listagem de tipos de vínculo.
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
            $tiposVinculo =
                $this->tipoVinculoService->listar(
                    $organizacaoId,
                    $busca,
                    $situacao
                );

            renderizarView(
                'tipos-vinculo/index',
                [
                    'tituloPagina' =>
                        'Tipos de vínculo',

                    'usuario' =>
                        $usuario,

                    'tiposVinculo' =>
                        $tiposVinculo,

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
                'tipos-vinculo/index',
                [
                    'tituloPagina' =>
                        'Tipos de vínculo',

                    'usuario' =>
                        $usuario,

                    'tiposVinculo' =>
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
                            : 'Não foi possível carregar os tipos de vínculo.',
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
            $_SESSION['tipo_vinculo_formulario']
            ?? [];

        $errosFormulario =
            $_SESSION['tipo_vinculo_erros']
            ?? [];

        unset(
            $_SESSION['tipo_vinculo_formulario'],
            $_SESSION['tipo_vinculo_erros']
        );

        renderizarView(
            'tipos-vinculo/create',
            [
                'tituloPagina' =>
                    'Novo tipo de vínculo',

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
                'tipos-vinculo/criar'
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
                $this->tipoVinculoService->criar(
                    $organizacaoId,
                    $_POST
                );

            if (!$resultado['sucesso']) {
                $_SESSION[
                    'tipo_vinculo_formulario'
                ] =
                    $resultado['dados']
                    ?? $_POST;

                $_SESSION[
                    'tipo_vinculo_erros'
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
                    'tipos-vinculo/criar'
                );
            }

            definirFlash(
                'sucesso',
                (string) (
                    $resultado['mensagem']
                    ?? 'Tipo de vínculo cadastrado com sucesso.'
                )
            );

            redirecionar(
                'tipos-vinculo'
            );
        } catch (Throwable $erro) {
            $_SESSION[
                'tipo_vinculo_formulario'
            ] = $_POST;

            definirFlash(
                'erro',
                env('APP_DEBUG', false)
                    ? $erro->getMessage()
                    : 'Não foi possível cadastrar o tipo de vínculo.'
            );

            redirecionar(
                'tipos-vinculo/criar'
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

        $tipoVinculoId = filter_input(
            INPUT_GET,
            'id',
            FILTER_VALIDATE_INT
        );

        if (
            !$tipoVinculoId
            || $tipoVinculoId <= 0
        ) {
            definirFlash(
                'erro',
                'O tipo de vínculo informado é inválido.'
            );

            redirecionar(
                'tipos-vinculo'
            );
        }

        $organizacaoId = (int) (
            $usuario['organizacao_id']
            ?? 0
        );

        $tipoVinculo =
            $this->tipoVinculoService->buscarPorId(
                $tipoVinculoId,
                $organizacaoId
            );

        if (!$tipoVinculo) {
            definirFlash(
                'erro',
                'O tipo de vínculo informado não foi encontrado.'
            );

            redirecionar(
                'tipos-vinculo'
            );
        }

        $dadosTemporarios =
            $_SESSION['tipo_vinculo_formulario']
            ?? null;

        $errosFormulario =
            $_SESSION['tipo_vinculo_erros']
            ?? [];

        unset(
            $_SESSION['tipo_vinculo_formulario'],
            $_SESSION['tipo_vinculo_erros']
        );

        if (is_array($dadosTemporarios)) {
            $tipoVinculo = array_merge(
                $tipoVinculo,
                $dadosTemporarios
            );
        }

        renderizarView(
            'tipos-vinculo/edit',
            [
                'tituloPagina' =>
                    'Editar tipo de vínculo',

                'usuario' =>
                    $usuario,

                'tipoVinculo' =>
                    $tipoVinculo,

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
                'tipos-vinculo'
            );
        }

        $usuario = usuarioAutenticado();

        if (!$usuario) {
            redirecionar('login');
        }

        $tipoVinculoId = filter_var(
            $_POST['tipo_vinculo_id']
            ?? null,
            FILTER_VALIDATE_INT
        );

        if (
            !$tipoVinculoId
            || $tipoVinculoId <= 0
        ) {
            definirFlash(
                'erro',
                'O tipo de vínculo informado é inválido.'
            );

            redirecionar(
                'tipos-vinculo'
            );
        }

        $organizacaoId = (int) (
            $usuario['organizacao_id']
            ?? 0
        );

        try {
            $resultado =
                $this->tipoVinculoService->atualizar(
                    $tipoVinculoId,
                    $organizacaoId,
                    $_POST
                );

            if (!$resultado['sucesso']) {
                $_SESSION[
                    'tipo_vinculo_formulario'
                ] =
                    $resultado['dados']
                    ?? $_POST;

                $_SESSION[
                    'tipo_vinculo_erros'
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
                    'tipos-vinculo/editar?id='
                    . $tipoVinculoId
                );
            }

            definirFlash(
                'sucesso',
                (string) (
                    $resultado['mensagem']
                    ?? 'Tipo de vínculo atualizado com sucesso.'
                )
            );

            redirecionar(
                'tipos-vinculo'
            );
        } catch (Throwable $erro) {
            $_SESSION[
                'tipo_vinculo_formulario'
            ] = $_POST;

            definirFlash(
                'erro',
                env('APP_DEBUG', false)
                    ? $erro->getMessage()
                    : 'Não foi possível atualizar o tipo de vínculo.'
            );

            redirecionar(
                'tipos-vinculo/editar?id='
                . $tipoVinculoId
            );
        }
    }

    /**
     * Ativa ou desativa um tipo de vínculo.
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
                'tipos-vinculo'
            );
        }

        $usuario = usuarioAutenticado();

        if (!$usuario) {
            redirecionar('login');
        }

        $tipoVinculoId = filter_var(
            $_POST['tipo_vinculo_id']
            ?? null,
            FILTER_VALIDATE_INT
        );

        if (
            !$tipoVinculoId
            || $tipoVinculoId <= 0
        ) {
            definirFlash(
                'erro',
                'O tipo de vínculo informado é inválido.'
            );

            redirecionar(
                'tipos-vinculo'
            );
        }

        $organizacaoId = (int) (
            $usuario['organizacao_id']
            ?? 0
        );

        try {
            $resultado =
                $this->tipoVinculoService->alterarStatus(
                    $tipoVinculoId,
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
                    : 'Não foi possível alterar a situação do tipo de vínculo.'
            );
        }

        redirecionar(
            'tipos-vinculo'
        );
    }
}