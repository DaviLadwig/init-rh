<?php

declare(strict_types=1);

require_once BASE_PATH . '/app/helpers/url.php';
require_once BASE_PATH . '/app/helpers/view.php';
require_once BASE_PATH . '/app/helpers/session.php';

class CargoController
{
    public function __construct(
        private CargoService $cargoService
    ) {
    }

    /**
     * Exibe a listagem de cargos.
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
            $cargos = $this->cargoService->listar(
                $organizacaoId,
                $busca,
                $situacao
            );

            renderizarView(
                'cargos/index',
                [
                    'tituloPagina' => 'Cargos',
                    'usuario' => $usuario,
                    'cargos' => $cargos,
                    'busca' => $busca,
                    'situacao' => $situacao,
                    'sucesso' => obterFlash('sucesso'),
                    'erro' => obterFlash('erro'),
                ],
                'layouts/main'
            );
        } catch (Throwable $erro) {
            renderizarView(
                'cargos/index',
                [
                    'tituloPagina' => 'Cargos',
                    'usuario' => $usuario,
                    'cargos' => [],
                    'busca' => $busca,
                    'situacao' => $situacao,
                    'sucesso' => null,

                    'erro' => env('APP_DEBUG', false)
                        ? $erro->getMessage()
                        : 'Não foi possível carregar os cargos.',
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
            $_SESSION['cargo_formulario'] ?? [];

        $errosFormulario =
            $_SESSION['cargo_erros'] ?? [];

        unset(
            $_SESSION['cargo_formulario'],
            $_SESSION['cargo_erros']
        );

        renderizarView(
            'cargos/create',
            [
                'tituloPagina' => 'Novo cargo',
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

            redirecionar('cargos/criar');
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
            $resultado = $this->cargoService->criar(
                $organizacaoId,
                $_POST
            );

            if (!$resultado['sucesso']) {
                $_SESSION['cargo_formulario'] =
                    $resultado['dados'] ?? $_POST;

                $_SESSION['cargo_erros'] =
                    $resultado['erros'] ?? [];

                definirFlash(
                    'erro',
                    (string) (
                        $resultado['mensagem']
                        ?? 'Verifique os dados informados.'
                    )
                );

                redirecionar('cargos/criar');
            }

            definirFlash(
                'sucesso',
                (string) $resultado['mensagem']
            );

            redirecionar('cargos');
        } catch (Throwable $erro) {
            $_SESSION['cargo_formulario'] = $_POST;

            definirFlash(
                'erro',
                env('APP_DEBUG', false)
                    ? $erro->getMessage()
                    : 'Não foi possível cadastrar o cargo.'
            );

            redirecionar('cargos/criar');
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

        $cargoId = filter_input(
            INPUT_GET,
            'id',
            FILTER_VALIDATE_INT
        );

        if (!$cargoId || $cargoId <= 0) {
            definirFlash(
                'erro',
                'O cargo informado é inválido.'
            );

            redirecionar('cargos');
        }

        $organizacaoId = (int) (
            $usuario['organizacao_id']
            ?? 0
        );

        $cargo = $this->cargoService->buscarPorId(
            $cargoId,
            $organizacaoId
        );

        if (!$cargo) {
            definirFlash(
                'erro',
                'O cargo informado não foi encontrado.'
            );

            redirecionar('cargos');
        }

        $dadosTemporarios =
            $_SESSION['cargo_formulario'] ?? null;

        $errosFormulario =
            $_SESSION['cargo_erros'] ?? [];

        unset(
            $_SESSION['cargo_formulario'],
            $_SESSION['cargo_erros']
        );

        if (is_array($dadosTemporarios)) {
            $cargo = array_merge(
                $cargo,
                $dadosTemporarios
            );
        }

        renderizarView(
            'cargos/edit',
            [
                'tituloPagina' => 'Editar cargo',
                'usuario' => $usuario,
                'cargo' => $cargo,

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

            redirecionar('cargos');
        }

        $usuario = usuarioAutenticado();

        if (!$usuario) {
            redirecionar('login');
        }

        $cargoId = filter_var(
            $_POST['cargo_id'] ?? null,
            FILTER_VALIDATE_INT
        );

        if (!$cargoId || $cargoId <= 0) {
            definirFlash(
                'erro',
                'O cargo informado é inválido.'
            );

            redirecionar('cargos');
        }

        $organizacaoId = (int) (
            $usuario['organizacao_id']
            ?? 0
        );

        try {
            $resultado =
                $this->cargoService->atualizar(
                    $cargoId,
                    $organizacaoId,
                    $_POST
                );

            if (!$resultado['sucesso']) {
                $_SESSION['cargo_formulario'] =
                    $resultado['dados'] ?? $_POST;

                $_SESSION['cargo_erros'] =
                    $resultado['erros'] ?? [];

                definirFlash(
                    'erro',
                    (string) (
                        $resultado['mensagem']
                        ?? 'Verifique os dados informados.'
                    )
                );

                redirecionar(
                    'cargos/editar?id=' . $cargoId
                );
            }

            definirFlash(
                'sucesso',
                (string) $resultado['mensagem']
            );

            redirecionar('cargos');
        } catch (Throwable $erro) {
            $_SESSION['cargo_formulario'] = $_POST;

            definirFlash(
                'erro',
                env('APP_DEBUG', false)
                    ? $erro->getMessage()
                    : 'Não foi possível atualizar o cargo.'
            );

            redirecionar(
                'cargos/editar?id=' . $cargoId
            );
        }
    }

    /**
     * Ativa ou desativa um cargo.
     */
    public function alterarStatus(): void
    {
        if (!csrfValido($_POST['csrf_token'] ?? null)) {
            definirFlash(
                'erro',
                'A solicitação expirou. Tente novamente.'
            );

            redirecionar('cargos');
        }

        $usuario = usuarioAutenticado();

        if (!$usuario) {
            redirecionar('login');
        }

        $cargoId = filter_var(
            $_POST['cargo_id'] ?? null,
            FILTER_VALIDATE_INT
        );

        if (!$cargoId || $cargoId <= 0) {
            definirFlash(
                'erro',
                'O cargo informado é inválido.'
            );

            redirecionar('cargos');
        }

        $organizacaoId = (int) (
            $usuario['organizacao_id']
            ?? 0
        );

        try {
            $resultado =
                $this->cargoService->alterarStatus(
                    $cargoId,
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
                    : 'Não foi possível alterar a situação do cargo.'
            );
        }

        redirecionar('cargos');
    }
}