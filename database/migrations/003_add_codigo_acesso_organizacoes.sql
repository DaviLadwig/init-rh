BEGIN;

ALTER TABLE organizacoes
    ADD COLUMN IF NOT EXISTS codigo_acesso VARCHAR(60);

UPDATE organizacoes
SET
    codigo_acesso = 'saude-vitoria',
    atualizado_em = CURRENT_TIMESTAMP
WHERE id = 1
  AND (
      codigo_acesso IS NULL
      OR TRIM(codigo_acesso) = ''
  );

ALTER TABLE organizacoes
    ALTER COLUMN codigo_acesso SET NOT NULL;

DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM pg_constraint
        WHERE conname = 'chk_organizacoes_codigo_acesso'
    ) THEN
        ALTER TABLE organizacoes
            ADD CONSTRAINT chk_organizacoes_codigo_acesso
            CHECK (
                codigo_acesso ~ '^[a-z0-9-]+$'
            );
    END IF;
END
$$;

CREATE UNIQUE INDEX IF NOT EXISTS uq_organizacoes_codigo_acesso
    ON organizacoes (LOWER(codigo_acesso))
    WHERE excluido_em IS NULL;

COMMIT;