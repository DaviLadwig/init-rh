BEGIN;

-- =========================================================
-- TABELA: CARGOS
-- =========================================================
--
-- Representa o cargo formal ou contratual do colaborador.
--
-- Exemplos:
-- Assistente Administrativo
-- Motorista
-- Técnico de Enfermagem
-- Coordenador
-- Auxiliar de Serviços Gerais
-- =========================================================

CREATE TABLE cargos (
    id BIGSERIAL PRIMARY KEY,

    organizacao_id BIGINT NOT NULL,

    nome VARCHAR(120) NOT NULL,
    codigo VARCHAR(30),

    descricao TEXT,

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

    CONSTRAINT fk_cargos_organizacao
        FOREIGN KEY (organizacao_id)
        REFERENCES organizacoes(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT chk_cargos_codigo
        CHECK (
            codigo IS NULL
            OR codigo ~ '^[A-Z0-9_-]+$'
        ),

    /*
     * Permitirá futuramente criar relações compostas
     * respeitando sempre a organização.
     */
    CONSTRAINT uq_cargos_id_organizacao
        UNIQUE (
            id,
            organizacao_id
        )
);

CREATE UNIQUE INDEX uq_cargos_nome_organizacao
    ON cargos (
        organizacao_id,
        LOWER(nome)
    )
    WHERE excluido_em IS NULL;

CREATE UNIQUE INDEX uq_cargos_codigo_organizacao
    ON cargos (
        organizacao_id,
        LOWER(codigo)
    )
    WHERE codigo IS NOT NULL
      AND excluido_em IS NULL;

CREATE INDEX idx_cargos_organizacao
    ON cargos (organizacao_id);

CREATE INDEX idx_cargos_nome
    ON cargos (nome);

CREATE INDEX idx_cargos_ativo
    ON cargos (ativo);


-- =========================================================
-- TABELA: FUNÇÕES
-- =========================================================
--
-- Representa uma responsabilidade ou atribuição adicional.
--
-- Exemplos:
-- Coordenador de Transporte
-- Responsável pelo Protocolo
-- Chefe de Setor
-- Fiscal de Contratos
--
-- A função não substitui o cargo e será opcional no cadastro
-- funcional do colaborador.
-- =========================================================

CREATE TABLE funcoes (
    id BIGSERIAL PRIMARY KEY,

    organizacao_id BIGINT NOT NULL,

    nome VARCHAR(120) NOT NULL,
    codigo VARCHAR(30),

    descricao TEXT,

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

    CONSTRAINT fk_funcoes_organizacao
        FOREIGN KEY (organizacao_id)
        REFERENCES organizacoes(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT chk_funcoes_codigo
        CHECK (
            codigo IS NULL
            OR codigo ~ '^[A-Z0-9_-]+$'
        ),

    CONSTRAINT uq_funcoes_id_organizacao
        UNIQUE (
            id,
            organizacao_id
        )
);

CREATE UNIQUE INDEX uq_funcoes_nome_organizacao
    ON funcoes (
        organizacao_id,
        LOWER(nome)
    )
    WHERE excluido_em IS NULL;

CREATE UNIQUE INDEX uq_funcoes_codigo_organizacao
    ON funcoes (
        organizacao_id,
        LOWER(codigo)
    )
    WHERE codigo IS NOT NULL
      AND excluido_em IS NULL;

CREATE INDEX idx_funcoes_organizacao
    ON funcoes (organizacao_id);

CREATE INDEX idx_funcoes_nome
    ON funcoes (nome);

CREATE INDEX idx_funcoes_ativo
    ON funcoes (ativo);

COMMIT;