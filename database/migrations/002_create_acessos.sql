BEGIN;

-- =========================================================
-- PERFIL: ADMINISTRADOR
-- =========================================================

INSERT INTO perfis (
    organizacao_id,
    nome,
    codigo,
    descricao
)
SELECT
    o.id,
    'Administrador',
    'ADMIN',
    'Acesso completo às configurações e módulos do sistema.'
FROM organizacoes o
WHERE o.nome = 'Secretaria Municipal de Saúde'
  AND o.excluido_em IS NULL
  AND NOT EXISTS (
      SELECT 1
      FROM perfis p
      WHERE p.organizacao_id = o.id
        AND p.codigo = 'ADMIN'
        AND p.excluido_em IS NULL
  );


-- =========================================================
-- PERFIL: RECURSOS HUMANOS
-- =========================================================

INSERT INTO perfis (
    organizacao_id,
    nome,
    codigo,
    descricao
)
SELECT
    o.id,
    'Recursos Humanos',
    'RH',
    'Responsável pela gestão dos colaboradores e rotinas de RH.'
FROM organizacoes o
WHERE o.nome = 'Secretaria Municipal de Saúde'
  AND o.excluido_em IS NULL
  AND NOT EXISTS (
      SELECT 1
      FROM perfis p
      WHERE p.organizacao_id = o.id
        AND p.codigo = 'RH'
        AND p.excluido_em IS NULL
  );


-- =========================================================
-- PERFIL: GESTOR
-- =========================================================

INSERT INTO perfis (
    organizacao_id,
    nome,
    codigo,
    descricao
)
SELECT
    o.id,
    'Gestor',
    'GESTOR',
    'Responsável por acompanhar unidades, setores ou equipes.'
FROM organizacoes o
WHERE o.nome = 'Secretaria Municipal de Saúde'
  AND o.excluido_em IS NULL
  AND NOT EXISTS (
      SELECT 1
      FROM perfis p
      WHERE p.organizacao_id = o.id
        AND p.codigo = 'GESTOR'
        AND p.excluido_em IS NULL
  );


-- =========================================================
-- PERFIL: COLABORADOR
-- =========================================================

INSERT INTO perfis (
    organizacao_id,
    nome,
    codigo,
    descricao
)
SELECT
    o.id,
    'Colaborador',
    'COLABORADOR',
    'Acesso ao portal pessoal do colaborador.'
FROM organizacoes o
WHERE o.nome = 'Secretaria Municipal de Saúde'
  AND o.excluido_em IS NULL
  AND NOT EXISTS (
      SELECT 1
      FROM perfis p
      WHERE p.organizacao_id = o.id
        AND p.codigo = 'COLABORADOR'
        AND p.excluido_em IS NULL
  );


-- =========================================================
-- PERFIL: SAÚDE OCUPACIONAL
-- =========================================================

INSERT INTO perfis (
    organizacao_id,
    nome,
    codigo,
    descricao
)
SELECT
    o.id,
    'Saúde Ocupacional',
    'SAUDE_OCUPACIONAL',
    'Acesso restrito aos registros de saúde ocupacional.'
FROM organizacoes o
WHERE o.nome = 'Secretaria Municipal de Saúde'
  AND o.excluido_em IS NULL
  AND NOT EXISTS (
      SELECT 1
      FROM perfis p
      WHERE p.organizacao_id = o.id
        AND p.codigo = 'SAUDE_OCUPACIONAL'
        AND p.excluido_em IS NULL
  );

COMMIT;