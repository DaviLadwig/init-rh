BEGIN;

-- =========================================================
-- PERMISSÕES DO MÓDULO DE DOCUMENTOS
-- =========================================================
--
-- As permissões visualizar e gerenciar já podem existir
-- em bancos criados pelas migrations anteriores.
--
-- Esta migration:
--
-- 1. Mantém e atualiza as permissões existentes;
-- 2. Cria baixar e excluir, caso ainda não existam;
-- 3. Concede as quatro permissões aos perfis que já
--    possuem documentos.gerenciar.
-- =========================================================


-- =========================================================
-- CRIA AS PERMISSÕES QUE AINDA NÃO EXISTEM
-- =========================================================

INSERT INTO permissoes (
    nome,
    codigo,
    modulo,
    descricao,
    ativo,
    criado_em,
    atualizado_em
)
SELECT
    nova_permissao.nome,
    nova_permissao.codigo,
    nova_permissao.modulo,
    nova_permissao.descricao,
    TRUE,
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP

FROM (
    VALUES
        (
            'Visualizar documentos',
            'documentos.visualizar',
            'DOCUMENTOS',
            'Permite visualizar a listagem e os metadados dos documentos dos colaboradores.'
        ),
        (
            'Gerenciar documentos',
            'documentos.gerenciar',
            'DOCUMENTOS',
            'Permite anexar, editar, ativar e desativar documentos dos colaboradores.'
        ),
        (
            'Baixar documentos',
            'documentos.baixar',
            'DOCUMENTOS',
            'Permite acessar e baixar o conteúdo dos documentos pessoais dos colaboradores.'
        ),
        (
            'Excluir documentos',
            'documentos.excluir',
            'DOCUMENTOS',
            'Permite realizar a exclusão lógica de documentos dos colaboradores.'
        )
) AS nova_permissao (
    nome,
    codigo,
    modulo,
    descricao
)

WHERE NOT EXISTS (
    SELECT 1

    FROM permissoes
        AS permissao_existente

    WHERE LOWER(
        permissao_existente.codigo
    ) = LOWER(
        nova_permissao.codigo
    )
);


-- =========================================================
-- ATUALIZA AS PERMISSÕES JÁ EXISTENTES
-- =========================================================

UPDATE permissoes

SET
    nome = CASE codigo

        WHEN 'documentos.visualizar'
            THEN 'Visualizar documentos'

        WHEN 'documentos.gerenciar'
            THEN 'Gerenciar documentos'

        WHEN 'documentos.baixar'
            THEN 'Baixar documentos'

        WHEN 'documentos.excluir'
            THEN 'Excluir documentos'

        ELSE nome
    END,

    modulo = 'DOCUMENTOS',

    descricao = CASE codigo

        WHEN 'documentos.visualizar'
            THEN
                'Permite visualizar a listagem e os metadados dos documentos dos colaboradores.'

        WHEN 'documentos.gerenciar'
            THEN
                'Permite anexar, editar, ativar e desativar documentos dos colaboradores.'

        WHEN 'documentos.baixar'
            THEN
                'Permite acessar e baixar o conteúdo dos documentos pessoais dos colaboradores.'

        WHEN 'documentos.excluir'
            THEN
                'Permite realizar a exclusão lógica de documentos dos colaboradores.'

        ELSE descricao
    END,

    ativo = TRUE,

    atualizado_em =
        CURRENT_TIMESTAMP

WHERE codigo IN (
    'documentos.visualizar',
    'documentos.gerenciar',
    'documentos.baixar',
    'documentos.excluir'
);


-- =========================================================
-- CONCEDE AS NOVAS PERMISSÕES AOS PERFIS GERENCIADORES
-- =========================================================
--
-- Apenas os perfis que já possuíam:
--
-- documentos.gerenciar
--
-- receberão baixar e excluir.
--
-- Um perfil que possui somente documentos.visualizar
-- continuará sem acesso ao conteúdo real dos arquivos.
-- =========================================================

WITH perfis_gerenciadores AS (
    SELECT DISTINCT
        perfil_permissao.perfil_id

    FROM perfil_permissoes
        AS perfil_permissao

    INNER JOIN permissoes
        AS permissao_atual

        ON permissao_atual.id =
            perfil_permissao.permissao_id

    WHERE permissao_atual.codigo =
        'documentos.gerenciar'

      AND permissao_atual.ativo = TRUE
),

permissoes_do_modulo AS (
    SELECT
        permissao.id
            AS permissao_id

    FROM permissoes
        AS permissao

    WHERE permissao.codigo IN (
        'documentos.visualizar',
        'documentos.gerenciar',
        'documentos.baixar',
        'documentos.excluir'
    )

      AND permissao.ativo = TRUE
)

INSERT INTO perfil_permissoes (
    perfil_id,
    permissao_id,
    criado_em
)
SELECT
    perfil_gerenciador.perfil_id,
    permissao_modulo.permissao_id,
    CURRENT_TIMESTAMP

FROM perfis_gerenciadores
    AS perfil_gerenciador

CROSS JOIN permissoes_do_modulo
    AS permissao_modulo

WHERE NOT EXISTS (
    SELECT 1

    FROM perfil_permissoes
        AS vinculo_existente

    WHERE vinculo_existente.perfil_id =
        perfil_gerenciador.perfil_id

      AND vinculo_existente.permissao_id =
        permissao_modulo.permissao_id
);

COMMIT;