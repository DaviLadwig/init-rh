<?php

declare(strict_types=1);

class ColaboradorService
{
    public function __construct(
        private ColaboradorRepository $colaboradorRepository,
        private SetorRepository $setorRepository,
        private CargoRepository $cargoRepository,
        private FuncaoRepository $funcaoRepository,
        private TipoVinculoRepository $tipoVinculoRepository,
        private JornadaTrabalhoRepository $jornadaTrabalhoRepository
    ) {
    }

    /**
     * Lista os colaboradores da organização.
     */
    public function listar(
        int $organizacaoId,
        string $busca = '',
        string $situacao = 'TODOS'
    ): array {
        if ($organizacaoId <= 0) {
            return [];
        }

        return $this->colaboradorRepository->listar(
            $organizacaoId,
            trim($busca),
            $this->normalizarSituacao($situacao)
        );
    }

    /**
     * Busca um colaborador pelo ID.
     */
    public function buscarPorId(
        int $colaboradorId,
        int $organizacaoId
    ): ?array {
        if (
            $colaboradorId <= 0
            || $organizacaoId <= 0
        ) {
            return null;
        }

        return $this->colaboradorRepository->buscarPorId(
            $colaboradorId,
            $organizacaoId
        );
    }

    /**
     * Cadastra um novo colaborador.
     */
    public function criar(
        int $organizacaoId,
        int $unidadeId,
        array $dadosRecebidos
    ): array {
        $dados = $this->normalizarDados(
            $dadosRecebidos
        );

        $erros = $this->validarDadosBasicos(
            $dados
        );

        if ($organizacaoId <= 0) {
            $erros['organizacao'] =
                'A organização não foi identificada.';
        }

        if ($unidadeId <= 0) {
            $erros['unidade'] =
                'A unidade principal não foi identificada.';
        }

        $validacaoRelacionamentos =
            $this->validarRelacionamentos(
                $organizacaoId,
                $dados
            );

        $erros = array_merge(
            $erros,
            $validacaoRelacionamentos['erros']
        );

        $tipoVinculo =
            $validacaoRelacionamentos[
                'tipo_vinculo'
            ];

        $this->validarDatasDoVinculo(
            $dados,
            $tipoVinculo,
            $erros
        );

        if (
            $dados['cpf'] !== ''
            && $this->colaboradorRepository->existeCpf(
                $organizacaoId,
                $dados['cpf']
            )
        ) {
            $erros['cpf'] =
                'Já existe um colaborador cadastrado com este CPF.';
        }

        if (
            $dados['matricula'] !== null
            && $this->colaboradorRepository->existeMatricula(
                $organizacaoId,
                $dados['matricula']
            )
        ) {
            $erros['matricula'] =
                'Esta matrícula já está sendo utilizada.';
        }

        if ($erros !== []) {
            return [
                'sucesso' => false,
                'mensagem' =>
                    'Verifique os campos informados.',
                'erros' => $erros,
                'dados' => $dados,
            ];
        }

        try {
            $colaboradorId =
                $this->colaboradorRepository->criar([
                    'organizacao_id' =>
                        $organizacaoId,

                    'unidade_id' =>
                        $unidadeId,

                    ...$dados,
                ]);

            return [
                'sucesso' => true,
                'mensagem' =>
                    'Colaborador cadastrado com sucesso.',
                'colaborador_id' =>
                    $colaboradorId,
            ];
        } catch (PDOException $erro) {
            if ($erro->getCode() === '23505') {
                return [
                    'sucesso' => false,
                    'mensagem' =>
                        'Já existe um colaborador com o CPF ou matrícula informados.',
                    'erros' => [],
                    'dados' => $dados,
                ];
            }

            if ($erro->getCode() === '23503') {
                return [
                    'sucesso' => false,
                    'mensagem' =>
                        'Um dos dados funcionais selecionados não está mais disponível.',
                    'erros' => [],
                    'dados' => $dados,
                ];
            }

            throw $erro;
        }
    }

    /**
     * Atualiza um colaborador.
     */
    public function atualizar(
        int $colaboradorId,
        int $organizacaoId,
        int $unidadeId,
        array $dadosRecebidos
    ): array {
        if (
            $colaboradorId <= 0
            || $organizacaoId <= 0
        ) {
            return [
                'sucesso' => false,
                'mensagem' =>
                    'O colaborador informado é inválido.',
                'erros' => [],
            ];
        }

        if ($unidadeId <= 0) {
            return [
                'sucesso' => false,
                'mensagem' =>
                    'A unidade principal não foi identificada.',
                'erros' => [],
            ];
        }

        $colaboradorAtual =
            $this->colaboradorRepository->buscarPorId(
                $colaboradorId,
                $organizacaoId
            );

        if (!$colaboradorAtual) {
            return [
                'sucesso' => false,
                'mensagem' =>
                    'O colaborador não foi encontrado.',
                'erros' => [],
            ];
        }

        $dados = $this->normalizarDados(
            $dadosRecebidos
        );

        $erros = $this->validarDadosBasicos(
            $dados
        );

        $validacaoRelacionamentos =
            $this->validarRelacionamentos(
                $organizacaoId,
                $dados,
                $colaboradorAtual
            );

        $erros = array_merge(
            $erros,
            $validacaoRelacionamentos['erros']
        );

        $tipoVinculo =
            $validacaoRelacionamentos[
                'tipo_vinculo'
            ];

        $this->validarDatasDoVinculo(
            $dados,
            $tipoVinculo,
            $erros
        );

        if (
            $dados['cpf'] !== ''
            && $this->colaboradorRepository->existeCpf(
                $organizacaoId,
                $dados['cpf'],
                $colaboradorId
            )
        ) {
            $erros['cpf'] =
                'Já existe outro colaborador cadastrado com este CPF.';
        }

        if (
            $dados['matricula'] !== null
            && $this->colaboradorRepository->existeMatricula(
                $organizacaoId,
                $dados['matricula'],
                $colaboradorId
            )
        ) {
            $erros['matricula'] =
                'Esta matrícula já está sendo utilizada por outro colaborador.';
        }

        if ($erros !== []) {
            return [
                'sucesso' => false,
                'mensagem' =>
                    'Verifique os campos informados.',
                'erros' => $erros,
                'dados' => $dados,
            ];
        }

        try {
            $atualizado =
                $this->colaboradorRepository->atualizar(
                    $colaboradorId,
                    $organizacaoId,
                    [
                        'unidade_id' =>
                            $unidadeId,

                        ...$dados,
                    ]
                );

            return [
                'sucesso' => true,
                'mensagem' => $atualizado
                    ? 'Colaborador atualizado com sucesso.'
                    : 'Nenhuma alteração foi necessária.',
            ];
        } catch (PDOException $erro) {
            if ($erro->getCode() === '23505') {
                return [
                    'sucesso' => false,
                    'mensagem' =>
                        'Já existe outro colaborador com o CPF ou matrícula informados.',
                    'erros' => [],
                    'dados' => $dados,
                ];
            }

            if ($erro->getCode() === '23503') {
                return [
                    'sucesso' => false,
                    'mensagem' =>
                        'Um dos dados funcionais selecionados não está mais disponível.',
                    'erros' => [],
                    'dados' => $dados,
                ];
            }

            throw $erro;
        }
    }

    /**
     * Ativa ou desativa o colaborador.
     */
    public function alterarStatus(
        int $colaboradorId,
        int $organizacaoId
    ): array {
        if (
            $colaboradorId <= 0
            || $organizacaoId <= 0
        ) {
            return [
                'sucesso' => false,
                'mensagem' =>
                    'O colaborador informado é inválido.',
            ];
        }

        $colaborador =
            $this->colaboradorRepository->buscarPorId(
                $colaboradorId,
                $organizacaoId
            );

        if (!$colaborador) {
            return [
                'sucesso' => false,
                'mensagem' =>
                    'O colaborador não foi encontrado.',
            ];
        }

        $novoStatus = !$this->valorBooleano(
            $colaborador['ativo']
            ?? false
        );

        $alterado =
            $this->colaboradorRepository->alterarStatus(
                $colaboradorId,
                $organizacaoId,
                $novoStatus
            );

        if (!$alterado) {
            return [
                'sucesso' => false,
                'mensagem' =>
                    'Não foi possível alterar a situação do colaborador.',
            ];
        }

        return [
            'sucesso' => true,
            'mensagem' => $novoStatus
                ? 'Colaborador ativado com sucesso.'
                : 'Colaborador desativado com sucesso.',
            'ativo' => $novoStatus,
        ];
    }

    /**
     * Normaliza os dados recebidos do formulário.
     */
    private function normalizarDados(
        array $dados
    ): array {
        $nomeCompleto = trim(
            (string) (
                $dados['nome_completo']
                ?? ''
            )
        );

        $nomeCompleto = preg_replace(
            '/\s+/u',
            ' ',
            $nomeCompleto
        ) ?? $nomeCompleto;

        $cpf = preg_replace(
            '/\D+/',
            '',
            (string) ($dados['cpf'] ?? '')
        ) ?? '';

        $dataNascimento = trim(
            (string) (
                $dados['data_nascimento']
                ?? ''
            )
        );

        $email = mb_strtolower(
            trim(
                (string) (
                    $dados['email']
                    ?? ''
                )
            )
        );

        $telefone = preg_replace(
            '/\D+/',
            '',
            (string) (
                $dados['telefone']
                ?? ''
            )
        ) ?? '';

        $matricula = trim(
            (string) (
                $dados['matricula']
                ?? ''
            )
        );

        $funcaoIdRecebida =
            $dados['funcao_id']
            ?? null;

        $dataAdmissao = trim(
            (string) (
                $dados['data_admissao']
                ?? ''
            )
        );

        $dataFimVinculo = trim(
            (string) (
                $dados['data_fim_vinculo']
                ?? ''
            )
        );

        $observacoes = trim(
            (string) (
                $dados['observacoes']
                ?? ''
            )
        );

        $ativo = filter_var(
            $dados['ativo'] ?? true,
            FILTER_VALIDATE_BOOL,
            FILTER_NULL_ON_FAILURE
        );

        return [
            'nome_completo' =>
                $nomeCompleto,

            'cpf' =>
                $cpf,

            'data_nascimento' =>
                $dataNascimento !== ''
                    ? $dataNascimento
                    : null,

            'email' =>
                $email !== ''
                    ? $email
                    : null,

            'telefone' =>
                $telefone !== ''
                    ? $telefone
                    : null,

            'matricula' =>
                $matricula !== ''
                    ? $matricula
                    : null,

            'setor_id' =>
                (int) (
                    $dados['setor_id']
                    ?? 0
                ),

            'cargo_id' =>
                (int) (
                    $dados['cargo_id']
                    ?? 0
                ),

            'funcao_id' =>
                $funcaoIdRecebida !== null
                && $funcaoIdRecebida !== ''
                    ? (int) $funcaoIdRecebida
                    : null,

            'tipo_vinculo_id' =>
                (int) (
                    $dados['tipo_vinculo_id']
                    ?? 0
                ),

            'jornada_trabalho_id' =>
                (int) (
                    $dados[
                        'jornada_trabalho_id'
                    ]
                    ?? 0
                ),

            'data_admissao' =>
                $dataAdmissao,

            'data_fim_vinculo' =>
                $dataFimVinculo !== ''
                    ? $dataFimVinculo
                    : null,

            'observacoes' =>
                $observacoes !== ''
                    ? $observacoes
                    : null,

            'ativo' =>
                $ativo ?? true,
        ];
    }

    /**
     * Valida campos pessoais e funcionais básicos.
     */
    private function validarDadosBasicos(
        array $dados
    ): array {
        $erros = [];

        $nomeCompleto =
            (string) $dados['nome_completo'];

        if ($nomeCompleto === '') {
            $erros['nome_completo'] =
                'Informe o nome completo.';
        } elseif (
            mb_strlen($nomeCompleto) < 3
        ) {
            $erros['nome_completo'] =
                'O nome deve possuir pelo menos 3 caracteres.';
        } elseif (
            mb_strlen($nomeCompleto) > 180
        ) {
            $erros['nome_completo'] =
                'O nome deve possuir no máximo 180 caracteres.';
        }

        $cpf = (string) $dados['cpf'];

        if ($cpf === '') {
            $erros['cpf'] =
                'Informe o CPF.';
        } elseif (!$this->cpfValido($cpf)) {
            $erros['cpf'] =
                'Informe um CPF válido.';
        }

        if (
            $dados['data_nascimento'] !== null
        ) {
            $dataNascimento =
                (string) $dados[
                    'data_nascimento'
                ];

            if (
                !$this->dataValida(
                    $dataNascimento
                )
            ) {
                $erros['data_nascimento'] =
                    'Informe uma data de nascimento válida.';
            } elseif (
                $dataNascimento
                > date('Y-m-d')
            ) {
                $erros['data_nascimento'] =
                    'A data de nascimento não pode estar no futuro.';
            }
        }

        if ($dados['email'] !== null) {
            $email =
                (string) $dados['email'];

            if (
                mb_strlen($email) > 160
            ) {
                $erros['email'] =
                    'O e-mail deve possuir no máximo 160 caracteres.';
            } elseif (
                !filter_var(
                    $email,
                    FILTER_VALIDATE_EMAIL
                )
            ) {
                $erros['email'] =
                    'Informe um endereço de e-mail válido.';
            }
        }

        if ($dados['telefone'] !== null) {
            $telefone =
                (string) $dados['telefone'];

            $quantidadeDigitos =
                strlen($telefone);

            if (
                !in_array(
                    $quantidadeDigitos,
                    [10, 11],
                    true
                )
            ) {
                $erros['telefone'] =
                    'Informe um telefone com DDD.';
            }
        }

        if ($dados['matricula'] !== null) {
            $matricula =
                (string) $dados['matricula'];

            if (
                mb_strlen($matricula) > 50
            ) {
                $erros['matricula'] =
                    'A matrícula deve possuir no máximo 50 caracteres.';
            }
        }

        if (
            !$this->dataValida(
                (string) $dados[
                    'data_admissao'
                ]
            )
        ) {
            $erros['data_admissao'] =
                'Informe uma data de admissão válida.';
        }

        if (
            $dados['data_fim_vinculo']
            !== null
            && !$this->dataValida(
                (string) $dados[
                    'data_fim_vinculo'
                ]
            )
        ) {
            $erros['data_fim_vinculo'] =
                'Informe uma data final válida.';
        }

        if (
            $dados['observacoes'] !== null
            && mb_strlen(
                (string) $dados[
                    'observacoes'
                ]
            ) > 5000
        ) {
            $erros['observacoes'] =
                'As observações devem possuir no máximo 5.000 caracteres.';
        }

        return $erros;
    }

    /**
     * Valida os selects e seus relacionamentos.
     */
    private function validarRelacionamentos(
        int $organizacaoId,
        array $dados,
        ?array $colaboradorAtual = null
    ): array {
        $erros = [];

        $setor = null;
        $cargo = null;
        $funcao = null;
        $tipoVinculo = null;
        $jornada = null;

        if ($dados['setor_id'] <= 0) {
            $erros['setor_id'] =
                'Selecione o setor.';
        } else {
            $setor =
                $this->setorRepository->buscarPorId(
                    $dados['setor_id'],
                    $organizacaoId
                );

            if (!$setor) {
                $erros['setor_id'] =
                    'O setor selecionado não foi encontrado.';
            } elseif (
                !$this->registroPodeSerSelecionado(
                    $setor,
                    $colaboradorAtual[
                        'setor_id'
                    ] ?? null,
                    $dados['setor_id']
                )
            ) {
                $erros['setor_id'] =
                    'O setor selecionado está inativo.';
            }
        }

        if ($dados['cargo_id'] <= 0) {
            $erros['cargo_id'] =
                'Selecione o cargo.';
        } else {
            $cargo =
                $this->cargoRepository->buscarPorId(
                    $dados['cargo_id'],
                    $organizacaoId
                );

            if (!$cargo) {
                $erros['cargo_id'] =
                    'O cargo selecionado não foi encontrado.';
            } elseif (
                !$this->registroPodeSerSelecionado(
                    $cargo,
                    $colaboradorAtual[
                        'cargo_id'
                    ] ?? null,
                    $dados['cargo_id']
                )
            ) {
                $erros['cargo_id'] =
                    'O cargo selecionado está inativo.';
            }
        }

        if ($dados['funcao_id'] !== null) {
            if ($dados['funcao_id'] <= 0) {
                $erros['funcao_id'] =
                    'A função selecionada é inválida.';
            } else {
                $funcao =
                    $this->funcaoRepository->buscarPorId(
                        $dados['funcao_id'],
                        $organizacaoId
                    );

                if (!$funcao) {
                    $erros['funcao_id'] =
                        'A função selecionada não foi encontrada.';
                } elseif (
                    !$this->registroPodeSerSelecionado(
                        $funcao,
                        $colaboradorAtual[
                            'funcao_id'
                        ] ?? null,
                        $dados['funcao_id']
                    )
                ) {
                    $erros['funcao_id'] =
                        'A função selecionada está inativa.';
                }
            }
        }

        if (
            $dados['tipo_vinculo_id'] <= 0
        ) {
            $erros['tipo_vinculo_id'] =
                'Selecione o tipo de vínculo.';
        } else {
            $tipoVinculo =
                $this->tipoVinculoRepository->buscarPorId(
                    $dados['tipo_vinculo_id'],
                    $organizacaoId
                );

            if (!$tipoVinculo) {
                $erros['tipo_vinculo_id'] =
                    'O tipo de vínculo selecionado não foi encontrado.';
            } elseif (
                !$this->registroPodeSerSelecionado(
                    $tipoVinculo,
                    $colaboradorAtual[
                        'tipo_vinculo_id'
                    ] ?? null,
                    $dados['tipo_vinculo_id']
                )
            ) {
                $erros['tipo_vinculo_id'] =
                    'O tipo de vínculo selecionado está inativo.';
            }
        }

        if (
            $dados['jornada_trabalho_id'] <= 0
        ) {
            $erros['jornada_trabalho_id'] =
                'Selecione a jornada de trabalho.';
        } else {
            $jornada =
                $this->jornadaTrabalhoRepository->buscarPorId(
                    $dados[
                        'jornada_trabalho_id'
                    ],
                    $organizacaoId
                );

            if (!$jornada) {
                $erros['jornada_trabalho_id'] =
                    'A jornada selecionada não foi encontrada.';
            } elseif (
                !$this->registroPodeSerSelecionado(
                    $jornada,
                    $colaboradorAtual[
                        'jornada_trabalho_id'
                    ] ?? null,
                    $dados[
                        'jornada_trabalho_id'
                    ]
                )
            ) {
                $erros['jornada_trabalho_id'] =
                    'A jornada selecionada está inativa.';
            }
        }

        return [
            'erros' => $erros,
            'setor' => $setor,
            'cargo' => $cargo,
            'funcao' => $funcao,
            'tipo_vinculo' =>
                $tipoVinculo,
            'jornada' => $jornada,
        ];
    }

    /**
     * Valida as datas conforme o tipo de vínculo.
     */
    private function validarDatasDoVinculo(
        array $dados,
        ?array $tipoVinculo,
        array &$erros
    ): void {
        $dataAdmissao =
            (string) $dados['data_admissao'];

        $dataFim =
            $dados['data_fim_vinculo'];

        if (
            $tipoVinculo
            && $this->valorBooleano(
                $tipoVinculo[
                    'exige_data_fim'
                ] ?? false
            )
            && $dataFim === null
        ) {
            $erros['data_fim_vinculo'] =
                'Este tipo de vínculo exige uma data final.';
        }

        if (
            $dataFim !== null
            && $this->dataValida(
                $dataAdmissao
            )
            && $this->dataValida(
                (string) $dataFim
            )
            && $dataFim < $dataAdmissao
        ) {
            $erros['data_fim_vinculo'] =
                'A data final não pode ser anterior à data de admissão.';
        }
    }

    /**
     * Permite manter um relacionamento inativo já vinculado
     * durante a edição, mas impede selecionar um novo inativo.
     */
    private function registroPodeSerSelecionado(
        array $registro,
        mixed $registroAtualId,
        int $registroSelecionadoId
    ): bool {
        if (
            $this->valorBooleano(
                $registro['ativo']
                ?? false
            )
        ) {
            return true;
        }

        return (int) $registroAtualId
            === $registroSelecionadoId;
    }

    /**
     * Valida um CPF brasileiro.
     */
    private function cpfValido(
        string $cpf
    ): bool {
        if (
            !preg_match(
                '/^[0-9]{11}$/',
                $cpf
            )
        ) {
            return false;
        }

        if (
            preg_match(
                '/^(\d)\1{10}$/',
                $cpf
            )
        ) {
            return false;
        }

        for (
            $tamanho = 9;
            $tamanho < 11;
            $tamanho++
        ) {
            $soma = 0;

            for (
                $indice = 0;
                $indice < $tamanho;
                $indice++
            ) {
                $soma +=
                    (int) $cpf[$indice]
                    * (
                        ($tamanho + 1)
                        - $indice
                    );
            }

            $digito =
                (10 * $soma)
                % 11;

            if ($digito === 10) {
                $digito = 0;
            }

            if (
                $digito
                !== (int) $cpf[$tamanho]
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Valida uma data no formato YYYY-MM-DD.
     */
    private function dataValida(
        string $data
    ): bool {
        if ($data === '') {
            return false;
        }

        $dataConvertida =
            DateTimeImmutable::createFromFormat(
                '!Y-m-d',
                $data
            );

        return $dataConvertida
            instanceof DateTimeImmutable
            && $dataConvertida->format(
                'Y-m-d'
            ) === $data;
    }

    /**
     * Converte diferentes formatos vindos do PostgreSQL
     * ou do formulário para booleano.
     */
    private function valorBooleano(
        mixed $valor
    ): bool {
        if (is_bool($valor)) {
            return $valor;
        }

        if (is_int($valor)) {
            return $valor === 1;
        }

        return in_array(
            mb_strtolower(
                trim((string) $valor)
            ),
            [
                '1',
                'true',
                't',
                'yes',
                'sim',
                'on',
            ],
            true
        );
    }

    /**
     * Normaliza o filtro de situação.
     */
    private function normalizarSituacao(
        string $situacao
    ): string {
        $situacao = strtoupper(
            trim($situacao)
        );

        if (
            !in_array(
                $situacao,
                [
                    'TODOS',
                    'ATIVOS',
                    'INATIVOS',
                ],
                true
            )
        ) {
            return 'TODOS';
        }

        return $situacao;
    }
}