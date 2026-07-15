<?php

declare(strict_types=1);

/** @var array<int, array<string, mixed>> $cargos */
/** @var string $busca */
/** @var string $situacao */
/** @var string|null $sucesso */
/** @var string|null $erro */

$cargos = isset($cargos) && is_array($cargos)
    ? $cargos
    : [];

$busca = isset($busca)
    ? trim((string) $busca)
    : '';

$situacao = isset($situacao)
    ? strtoupper(trim((string) $situacao))
    : 'TODOS';

$sucesso = isset($sucesso) && is_string($sucesso)
    ? $sucesso
    : null;

$erro = isset($erro) && is_string($erro)
    ? $erro
    : null;

$totalCargos = count($cargos);

$totalAtivos = count(
    array_filter(
        $cargos,
        static fn (array $cargo): bool =>
            (bool) ($cargo['ativo'] ?? false)
    )
);

$totalInativos = $totalCargos - $totalAtivos;
?>

<?php if ($sucesso !== null): ?>
    <div class="alert alert--success" role="status">
        <?= escapar($sucesso) ?>
    </div>
<?php endif; ?>

<?php if ($erro !== null): ?>
    <div class="alert alert--error" role="alert">
        <?= escapar($erro) ?>
    </div>
<?php endif; ?>

<section class="catalog-page-header">

    <div class="catalog-page-header__content">

        <span class="catalog-eyebrow">
            Estrutura organizacional
        </span>

        <h1>Cargos</h1>

        <p>
            Cadastre os cargos formais ou contratuais utilizados
            pelos colaboradores da Secretaria de Saúde.
        </p>

    </div>

    <?php if (temPermissao('estrutura.gerenciar')): ?>

        <a
            href="<?= escapar(appUrl('cargos/criar')) ?>"
            class="catalog-primary-button"
        >
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M12 5v14" />
                <path d="M5 12h14" />
            </svg>

            <span>Novo cargo</span>
        </a>

    <?php endif; ?>

</section>

<section class="catalog-statistics" aria-label="Resumo dos cargos">

    <article class="catalog-statistics__card">

        <span class="catalog-statistics__icon">
            <svg viewBox="0 0 24 24">
                <path d="M9 7V5a3 3 0 0 1 6 0v2" />
                <rect x="3" y="7" width="18" height="13" rx="2" />
                <path d="M3 12h18" />
                <path d="M10 12v2h4v-2" />
            </svg>
        </span>

        <div>
            <span>Total de cargos</span>
            <strong><?= $totalCargos ?></strong>
        </div>

    </article>

    <article class="catalog-statistics__card">

        <span class="catalog-statistics__icon catalog-statistics__icon--active">
            <svg viewBox="0 0 24 24">
                <path d="m5 12 4 4L19 6" />
            </svg>
        </span>

        <div>
            <span>Cargos ativos</span>
            <strong><?= $totalAtivos ?></strong>
        </div>

    </article>

    <article class="catalog-statistics__card">

        <span class="catalog-statistics__icon catalog-statistics__icon--inactive">
            <svg viewBox="0 0 24 24">
                <path d="M6 6l12 12" />
                <path d="M18 6 6 18" />
            </svg>
        </span>

        <div>
            <span>Cargos inativos</span>
            <strong><?= $totalInativos ?></strong>
        </div>

    </article>

</section>

<section class="catalog-panel">

    <form
        class="catalog-filters"
        method="GET"
        action="<?= escapar(appUrl('cargos')) ?>"
    >

        <div class="catalog-search">

            <span class="catalog-search__icon">
                <svg viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="7" />
                    <path d="m20 20-3.5-3.5" />
                </svg>
            </span>

            <input
                type="search"
                name="busca"
                value="<?= escapar($busca) ?>"
                placeholder="Buscar por nome, código ou descrição"
                maxlength="120"
            >

        </div>

        <div class="catalog-filter-group">

            <label for="situacao" class="sr-only">
                Situação
            </label>

            <select id="situacao" name="situacao">
                <option
                    value="TODOS"
                    <?= $situacao === 'TODOS' ? 'selected' : '' ?>
                >
                    Todos
                </option>

                <option
                    value="ATIVOS"
                    <?= $situacao === 'ATIVOS' ? 'selected' : '' ?>
                >
                    Ativos
                </option>

                <option
                    value="INATIVOS"
                    <?= $situacao === 'INATIVOS' ? 'selected' : '' ?>
                >
                    Inativos
                </option>
            </select>

            <button
                type="submit"
                class="catalog-filter-button"
            >
                Filtrar
            </button>

            <?php if ($busca !== '' || $situacao !== 'TODOS'): ?>

                <a
                    href="<?= escapar(appUrl('cargos')) ?>"
                    class="catalog-clear-button"
                >
                    Limpar
                </a>

            <?php endif; ?>

        </div>

    </form>

    <?php if ($cargos === []): ?>

        <div class="catalog-empty">

            <span class="catalog-empty__icon">
                <svg viewBox="0 0 24 24">
                    <path d="M9 7V5a3 3 0 0 1 6 0v2" />
                    <rect x="3" y="7" width="18" height="13" rx="2" />
                    <path d="M3 12h18" />
                </svg>
            </span>

            <h2>Nenhum cargo encontrado</h2>

            <p>
                <?php if ($busca !== '' || $situacao !== 'TODOS'): ?>
                    Não encontramos cargos com os filtros informados.
                <?php else: ?>
                    Cadastre o primeiro cargo utilizado pela organização.
                <?php endif; ?>
            </p>

            <?php if (temPermissao('estrutura.gerenciar')): ?>

                <a
                    href="<?= escapar(appUrl('cargos/criar')) ?>"
                    class="catalog-primary-button"
                >
                    Cadastrar cargo
                </a>

            <?php endif; ?>

        </div>

    <?php else: ?>

        <div class="catalog-table-wrapper">

            <table class="catalog-table">

                <thead>
                    <tr>
                        <th>Cargo</th>
                        <th>Descrição</th>
                        <th>Situação</th>
                        <th class="catalog-table__actions-title">
                            Ações
                        </th>
                    </tr>
                </thead>

                <tbody>

                    <?php foreach ($cargos as $cargo): ?>

                        <?php
                        $cargoId = (int) ($cargo['id'] ?? 0);
                        $cargoNome = trim((string) ($cargo['nome'] ?? ''));
                        $cargoCodigo = trim((string) ($cargo['codigo'] ?? ''));
                        $cargoDescricao = trim((string) ($cargo['descricao'] ?? ''));
                        $cargoAtivo = (bool) ($cargo['ativo'] ?? false);
                        ?>

                        <tr>

                            <td>

                                <div class="catalog-name">

                                    <span class="catalog-name__icon">
                                        <?= escapar(
                                            mb_strtoupper(
                                                mb_substr($cargoNome, 0, 1)
                                            )
                                        ) ?>
                                    </span>

                                    <div>
                                        <strong>
                                            <?= escapar($cargoNome) ?>
                                        </strong>

                                        <span>
                                            <?= $cargoCodigo !== ''
                                                ? escapar($cargoCodigo)
                                                : 'Sem código' ?>
                                        </span>
                                    </div>

                                </div>

                            </td>

                            <td>

                                <p class="catalog-description">
                                    <?= $cargoDescricao !== ''
                                        ? escapar($cargoDescricao)
                                        : 'Descrição não informada.' ?>
                                </p>

                            </td>

                            <td>

                                <span class="status-badge <?= $cargoAtivo
                                    ? 'status-badge--active'
                                    : 'status-badge--inactive' ?>"
                                >
                                    <span></span>

                                    <?= $cargoAtivo ? 'Ativo' : 'Inativo' ?>
                                </span>

                            </td>

                            <td>

                                <div class="catalog-actions">

                                    <?php if (
                                        temPermissao('estrutura.gerenciar')
                                    ): ?>

                                        <a
                                            href="<?= escapar(
                                                appUrl(
                                                    'cargos/editar?id='
                                                    . $cargoId
                                                )
                                            ) ?>"
                                            class="catalog-action-button"
                                            title="Editar cargo"
                                            aria-label="Editar cargo <?= escapar($cargoNome) ?>"
                                        >
                                            <svg viewBox="0 0 24 24">
                                                <path d="M12 20h9" />
                                                <path
                                                    d="M16.5 3.5a2.12 2.12 0 0 1 3 3L8 18l-4 1 1-4z"
                                                />
                                            </svg>
                                        </a>

                                        <form
                                            method="POST"
                                            action="<?= escapar(
                                                appUrl(
                                                    'cargos/alterar-status'
                                                )
                                            ) ?>"
                                            class="catalog-status-form"
                                            data-catalog-status-form
                                            data-item-name="<?= escapar($cargoNome) ?>"
                                            data-item-type="cargo"
                                            data-item-active="<?= $cargoAtivo
                                                ? 'true'
                                                : 'false' ?>"
                                        >
                                            <?= campoCsrf() ?>

                                            <input
                                                type="hidden"
                                                name="cargo_id"
                                                value="<?= $cargoId ?>"
                                            >

                                            <button
                                                type="submit"
                                                class="catalog-action-button <?= $cargoAtivo
                                                    ? 'catalog-action-button--deactivate'
                                                    : 'catalog-action-button--activate' ?>"
                                                title="<?= $cargoAtivo
                                                    ? 'Desativar cargo'
                                                    : 'Ativar cargo' ?>"
                                            >
                                                <?php if ($cargoAtivo): ?>

                                                    <svg viewBox="0 0 24 24">
                                                        <circle
                                                            cx="12"
                                                            cy="12"
                                                            r="9"
                                                        />
                                                        <path d="M8 12h8" />
                                                    </svg>

                                                <?php else: ?>

                                                    <svg viewBox="0 0 24 24">
                                                        <circle
                                                            cx="12"
                                                            cy="12"
                                                            r="9"
                                                        />
                                                        <path d="m8 12 3 3 5-6" />
                                                    </svg>

                                                <?php endif; ?>
                                            </button>

                                        </form>

                                    <?php endif; ?>

                                </div>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    <?php endif; ?>

</section>