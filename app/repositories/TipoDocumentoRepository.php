<?php

declare(strict_types=1);

class TipoDocumentoRepository
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    /**
     * Lista os tipos de documento da organização.
     *
     * Permite busca por nome, código ou descrição
     * e filtro entre ativos e inativos.
     */
    public function listar(
        int $organizacaoId,
        string $busca = '',
        string $situacao = 'TODOS'
    ): array {
        $sql = '
            SELECT
                tipo_documento.id,
                tipo_documento.organizacao_id,
                tipo_documento.nome,
                tipo_documento.codigo,
                tipo_documento.descricao,
                tipo_documento.exige_validade,
                tipo_documento.ativo,
                tipo_documento.criado_em,
                tipo_documento.atualizado_em

            FROM tipos_documento
                AS tipo_documento

            WHERE tipo_documento.organizacao_id =
                :organizacao_id

              AND tipo_documento.excluido_em IS NULL
        ';

        $parametros = [
            ':organizacao_id' =>
                $organizacaoId,
        ];

        $busca = trim($busca);

        if ($busca !== '') {
            $sql .= '
                AND (
                    tipo_documento.nome
                        ILIKE :busca_nome

                    OR tipo_documento.codigo
                        ILIKE :busca_codigo

                    OR tipo_documento.descricao
                        ILIKE :busca_descricao
                )
            ';

            $termoBusca =
                '%' . $busca . '%';

            $parametros[':busca_nome'] =
                $termoBusca;

            $parametros[':busca_codigo'] =
                $termoBusca;

            $parametros[':busca_descricao'] =
                $termoBusca;
        }

        if ($situacao === 'ATIVOS') {
            $sql .= '
                AND tipo_documento.ativo = TRUE
            ';
        } elseif ($situacao === 'INATIVOS') {
            $sql .= '
                AND tipo_documento.ativo = FALSE
            ';
        }

        $sql .= '
            ORDER BY
                tipo_documento.nome ASC,
                tipo_documento.id ASC
        ';

        $stmt = $this->pdo->prepare($sql);

        foreach (
            $parametros as $parametro => $valor
        ) {
            if (
                $parametro
                === ':organizacao_id'
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
     * Lista apenas os tipos ativos.
     *
     * Este método será utilizado no formulário
     * de envio de documentos.
     */
    public function listarAtivos(
        int $organizacaoId
    ): array {
        if ($organizacaoId <= 0) {
            return [];
        }

        return $this->listar(
            $organizacaoId,
            '',
            'ATIVOS'
        );
    }

    /**
     * Busca um tipo de documento pelo ID e organização.
     *
     * O filtro por organização impede que um tipo
     * pertencente a outra organização seja utilizado.
     */
    public function buscarPorId(
        int $tipoDocumentoId,
        int $organizacaoId
    ): ?array {
        $sql = '
            SELECT
                tipo_documento.id,
                tipo_documento.organizacao_id,
                tipo_documento.nome,
                tipo_documento.codigo,
                tipo_documento.descricao,
                tipo_documento.exige_validade,
                tipo_documento.ativo,
                tipo_documento.criado_em,
                tipo_documento.atualizado_em

            FROM tipos_documento
                AS tipo_documento

            WHERE tipo_documento.id =
                :tipo_documento_id

              AND tipo_documento.organizacao_id =
                :organizacao_id

              AND tipo_documento.excluido_em IS NULL

            LIMIT 1
        ';

        $stmt = $this->pdo->prepare($sql);

        $stmt->bindValue(
            ':tipo_documento_id',
            $tipoDocumentoId,
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':organizacao_id',
            $organizacaoId,
            PDO::PARAM_INT
        );

        $stmt->execute();

        $tipoDocumento = $stmt->fetch(
            PDO::FETCH_ASSOC
        );

        return $tipoDocumento ?: null;
    }

    /**
     * Verifica se um tipo de documento está ativo
     * e pertence à organização.
     */
    public function estaAtivo(
        int $tipoDocumentoId,
        int $organizacaoId
    ): bool {
        $sql = '
            SELECT 1

            FROM tipos_documento

            WHERE id =
                :tipo_documento_id

              AND organizacao_id =
                :organizacao_id

              AND ativo = TRUE

              AND excluido_em IS NULL

            LIMIT 1
        ';

        $stmt = $this->pdo->prepare($sql);

        $stmt->bindValue(
            ':tipo_documento_id',
            $tipoDocumentoId,
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':organizacao_id',
            $organizacaoId,
            PDO::PARAM_INT
        );

        $stmt->execute();

        return (bool) $stmt->fetchColumn();
    }

    /**
     * Verifica se o tipo selecionado exige
     * uma data de validade.
     *
     * Retorna falso quando o registro não existe,
     * não pertence à organização ou foi excluído.
     */
    public function exigeValidade(
        int $tipoDocumentoId,
        int $organizacaoId
    ): bool {
        $sql = '
            SELECT exige_validade

            FROM tipos_documento

            WHERE id =
                :tipo_documento_id

              AND organizacao_id =
                :organizacao_id

              AND excluido_em IS NULL

            LIMIT 1
        ';

        $stmt = $this->pdo->prepare($sql);

        $stmt->bindValue(
            ':tipo_documento_id',
            $tipoDocumentoId,
            PDO::PARAM_INT
        );

        $stmt->bindValue(
            ':organizacao_id',
            $organizacaoId,
            PDO::PARAM_INT
        );

        $stmt->execute();

        $resultado = $stmt->fetchColumn();

        if ($resultado === false) {
            return false;
        }

        return $this->valorBooleano(
            $resultado
        );
    }

    /**
     * Converte os diferentes formatos booleanos
     * retornados pelo PostgreSQL.
     */
    private function valorBooleano(
        mixed $valor
    ): bool {
        if (is_bool($valor)) {
            return $valor;
        }

        if (is_int($valor)) {
            return $valor === 1;
        }

        return in_array(
            mb_strtolower(
                trim((string) $valor)
            ),
            [
                '1',
                'true',
                't',
                'yes',
                'sim',
                'on',
            ],
            true
        );
    }
}