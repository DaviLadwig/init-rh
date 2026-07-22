<?php

declare(strict_types=1);

/**
 * Controller responsável pelo módulo de Colaboradores.
 *
 * Responsabilidades principais:
 * - listar colaboradores;
 * - abrir os formulários de cadastro e edição;
 * - salvar e atualizar registros;
 * - visualizar o perfil individual;
 * - ativar e desativar colaboradores;
 * - carregar as opções dos selects funcionais;
 * - identificar internamente a unidade principal;
 * - validar o token CSRF recebido pelos formulários.
 */
class ColaboradorController
{
    public function __construct(
        private ColaboradorService $colaboradorService,
        private SetorRepository $setorRepository,
        private CargoRepository $cargoRepository,
        private FuncaoRepository $funcaoRepository,
        private TipoVinculoRepository $tipoVinculoRepository,
        private JornadaTrabalhoRepository $jornadaTrabalhoRepository,
        private PDO $pdo
    ) {
    }

    /**
     * Exibe a listagem dos colaboradores.
     */
    public function index(): void
    {
        $organizacaoId =
            $this->obterOrganizacaoId();

        if ($organizacaoId <= 0) {
            definirFlash(
                'erro',
                'A organização do usuário não foi identificada.'
            );

            redirecionar('dashboard');
        }

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

        $colaboradores =
            $this->colaboradorService->listar(
                $organizacaoId,
                $busca,
                $situacao
            );

        renderizarView(
            'colaboradores/index',
            [
                'colaboradores' =>
                    $colaboradores,

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
    }

    /**
     * Exibe o formulário de cadastro.
     */
    public function criar(): void
    {
        $organizacaoId =
            $this->obterOrganizacaoId();

        if ($organizacaoId <= 0) {
            definirFlash(
                'erro',
                'A organização do usuário não foi identificada.'
            );

            redirecionar('colaboradores');
        }

        /*
         * Recupera os valores digitados anteriormente caso o
         * formulário tenha retornado por causa de algum erro.
         */
        $formulario =
            $this->recuperarFormularioDaSessao();

        /*
         * Carrega os dados que alimentarão os selects do formulário.
         */
        $opcoes =
            $this->carregarOpcoes(
                $organizacaoId
            );

        renderizarView(
            'colaboradores/create',
            [
                'dadosFormulario' =>
                    $formulario['dados'],

                'errosFormulario' =>
                    $formulario['erros'],

                'setores' =>
                    $opcoes['setores'],

                'cargos' =>
                    $opcoes['cargos'],

                'funcoes' =>
                    $opcoes['funcoes'],

                'tiposVinculo' =>
                    $opcoes['tipos_vinculo'],

                'jornadas' =>
                    $opcoes['jornadas'],

                'erro' =>
                    obterFlash('erro'),
            ],
            'layouts/main'
        );
    }

    /**
     * Salva um novo colaborador.
     */
    public function salvar(): void
    {
        /*
         * O helper csrfValido() exige que o token recebido seja
         * informado como argumento. O campo é enviado pelo formulário
         * com o nome csrf_token, gerado pela função campoCsrf().
         */
        $csrfToken = $_POST['csrf_token'] ?? null;

        if (
            !csrfValido(
                is_string($csrfToken)
                    ? $csrfToken
                    : null
            )
        ) {
            definirFlash(
                'erro',
                'A sessão expirou. Atualize a página e tente novamente.'
            );

            redirecionar(
                'colaboradores/criar'
            );
        }

        $organizacaoId =
            $this->obterOrganizacaoId();

        /*
         * A unidade não será exibida no formulário nesta primeira versão.
         * O controller identifica internamente a unidade principal ativa.
         */
        $unidadeId =
            $this->obterUnidadePrincipalId(
                $organizacaoId
            );

        $resultado =
            $this->colaboradorService->criar(
                $organizacaoId,
                $unidadeId,
                $_POST
            );

        if (
            !($resultado['sucesso'] ?? false)
        ) {
            /*
             * Mantém os valores digitados e os erros na sessão para
             * reapresentá-los no formulário de cadastro.
             */
            $_SESSION[
                'colaborador_formulario'
            ] = $resultado['dados'] ?? $_POST;

            $_SESSION[
                'colaborador_erros'
            ] = $resultado['erros'] ?? [];

            definirFlash(
                'erro',
                (string) (
                    $resultado['mensagem']
                    ?? 'Não foi possível cadastrar o colaborador.'
                )
            );

            redirecionar(
                'colaboradores/criar'
            );
        }

        definirFlash(
            'sucesso',
            (string) (
                $resultado['mensagem']
                ?? 'Colaborador cadastrado com sucesso.'
            )
        );

        redirecionar('colaboradores');
    }

    /**
     * Exibe os detalhes de um colaborador.
     */
    public function visualizar(): void
    {
        $organizacaoId =
            $this->obterOrganizacaoId();

        $colaboradorId = filter_input(
            INPUT_GET,
            'id',
            FILTER_VALIDATE_INT
        );

        if (!$colaboradorId) {
            definirFlash(
                'erro',
                'O colaborador informado é inválido.'
            );

            redirecionar('colaboradores');
        }

        $colaborador =
            $this->colaboradorService->buscarPorId(
                (int) $colaboradorId,
                $organizacaoId
            );

        if (!$colaborador) {
            definirFlash(
                'erro',
                'O colaborador não foi encontrado.'
            );

            redirecionar('colaboradores');
        }

        renderizarView(
            'colaboradores/show',
            [
                'colaborador' =>
                    $colaborador,

                'sucesso' =>
                    obterFlash('sucesso'),

                'erro' =>
                    obterFlash('erro'),
            ],
            'layouts/main'
        );
    }

    /**
     * Exibe o formulário de edição.
     */
    public function editar(): void
    {
        $organizacaoId =
            $this->obterOrganizacaoId();

        $colaboradorId = filter_input(
            INPUT_GET,
            'id',
            FILTER_VALIDATE_INT
        );

        if (!$colaboradorId) {
            definirFlash(
                'erro',
                'O colaborador informado é inválido.'
            );

            redirecionar('colaboradores');
        }

        $colaboradorAtual =
            $this->colaboradorService->buscarPorId(
                (int) $colaboradorId,
                $organizacaoId
            );

        if (!$colaboradorAtual) {
            definirFlash(
                'erro',
                'O colaborador não foi encontrado.'
            );

            redirecionar('colaboradores');
        }

        $formulario =
            $this->recuperarFormularioDaSessao();

        $colaborador =
            $colaboradorAtual;

        /*
         * Caso a atualização tenha retornado com erros, mantém os
         * valores que o usuário digitou em vez de restaurar os valores
         * antigos do banco de dados.
         */
        if ($formulario['dados'] !== []) {
            $colaborador = array_merge(
                $colaboradorAtual,
                $formulario['dados']
            );

            $colaborador['id'] =
                $colaboradorAtual['id'];
        }

        /*
         * Carrega registros ativos e também preserva nos selects algum
         * registro inativo que já esteja relacionado ao colaborador.
         */
        $opcoes =
            $this->carregarOpcoes(
                $organizacaoId,
                $colaboradorAtual
            );

        renderizarView(
            'colaboradores/edit',
            [
                'colaborador' =>
                    $colaborador,

                'errosFormulario' =>
                    $formulario['erros'],

                'setores' =>
                    $opcoes['setores'],

                'cargos' =>
                    $opcoes['cargos'],

                'funcoes' =>
                    $opcoes['funcoes'],

                'tiposVinculo' =>
                    $opcoes['tipos_vinculo'],

                'jornadas' =>
                    $opcoes['jornadas'],

                'erro' =>
                    obterFlash('erro'),
            ],
            'layouts/main'
        );
    }

    /**
     * Atualiza um colaborador existente.
     */
    public function atualizar(): void
    {
        $colaboradorId = filter_input(
            INPUT_POST,
            'colaborador_id',
            FILTER_VALIDATE_INT
        );

        if (!$colaboradorId) {
            definirFlash(
                'erro',
                'O colaborador informado é inválido.'
            );

            redirecionar('colaboradores');
        }

        /*
         * Valida o token CSRF enviado pelo formulário de edição.
         */
        $csrfToken = $_POST['csrf_token'] ?? null;

        if (
            !csrfValido(
                is_string($csrfToken)
                    ? $csrfToken
                    : null
            )
        ) {
            definirFlash(
                'erro',
                'A sessão expirou. Atualize a página e tente novamente.'
            );

            redirecionar(
                'colaboradores/editar?id='
                . (int) $colaboradorId
            );
        }

        $organizacaoId =
            $this->obterOrganizacaoId();

        $unidadeId =
            $this->obterUnidadePrincipalId(
                $organizacaoId
            );

        $resultado =
            $this->colaboradorService->atualizar(
                (int) $colaboradorId,
                $organizacaoId,
                $unidadeId,
                $_POST
            );

        if (
            !($resultado['sucesso'] ?? false)
        ) {
            /*
             * Mantém os valores e mensagens de validação para que o
             * usuário não precise preencher todo o formulário novamente.
             */
            $_SESSION[
                'colaborador_formulario'
            ] = $resultado['dados'] ?? $_POST;

            $_SESSION[
                'colaborador_erros'
            ] = $resultado['erros'] ?? [];

            definirFlash(
                'erro',
                (string) (
                    $resultado['mensagem']
                    ?? 'Não foi possível atualizar o colaborador.'
                )
            );

            redirecionar(
                'colaboradores/editar?id='
                . (int) $colaboradorId
            );
        }

        definirFlash(
            'sucesso',
            (string) (
                $resultado['mensagem']
                ?? 'Colaborador atualizado com sucesso.'
            )
        );

        redirecionar(
            'colaboradores/visualizar?id='
            . (int) $colaboradorId
        );
    }

    /**
     * Ativa ou desativa um colaborador.
     */
    public function alterarStatus(): void
    {
        $colaboradorId = filter_input(
            INPUT_POST,
            'colaborador_id',
            FILTER_VALIDATE_INT
        );

        if (!$colaboradorId) {
            definirFlash(
                'erro',
                'O colaborador informado é inválido.'
            );

            redirecionar('colaboradores');
        }

        /*
         * Valida o token CSRF enviado pelo formulário de alteração
         * de status exibido na listagem.
         */
        $csrfToken = $_POST['csrf_token'] ?? null;

        if (
            !csrfValido(
                is_string($csrfToken)
                    ? $csrfToken
                    : null
            )
        ) {
            definirFlash(
                'erro',
                'A sessão expirou. Atualize a página e tente novamente.'
            );

            redirecionar('colaboradores');
        }

        $organizacaoId =
            $this->obterOrganizacaoId();

        $resultado =
            $this->colaboradorService->alterarStatus(
                (int) $colaboradorId,
                $organizacaoId
            );

        $tipoFlash =
            ($resultado['sucesso'] ?? false)
                ? 'sucesso'
                : 'erro';

        definirFlash(
            $tipoFlash,
            (string) (
                $resultado['mensagem']
                ?? 'Não foi possível alterar a situação do colaborador.'
            )
        );

        redirecionar('colaboradores');
    }

    /**
     * Obtém a organização vinculada ao usuário autenticado.
     */
    private function obterOrganizacaoId(): int
    {
        $usuario =
            usuarioAutenticado();

        if (!is_array($usuario)) {
            return 0;
        }

        return (int) (
            $usuario['organizacao_id']
            ?? 0
        );
    }

    /**
     * Obtém a unidade principal ativa da organização.
     *
     * Nesta primeira versão existe somente uma unidade operacional e
     * esse conceito não será exposto no formulário de colaboradores.
     */
    private function obterUnidadePrincipalId(
        int $organizacaoId
    ): int {
        if ($organizacaoId <= 0) {
            return 0;
        }

        $sql = '
            SELECT id
            FROM unidades
            WHERE organizacao_id =
                :organizacao_id

              AND ativo = TRUE

            ORDER BY id ASC
            LIMIT 1
        ';

        $stmt = $this->pdo->prepare($sql);

        $stmt->bindValue(
            ':organizacao_id',
            $organizacaoId,
            PDO::PARAM_INT
        );

        $stmt->execute();

        return (int) (
            $stmt->fetchColumn()
            ?: 0
        );
    }

    /**
     * Carrega as opções utilizadas nos selects funcionais.
     *
     * No cadastro, somente registros ativos são exibidos.
     * Na edição, o registro atualmente vinculado também é incluído,
     * mesmo que tenha sido desativado depois do cadastro.
     */
    private function carregarOpcoes(
        int $organizacaoId,
        ?array $colaboradorAtual = null
    ): array {
        $setores =
            $this->setorRepository->listar(
                $organizacaoId,
                '',
                'ATIVOS'
            );

        $cargos =
            $this->cargoRepository->listar(
                $organizacaoId,
                '',
                'ATIVOS'
            );

        $funcoes =
            $this->funcaoRepository->listar(
                $organizacaoId,
                '',
                'ATIVOS'
            );

        $tiposVinculo =
            $this->tipoVinculoRepository->listar(
                $organizacaoId,
                '',
                'ATIVOS'
            );

        $jornadas =
            $this->jornadaTrabalhoRepository->listar(
                $organizacaoId,
                '',
                'ATIVOS'
            );

        if ($colaboradorAtual !== null) {
            $setores =
                $this->adicionarRegistroAtual(
                    $setores,
                    $this->buscarRegistroAtual(
                        $this->setorRepository,
                        (int) (
                            $colaboradorAtual[
                                'setor_id'
                            ] ?? 0
                        ),
                        $organizacaoId
                    )
                );

            $cargos =
                $this->adicionarRegistroAtual(
                    $cargos,
                    $this->buscarRegistroAtual(
                        $this->cargoRepository,
                        (int) (
                            $colaboradorAtual[
                                'cargo_id'
                            ] ?? 0
                        ),
                        $organizacaoId
                    )
                );

            $funcoes =
                $this->adicionarRegistroAtual(
                    $funcoes,
                    $this->buscarRegistroAtual(
                        $this->funcaoRepository,
                        (int) (
                            $colaboradorAtual[
                                'funcao_id'
                            ] ?? 0
                        ),
                        $organizacaoId
                    )
                );

            $tiposVinculo =
                $this->adicionarRegistroAtual(
                    $tiposVinculo,
                    $this->buscarRegistroAtual(
                        $this->tipoVinculoRepository,
                        (int) (
                            $colaboradorAtual[
                                'tipo_vinculo_id'
                            ] ?? 0
                        ),
                        $organizacaoId
                    )
                );

            $jornadas =
                $this->adicionarRegistroAtual(
                    $jornadas,
                    $this->buscarRegistroAtual(
                        $this->jornadaTrabalhoRepository,
                        (int) (
                            $colaboradorAtual[
                                'jornada_trabalho_id'
                            ] ?? 0
                        ),
                        $organizacaoId
                    )
                );
        }

        return [
            'setores' =>
                $setores,

            'cargos' =>
                $cargos,

            'funcoes' =>
                $funcoes,

            'tipos_vinculo' =>
                $tiposVinculo,

            'jornadas' =>
                $jornadas,
        ];
    }

    /**
     * Busca um registro atualmente vinculado em qualquer repository
     * que possua o método buscarPorId().
     */
    private function buscarRegistroAtual(
        object $repository,
        int $registroId,
        int $organizacaoId
    ): ?array {
        if ($registroId <= 0) {
            return null;
        }

        if (
            !method_exists(
                $repository,
                'buscarPorId'
            )
        ) {
            return null;
        }

        $registro =
            $repository->buscarPorId(
                $registroId,
                $organizacaoId
            );

        return is_array($registro)
            ? $registro
            : null;
    }

    /**
     * Adiciona à lista um registro atualmente vinculado que esteja
     * inativo, evitando opções duplicadas no select.
     */
    private function adicionarRegistroAtual(
        array $registros,
        ?array $registroAtual
    ): array {
        if ($registroAtual === null) {
            return $registros;
        }

        $registroAtualId = (int) (
            $registroAtual['id']
            ?? 0
        );

        if ($registroAtualId <= 0) {
            return $registros;
        }

        foreach ($registros as $registro) {
            if (
                (int) ($registro['id'] ?? 0)
                === $registroAtualId
            ) {
                return $registros;
            }
        }

        $registros[] =
            $registroAtual;

        usort(
            $registros,
            static function (
                array $primeiro,
                array $segundo
            ): int {
                return strcasecmp(
                    (string) (
                        $primeiro['nome']
                        ?? ''
                    ),
                    (string) (
                        $segundo['nome']
                        ?? ''
                    )
                );
            }
        );

        return $registros;
    }

    /**
     * Recupera os dados temporários armazenados após uma falha de
     * validação e remove esses dados da sessão depois da leitura.
     */
    private function recuperarFormularioDaSessao(): array
    {
        $dados =
            $_SESSION[
                'colaborador_formulario'
            ] ?? [];

        $erros =
            $_SESSION[
                'colaborador_erros'
            ] ?? [];

        unset(
            $_SESSION[
                'colaborador_formulario'
            ],
            $_SESSION[
                'colaborador_erros'
            ]
        );

        return [
            'dados' => is_array($dados)
                ? $dados
                : [],

            'erros' => is_array($erros)
                ? $erros
                : [],
        ];
    }
}