BEGIN;

-- =========================================================
-- TABELA: TIPOS DE DOCUMENTO
-- =========================================================
--
-- Catálogo utilizado no select de documentos.
--
-- Exemplos:
-- - RG
-- - CPF
-- - Contrato
-- - Portaria
-- - Certificado
-- - ASO
-- =========================================================

CREATE TABLE tipos_documento (
    id BIGSERIAL PRIMARY KEY,

    organizacao_id BIGINT NOT NULL,

    nome VARCHAR(120) NOT NULL,

    codigo VARCHAR(40) NOT NULL,

    descricao TEXT,

    /*
     * Indica se documentos desse tipo normalmente
     * precisam possuir uma data de validade.
     */
    exige_validade BOOLEAN
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

    CONSTRAINT fk_tipos_documento_organizacao
        FOREIGN KEY (organizacao_id)
        REFERENCES organizacoes(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT chk_tipos_documento_nome
        CHECK (
            CHAR_LENGTH(TRIM(nome)) >= 2
        ),

    CONSTRAINT chk_tipos_documento_codigo
        CHECK (
            codigo ~ '^[A-Z0-9_-]+$'
        ),

    CONSTRAINT uq_tipos_documento_id_organizacao
        UNIQUE (
            id,
            organizacao_id
        )
);

-- Nome único dentro da organização.
CREATE UNIQUE INDEX uq_tipos_documento_nome_organizacao
    ON tipos_documento (
        organizacao_id,
        LOWER(nome)
    )
    WHERE excluido_em IS NULL;

-- Código único dentro da organização.
CREATE UNIQUE INDEX uq_tipos_documento_codigo_organizacao
    ON tipos_documento (
        organizacao_id,
        LOWER(codigo)
    )
    WHERE excluido_em IS NULL;

CREATE INDEX idx_tipos_documento_organizacao
    ON tipos_documento (
        organizacao_id
    );

CREATE INDEX idx_tipos_documento_ativo
    ON tipos_documento (
        organizacao_id,
        ativo
    )
    WHERE excluido_em IS NULL;


-- =========================================================
-- TABELA: DOCUMENTOS DOS COLABORADORES
-- =========================================================
--
-- Guarda as informações do documento e os metadados
-- do arquivo enviado.
--
-- O arquivo físico não será salvo no banco.
-- =========================================================

CREATE TABLE documentos_colaboradores (
    id BIGSERIAL PRIMARY KEY,

    organizacao_id BIGINT NOT NULL,

    colaborador_id BIGINT NOT NULL,

    tipo_documento_id BIGINT NOT NULL,

    /*
     * Usuário que realizou o envio.
     *
     * Pode ficar nulo caso o usuário seja removido
     * posteriormente.
     */
    enviado_por_usuario_id BIGINT,

    titulo VARCHAR(180) NOT NULL,

    numero_documento VARCHAR(100),

    data_emissao DATE,

    data_validade DATE,

    descricao TEXT,

    /*
     * Metadados do arquivo.
     */
    arquivo_nome_original VARCHAR(255) NOT NULL,

    /*
     * Nome aleatório gerado pelo sistema.
     *
     * Nunca devemos usar diretamente o nome enviado
     * pelo usuário para salvar o arquivo físico.
     */
    arquivo_nome_armazenado VARCHAR(255) NOT NULL,

    /*
     * Caminho interno relativo.
     *
     * Exemplo:
     * organizacao_1/colaborador_15/arquivo.pdf
     */
    arquivo_caminho_relativo VARCHAR(500) NOT NULL,

    arquivo_extensao VARCHAR(20) NOT NULL,

    arquivo_mime VARCHAR(150) NOT NULL,

    arquivo_tamanho_bytes BIGINT NOT NULL,

    /*
     * Hash SHA-256 utilizado para integridade e
     * identificação do arquivo.
     */
    arquivo_hash_sha256 CHAR(64) NOT NULL,

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

    CONSTRAINT fk_documentos_colaborador_organizacao
        FOREIGN KEY (organizacao_id)
        REFERENCES organizacoes(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    /*
     * A chave composta garante que o colaborador
     * pertence à mesma organização do documento.
     */
    CONSTRAINT fk_documentos_colaborador
        FOREIGN KEY (
            colaborador_id,
            organizacao_id
        )
        REFERENCES colaboradores (
            id,
            organizacao_id
        )
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    /*
     * Também garante que o tipo de documento pertence
     * à mesma organização.
     */
    CONSTRAINT fk_documentos_tipo_documento
        FOREIGN KEY (
            tipo_documento_id,
            organizacao_id
        )
        REFERENCES tipos_documento (
            id,
            organizacao_id
        )
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_documentos_usuario_envio
        FOREIGN KEY (
            enviado_por_usuario_id
        )
        REFERENCES usuarios(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL,

    CONSTRAINT chk_documentos_titulo
        CHECK (
            CHAR_LENGTH(TRIM(titulo)) >= 2
        ),

    CONSTRAINT chk_documentos_nome_original
        CHECK (
            CHAR_LENGTH(
                TRIM(arquivo_nome_original)
            ) >= 1
        ),

    CONSTRAINT chk_documentos_nome_armazenado
        CHECK (
            CHAR_LENGTH(
                TRIM(arquivo_nome_armazenado)
            ) >= 1
        ),

    CONSTRAINT chk_documentos_caminho
        CHECK (
            CHAR_LENGTH(
                TRIM(arquivo_caminho_relativo)
            ) >= 1
        ),

    CONSTRAINT chk_documentos_tamanho
        CHECK (
            arquivo_tamanho_bytes > 0
        ),

    CONSTRAINT chk_documentos_hash
        CHECK (
            arquivo_hash_sha256
                ~ '^[a-f0-9]{64}$'
        ),

    CONSTRAINT chk_documentos_datas
        CHECK (
            data_validade IS NULL
            OR data_emissao IS NULL
            OR data_validade >= data_emissao
        ),

    CONSTRAINT uq_documentos_colaborador_id_organizacao
        UNIQUE (
            id,
            organizacao_id
        )
);

-- O mesmo caminho interno não pode ser registrado duas vezes.
CREATE UNIQUE INDEX uq_documentos_caminho_organizacao
    ON documentos_colaboradores (
        organizacao_id,
        arquivo_caminho_relativo
    )
    WHERE excluido_em IS NULL;

CREATE INDEX idx_documentos_colaborador
    ON documentos_colaboradores (
        organizacao_id,
        colaborador_id
    )
    WHERE excluido_em IS NULL;

CREATE INDEX idx_documentos_tipo
    ON documentos_colaboradores (
        organizacao_id,
        tipo_documento_id
    )
    WHERE excluido_em IS NULL;

CREATE INDEX idx_documentos_usuario_envio
    ON documentos_colaboradores (
        enviado_por_usuario_id
    );

CREATE INDEX idx_documentos_validade
    ON documentos_colaboradores (
        organizacao_id,
        data_validade
    )
    WHERE data_validade IS NOT NULL
      AND ativo = TRUE
      AND excluido_em IS NULL;

CREATE INDEX idx_documentos_hash
    ON documentos_colaboradores (
        organizacao_id,
        arquivo_hash_sha256
    )
    WHERE excluido_em IS NULL;

CREATE INDEX idx_documentos_ativo
    ON documentos_colaboradores (
        organizacao_id,
        ativo
    )
    WHERE excluido_em IS NULL;


-- =========================================================
-- TIPOS INICIAIS
-- =========================================================
--
-- Cria os tipos abaixo para todas as organizações
-- atualmente existentes.
-- =========================================================

INSERT INTO tipos_documento (
    organizacao_id,
    nome,
    codigo,
    descricao,
    exige_validade
)
SELECT
    organizacao.id,
    tipo.nome,
    tipo.codigo,
    tipo.descricao,
    tipo.exige_validade

FROM organizacoes AS organizacao

CROSS JOIN (
    VALUES
        (
            'RG',
            'RG',
            'Documento de identidade.',
            FALSE
        ),
        (
            'CPF',
            'CPF',
            'Cadastro de Pessoa Física.',
            FALSE
        ),
        (
            'CNH',
            'CNH',
            'Carteira Nacional de Habilitação.',
            TRUE
        ),
        (
            'PIS/PASEP',
            'PIS_PASEP',
            'Comprovante do número PIS ou PASEP.',
            FALSE
        ),
        (
            'Título eleitoral',
            'TITULO_ELEITORAL',
            'Documento eleitoral do colaborador.',
            FALSE
        ),
        (
            'Comprovante de residência',
            'COMPROVANTE_RESIDENCIA',
            'Comprovante de endereço residencial.',
            FALSE
        ),
        (
            'Contrato',
            'CONTRATO',
            'Contrato ou termo de vínculo.',
            TRUE
        ),
        (
            'Portaria',
            'PORTARIA',
            'Portaria de nomeação, designação ou lotação.',
            FALSE
        ),
        (
            'Diploma',
            'DIPLOMA',
            'Diploma de formação acadêmica.',
            FALSE
        ),
        (
            'Certificado',
            'CERTIFICADO',
            'Certificado profissional ou de capacitação.',
            FALSE
        ),
        (
            'ASO',
            'ASO',
            'Atestado de Saúde Ocupacional.',
            TRUE
        ),
        (
            'Outro',
            'OUTRO',
            'Outro documento relacionado ao colaborador.',
            FALSE
        )
) AS tipo (
    nome,
    codigo,
    descricao,
    exige_validade
)

ON CONFLICT DO NOTHING;

COMMIT;