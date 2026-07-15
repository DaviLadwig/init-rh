BEGIN;

INSERT INTO unidades (
    organizacao_id,
    nome,
    sigla,
    descricao,
    principal,
    ativo
)
SELECT
    o.id,
    'Sede da Secretaria Municipal de Saúde',
    'SEDE',
    'Unidade interna correspondente ao prédio da Secretaria Municipal de Saúde.',
    TRUE,
    TRUE
FROM organizacoes o
WHERE o.codigo_acesso = 'saude-vitoria'
  AND o.ativo = TRUE
  AND o.excluido_em IS NULL

  AND NOT EXISTS (
      SELECT 1
      FROM unidades u
      WHERE u.organizacao_id = o.id
        AND u.principal = TRUE
        AND u.excluido_em IS NULL
  );

COMMIT;
