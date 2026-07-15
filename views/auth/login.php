<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="description"
        content="Acesso ao Sistema de RH"
    >

    <title>
        <?= escapar($tituloPagina ?? 'Entrar') ?>
        |
        <?= escapar($nomeSistema ?? 'Sistema de RH') ?>
    </title>

    <link
        rel="stylesheet"
        href="<?= escapar(appUrl('css/app.css')) ?>"
    >
</head>

<body class="auth-page">

    <main class="auth-wrapper">

        <section class="auth-card">

            <!-- PAINEL VISUAL -->
            <div class="auth-visual">

                <div class="auth-visual__decoration auth-visual__decoration--one"></div>
                <div class="auth-visual__decoration auth-visual__decoration--two"></div>

                <header class="auth-visual__brand">

                    <div class="brand-mark">
                        RH
                    </div>

                    <div class="brand-text">
                        <strong>
                            <?= escapar(
                                $nomeSistema ?? 'Sistema de RH'
                            ) ?>
                        </strong>

                        <span>
                            Gestão inteligente de pessoas
                        </span>
                    </div>

                </header>

                <div class="auth-visual__content">

                    <span class="visual-eyebrow">
                        Gestão integrada
                    </span>

                    <h1>
                        Pessoas bem cuidadas transformam organizações.
                    </h1>

                    <p>
                        Centralize colaboradores, frequência, férias,
                        documentos, escalas e rotinas de RH em uma única
                        plataforma.
                    </p>

                    <div class="visual-features">

                        <div class="visual-feature">
                            <span class="visual-feature__icon">
                                <svg
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                >
                                    <path
                                        d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"
                                    />

                                    <circle
                                        cx="9"
                                        cy="7"
                                        r="4"
                                    />

                                    <path
                                        d="M22 21v-2a4 4 0 0 0-3-3.87"
                                    />

                                    <path
                                        d="M16 3.13a4 4 0 0 1 0 7.75"
                                    />
                                </svg>
                            </span>

                            <span>Gestão de colaboradores</span>
                        </div>

                        <div class="visual-feature">
                            <span class="visual-feature__icon">
                                <svg
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                >
                                    <circle
                                        cx="12"
                                        cy="12"
                                        r="9"
                                    />

                                    <path d="M12 7v5l3 2" />
                                </svg>
                            </span>

                            <span>Frequência e escalas</span>
                        </div>

                        <div class="visual-feature">
                            <span class="visual-feature__icon">
                                <svg
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                >
                                    <path
                                        d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"
                                    />

                                    <path d="M14 2v6h6" />
                                    <path d="M8 13h8" />
                                    <path d="M8 17h8" />
                                </svg>
                            </span>

                            <span>Documentos organizados</span>
                        </div>

                    </div>

                </div>

                <div class="auth-illustration">

                    <svg
                        viewBox="0 0 620 310"
                        role="img"
                        aria-label="Ilustração de gestão de pessoas"
                    >
                        <ellipse
                            class="illustration-shadow"
                            cx="310"
                            cy="278"
                            rx="225"
                            ry="20"
                        />

                        <rect
                            class="illustration-card illustration-card--back"
                            x="82"
                            y="60"
                            width="175"
                            height="184"
                            rx="22"
                        />

                        <rect
                            class="illustration-card illustration-card--front"
                            x="182"
                            y="24"
                            width="255"
                            height="222"
                            rx="26"
                        />

                        <rect
                            class="illustration-header"
                            x="207"
                            y="49"
                            width="205"
                            height="39"
                            rx="12"
                        />

                        <circle
                            class="illustration-avatar"
                            cx="236"
                            cy="68"
                            r="11"
                        />

                        <rect
                            class="illustration-line"
                            x="259"
                            y="60"
                            width="93"
                            height="7"
                            rx="3.5"
                        />

                        <rect
                            class="illustration-line illustration-line--short"
                            x="259"
                            y="73"
                            width="62"
                            height="5"
                            rx="2.5"
                        />

                        <rect
                            class="illustration-row"
                            x="207"
                            y="106"
                            width="205"
                            height="32"
                            rx="10"
                        />

                        <circle
                            class="illustration-dot"
                            cx="226"
                            cy="122"
                            r="7"
                        />

                        <rect
                            class="illustration-line"
                            x="242"
                            y="116"
                            width="87"
                            height="6"
                            rx="3"
                        />

                        <rect
                            class="illustration-row"
                            x="207"
                            y="148"
                            width="205"
                            height="32"
                            rx="10"
                        />

                        <circle
                            class="illustration-dot"
                            cx="226"
                            cy="164"
                            r="7"
                        />

                        <rect
                            class="illustration-line"
                            x="242"
                            y="158"
                            width="112"
                            height="6"
                            rx="3"
                        />

                        <rect
                            class="illustration-row"
                            x="207"
                            y="190"
                            width="205"
                            height="32"
                            rx="10"
                        />

                        <circle
                            class="illustration-dot"
                            cx="226"
                            cy="206"
                            r="7"
                        />

                        <rect
                            class="illustration-line"
                            x="242"
                            y="200"
                            width="74"
                            height="6"
                            rx="3"
                        />

                        <circle
                            class="illustration-head"
                            cx="468"
                            cy="126"
                            r="27"
                        />

                        <path
                            class="illustration-hair"
                            d="M442 124c2-25 16-37 36-34 19 3 27 18 21 38-9-8-18-13-30-12-10 1-18 4-27 8z"
                        />

                        <path
                            class="illustration-body"
                            d="M430 254c4-65 11-95 39-99 31 2 42 30 45 99z"
                        />

                        <path
                            class="illustration-arm"
                            d="M449 169c-19 22-32 37-47 51"
                        />

                        <path
                            class="illustration-arm"
                            d="M489 171c17 14 29 28 40 48"
                        />

                        <path
                            class="illustration-leg"
                            d="M458 250l-8 35"
                        />

                        <path
                            class="illustration-leg"
                            d="M490 250l13 35"
                        />
                    </svg>

                </div>

            </div>

            <!-- PAINEL DO FORMULÁRIO -->
            <div class="auth-form-panel">

                <div class="auth-form-panel__content">

                    <header class="login-header">

                        <span class="login-eyebrow">
                            Acesso ao sistema
                        </span>

                        <h2>Bem-vindo de volta</h2>

                        <p>
                            Informe suas credenciais para acessar o painel
                            administrativo.
                        </p>

                    </header>

                    <?php if (!empty($erro)): ?>
                        <div
                            class="alert alert--error"
                            role="alert"
                        >
                            <?= escapar($erro) ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($sucesso)): ?>
                        <div
                            class="alert alert--success"
                            role="status"
                        >
                            <?= escapar($sucesso) ?>
                        </div>
                    <?php endif; ?>

                    <form
                        class="login-form"
                        method="POST"
                        action="<?= escapar(appUrl('login')) ?>"
                        autocomplete="on"
                    >
                    <?= campoCsrf() ?>

                        <div class="form-group">

                            <label for="codigo_organizacao">
                                Código da organização
                            </label>

                            <div class="input-wrapper">

                                <span class="input-icon">
                                    <svg
                                        viewBox="0 0 24 24"
                                        aria-hidden="true"
                                    >
                                        <path d="M3 21h18" />
                                        <path d="M6 21V7l6-4 6 4v14" />
                                        <path d="M9 9h1" />
                                        <path d="M14 9h1" />
                                        <path d="M9 13h1" />
                                        <path d="M14 13h1" />
                                        <path d="M10 21v-4h4v4" />
                                    </svg>
                                </span>

                                <input
                                    type="text"
                                    id="codigo_organizacao"
                                    name="codigo_organizacao"
                                    value="<?= escapar(
                                        $codigoOrganizacao
                                        ?? 'saude-vitoria'
                                    ) ?>"
                                    placeholder="Ex.: saude-vitoria"
                                    maxlength="60"
                                    autocomplete="organization"
                                    required
                                    autofocus
                                >

                            </div>

                        </div>

                        <div class="form-group">

                            <label for="email">
                                E-mail
                            </label>

                            <div class="input-wrapper">

                                <span class="input-icon">
                                    <svg
                                        viewBox="0 0 24 24"
                                        aria-hidden="true"
                                    >
                                        <rect
                                            x="3"
                                            y="5"
                                            width="18"
                                            height="14"
                                            rx="2"
                                        />

                                        <path d="m3 7 9 6 9-6" />
                                    </svg>
                                </span>

                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    value="<?= escapar($emailAnterior ?? '') ?>"
                                    placeholder="admin@rh.com"
                                    maxlength="150"
                                    autocomplete="username"
                                    required
                                >

                            </div>

                        </div>

                        <div class="form-group">

                            <div class="form-label-row">

                                <label for="senha">
                                    Senha
                                </label>

                                <button
                                    type="button"
                                    class="password-toggle"
                                    id="passwordToggle"
                                    aria-label="Mostrar senha"
                                >
                                    Mostrar senha
                                </button>

                            </div>

                            <div class="input-wrapper">

                                <span class="input-icon">
                                    <svg
                                        viewBox="0 0 24 24"
                                        aria-hidden="true"
                                    >
                                        <rect
                                            x="4"
                                            y="10"
                                            width="16"
                                            height="11"
                                            rx="2"
                                        />

                                        <path d="M8 10V7a4 4 0 0 1 8 0v3" />
                                    </svg>
                                </span>

                                <input
                                    type="password"
                                    id="senha"
                                    name="senha"
                                    placeholder="Digite sua senha"
                                    autocomplete="current-password"
                                    required
                                >

                            </div>

                        </div>

                        <button
                            type="submit"
                            class="button button--primary"
                        >
                            <span>Entrar no sistema</span>

                            <svg
                                viewBox="0 0 24 24"
                                aria-hidden="true"
                            >
                                <path d="M5 12h14" />
                                <path d="m13 6 6 6-6 6" />
                            </svg>
                        </button>

                    </form>

                    <footer class="login-footer">

                        <span class="security-indicator">
                            <svg
                                viewBox="0 0 24 24"
                                aria-hidden="true"
                            >
                                <path
                                    d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"
                                />

                                <path d="m9 12 2 2 4-4" />
                            </svg>
                        </span>

                        <div>
                            <strong>Acesso protegido</strong>

                            <span>
                                Suas informações são transmitidas com segurança.
                            </span>
                        </div>

                    </footer>

                </div>

            </div>

        </section>

    </main>

    <script src="<?= escapar(appUrl('js/login.js')) ?>"></script>

</body>

</html>