<?php

declare(strict_types=1);

/** @var array<string, mixed> $colaborador */
/** @var string|null $sucesso */
/** @var string|null $erro */

$colaborador =
    isset($colaborador) && is_array($colaborador)
    ? $colaborador
    : [];

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

$formatarDataHora = static function (
    mixed $valor
): string {
    $data = trim((string) $valor);

    if ($data === '') {
        return 'Não informada';
    }

    try {
        $dataConvertida =
            new DateTimeImmutable($data);

        return $dataConvertida->format(
            'd/m/Y \à\s H:i'
        );
    } catch (Throwable) {
        return $data;
    }
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
            static fn(string $parte): bool =>
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

$formatarCargaHoraria = static function (
    mixed $valor
): string {
    if (
        $valor === null
        || $valor === ''
    ) {
        return 'Escala especial';
    }

    $carga = number_format(
        (float) $valor,
        2,
        ',',
        '.'
    );

    $carga = rtrim(
        rtrim(
            $carga,
            '0'
        ),
        ','
    );

    return $carga . ' horas semanais';
};

/*
|--------------------------------------------------------------------------
| Dados do colaborador
|--------------------------------------------------------------------------
*/

$colaboradorId = (int) (
    $colaborador['id']
    ?? 0
);

$nomeCompleto = trim(
    (string) (
        $colaborador['nome_completo']
        ?? ''
    )
);

$cpf = $formatarCpf(
    $colaborador['cpf']
        ?? ''
);

$dataNascimento = $formatarData(
    $colaborador['data_nascimento']
        ?? ''
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

$matricula = trim(
    (string) (
        $colaborador['matricula']
        ?? ''
    )
);

$unidadeNome = trim(
    (string) (
        $colaborador['unidade_nome']
        ?? ''
    )
);

$setorNome = trim(
    (string) (
        $colaborador['setor_nome']
        ?? ''
    )
);

$cargoNome = trim(
    (string) (
        $colaborador['cargo_nome']
        ?? ''
    )
);

$funcaoNome = trim(
    (string) (
        $colaborador['funcao_nome']
        ?? ''
    )
);

$tipoVinculoNome = trim(
    (string) (
        $colaborador['tipo_vinculo_nome']
        ?? ''
    )
);

$jornadaNome = trim(
    (string) (
        $colaborador['jornada_nome']
        ?? ''
    )
);

$cargaHoraria = $formatarCargaHoraria(
    $colaborador['carga_horaria_semanal']
        ?? null
);

$dataAdmissao = $formatarData(
    $colaborador['data_admissao']
        ?? ''
);

$dataFimVinculo = $formatarData(
    $colaborador['data_fim_vinculo']
        ?? ''
);

$observacoes = trim(
    (string) (
        $colaborador['observacoes']
        ?? ''
    )
);

$criadoEm = $formatarDataHora(
    $colaborador['criado_em']
        ?? ''
);

$atualizadoEm = $formatarDataHora(
    $colaborador['atualizado_em']
        ?? ''
);

$ativo = filter_var(
    $colaborador['ativo']
        ?? false,
    FILTER_VALIDATE_BOOL
);

$exigeDataFim = filter_var(
    $colaborador['exige_data_fim']
        ?? false,
    FILTER_VALIDATE_BOOL
);

$iniciais = $obterIniciais(
    $nomeCompleto
);
?>

<?php if ($sucesso !== null): ?>

    <div
        class="alert alert--success"
        role="status">
        <?= escapar($sucesso) ?>
    </div>

<?php endif; ?>

<?php if ($erro !== null): ?>

    <div
        class="alert alert--error"
        role="alert">
        <?= escapar($erro) ?>
    </div>

<?php endif; ?>

<section class="employees-page-header">

    <div class="employees-page-header__content">

        <a
            href="<?= escapar(
                        appUrl('colaboradores')
                    ) ?>"
            class="employees-back-link">
            <svg
                viewBox="0 0 24 24"
                aria-hidden="true">
                <path d="m15 18-6-6 6-6"></path>
            </svg>

            <span>
                Voltar para colaboradores
            </span>
        </a>

        <span class="employees-eyebrow">
            Gestão de pessoas
        </span>

        <h1>Perfil do colaborador</h1>

        <p>
            Consulte os dados pessoais e funcionais do
            colaborador selecionado.
        </p>

    </div>

    <div class="employees-page-header__actions">

        <?php if (
            $colaboradorId > 0
            && temPermissao(
                'documentos.visualizar'
            )
        ): ?>

            <a
                href="<?= escapar(
                            appUrl(
                                'colaboradores/documentos?colaborador_id='
                                    . $colaboradorId
                            )
                        ) ?>"
                class="employees-primary-button">
                <svg
                    viewBox="0 0 24 24"
                    aria-hidden="true">
                    <path
                        d="M4 5a2 2 0 0 1 2-2h4l2 2h6
                        a2 2 0 0 1 2 2v12
                        a2 2 0 0 1-2 2H6
                        a2 2 0 0 1-2-2z"></path>

                    <path d="M9 12h6"></path>
                    <path d="M12 9v6"></path>
                </svg>

                <span>Documentos</span>
            </a>

        <?php endif; ?>

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
                class="employees-secondary-button">
                <svg
                    viewBox="0 0 24 24"
                    aria-hidden="true">
                    <path d="M12 20h9"></path>

                    <path
                        d="M16.5 3.5a2.12 2.12
                        0 0 1 3 3L8 18l-4 1 1-4z"></path>
                </svg>

                <span>Editar colaborador</span>
            </a>

        <?php endif; ?>

    </div>

</section>

<section class="employee-profile">

    <!-- =====================================================
         RESUMO PRINCIPAL
    ====================================================== -->

    <article class="employee-profile__hero">

        <div class="employee-profile__identity">

            <span class="employee-profile__avatar">
                <?= escapar($iniciais) ?>
            </span>

            <div class="employee-profile__identity-content">

                <div class="employee-profile__name-line">

                    <h2>
                        <?= escapar($nomeCompleto) ?>
                    </h2>

                    <span
                        class="employees-status <?= $ativo
                                                    ? 'employees-status--active'
                                                    : 'employees-status--inactive' ?>">
                        <span></span>

                        <?= $ativo
                            ? 'Ativo'
                            : 'Inativo' ?>
                    </span>

                </div>

                <p>
                    <?= $cargoNome !== ''
                        ? escapar($cargoNome)
                        : 'Cargo não informado' ?>

                    <?php if ($setorNome !== ''): ?>
                        <span>•</span>
                        <?= escapar($setorNome) ?>
                    <?php endif; ?>
                </p>

                <div class="employee-profile__quick-info">

                    <span>
                        <strong>Matrícula:</strong>

                        <?= $matricula !== ''
                            ? escapar($matricula)
                            : 'Não informada' ?>
                    </span>

                    <span>
                        <strong>CPF:</strong>

                        <?= escapar($cpf) ?>
                    </span>

                    <span>
                        <strong>Admissão:</strong>

                        <?= escapar($dataAdmissao) ?>
                    </span>

                </div>

            </div>

        </div>

        <?php if (
            temPermissao(
                'estrutura.gerenciar'
            )
        ): ?>

            <form
                method="POST"
                action="<?= escapar(
                            appUrl(
                                'colaboradores/alterar-status'
                            )
                        ) ?>"
                class="employee-profile__status-form"
                data-employee-status-form
                data-employee-name="<?= escapar(
                                        $nomeCompleto
                                    ) ?>"
                data-employee-active="<?= $ativo
                                            ? 'true'
                                            : 'false' ?>">
                <?= campoCsrf() ?>

                <input
                    type="hidden"
                    name="colaborador_id"
                    value="<?= $colaboradorId ?>">

                <button
                    type="submit"
                    class="employees-status-button <?= $ativo
                                                        ? 'employees-status-button--deactivate'
                                                        : 'employees-status-button--activate' ?>">
                    <?php if ($ativo): ?>

                        <svg
                            viewBox="0 0 24 24"
                            aria-hidden="true">
                            <circle
                                cx="12"
                                cy="12"
                                r="9"></circle>

                            <path d="M8 12h8"></path>
                        </svg>

                        <span>
                            Desativar colaborador
                        </span>

                    <?php else: ?>

                        <svg
                            viewBox="0 0 24 24"
                            aria-hidden="true">
                            <circle
                                cx="12"
                                cy="12"
                                r="9"></circle>

                            <path d="m8 12 3 3 5-6"></path>
                        </svg>

                        <span>
                            Ativar colaborador
                        </span>

                    <?php endif; ?>
                </button>

            </form>

        <?php endif; ?>

    </article>

    <!-- =====================================================
         CONTEÚDO DO PERFIL
    ====================================================== -->

    <div class="employee-profile__grid">

        <div class="employee-profile__main">

            <!-- DADOS PESSOAIS -->

            <article class="employee-profile-card">

                <header class="employee-profile-card__header">

                    <span class="employee-profile-card__icon">

                        <svg
                            viewBox="0 0 24 24"
                            aria-hidden="true">
                            <circle
                                cx="12"
                                cy="8"
                                r="4"></circle>

                            <path
                                d="M4 21a8 8 0 0 1 16 0"></path>
                        </svg>

                    </span>

                    <div>
                        <h3>Dados pessoais</h3>

                        <p>
                            Identificação e contato do colaborador.
                        </p>
                    </div>

                </header>

                <div class="employee-profile-card__content">

                    <div class="employee-information-grid">

                        <div class="employee-information-item">
                            <span>Nome completo</span>

                            <strong>
                                <?= escapar($nomeCompleto) ?>
                            </strong>
                        </div>

                        <div class="employee-information-item">
                            <span>CPF</span>

                            <strong>
                                <?= escapar($cpf) ?>
                            </strong>
                        </div>

                        <div class="employee-information-item">
                            <span>Data de nascimento</span>

                            <strong>
                                <?= escapar(
                                    $dataNascimento
                                ) ?>
                            </strong>
                        </div>

                        <div class="employee-information-item">
                            <span>Telefone</span>

                            <strong>
                                <?= escapar($telefone) ?>
                            </strong>
                        </div>

                        <div
                            class="
                                employee-information-item
                                employee-information-item--wide
                            ">
                            <span>E-mail</span>

                            <?php if ($email !== ''): ?>

                                <a
                                    href="mailto:<?= escapar(
                                                        $email
                                                    ) ?>">
                                    <?= escapar($email) ?>
                                </a>

                            <?php else: ?>

                                <strong>
                                    Não informado
                                </strong>

                            <?php endif; ?>
                        </div>

                    </div>

                </div>

            </article>

            <!-- DADOS FUNCIONAIS -->

            <article class="employee-profile-card">

                <header class="employee-profile-card__header">

                    <span class="employee-profile-card__icon">

                        <svg
                            viewBox="0 0 24 24"
                            aria-hidden="true">
                            <rect
                                x="3"
                                y="7"
                                width="18"
                                height="13"
                                rx="2"></rect>

                            <path d="M8 7V5a4 4 0 0 1 8 0v2"></path>

                            <path d="M3 12h18"></path>
                        </svg>

                    </span>

                    <div>
                        <h3>Dados funcionais</h3>

                        <p>
                            Posição na estrutura organizacional.
                        </p>
                    </div>

                </header>

                <div class="employee-profile-card__content">

                    <div class="employee-information-grid">

                        <div class="employee-information-item">
                            <span>Matrícula</span>

                            <strong>
                                <?= $matricula !== ''
                                    ? escapar($matricula)
                                    : 'Não informada' ?>
                            </strong>
                        </div>

                        <div class="employee-information-item">
                            <span>Unidade</span>

                            <strong>
                                <?= $unidadeNome !== ''
                                    ? escapar($unidadeNome)
                                    : 'Não informada' ?>
                            </strong>
                        </div>

                        <div class="employee-information-item">
                            <span>Setor</span>

                            <strong>
                                <?= $setorNome !== ''
                                    ? escapar($setorNome)
                                    : 'Não informado' ?>
                            </strong>
                        </div>

                        <div class="employee-information-item">
                            <span>Cargo</span>

                            <strong>
                                <?= $cargoNome !== ''
                                    ? escapar($cargoNome)
                                    : 'Não informado' ?>
                            </strong>
                        </div>

                        <div
                            class="
                                employee-information-item
                                employee-information-item--wide
                            ">
                            <span>Função adicional</span>

                            <strong>
                                <?= $funcaoNome !== ''
                                    ? escapar($funcaoNome)
                                    : 'Nenhuma função adicional' ?>
                            </strong>
                        </div>

                    </div>

                </div>

            </article>

            <!-- VÍNCULO E JORNADA -->

            <article class="employee-profile-card">

                <header class="employee-profile-card__header">

                    <span class="employee-profile-card__icon">

                        <svg
                            viewBox="0 0 24 24"
                            aria-hidden="true">
                            <circle
                                cx="12"
                                cy="12"
                                r="9"></circle>

                            <path d="M12 7v5l3 2"></path>
                        </svg>

                    </span>

                    <div>
                        <h3>Vínculo e jornada</h3>

                        <p>
                            Forma de contratação e período funcional.
                        </p>
                    </div>

                </header>

                <div class="employee-profile-card__content">

                    <div class="employee-information-grid">

                        <div class="employee-information-item">
                            <span>Tipo de vínculo</span>

                            <strong>
                                <?= $tipoVinculoNome !== ''
                                    ? escapar(
                                        $tipoVinculoNome
                                    )
                                    : 'Não informado' ?>
                            </strong>
                        </div>

                        <div class="employee-information-item">
                            <span>Jornada</span>

                            <strong>
                                <?= $jornadaNome !== ''
                                    ? escapar($jornadaNome)
                                    : 'Não informada' ?>
                            </strong>
                        </div>

                        <div class="employee-information-item">
                            <span>Carga horária</span>

                            <strong>
                                <?= escapar($cargaHoraria) ?>
                            </strong>
                        </div>

                        <div class="employee-information-item">
                            <span>Data de admissão</span>

                            <strong>
                                <?= escapar($dataAdmissao) ?>
                            </strong>
                        </div>

                        <div class="employee-information-item">
                            <span>Data final do vínculo</span>

                            <strong>
                                <?= escapar(
                                    $dataFimVinculo
                                ) ?>
                            </strong>
                        </div>

                        <div class="employee-information-item">
                            <span>Prazo determinado</span>

                            <strong>
                                <?= $exigeDataFim
                                    ? 'Sim'
                                    : 'Não' ?>
                            </strong>
                        </div>

                    </div>

                </div>

            </article>

            <!-- OBSERVAÇÕES -->

            <article class="employee-profile-card">

                <header class="employee-profile-card__header">

                    <span class="employee-profile-card__icon">

                        <svg
                            viewBox="0 0 24 24"
                            aria-hidden="true">
                            <path
                                d="M21 15a4 4 0 0 1-4 4H8l-5 3V7
                                a4 4 0 0 1 4-4h10
                                a4 4 0 0 1 4 4z"></path>
                        </svg>

                    </span>

                    <div>
                        <h3>Observações</h3>

                        <p>
                            Informações internas do cadastro.
                        </p>
                    </div>

                </header>

                <div class="employee-profile-card__content">

                    <p class="employee-profile__observations">
                        <?= $observacoes !== ''
                            ? nl2br(
                                escapar($observacoes)
                            )
                            : 'Nenhuma observação foi registrada.' ?>
                    </p>

                </div>

            </article>

        </div>

        <!-- =================================================
             COLUNA LATERAL
        ================================================== -->

        <aside class="employee-profile__sidebar">

            <article class="employee-summary-card">

                <h3>Resumo funcional</h3>

                <div class="employee-summary-card__list">

                    <div>
                        <span>Situação</span>

                        <strong class="<?= $ativo
                                            ? 'is-active'
                                            : 'is-inactive' ?>">
                            <?= $ativo
                                ? 'Ativo'
                                : 'Inativo' ?>
                        </strong>
                    </div>

                    <div>
                        <span>Setor</span>

                        <strong>
                            <?= $setorNome !== ''
                                ? escapar($setorNome)
                                : 'Não informado' ?>
                        </strong>
                    </div>

                    <div>
                        <span>Cargo</span>

                        <strong>
                            <?= $cargoNome !== ''
                                ? escapar($cargoNome)
                                : 'Não informado' ?>
                        </strong>
                    </div>

                    <div>
                        <span>Vínculo</span>

                        <strong>
                            <?= $tipoVinculoNome !== ''
                                ? escapar(
                                    $tipoVinculoNome
                                )
                                : 'Não informado' ?>
                        </strong>
                    </div>

                    <div>
                        <span>Jornada</span>

                        <strong>
                            <?= $jornadaNome !== ''
                                ? escapar($jornadaNome)
                                : 'Não informada' ?>
                        </strong>
                    </div>

                </div>

            </article>

            <article class="employee-summary-card">

                <h3>Controle do registro</h3>

                <div class="employee-summary-card__list">

                    <div>
                        <span>Cadastrado em</span>

                        <strong>
                            <?= escapar($criadoEm) ?>
                        </strong>
                    </div>

                    <div>
                        <span>Última atualização</span>

                        <strong>
                            <?= escapar($atualizadoEm) ?>
                        </strong>
                    </div>

                    <div>
                        <span>Identificador</span>

                        <strong>
                            #<?= $colaboradorId ?>
                        </strong>
                    </div>

                </div>

            </article>

        </aside>

    </div>

</section>