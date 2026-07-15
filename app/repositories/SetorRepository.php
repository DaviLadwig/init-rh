<?php

declare(strict_types=1);

class SetorRepository
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    /**
     * Lista os setores pertencentes à organização.
     */
    public function listar(
        int $organizacaoId,
        string $busca = '',
        string $situacao = 'TODOS'
    ): array {
        $sql = '
            SELECT
                s.id,
                s.organizacao_id,
                s.unidade_id,
                s.nome,
                s.sigla,
                s.descricao,
                s.telefone,
                s.email,
                s.ativo,
                s.criado_em,
                s.atualizado_em,

                u.nome AS unidade_nome,
                u.sigla AS unidade_sigla

            FROM setores s

            INNER JOIN unidades u
                ON u.id = s.unidade_id
                AND u.organizacao_id = s.organizacao_id

            WHERE s.organizacao_id = :organizacao_id
              AND s.excluido_em IS NULL
              AND u.excluido_em IS NULL
        ';

        $parametros = [
            ':organizacao_id' => $organizacaoId,
        ];

        if ($busca !== '') {
            $sql .= '
                AND (
                    LOWER(s.nome) LIKE LOWER(:busca)
                    OR LOWER(COALESCE(s.sigla, \'\'))
                        LIKE LOWER(:busca)
                    OR LOWER(COALESCE(s.email, \'\'))
                        LIKE LOWER(:busca)
                )
            ';

            $parametros[':busca'] = '%' . $busca . '%';
        }

        if ($situacao === 'ATIVOS') {
            $sql .= ' AND s.ativo = TRUE ';
        }

        if ($situacao === 'INATIVOS') {
            $sql .= ' AND s.ativo = FALSE ';
        }

        $sql .= '
            ORDER BY
                s.ativo DESC,
                s.nome ASC
        ';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($parametros);

        return $stmt->fetchAll();
    }

    /**
     * Localiza um setor pelo ID, sempre respeitando a organização.
     */
    public function buscarPorId(
        int $setorId,
        int $organizacaoId
    ): ?array {
        $sql = '
            SELECT
                s.id,
                s.organizacao_id,
                s.unidade_id,
                s.nome,
                s.sigla,
                s.descricao,
                s.telefone,
                s.email,
                s.ativo,
                s.criado_em,
                s.atualizado_em,

                u.nome AS unidade_nome,
                u.sigla AS unidade_sigla

            FROM setores s

            INNER JOIN unidades u
                ON u.id = s.unidade_id
                AND u.organizacao_id = s.organizacao_id

            WHERE s.id = :setor_id
              AND s.organizacao_id = :organizacao_id
              AND s.excluido_em IS NULL
              AND u.excluido_em IS NULL

            LIMIT 1
        ';

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':setor_id' => $setorId,
            ':organizacao_id' => $organizacaoId,
        ]);

        $setor = $stmt->fetch();

        return $setor ?: null;
    }

    /**
     * Localiza a unidade principal ativa da organização.
     *
     * A unidade será usada internamente e não aparecerá
     * no formulário de cadastro do setor.
     */
    public function buscarUnidadePrincipal(
        int $organizacaoId
    ): ?array {
        $sql = '
            SELECT
                id,
                organizacao_id,
                nome,
                sigla,
                principal,
                ativo

            FROM unidades

            WHERE organizacao_id = :organizacao_id
              AND principal = TRUE
              AND ativo = TRUE
              AND excluido_em IS NULL

            LIMIT 1
        ';

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':organizacao_id' => $organizacaoId,
        ]);

        $unidade = $stmt->fetch();

        return $unidade ?: null;
    }

    /**
     * Verifica se já existe um setor com o mesmo nome
     * dentro da unidade principal.
     */
    public function existeNome(
        int $organizacaoId,
        int $unidadeId,
        string $nome,
        ?int $ignorarSetorId = null
    ): bool {
        $sql = '
            SELECT 1
            FROM setores

            WHERE organizacao_id = :organizacao_id
              AND unidade_id = :unidade_id
              AND LOWER(nome) = LOWER(:nome)
              AND excluido_em IS NULL
        ';

        $parametros = [
            ':organizacao_id' => $organizacaoId,
            ':unidade_id' => $unidadeId,
            ':nome' => $nome,
        ];

        if ($ignorarSetorId !== null) {
            $sql .= ' AND id <> :ignorar_setor_id ';

            $parametros[':ignorar_setor_id'] =
                $ignorarSetorId;
        }

        $sql .= ' LIMIT 1 ';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($parametros);

        return (bool) $stmt->fetchColumn();
    }

    /**
     * Verifica se a sigla informada já está em uso.
     */
    public function existeSigla(
        int $organizacaoId,
        int $unidadeId,
        string $sigla,
        ?int $ignorarSetorId = null
    ): bool {
        $sql = '
            SELECT 1
            FROM setores

            WHERE organizacao_id = :organizacao_id
              AND unidade_id = :unidade_id
              AND LOWER(sigla) = LOWER(:sigla)
              AND excluido_em IS NULL
        ';

        $parametros = [
            ':organizacao_id' => $organizacaoId,
            ':unidade_id' => $unidadeId,
            ':sigla' => $sigla,
        ];

        if ($ignorarSetorId !== null) {
            $sql .= ' AND id <> :ignorar_setor_id ';

            $parametros[':ignorar_setor_id'] =
                $ignorarSetorId;
        }

        $sql .= ' LIMIT 1 ';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($parametros);

        return (bool) $stmt->fetchColumn();
    }

    /**
     * Cadastra um novo setor.
     */
    public function criar(array $dados): int
    {
        $sql = '
            INSERT INTO setores (
                organizacao_id,
                unidade_id,
                nome,
                sigla,
                descricao,
                telefone,
                email,
                ativo
            )
            VALUES (
                :organizacao_id,
                :unidade_id,
                :nome,
                :sigla,
                :descricao,
                :telefone,
                :email,
                :ativo
            )
            RETURNING id
        ';

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':organizacao_id' =>
                $dados['organizacao_id'],

            ':unidade_id' =>
                $dados['unidade_id'],

            ':nome' =>
                $dados['nome'],

            ':sigla' =>
                $dados['sigla'],

            ':descricao' =>
                $dados['descricao'],

            ':telefone' =>
                $dados['telefone'],

            ':email' =>
                $dados['email'],

            ':ativo' =>
                $dados['ativo'],
        ]);

        return (int) $stmt->fetchColumn();
    }

    /**
     * Atualiza um setor da organização.
     */
    public function atualizar(
        int $setorId,
        int $organizacaoId,
        array $dados
    ): bool {
        $sql = '
            UPDATE setores
            SET
                nome = :nome,
                sigla = :sigla,
                descricao = :descricao,
                telefone = :telefone,
                email = :email,
                ativo = :ativo,
                atualizado_em = CURRENT_TIMESTAMP

            WHERE id = :setor_id
              AND organizacao_id = :organizacao_id
              AND excluido_em IS NULL
        ';

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':nome' => $dados['nome'],
            ':sigla' => $dados['sigla'],
            ':descricao' => $dados['descricao'],
            ':telefone' => $dados['telefone'],
            ':email' => $dados['email'],
            ':ativo' => $dados['ativo'],
            ':setor_id' => $setorId,
            ':organizacao_id' => $organizacaoId,
        ]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Ativa ou desativa um setor.
     */
    public function alterarStatus(
        int $setorId,
        int $organizacaoId,
        bool $ativo
    ): bool {
        $sql = '
            UPDATE setores
            SET
                ativo = :ativo,
                atualizado_em = CURRENT_TIMESTAMP

            WHERE id = :setor_id
              AND organizacao_id = :organizacao_id
              AND excluido_em IS NULL
        ';

        $stmt = $this->pdo->prepare($sql);

        $stmt->bindValue(
            ':ativo',
            $ativo,
            PDO::PARAM_BOOL
        );

        $stmt->bindValue(
            ':setor_id',
            $setorId,
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':organizacao_id',
            $organizacaoId,
            PDO::PARAM_INT
        );

        $stmt->execute();

        return $stmt->rowCount() > 0;
    }
}