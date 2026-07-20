<?php

declare(strict_types=1);

class TipoVinculoRepository
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    /**
     * Lista os tipos de vínculo pertencentes à organização.
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
                exige_data_fim,
                ativo,
                criado_em,
                atualizado_em
            FROM tipos_vinculo
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

            $parametros[':busca'] =
                '%' . $busca . '%';
        }

        if ($situacao === 'ATIVOS') {
            $sql .= '
                AND ativo = TRUE
            ';
        }

        if ($situacao === 'INATIVOS') {
            $sql .= '
                AND ativo = FALSE
            ';
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
     * Busca um tipo de vínculo pelo ID.
     */
    public function buscarPorId(
        int $tipoVinculoId,
        int $organizacaoId
    ): ?array {
        $sql = '
            SELECT
                id,
                organizacao_id,
                nome,
                codigo,
                descricao,
                exige_data_fim,
                ativo,
                criado_em,
                atualizado_em
            FROM tipos_vinculo
            WHERE id = :tipo_vinculo_id
              AND organizacao_id = :organizacao_id
              AND excluido_em IS NULL
            LIMIT 1
        ';

        $stmt = $this->pdo->prepare($sql);

        $stmt->bindValue(
            ':tipo_vinculo_id',
            $tipoVinculoId,
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':organizacao_id',
            $organizacaoId,
            PDO::PARAM_INT
        );

        $stmt->execute();

        $tipoVinculo = $stmt->fetch();

        return $tipoVinculo ?: null;
    }

    /**
     * Verifica se o nome já está cadastrado.
     */
    public function existeNome(
        int $organizacaoId,
        string $nome,
        ?int $ignorarTipoVinculoId = null
    ): bool {
        $sql = '
            SELECT 1
            FROM tipos_vinculo
            WHERE organizacao_id = :organizacao_id
              AND LOWER(nome) = LOWER(:nome)
              AND excluido_em IS NULL
        ';

        $parametros = [
            ':organizacao_id' => $organizacaoId,
            ':nome' => $nome,
        ];

        if ($ignorarTipoVinculoId !== null) {
            $sql .= '
                AND id <> :ignorar_tipo_vinculo_id
            ';

            $parametros[':ignorar_tipo_vinculo_id'] =
                $ignorarTipoVinculoId;
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
        ?int $ignorarTipoVinculoId = null
    ): bool {
        $sql = '
            SELECT 1
            FROM tipos_vinculo
            WHERE organizacao_id = :organizacao_id
              AND LOWER(codigo) = LOWER(:codigo)
              AND excluido_em IS NULL
        ';

        $parametros = [
            ':organizacao_id' => $organizacaoId,
            ':codigo' => $codigo,
        ];

        if ($ignorarTipoVinculoId !== null) {
            $sql .= '
                AND id <> :ignorar_tipo_vinculo_id
            ';

            $parametros[':ignorar_tipo_vinculo_id'] =
                $ignorarTipoVinculoId;
        }

        $sql .= ' LIMIT 1 ';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($parametros);

        return (bool) $stmt->fetchColumn();
    }

    /**
     * Cadastra um tipo de vínculo.
     */
    public function criar(array $dados): int
    {
        $sql = '
            INSERT INTO tipos_vinculo (
                organizacao_id,
                nome,
                codigo,
                descricao,
                exige_data_fim,
                ativo
            )
            VALUES (
                :organizacao_id,
                :nome,
                :codigo,
                :descricao,
                :exige_data_fim,
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
            (string) $dados['nome'],
            PDO::PARAM_STR
        );

        if ($dados['codigo'] === null) {
            $stmt->bindValue(
                ':codigo',
                null,
                PDO::PARAM_NULL
            );
        } else {
            $stmt->bindValue(
                ':codigo',
                (string) $dados['codigo'],
                PDO::PARAM_STR
            );
        }

        if ($dados['descricao'] === null) {
            $stmt->bindValue(
                ':descricao',
                null,
                PDO::PARAM_NULL
            );
        } else {
            $stmt->bindValue(
                ':descricao',
                (string) $dados['descricao'],
                PDO::PARAM_STR
            );
        }

        $stmt->bindValue(
            ':exige_data_fim',
            (bool) $dados['exige_data_fim'],
            PDO::PARAM_BOOL
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
     * Atualiza um tipo de vínculo.
     */
    public function atualizar(
        int $tipoVinculoId,
        int $organizacaoId,
        array $dados
    ): bool {
        $sql = '
            UPDATE tipos_vinculo
            SET
                nome = :nome,
                codigo = :codigo,
                descricao = :descricao,
                exige_data_fim = :exige_data_fim,
                ativo = :ativo,
                atualizado_em = CURRENT_TIMESTAMP
            WHERE id = :tipo_vinculo_id
              AND organizacao_id = :organizacao_id
              AND excluido_em IS NULL
        ';

        $stmt = $this->pdo->prepare($sql);

        $stmt->bindValue(
            ':nome',
            (string) $dados['nome'],
            PDO::PARAM_STR
        );

        if ($dados['codigo'] === null) {
            $stmt->bindValue(
                ':codigo',
                null,
                PDO::PARAM_NULL
            );
        } else {
            $stmt->bindValue(
                ':codigo',
                (string) $dados['codigo'],
                PDO::PARAM_STR
            );
        }

        if ($dados['descricao'] === null) {
            $stmt->bindValue(
                ':descricao',
                null,
                PDO::PARAM_NULL
            );
        } else {
            $stmt->bindValue(
                ':descricao',
                (string) $dados['descricao'],
                PDO::PARAM_STR
            );
        }

        $stmt->bindValue(
            ':exige_data_fim',
            (bool) $dados['exige_data_fim'],
            PDO::PARAM_BOOL
        );

        $stmt->bindValue(
            ':ativo',
            (bool) $dados['ativo'],
            PDO::PARAM_BOOL
        );

        $stmt->bindValue(
            ':tipo_vinculo_id',
            $tipoVinculoId,
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
     * Ativa ou desativa um tipo de vínculo.
     */
    public function alterarStatus(
        int $tipoVinculoId,
        int $organizacaoId,
        bool $ativo
    ): bool {
        $sql = '
            UPDATE tipos_vinculo
            SET
                ativo = :ativo,
                atualizado_em = CURRENT_TIMESTAMP
            WHERE id = :tipo_vinculo_id
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
            ':tipo_vinculo_id',
            $tipoVinculoId,
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