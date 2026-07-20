<?php

declare(strict_types=1);

/** @var array<int, array<string, mixed>> $jornadas */
/** @var string $busca */
/** @var string $situacao */
/** @var string|null $sucesso */
/** @var string|null $erro */

$jornadas =
    isset($jornadas) && is_array($jornadas)
        ? $jornadas
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

$totalJornadas = count($jornadas);

$totalAtivas = count(
    array_filter(
        $jornadas,
        static function (array $jornada): bool {
            return filter_var(
                $jornada['ativo'] ?? false,
                FILTER_VALIDATE_BOOL
            );
        }
    )
);

$totalComCargaSemanal = count(
    array_filter(
        $jornadas,
        static function (array $jornada): bool {
            $cargaHoraria =
                $jornada['carga_horaria_semanal']
                ?? null;

            return $cargaHoraria !== null
                && $cargaHoraria !== '';
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

        <h1>Jornadas de trabalho</h1>

        <p>
            Cadastre as cargas horárias e escalas utilizadas
            pelos colaboradores da Secretaria Municipal de Saúde.
        </p>

    </div>

    <?php if (temPermissao('estrutura.gerenciar')): ?>

        <a
            href="<?= escapar(
                appUrl('jornadas/criar')
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

            <span>Nova jornada</span>
        </a>

    <?php endif; ?>

</section>

<section
    class="catalog-statistics"
    aria-label="Resumo das jornadas"
>

    <article class="catalog-statistics__card">

        <span class="catalog-statistics__icon">

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
            <span>Total de jornadas</span>

            <strong>
                <?= $totalJornadas ?>
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
            <span>Jornadas ativas</span>

            <strong>
                <?= $totalAtivas ?>
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
                <path d="M8 4v16"></path>
                <path d="M16 4v16"></path>
                <path d="M4 8h16"></path>
                <path d="M4 16h16"></path>
            </svg>
        </span>

        <div>
            <span>Com carga semanal</span>

            <strong>
                <?= $totalComCargaSemanal ?>
            </strong>
        </div>

    </article>

</section>

<section class="catalog-panel">

    <form
        method="GET"
        action="<?= escapar(
            appUrl('jornadas')
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
                placeholder="Buscar por nome, código, descrição ou carga horária"
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
                    Ativas
                </option>

                <option
                    value="INATIVOS"
                    <?= $situacao === 'INATIVOS'
                        ? 'selected'
                        : '' ?>
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

            <?php if (
                $busca !== ''
                || $situacao !== 'TODOS'
            ): ?>

                <a
                    href="<?= escapar(
                        appUrl('jornadas')
                    ) ?>"
                    class="catalog-clear-button"
                >
                    Limpar
                </a>

            <?php endif; ?>

        </div>

    </form>

    <?php if ($jornadas === []): ?>

        <div class="catalog-empty">

            <span class="catalog-empty__icon">

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

            <h2>
                Nenhuma jornada encontrada
            </h2>

            <p>
                <?php if (
                    $busca !== ''
                    || $situacao !== 'TODOS'
                ): ?>

                    Não encontramos jornadas com os filtros
                    informados.

                <?php else: ?>

                    Cadastre a primeira jornada de trabalho
                    utilizada pela organização.

                <?php endif; ?>
            </p>

            <?php if (
                temPermissao('estrutura.gerenciar')
            ): ?>

                <a
                    href="<?= escapar(
                        appUrl('jornadas/criar')
                    ) ?>"
                    class="catalog-primary-button"
                >
                    Cadastrar jornada
                </a>

            <?php endif; ?>

        </div>

    <?php else: ?>

        <div class="catalog-table-wrapper">

            <table class="catalog-table">

                <thead>
                    <tr>
                        <th>Jornada</th>
                        <th>Carga semanal</th>
                        <th>Descrição</th>
                        <th>Situação</th>

                        <th class="catalog-table__actions-title">
                            Ações
                        </th>
                    </tr>
                </thead>

                <tbody>

                    <?php foreach (
                        $jornadas as $jornada
                    ): ?>

                        <?php
                        $jornadaId = (int) (
                            $jornada['id']
                            ?? 0
                        );

                        $nome = trim(
                            (string) (
                                $jornada['nome']
                                ?? ''
                            )
                        );

                        $codigo = trim(
                            (string) (
                                $jornada['codigo']
                                ?? ''
                            )
                        );

                        $descricao = trim(
                            (string) (
                                $jornada['descricao']
                                ?? ''
                            )
                        );

                        $cargaHoraria =
                            $jornada[
                                'carga_horaria_semanal'
                            ]
                            ?? null;

                        $ativo = filter_var(
                            $jornada['ativo']
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
                            : 'J';

                        $cargaFormatada = null;

                        if (
                            $cargaHoraria !== null
                            && $cargaHoraria !== ''
                        ) {
                            $cargaFormatada = number_format(
                                (float) $cargaHoraria,
                                2,
                                ',',
                                '.'
                            );

                            $cargaFormatada = rtrim(
                                rtrim(
                                    $cargaFormatada,
                                    '0'
                                ),
                                ','
                            );
                        }
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

                                <?php if (
                                    $cargaFormatada !== null
                                ): ?>

                                    <span
                                        class="
                                            status-badge
                                            status-badge--active
                                        "
                                    >
                                        <span></span>

                                        <?= escapar(
                                            $cargaFormatada
                                        ) ?>
                                        horas
                                    </span>

                                <?php else: ?>

                                    <span
                                        class="
                                            status-badge
                                            status-badge--inactive
                                        "
                                    >
                                        <span></span>

                                        Escala especial
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
                                        ? 'Ativa'
                                        : 'Inativa' ?>
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
                                                    'jornadas/editar?id='
                                                    . $jornadaId
                                                )
                                            ) ?>"
                                            class="catalog-action-button"
                                            title="Editar jornada"
                                            aria-label="Editar jornada <?= escapar($nome) ?>"
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
                                                    'jornadas/alterar-status'
                                                )
                                            ) ?>"
                                            class="catalog-status-form"
                                            data-catalog-status-form
                                            data-item-name="<?= escapar($nome) ?>"
                                            data-item-type="jornada"
                                            data-item-active="<?= $ativo
                                                ? 'true'
                                                : 'false' ?>"
                                        >
                                            <?= campoCsrf() ?>

                                            <input
                                                type="hidden"
                                                name="jornada_id"
                                                value="<?= $jornadaId ?>"
                                            >

                                            <button
                                                type="submit"
                                                class="catalog-action-button <?= $ativo
                                                    ? 'catalog-action-button--deactivate'
                                                    : 'catalog-action-button--activate' ?>"
                                                title="<?= $ativo
                                                    ? 'Desativar jornada'
                                                    : 'Ativar jornada' ?>"
                                                aria-label="<?= $ativo
                                                    ? 'Desativar jornada ' . escapar($nome)
                                                    : 'Ativar jornada ' . escapar($nome) ?>"
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