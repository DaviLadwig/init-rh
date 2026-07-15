BEGIN;

INSERT INTO organizacoes (
    nome,
    nome_fantasia,
    tipo,
    cidade,
    estado
)
SELECT
    'Secretaria Municipal de Saúde',
    'Secretaria de Saúde',
    'PUBLICA',
    'Vitória do Mearim',
    'MA'
WHERE NOT EXISTS (
    SELECT 1
    FROM organizacoes
    WHERE nome = 'Secretaria Municipal de Saúde'
      AND excluido_em IS NULL
);

COMMIT;