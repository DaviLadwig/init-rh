<?php

declare(strict_types=1);

class FuncaoRepository
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    /**
     * Lista as funções da organização.
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
            FROM funcoes
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
     * Busca uma função por ID dentro da organização.
     */
    public function buscarPorId(
        int $funcaoId,
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
            FROM funcoes
            WHERE id = :funcao_id
              AND organizacao_id = :organizacao_id
              AND excluido_em IS NULL
            LIMIT 1
        ';

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':funcao_id' => $funcaoId,
            ':organizacao_id' => $organizacaoId,
        ]);

        $funcao = $stmt->fetch();

        return $funcao ?: null;
    }

    /**
     * Verifica se o nome já está cadastrado.
     */
    public function existeNome(
        int $organizacaoId,
        string $nome,
        ?int $ignorarFuncaoId = null
    ): bool {
        $sql = '
            SELECT 1
            FROM funcoes
            WHERE organizacao_id = :organizacao_id
              AND LOWER(nome) = LOWER(:nome)
              AND excluido_em IS NULL
        ';

        $parametros = [
            ':organizacao_id' => $organizacaoId,
            ':nome' => $nome,
        ];

        if ($ignorarFuncaoId !== null) {
            $sql .= ' AND id <> :ignorar_funcao_id ';

            $parametros[':ignorar_funcao_id'] =
                $ignorarFuncaoId;
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
        ?int $ignorarFuncaoId = null
    ): bool {
        $sql = '
            SELECT 1
            FROM funcoes
            WHERE organizacao_id = :organizacao_id
              AND LOWER(codigo) = LOWER(:codigo)
              AND excluido_em IS NULL
        ';

        $parametros = [
            ':organizacao_id' => $organizacaoId,
            ':codigo' => $codigo,
        ];

        if ($ignorarFuncaoId !== null) {
            $sql .= ' AND id <> :ignorar_funcao_id ';

            $parametros[':ignorar_funcao_id'] =
                $ignorarFuncaoId;
        }

        $sql .= ' LIMIT 1 ';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($parametros);

        return (bool) $stmt->fetchColumn();
    }

    /**
     * Cadastra uma função.
     */
    public function criar(array $dados): int
    {
        $sql = '
            INSERT INTO funcoes (
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
     * Atualiza uma função.
     */
    public function atualizar(
        int $funcaoId,
        int $organizacaoId,
        array $dados
    ): bool {
        $sql = '
            UPDATE funcoes
            SET
                nome = :nome,
                codigo = :codigo,
                descricao = :descricao,
                ativo = :ativo,
                atualizado_em = CURRENT_TIMESTAMP
            WHERE id = :funcao_id
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
            ':funcao_id',
            $funcaoId,
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
     * Ativa ou desativa uma função.
     */
    public function alterarStatus(
        int $funcaoId,
        int $organizacaoId,
        bool $ativo
    ): bool {
        $sql = '
            UPDATE funcoes
            SET
                ativo = :ativo,
                atualizado_em = CURRENT_TIMESTAMP
            WHERE id = :funcao_id
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
            ':funcao_id',
            $funcaoId,
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