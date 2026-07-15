<?php

declare(strict_types=1);

class UsuarioRepository
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    /**
     * Busca o usuário pelo código da organização e e-mail.
     */
    public function buscarParaLogin(
        string $codigoOrganizacao,
        string $email
    ): ?array {
        $sql = '
            SELECT
                u.id,
                u.organizacao_id,
                u.perfil_id,
                u.nome,
                u.email,
                u.senha,
                u.ativo,
                u.trocar_senha,
                u.tentativas_login,
                u.bloqueado_ate,

                p.nome AS perfil_nome,
                p.codigo AS perfil_codigo,
                p.ativo AS perfil_ativo,

                o.nome AS organizacao_nome,
                o.codigo_acesso AS organizacao_codigo,
                o.ativo AS organizacao_ativa

            FROM usuarios u

            INNER JOIN perfis p
                ON p.id = u.perfil_id
                AND p.organizacao_id = u.organizacao_id

            INNER JOIN organizacoes o
                ON o.id = u.organizacao_id

            WHERE LOWER(o.codigo_acesso)
                    = LOWER(:codigo_organizacao)

              AND LOWER(u.email)
                    = LOWER(:email)

              AND u.excluido_em IS NULL
              AND p.excluido_em IS NULL
              AND o.excluido_em IS NULL

            LIMIT 1
        ';

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':codigo_organizacao' => $codigoOrganizacao,
            ':email' => $email,
        ]);

        $usuario = $stmt->fetch();

        return $usuario ?: null;
    }

    /**
     * Busca um usuário autenticado por ID.
     */
    public function buscarPorId(
        int $usuarioId,
        int $organizacaoId
    ): ?array {
        $sql = '
            SELECT
                id,
                organizacao_id,
                senha,
                ativo,
                trocar_senha
            FROM usuarios
            WHERE id = :usuario_id
              AND organizacao_id = :organizacao_id
              AND excluido_em IS NULL
            LIMIT 1
        ';

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':usuario_id' => $usuarioId,
            ':organizacao_id' => $organizacaoId,
        ]);

        $usuario = $stmt->fetch();

        return $usuario ?: null;
    }

    /**
     * Retorna as permissões do perfil.
     */
    public function buscarPermissoesPerfil(
        int $perfilId
    ): array {
        $sql = '
            SELECT pe.codigo
            FROM perfil_permissoes pp

            INNER JOIN permissoes pe
                ON pe.id = pp.permissao_id

            WHERE pp.perfil_id = :perfil_id
              AND pe.ativo = TRUE

            ORDER BY pe.codigo
        ';

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':perfil_id' => $perfilId,
        ]);

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Registra uma tentativa de acesso inválida.
     */
    public function registrarFalhaLogin(
        int $usuarioId
    ): void {
        $sql = '
            UPDATE usuarios
            SET
                tentativas_login = tentativas_login + 1,

                bloqueado_ate = CASE
                    WHEN tentativas_login + 1 >= 5
                    THEN CURRENT_TIMESTAMP
                        + INTERVAL \'15 minutes\'
                    ELSE bloqueado_ate
                END,

                atualizado_em = CURRENT_TIMESTAMP

            WHERE id = :usuario_id
        ';

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':usuario_id' => $usuarioId,
        ]);
    }

    /**
     * Limpa um bloqueio que já expirou.
     */
    public function limparBloqueio(
        int $usuarioId
    ): void {
        $sql = '
            UPDATE usuarios
            SET
                tentativas_login = 0,
                bloqueado_ate = NULL,
                atualizado_em = CURRENT_TIMESTAMP
            WHERE id = :usuario_id
        ';

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':usuario_id' => $usuarioId,
        ]);
    }

    /**
     * Registra um login realizado com sucesso.
     */
    public function registrarLoginSucesso(
        int $usuarioId
    ): void {
        $sql = '
            UPDATE usuarios
            SET
                tentativas_login = 0,
                bloqueado_ate = NULL,
                ultimo_acesso = CURRENT_TIMESTAMP,
                atualizado_em = CURRENT_TIMESTAMP
            WHERE id = :usuario_id
        ';

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':usuario_id' => $usuarioId,
        ]);
    }

    /**
     * Atualiza a senha do usuário.
     */
    public function atualizarSenha(
        int $usuarioId,
        int $organizacaoId,
        string $senhaHash
    ): void {
        $sql = '
            UPDATE usuarios
            SET
                senha = :senha,
                trocar_senha = FALSE,
                tentativas_login = 0,
                bloqueado_ate = NULL,
                atualizado_em = CURRENT_TIMESTAMP
            WHERE id = :usuario_id
              AND organizacao_id = :organizacao_id
        ';

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            ':senha' => $senhaHash,
            ':usuario_id' => $usuarioId,
            ':organizacao_id' => $organizacaoId,
        ]);
    }
}