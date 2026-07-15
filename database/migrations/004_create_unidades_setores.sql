BEGIN;

-- =========================================================
-- TABELA: UNIDADES
-- =========================================================
--
-- Neste primeiro momento existirá somente uma unidade:
-- "Sede da Secretaria Municipal de Saúde".
--
-- A unidade ficará oculta nas telas, mas permitirá uma
-- expansão futura sem alterar toda a estrutura do sistema.
-- =========================================================

CREATE TABLE unidades (
    id BIGSERIAL PRIMARY KEY,

    organizacao_id BIGINT NOT NULL,

    nome VARCHAR(150) NOT NULL,
    sigla VARCHAR(30),

    descricao TEXT,

    principal BOOLEAN
        NOT NULL
        DEFAULT FALSE,

    ativo BOOLEAN
        NOT NULL
        DEFAULT TRUE,

    criado_em TIMESTAMPTZ
        NOT NULL
        DEFAULT CURRENT_TIMESTAMP,

    atualizado_em TIMESTAMPTZ
        NOT NULL
        DEFAULT CURRENT_TIMESTAMP,

    excluido_em TIMESTAMPTZ,

    CONSTRAINT fk_unidades_organizacao
        FOREIGN KEY (organizacao_id)
        REFERENCES organizacoes(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    /*
     * Esta chave composta permitirá garantir que um setor
     * nunca seja vinculado a uma unidade de outra organização.
     */
    CONSTRAINT uq_unidades_id_organizacao
        UNIQUE (
            id,
            organizacao_id
        ),

    CONSTRAINT chk_unidades_sigla
        CHECK (
            sigla IS NULL
            OR sigla ~ '^[A-Z0-9_-]+$'
        )
);

CREATE UNIQUE INDEX uq_unidades_nome_organizacao
    ON unidades (
        organizacao_id,
        LOWER(nome)
    )
    WHERE excluido_em IS NULL;

CREATE UNIQUE INDEX uq_unidades_sigla_organizacao
    ON unidades (
        organizacao_id,
        LOWER(sigla)
    )
    WHERE sigla IS NOT NULL
      AND excluido_em IS NULL;

/*
 * Permite apenas uma unidade principal ativa
 * por organização.
 */
CREATE UNIQUE INDEX uq_unidades_principal_organizacao
    ON unidades (organizacao_id)
    WHERE principal = TRUE
      AND excluido_em IS NULL;

CREATE INDEX idx_unidades_organizacao
    ON unidades (organizacao_id);

CREATE INDEX idx_unidades_ativo
    ON unidades (ativo);


-- =========================================================
-- TABELA: SETORES
-- =========================================================
--
-- Os setores representarão apenas os departamentos internos
-- existentes no prédio da Secretaria Municipal de Saúde.
-- =========================================================

CREATE TABLE setores (
    id BIGSERIAL PRIMARY KEY,

    organizacao_id BIGINT NOT NULL,
    unidade_id BIGINT NOT NULL,

    nome VARCHAR(120) NOT NULL,
    sigla VARCHAR(30),

    descricao TEXT,

    telefone VARCHAR(20),
    email VARCHAR(150),

    ativo BOOLEAN
        NOT NULL
        DEFAULT TRUE,

    criado_em TIMESTAMPTZ
        NOT NULL
        DEFAULT CURRENT_TIMESTAMP,

    atualizado_em TIMESTAMPTZ
        NOT NULL
        DEFAULT CURRENT_TIMESTAMP,

    excluido_em TIMESTAMPTZ,

    CONSTRAINT fk_setores_organizacao
        FOREIGN KEY (organizacao_id)
        REFERENCES organizacoes(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    /*
     * Garante que o setor e a unidade pertençam
     * à mesma organização.
     */
    CONSTRAINT fk_setores_unidade_organizacao
        FOREIGN KEY (
            unidade_id,
            organizacao_id
        )
        REFERENCES unidades (
            id,
            organizacao_id
        )
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT chk_setores_sigla
        CHECK (
            sigla IS NULL
            OR sigla ~ '^[A-Z0-9_-]+$'
        ),

    CONSTRAINT chk_setores_email
        CHECK (
            email IS NULL
            OR POSITION('@' IN email) > 1
        )
);

CREATE UNIQUE INDEX uq_setores_nome_unidade
    ON setores (
        unidade_id,
        LOWER(nome)
    )
    WHERE excluido_em IS NULL;

CREATE UNIQUE INDEX uq_setores_sigla_unidade
    ON setores (
        unidade_id,
        LOWER(sigla)
    )
    WHERE sigla IS NOT NULL
      AND excluido_em IS NULL;

CREATE INDEX idx_setores_organizacao
    ON setores (organizacao_id);

CREATE INDEX idx_setores_unidade
    ON setores (unidade_id);

CREATE INDEX idx_setores_ativo
    ON setores (ativo);

CREATE INDEX idx_setores_nome
    ON setores (nome);

COMMIT;