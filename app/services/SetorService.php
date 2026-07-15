<?php

declare(strict_types=1);

class SetorService
{
    public function __construct(
        private SetorRepository $setorRepository
    ) {
    }

    /**
     * Retorna os setores da organização.
     */
    public function listar(
        int $organizacaoId,
        string $busca = '',
        string $situacao = 'TODOS'
    ): array {
        $busca = trim($busca);

        $situacoesPermitidas = [
            'TODOS',
            'ATIVOS',
            'INATIVOS',
        ];

        $situacao = strtoupper(
            trim($situacao)
        );

        if (
            !in_array(
                $situacao,
                $situacoesPermitidas,
                true
            )
        ) {
            $situacao = 'TODOS';
        }

        return $this->setorRepository->listar(
            $organizacaoId,
            $busca,
            $situacao
        );
    }

    /**
     * Localiza um setor da organização.
     */
    public function buscarPorId(
        int $setorId,
        int $organizacaoId
    ): ?array {
        if ($setorId <= 0 || $organizacaoId <= 0) {
            return null;
        }

        return $this->setorRepository->buscarPorId(
            $setorId,
            $organizacaoId
        );
    }

    /**
     * Valida e cadastra um novo setor.
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

        $unidadePrincipal =
            $this->setorRepository
                ->buscarUnidadePrincipal(
                    $organizacaoId
                );

        if (!$unidadePrincipal) {
            $erros['unidade'] =
                'A unidade principal da organização não foi encontrada.';
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

        $unidadeId =
            (int) $unidadePrincipal['id'];

        if (
            $this->setorRepository->existeNome(
                $organizacaoId,
                $unidadeId,
                $dados['nome']
            )
        ) {
            return [
                'sucesso' => false,
                'mensagem' =>
                    'Já existe um setor com esse nome.',
                'erros' => [
                    'nome' =>
                        'Já existe um setor com esse nome.',
                ],
                'dados' => $dados,
            ];
        }

        if (
            $dados['sigla'] !== null
            && $this->setorRepository->existeSigla(
                $organizacaoId,
                $unidadeId,
                $dados['sigla']
            )
        ) {
            return [
                'sucesso' => false,
                'mensagem' =>
                    'A sigla informada já está sendo utilizada.',
                'erros' => [
                    'sigla' =>
                        'A sigla informada já está sendo utilizada.',
                ],
                'dados' => $dados,
            ];
        }

        try {
            $setorId =
                $this->setorRepository->criar([
                    'organizacao_id' =>
                        $organizacaoId,

                    'unidade_id' =>
                        $unidadeId,

                    'nome' =>
                        $dados['nome'],

                    'sigla' =>
                        $dados['sigla'],

                    'descricao' =>
                        $dados['descricao'],

                    'telefone' =>
                        $dados['telefone'],

                    'email' =>
                        $dados['email'],

                    'ativo' =>
                        $dados['ativo'],
                ]);

            return [
                'sucesso' => true,
                'mensagem' =>
                    'Setor cadastrado com sucesso.',
                'setor_id' => $setorId,
            ];
        } catch (PDOException $erro) {
            if ($erro->getCode() === '23505') {
                return [
                    'sucesso' => false,
                    'mensagem' =>
                        'Já existe um setor com os dados informados.',
                    'erros' => [],
                    'dados' => $dados,
                ];
            }

            throw $erro;
        }
    }

    /**
     * Valida e atualiza um setor.
     */
    public function atualizar(
        int $setorId,
        int $organizacaoId,
        array $dadosRecebidos
    ): array {
        $setorAtual =
            $this->setorRepository->buscarPorId(
                $setorId,
                $organizacaoId
            );

        if (!$setorAtual) {
            return [
                'sucesso' => false,
                'mensagem' =>
                    'O setor informado não foi encontrado.',
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

        $unidadeId =
            (int) $setorAtual['unidade_id'];

        if (
            $this->setorRepository->existeNome(
                $organizacaoId,
                $unidadeId,
                $dados['nome'],
                $setorId
            )
        ) {
            return [
                'sucesso' => false,
                'mensagem' =>
                    'Já existe outro setor com esse nome.',
                'erros' => [
                    'nome' =>
                        'Já existe outro setor com esse nome.',
                ],
                'dados' => $dados,
            ];
        }

        if (
            $dados['sigla'] !== null
            && $this->setorRepository->existeSigla(
                $organizacaoId,
                $unidadeId,
                $dados['sigla'],
                $setorId
            )
        ) {
            return [
                'sucesso' => false,
                'mensagem' =>
                    'A sigla informada já está sendo utilizada.',
                'erros' => [
                    'sigla' =>
                        'A sigla informada já está sendo utilizada.',
                ],
                'dados' => $dados,
            ];
        }

        try {
            $atualizado =
                $this->setorRepository->atualizar(
                    $setorId,
                    $organizacaoId,
                    $dados
                );

            if (!$atualizado) {
                return [
                    'sucesso' => false,
                    'mensagem' =>
                        'Nenhuma alteração foi realizada.',
                    'erros' => [],
                    'dados' => $dados,
                ];
            }

            return [
                'sucesso' => true,
                'mensagem' =>
                    'Setor atualizado com sucesso.',
            ];
        } catch (PDOException $erro) {
            if ($erro->getCode() === '23505') {
                return [
                    'sucesso' => false,
                    'mensagem' =>
                        'Já existe um setor com os dados informados.',
                    'erros' => [],
                    'dados' => $dados,
                ];
            }

            throw $erro;
        }
    }

    /**
     * Ativa ou desativa um setor.
     */
    public function alterarStatus(
        int $setorId,
        int $organizacaoId
    ): array {
        $setor =
            $this->setorRepository->buscarPorId(
                $setorId,
                $organizacaoId
            );

        if (!$setor) {
            return [
                'sucesso' => false,
                'mensagem' =>
                    'O setor informado não foi encontrado.',
            ];
        }

        $novoStatus = !(bool) $setor['ativo'];

        $alterado =
            $this->setorRepository->alterarStatus(
                $setorId,
                $organizacaoId,
                $novoStatus
            );

        if (!$alterado) {
            return [
                'sucesso' => false,
                'mensagem' =>
                    'Não foi possível alterar a situação do setor.',
            ];
        }

        return [
            'sucesso' => true,
            'mensagem' => $novoStatus
                ? 'Setor ativado com sucesso.'
                : 'Setor desativado com sucesso.',
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

        $sigla = strtoupper(
            trim(
                (string) ($dados['sigla'] ?? '')
            )
        );

        /*
         * Remove espaços internos da sigla.
         * Exemplo: "R H" torna-se "RH".
         */
        $sigla = preg_replace(
            '/\s+/',
            '',
            $sigla
        ) ?? '';

        $descricao = trim(
            (string) ($dados['descricao'] ?? '')
        );

        $telefone = trim(
            (string) ($dados['telefone'] ?? '')
        );

        $email = strtolower(
            trim(
                (string) ($dados['email'] ?? '')
            )
        );

        $ativoRecebido =
            $dados['ativo'] ?? true;

        $ativo = filter_var(
            $ativoRecebido,
            FILTER_VALIDATE_BOOL,
            FILTER_NULL_ON_FAILURE
        );

        return [
            'nome' => $nome,

            'sigla' =>
                $sigla !== ''
                    ? $sigla
                    : null,

            'descricao' =>
                $descricao !== ''
                    ? $descricao
                    : null,

            'telefone' =>
                $telefone !== ''
                    ? $telefone
                    : null,

            'email' =>
                $email !== ''
                    ? $email
                    : null,

            'ativo' =>
                $ativo ?? true,
        ];
    }

    /**
     * Valida os campos do setor.
     */
    private function validarDados(
        array $dados
    ): array {
        $erros = [];

        $nome = (string) $dados['nome'];

        if ($nome === '') {
            $erros['nome'] =
                'Informe o nome do setor.';
        } elseif (mb_strlen($nome) < 2) {
            $erros['nome'] =
                'O nome do setor deve possuir pelo menos 2 caracteres.';
        } elseif (mb_strlen($nome) > 120) {
            $erros['nome'] =
                'O nome do setor deve possuir no máximo 120 caracteres.';
        }

        if ($dados['sigla'] !== null) {
            $sigla = (string) $dados['sigla'];

            if (mb_strlen($sigla) > 30) {
                $erros['sigla'] =
                    'A sigla deve possuir no máximo 30 caracteres.';
            } elseif (
                !preg_match(
                    '/^[A-Z0-9_-]+$/',
                    $sigla
                )
            ) {
                $erros['sigla'] =
                    'Use somente letras, números, hífen ou sublinhado na sigla.';
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

        if (
            $dados['telefone'] !== null
            && mb_strlen(
                (string) $dados['telefone']
            ) > 20
        ) {
            $erros['telefone'] =
                'O telefone deve possuir no máximo 20 caracteres.';
        }

        if ($dados['email'] !== null) {
            $email = (string) $dados['email'];

            if (mb_strlen($email) > 150) {
                $erros['email'] =
                    'O e-mail deve possuir no máximo 150 caracteres.';
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

        return $erros;
    }
}