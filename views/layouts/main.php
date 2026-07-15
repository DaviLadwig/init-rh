<?php

declare(strict_types=1);

/** @var string $conteudo */
/** @var string $tituloPagina */
/** @var array<string, mixed> $usuario */

$tituloPagina = $tituloPagina ?? 'Sistema de RH';
$usuario = $usuario ?? usuarioAutenticado() ?? [];

$nomeUsuario = (string) (
    $usuario['nome']
    ?? 'Usuário'
);

$primeiraLetra = mb_strtoupper(
    mb_substr($nomeUsuario, 0, 1)
);

$caminhoAtual = parse_url(
    $_SERVER['REQUEST_URI'] ?? '/',
    PHP_URL_PATH
);

if (!is_string($caminhoAtual)) {
    $caminhoAtual = '/';
}

$dashboardAtivo = str_ends_with(
    rtrim($caminhoAtual, '/'),
    '/dashboard'
);

$setoresAtivo = str_contains(
    $caminhoAtual,
    '/setores'
);

$cargosAtivo = str_contains(
    $caminhoAtual,
    '/cargos'
);

$funcoesAtivo = str_contains(
    $caminhoAtual,
    '/funcoes'
);

$cargosOuFuncoesAtivo =
    $cargosAtivo || $funcoesAtivo;
?>  

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?= escapar($tituloPagina) ?>
        | Sistema de RH
    </title>

    <link
        rel="stylesheet"
        href="<?= escapar(appUrl('css/app.css')) ?>"
    >

    <link
        rel="stylesheet"
        href="<?= escapar(appUrl('css/layout.css')) ?>"
    >

    <link
        rel="stylesheet"
        href="<?= escapar(appUrl('css/dashboard.css')) ?>"
    >
    <link
        rel="stylesheet"
        href="<?= escapar(appUrl('css/setores.css')) ?>"
    >

    <?php if ($cargosOuFuncoesAtivo): ?>

        <link
            rel="stylesheet"
            href="<?= escapar(
                appUrl('css/cargos-funcoes.css')
            ) ?>"
        >

    <?php endif; ?>

</head>

<body class="app-page">

    <div
        class="sidebar-overlay"
        id="sidebarOverlay"
        aria-hidden="true"
    ></div>

    <aside
        class="app-sidebar"
        id="appSidebar"
    >

        <div class="sidebar-brand">

            <a
                href="<?= escapar(appUrl('dashboard')) ?>"
                class="sidebar-link<?= $dashboardAtivo
                    ? ' sidebar-link--active'
                    : '' ?>"
            >
                <span class="sidebar-brand__mark">
                    RH
                </span>

                <span class="sidebar-brand__text">
                    <strong>Sistema de RH</strong>

                    <small>Gestão de pessoas</small>
                </span>
            </a>

            <button
                type="button"
                class="sidebar-close"
                id="sidebarClose"
                aria-label="Fechar menu"
            >
                ×
            </button>

        </div>

        <nav
            class="sidebar-navigation"
            aria-label="Menu principal"
        >

            <span class="sidebar-navigation__title">
                Principal
            </span>

            <a
                href="<?= escapar(appUrl('dashboard')) ?>"
                class="sidebar-link<?= $dashboardAtivo
                    ? ' sidebar-link--active'
                    : '' ?>"
            >
                <span
                    class="sidebar-link__icon"
                    aria-hidden="true"
                >
                    ▦
                </span>

                <span>Dashboard</span>
            </a>

            <span class="sidebar-navigation__title">
                Gestão de pessoas
            </span>

            <span class="sidebar-link sidebar-link--disabled">
                <span
                    class="sidebar-link__icon"
                    aria-hidden="true"
                >
                    ♙
                </span>

                <span>Colaboradores</span>
            </span>

            <span class="sidebar-link sidebar-link--disabled">
                <span
                    class="sidebar-link__icon"
                    aria-hidden="true"
                >
                    ◫
                </span>

                <span>Unidades</span>
            </span>

            <a
                href="<?= escapar(appUrl('setores')) ?>"
                class="sidebar-link<?= $setoresAtivo
                    ? ' sidebar-link--active'
                    : '' ?>"
            >
                <span
                    class="sidebar-link__icon"
                    aria-hidden="true"
                >
                    ▦
                </span>

                <span>Setores</span>
            </a>

            <a
                href="<?= escapar(appUrl('cargos')) ?>"
                class="sidebar-link<?= $cargosAtivo
                    ? ' sidebar-link--active'
                    : '' ?>"
            >
                <span
                    class="sidebar-link__icon"
                    aria-hidden="true"
                >
                    ◇
                </span>

                <span>Cargos</span>
            </a>

            <a
                href="<?= escapar(appUrl('funcoes')) ?>"
                class="sidebar-link<?= $funcoesAtivo
                    ? ' sidebar-link--active'
                    : '' ?>"
            >
                <span
                    class="sidebar-link__icon"
                    aria-hidden="true"
                >
                    ⊙
                </span>

                <span>Funções</span>
            </a>

            <span class="sidebar-navigation__title">
                Rotinas
            </span>

            <span class="sidebar-link sidebar-link--disabled">
                <span
                    class="sidebar-link__icon"
                    aria-hidden="true"
                >
                    ◷
                </span>

                <span>Frequência</span>
            </span>

            <span class="sidebar-link sidebar-link--disabled">
                <span
                    class="sidebar-link__icon"
                    aria-hidden="true"
                >
                    ☼
                </span>

                <span>Férias</span>
            </span>

            <span class="sidebar-link sidebar-link--disabled">
                <span
                    class="sidebar-link__icon"
                    aria-hidden="true"
                >
                    ▤
                </span>

                <span>Escalas</span>
            </span>

            <span class="sidebar-link sidebar-link--disabled">
                <span
                    class="sidebar-link__icon"
                    aria-hidden="true"
                >
                    ▱
                </span>

                <span>Documentos</span>
            </span>

            <span class="sidebar-navigation__title">
                Gestão
            </span>

            <span class="sidebar-link sidebar-link--disabled">
                <span
                    class="sidebar-link__icon"
                    aria-hidden="true"
                >
                    ✓
                </span>

                <span>Avaliações</span>
            </span>

            <span class="sidebar-link sidebar-link--disabled">
                <span
                    class="sidebar-link__icon"
                    aria-hidden="true"
                >
                    ◉
                </span>

                <span>Comunicados</span>
            </span>

            <span class="sidebar-link sidebar-link--disabled">
                <span
                    class="sidebar-link__icon"
                    aria-hidden="true"
                >
                    ▥
                </span>

                <span>Relatórios</span>
            </span>

        </nav>

        <div class="sidebar-footer">

            <div class="sidebar-footer__organization">

                <span class="sidebar-footer__icon">
                    O
                </span>

                <div>
                    <strong>
                        <?= escapar(
                            (string) (
                                $usuario['organizacao_nome']
                                ?? ''
                            )
                        ) ?>
                    </strong>

                    <small>Organização ativa</small>
                </div>

            </div>

        </div>

    </aside>

    <div class="app-shell">

        <header class="app-header">

            <div class="app-header__left">

                <button
                    type="button"
                    class="menu-toggle"
                    id="menuToggle"
                    aria-label="Abrir menu"
                    aria-controls="appSidebar"
                    aria-expanded="false"
                >
                    ☰
                </button>

                <div class="page-heading">
                    <span>Painel administrativo</span>

                    <strong>
                        <?= escapar($tituloPagina) ?>
                    </strong>
                </div>

            </div>

            <div class="app-header__right">

                <div class="header-user">

                    <span class="header-user__avatar">
                        <?= escapar($primeiraLetra) ?>
                    </span>

                    <div class="header-user__content">

                        <strong>
                            <?= escapar($nomeUsuario) ?>
                        </strong>

                        <small>
                            <?= escapar(
                                (string) (
                                    $usuario['perfil_nome']
                                    ?? ''
                                )
                            ) ?>
                        </small>

                    </div>

                </div>

                <form
                    method="POST"
                    action="<?= escapar(appUrl('logout')) ?>"
                >
                    <?= campoCsrf() ?>

                    <button
                        type="submit"
                        class="header-logout"
                    >
                        Sair
                    </button>
                </form>

            </div>

        </header>

        <main class="app-content">

            <?= $conteudo ?>

        </main>

    </div>

    <script
        src="<?= escapar(appUrl('js/layout.js')) ?>"
    ></script>

    <script
        src="<?= escapar(appUrl('js/setores.js')) ?>"
    ></script>

    <?php if ($cargosOuFuncoesAtivo): ?>

        <script
            src="<?= escapar(
                appUrl('js/cargos-funcoes.js')
            ) ?>"
        ></script>

    <?php endif; ?>

</body>

</html>