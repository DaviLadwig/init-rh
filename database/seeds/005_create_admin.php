<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';

/*
|--------------------------------------------------------------------------
| Dados iniciais do administrador
|--------------------------------------------------------------------------
| Altere o nome, e-mail e senha antes de executar, caso necessário.
*/

$nomeAdministrador = 'Administrador do Sistema';
$emailAdministrador = 'admin@sistema-rh.local';
$senhaTemporaria = 'Admin@123';

try {
    $pdo = conectarBanco();

    $pdo->beginTransaction();

    /*
     * Localiza a organização inicial e o perfil ADMIN.
     */
    $sqlPerfil = '
        SELECT
            p.id AS perfil_id,
            p.organizacao_id
        FROM perfis p
        INNER JOIN organizacoes o
            ON o.id = p.organizacao_id
        WHERE p.codigo = :codigo
          AND p.ativo = TRUE
          AND p.excluido_em IS NULL
          AND o.ativo = TRUE
          AND o.excluido_em IS NULL
        ORDER BY p.id
        LIMIT 1
    ';

    $stmtPerfil = $pdo->prepare($sqlPerfil);

    $stmtPerfil->execute([
        ':codigo' => 'ADMIN',
    ]);

    $perfil = $stmtPerfil->fetch();

    if (!$perfil) {
        throw new RuntimeException(
            'O perfil ADMIN não foi encontrado.'
        );
    }

    $organizacaoId = (int) $perfil['organizacao_id'];
    $perfilId = (int) $perfil['perfil_id'];

    /*
     * Verifica se o administrador já foi cadastrado.
     */
    $sqlUsuarioExistente = '
        SELECT id
        FROM usuarios
        WHERE organizacao_id = :organizacao_id
          AND LOWER(email) = LOWER(:email)
          AND excluido_em IS NULL
        LIMIT 1
    ';

    $stmtUsuarioExistente = $pdo->prepare($sqlUsuarioExistente);

    $stmtUsuarioExistente->execute([
        ':organizacao_id' => $organizacaoId,
        ':email' => $emailAdministrador,
    ]);

    $usuarioExistente = $stmtUsuarioExistente->fetch();

    if ($usuarioExistente) {
        $pdo->rollBack();

        echo PHP_EOL;
        echo 'O usuário administrador já está cadastrado.' . PHP_EOL;
        echo 'E-mail: ' . $emailAdministrador . PHP_EOL;
        exit;
    }

    /*
     * Gera o hash seguro da senha.
     */
    $senhaHash = password_hash(
        $senhaTemporaria,
        PASSWORD_DEFAULT
    );

    if ($senhaHash === false) {
        throw new RuntimeException(
            'Não foi possível gerar o hash da senha.'
        );
    }

    /*
     * Cadastra o primeiro administrador.
     */
    $sqlInsert = '
        INSERT INTO usuarios (
            organizacao_id,
            perfil_id,
            nome,
            email,
            senha,
            ativo,
            trocar_senha
        )
        VALUES (
            :organizacao_id,
            :perfil_id,
            :nome,
            :email,
            :senha,
            TRUE,
            TRUE
        )
        RETURNING id
    ';

    $stmtInsert = $pdo->prepare($sqlInsert);

    $stmtInsert->execute([
        ':organizacao_id' => $organizacaoId,
        ':perfil_id' => $perfilId,
        ':nome' => $nomeAdministrador,
        ':email' => $emailAdministrador,
        ':senha' => $senhaHash,
    ]);

    $usuarioId = $stmtInsert->fetchColumn();

    $pdo->commit();

    echo PHP_EOL;
    echo 'Administrador criado com sucesso.' . PHP_EOL;
    echo 'ID: ' . $usuarioId . PHP_EOL;
    echo 'E-mail: ' . $emailAdministrador . PHP_EOL;
    echo 'Senha temporária: ' . $senhaTemporaria . PHP_EOL;
    echo 'Troca de senha obrigatória: sim' . PHP_EOL;
} catch (Throwable $erro) {
    if (
        isset($pdo) &&
        $pdo instanceof PDO &&
        $pdo->inTransaction()
    ) {
        $pdo->rollBack();
    }

    echo PHP_EOL;
    echo 'Erro ao criar administrador:' . PHP_EOL;
    echo $erro->getMessage() . PHP_EOL;

    exit(1);
}