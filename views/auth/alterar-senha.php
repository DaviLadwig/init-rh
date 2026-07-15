<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Alterar senha | Sistema de RH</title>

    <link
        rel="stylesheet"
        href="<?= escapar(appUrl('css/app.css')) ?>"
    >
</head>

<body class="auth-page">

    <main class="auth-wrapper">

        <section
            class="auth-card"
            style="grid-template-columns: 1fr;"
        >

            <div class="auth-form-panel">

                <div class="auth-form-panel__content">

                    <header class="login-header">

                        <span class="login-eyebrow">
                            Primeiro acesso
                        </span>

                        <h2>Crie sua nova senha</h2>

                        <p>
                            Por segurança, substitua a senha temporária antes de acessar o sistema.
                        </p>

                    </header>

                    <?php if (!empty($erro)): ?>
                        <div class="alert alert--error">
                            <?= escapar($erro) ?>
                        </div>
                    <?php endif; ?>

                    <form
                        class="login-form"
                        method="POST"
                        action="<?= escapar(
                            appUrl('alterar-senha')
                        ) ?>"
                    >

                        <?= campoCsrf() ?>

                        <div class="form-group">

                            <label for="senha_atual">
                                Senha atual
                            </label>

                            <div class="input-wrapper">

                                <input
                                    type="password"
                                    id="senha_atual"
                                    name="senha_atual"
                                    placeholder="Digite a senha temporária"
                                    autocomplete="current-password"
                                    style="padding-left: 16px;"
                                    required
                                >

                            </div>

                        </div>

                        <div class="form-group">

                            <label for="nova_senha">
                                Nova senha
                            </label>

                            <div class="input-wrapper">

                                <input
                                    type="password"
                                    id="nova_senha"
                                    name="nova_senha"
                                    placeholder="Digite a nova senha"
                                    autocomplete="new-password"
                                    style="padding-left: 16px;"
                                    required
                                >

                            </div>

                        </div>

                        <div class="form-group">

                            <label for="confirmar_senha">
                                Confirmar nova senha
                            </label>

                            <div class="input-wrapper">

                                <input
                                    type="password"
                                    id="confirmar_senha"
                                    name="confirmar_senha"
                                    placeholder="Repita a nova senha"
                                    autocomplete="new-password"
                                    style="padding-left: 16px;"
                                    required
                                >

                            </div>

                        </div>

                        <button
                            type="submit"
                            class="button button--primary"
                        >
                            Salvar nova senha
                        </button>

                    </form>

                </div>

            </div>

        </section>

    </main>

</body>

</html>