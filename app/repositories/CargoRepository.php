<?php

declare(strict_types=1);

class CargoRepository
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    /**
     * Lista os cargos da organização.
     */
    public function listar(
        int $organizacaoId,
        string $busca = '',
        string $situacao = 'TODOS'
    ): array {
        $sql = '
            SELECT
                id,
                organizacao_id,
                nome,
                codigo,
                descricao,
                ativo,
                criado_em,
                atualizado_em
            FROM cargos
            WHERE organizacao_id = :organizacao_id
              AND excluido_em IS NULL
        ';

        $parametros = [
            ':organizacao_id' => $organizacaoId,
        ];

        if ($busca !== '') {
            $sql .= '
                AND (
                    LOWER(nome) LIKE LOWER(:busca)
                    OR LOWER(COALESCE(codigo, \'\'))
                        LIKE LOWER(:busca)
                    OR LOWER(COALESCE(descricao, \'\'))
                        LIKE LOWER(:busca)
                )
            ';

            $parametros[':busca'] = '%' . $busca . '%';
        }

        if ($situacao === 'ATIVOS') {
            $sql .= ' AND ativo = TRUE ';
        }

        if ($situacao === 'INATIVOS') {
            $sql .= ' AND ativo = FALSE ';
        }

        $sql .= '
            ORDER BY
                ativo DESC,
                nome ASC
        ';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($parametros);

        return $stmt->fetchAll();
    }

    /**
     * Busca um cargo por ID dentro da organização.
     */
    public function buscarPorId(
        int $cargoId,
        int $organizacaoId
    ): ?array {
        $sql = '
            SELECT
                id,
                organizacao_id,
                nome,
                codigo,
                descricao,
                ativo,
                criado_em,
                atualizado_em
            FROM cargos
            WHERE id = :cargo_id
              AND organizacao_id = :organizacao_id
              AND excluido_em IS NULL
            LIMIT 1
        ';

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':cargo_id' => $cargoId,
            ':organizacao_id' => $organizacaoId,
        ]);

        $cargo = $stmt->fetch();

        return $cargo ?: null;
    }

    /**
     * Verifica se o nome já está cadastrado.
     */
    public function existeNome(
        int $organizacaoId,
        string $nome,
        ?int $ignorarCargoId = null
    ): bool {
        $sql = '
            SELECT 1
            FROM cargos
            WHERE organizacao_id = :organizacao_id
              AND LOWER(nome) = LOWER(:nome)
              AND excluido_em IS NULL
        ';

        $parametros = [
            ':organizacao_id' => $organizacaoId,
            ':nome' => $nome,
        ];

        if ($ignorarCargoId !== null) {
            $sql .= ' AND id <> :ignorar_cargo_id ';

            $parametros[':ignorar_cargo_id'] =
                $ignorarCargoId;
        }

        $sql .= ' LIMIT 1 ';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($parametros);

        return (bool) $stmt->fetchColumn();
    }

    /**
     * Verifica se o código já está cadastrado.
     */
    public function existeCodigo(
        int $organizacaoId,
        string $codigo,
        ?int $ignorarCargoId = null
    ): bool {
        $sql = '
            SELECT 1
            FROM cargos
            WHERE organizacao_id = :organizacao_id
              AND LOWER(codigo) = LOWER(:codigo)
              AND excluido_em IS NULL
        ';

        $parametros = [
            ':organizacao_id' => $organizacaoId,
            ':codigo' => $codigo,
        ];

        if ($ignorarCargoId !== null) {
            $sql .= ' AND id <> :ignorar_cargo_id ';

            $parametros[':ignorar_cargo_id'] =
                $ignorarCargoId;
        }

        $sql .= ' LIMIT 1 ';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($parametros);

        return (bool) $stmt->fetchColumn();
    }

    /**
     * Cadastra um cargo.
     */
    public function criar(array $dados): int
    {
        $sql = '
            INSERT INTO cargos (
                organizacao_id,
                nome,
                codigo,
                descricao,
                ativo
            )
            VALUES (
                :organizacao_id,
                :nome,
                :codigo,
                :descricao,
                :ativo
            )
            RETURNING id
        ';

        $stmt = $this->pdo->prepare($sql);

        $stmt->bindValue(
            ':organizacao_id',
            (int) $dados['organizacao_id'],
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':nome',
            (string) $dados['nome']
        );

        $stmt->bindValue(
            ':codigo',
            $dados['codigo']
        );

        $stmt->bindValue(
            ':descricao',
            $dados['descricao']
        );

        $stmt->bindValue(
            ':ativo',
            (bool) $dados['ativo'],
            PDO::PARAM_BOOL
        );

        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    /**
     * Atualiza um cargo.
     */
    public function atualizar(
        int $cargoId,
        int $organizacaoId,
        array $dados
    ): bool {
        $sql = '
            UPDATE cargos
            SET
                nome = :nome,
                codigo = :codigo,
                descricao = :descricao,
                ativo = :ativo,
                atualizado_em = CURRENT_TIMESTAMP
            WHERE id = :cargo_id
              AND organizacao_id = :organizacao_id
              AND excluido_em IS NULL
        ';

        $stmt = $this->pdo->prepare($sql);

        $stmt->bindValue(
            ':nome',
            (string) $dados['nome']
        );

        $stmt->bindValue(
            ':codigo',
            $dados['codigo']
        );

        $stmt->bindValue(
            ':descricao',
            $dados['descricao']
        );

        $stmt->bindValue(
            ':ativo',
            (bool) $dados['ativo'],
            PDO::PARAM_BOOL
        );

        $stmt->bindValue(
            ':cargo_id',
            $cargoId,
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

    /**
     * Ativa ou desativa um cargo.
     */
    public function alterarStatus(
        int $cargoId,
        int $organizacaoId,
        bool $ativo
    ): bool {
        $sql = '
            UPDATE cargos
            SET
                ativo = :ativo,
                atualizado_em = CURRENT_TIMESTAMP
            WHERE id = :cargo_id
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
            ':cargo_id',
            $cargoId,
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