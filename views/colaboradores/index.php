<?php

declare(strict_types=1);

/** @var array<int, array<string, mixed>> $colaboradores */
/** @var string $busca */
/** @var string $situacao */
/** @var string|null $sucesso */
/** @var string|null $erro */

$colaboradores =
    isset($colaboradores) && is_array($colaboradores)
        ? $colaboradores
        : [];

$busca = isset($busca)
    ? trim((string) $busca)
    : '';

$situacao = isset($situacao)
    ? strtoupper(trim((string) $situacao))
    : 'TODOS';

if (
    !in_array(
        $situacao,
        [
            'TODOS',
            'ATIVOS',
            'INATIVOS',
        ],
        true
    )
) {
    $situacao = 'TODOS';
}

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
| Funções locais de formatação
|--------------------------------------------------------------------------
*/

$formatarCpf = static function (
    mixed $valor
): string {
    $cpf = preg_replace(
        '/\D+/',
        '',
        (string) $valor
    ) ?? '';

    if (strlen($cpf) !== 11) {
        return $cpf !== ''
            ? $cpf
            : 'Não informado';
    }

    return substr($cpf, 0, 3)
        . '.'
        . substr($cpf, 3, 3)
        . '.'
        . substr($cpf, 6, 3)
        . '-'
        . substr($cpf, 9, 2);
};

$formatarTelefone = static function (
    mixed $valor
): string {
    $telefone = preg_replace(
        '/\D+/',
        '',
        (string) $valor
    ) ?? '';

    if (strlen($telefone) === 11) {
        return '('
            . substr($telefone, 0, 2)
            . ') '
            . substr($telefone, 2, 5)
            . '-'
            . substr($telefone, 7, 4);
    }

    if (strlen($telefone) === 10) {
        return '('
            . substr($telefone, 0, 2)
            . ') '
            . substr($telefone, 2, 4)
            . '-'
            . substr($telefone, 6, 4);
    }

    return $telefone !== ''
        ? $telefone
        : 'Não informado';
};

$formatarData = static function (
    mixed $valor
): string {
    $data = trim((string) $valor);

    if ($data === '') {
        return 'Não informada';
    }

    $dataConvertida =
        DateTimeImmutable::createFromFormat(
            '!Y-m-d',
            $data
        );

    if (!$dataConvertida) {
        return $data;
    }

    return $dataConvertida->format(
        'd/m/Y'
    );
};

$obterIniciais = static function (
    string $nome
): string {
    $partes = preg_split(
        '/\s+/u',
        trim($nome)
    );

    if (
        !is_array($partes)
        || $partes === []
    ) {
        return 'C';
    }

    $partes = array_values(
        array_filter(
            $partes,
            static fn (string $parte): bool =>
                $parte !== ''
        )
    );

    if ($partes === []) {
        return 'C';
    }

    $primeiraInicial = mb_strtoupper(
        mb_substr(
            $partes[0],
            0,
            1
        )
    );

    if (count($partes) === 1) {
        return $primeiraInicial;
    }

    $ultimaInicial = mb_strtoupper(
        mb_substr(
            $partes[count($partes) - 1],
            0,
            1
        )
    );

    return $primeiraInicial
        . $ultimaInicial;
};

/*
|--------------------------------------------------------------------------
| Indicadores da listagem atual
|--------------------------------------------------------------------------
*/

$totalColaboradores =
    count($colaboradores);

$totalAtivos = count(
    array_filter(
        $colaboradores,
        static function (
            array $colaborador
        ): bool {
            return filter_var(
                $colaborador['ativo']
                ?? false,
                FILTER_VALIDATE_BOOL
            );
        }
    )
);

$totalInativos =
    $totalColaboradores
    - $totalAtivos;
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

<section class="employees-page-header">

    <div class="employees-page-header__content">

        <span class="employees-eyebrow">
            Gestão de pessoas
        </span>

        <h1>Colaboradores</h1>

        <p>
            Consulte e gerencie os dados pessoais e funcionais
            dos colaboradores da organização.
        </p>

    </div>

    <?php if (
        temPermissao(
            'estrutura.gerenciar'
        )
    ): ?>

        <a
            href="<?= escapar(
                appUrl(
                    'colaboradores/criar'
                )
            ) ?>"
            class="employees-primary-button"
        >
            <svg
                viewBox="0 0 24 24"
                aria-hidden="true"
            >
                <path d="M12 5v14"></path>
                <path d="M5 12h14"></path>
            </svg>

            <span>
                Novo colaborador
            </span>
        </a>

    <?php endif; ?>

</section>

<section
    class="employees-statistics"
    aria-label="Resumo dos colaboradores"
>

    <article class="employees-statistics__card">

        <span class="employees-statistics__icon">

            <svg
                viewBox="0 0 24 24"
                aria-hidden="true"
            >
                <path
                    d="M16 21v-2a4 4 0 0 0-4-4H6
                    a4 4 0 0 0-4 4v2"
                ></path>

                <circle
                    cx="9"
                    cy="7"
                    r="4"
                ></circle>

                <path
                    d="M22 21v-2a4 4 0 0 0-3-3.87"
                ></path>

                <path
                    d="M16 3.13a4 4 0 0 1 0 7.75"
                ></path>
            </svg>

        </span>

        <div>
            <span>Total exibido</span>

            <strong>
                <?= $totalColaboradores ?>
            </strong>
        </div>

    </article>

    <article class="employees-statistics__card">

        <span
            class="
                employees-statistics__icon
                employees-statistics__icon--active
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
            <span>Ativos</span>

            <strong>
                <?= $totalAtivos ?>
            </strong>
        </div>

    </article>

    <article class="employees-statistics__card">

        <span
            class="
                employees-statistics__icon
                employees-statistics__icon--inactive
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

                <path d="M8 12h8"></path>
            </svg>
        </span>

        <div>
            <span>Inativos</span>

            <strong>
                <?= $totalInativos ?>
            </strong>
        </div>

    </article>

</section>

<section class="employees-panel">

    <form
        method="GET"
        action="<?= escapar(
            appUrl('colaboradores')
        ) ?>"
        class="employees-filters"
    >

        <div class="employees-search">

            <span class="employees-search__icon">

                <svg
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                >
                    <circle
                        cx="11"
                        cy="11"
                        r="7"
                    ></circle>

                    <path
                        d="m20 20-3.5-3.5"
                    ></path>
                </svg>

            </span>

            <input
                type="search"
                name="busca"
                value="<?= escapar($busca) ?>"
                placeholder="Buscar por nome, CPF, matrícula, cargo ou setor"
                maxlength="180"
            >

        </div>

        <div class="employees-filter-group">

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
                class="employees-filter-button"
            >
                Filtrar
            </button>

            <?php if (
                $busca !== ''
                || $situacao !== 'TODOS'
            ): ?>

                <a
                    href="<?= escapar(
                        appUrl('colaboradores')
                    ) ?>"
                    class="employees-clear-button"
                >
                    Limpar
                </a>

            <?php endif; ?>

        </div>

    </form>

    <?php if ($colaboradores === []): ?>

        <div class="employees-empty">

            <span class="employees-empty__icon">

                <svg
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                >
                    <path
                        d="M16 21v-2a4 4 0 0 0-4-4H6
                        a4 4 0 0 0-4 4v2"
                    ></path>

                    <circle
                        cx="9"
                        cy="7"
                        r="4"
                    ></circle>

                    <path
                        d="M19 8v6"
                    ></path>

                    <path
                        d="M22 11h-6"
                    ></path>
                </svg>

            </span>

            <h2>
                Nenhum colaborador encontrado
            </h2>

            <p>
                <?php if (
                    $busca !== ''
                    || $situacao !== 'TODOS'
                ): ?>

                    Não encontramos colaboradores com os
                    filtros informados.

                <?php else: ?>

                    Cadastre o primeiro colaborador para
                    iniciar a gestão de pessoas.

                <?php endif; ?>
            </p>

            <?php if (
                temPermissao(
                    'estrutura.gerenciar'
                )
            ): ?>

                <a
                    href="<?= escapar(
                        appUrl(
                            'colaboradores/criar'
                        )
                    ) ?>"
                    class="employees-primary-button"
                >
                    Cadastrar colaborador
                </a>

            <?php endif; ?>

        </div>

    <?php else: ?>

        <div class="employees-table-wrapper">

            <table class="employees-table">

                <thead>
                    <tr>
                        <th>Colaborador</th>
                        <th>Dados funcionais</th>
                        <th>Vínculo</th>
                        <th>Contato</th>
                        <th>Situação</th>

                        <th
                            class="
                                employees-table__actions-title
                            "
                        >
                            Ações
                        </th>
                    </tr>
                </thead>

                <tbody>

                    <?php foreach (
                        $colaboradores
                        as $colaborador
                    ): ?>

                        <?php
                        $colaboradorId = (int) (
                            $colaborador['id']
                            ?? 0
                        );

                        $nome = trim(
                            (string) (
                                $colaborador[
                                    'nome_completo'
                                ]
                                ?? ''
                            )
                        );

                        $cpf = $formatarCpf(
                            $colaborador['cpf']
                            ?? ''
                        );

                        $matricula = trim(
                            (string) (
                                $colaborador[
                                    'matricula'
                                ]
                                ?? ''
                            )
                        );

                        $setorNome = trim(
                            (string) (
                                $colaborador[
                                    'setor_nome'
                                ]
                                ?? ''
                            )
                        );

                        $cargoNome = trim(
                            (string) (
                                $colaborador[
                                    'cargo_nome'
                                ]
                                ?? ''
                            )
                        );

                        $funcaoNome = trim(
                            (string) (
                                $colaborador[
                                    'funcao_nome'
                                ]
                                ?? ''
                            )
                        );

                        $tipoVinculoNome = trim(
                            (string) (
                                $colaborador[
                                    'tipo_vinculo_nome'
                                ]
                                ?? ''
                            )
                        );

                        $jornadaNome = trim(
                            (string) (
                                $colaborador[
                                    'jornada_nome'
                                ]
                                ?? ''
                            )
                        );

                        $email = trim(
                            (string) (
                                $colaborador['email']
                                ?? ''
                            )
                        );

                        $telefone = $formatarTelefone(
                            $colaborador['telefone']
                            ?? ''
                        );

                        $dataAdmissao = $formatarData(
                            $colaborador[
                                'data_admissao'
                            ]
                            ?? ''
                        );

                        $ativo = filter_var(
                            $colaborador['ativo']
                            ?? false,
                            FILTER_VALIDATE_BOOL
                        );

                        $iniciais =
                            $obterIniciais($nome);
                        ?>

                        <tr>

                            <td>

                                <div class="employees-person">

                                    <span
                                        class="
                                            employees-person__avatar
                                        "
                                    >
                                        <?= escapar(
                                            $iniciais
                                        ) ?>
                                    </span>

                                    <div
                                        class="
                                            employees-person__content
                                        "
                                    >

                                        <strong>
                                            <?= escapar(
                                                $nome
                                            ) ?>
                                        </strong>

                                        <span>
                                            CPF:
                                            <?= escapar(
                                                $cpf
                                            ) ?>
                                        </span>

                                        <span>
                                            Matrícula:
                                            <?= $matricula !== ''
                                                ? escapar(
                                                    $matricula
                                                )
                                                : 'Não informada' ?>
                                        </span>

                                    </div>

                                </div>

                            </td>

                            <td>

                                <div
                                    class="
                                        employees-detail-stack
                                    "
                                >
                                    <strong>
                                        <?= $cargoNome !== ''
                                            ? escapar(
                                                $cargoNome
                                            )
                                            : 'Cargo não informado' ?>
                                    </strong>

                                    <span>
                                        <?= $setorNome !== ''
                                            ? escapar(
                                                $setorNome
                                            )
                                            : 'Setor não informado' ?>
                                    </span>

                                    <?php if (
                                        $funcaoNome !== ''
                                    ): ?>

                                        <small>
                                            Função:
                                            <?= escapar(
                                                $funcaoNome
                                            ) ?>
                                        </small>

                                    <?php endif; ?>
                                </div>

                            </td>

                            <td>

                                <div
                                    class="
                                        employees-detail-stack
                                    "
                                >
                                    <strong>
                                        <?= $tipoVinculoNome !== ''
                                            ? escapar(
                                                $tipoVinculoNome
                                            )
                                            : 'Não informado' ?>
                                    </strong>

                                    <span>
                                        <?= $jornadaNome !== ''
                                            ? escapar(
                                                $jornadaNome
                                            )
                                            : 'Jornada não informada' ?>
                                    </span>

                                    <small>
                                        Admissão:
                                        <?= escapar(
                                            $dataAdmissao
                                        ) ?>
                                    </small>
                                </div>

                            </td>

                            <td>

                                <div
                                    class="
                                        employees-detail-stack
                                    "
                                >
                                    <strong>
                                        <?= $telefone !== ''
                                            ? escapar(
                                                $telefone
                                            )
                                            : 'Não informado' ?>
                                    </strong>

                                    <span>
                                        <?= $email !== ''
                                            ? escapar(
                                                $email
                                            )
                                            : 'E-mail não informado' ?>
                                    </span>
                                </div>

                            </td>

                            <td>

                                <span
                                    class="employees-status <?= $ativo
                                        ? 'employees-status--active'
                                        : 'employees-status--inactive' ?>"
                                >
                                    <span></span>

                                    <?= $ativo
                                        ? 'Ativo'
                                        : 'Inativo' ?>
                                </span>

                            </td>

                            <td>

                                <div class="employees-actions">

                                    <a
                                        href="<?= escapar(
                                            appUrl(
                                                'colaboradores/visualizar?id='
                                                . $colaboradorId
                                            )
                                        ) ?>"
                                        class="employees-action-button"
                                        title="Visualizar colaborador"
                                        aria-label="Visualizar <?= escapar($nome) ?>"
                                    >
                                        <svg
                                            viewBox="0 0 24 24"
                                            aria-hidden="true"
                                        >
                                            <path
                                                d="M2 12s3.5-7 10-7
                                                10 7 10 7-3.5 7-10 7
                                               S2 12 2 12z"
                                            ></path>

                                            <circle
                                                cx="12"
                                                cy="12"
                                                r="3"
                                            ></circle>
                                        </svg>
                                    </a>

                                    <?php if (
                                        temPermissao(
                                            'estrutura.gerenciar'
                                        )
                                    ): ?>

                                        <a
                                            href="<?= escapar(
                                                appUrl(
                                                    'colaboradores/editar?id='
                                                    . $colaboradorId
                                                )
                                            ) ?>"
                                            class="employees-action-button"
                                            title="Editar colaborador"
                                            aria-label="Editar <?= escapar($nome) ?>"
                                        >
                                            <svg
                                                viewBox="0 0 24 24"
                                                aria-hidden="true"
                                            >
                                                <path
                                                    d="M12 20h9"
                                                ></path>

                                                <path
                                                    d="M16.5 3.5
                                                    a2.12 2.12 0 0 1 3 3
                                                    L8 18l-4 1 1-4z"
                                                ></path>
                                            </svg>
                                        </a>

                                        <form
                                            method="POST"
                                            action="<?= escapar(
                                                appUrl(
                                                    'colaboradores/alterar-status'
                                                )
                                            ) ?>"
                                            class="employees-status-form"
                                            data-employee-status-form
                                            data-employee-name="<?= escapar($nome) ?>"
                                            data-employee-active="<?= $ativo
                                                ? 'true'
                                                : 'false' ?>"
                                        >
                                            <?= campoCsrf() ?>

                                            <input
                                                type="hidden"
                                                name="colaborador_id"
                                                value="<?= $colaboradorId ?>"
                                            >

                                            <button
                                                type="submit"
                                                class="employees-action-button <?= $ativo
                                                    ? 'employees-action-button--deactivate'
                                                    : 'employees-action-button--activate' ?>"
                                                title="<?= $ativo
                                                    ? 'Desativar colaborador'
                                                    : 'Ativar colaborador' ?>"
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

                                                        <path
                                                            d="M8 12h8"
                                                        ></path>
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

                                                        <path
                                                            d="m8 12 3 3 5-6"
                                                        ></path>
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