BEGIN;

-- =========================================================
-- VINCULA TODAS AS PERMISSÕES AO PERFIL ADMIN
-- =========================================================

INSERT INTO perfil_permissoes (
    perfil_id,
    permissao_id
)
SELECT
    p.id,
    pe.id
FROM perfis p
CROSS JOIN permissoes pe
WHERE p.codigo = 'ADMIN'
  AND p.organizacao_id = 1
  AND p.ativo = TRUE
  AND p.excluido_em IS NULL
  AND pe.ativo = TRUE
ON CONFLICT (
    perfil_id,
    permissao_id
)
DO NOTHING;

COMMIT;