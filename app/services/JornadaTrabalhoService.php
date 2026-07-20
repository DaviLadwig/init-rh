<?php

declare(strict_types=1);

class JornadaTrabalhoService
{
    public function __construct(
        private JornadaTrabalhoRepository $jornadaTrabalhoRepository
    ) {
    }

    /**
     * Lista as jornadas da organização.
     */
    public function listar(
        int $organizacaoId,
        string $busca = '',
        string $situacao = 'TODOS'
    ): array {
        if ($organizacaoId <= 0) {
            return [];
        }

        return $this->jornadaTrabalhoRepository->listar(
            $organizacaoId,
            trim($busca),
            $this->normalizarSituacao($situacao)
        );
    }

    /**
     * Busca uma jornada pelo ID.
     */
    public function buscarPorId(
        int $jornadaId,
        int $organizacaoId
    ): ?array {
        if (
            $jornadaId <= 0
            || $organizacaoId <= 0
        ) {
            return null;
        }

        return $this->jornadaTrabalhoRepository->buscarPorId(
            $jornadaId,
            $organizacaoId
        );
    }

    /**
     * Cadastra uma jornada.
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
            $this->jornadaTrabalhoRepository->existeNome(
                $organizacaoId,
                $dados['nome']
            )
        ) {
            return [
                'sucesso' => false,
                'mensagem' =>
                    'Já existe uma jornada com esse nome.',
                'erros' => [
                    'nome' =>
                        'Já existe uma jornada com esse nome.',
                ],
                'dados' => $dados,
            ];
        }

        if (
            $dados['codigo'] !== null
            && $this->jornadaTrabalhoRepository->existeCodigo(
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
            $jornadaId =
                $this->jornadaTrabalhoRepository->criar([
                    'organizacao_id' =>
                        $organizacaoId,

                    'nome' =>
                        $dados['nome'],

                    'codigo' =>
                        $dados['codigo'],

                    'carga_horaria_semanal' =>
                        $dados['carga_horaria_semanal'],

                    'descricao' =>
                        $dados['descricao'],

                    'ativo' =>
                        $dados['ativo'],
                ]);

            return [
                'sucesso' => true,
                'mensagem' =>
                    'Jornada de trabalho cadastrada com sucesso.',
                'jornada_id' => $jornadaId,
            ];
        } catch (PDOException $erro) {
            if ($erro->getCode() === '23505') {
                return [
                    'sucesso' => false,
                    'mensagem' =>
                        'Já existe uma jornada com os dados informados.',
                    'erros' => [],
                    'dados' => $dados,
                ];
            }

            throw $erro;
        }
    }

    /**
     * Atualiza uma jornada.
     */
    public function atualizar(
        int $jornadaId,
        int $organizacaoId,
        array $dadosRecebidos
    ): array {
        if (
            $jornadaId <= 0
            || $organizacaoId <= 0
        ) {
            return [
                'sucesso' => false,
                'mensagem' =>
                    'A jornada informada é inválida.',
                'erros' => [],
            ];
        }

        $jornadaAtual =
            $this->jornadaTrabalhoRepository->buscarPorId(
                $jornadaId,
                $organizacaoId
            );

        if (!$jornadaAtual) {
            return [
                'sucesso' => false,
                'mensagem' =>
                    'A jornada informada não foi encontrada.',
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
            $this->jornadaTrabalhoRepository->existeNome(
                $organizacaoId,
                $dados['nome'],
                $jornadaId
            )
        ) {
            return [
                'sucesso' => false,
                'mensagem' =>
                    'Já existe outra jornada com esse nome.',
                'erros' => [
                    'nome' =>
                        'Já existe outra jornada com esse nome.',
                ],
                'dados' => $dados,
            ];
        }

        if (
            $dados['codigo'] !== null
            && $this->jornadaTrabalhoRepository->existeCodigo(
                $organizacaoId,
                $dados['codigo'],
                $jornadaId
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
                $this->jornadaTrabalhoRepository->atualizar(
                    $jornadaId,
                    $organizacaoId,
                    $dados
                );

            return [
                'sucesso' => true,
                'mensagem' => $atualizado
                    ? 'Jornada de trabalho atualizada com sucesso.'
                    : 'Nenhuma alteração foi necessária.',
            ];
        } catch (PDOException $erro) {
            if ($erro->getCode() === '23505') {
                return [
                    'sucesso' => false,
                    'mensagem' =>
                        'Já existe uma jornada com os dados informados.',
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
        int $jornadaId,
        int $organizacaoId
    ): array {
        if (
            $jornadaId <= 0
            || $organizacaoId <= 0
        ) {
            return [
                'sucesso' => false,
                'mensagem' =>
                    'A jornada informada é inválida.',
            ];
        }

        $jornada =
            $this->jornadaTrabalhoRepository->buscarPorId(
                $jornadaId,
                $organizacaoId
            );

        if (!$jornada) {
            return [
                'sucesso' => false,
                'mensagem' =>
                    'A jornada não foi encontrada.',
            ];
        }

        $novoStatus = !filter_var(
            $jornada['ativo'] ?? false,
            FILTER_VALIDATE_BOOL
        );

        $alterado =
            $this->jornadaTrabalhoRepository->alterarStatus(
                $jornadaId,
                $organizacaoId,
                $novoStatus
            );

        if (!$alterado) {
            return [
                'sucesso' => false,
                'mensagem' =>
                    'Não foi possível alterar a situação da jornada.',
            ];
        }

        return [
            'sucesso' => true,
            'mensagem' => $novoStatus
                ? 'Jornada ativada com sucesso.'
                : 'Jornada desativada com sucesso.',
            'ativo' => $novoStatus,
        ];
    }

    /**
     * Normaliza os dados do formulário.
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

        $cargaRecebida = trim(
            (string) (
                $dados['carga_horaria_semanal']
                ?? ''
            )
        );

        $cargaRecebida = str_replace(
            ',',
            '.',
            $cargaRecebida
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

            /*
             * Mantém a string quando preenchida para permitir
             * que a validação detecte valores inválidos.
             */
            'carga_horaria_semanal' =>
                $cargaRecebida !== ''
                    ? $cargaRecebida
                    : null,

            'descricao' => $descricao !== ''
                ? $descricao
                : null,

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
                'Informe o nome da jornada.';
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
            $dados['carga_horaria_semanal']
            !== null
        ) {
            $carga = (string)
                $dados['carga_horaria_semanal'];

            if (
                !preg_match(
                    '/^\d{1,3}(\.\d{1,2})?$/',
                    $carga
                )
            ) {
                $erros['carga_horaria_semanal'] =
                    'Informe uma carga horária válida, como 40 ou 36,50.';
            } elseif (
                (float) $carga <= 0
                || (float) $carga > 168
            ) {
                $erros['carga_horaria_semanal'] =
                    'A carga horária deve ser maior que 0 e menor ou igual a 168.';
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