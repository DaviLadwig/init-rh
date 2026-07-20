<?php

declare(strict_types=1);

class TipoVinculoService
{
    public function __construct(
        private TipoVinculoRepository $tipoVinculoRepository
    ) {
    }

    /**
     * Lista os tipos de vínculo da organização.
     */
    public function listar(
        int $organizacaoId,
        string $busca = '',
        string $situacao = 'TODOS'
    ): array {
        if ($organizacaoId <= 0) {
            return [];
        }

        return $this->tipoVinculoRepository->listar(
            $organizacaoId,
            trim($busca),
            $this->normalizarSituacao($situacao)
        );
    }

    /**
     * Busca um tipo de vínculo pelo ID.
     */
    public function buscarPorId(
        int $tipoVinculoId,
        int $organizacaoId
    ): ?array {
        if (
            $tipoVinculoId <= 0
            || $organizacaoId <= 0
        ) {
            return null;
        }

        return $this->tipoVinculoRepository->buscarPorId(
            $tipoVinculoId,
            $organizacaoId
        );
    }

    /**
     * Cadastra um tipo de vínculo.
     */
    public function criar(
        int $organizacaoId,
        array $dadosRecebidos
    ): array {
        $dados = $this->normalizarDados(
            $dadosRecebidos
        );

        $erros = $this->validarDados($dados);

        if ($organizacaoId <= 0) {
            $erros['organizacao'] =
                'A organização não foi identificada.';
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

        if (
            $this->tipoVinculoRepository->existeNome(
                $organizacaoId,
                $dados['nome']
            )
        ) {
            return [
                'sucesso' => false,
                'mensagem' =>
                    'Já existe um tipo de vínculo com esse nome.',
                'erros' => [
                    'nome' =>
                        'Já existe um tipo de vínculo com esse nome.',
                ],
                'dados' => $dados,
            ];
        }

        if (
            $dados['codigo'] !== null
            && $this->tipoVinculoRepository->existeCodigo(
                $organizacaoId,
                $dados['codigo']
            )
        ) {
            return [
                'sucesso' => false,
                'mensagem' =>
                    'O código informado já está sendo utilizado.',
                'erros' => [
                    'codigo' =>
                        'O código informado já está sendo utilizado.',
                ],
                'dados' => $dados,
            ];
        }

        try {
            $tipoVinculoId =
                $this->tipoVinculoRepository->criar([
                    'organizacao_id' =>
                        $organizacaoId,

                    'nome' =>
                        $dados['nome'],

                    'codigo' =>
                        $dados['codigo'],

                    'descricao' =>
                        $dados['descricao'],

                    'exige_data_fim' =>
                        $dados['exige_data_fim'],

                    'ativo' =>
                        $dados['ativo'],
                ]);

            return [
                'sucesso' => true,
                'mensagem' =>
                    'Tipo de vínculo cadastrado com sucesso.',
                'tipo_vinculo_id' => $tipoVinculoId,
            ];
        } catch (PDOException $erro) {
            if ($erro->getCode() === '23505') {
                return [
                    'sucesso' => false,
                    'mensagem' =>
                        'Já existe um tipo de vínculo com os dados informados.',
                    'erros' => [],
                    'dados' => $dados,
                ];
            }

            throw $erro;
        }
    }

    /**
     * Atualiza um tipo de vínculo.
     */
    public function atualizar(
        int $tipoVinculoId,
        int $organizacaoId,
        array $dadosRecebidos
    ): array {
        if (
            $tipoVinculoId <= 0
            || $organizacaoId <= 0
        ) {
            return [
                'sucesso' => false,
                'mensagem' =>
                    'O tipo de vínculo informado é inválido.',
                'erros' => [],
            ];
        }

        $tipoVinculoAtual =
            $this->tipoVinculoRepository->buscarPorId(
                $tipoVinculoId,
                $organizacaoId
            );

        if (!$tipoVinculoAtual) {
            return [
                'sucesso' => false,
                'mensagem' =>
                    'O tipo de vínculo informado não foi encontrado.',
                'erros' => [],
            ];
        }

        $dados = $this->normalizarDados(
            $dadosRecebidos
        );

        $erros = $this->validarDados($dados);

        if ($erros !== []) {
            return [
                'sucesso' => false,
                'mensagem' =>
                    'Verifique os campos informados.',
                'erros' => $erros,
                'dados' => $dados,
            ];
        }

        if (
            $this->tipoVinculoRepository->existeNome(
                $organizacaoId,
                $dados['nome'],
                $tipoVinculoId
            )
        ) {
            return [
                'sucesso' => false,
                'mensagem' =>
                    'Já existe outro tipo de vínculo com esse nome.',
                'erros' => [
                    'nome' =>
                        'Já existe outro tipo de vínculo com esse nome.',
                ],
                'dados' => $dados,
            ];
        }

        if (
            $dados['codigo'] !== null
            && $this->tipoVinculoRepository->existeCodigo(
                $organizacaoId,
                $dados['codigo'],
                $tipoVinculoId
            )
        ) {
            return [
                'sucesso' => false,
                'mensagem' =>
                    'O código informado já está sendo utilizado.',
                'erros' => [
                    'codigo' =>
                        'O código informado já está sendo utilizado.',
                ],
                'dados' => $dados,
            ];
        }

        try {
            $atualizado =
                $this->tipoVinculoRepository->atualizar(
                    $tipoVinculoId,
                    $organizacaoId,
                    $dados
                );

            return [
                'sucesso' => true,
                'mensagem' => $atualizado
                    ? 'Tipo de vínculo atualizado com sucesso.'
                    : 'Nenhuma alteração foi necessária.',
            ];
        } catch (PDOException $erro) {
            if ($erro->getCode() === '23505') {
                return [
                    'sucesso' => false,
                    'mensagem' =>
                        'Já existe um tipo de vínculo com os dados informados.',
                    'erros' => [],
                    'dados' => $dados,
                ];
            }

            throw $erro;
        }
    }

    /**
     * Alterna entre ativo e inativo.
     */
    public function alterarStatus(
        int $tipoVinculoId,
        int $organizacaoId
    ): array {
        if (
            $tipoVinculoId <= 0
            || $organizacaoId <= 0
        ) {
            return [
                'sucesso' => false,
                'mensagem' =>
                    'O tipo de vínculo informado é inválido.',
            ];
        }

        $tipoVinculo =
            $this->tipoVinculoRepository->buscarPorId(
                $tipoVinculoId,
                $organizacaoId
            );

        if (!$tipoVinculo) {
            return [
                'sucesso' => false,
                'mensagem' =>
                    'O tipo de vínculo não foi encontrado.',
            ];
        }

        $novoStatus = !filter_var(
            $tipoVinculo['ativo'] ?? false,
            FILTER_VALIDATE_BOOL
        );

        $alterado =
            $this->tipoVinculoRepository->alterarStatus(
                $tipoVinculoId,
                $organizacaoId,
                $novoStatus
            );

        if (!$alterado) {
            return [
                'sucesso' => false,
                'mensagem' =>
                    'Não foi possível alterar a situação do tipo de vínculo.',
            ];
        }

        return [
            'sucesso' => true,
            'mensagem' => $novoStatus
                ? 'Tipo de vínculo ativado com sucesso.'
                : 'Tipo de vínculo desativado com sucesso.',
            'ativo' => $novoStatus,
        ];
    }

    /**
     * Normaliza os dados recebidos do formulário.
     */
    private function normalizarDados(
        array $dados
    ): array {
        $nome = trim(
            (string) ($dados['nome'] ?? '')
        );

        $codigo = strtoupper(
            trim(
                (string) ($dados['codigo'] ?? '')
            )
        );

        $codigo = preg_replace(
            '/\s+/',
            '',
            $codigo
        ) ?? '';

        $descricao = trim(
            (string) ($dados['descricao'] ?? '')
        );

        $exigeDataFim = filter_var(
            $dados['exige_data_fim'] ?? false,
            FILTER_VALIDATE_BOOL,
            FILTER_NULL_ON_FAILURE
        );

        $ativo = filter_var(
            $dados['ativo'] ?? true,
            FILTER_VALIDATE_BOOL,
            FILTER_NULL_ON_FAILURE
        );

        return [
            'nome' => $nome,

            'codigo' => $codigo !== ''
                ? $codigo
                : null,

            'descricao' => $descricao !== ''
                ? $descricao
                : null,

            'exige_data_fim' =>
                $exigeDataFim ?? false,

            'ativo' => $ativo ?? true,
        ];
    }

    /**
     * Valida os campos.
     */
    private function validarDados(
        array $dados
    ): array {
        $erros = [];

        $nome = (string) $dados['nome'];

        if ($nome === '') {
            $erros['nome'] =
                'Informe o nome do tipo de vínculo.';
        } elseif (mb_strlen($nome) < 2) {
            $erros['nome'] =
                'O nome deve possuir pelo menos 2 caracteres.';
        } elseif (mb_strlen($nome) > 120) {
            $erros['nome'] =
                'O nome deve possuir no máximo 120 caracteres.';
        }

        if ($dados['codigo'] !== null) {
            $codigo = (string) $dados['codigo'];

            if (mb_strlen($codigo) > 30) {
                $erros['codigo'] =
                    'O código deve possuir no máximo 30 caracteres.';
            } elseif (
                !preg_match(
                    '/^[A-Z0-9_-]+$/',
                    $codigo
                )
            ) {
                $erros['codigo'] =
                    'Use somente letras, números, hífen ou sublinhado.';
            }
        }

        if (
            $dados['descricao'] !== null
            && mb_strlen(
                (string) $dados['descricao']
            ) > 2000
        ) {
            $erros['descricao'] =
                'A descrição deve possuir no máximo 2.000 caracteres.';
        }

        return $erros;
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