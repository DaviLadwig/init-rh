BEGIN;

CREATE TABLE organizacoes (
    id BIGSERIAL PRIMARY KEY,

    nome VARCHAR(150) NOT NULL,
    nome_fantasia VARCHAR(150),

    tipo VARCHAR(50) NOT NULL DEFAULT 'OUTRO',

    documento VARCHAR(20),
    email VARCHAR(150),
    telefone VARCHAR(20),

    cep VARCHAR(8),
    logradouro VARCHAR(150),
    numero VARCHAR(20),
    complemento VARCHAR(100),
    bairro VARCHAR(100),
    cidade VARCHAR(100),
    estado CHAR(2),

    logo VARCHAR(255),

    fuso_horario VARCHAR(50)
        NOT NULL
        DEFAULT 'America/Sao_Paulo',

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

    CONSTRAINT chk_organizacoes_tipo
        CHECK (
            tipo IN (
                'PUBLICA',
                'PRIVADA',
                'ONG',
                'OUTRO'
            )
        ),

    CONSTRAINT chk_organizacoes_estado
        CHECK (
            estado IS NULL
            OR estado ~ '^[A-Z]{2}$'
        )
);

CREATE UNIQUE INDEX uq_organizacoes_documento
    ON organizacoes (documento)
    WHERE documento IS NOT NULL
      AND excluido_em IS NULL;

CREATE INDEX idx_organizacoes_nome
    ON organizacoes (nome);

CREATE INDEX idx_organizacoes_ativo
    ON organizacoes (ativo);

COMMIT;