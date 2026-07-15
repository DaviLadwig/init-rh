<?php

declare(strict_types=1);

class AutenticacaoService
{
    private const HASH_FICTICIO =
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.';

    public function __construct(
        private UsuarioRepository $usuarioRepository
    ) {
    }

    /**
     * Valida as credenciais e retorna os dados da sessão.
     */
    public function autenticar(
        string $codigoOrganizacao,
        string $email,
        string $senha
    ): array {
        $usuario =
            $this->usuarioRepository->buscarParaLogin(
                $codigoOrganizacao,
                $email
            );

        if (!$usuario) {
            password_verify(
                $senha,
                self::HASH_FICTICIO
            );

            return [
                'sucesso' => false,
                'mensagem' =>
                    'Organização, e-mail ou senha inválidos.',
            ];
        }

        if (
            !$usuario['ativo']
            || !$usuario['perfil_ativo']
            || !$usuario['organizacao_ativa']
        ) {
            return [
                'sucesso' => false,
                'mensagem' =>
                    'Este acesso está inativo. Procure o administrador.',
            ];
        }

        if (!empty($usuario['bloqueado_ate'])) {
            $bloqueadoAte = new DateTimeImmutable(
                (string) $usuario['bloqueado_ate']
            );

            $agora = new DateTimeImmutable('now');

            if ($bloqueadoAte > $agora) {
                return [
                    'sucesso' => false,
                    'mensagem' =>
                        'Acesso temporariamente bloqueado. Tente novamente mais tarde.',
                ];
            }

            $this->usuarioRepository->limparBloqueio(
                (int) $usuario['id']
            );
        }

        if (
            !password_verify(
                $senha,
                (string) $usuario['senha']
            )
        ) {
            $this->usuarioRepository
                ->registrarFalhaLogin(
                    (int) $usuario['id']
                );

            return [
                'sucesso' => false,
                'mensagem' =>
                    'Organização, e-mail ou senha inválidos.',
            ];
        }

        $permissoes =
            $this->usuarioRepository
                ->buscarPermissoesPerfil(
                    (int) $usuario['perfil_id']
                );

        $this->usuarioRepository
            ->registrarLoginSucesso(
                (int) $usuario['id']
            );

        return [
            'sucesso' => true,

            'usuario' => [
                'id' => (int) $usuario['id'],

                'organizacao_id' =>
                    (int) $usuario['organizacao_id'],

                'organizacao_nome' =>
                    (string) $usuario['organizacao_nome'],

                'organizacao_codigo' =>
                    (string) $usuario['organizacao_codigo'],

                'perfil_id' =>
                    (int) $usuario['perfil_id'],

                'perfil_nome' =>
                    (string) $usuario['perfil_nome'],

                'perfil_codigo' =>
                    (string) $usuario['perfil_codigo'],

                'nome' =>
                    (string) $usuario['nome'],

                'email' =>
                    (string) $usuario['email'],

                'trocar_senha' =>
                    (bool) $usuario['trocar_senha'],

                'permissoes' =>
                    $permissoes,
            ],
        ];
    }

    /**
     * Valida e salva uma nova senha.
     */
    public function alterarSenha(
        array $usuarioSessao,
        string $senhaAtual,
        string $novaSenha,
        string $confirmacao
    ): array {
        if ($novaSenha !== $confirmacao) {
            return [
                'sucesso' => false,
                'mensagem' =>
                    'A confirmação da nova senha não confere.',
            ];
        }

        $senhaValida =
            strlen($novaSenha) >= 8
            && preg_match('/[A-Z]/', $novaSenha)
            && preg_match('/[a-z]/', $novaSenha)
            && preg_match('/[0-9]/', $novaSenha)
            && preg_match('/[^A-Za-z0-9]/', $novaSenha);

        if (!$senhaValida) {
            return [
                'sucesso' => false,
                'mensagem' =>
                    'A senha deve possuir pelo menos 8 caracteres, incluindo maiúscula, minúscula, número e símbolo.',
            ];
        }

        $usuarioBanco =
            $this->usuarioRepository->buscarPorId(
                (int) $usuarioSessao['id'],
                (int) $usuarioSessao['organizacao_id']
            );

        if (
            !$usuarioBanco
            || !$usuarioBanco['ativo']
        ) {
            return [
                'sucesso' => false,
                'mensagem' =>
                    'O usuário não está disponível.',
            ];
        }

        if (
            !password_verify(
                $senhaAtual,
                (string) $usuarioBanco['senha']
            )
        ) {
            return [
                'sucesso' => false,
                'mensagem' =>
                    'A senha atual está incorreta.',
            ];
        }

        if (
            password_verify(
                $novaSenha,
                (string) $usuarioBanco['senha']
            )
        ) {
            return [
                'sucesso' => false,
                'mensagem' =>
                    'A nova senha deve ser diferente da senha atual.',
            ];
        }

        $novoHash = password_hash(
            $novaSenha,
            PASSWORD_DEFAULT
        );

        if ($novoHash === false) {
            throw new RuntimeException(
                'Não foi possível proteger a nova senha.'
            );
        }

        $this->usuarioRepository->atualizarSenha(
            (int) $usuarioSessao['id'],
            (int) $usuarioSessao['organizacao_id'],
            $novoHash
        );

        return [
            'sucesso' => true,
        ];
    }
}