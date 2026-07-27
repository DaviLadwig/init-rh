BEGIN;

INSERT INTO tipos_documento (
    organizacao_id,
    nome,
    codigo,
    descricao,
    exige_validade,
    ativo,
    criado_em,
    atualizado_em
)
SELECT
    organizacao.id,
    'Registro profissional',
    'REGISTRO_PROFISSIONAL',
    'Registro em conselho profissional, como CRM, COREN, CRO, CRF, CREFITO, CRP ou outro conselho.',
    TRUE,
    TRUE,
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP

FROM organizacoes AS organizacao

WHERE NOT EXISTS (
    SELECT 1

    FROM tipos_documento
        AS tipo_existente

    WHERE tipo_existente.organizacao_id =
        organizacao.id

      AND tipo_existente.codigo =
        'REGISTRO_PROFISSIONAL'

      AND tipo_existente.excluido_em
        IS NULL
);

COMMIT;