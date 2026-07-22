BEGIN;

-- =========================================================
-- TABELA: COLABORADORES
-- =========================================================
--
-- Armazena os dados pessoais e funcionais dos colaboradores.
--
-- Cada colaborador será relacionado a:
-- - Organização
-- - Unidade
-- - Setor
-- - Cargo
-- - Função opcional
-- - Tipo de vínculo
-- - Jornada de trabalho
-- =========================================================

CREATE TABLE colaboradores (
    id BIGSERIAL PRIMARY KEY,

    /*
     * Organização à qual o colaborador pertence.
     */
    organizacao_id BIGINT NOT NULL,

    /*
     * Unidade principal do colaborador.
     *
     * Nesta primeira versão, a unidade continuará sendo
     * definida internamente e não será exibida no formulário.
     */
    unidade_id BIGINT NOT NULL,

    /*
     * Dados pessoais.
     */
    nome_completo VARCHAR(180) NOT NULL,

    cpf VARCHAR(11) NOT NULL,

    data_nascimento DATE,

    email VARCHAR(160),

    telefone VARCHAR(20),

    /*
     * Dados funcionais.
     */
    matricula VARCHAR(50),

    setor_id BIGINT NOT NULL,

    cargo_id BIGINT NOT NULL,

    /*
     * A função é opcional.
     *
     * Exemplo:
     * Cargo: Assistente administrativo
     * Função: Chefe do setor
     */
    funcao_id BIGINT,

    tipo_vinculo_id BIGINT NOT NULL,

    jornada_trabalho_id BIGINT NOT NULL,

    data_admissao DATE NOT NULL,

    /*
     * Será exigida pelo service quando o tipo de vínculo
     * possuir exige_data_fim = TRUE.
     */
    data_fim_vinculo DATE,

    observacoes TEXT,

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

    /*
     * Relacionamentos.
     */
    CONSTRAINT fk_colaboradores_organizacao
        FOREIGN KEY (organizacao_id)
        REFERENCES organizacoes(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_colaboradores_unidade
        FOREIGN KEY (unidade_id)
        REFERENCES unidades(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_colaboradores_setor
        FOREIGN KEY (setor_id)
        REFERENCES setores(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_colaboradores_cargo
        FOREIGN KEY (cargo_id)
        REFERENCES cargos(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_colaboradores_funcao
        FOREIGN KEY (funcao_id)
        REFERENCES funcoes(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_colaboradores_tipo_vinculo
        FOREIGN KEY (tipo_vinculo_id)
        REFERENCES tipos_vinculo(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_colaboradores_jornada
        FOREIGN KEY (jornada_trabalho_id)
        REFERENCES jornadas_trabalho(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    /*
     * Validações básicas.
     */
    CONSTRAINT chk_colaboradores_nome
        CHECK (
            CHAR_LENGTH(TRIM(nome_completo)) >= 3
        ),

    CONSTRAINT chk_colaboradores_cpf
        CHECK (
            cpf ~ '^[0-9]{11}$'
        ),

    CONSTRAINT chk_colaboradores_email
        CHECK (
            email IS NULL
            OR CHAR_LENGTH(TRIM(email)) >= 5
        ),

    CONSTRAINT chk_colaboradores_matricula
        CHECK (
            matricula IS NULL
            OR CHAR_LENGTH(TRIM(matricula)) >= 1
        ),

    CONSTRAINT chk_colaboradores_datas_vinculo
        CHECK (
            data_fim_vinculo IS NULL
            OR data_fim_vinculo >= data_admissao
        ),

    /*
     * Prepara a tabela para relacionamentos compostos
     * no futuro.
     */
    CONSTRAINT uq_colaboradores_id_organizacao
        UNIQUE (
            id,
            organizacao_id
        )
);

-- =========================================================
-- ÍNDICES ÚNICOS
-- =========================================================

-- O CPF não pode ser repetido dentro da organização.
CREATE UNIQUE INDEX uq_colaboradores_cpf_organizacao
    ON colaboradores (
        organizacao_id,
        cpf
    )
    WHERE excluido_em IS NULL;

-- A matrícula não pode ser repetida dentro da organização
-- quando estiver preenchida.
CREATE UNIQUE INDEX uq_colaboradores_matricula_organizacao
    ON colaboradores (
        organizacao_id,
        LOWER(matricula)
    )
    WHERE matricula IS NOT NULL
      AND TRIM(matricula) <> ''
      AND excluido_em IS NULL;

-- =========================================================
-- ÍNDICES DE CONSULTA
-- =========================================================

CREATE INDEX idx_colaboradores_organizacao
    ON colaboradores (
        organizacao_id
    );

CREATE INDEX idx_colaboradores_unidade
    ON colaboradores (
        unidade_id
    );

CREATE INDEX idx_colaboradores_setor
    ON colaboradores (
        setor_id
    );

CREATE INDEX idx_colaboradores_cargo
    ON colaboradores (
        cargo_id
    );

CREATE INDEX idx_colaboradores_funcao
    ON colaboradores (
        funcao_id
    );

CREATE INDEX idx_colaboradores_tipo_vinculo
    ON colaboradores (
        tipo_vinculo_id
    );

CREATE INDEX idx_colaboradores_jornada
    ON colaboradores (
        jornada_trabalho_id
    );

CREATE INDEX idx_colaboradores_nome
    ON colaboradores (
        LOWER(nome_completo)
    );

CREATE INDEX idx_colaboradores_ativo
    ON colaboradores (
        ativo
    );

CREATE INDEX idx_colaboradores_organizacao_ativo
    ON colaboradores (
        organizacao_id,
        ativo
    )
    WHERE excluido_em IS NULL;

COMMIT;
