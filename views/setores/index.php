<?php

declare(strict_types=1);

/** @var array<int, array<string, mixed>> $setores */
/** @var string $busca */
/** @var string $situacao */
/** @var string|null $sucesso */
/** @var string|null $erro */

$setores = isset($setores) && is_array($setores)
    ? $setores
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

$totalSetores = count($setores);

$totalAtivos = count(
    array_filter(
        $setores,
        static fn (array $setor): bool =>
            (bool) ($setor['ativo'] ?? false)
    )
);

$totalInativos = $totalSetores - $totalAtivos;
?>

<?php if ($sucesso !== null): ?>

    <div
        class="alert alert--success"
        role="status"
    >
        <?= escapar($sucesso) ?>
    </div>

<?php endif; ?>

<?php if ($erro !== null): ?>

    <div
        class="alert alert--error"
        role="alert"
    >
        <?= escapar($erro) ?>
    </div>

<?php endif; ?>

<section class="page-header">

    <div class="page-header__content">

        <span class="page-eyebrow">
            Estrutura organizacional
        </span>

        <h1>Setores</h1>

        <p>
            Organize os departamentos internos existentes no prédio
            da Secretaria de Saúde.
        </p>

    </div>

    <?php if (temPermissao('estrutura.gerenciar')): ?>

        <a
            href="<?= escapar(appUrl('setores/criar')) ?>"
            class="page-primary-button"
        >
            <svg
                viewBox="0 0 24 24"
                aria-hidden="true"
            >
                <path d="M12 5v14" />
                <path d="M5 12h14" />
            </svg>

            <span>Novo setor</span>
        </a>

    <?php endif; ?>

</section>

<section
    class="sector-statistics"
    aria-label="Resumo dos setores"
>

    <article class="sector-statistics__card">

        <span class="sector-statistics__icon">
            <svg viewBox="0 0 24 24">
                <path d="M3 21h18" />
                <path d="M6 21V7l6-4 6 4v14" />
                <path d="M9 9h1" />
                <path d="M14 9h1" />
                <path d="M9 13h1" />
                <path d="M14 13h1" />
            </svg>
        </span>

        <div>
            <span>Total de setores</span>
            <strong><?= $totalSetores ?></strong>
        </div>

    </article>

    <article class="sector-statistics__card">

        <span class="sector-statistics__icon sector-statistics__icon--active">
            <svg viewBox="0 0 24 24">
                <path d="m5 12 4 4L19 6" />
            </svg>
        </span>

        <div>
            <span>Setores ativos</span>
            <strong><?= $totalAtivos ?></strong>
        </div>

    </article>

    <article class="sector-statistics__card">

        <span class="sector-statistics__icon sector-statistics__icon--inactive">
            <svg viewBox="0 0 24 24">
                <path d="M6 6l12 12" />
                <path d="M18 6 6 18" />
            </svg>
        </span>

        <div>
            <span>Setores inativos</span>
            <strong><?= $totalInativos ?></strong>
        </div>

    </article>

</section>

<section class="sector-panel">

    <form
        class="sector-filters"
        method="GET"
        action="<?= escapar(appUrl('setores')) ?>"
    >

        <div class="sector-search">

            <span class="sector-search__icon">
                <svg viewBox="0 0 24 24">
                    <circle
                        cx="11"
                        cy="11"
                        r="7"
                    />

                    <path d="m20 20-3.5-3.5" />
                </svg>
            </span>

            <input
                type="search"
                name="busca"
                value="<?= escapar($busca) ?>"
                placeholder="Buscar por nome, sigla ou e-mail"
                maxlength="120"
            >

        </div>

        <div class="sector-filter-group">

            <label
                for="situacao"
                class="sr-only"
            >
                Situação
            </label>

            <select
                id="situacao"
                name="situacao"
            >
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
                class="sector-filter-button"
            >
                Filtrar
            </button>

            <?php if (
                $busca !== ''
                || $situacao !== 'TODOS'
            ): ?>

                <a
                    href="<?= escapar(appUrl('setores')) ?>"
                    class="sector-clear-button"
                >
                    Limpar
                </a>

            <?php endif; ?>

        </div>

    </form>

    <?php if ($setores === []): ?>

        <div class="sector-empty">

            <span class="sector-empty__icon">
                <svg viewBox="0 0 24 24">
                    <path d="M3 21h18" />
                    <path d="M6 21V7l6-4 6 4v14" />
                    <path d="M9 9h1" />
                    <path d="M14 9h1" />
                    <path d="M9 13h1" />
                    <path d="M14 13h1" />
                </svg>
            </span>

            <h2>Nenhum setor encontrado</h2>

            <p>
                <?php if (
                    $busca !== ''
                    || $situacao !== 'TODOS'
                ): ?>
                    Não encontramos setores com os filtros informados.
                <?php else: ?>
                    Cadastre o primeiro setor interno da Secretaria de Saúde.
                <?php endif; ?>
            </p>

            <?php if (temPermissao('estrutura.gerenciar')): ?>

                <a
                    href="<?= escapar(appUrl('setores/criar')) ?>"
                    class="page-primary-button"
                >
                    Cadastrar setor
                </a>

            <?php endif; ?>

        </div>

    <?php else: ?>

        <div class="sector-table-wrapper">

            <table class="sector-table">

                <thead>
                    <tr>
                        <th>Setor</th>
                        <th>Contato</th>
                        <th>Situação</th>
                        <th class="sector-table__actions-title">
                            Ações
                        </th>
                    </tr>
                </thead>

                <tbody>

                    <?php foreach ($setores as $setor): ?>

                        <?php
                        $setorId = (int) (
                            $setor['id']
                            ?? 0
                        );

                        $setorNome = (string) (
                            $setor['nome']
                            ?? ''
                        );

                        $setorSigla = trim(
                            (string) (
                                $setor['sigla']
                                ?? ''
                            )
                        );

                        $setorEmail = trim(
                            (string) (
                                $setor['email']
                                ?? ''
                            )
                        );

                        $setorTelefone = trim(
                            (string) (
                                $setor['telefone']
                                ?? ''
                            )
                        );

                        $setorAtivo = (bool) (
                            $setor['ativo']
                            ?? false
                        );
                        ?>

                        <tr>

                            <td>

                                <div class="sector-name">

                                    <span class="sector-name__icon">
                                        <?= escapar(
                                            mb_strtoupper(
                                                mb_substr(
                                                    $setorNome,
                                                    0,
                                                    1
                                                )
                                            )
                                        ) ?>
                                    </span>

                                    <div>
                                        <strong>
                                            <?= escapar($setorNome) ?>
                                        </strong>

                                        <span>
                                            <?= $setorSigla !== ''
                                                ? escapar($setorSigla)
                                                : 'Sem sigla' ?>
                                        </span>
                                    </div>

                                </div>

                            </td>

                            <td>

                                <div class="sector-contact">

                                    <span>
                                        <?= $setorEmail !== ''
                                            ? escapar($setorEmail)
                                            : 'E-mail não informado' ?>
                                    </span>

                                    <small>
                                        <?= $setorTelefone !== ''
                                            ? escapar($setorTelefone)
                                            : 'Telefone não informado' ?>
                                    </small>

                                </div>

                            </td>

                            <td>

                                <span class="status-badge <?= $setorAtivo
                                    ? 'status-badge--active'
                                    : 'status-badge--inactive' ?>"
                                >
                                    <span></span>

                                    <?= $setorAtivo
                                        ? 'Ativo'
                                        : 'Inativo' ?>
                                </span>

                            </td>

                            <td>

                                <div class="sector-actions">

                                    <?php if (
                                        temPermissao(
                                            'estrutura.gerenciar'
                                        )
                                    ): ?>

                                        <a
                                            href="<?= escapar(
                                                appUrl(
                                                    'setores/editar?id='
                                                    . $setorId
                                                )
                                            ) ?>"
                                            class="sector-action-button"
                                            title="Editar setor"
                                            aria-label="Editar setor <?= escapar($setorNome) ?>"
                                        >
                                            <svg viewBox="0 0 24 24">
                                                <path
                                                    d="M12 20h9"
                                                />

                                                <path
                                                    d="M16.5 3.5a2.12 2.12 0 0 1 3 3L8 18l-4 1 1-4z"
                                                />
                                            </svg>
                                        </a>

                                        <form
                                            method="POST"
                                            action="<?= escapar(
                                                appUrl(
                                                    'setores/alterar-status'
                                                )
                                            ) ?>"
                                            class="sector-status-form"
                                            data-sector-status-form
                                            data-sector-name="<?= escapar($setorNome) ?>"
                                            data-sector-active="<?= $setorAtivo
                                                ? 'true'
                                                : 'false' ?>"
                                        >
                                            <?= campoCsrf() ?>

                                            <input
                                                type="hidden"
                                                name="setor_id"
                                                value="<?= $setorId ?>"
                                            >

                                            <button
                                                type="submit"
                                                class="sector-action-button <?= $setorAtivo
                                                    ? 'sector-action-button--deactivate'
                                                    : 'sector-action-button--activate' ?>"
                                                title="<?= $setorAtivo
                                                    ? 'Desativar setor'
                                                    : 'Ativar setor' ?>"
                                                aria-label="<?= $setorAtivo
                                                    ? 'Desativar setor '
                                                    : 'Ativar setor ' ?><?= escapar($setorNome) ?>"
                                            >
                                                <?php if ($setorAtivo): ?>

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

                                    <?php else: ?>

                                        <span class="sector-no-action">
                                            Somente visualização
                                        </span>

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