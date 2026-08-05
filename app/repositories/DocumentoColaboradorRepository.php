<?php

declare(strict_types=1);

class DocumentoColaboradorRepository
{
    public function __construct(
        private PDO $pdo
    ) {
    }


    /**
     * Lista documentos de todos os colaboradores da organização.
     *
     * A consulta é paginada e nunca retorna documentos de
     * outra organização.
     */
    public function listarGeral(
        int $organizacaoId,
        string $busca = '',
        string $situacao = 'TODOS',
        string $validade = 'TODOS',
        int $tipoDocumentoId = 0,
        int $limite = 25,
        int $offset = 0,
        int $diasParaVencer = 30
    ): array {
        $filtros = $this->montarFiltrosGerais(
            $organizacaoId,
            $busca,
            $situacao,
            $validade,
            $tipoDocumentoId,
            $diasParaVencer
        );

        $limite = max(
            1,
            min($limite, 100)
        );

        $offset = max(
            0,
            $offset
        );

        $sql = '
            SELECT
                documento.id,
                documento.organizacao_id,
                documento.colaborador_id,
                documento.tipo_documento_id,
                documento.enviado_por_usuario_id,

                documento.titulo,
                documento.numero_documento,
                documento.data_emissao,
                documento.data_validade,
                documento.descricao,

                documento.arquivo_nome_original,
                documento.arquivo_nome_armazenado,
                documento.arquivo_caminho_relativo,
                documento.arquivo_extensao,
                documento.arquivo_mime,
                documento.arquivo_tamanho_bytes,
                documento.arquivo_hash_sha256,

                documento.ativo,
                documento.criado_em,
                documento.atualizado_em,

                colaborador.nome_completo
                    AS colaborador_nome,

                colaborador.matricula
                    AS colaborador_matricula,

                colaborador.ativo
                    AS colaborador_ativo,

                tipo_documento.nome
                    AS tipo_documento_nome,

                tipo_documento.codigo
                    AS tipo_documento_codigo,

                tipo_documento.exige_validade,

                usuario.nome
                    AS enviado_por_nome,

                CASE
                    WHEN documento.data_validade IS NULL
                        THEN \'SEM_VALIDADE\'

                    WHEN documento.data_validade < CURRENT_DATE
                        THEN \'VENCIDO\'

                    WHEN documento.data_validade <=
                        CURRENT_DATE
                        + (
                            CAST(
                                :dias_para_vencer_select
                                AS INTEGER
                            )
                            * INTERVAL \'1 day\'
                        )
                        THEN \'VENCENDO\'

                    ELSE \'VALIDO\'
                END AS situacao_validade,

                CASE
                    WHEN documento.data_validade IS NULL
                        THEN NULL

                    ELSE documento.data_validade
                        - CURRENT_DATE
                END AS dias_para_vencer

            FROM documentos_colaboradores
                AS documento

            INNER JOIN colaboradores
                AS colaborador
                ON colaborador.id =
                    documento.colaborador_id

                AND colaborador.organizacao_id =
                    documento.organizacao_id

            INNER JOIN tipos_documento
                AS tipo_documento
                ON tipo_documento.id =
                    documento.tipo_documento_id

                AND tipo_documento.organizacao_id =
                    documento.organizacao_id

            LEFT JOIN usuarios
                AS usuario
                ON usuario.id =
                    documento.enviado_por_usuario_id

            WHERE documento.excluido_em IS NULL
        ';

        $sql .= $filtros['sql'];

        $sql .= '
            ORDER BY
                CASE
                    WHEN documento.ativo = TRUE
                        AND documento.data_validade
                            < CURRENT_DATE
                        THEN 1

                    WHEN documento.ativo = TRUE
                        AND documento.data_validade
                            BETWEEN CURRENT_DATE
                            AND CURRENT_DATE
                                + (
                                    CAST(
                                        :dias_para_vencer_ordem
                                        AS INTEGER
                                    )
                                    * INTERVAL \'1 day\'
                                )
                        THEN 2

                    ELSE 3
                END,

                documento.data_validade ASC
                    NULLS LAST,

                colaborador.nome_completo ASC,

                documento.criado_em DESC,

                documento.id DESC

            LIMIT :limite
            OFFSET :offset
        ';

        $stmt = $this->pdo->prepare($sql);

        $parametros = $filtros['parametros'];

        $parametros[':dias_para_vencer_select'] =
            max(1, $diasParaVencer);

        $parametros[':dias_para_vencer_ordem'] =
            max(1, $diasParaVencer);

        $this->vincularParametrosConsulta(
            $stmt,
            $parametros
        );

        $stmt->bindValue(
            ':limite',
            $limite,
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':offset',
            $offset,
            PDO::PARAM_INT
        );

        $stmt->execute();

        return $stmt->fetchAll(
            PDO::FETCH_ASSOC
        );
    }

    /**
     * Conta os documentos da listagem geral usando
     * exatamente os mesmos filtros da consulta paginada.
     */
    public function contarGeral(
        int $organizacaoId,
        string $busca = '',
        string $situacao = 'TODOS',
        string $validade = 'TODOS',
        int $tipoDocumentoId = 0,
        int $diasParaVencer = 30
    ): int {
        $filtros = $this->montarFiltrosGerais(
            $organizacaoId,
            $busca,
            $situacao,
            $validade,
            $tipoDocumentoId,
            $diasParaVencer
        );

        $sql = '
            SELECT COUNT(*)

            FROM documentos_colaboradores
                AS documento

            INNER JOIN colaboradores
                AS colaborador
                ON colaborador.id =
                    documento.colaborador_id

                AND colaborador.organizacao_id =
                    documento.organizacao_id

            INNER JOIN tipos_documento
                AS tipo_documento
                ON tipo_documento.id =
                    documento.tipo_documento_id

                AND tipo_documento.organizacao_id =
                    documento.organizacao_id

            WHERE documento.excluido_em IS NULL
        ';

        $sql .= $filtros['sql'];

        $stmt = $this->pdo->prepare($sql);

        $this->vincularParametrosConsulta(
            $stmt,
            $filtros['parametros']
        );

        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    /**
     * Retorna os indicadores usados no cabeçalho da
     * tela central de documentos.
     *
     * Documentos inativos não entram nos alertas de
     * vencimento.
     */
    public function obterIndicadoresGerais(
        int $organizacaoId,
        int $diasParaVencer = 30
    ): array {
        $diasParaVencer = max(
            1,
            min($diasParaVencer, 365)
        );

        $sql = '
            SELECT
                COUNT(*) AS total,

                COUNT(*) FILTER (
                    WHERE documento.ativo = TRUE
                ) AS ativos,

                COUNT(*) FILTER (
                    WHERE documento.ativo = FALSE
                ) AS inativos,

                COUNT(*) FILTER (
                    WHERE documento.ativo = TRUE

                      AND documento.data_validade
                        < CURRENT_DATE
                ) AS vencidos,

                COUNT(*) FILTER (
                    WHERE documento.ativo = TRUE

                      AND documento.data_validade
                        BETWEEN CURRENT_DATE

                        AND CURRENT_DATE
                            + (
                                CAST(
                                    :dias_para_vencer
                                    AS INTEGER
                                )
                                * INTERVAL \'1 day\'
                            )
                ) AS vencendo,

                COUNT(*) FILTER (
                    WHERE documento.ativo = TRUE

                      AND documento.data_validade
                        IS NULL
                ) AS sem_validade

            FROM documentos_colaboradores
                AS documento

            WHERE documento.organizacao_id =
                :organizacao_id

              AND documento.excluido_em IS NULL
        ';

        $stmt = $this->pdo->prepare($sql);

        $stmt->bindValue(
            ':organizacao_id',
            $organizacaoId,
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':dias_para_vencer',
            $diasParaVencer,
            PDO::PARAM_INT
        );

        $stmt->execute();

        $indicadores = $stmt->fetch(
            PDO::FETCH_ASSOC
        );

        if (!is_array($indicadores)) {
            return [
                'total' => 0,
                'ativos' => 0,
                'inativos' => 0,
                'vencidos' => 0,
                'vencendo' => 0,
                'sem_validade' => 0,
            ];
        }

        return [
            'total' =>
                (int) ($indicadores['total'] ?? 0),

            'ativos' =>
                (int) ($indicadores['ativos'] ?? 0),

            'inativos' =>
                (int) ($indicadores['inativos'] ?? 0),

            'vencidos' =>
                (int) ($indicadores['vencidos'] ?? 0),

            'vencendo' =>
                (int) ($indicadores['vencendo'] ?? 0),

            'sem_validade' =>
                (int) (
                    $indicadores['sem_validade']
                    ?? 0
                ),
        ];
    }

    /**
     * Lista os documentos de um colaborador.
     *
     * A consulta é sempre limitada pela organização
     * para impedir acesso cruzado entre organizações.
     */
    public function listarPorColaborador(
        int $organizacaoId,
        int $colaboradorId,
        string $busca = '',
        string $situacao = 'TODOS'
    ): array {
        $sql = '
            SELECT
                documento.id,
                documento.organizacao_id,
                documento.colaborador_id,
                documento.tipo_documento_id,
                documento.enviado_por_usuario_id,

                documento.titulo,
                documento.numero_documento,
                documento.data_emissao,
                documento.data_validade,
                documento.descricao,

                documento.arquivo_nome_original,
                documento.arquivo_nome_armazenado,
                documento.arquivo_caminho_relativo,
                documento.arquivo_extensao,
                documento.arquivo_mime,
                documento.arquivo_tamanho_bytes,
                documento.arquivo_hash_sha256,

                documento.ativo,
                documento.criado_em,
                documento.atualizado_em,

                tipo_documento.nome
                    AS tipo_documento_nome,

                tipo_documento.codigo
                    AS tipo_documento_codigo,

                tipo_documento.exige_validade,

                usuario.nome
                    AS enviado_por_nome

            FROM documentos_colaboradores
                AS documento

            INNER JOIN tipos_documento
                AS tipo_documento
                ON tipo_documento.id =
                    documento.tipo_documento_id

                AND tipo_documento.organizacao_id =
                    documento.organizacao_id

            LEFT JOIN usuarios
                AS usuario
                ON usuario.id =
                    documento.enviado_por_usuario_id

            WHERE documento.organizacao_id =
                :organizacao_id

              AND documento.colaborador_id =
                :colaborador_id

              AND documento.excluido_em IS NULL
        ';

        $parametros = [
            ':organizacao_id' =>
                $organizacaoId,

            ':colaborador_id' =>
                $colaboradorId,
        ];

        $busca = trim($busca);

        if ($busca !== '') {
            $sql .= '
                AND (
                    documento.titulo
                        ILIKE :busca_titulo

                    OR documento.numero_documento
                        ILIKE :busca_numero

                    OR documento.arquivo_nome_original
                        ILIKE :busca_arquivo

                    OR tipo_documento.nome
                        ILIKE :busca_tipo
                )
            ';

            $termoBusca = '%' . $busca . '%';

            $parametros[':busca_titulo'] =
                $termoBusca;

            $parametros[':busca_numero'] =
                $termoBusca;

            $parametros[':busca_arquivo'] =
                $termoBusca;

            $parametros[':busca_tipo'] =
                $termoBusca;
        }

        if ($situacao === 'ATIVOS') {
            $sql .= '
                AND documento.ativo = TRUE
            ';
        } elseif ($situacao === 'INATIVOS') {
            $sql .= '
                AND documento.ativo = FALSE
            ';
        }

        $sql .= '
            ORDER BY
                documento.criado_em DESC,
                documento.id DESC
        ';

        $stmt = $this->pdo->prepare($sql);

        foreach (
            $parametros as $parametro => $valor
        ) {
            if (
                $parametro === ':organizacao_id'
                || $parametro === ':colaborador_id'
            ) {
                $stmt->bindValue(
                    $parametro,
                    (int) $valor,
                    PDO::PARAM_INT
                );

                continue;
            }

            $stmt->bindValue(
                $parametro,
                (string) $valor,
                PDO::PARAM_STR
            );
        }

        $stmt->execute();

        return $stmt->fetchAll(
            PDO::FETCH_ASSOC
        );
    }

    /**
     * Busca um documento pelo ID.
     *
     * Esta consulta será utilizada na visualização,
     * edição, exclusão e download protegido.
     */
    public function buscarPorId(
        int $documentoId,
        int $organizacaoId
    ): ?array {
        $sql = '
            SELECT
                documento.id,
                documento.organizacao_id,
                documento.colaborador_id,
                documento.tipo_documento_id,
                documento.enviado_por_usuario_id,

                documento.titulo,
                documento.numero_documento,
                documento.data_emissao,
                documento.data_validade,
                documento.descricao,

                documento.arquivo_nome_original,
                documento.arquivo_nome_armazenado,
                documento.arquivo_caminho_relativo,
                documento.arquivo_extensao,
                documento.arquivo_mime,
                documento.arquivo_tamanho_bytes,
                documento.arquivo_hash_sha256,

                documento.ativo,
                documento.criado_em,
                documento.atualizado_em,

                colaborador.nome_completo
                    AS colaborador_nome,

                tipo_documento.nome
                    AS tipo_documento_nome,

                tipo_documento.codigo
                    AS tipo_documento_codigo,

                tipo_documento.exige_validade,

                usuario.nome
                    AS enviado_por_nome

            FROM documentos_colaboradores
                AS documento

            INNER JOIN colaboradores
                AS colaborador
                ON colaborador.id =
                    documento.colaborador_id

                AND colaborador.organizacao_id =
                    documento.organizacao_id

            INNER JOIN tipos_documento
                AS tipo_documento
                ON tipo_documento.id =
                    documento.tipo_documento_id

                AND tipo_documento.organizacao_id =
                    documento.organizacao_id

            LEFT JOIN usuarios
                AS usuario
                ON usuario.id =
                    documento.enviado_por_usuario_id

            WHERE documento.id =
                :documento_id

              AND documento.organizacao_id =
                :organizacao_id

              AND documento.excluido_em IS NULL

            LIMIT 1
        ';

        $stmt = $this->pdo->prepare($sql);

        $stmt->bindValue(
            ':documento_id',
            $documentoId,
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':organizacao_id',
            $organizacaoId,
            PDO::PARAM_INT
        );

        $stmt->execute();

        $documento = $stmt->fetch(
            PDO::FETCH_ASSOC
        );

        return $documento ?: null;
    }

    /**
     * Verifica se um caminho relativo já está registrado.
     *
     * O nome físico será aleatório, mas esta verificação
     * adiciona uma camada extra contra colisões.
     */
    public function existeCaminho(
        int $organizacaoId,
        string $caminhoRelativo
    ): bool {
        $sql = '
            SELECT 1

            FROM documentos_colaboradores

            WHERE organizacao_id =
                :organizacao_id

              AND arquivo_caminho_relativo =
                :caminho_relativo

              AND excluido_em IS NULL

            LIMIT 1
        ';

        $stmt = $this->pdo->prepare($sql);

        $stmt->bindValue(
            ':organizacao_id',
            $organizacaoId,
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':caminho_relativo',
            $caminhoRelativo,
            PDO::PARAM_STR
        );

        $stmt->execute();

        return (bool) $stmt->fetchColumn();
    }

    /**
     * Verifica se o mesmo arquivo já foi anexado ao
     * mesmo colaborador.
     *
     * O hash SHA-256 identifica o conteúdo real do
     * arquivo, independentemente do nome.
     */
    public function existeHashNoColaborador(
        int $organizacaoId,
        int $colaboradorId,
        string $hashSha256
    ): bool {
        $sql = '
            SELECT 1

            FROM documentos_colaboradores

            WHERE organizacao_id =
                :organizacao_id

              AND colaborador_id =
                :colaborador_id

              AND arquivo_hash_sha256 =
                :arquivo_hash

              AND excluido_em IS NULL

            LIMIT 1
        ';

        $stmt = $this->pdo->prepare($sql);

        $stmt->bindValue(
            ':organizacao_id',
            $organizacaoId,
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':colaborador_id',
            $colaboradorId,
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':arquivo_hash',
            mb_strtolower(
                trim($hashSha256)
            ),
            PDO::PARAM_STR
        );

        $stmt->execute();

        return (bool) $stmt->fetchColumn();
    }

    /**
     * Registra os metadados de um novo documento.
     *
     * O arquivo físico já deverá ter sido validado pelo
     * service antes da chamada deste método.
     */
    public function criar(
        array $dados
    ): int {
        $sql = '
            INSERT INTO documentos_colaboradores (
                organizacao_id,
                colaborador_id,
                tipo_documento_id,
                enviado_por_usuario_id,

                titulo,
                numero_documento,
                data_emissao,
                data_validade,
                descricao,

                arquivo_nome_original,
                arquivo_nome_armazenado,
                arquivo_caminho_relativo,
                arquivo_extensao,
                arquivo_mime,
                arquivo_tamanho_bytes,
                arquivo_hash_sha256,

                ativo,
                criado_em,
                atualizado_em
            ) VALUES (
                :organizacao_id,
                :colaborador_id,
                :tipo_documento_id,
                :enviado_por_usuario_id,

                :titulo,
                :numero_documento,
                :data_emissao,
                :data_validade,
                :descricao,

                :arquivo_nome_original,
                :arquivo_nome_armazenado,
                :arquivo_caminho_relativo,
                :arquivo_extensao,
                :arquivo_mime,
                :arquivo_tamanho_bytes,
                :arquivo_hash_sha256,

                :ativo,
                CURRENT_TIMESTAMP,
                CURRENT_TIMESTAMP
            )

            RETURNING id
        ';

        $stmt = $this->pdo->prepare($sql);

        $this->vincularDadosCriacao(
            $stmt,
            $dados
        );

        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    /**
     * Atualiza somente os dados descritivos.
     *
     * O arquivo físico e seus metadados de integridade
     * não são alterados por este método.
     */
    public function atualizarMetadados(
        int $documentoId,
        int $organizacaoId,
        array $dados
    ): bool {
        $sql = '
            UPDATE documentos_colaboradores

            SET
                tipo_documento_id =
                    :tipo_documento_id,

                titulo =
                    :titulo,

                numero_documento =
                    :numero_documento,

                data_emissao =
                    :data_emissao,

                data_validade =
                    :data_validade,

                descricao =
                    :descricao,

                atualizado_em =
                    CURRENT_TIMESTAMP

            WHERE id =
                :documento_id

              AND organizacao_id =
                :organizacao_id

              AND excluido_em IS NULL
        ';

        $stmt = $this->pdo->prepare($sql);

        $stmt->bindValue(
            ':documento_id',
            $documentoId,
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':organizacao_id',
            $organizacaoId,
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':tipo_documento_id',
            (int) $dados['tipo_documento_id'],
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':titulo',
            (string) $dados['titulo'],
            PDO::PARAM_STR
        );

        $this->vincularTextoNulo(
            $stmt,
            ':numero_documento',
            $dados['numero_documento']
                ?? null
        );

        $this->vincularTextoNulo(
            $stmt,
            ':data_emissao',
            $dados['data_emissao']
                ?? null
        );

        $this->vincularTextoNulo(
            $stmt,
            ':data_validade',
            $dados['data_validade']
                ?? null
        );

        $this->vincularTextoNulo(
            $stmt,
            ':descricao',
            $dados['descricao']
                ?? null
        );

        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    /**
     * Ativa ou desativa um documento.
     */
    public function alterarStatus(
        int $documentoId,
        int $organizacaoId,
        bool $ativo
    ): bool {
        $sql = '
            UPDATE documentos_colaboradores

            SET
                ativo =
                    :ativo,

                atualizado_em =
                    CURRENT_TIMESTAMP

            WHERE id =
                :documento_id

              AND organizacao_id =
                :organizacao_id

              AND excluido_em IS NULL
        ';

        $stmt = $this->pdo->prepare($sql);

        $stmt->bindValue(
            ':ativo',
            $ativo,
            PDO::PARAM_BOOL
        );

        $stmt->bindValue(
            ':documento_id',
            $documentoId,
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
     * Realiza a exclusão lógica do registro.
     *
     * A remoção do arquivo físico será controlada pelo
     * service depois das verificações de segurança.
     */
    public function marcarComoExcluido(
        int $documentoId,
        int $organizacaoId
    ): bool {
        $sql = '
            UPDATE documentos_colaboradores

            SET
                ativo =
                    FALSE,

                excluido_em =
                    CURRENT_TIMESTAMP,

                atualizado_em =
                    CURRENT_TIMESTAMP

            WHERE id =
                :documento_id

              AND organizacao_id =
                :organizacao_id

              AND excluido_em IS NULL
        ';

        $stmt = $this->pdo->prepare($sql);

        $stmt->bindValue(
            ':documento_id',
            $documentoId,
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
     * Monta os filtros compartilhados pela listagem
     * geral e pela contagem.
     */
    private function montarFiltrosGerais(
        int $organizacaoId,
        string $busca,
        string $situacao,
        string $validade,
        int $tipoDocumentoId,
        int $diasParaVencer
    ): array {
        $sql = '
            AND documento.organizacao_id =
                :organizacao_id
        ';

        $parametros = [
            ':organizacao_id' =>
                $organizacaoId,
        ];

        $busca = trim($busca);

        if ($busca !== '') {
            $sql .= '
                AND (
                    documento.titulo
                        ILIKE :busca_titulo

                    OR documento.numero_documento
                        ILIKE :busca_numero

                    OR documento.arquivo_nome_original
                        ILIKE :busca_arquivo

                    OR tipo_documento.nome
                        ILIKE :busca_tipo

                    OR colaborador.nome_completo
                        ILIKE :busca_colaborador

                    OR colaborador.matricula
                        ILIKE :busca_matricula
                )
            ';

            $termoBusca =
                '%' . $busca . '%';

            $parametros[':busca_titulo'] =
                $termoBusca;

            $parametros[':busca_numero'] =
                $termoBusca;

            $parametros[':busca_arquivo'] =
                $termoBusca;

            $parametros[':busca_tipo'] =
                $termoBusca;

            $parametros[':busca_colaborador'] =
                $termoBusca;

            $parametros[':busca_matricula'] =
                $termoBusca;
        }

        $situacao = strtoupper(
            trim($situacao)
        );

        if ($situacao === 'ATIVOS') {
            $sql .= '
                AND documento.ativo = TRUE
            ';
        } elseif ($situacao === 'INATIVOS') {
            $sql .= '
                AND documento.ativo = FALSE
            ';
        }

        if ($tipoDocumentoId > 0) {
            $sql .= '
                AND documento.tipo_documento_id =
                    :tipo_documento_id
            ';

            $parametros[':tipo_documento_id'] =
                $tipoDocumentoId;
        }

        $validade = strtoupper(
            trim($validade)
        );

        $diasParaVencer = max(
            1,
            min($diasParaVencer, 365)
        );

        if ($validade === 'VENCIDOS') {
            $sql .= '
                AND documento.data_validade
                    < CURRENT_DATE
            ';
        } elseif ($validade === 'VENCENDO') {
            $sql .= '
                AND documento.data_validade
                    BETWEEN CURRENT_DATE

                    AND CURRENT_DATE
                        + (
                            CAST(
                                :dias_para_vencer_filtro
                                AS INTEGER
                            )
                            * INTERVAL \'1 day\'
                        )
            ';

            $parametros[':dias_para_vencer_filtro'] =
                $diasParaVencer;
        } elseif ($validade === 'VALIDOS') {
            $sql .= '
                AND documento.data_validade
                    > CURRENT_DATE
                        + (
                            CAST(
                                :dias_para_vencer_filtro
                                AS INTEGER
                            )
                            * INTERVAL \'1 day\'
                        )
            ';

            $parametros[':dias_para_vencer_filtro'] =
                $diasParaVencer;
        } elseif (
            $validade === 'SEM_VALIDADE'
        ) {
            $sql .= '
                AND documento.data_validade
                    IS NULL
            ';
        }

        return [
            'sql' => $sql,
            'parametros' => $parametros,
        ];
    }

    /**
     * Vincula parâmetros inteiros e textuais usados nas
     * consultas de listagem e contagem.
     */
    private function vincularParametrosConsulta(
        PDOStatement $stmt,
        array $parametros
    ): void {
        $parametrosInteiros = [
            ':organizacao_id',
            ':tipo_documento_id',
            ':dias_para_vencer_filtro',
            ':dias_para_vencer_select',
            ':dias_para_vencer_ordem',
        ];

        foreach (
            $parametros as $parametro => $valor
        ) {
            $stmt->bindValue(
                $parametro,
                in_array(
                    $parametro,
                    $parametrosInteiros,
                    true
                )
                    ? (int) $valor
                    : (string) $valor,
                in_array(
                    $parametro,
                    $parametrosInteiros,
                    true
                )
                    ? PDO::PARAM_INT
                    : PDO::PARAM_STR
            );
        }
    }

    /**
     * Vincula os dados usados na criação.
     */
    private function vincularDadosCriacao(
        PDOStatement $stmt,
        array $dados
    ): void {
        $stmt->bindValue(
            ':organizacao_id',
            (int) $dados['organizacao_id'],
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':colaborador_id',
            (int) $dados['colaborador_id'],
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':tipo_documento_id',
            (int) $dados['tipo_documento_id'],
            PDO::PARAM_INT
        );

        $this->vincularInteiroNulo(
            $stmt,
            ':enviado_por_usuario_id',
            $dados['enviado_por_usuario_id']
                ?? null
        );

        $stmt->bindValue(
            ':titulo',
            (string) $dados['titulo'],
            PDO::PARAM_STR
        );

        $this->vincularTextoNulo(
            $stmt,
            ':numero_documento',
            $dados['numero_documento']
                ?? null
        );

        $this->vincularTextoNulo(
            $stmt,
            ':data_emissao',
            $dados['data_emissao']
                ?? null
        );

        $this->vincularTextoNulo(
            $stmt,
            ':data_validade',
            $dados['data_validade']
                ?? null
        );

        $this->vincularTextoNulo(
            $stmt,
            ':descricao',
            $dados['descricao']
                ?? null
        );

        $stmt->bindValue(
            ':arquivo_nome_original',
            (string) $dados[
                'arquivo_nome_original'
            ],
            PDO::PARAM_STR
        );

        $stmt->bindValue(
            ':arquivo_nome_armazenado',
            (string) $dados[
                'arquivo_nome_armazenado'
            ],
            PDO::PARAM_STR
        );

        $stmt->bindValue(
            ':arquivo_caminho_relativo',
            (string) $dados[
                'arquivo_caminho_relativo'
            ],
            PDO::PARAM_STR
        );

        $stmt->bindValue(
            ':arquivo_extensao',
            (string) $dados[
                'arquivo_extensao'
            ],
            PDO::PARAM_STR
        );

        $stmt->bindValue(
            ':arquivo_mime',
            (string) $dados[
                'arquivo_mime'
            ],
            PDO::PARAM_STR
        );

        $stmt->bindValue(
            ':arquivo_tamanho_bytes',
            (int) $dados[
                'arquivo_tamanho_bytes'
            ],
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':arquivo_hash_sha256',
            mb_strtolower(
                trim(
                    (string) $dados[
                        'arquivo_hash_sha256'
                    ]
                )
            ),
            PDO::PARAM_STR
        );

        $stmt->bindValue(
            ':ativo',
            (bool) (
                $dados['ativo']
                ?? true
            ),
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
     * Vincula um inteiro que pode ser nulo.
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

/*
esse respositório:

Listar documentos por colaborador
Buscar por título, número, tipo ou nome do arquivo
Filtrar ativos e inativos
Buscar documento para visualização ou download
Impedir caminhos duplicados
Detectar arquivo duplicado pelo SHA-256
Registrar metadados do upload
Editar dados descritivos
Ativar e desativar
Realizar exclusão lógica
Restringir todas as consultas pela organização

*/