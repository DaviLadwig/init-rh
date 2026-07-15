<?php

declare(strict_types=1);

class FuncaoService
{
    public function __construct(
        private FuncaoRepository $funcaoRepository
    ) {
    }

    /**
     * Retorna as funções da organização aplicando os filtros.
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

        return $this->funcaoRepository->listar(
            $organizacaoId,
            $busca,
            $situacao
        );
    }

    /**
     * Localiza uma função da organização.
     */
    public function buscarPorId(
        int $funcaoId,
        int $organizacaoId
    ): ?array {
        if (
            $funcaoId <= 0
            || $organizacaoId <= 0
        ) {
            return null;
        }

        return $this->funcaoRepository->buscarPorId(
            $funcaoId,
            $organizacaoId
        );
    }

    /**
     * Valida e cadastra uma função.
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
            $this->funcaoRepository->existeNome(
                $organizacaoId,
                $dados['nome']
            )
        ) {
            return [
                'sucesso' => false,
                'mensagem' =>
                    'Já existe uma função com esse nome.',
                'erros' => [
                    'nome' =>
                        'Já existe uma função com esse nome.',
                ],
                'dados' => $dados,
            ];
        }

        if (
            $dados['codigo'] !== null
            && $this->funcaoRepository->existeCodigo(
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
            $funcaoId =
                $this->funcaoRepository->criar([
                    'organizacao_id' =>
                        $organizacaoId,

                    'nome' =>
                        $dados['nome'],

                    'codigo' =>
                        $dados['codigo'],

                    'descricao' =>
                        $dados['descricao'],

                    'ativo' =>
                        $dados['ativo'],
                ]);

            return [
                'sucesso' => true,
                'mensagem' =>
                    'Função cadastrada com sucesso.',
                'funcao_id' => $funcaoId,
            ];
        } catch (PDOException $erro) {
            if ($erro->getCode() === '23505') {
                return [
                    'sucesso' => false,
                    'mensagem' =>
                        'Já existe uma função com os dados informados.',
                    'erros' => [],
                    'dados' => $dados,
                ];
            }

            throw $erro;
        }
    }

    /**
     * Valida e atualiza uma função.
     */
    public function atualizar(
        int $funcaoId,
        int $organizacaoId,
        array $dadosRecebidos
    ): array {
        if (
            $funcaoId <= 0
            || $organizacaoId <= 0
        ) {
            return [
                'sucesso' => false,
                'mensagem' =>
                    'A função informada é inválida.',
                'erros' => [],
            ];
        }

        $funcaoAtual =
            $this->funcaoRepository->buscarPorId(
                $funcaoId,
                $organizacaoId
            );

        if (!$funcaoAtual) {
            return [
                'sucesso' => false,
                'mensagem' =>
                    'A função informada não foi encontrada.',
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
            $this->funcaoRepository->existeNome(
                $organizacaoId,
                $dados['nome'],
                $funcaoId
            )
        ) {
            return [
                'sucesso' => false,
                'mensagem' =>
                    'Já existe outra função com esse nome.',
                'erros' => [
                    'nome' =>
                        'Já existe outra função com esse nome.',
                ],
                'dados' => $dados,
            ];
        }

        if (
            $dados['codigo'] !== null
            && $this->funcaoRepository->existeCodigo(
                $organizacaoId,
                $dados['codigo'],
                $funcaoId
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
                $this->funcaoRepository->atualizar(
                    $funcaoId,
                    $organizacaoId,
                    $dados
                );

            if (!$atualizado) {
                return [
                    'sucesso' => true,
                    'mensagem' =>
                        'Nenhuma alteração foi necessária.',
                ];
            }

            return [
                'sucesso' => true,
                'mensagem' =>
                    'Função atualizada com sucesso.',
            ];
        } catch (PDOException $erro) {
            if ($erro->getCode() === '23505') {
                return [
                    'sucesso' => false,
                    'mensagem' =>
                        'Já existe uma função com os dados informados.',
                    'erros' => [],
                    'dados' => $dados,
                ];
            }

            throw $erro;
        }
    }

    /**
     * Alterna a situação da função.
     */
    public function alterarStatus(
        int $funcaoId,
        int $organizacaoId
    ): array {
        $funcao =
            $this->funcaoRepository->buscarPorId(
                $funcaoId,
                $organizacaoId
            );

        if (!$funcao) {
            return [
                'sucesso' => false,
                'mensagem' =>
                    'A função informada não foi encontrada.',
            ];
        }

        $novoStatus = !(bool) (
            $funcao['ativo']
            ?? false
        );

        $alterado =
            $this->funcaoRepository->alterarStatus(
                $funcaoId,
                $organizacaoId,
                $novoStatus
            );

        if (!$alterado) {
            return [
                'sucesso' => false,
                'mensagem' =>
                    'Não foi possível alterar a situação da função.',
            ];
        }

        return [
            'sucesso' => true,
            'mensagem' => $novoStatus
                ? 'Função ativada com sucesso.'
                : 'Função desativada com sucesso.',
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
     * Valida os campos da função.
     */
    private function validarDados(
        array $dados
    ): array {
        $erros = [];

        $nome = (string) $dados['nome'];

        if ($nome === '') {
            $erros['nome'] =
                'Informe o nome da função.';
        } elseif (mb_strlen($nome) < 2) {
            $erros['nome'] =
                'O nome da função deve possuir pelo menos 2 caracteres.';
        } elseif (mb_strlen($nome) > 120) {
            $erros['nome'] =
                'O nome da função deve possuir no máximo 120 caracteres.';
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