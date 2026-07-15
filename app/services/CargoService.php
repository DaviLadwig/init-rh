<?php

declare(strict_types=1);

class CargoService
{
    public function __construct(
        private CargoRepository $cargoRepository
    ) {
    }

    /**
     * Retorna os cargos da organização aplicando os filtros.
     */
    public function listar(
        int $organizacaoId,
        string $busca = '',
        string $situacao = 'TODOS'
    ): array {
        if ($organizacaoId <= 0) {
            return [];
        }

        $busca = trim($busca);

        $situacao = $this->normalizarSituacao(
            $situacao
        );

        return $this->cargoRepository->listar(
            $organizacaoId,
            $busca,
            $situacao
        );
    }

    /**
     * Localiza um cargo pertencente à organização.
     */
    public function buscarPorId(
        int $cargoId,
        int $organizacaoId
    ): ?array {
        if (
            $cargoId <= 0
            || $organizacaoId <= 0
        ) {
            return null;
        }

        return $this->cargoRepository->buscarPorId(
            $cargoId,
            $organizacaoId
        );
    }

    /**
     * Valida e cadastra um cargo.
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
            $this->cargoRepository->existeNome(
                $organizacaoId,
                $dados['nome']
            )
        ) {
            return [
                'sucesso' => false,
                'mensagem' =>
                    'Já existe um cargo com esse nome.',
                'erros' => [
                    'nome' =>
                        'Já existe um cargo com esse nome.',
                ],
                'dados' => $dados,
            ];
        }

        if (
            $dados['codigo'] !== null
            && $this->cargoRepository->existeCodigo(
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
            $cargoId = $this->cargoRepository->criar([
                'organizacao_id' => $organizacaoId,
                'nome' => $dados['nome'],
                'codigo' => $dados['codigo'],
                'descricao' => $dados['descricao'],
                'ativo' => $dados['ativo'],
            ]);

            return [
                'sucesso' => true,
                'mensagem' =>
                    'Cargo cadastrado com sucesso.',
                'cargo_id' => $cargoId,
            ];
        } catch (PDOException $erro) {
            /*
             * PostgreSQL: violação de chave única.
             */
            if ($erro->getCode() === '23505') {
                return [
                    'sucesso' => false,
                    'mensagem' =>
                        'Já existe um cargo com os dados informados.',
                    'erros' => [],
                    'dados' => $dados,
                ];
            }

            throw $erro;
        }
    }

    /**
     * Valida e atualiza um cargo.
     */
    public function atualizar(
        int $cargoId,
        int $organizacaoId,
        array $dadosRecebidos
    ): array {
        if (
            $cargoId <= 0
            || $organizacaoId <= 0
        ) {
            return [
                'sucesso' => false,
                'mensagem' =>
                    'O cargo informado é inválido.',
                'erros' => [],
            ];
        }

        $cargoAtual =
            $this->cargoRepository->buscarPorId(
                $cargoId,
                $organizacaoId
            );

        if (!$cargoAtual) {
            return [
                'sucesso' => false,
                'mensagem' =>
                    'O cargo informado não foi encontrado.',
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
            $this->cargoRepository->existeNome(
                $organizacaoId,
                $dados['nome'],
                $cargoId
            )
        ) {
            return [
                'sucesso' => false,
                'mensagem' =>
                    'Já existe outro cargo com esse nome.',
                'erros' => [
                    'nome' =>
                        'Já existe outro cargo com esse nome.',
                ],
                'dados' => $dados,
            ];
        }

        if (
            $dados['codigo'] !== null
            && $this->cargoRepository->existeCodigo(
                $organizacaoId,
                $dados['codigo'],
                $cargoId
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
                $this->cargoRepository->atualizar(
                    $cargoId,
                    $organizacaoId,
                    $dados
                );

            if (!$atualizado) {
                /*
                 * rowCount() pode retornar zero quando os dados
                 * enviados são iguais aos dados já armazenados.
                 */
                return [
                    'sucesso' => true,
                    'mensagem' =>
                        'Nenhuma alteração foi necessária.',
                ];
            }

            return [
                'sucesso' => true,
                'mensagem' =>
                    'Cargo atualizado com sucesso.',
            ];
        } catch (PDOException $erro) {
            if ($erro->getCode() === '23505') {
                return [
                    'sucesso' => false,
                    'mensagem' =>
                        'Já existe um cargo com os dados informados.',
                    'erros' => [],
                    'dados' => $dados,
                ];
            }

            throw $erro;
        }
    }

    /**
     * Alterna a situação do cargo.
     */
    public function alterarStatus(
        int $cargoId,
        int $organizacaoId
    ): array {
        $cargo = $this->cargoRepository->buscarPorId(
            $cargoId,
            $organizacaoId
        );

        if (!$cargo) {
            return [
                'sucesso' => false,
                'mensagem' =>
                    'O cargo informado não foi encontrado.',
            ];
        }

        $novoStatus = !(bool) (
            $cargo['ativo']
            ?? false
        );

        $alterado =
            $this->cargoRepository->alterarStatus(
                $cargoId,
                $organizacaoId,
                $novoStatus
            );

        if (!$alterado) {
            return [
                'sucesso' => false,
                'mensagem' =>
                    'Não foi possível alterar a situação do cargo.',
            ];
        }

        return [
            'sucesso' => true,
            'mensagem' => $novoStatus
                ? 'Cargo ativado com sucesso.'
                : 'Cargo desativado com sucesso.',
            'ativo' => $novoStatus,
        ];
    }

    /**
     * Normaliza os campos recebidos do formulário.
     */
    private function normalizarDados(
        array $dados
    ): array {
        $nome = trim(
            (string) ($dados['nome'] ?? '')
        );

        $codigo = strtoupper(
            trim(
                (string) (
                    $dados['codigo']
                    ?? ''
                )
            )
        );

        $codigo = preg_replace(
            '/\s+/',
            '',
            $codigo
        ) ?? '';

        $descricao = trim(
            (string) (
                $dados['descricao']
                ?? ''
            )
        );

        $ativoRecebido =
            $dados['ativo']
            ?? true;

        $ativo = filter_var(
            $ativoRecebido,
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

            'ativo' => $ativo ?? true,
        ];
    }

    /**
     * Valida os campos do cargo.
     */
    private function validarDados(
        array $dados
    ): array {
        $erros = [];

        $nome = (string) $dados['nome'];

        if ($nome === '') {
            $erros['nome'] =
                'Informe o nome do cargo.';
        } elseif (mb_strlen($nome) < 2) {
            $erros['nome'] =
                'O nome do cargo deve possuir pelo menos 2 caracteres.';
        } elseif (mb_strlen($nome) > 120) {
            $erros['nome'] =
                'O nome do cargo deve possuir no máximo 120 caracteres.';
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
                    'Use somente letras, números, hífen ou sublinhado no código.';
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

        $situacoesPermitidas = [
            'TODOS',
            'ATIVOS',
            'INATIVOS',
        ];

        if (
            !in_array(
                $situacao,
                $situacoesPermitidas,
                true
            )
        ) {
            return 'TODOS';
        }

        return $situacao;
    }
}