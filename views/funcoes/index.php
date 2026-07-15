<?php

declare(strict_types=1);

/** @var array<int, array<string, mixed>> $funcoes */
/** @var string $busca */
/** @var string $situacao */
/** @var string|null $sucesso */
/** @var string|null $erro */

$funcoes = isset($funcoes) && is_array($funcoes)
    ? $funcoes
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

$totalFuncoes = count($funcoes);

$totalAtivas = count(
    array_filter(
        $funcoes,
        static fn (array $funcao): bool =>
            (bool) ($funcao['ativo'] ?? false)
    )
);

$totalInativas = $totalFuncoes - $totalAtivas;
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

        <h1>Funções</h1>

        <p>
            Cadastre responsabilidades ou atribuições adicionais
            exercidas pelos colaboradores.
        </p>

    </div>

    <?php if (temPermissao('estrutura.gerenciar')): ?>

        <a
            href="<?= escapar(appUrl('funcoes/criar')) ?>"
            class="catalog-primary-button"
        >
            <svg viewBox="0 0 24 24">
                <path d="M12 5v14" />
                <path d="M5 12h14" />
            </svg>

            <span>Nova função</span>
        </a>

    <?php endif; ?>

</section>

<section class="catalog-statistics">

    <article class="catalog-statistics__card">
        <span class="catalog-statistics__icon">
            <svg viewBox="0 0 24 24">
                <path d="M12 3v18" />
                <path d="M3 12h18" />
                <circle cx="12" cy="12" r="9" />
            </svg>
        </span>

        <div>
            <span>Total de funções</span>
            <strong><?= $totalFuncoes ?></strong>
        </div>
    </article>

    <article class="catalog-statistics__card">
        <span class="catalog-statistics__icon catalog-statistics__icon--active">
            <svg viewBox="0 0 24 24">
                <path d="m5 12 4 4L19 6" />
            </svg>
        </span>

        <div>
            <span>Funções ativas</span>
            <strong><?= $totalAtivas ?></strong>
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
            <span>Funções inativas</span>
            <strong><?= $totalInativas ?></strong>
        </div>
    </article>

</section>

<section class="catalog-panel">

    <form
        class="catalog-filters"
        method="GET"
        action="<?= escapar(appUrl('funcoes')) ?>"
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

            <select name="situacao" aria-label="Situação">
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
                    Ativas
                </option>

                <option
                    value="INATIVOS"
                    <?= $situacao === 'INATIVOS' ? 'selected' : '' ?>
                >
                    Inativas
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
                    href="<?= escapar(appUrl('funcoes')) ?>"
                    class="catalog-clear-button"
                >
                    Limpar
                </a>
            <?php endif; ?>

        </div>

    </form>

    <?php if ($funcoes === []): ?>

        <div class="catalog-empty">

            <span class="catalog-empty__icon">
                <svg viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="9" />
                    <path d="M12 7v10" />
                    <path d="M7 12h10" />
                </svg>
            </span>

            <h2>Nenhuma função encontrada</h2>

            <p>
                Cadastre a primeira função adicional da organização.
            </p>

            <?php if (temPermissao('estrutura.gerenciar')): ?>
                <a
                    href="<?= escapar(appUrl('funcoes/criar')) ?>"
                    class="catalog-primary-button"
                >
                    Cadastrar função
                </a>
            <?php endif; ?>

        </div>

    <?php else: ?>

        <div class="catalog-table-wrapper">

            <table class="catalog-table">

                <thead>
                    <tr>
                        <th>Função</th>
                        <th>Descrição</th>
                        <th>Situação</th>
                        <th class="catalog-table__actions-title">
                            Ações
                        </th>
                    </tr>
                </thead>

                <tbody>

                    <?php foreach ($funcoes as $funcao): ?>

                        <?php
                        $funcaoId = (int) ($funcao['id'] ?? 0);
                        $funcaoNome = trim((string) ($funcao['nome'] ?? ''));
                        $funcaoCodigo = trim((string) ($funcao['codigo'] ?? ''));
                        $funcaoDescricao = trim((string) ($funcao['descricao'] ?? ''));
                        $funcaoAtiva = (bool) ($funcao['ativo'] ?? false);
                        ?>

                        <tr>

                            <td>
                                <div class="catalog-name">

                                    <span class="catalog-name__icon">
                                        <?= escapar(
                                            mb_strtoupper(
                                                mb_substr($funcaoNome, 0, 1)
                                            )
                                        ) ?>
                                    </span>

                                    <div>
                                        <strong>
                                            <?= escapar($funcaoNome) ?>
                                        </strong>

                                        <span>
                                            <?= $funcaoCodigo !== ''
                                                ? escapar($funcaoCodigo)
                                                : 'Sem código' ?>
                                        </span>
                                    </div>

                                </div>
                            </td>

                            <td>
                                <p class="catalog-description">
                                    <?= $funcaoDescricao !== ''
                                        ? escapar($funcaoDescricao)
                                        : 'Descrição não informada.' ?>
                                </p>
                            </td>

                            <td>
                                <span class="status-badge <?= $funcaoAtiva
                                    ? 'status-badge--active'
                                    : 'status-badge--inactive' ?>"
                                >
                                    <span></span>
                                    <?= $funcaoAtiva ? 'Ativa' : 'Inativa' ?>
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
                                                    'funcoes/editar?id='
                                                    . $funcaoId
                                                )
                                            ) ?>"
                                            class="catalog-action-button"
                                            title="Editar função"
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
                                                    'funcoes/alterar-status'
                                                )
                                            ) ?>"
                                            class="catalog-status-form"
                                            data-catalog-status-form
                                            data-item-name="<?= escapar($funcaoNome) ?>"
                                            data-item-type="função"
                                            data-item-active="<?= $funcaoAtiva
                                                ? 'true'
                                                : 'false' ?>"
                                        >
                                            <?= campoCsrf() ?>

                                            <input
                                                type="hidden"
                                                name="funcao_id"
                                                value="<?= $funcaoId ?>"
                                            >

                                            <button
                                                type="submit"
                                                class="catalog-action-button <?= $funcaoAtiva
                                                    ? 'catalog-action-button--deactivate'
                                                    : 'catalog-action-button--activate' ?>"
                                            >
                                                <?php if ($funcaoAtiva): ?>
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