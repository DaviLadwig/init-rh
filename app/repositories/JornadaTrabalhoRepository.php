<?php

declare(strict_types=1);

class JornadaTrabalhoRepository
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    /**
     * Lista as jornadas de trabalho da organização.
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
                carga_horaria_semanal,
                descricao,
                ativo,
                criado_em,
                atualizado_em
            FROM jornadas_trabalho
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
                    OR CAST(
                        carga_horaria_semanal AS TEXT
                    ) LIKE :busca
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
     * Busca uma jornada pelo ID.
     */
    public function buscarPorId(
        int $jornadaId,
        int $organizacaoId
    ): ?array {
        $sql = '
            SELECT
                id,
                organizacao_id,
                nome,
                codigo,
                carga_horaria_semanal,
                descricao,
                ativo,
                criado_em,
                atualizado_em
            FROM jornadas_trabalho
            WHERE id = :jornada_id
              AND organizacao_id = :organizacao_id
              AND excluido_em IS NULL
            LIMIT 1
        ';

        $stmt = $this->pdo->prepare($sql);

        $stmt->bindValue(
            ':jornada_id',
            $jornadaId,
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':organizacao_id',
            $organizacaoId,
            PDO::PARAM_INT
        );

        $stmt->execute();

        $jornada = $stmt->fetch();

        return $jornada ?: null;
    }

    /**
     * Verifica se o nome já está cadastrado.
     */
    public function existeNome(
        int $organizacaoId,
        string $nome,
        ?int $ignorarJornadaId = null
    ): bool {
        $sql = '
            SELECT 1
            FROM jornadas_trabalho
            WHERE organizacao_id = :organizacao_id
              AND LOWER(nome) = LOWER(:nome)
              AND excluido_em IS NULL
        ';

        $parametros = [
            ':organizacao_id' => $organizacaoId,
            ':nome' => $nome,
        ];

        if ($ignorarJornadaId !== null) {
            $sql .= '
                AND id <> :ignorar_jornada_id
            ';

            $parametros[':ignorar_jornada_id'] =
                $ignorarJornadaId;
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
        ?int $ignorarJornadaId = null
    ): bool {
        $sql = '
            SELECT 1
            FROM jornadas_trabalho
            WHERE organizacao_id = :organizacao_id
              AND LOWER(codigo) = LOWER(:codigo)
              AND excluido_em IS NULL
        ';

        $parametros = [
            ':organizacao_id' => $organizacaoId,
            ':codigo' => $codigo,
        ];

        if ($ignorarJornadaId !== null) {
            $sql .= '
                AND id <> :ignorar_jornada_id
            ';

            $parametros[':ignorar_jornada_id'] =
                $ignorarJornadaId;
        }

        $sql .= ' LIMIT 1 ';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($parametros);

        return (bool) $stmt->fetchColumn();
    }

    /**
     * Cadastra uma jornada de trabalho.
     */
    public function criar(array $dados): int
    {
        $sql = '
            INSERT INTO jornadas_trabalho (
                organizacao_id,
                nome,
                codigo,
                carga_horaria_semanal,
                descricao,
                ativo
            )
            VALUES (
                :organizacao_id,
                :nome,
                :codigo,
                :carga_horaria_semanal,
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

        if ($dados['carga_horaria_semanal'] === null) {
            $stmt->bindValue(
                ':carga_horaria_semanal',
                null,
                PDO::PARAM_NULL
            );
        } else {
            /*
             * PDO não possui parâmetro específico para NUMERIC.
             * O valor decimal é enviado como string para preservar
             * corretamente casas decimais no PostgreSQL.
             */
            $stmt->bindValue(
                ':carga_horaria_semanal',
                (string) $dados['carga_horaria_semanal'],
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
            ':ativo',
            (bool) $dados['ativo'],
            PDO::PARAM_BOOL
        );

        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    /**
     * Atualiza uma jornada de trabalho.
     */
    public function atualizar(
        int $jornadaId,
        int $organizacaoId,
        array $dados
    ): bool {
        $sql = '
            UPDATE jornadas_trabalho
            SET
                nome = :nome,
                codigo = :codigo,
                carga_horaria_semanal =
                    :carga_horaria_semanal,
                descricao = :descricao,
                ativo = :ativo,
                atualizado_em = CURRENT_TIMESTAMP
            WHERE id = :jornada_id
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

        if ($dados['carga_horaria_semanal'] === null) {
            $stmt->bindValue(
                ':carga_horaria_semanal',
                null,
                PDO::PARAM_NULL
            );
        } else {
            $stmt->bindValue(
                ':carga_horaria_semanal',
                (string) $dados['carga_horaria_semanal'],
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
            ':ativo',
            (bool) $dados['ativo'],
            PDO::PARAM_BOOL
        );

        $stmt->bindValue(
            ':jornada_id',
            $jornadaId,
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
     * Ativa ou desativa uma jornada.
     */
    public function alterarStatus(
        int $jornadaId,
        int $organizacaoId,
        bool $ativo
    ): bool {
        $sql = '
            UPDATE jornadas_trabalho
            SET
                ativo = :ativo,
                atualizado_em = CURRENT_TIMESTAMP
            WHERE id = :jornada_id
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
            ':jornada_id',
            $jornadaId,
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