BEGIN;

-- =========================================================
-- TABELA: TIPOS DE VÍNCULO
-- =========================================================
--
-- Representa a forma de contratação ou vínculo funcional
-- do colaborador.
--
-- Exemplos:
-- - Efetivo
-- - Comissionado
-- - Contratado
-- - Temporário
-- - Terceirizado
-- - Estagiário
-- =========================================================

CREATE TABLE tipos_vinculo (
    id BIGSERIAL PRIMARY KEY,

    organizacao_id BIGINT NOT NULL,

    nome VARCHAR(120) NOT NULL,

    codigo VARCHAR(30),

    descricao TEXT,

    /*
     * Indica se o vínculo normalmente precisa possuir uma
     * data prevista de encerramento.
     *
     * Exemplos:
     * Efetivo: FALSE
     * Temporário: TRUE
     * Contratado: TRUE
     */
    exige_data_fim BOOLEAN
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

    CONSTRAINT fk_tipos_vinculo_organizacao
        FOREIGN KEY (organizacao_id)
        REFERENCES organizacoes(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT chk_tipos_vinculo_nome
        CHECK (
            CHAR_LENGTH(TRIM(nome)) >= 2
        ),

    CONSTRAINT chk_tipos_vinculo_codigo
        CHECK (
            codigo IS NULL
            OR codigo ~ '^[A-Z0-9_-]+$'
        ),

    /*
     * Permite futuramente criar chaves estrangeiras
     * compostas, garantindo que o registro pertence à
     * mesma organização.
     */
    CONSTRAINT uq_tipos_vinculo_id_organizacao
        UNIQUE (
            id,
            organizacao_id
        )
);

-- Nome único dentro da organização.
CREATE UNIQUE INDEX uq_tipos_vinculo_nome_organizacao
    ON tipos_vinculo (
        organizacao_id,
        LOWER(nome)
    )
    WHERE excluido_em IS NULL;

-- Código único dentro da organização, quando informado.
CREATE UNIQUE INDEX uq_tipos_vinculo_codigo_organizacao
    ON tipos_vinculo (
        organizacao_id,
        LOWER(codigo)
    )
    WHERE codigo IS NOT NULL
      AND excluido_em IS NULL;

CREATE INDEX idx_tipos_vinculo_organizacao
    ON tipos_vinculo (
        organizacao_id
    );

CREATE INDEX idx_tipos_vinculo_nome
    ON tipos_vinculo (
        nome
    );

CREATE INDEX idx_tipos_vinculo_ativo
    ON tipos_vinculo (
        ativo
    );


-- =========================================================
-- TABELA: JORNADAS DE TRABALHO
-- =========================================================
--
-- Representa a carga horária ou escala do colaborador.
--
-- Exemplos:
-- - 20 horas semanais
-- - 30 horas semanais
-- - 40 horas semanais
-- - 44 horas semanais
-- - Escala 12x36
-- - Plantão
-- =========================================================

CREATE TABLE jornadas_trabalho (
    id BIGSERIAL PRIMARY KEY,

    organizacao_id BIGINT NOT NULL,

    nome VARCHAR(120) NOT NULL,

    codigo VARCHAR(30),

    /*
     * Pode permanecer NULL quando a jornada não puder ser
     * representada somente por uma carga semanal fixa.
     *
     * Exemplos:
     * 40 horas semanais: 40.00
     * Escala 12x36: pode ser NULL
     * Plantão: pode ser NULL
     */
    carga_horaria_semanal NUMERIC(5, 2),

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

    CONSTRAINT fk_jornadas_trabalho_organizacao
        FOREIGN KEY (organizacao_id)
        REFERENCES organizacoes(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT chk_jornadas_trabalho_nome
        CHECK (
            CHAR_LENGTH(TRIM(nome)) >= 2
        ),

    CONSTRAINT chk_jornadas_trabalho_codigo
        CHECK (
            codigo IS NULL
            OR codigo ~ '^[A-Z0-9_-]+$'
        ),

    CONSTRAINT chk_jornadas_carga_horaria
        CHECK (
            carga_horaria_semanal IS NULL
            OR (
                carga_horaria_semanal > 0
                AND carga_horaria_semanal <= 168
            )
        ),

    CONSTRAINT uq_jornadas_trabalho_id_organizacao
        UNIQUE (
            id,
            organizacao_id
        )
);

-- Nome único dentro da organização.
CREATE UNIQUE INDEX uq_jornadas_trabalho_nome_organizacao
    ON jornadas_trabalho (
        organizacao_id,
        LOWER(nome)
    )
    WHERE excluido_em IS NULL;

-- Código único dentro da organização, quando informado.
CREATE UNIQUE INDEX uq_jornadas_trabalho_codigo_organizacao
    ON jornadas_trabalho (
        organizacao_id,
        LOWER(codigo)
    )
    WHERE codigo IS NOT NULL
      AND excluido_em IS NULL;

CREATE INDEX idx_jornadas_trabalho_organizacao
    ON jornadas_trabalho (
        organizacao_id
    );

CREATE INDEX idx_jornadas_trabalho_nome
    ON jornadas_trabalho (
        nome
    );

CREATE INDEX idx_jornadas_trabalho_ativo
    ON jornadas_trabalho (
        ativo
    );

COMMIT;

--===========================
-- caso a query tool dÊ erro:
--===========================
ROLLBACK;