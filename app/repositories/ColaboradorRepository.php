<?php

declare(strict_types=1);

class ColaboradorRepository
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    /**
     * Lista os colaboradores pertencentes à organização.
     */
    public function listar(
        int $organizacaoId,
        string $busca = '',
        string $situacao = 'TODOS'
    ): array {
        $sql = '
            SELECT
                colaborador.id,
                colaborador.organizacao_id,
                colaborador.unidade_id,
                colaborador.nome_completo,
                colaborador.cpf,
                colaborador.data_nascimento,
                colaborador.email,
                colaborador.telefone,
                colaborador.matricula,
                colaborador.setor_id,
                colaborador.cargo_id,
                colaborador.funcao_id,
                colaborador.tipo_vinculo_id,
                colaborador.jornada_trabalho_id,
                colaborador.data_admissao,
                colaborador.data_fim_vinculo,
                colaborador.observacoes,
                colaborador.ativo,
                colaborador.criado_em,
                colaborador.atualizado_em,

                unidade.nome AS unidade_nome,
                setor.nome AS setor_nome,
                cargo.nome AS cargo_nome,
                funcao.nome AS funcao_nome,
                tipo_vinculo.nome AS tipo_vinculo_nome,
                jornada.nome AS jornada_nome,
                jornada.carga_horaria_semanal

            FROM colaboradores AS colaborador

            INNER JOIN unidades AS unidade
                ON unidade.id = colaborador.unidade_id
                AND unidade.organizacao_id =
                    colaborador.organizacao_id

            INNER JOIN setores AS setor
                ON setor.id = colaborador.setor_id
                AND setor.organizacao_id =
                    colaborador.organizacao_id

            INNER JOIN cargos AS cargo
                ON cargo.id = colaborador.cargo_id
                AND cargo.organizacao_id =
                    colaborador.organizacao_id

            LEFT JOIN funcoes AS funcao
                ON funcao.id = colaborador.funcao_id
                AND funcao.organizacao_id =
                    colaborador.organizacao_id

            INNER JOIN tipos_vinculo AS tipo_vinculo
                ON tipo_vinculo.id =
                    colaborador.tipo_vinculo_id
                AND tipo_vinculo.organizacao_id =
                    colaborador.organizacao_id

            INNER JOIN jornadas_trabalho AS jornada
                ON jornada.id =
                    colaborador.jornada_trabalho_id
                AND jornada.organizacao_id =
                    colaborador.organizacao_id

            WHERE colaborador.organizacao_id =
                :organizacao_id

              AND colaborador.excluido_em IS NULL
        ';

        $parametros = [
            ':organizacao_id' => $organizacaoId,
        ];

        $busca = trim($busca);

        if ($busca !== '') {
            $sql .= '
                AND (
                    colaborador.nome_completo
                        ILIKE :busca

                    OR colaborador.cpf
                        LIKE :busca

                    OR colaborador.matricula
                        ILIKE :busca

                    OR colaborador.email
                        ILIKE :busca

                    OR setor.nome
                        ILIKE :busca

                    OR cargo.nome
                        ILIKE :busca

                    OR tipo_vinculo.nome
                        ILIKE :busca

                    OR jornada.nome
                        ILIKE :busca
                )
            ';

            $parametros[':busca'] =
                '%' . $busca . '%';
        }

        if ($situacao === 'ATIVOS') {
            $sql .= '
                AND colaborador.ativo = TRUE
            ';
        } elseif ($situacao === 'INATIVOS') {
            $sql .= '
                AND colaborador.ativo = FALSE
            ';
        }

        $sql .= '
            ORDER BY
                colaborador.nome_completo ASC,
                colaborador.id ASC
        ';

        $stmt = $this->pdo->prepare($sql);

        foreach (
            $parametros as $chave => $valor
        ) {
            if ($chave === ':organizacao_id') {
                $stmt->bindValue(
                    $chave,
                    (int) $valor,
                    PDO::PARAM_INT
                );

                continue;
            }

            $stmt->bindValue(
                $chave,
                (string) $valor,
                PDO::PARAM_STR
            );
        }

        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Busca um colaborador pelo ID e pela organização.
     */
    public function buscarPorId(
        int $colaboradorId,
        int $organizacaoId
    ): ?array {
        $sql = '
            SELECT
                colaborador.id,
                colaborador.organizacao_id,
                colaborador.unidade_id,
                colaborador.nome_completo,
                colaborador.cpf,
                colaborador.data_nascimento,
                colaborador.email,
                colaborador.telefone,
                colaborador.matricula,
                colaborador.setor_id,
                colaborador.cargo_id,
                colaborador.funcao_id,
                colaborador.tipo_vinculo_id,
                colaborador.jornada_trabalho_id,
                colaborador.data_admissao,
                colaborador.data_fim_vinculo,
                colaborador.observacoes,
                colaborador.ativo,
                colaborador.criado_em,
                colaborador.atualizado_em,

                unidade.nome AS unidade_nome,
                setor.nome AS setor_nome,
                cargo.nome AS cargo_nome,
                funcao.nome AS funcao_nome,
                tipo_vinculo.nome AS tipo_vinculo_nome,
                tipo_vinculo.exige_data_fim,
                jornada.nome AS jornada_nome,
                jornada.carga_horaria_semanal

            FROM colaboradores AS colaborador

            INNER JOIN unidades AS unidade
                ON unidade.id = colaborador.unidade_id
                AND unidade.organizacao_id =
                    colaborador.organizacao_id

            INNER JOIN setores AS setor
                ON setor.id = colaborador.setor_id
                AND setor.organizacao_id =
                    colaborador.organizacao_id

            INNER JOIN cargos AS cargo
                ON cargo.id = colaborador.cargo_id
                AND cargo.organizacao_id =
                    colaborador.organizacao_id

            LEFT JOIN funcoes AS funcao
                ON funcao.id = colaborador.funcao_id
                AND funcao.organizacao_id =
                    colaborador.organizacao_id

            INNER JOIN tipos_vinculo AS tipo_vinculo
                ON tipo_vinculo.id =
                    colaborador.tipo_vinculo_id
                AND tipo_vinculo.organizacao_id =
                    colaborador.organizacao_id

            INNER JOIN jornadas_trabalho AS jornada
                ON jornada.id =
                    colaborador.jornada_trabalho_id
                AND jornada.organizacao_id =
                    colaborador.organizacao_id

            WHERE colaborador.id =
                :colaborador_id

              AND colaborador.organizacao_id =
                :organizacao_id

              AND colaborador.excluido_em IS NULL

            LIMIT 1
        ';

        $stmt = $this->pdo->prepare($sql);

        $stmt->bindValue(
            ':colaborador_id',
            $colaboradorId,
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':organizacao_id',
            $organizacaoId,
            PDO::PARAM_INT
        );

        $stmt->execute();

        $colaborador = $stmt->fetch();

        return $colaborador ?: null;
    }

    /**
     * Verifica se o CPF já está cadastrado na organização.
     */
    public function existeCpf(
        int $organizacaoId,
        string $cpf,
        ?int $ignorarColaboradorId = null
    ): bool {
        $sql = '
            SELECT 1
            FROM colaboradores
            WHERE organizacao_id =
                :organizacao_id

              AND cpf = :cpf

              AND excluido_em IS NULL
        ';

        if ($ignorarColaboradorId !== null) {
            $sql .= '
                AND id <> :ignorar_id
            ';
        }

        $sql .= '
            LIMIT 1
        ';

        $stmt = $this->pdo->prepare($sql);

        $stmt->bindValue(
            ':organizacao_id',
            $organizacaoId,
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':cpf',
            $cpf,
            PDO::PARAM_STR
        );

        if ($ignorarColaboradorId !== null) {
            $stmt->bindValue(
                ':ignorar_id',
                $ignorarColaboradorId,
                PDO::PARAM_INT
            );
        }

        $stmt->execute();

        return (bool) $stmt->fetchColumn();
    }

    /**
     * Verifica se a matrícula já está cadastrada.
     */
    public function existeMatricula(
        int $organizacaoId,
        string $matricula,
        ?int $ignorarColaboradorId = null
    ): bool {
        $sql = '
            SELECT 1
            FROM colaboradores
            WHERE organizacao_id =
                :organizacao_id

              AND LOWER(matricula) =
                LOWER(:matricula)

              AND excluido_em IS NULL
        ';

        if ($ignorarColaboradorId !== null) {
            $sql .= '
                AND id <> :ignorar_id
            ';
        }

        $sql .= '
            LIMIT 1
        ';

        $stmt = $this->pdo->prepare($sql);

        $stmt->bindValue(
            ':organizacao_id',
            $organizacaoId,
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':matricula',
            $matricula,
            PDO::PARAM_STR
        );

        if ($ignorarColaboradorId !== null) {
            $stmt->bindValue(
                ':ignorar_id',
                $ignorarColaboradorId,
                PDO::PARAM_INT
            );
        }

        $stmt->execute();

        return (bool) $stmt->fetchColumn();
    }

    /**
     * Cadastra um colaborador.
     */
    public function criar(
        array $dados
    ): int {
        $sql = '
            INSERT INTO colaboradores (
                organizacao_id,
                unidade_id,
                nome_completo,
                cpf,
                data_nascimento,
                email,
                telefone,
                matricula,
                setor_id,
                cargo_id,
                funcao_id,
                tipo_vinculo_id,
                jornada_trabalho_id,
                data_admissao,
                data_fim_vinculo,
                observacoes,
                ativo,
                criado_em,
                atualizado_em
            ) VALUES (
                :organizacao_id,
                :unidade_id,
                :nome_completo,
                :cpf,
                :data_nascimento,
                :email,
                :telefone,
                :matricula,
                :setor_id,
                :cargo_id,
                :funcao_id,
                :tipo_vinculo_id,
                :jornada_trabalho_id,
                :data_admissao,
                :data_fim_vinculo,
                :observacoes,
                :ativo,
                CURRENT_TIMESTAMP,
                CURRENT_TIMESTAMP
            )
            RETURNING id
        ';

        $stmt = $this->pdo->prepare($sql);

        $this->vincularDados(
            $stmt,
            $dados
        );

        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    /**
     * Atualiza um colaborador.
     */
    public function atualizar(
        int $colaboradorId,
        int $organizacaoId,
        array $dados
    ): bool {
        $sql = '
            UPDATE colaboradores
            SET
                unidade_id =
                    :unidade_id,

                nome_completo =
                    :nome_completo,

                cpf =
                    :cpf,

                data_nascimento =
                    :data_nascimento,

                email =
                    :email,

                telefone =
                    :telefone,

                matricula =
                    :matricula,

                setor_id =
                    :setor_id,

                cargo_id =
                    :cargo_id,

                funcao_id =
                    :funcao_id,

                tipo_vinculo_id =
                    :tipo_vinculo_id,

                jornada_trabalho_id =
                    :jornada_trabalho_id,

                data_admissao =
                    :data_admissao,

                data_fim_vinculo =
                    :data_fim_vinculo,

                observacoes =
                    :observacoes,

                ativo =
                    :ativo,

                atualizado_em =
                    CURRENT_TIMESTAMP

            WHERE id =
                :colaborador_id

              AND organizacao_id =
                :organizacao_id

              AND excluido_em IS NULL
        ';

        $stmt = $this->pdo->prepare($sql);

        $stmt->bindValue(
            ':colaborador_id',
            $colaboradorId,
            PDO::PARAM_INT
        );

        $this->vincularDados(
            $stmt,
            [
                ...$dados,
                'organizacao_id' => $organizacaoId,
            ]
        );

        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    /**
     * Ativa ou desativa o colaborador.
     */
    public function alterarStatus(
        int $colaboradorId,
        int $organizacaoId,
        bool $ativo
    ): bool {
        $sql = '
            UPDATE colaboradores
            SET
                ativo = :ativo,
                atualizado_em =
                    CURRENT_TIMESTAMP

            WHERE id =
                :colaborador_id

              AND organizacao_id =
                :organizacao_id

              AND excluido_em IS NULL
        ';

        $stmt = $this->pdo->prepare($sql);

        $stmt->bindValue(
            ':ativo',
            $ativo,
            PDO::PARAM_BOOL /*Evita o erro do PostgreSQL que tivemos anteriormente ao desativar registros.*/
        );

        $stmt->bindValue(
            ':colaborador_id',
            $colaboradorId,
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
     * Vincula os dados de criação ou atualização.
     */
    private function vincularDados(
        PDOStatement $stmt,
        array $dados
    ): void {
        $stmt->bindValue(
            ':organizacao_id',
            (int) $dados['organizacao_id'],
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':unidade_id',
            (int) $dados['unidade_id'],
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':nome_completo',
            (string) $dados['nome_completo'],
            PDO::PARAM_STR
        );

        $stmt->bindValue(
            ':cpf',
            (string) $dados['cpf'],
            PDO::PARAM_STR
        );

        $this->vincularTextoNulo(
            $stmt,
            ':data_nascimento',
            $dados['data_nascimento'] ?? null
        );

        $this->vincularTextoNulo(
            $stmt,
            ':email',
            $dados['email'] ?? null
        );

        $this->vincularTextoNulo(
            $stmt,
            ':telefone',
            $dados['telefone'] ?? null
        );

        $this->vincularTextoNulo(
            $stmt,
            ':matricula',
            $dados['matricula'] ?? null
        );

        $stmt->bindValue(
            ':setor_id',
            (int) $dados['setor_id'],
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':cargo_id',
            (int) $dados['cargo_id'],
            PDO::PARAM_INT
        );

        $this->vincularInteiroNulo(
            $stmt,
            ':funcao_id',
            $dados['funcao_id'] ?? null
        );

        $stmt->bindValue(
            ':tipo_vinculo_id',
            (int) $dados['tipo_vinculo_id'],
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':jornada_trabalho_id',
            (int) $dados['jornada_trabalho_id'],
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':data_admissao',
            (string) $dados['data_admissao'],
            PDO::PARAM_STR
        );

        $this->vincularTextoNulo(
            $stmt,
            ':data_fim_vinculo',
            $dados['data_fim_vinculo'] ?? null
        );

        $this->vincularTextoNulo(
            $stmt,
            ':observacoes',
            $dados['observacoes'] ?? null
        );

        $stmt->bindValue(
            ':ativo',
            (bool) $dados['ativo'],
            PDO::PARAM_BOOL
        );
    }

    /**
     * Vincula um texto que pode ser nulo.
     */
    private function vincularTextoNulo(
        PDOStatement $stmt,
        string $parametro,
        mixed $valor
    ): void {
        if (
            $valor === null
            || trim((string) $valor) === ''
        ) {
            $stmt->bindValue(
                $parametro,
                null,
                PDO::PARAM_NULL
            );

            return;
        }

        $stmt->bindValue(
            $parametro,
            (string) $valor,
            PDO::PARAM_STR
        );
    }

    /**
     * Vincula um número inteiro que pode ser nulo.
     */
    private function vincularInteiroNulo(
        PDOStatement $stmt,
        string $parametro,
        mixed $valor
    ): void {
        if (
            $valor === null
            || $valor === ''
        ) {
            $stmt->bindValue(
                $parametro,
                null,
                PDO::PARAM_NULL
            );

            return;
        }

        $stmt->bindValue(
            $parametro,
            (int) $valor,
            PDO::PARAM_INT
        );
    }
}