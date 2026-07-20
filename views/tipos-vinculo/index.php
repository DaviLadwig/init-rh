<?php

declare(strict_types=1);

/** @var array<int, array<string, mixed>> $tiposVinculo */
/** @var string $busca */
/** @var string $situacao */
/** @var string|null $sucesso */
/** @var string|null $erro */

$tiposVinculo =
    isset($tiposVinculo) && is_array($tiposVinculo)
        ? $tiposVinculo
        : [];

$busca = isset($busca)
    ? trim((string) $busca)
    : '';

$situacao = isset($situacao)
    ? strtoupper(trim((string) $situacao))
    : 'TODOS';

$sucesso =
    isset($sucesso) && is_string($sucesso)
        ? $sucesso
        : null;

$erro =
    isset($erro) && is_string($erro)
        ? $erro
        : null;

/*
|--------------------------------------------------------------------------
| Indicadores
|--------------------------------------------------------------------------
*/

$totalTiposVinculo = count($tiposVinculo);

$totalAtivos = count(
    array_filter(
        $tiposVinculo,
        static function (array $tipoVinculo): bool {
            return filter_var(
                $tipoVinculo['ativo'] ?? false,
                FILTER_VALIDATE_BOOL
            );
        }
    )
);

$totalComDataFim = count(
    array_filter(
        $tiposVinculo,
        static function (array $tipoVinculo): bool {
            return filter_var(
                $tipoVinculo['exige_data_fim'] ?? false,
                FILTER_VALIDATE_BOOL
            );
        }
    )
);
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

<section class="catalog-page-header">

    <div class="catalog-page-header__content">

        <span class="catalog-eyebrow">
            Gestão funcional
        </span>

        <h1>Tipos de vínculo</h1>

        <p>
            Cadastre as formas de contratação utilizadas pela
            Secretaria Municipal de Saúde, como efetivo,
            comissionado, contratado ou estagiário.
        </p>

    </div>

    <?php if (temPermissao('estrutura.gerenciar')): ?>

        <a
            href="<?= escapar(
                appUrl('tipos-vinculo/criar')
            ) ?>"
            class="catalog-primary-button"
        >
            <svg
                viewBox="0 0 24 24"
                aria-hidden="true"
            >
                <path d="M12 5v14"></path>
                <path d="M5 12h14"></path>
            </svg>

            <span>Novo tipo de vínculo</span>
        </a>

    <?php endif; ?>

</section>

<section
    class="catalog-statistics"
    aria-label="Resumo dos tipos de vínculo"
>

    <article class="catalog-statistics__card">

        <span class="catalog-statistics__icon">

            <svg
                viewBox="0 0 24 24"
                aria-hidden="true"
            >
                <path d="M8 7V5a4 4 0 0 1 8 0v2"></path>

                <rect
                    x="3"
                    y="7"
                    width="18"
                    height="13"
                    rx="2"
                ></rect>

                <path d="M3 12h18"></path>
            </svg>

        </span>

        <div>
            <span>Total de vínculos</span>

            <strong>
                <?= $totalTiposVinculo ?>
            </strong>
        </div>

    </article>

    <article class="catalog-statistics__card">

        <span
            class="
                catalog-statistics__icon
                catalog-statistics__icon--active
            "
        >
            <svg
                viewBox="0 0 24 24"
                aria-hidden="true"
            >
                <path d="m5 12 4 4L19 6"></path>
            </svg>
        </span>

        <div>
            <span>Vínculos ativos</span>

            <strong>
                <?= $totalAtivos ?>
            </strong>
        </div>

    </article>

    <article class="catalog-statistics__card">

        <span
            class="
                catalog-statistics__icon
                catalog-statistics__icon--inactive
            "
        >
            <svg
                viewBox="0 0 24 24"
                aria-hidden="true"
            >
                <circle
                    cx="12"
                    cy="12"
                    r="9"
                ></circle>

                <path d="M12 7v5l3 2"></path>
            </svg>
        </span>

        <div>
            <span>Exigem data final</span>

            <strong>
                <?= $totalComDataFim ?>
            </strong>
        </div>

    </article>

</section>

<section class="catalog-panel">

    <form
        method="GET"
        action="<?= escapar(
            appUrl('tipos-vinculo')
        ) ?>"
        class="catalog-filters"
    >

        <div class="catalog-search">

            <span class="catalog-search__icon">

                <svg
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                >
                    <circle
                        cx="11"
                        cy="11"
                        r="7"
                    ></circle>

                    <path d="m20 20-3.5-3.5"></path>
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
                    <?= $situacao === 'TODOS'
                        ? 'selected'
                        : '' ?>
                >
                    Todos
                </option>

                <option
                    value="ATIVOS"
                    <?= $situacao === 'ATIVOS'
                        ? 'selected'
                        : '' ?>
                >
                    Ativos
                </option>

                <option
                    value="INATIVOS"
                    <?= $situacao === 'INATIVOS'
                        ? 'selected'
                        : '' ?>
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

            <?php if (
                $busca !== ''
                || $situacao !== 'TODOS'
            ): ?>

                <a
                    href="<?= escapar(
                        appUrl('tipos-vinculo')
                    ) ?>"
                    class="catalog-clear-button"
                >
                    Limpar
                </a>

            <?php endif; ?>

        </div>

    </form>

    <?php if ($tiposVinculo === []): ?>

        <div class="catalog-empty">

            <span class="catalog-empty__icon">

                <svg
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                >
                    <path d="M8 7V5a4 4 0 0 1 8 0v2"></path>

                    <rect
                        x="3"
                        y="7"
                        width="18"
                        height="13"
                        rx="2"
                    ></rect>

                    <path d="M3 12h18"></path>
                </svg>

            </span>

            <h2>
                Nenhum tipo de vínculo encontrado
            </h2>

            <p>
                <?php if (
                    $busca !== ''
                    || $situacao !== 'TODOS'
                ): ?>

                    Não encontramos vínculos com os filtros
                    informados.

                <?php else: ?>

                    Cadastre a primeira forma de contratação
                    utilizada pela organização.

                <?php endif; ?>
            </p>

            <?php if (
                temPermissao('estrutura.gerenciar')
            ): ?>

                <a
                    href="<?= escapar(
                        appUrl('tipos-vinculo/criar')
                    ) ?>"
                    class="catalog-primary-button"
                >
                    Cadastrar tipo de vínculo
                </a>

            <?php endif; ?>

        </div>

    <?php else: ?>

        <div class="catalog-table-wrapper">

            <table class="catalog-table">

                <thead>
                    <tr>
                        <th>Tipo de vínculo</th>
                        <th>Encerramento</th>
                        <th>Descrição</th>
                        <th>Situação</th>

                        <th class="catalog-table__actions-title">
                            Ações
                        </th>
                    </tr>
                </thead>

                <tbody>

                    <?php foreach (
                        $tiposVinculo as $tipoVinculo
                    ): ?>

                        <?php
                        $tipoVinculoId = (int) (
                            $tipoVinculo['id']
                            ?? 0
                        );

                        $nome = trim(
                            (string) (
                                $tipoVinculo['nome']
                                ?? ''
                            )
                        );

                        $codigo = trim(
                            (string) (
                                $tipoVinculo['codigo']
                                ?? ''
                            )
                        );

                        $descricao = trim(
                            (string) (
                                $tipoVinculo['descricao']
                                ?? ''
                            )
                        );

                        $ativo = filter_var(
                            $tipoVinculo['ativo']
                            ?? false,
                            FILTER_VALIDATE_BOOL
                        );

                        $exigeDataFim = filter_var(
                            $tipoVinculo['exige_data_fim']
                            ?? false,
                            FILTER_VALIDATE_BOOL
                        );

                        $inicial = $nome !== ''
                            ? mb_strtoupper(
                                mb_substr(
                                    $nome,
                                    0,
                                    1
                                )
                            )
                            : 'V';
                        ?>

                        <tr>

                            <td>

                                <div class="catalog-name">

                                    <span class="catalog-name__icon">
                                        <?= escapar($inicial) ?>
                                    </span>

                                    <div>

                                        <strong>
                                            <?= escapar($nome) ?>
                                        </strong>

                                        <span>
                                            <?= $codigo !== ''
                                                ? escapar($codigo)
                                                : 'Sem código' ?>
                                        </span>

                                    </div>

                                </div>

                            </td>

                            <td>

                                <?php if ($exigeDataFim): ?>

                                    <span
                                        class="
                                            status-badge
                                            status-badge--inactive
                                        "
                                    >
                                        <span></span>

                                        Data final obrigatória
                                    </span>

                                <?php else: ?>

                                    <span
                                        class="
                                            status-badge
                                            status-badge--active
                                        "
                                    >
                                        <span></span>

                                        Sem data obrigatória
                                    </span>

                                <?php endif; ?>

                            </td>

                            <td>

                                <p class="catalog-description">
                                    <?= $descricao !== ''
                                        ? escapar($descricao)
                                        : 'Descrição não informada.' ?>
                                </p>

                            </td>

                            <td>

                                <span
                                    class="status-badge <?= $ativo
                                        ? 'status-badge--active'
                                        : 'status-badge--inactive' ?>"
                                >
                                    <span></span>

                                    <?= $ativo
                                        ? 'Ativo'
                                        : 'Inativo' ?>
                                </span>

                            </td>

                            <td>

                                <div class="catalog-actions">

                                    <?php if (
                                        temPermissao(
                                            'estrutura.gerenciar'
                                        )
                                    ): ?>

                                        <a
                                            href="<?= escapar(
                                                appUrl(
                                                    'tipos-vinculo/editar?id='
                                                    . $tipoVinculoId
                                                )
                                            ) ?>"
                                            class="catalog-action-button"
                                            title="Editar tipo de vínculo"
                                            aria-label="Editar <?= escapar($nome) ?>"
                                        >
                                            <svg
                                                viewBox="0 0 24 24"
                                                aria-hidden="true"
                                            >
                                                <path d="M12 20h9"></path>

                                                <path
                                                    d="M16.5 3.5a2.12 2.12 0 0 1 3 3L8 18l-4 1 1-4z"
                                                ></path>
                                            </svg>
                                        </a>

                                        <form
                                            method="POST"
                                            action="<?= escapar(
                                                appUrl(
                                                    'tipos-vinculo/alterar-status'
                                                )
                                            ) ?>"
                                            class="catalog-status-form"
                                            data-catalog-status-form
                                            data-item-name="<?= escapar($nome) ?>"
                                            data-item-type="tipo de vínculo"
                                            data-item-active="<?= $ativo
                                                ? 'true'
                                                : 'false' ?>"
                                        >
                                            <?= campoCsrf() ?>

                                            <input
                                                type="hidden"
                                                name="tipo_vinculo_id"
                                                value="<?= $tipoVinculoId ?>"
                                            >

                                            <button
                                                type="submit"
                                                class="catalog-action-button <?= $ativo
                                                    ? 'catalog-action-button--deactivate'
                                                    : 'catalog-action-button--activate' ?>"
                                                title="<?= $ativo
                                                    ? 'Desativar tipo de vínculo'
                                                    : 'Ativar tipo de vínculo' ?>"
                                                aria-label="<?= $ativo
                                                    ? 'Desativar ' . escapar($nome)
                                                    : 'Ativar ' . escapar($nome) ?>"
                                            >

                                                <?php if ($ativo): ?>

                                                    <svg
                                                        viewBox="0 0 24 24"
                                                        aria-hidden="true"
                                                    >
                                                        <circle
                                                            cx="12"
                                                            cy="12"
                                                            r="9"
                                                        ></circle>

                                                        <path d="M8 12h8"></path>
                                                    </svg>

                                                <?php else: ?>

                                                    <svg
                                                        viewBox="0 0 24 24"
                                                        aria-hidden="true"
                                                    >
                                                        <circle
                                                            cx="12"
                                                            cy="12"
                                                            r="9"
                                                        ></circle>

                                                        <path d="m8 12 3 3 5-6"></path>
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