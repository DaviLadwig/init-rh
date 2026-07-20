<?php

declare(strict_types=1);

/** @var array<string, mixed> $dadosFormulario */
/** @var array<string, string> $errosFormulario */
/** @var string|null $erro */

$dadosFormulario =
    isset($dadosFormulario) && is_array($dadosFormulario)
        ? $dadosFormulario
        : [];

$errosFormulario =
    isset($errosFormulario) && is_array($errosFormulario)
        ? $errosFormulario
        : [];

$erro =
    isset($erro) && is_string($erro)
        ? $erro
        : null;

$nome = trim(
    (string) ($dadosFormulario['nome'] ?? '')
);

$codigo = trim(
    (string) ($dadosFormulario['codigo'] ?? '')
);

$cargaHoraria = trim(
    (string) (
        $dadosFormulario['carga_horaria_semanal']
        ?? ''
    )
);

$descricao = trim(
    (string) ($dadosFormulario['descricao'] ?? '')
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
?>

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

        <a
            href="<?= escapar(
                appUrl('jornadas')
            ) ?>"
            class="catalog-back-link"
        >
            <svg
                viewBox="0 0 24 24"
                aria-hidden="true"
            >
                <path d="m15 18-6-6 6-6"></path>
            </svg>

            <span>
                Voltar para jornadas
            </span>
        </a>

        <span class="catalog-eyebrow">
            Gestão funcional
        </span>

        <h1>Nova jornada de trabalho</h1>

        <p>
            Cadastre uma carga horária ou escala utilizada
            pelos colaboradores.
        </p>

    </div>

</section>

<section class="catalog-form-card">

    <form
        method="POST"
        action="<?= escapar(
            appUrl('jornadas/criar')
        ) ?>"
        class="catalog-form"
        novalidate
    >

        <?= campoCsrf() ?>

        <div class="catalog-form__section">

            <header class="catalog-form__section-header">

                <h2>Identificação da jornada</h2>

                <p>
                    Informe o nome, código e a carga horária
                    semanal da jornada.
                </p>

            </header>

            <div class="catalog-form__grid">

                <div class="catalog-field">

                    <label for="nome">
                        Nome da jornada
                        <span>*</span>
                    </label>

                    <input
                        type="text"
                        id="nome"
                        name="nome"
                        value="<?= escapar($nome) ?>"
                        maxlength="120"
                        placeholder="Ex.: 40 horas semanais"
                        class="<?= isset(
                            $errosFormulario['nome']
                        )
                            ? 'is-invalid'
                            : '' ?>"
                        autofocus
                        required
                    >

                    <?php if (
                        isset($errosFormulario['nome'])
                    ): ?>

                        <small class="catalog-form-error">
                            <?= escapar(
                                $errosFormulario['nome']
                            ) ?>
                        </small>

                    <?php endif; ?>

                </div>

                <div class="catalog-field">

                    <label for="codigo">
                        Código
                    </label>

                    <input
                        type="text"
                        id="codigo"
                        name="codigo"
                        value="<?= escapar($codigo) ?>"
                        maxlength="30"
                        placeholder="Ex.: JORNADA_40H"
                        class="<?= isset(
                            $errosFormulario['codigo']
                        )
                            ? 'is-invalid'
                            : '' ?>"
                        data-uppercase
                    >

                    <?php if (
                        isset($errosFormulario['codigo'])
                    ): ?>

                        <small class="catalog-form-error">
                            <?= escapar(
                                $errosFormulario['codigo']
                            ) ?>
                        </small>

                    <?php else: ?>

                        <small class="catalog-form-help">
                            Use letras, números, hífen ou sublinhado.
                        </small>

                    <?php endif; ?>

                </div>

                <div class="catalog-field">

                    <label for="carga_horaria_semanal">
                        Carga horária semanal
                    </label>

                    <input
                        type="text"
                        id="carga_horaria_semanal"
                        name="carga_horaria_semanal"
                        value="<?= escapar($cargaHoraria) ?>"
                        maxlength="6"
                        placeholder="Ex.: 40"
                        inputmode="decimal"
                        class="<?= isset(
                            $errosFormulario[
                                'carga_horaria_semanal'
                            ]
                        )
                            ? 'is-invalid'
                            : '' ?>"
                        data-workload
                    >

                    <?php if (
                        isset(
                            $errosFormulario[
                                'carga_horaria_semanal'
                            ]
                        )
                    ): ?>

                        <small class="catalog-form-error">
                            <?= escapar(
                                $errosFormulario[
                                    'carga_horaria_semanal'
                                ]
                            ) ?>
                        </small>

                    <?php else: ?>

                        <small class="catalog-form-help">
                            Deixe vazio para plantões ou escalas
                            especiais sem carga semanal fixa.
                        </small>

                    <?php endif; ?>

                </div>

                <div
                    class="
                        catalog-field
                        catalog-field--full
                    "
                >

                    <label for="descricao">
                        Descrição
                    </label>

                    <textarea
                        id="descricao"
                        name="descricao"
                        maxlength="2000"
                        rows="5"
                        placeholder="Descreva como funciona esta jornada ou escala."
                        class="<?= isset(
                            $errosFormulario['descricao']
                        )
                            ? 'is-invalid'
                            : '' ?>"
                    ><?= escapar($descricao) ?></textarea>

                    <?php if (
                        isset($errosFormulario['descricao'])
                    ): ?>

                        <small class="catalog-form-error">
                            <?= escapar(
                                $errosFormulario['descricao']
                            ) ?>
                        </small>

                    <?php else: ?>

                        <small class="catalog-form-help">
                            Campo opcional, com até 2.000 caracteres.
                        </small>

                    <?php endif; ?>

                </div>

            </div>

        </div>

        <div class="catalog-form__section">

            <header class="catalog-form__section-header">

                <h2>Situação</h2>

                <p>
                    Defina se esta jornada poderá ser utilizada
                    imediatamente.
                </p>

            </header>

            <label class="catalog-status-switch">

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
                    class="catalog-status-switch__control"
                    aria-hidden="true"
                ></span>

                <span class="catalog-status-switch__content">

                    <strong>
                        Jornada ativa
                    </strong>

                    <small>
                        Jornadas ativas poderão ser vinculadas
                        aos colaboradores.
                    </small>

                </span>

            </label>

        </div>

        <footer class="catalog-form__actions">

            <a
                href="<?= escapar(
                    appUrl('jornadas')
                ) ?>"
                class="catalog-secondary-button"
            >
                Cancelar
            </a>

            <button
                type="submit"
                class="catalog-primary-button"
            >
                Salvar jornada
            </button>

        </footer>

    </form>

</section>