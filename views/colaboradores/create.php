<?php

declare(strict_types=1);

/** @var array<string, mixed> $dadosFormulario */
/** @var array<string, string> $errosFormulario */
/** @var array<int, array<string, mixed>> $setores */
/** @var array<int, array<string, mixed>> $cargos */
/** @var array<int, array<string, mixed>> $funcoes */
/** @var array<int, array<string, mixed>> $tiposVinculo */
/** @var array<int, array<string, mixed>> $jornadas */
/** @var string|null $erro */

$dadosFormulario =
    isset($dadosFormulario) && is_array($dadosFormulario)
        ? $dadosFormulario
        : [];

$errosFormulario =
    isset($errosFormulario) && is_array($errosFormulario)
        ? $errosFormulario
        : [];

$setores =
    isset($setores) && is_array($setores)
        ? $setores
        : [];

$cargos =
    isset($cargos) && is_array($cargos)
        ? $cargos
        : [];

$funcoes =
    isset($funcoes) && is_array($funcoes)
        ? $funcoes
        : [];

$tiposVinculo =
    isset($tiposVinculo) && is_array($tiposVinculo)
        ? $tiposVinculo
        : [];

$jornadas =
    isset($jornadas) && is_array($jornadas)
        ? $jornadas
        : [];

$erro =
    isset($erro) && is_string($erro)
        ? $erro
        : null;

/*
|--------------------------------------------------------------------------
| Valores do formulário
|--------------------------------------------------------------------------
*/

$nomeCompleto = trim(
    (string) (
        $dadosFormulario['nome_completo']
        ?? ''
    )
);

$cpf = preg_replace(
    '/\D+/',
    '',
    (string) (
        $dadosFormulario['cpf']
        ?? ''
    )
) ?? '';

$dataNascimento = trim(
    (string) (
        $dadosFormulario['data_nascimento']
        ?? ''
    )
);

$email = trim(
    (string) (
        $dadosFormulario['email']
        ?? ''
    )
);

$telefone = preg_replace(
    '/\D+/',
    '',
    (string) (
        $dadosFormulario['telefone']
        ?? ''
    )
) ?? '';

$matricula = trim(
    (string) (
        $dadosFormulario['matricula']
        ?? ''
    )
);

$setorId = (int) (
    $dadosFormulario['setor_id']
    ?? 0
);

$cargoId = (int) (
    $dadosFormulario['cargo_id']
    ?? 0
);

$funcaoId = (
    $dadosFormulario['funcao_id']
    ?? null
);

$funcaoId = $funcaoId !== null
    && $funcaoId !== ''
        ? (int) $funcaoId
        : null;

$tipoVinculoId = (int) (
    $dadosFormulario['tipo_vinculo_id']
    ?? 0
);

$jornadaTrabalhoId = (int) (
    $dadosFormulario['jornada_trabalho_id']
    ?? 0
);

$dataAdmissao = trim(
    (string) (
        $dadosFormulario['data_admissao']
        ?? ''
    )
);

$dataFimVinculo = trim(
    (string) (
        $dadosFormulario['data_fim_vinculo']
        ?? ''
    )
);

$observacoes = trim(
    (string) (
        $dadosFormulario['observacoes']
        ?? ''
    )
);

$ativo = array_key_exists(
    'ativo',
    $dadosFormulario
)
    ? filter_var(
        $dadosFormulario['ativo'],
        FILTER_VALIDATE_BOOL
    )
    : true;

/*
|--------------------------------------------------------------------------
| Formatação visual de CPF e telefone
|--------------------------------------------------------------------------
*/

$formatarCpf = static function (
    string $valor
): string {
    if (strlen($valor) !== 11) {
        return $valor;
    }

    return substr($valor, 0, 3)
        . '.'
        . substr($valor, 3, 3)
        . '.'
        . substr($valor, 6, 3)
        . '-'
        . substr($valor, 9, 2);
};

$formatarTelefone = static function (
    string $valor
): string {
    if (strlen($valor) === 11) {
        return '('
            . substr($valor, 0, 2)
            . ') '
            . substr($valor, 2, 5)
            . '-'
            . substr($valor, 7, 4);
    }

    if (strlen($valor) === 10) {
        return '('
            . substr($valor, 0, 2)
            . ') '
            . substr($valor, 2, 4)
            . '-'
            . substr($valor, 6, 4);
    }

    return $valor;
};

$cpfExibido =
    $formatarCpf($cpf);

$telefoneExibido =
    $formatarTelefone($telefone);
?>

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

        <a
            href="<?= escapar(
                appUrl('colaboradores')
            ) ?>"
            class="employees-back-link"
        >
            <svg
                viewBox="0 0 24 24"
                aria-hidden="true"
            >
                <path d="m15 18-6-6 6-6"></path>
            </svg>

            <span>
                Voltar para colaboradores
            </span>
        </a>

        <span class="employees-eyebrow">
            Gestão de pessoas
        </span>

        <h1>Novo colaborador</h1>

        <p>
            Preencha os dados pessoais e funcionais para
            cadastrar um novo colaborador.
        </p>

    </div>

</section>

<section class="employees-form-card">

    <form
        method="POST"
        action="<?= escapar(
            appUrl('colaboradores/criar')
        ) ?>"
        class="employees-form"
        data-employee-form
        novalidate
    >

        <?= campoCsrf() ?>

        <!-- =====================================================
             DADOS PESSOAIS
        ====================================================== -->

        <div class="employees-form__section">

            <header class="employees-form__section-header">

                <span class="employees-form__section-number">
                    01
                </span>

                <div>
                    <h2>Dados pessoais</h2>

                    <p>
                        Informe os dados de identificação e contato
                        do colaborador.
                    </p>
                </div>

            </header>

            <div class="employees-form__grid">

                <div
                    class="
                        employees-field
                        employees-field--wide
                    "
                >

                    <label for="nome_completo">
                        Nome completo
                        <span>*</span>
                    </label>

                    <input
                        type="text"
                        id="nome_completo"
                        name="nome_completo"
                        value="<?= escapar(
                            $nomeCompleto
                        ) ?>"
                        maxlength="180"
                        placeholder="Ex.: Maria da Silva"
                        autocomplete="name"
                        class="<?= isset(
                            $errosFormulario[
                                'nome_completo'
                            ]
                        )
                            ? 'is-invalid'
                            : '' ?>"
                        autofocus
                        required
                    >

                    <?php if (
                        isset(
                            $errosFormulario[
                                'nome_completo'
                            ]
                        )
                    ): ?>

                        <small class="employees-form-error">
                            <?= escapar(
                                $errosFormulario[
                                    'nome_completo'
                                ]
                            ) ?>
                        </small>

                    <?php endif; ?>

                </div>

                <div class="employees-field">

                    <label for="cpf">
                        CPF
                        <span>*</span>
                    </label>

                    <input
                        type="text"
                        id="cpf"
                        name="cpf"
                        value="<?= escapar(
                            $cpfExibido
                        ) ?>"
                        maxlength="14"
                        placeholder="000.000.000-00"
                        inputmode="numeric"
                        autocomplete="off"
                        class="<?= isset(
                            $errosFormulario['cpf']
                        )
                            ? 'is-invalid'
                            : '' ?>"
                        data-cpf
                        required
                    >

                    <?php if (
                        isset($errosFormulario['cpf'])
                    ): ?>

                        <small class="employees-form-error">
                            <?= escapar(
                                $errosFormulario['cpf']
                            ) ?>
                        </small>

                    <?php endif; ?>

                </div>

                <div class="employees-field">

                    <label for="data_nascimento">
                        Data de nascimento
                    </label>

                    <input
                        type="date"
                        id="data_nascimento"
                        name="data_nascimento"
                        value="<?= escapar(
                            $dataNascimento
                        ) ?>"
                        max="<?= escapar(
                            date('Y-m-d')
                        ) ?>"
                        class="<?= isset(
                            $errosFormulario[
                                'data_nascimento'
                            ]
                        )
                            ? 'is-invalid'
                            : '' ?>"
                    >

                    <?php if (
                        isset(
                            $errosFormulario[
                                'data_nascimento'
                            ]
                        )
                    ): ?>

                        <small class="employees-form-error">
                            <?= escapar(
                                $errosFormulario[
                                    'data_nascimento'
                                ]
                            ) ?>
                        </small>

                    <?php endif; ?>

                </div>

                <div class="employees-field">

                    <label for="email">
                        E-mail
                    </label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="<?= escapar($email) ?>"
                        maxlength="160"
                        placeholder="nome@exemplo.com"
                        autocomplete="email"
                        class="<?= isset(
                            $errosFormulario['email']
                        )
                            ? 'is-invalid'
                            : '' ?>"
                    >

                    <?php if (
                        isset($errosFormulario['email'])
                    ): ?>

                        <small class="employees-form-error">
                            <?= escapar(
                                $errosFormulario['email']
                            ) ?>
                        </small>

                    <?php endif; ?>

                </div>

                <div class="employees-field">

                    <label for="telefone">
                        Telefone
                    </label>

                    <input
                        type="text"
                        id="telefone"
                        name="telefone"
                        value="<?= escapar(
                            $telefoneExibido
                        ) ?>"
                        maxlength="15"
                        placeholder="(00) 00000-0000"
                        inputmode="tel"
                        autocomplete="tel"
                        class="<?= isset(
                            $errosFormulario['telefone']
                        )
                            ? 'is-invalid'
                            : '' ?>"
                        data-phone
                    >

                    <?php if (
                        isset(
                            $errosFormulario['telefone']
                    )
                    ): ?>

                        <small class="employees-form-error">
                            <?= escapar(
                                $errosFormulario['telefone']
                            ) ?>
                        </small>

                    <?php endif; ?>

                </div>

            </div>

        </div>

        <!-- =====================================================
             DADOS FUNCIONAIS
        ====================================================== -->

        <div class="employees-form__section">

            <header class="employees-form__section-header">

                <span class="employees-form__section-number">
                    02
                </span>

                <div>
                    <h2>Dados funcionais</h2>

                    <p>
                        Defina a matrícula e a posição do
                        colaborador na estrutura organizacional.
                    </p>
                </div>

            </header>

            <div class="employees-form__grid">

                <div class="employees-field">

                    <label for="matricula">
                        Matrícula
                    </label>

                    <input
                        type="text"
                        id="matricula"
                        name="matricula"
                        value="<?= escapar(
                            $matricula
                        ) ?>"
                        maxlength="50"
                        placeholder="Ex.: 000154"
                        autocomplete="off"
                        class="<?= isset(
                            $errosFormulario[
                                'matricula'
                            ]
                        )
                            ? 'is-invalid'
                            : '' ?>"
                    >

                    <?php if (
                        isset(
                            $errosFormulario[
                                'matricula'
                            ]
                        )
                    ): ?>

                        <small class="employees-form-error">
                            <?= escapar(
                                $errosFormulario[
                                    'matricula'
                                ]
                            ) ?>
                        </small>

                    <?php else: ?>

                        <small class="employees-form-help">
                            Campo opcional, mas não pode se repetir.
                        </small>

                    <?php endif; ?>

                </div>

                <div class="employees-field">

                    <label for="setor_id">
                        Setor
                        <span>*</span>
                    </label>

                    <select
                        id="setor_id"
                        name="setor_id"
                        class="<?= isset(
                            $errosFormulario['setor_id']
                        )
                            ? 'is-invalid'
                            : '' ?>"
                        required
                    >
                        <option value="">
                            Selecione o setor
                        </option>

                        <?php foreach (
                            $setores as $setor
                        ): ?>

                            <?php
                            $opcaoSetorId = (int) (
                                $setor['id']
                                ?? 0
                            );

                            $opcaoSetorNome = trim(
                                (string) (
                                    $setor['nome']
                                    ?? ''
                                )
                            );
                            ?>

                            <option
                                value="<?= $opcaoSetorId ?>"
                                <?= $setorId
                                    === $opcaoSetorId
                                        ? 'selected'
                                        : '' ?>
                            >
                                <?= escapar(
                                    $opcaoSetorNome
                                ) ?>
                            </option>

                        <?php endforeach; ?>
                    </select>

                    <?php if (
                        isset(
                            $errosFormulario['setor_id']
                        )
                    ): ?>

                        <small class="employees-form-error">
                            <?= escapar(
                                $errosFormulario['setor_id']
                            ) ?>
                        </small>

                    <?php endif; ?>

                </div>

                <div class="employees-field">

                    <label for="cargo_id">
                        Cargo
                        <span>*</span>
                    </label>

                    <select
                        id="cargo_id"
                        name="cargo_id"
                        class="<?= isset(
                            $errosFormulario['cargo_id']
                        )
                            ? 'is-invalid'
                            : '' ?>"
                        required
                    >
                        <option value="">
                            Selecione o cargo
                        </option>

                        <?php foreach (
                            $cargos as $cargo
                        ): ?>

                            <?php
                            $opcaoCargoId = (int) (
                                $cargo['id']
                                ?? 0
                            );

                            $opcaoCargoNome = trim(
                                (string) (
                                    $cargo['nome']
                                    ?? ''
                                )
                            );
                            ?>

                            <option
                                value="<?= $opcaoCargoId ?>"
                                <?= $cargoId
                                    === $opcaoCargoId
                                        ? 'selected'
                                        : '' ?>
                            >
                                <?= escapar(
                                    $opcaoCargoNome
                                ) ?>
                            </option>

                        <?php endforeach; ?>
                    </select>

                    <?php if (
                        isset(
                            $errosFormulario['cargo_id']
                        )
                    ): ?>

                        <small class="employees-form-error">
                            <?= escapar(
                                $errosFormulario['cargo_id']
                            ) ?>
                        </small>

                    <?php endif; ?>

                </div>

                <div class="employees-field">

                    <label for="funcao_id">
                        Função
                    </label>

                    <select
                        id="funcao_id"
                        name="funcao_id"
                        class="<?= isset(
                            $errosFormulario['funcao_id']
                        )
                            ? 'is-invalid'
                            : '' ?>"
                    >
                        <option value="">
                            Nenhuma função adicional
                        </option>

                        <?php foreach (
                            $funcoes as $funcao
                        ): ?>

                            <?php
                            $opcaoFuncaoId = (int) (
                                $funcao['id']
                                ?? 0
                            );

                            $opcaoFuncaoNome = trim(
                                (string) (
                                    $funcao['nome']
                                    ?? ''
                                )
                            );
                            ?>

                            <option
                                value="<?= $opcaoFuncaoId ?>"
                                <?= $funcaoId
                                    === $opcaoFuncaoId
                                        ? 'selected'
                                        : '' ?>
                            >
                                <?= escapar(
                                    $opcaoFuncaoNome
                                ) ?>
                            </option>

                        <?php endforeach; ?>
                    </select>

                    <?php if (
                        isset(
                            $errosFormulario['funcao_id']
                        )
                    ): ?>

                        <small class="employees-form-error">
                            <?= escapar(
                                $errosFormulario['funcao_id']
                            ) ?>
                        </small>

                    <?php else: ?>

                        <small class="employees-form-help">
                            Use somente quando o colaborador possuir
                            uma responsabilidade adicional.
                        </small>

                    <?php endif; ?>

                </div>

            </div>

        </div>

        <!-- =====================================================
             VÍNCULO E JORNADA
        ====================================================== -->

        <div class="employees-form__section">

            <header class="employees-form__section-header">

                <span class="employees-form__section-number">
                    03
                </span>

                <div>
                    <h2>Vínculo e jornada</h2>

                    <p>
                        Informe a forma de contratação, jornada
                        e período do vínculo.
                    </p>
                </div>

            </header>

            <div class="employees-form__grid">

                <div class="employees-field">

                    <label for="tipo_vinculo_id">
                        Tipo de vínculo
                        <span>*</span>
                    </label>

                    <select
                        id="tipo_vinculo_id"
                        name="tipo_vinculo_id"
                        class="<?= isset(
                            $errosFormulario[
                                'tipo_vinculo_id'
                            ]
                        )
                            ? 'is-invalid'
                            : '' ?>"
                        data-contract-type
                        required
                    >
                        <option value="">
                            Selecione o vínculo
                        </option>

                        <?php foreach (
                            $tiposVinculo
                            as $tipoVinculo
                        ): ?>

                            <?php
                            $opcaoTipoVinculoId = (int) (
                                $tipoVinculo['id']
                                ?? 0
                            );

                            $opcaoTipoVinculoNome = trim(
                                (string) (
                                    $tipoVinculo['nome']
                                    ?? ''
                                )
                            );

                            $exigeDataFim = filter_var(
                                $tipoVinculo[
                                    'exige_data_fim'
                                ]
                                ?? false,
                                FILTER_VALIDATE_BOOL
                            );
                            ?>

                            <option
                                value="<?= $opcaoTipoVinculoId ?>"
                                data-requires-end-date="<?= $exigeDataFim
                                    ? 'true'
                                    : 'false' ?>"
                                <?= $tipoVinculoId
                                    === $opcaoTipoVinculoId
                                        ? 'selected'
                                        : '' ?>
                            >
                                <?= escapar(
                                    $opcaoTipoVinculoNome
                                ) ?>
                            </option>

                        <?php endforeach; ?>
                    </select>

                    <?php if (
                        isset(
                            $errosFormulario[
                                'tipo_vinculo_id'
                            ]
                        )
                    ): ?>

                        <small class="employees-form-error">
                            <?= escapar(
                                $errosFormulario[
                                    'tipo_vinculo_id'
                                ]
                            ) ?>
                        </small>

                    <?php endif; ?>

                </div>

                <div class="employees-field">

                    <label for="jornada_trabalho_id">
                        Jornada de trabalho
                        <span>*</span>
                    </label>

                    <select
                        id="jornada_trabalho_id"
                        name="jornada_trabalho_id"
                        class="<?= isset(
                            $errosFormulario[
                                'jornada_trabalho_id'
                            ]
                        )
                            ? 'is-invalid'
                            : '' ?>"
                        required
                    >
                        <option value="">
                            Selecione a jornada
                        </option>

                        <?php foreach (
                            $jornadas as $jornada
                        ): ?>

                            <?php
                            $opcaoJornadaId = (int) (
                                $jornada['id']
                                ?? 0
                            );

                            $opcaoJornadaNome = trim(
                                (string) (
                                    $jornada['nome']
                                    ?? ''
                                )
                            );

                            $cargaSemanal =
                                $jornada[
                                    'carga_horaria_semanal'
                                ]
                                ?? null;

                            $textoJornada =
                                $opcaoJornadaNome;

                            if (
                                $cargaSemanal !== null
                                && $cargaSemanal !== ''
                            ) {
                                $cargaFormatada =
                                    number_format(
                                        (float) $cargaSemanal,
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

                                $textoJornada .=
                                    ' — '
                                    . $cargaFormatada
                                    . 'h semanais';
                            }
                            ?>

                            <option
                                value="<?= $opcaoJornadaId ?>"
                                <?= $jornadaTrabalhoId
                                    === $opcaoJornadaId
                                        ? 'selected'
                                        : '' ?>
                            >
                                <?= escapar(
                                    $textoJornada
                                ) ?>
                            </option>

                        <?php endforeach; ?>
                    </select>

                    <?php if (
                        isset(
                            $errosFormulario[
                                'jornada_trabalho_id'
                            ]
                        )
                    ): ?>

                        <small class="employees-form-error">
                            <?= escapar(
                                $errosFormulario[
                                    'jornada_trabalho_id'
                                ]
                            ) ?>
                        </small>

                    <?php endif; ?>

                </div>

                <div class="employees-field">

                    <label for="data_admissao">
                        Data de admissão
                        <span>*</span>
                    </label>

                    <input
                        type="date"
                        id="data_admissao"
                        name="data_admissao"
                        value="<?= escapar(
                            $dataAdmissao
                        ) ?>"
                        class="<?= isset(
                            $errosFormulario[
                                'data_admissao'
                            ]
                        )
                            ? 'is-invalid'
                            : '' ?>"
                        required
                    >

                    <?php if (
                        isset(
                            $errosFormulario[
                                'data_admissao'
                            ]
                        )
                    ): ?>

                        <small class="employees-form-error">
                            <?= escapar(
                                $errosFormulario[
                                    'data_admissao'
                                ]
                            ) ?>
                        </small>

                    <?php endif; ?>

                </div>

                <div
                    class="employees-field"
                    data-end-date-field
                >

                    <label for="data_fim_vinculo">
                        Data final do vínculo

                        <span
                            data-end-date-required
                            hidden
                        >
                            *
                        </span>
                    </label>

                    <input
                        type="date"
                        id="data_fim_vinculo"
                        name="data_fim_vinculo"
                        value="<?= escapar(
                            $dataFimVinculo
                        ) ?>"
                        min="<?= escapar(
                            $dataAdmissao
                        ) ?>"
                        class="<?= isset(
                            $errosFormulario[
                                'data_fim_vinculo'
                            ]
                        )
                            ? 'is-invalid'
                            : '' ?>"
                        data-contract-end-date
                    >

                    <?php if (
                        isset(
                            $errosFormulario[
                                'data_fim_vinculo'
                            ]
                        )
                    ): ?>

                        <small class="employees-form-error">
                            <?= escapar(
                                $errosFormulario[
                                    'data_fim_vinculo'
                                ]
                            ) ?>
                        </small>

                    <?php else: ?>

                        <small
                            class="employees-form-help"
                            data-end-date-help
                        >
                            Será obrigatória quando o tipo de vínculo
                            possuir prazo determinado.
                        </small>

                    <?php endif; ?>

                </div>

            </div>

        </div>

        <!-- =====================================================
             OBSERVAÇÕES E SITUAÇÃO
        ====================================================== -->

        <div class="employees-form__section">

            <header class="employees-form__section-header">

                <span class="employees-form__section-number">
                    04
                </span>

                <div>
                    <h2>Controle do cadastro</h2>

                    <p>
                        Adicione observações internas e defina a
                        situação inicial do colaborador.
                    </p>
                </div>

            </header>

            <div
                class="
                    employees-form__grid
                    employees-form__grid--control
                "
            >

                <div
                    class="
                        employees-field
                        employees-field--wide
                    "
                >

                    <label for="observacoes">
                        Observações
                    </label>

                    <textarea
                        id="observacoes"
                        name="observacoes"
                        maxlength="5000"
                        rows="5"
                        placeholder="Inclua informações internas relevantes sobre o colaborador."
                        class="<?= isset(
                            $errosFormulario[
                                'observacoes'
                            ]
                        )
                            ? 'is-invalid'
                            : '' ?>"
                    ><?= escapar(
                        $observacoes
                    ) ?></textarea>

                    <?php if (
                        isset(
                            $errosFormulario[
                                'observacoes'
                            ]
                        )
                    ): ?>

                        <small class="employees-form-error">
                            <?= escapar(
                                $errosFormulario[
                                    'observacoes'
                                ]
                            ) ?>
                        </small>

                    <?php else: ?>

                        <small class="employees-form-help">
                            Campo opcional, com até 5.000 caracteres.
                        </small>

                    <?php endif; ?>

                </div>

                <div class="employees-status-card">

                    <div class="employees-status-card__header">

                        <span>Situação</span>

                        <small>
                            Controle a disponibilidade do colaborador.
                        </small>

                    </div>

                    <label class="employees-status-switch">

                        <input
                            type="hidden"
                            name="ativo"
                            value="false"
                        >

                        <input
                            type="checkbox"
                            name="ativo"
                            value="true"
                            <?= $ativo
                                ? 'checked'
                                : '' ?>
                        >

                        <span
                            class="employees-status-switch__control"
                            aria-hidden="true"
                        ></span>

                        <span
                            class="
                                employees-status-switch__content
                            "
                        >
                            <strong>
                                Colaborador ativo
                            </strong>

                            <small>
                                Colaboradores ativos permanecem
                                disponíveis nos demais módulos.
                            </small>
                        </span>

                    </label>

                </div>

            </div>

        </div>

        <!-- =====================================================
             AÇÕES
        ====================================================== -->

        <footer class="employees-form__actions">

            <a
                href="<?= escapar(
                    appUrl('colaboradores')
                ) ?>"
                class="employees-secondary-button"
            >
                Cancelar
            </a>

            <button
                type="submit"
                class="employees-primary-button"
            >
                Salvar colaborador
            </button>

        </footer>

    </form>

</section>