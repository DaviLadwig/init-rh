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

/*
|--------------------------------------------------------------------------
| Valores do formulário
|--------------------------------------------------------------------------
*/

$nome = trim(
    (string) ($dadosFormulario['nome'] ?? '')
);

$codigo = trim(
    (string) ($dadosFormulario['codigo'] ?? '')
);

$descricao = trim(
    (string) ($dadosFormulario['descricao'] ?? '')
);

$exigeDataFim = array_key_exists(
    'exige_data_fim',
    $dadosFormulario
)
    ? filter_var(
        $dadosFormulario['exige_data_fim'],
        FILTER_VALIDATE_BOOL
    )
    : false;

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
                appUrl('tipos-vinculo')
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
                Voltar para tipos de vínculo
            </span>
        </a>

        <span class="catalog-eyebrow">
            Gestão funcional
        </span>

        <h1>Novo tipo de vínculo</h1>

        <p>
            Cadastre uma forma de contratação utilizada pela
            Secretaria Municipal de Saúde.
        </p>

    </div>

</section>

<section class="catalog-form-card">

    <form
        method="POST"
        action="<?= escapar(
            appUrl('tipos-vinculo/criar')
        ) ?>"
        class="catalog-form"
        novalidate
    >

        <?= campoCsrf() ?>

        <div class="catalog-form__section">

            <header class="catalog-form__section-header">

                <h2>Identificação do vínculo</h2>

                <p>
                    Informe o nome e o código utilizados para
                    identificar essa forma de contratação.
                </p>

            </header>

            <div class="catalog-form__grid">

                <div class="catalog-field">

                    <label for="nome">
                        Nome do tipo de vínculo
                        <span>*</span>
                    </label>

                    <input
                        type="text"
                        id="nome"
                        name="nome"
                        value="<?= escapar($nome) ?>"
                        maxlength="120"
                        placeholder="Ex.: Efetivo"
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
                        placeholder="Ex.: EFETIVO"
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
                            Use somente letras, números, hífen ou
                            sublinhado.
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
                        placeholder="Descreva as características deste tipo de vínculo."
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

                <h2>Prazo do vínculo</h2>

                <p>
                    Defina se colaboradores com este vínculo devem
                    possuir uma data prevista para encerramento.
                </p>

            </header>

            <label class="catalog-status-switch">

                <input
                    type="hidden"
                    name="exige_data_fim"
                    value="false"
                >

                <input
                    type="checkbox"
                    name="exige_data_fim"
                    value="true"
                    <?= $exigeDataFim
                        ? 'checked'
                        : '' ?>
                >

                <span
                    class="catalog-status-switch__control"
                    aria-hidden="true"
                ></span>

                <span class="catalog-status-switch__content">

                    <strong>
                        Exigir data final
                    </strong>

                    <small>
                        Use para contratos temporários, estágios ou
                        outros vínculos com prazo determinado.
                    </small>

                </span>

            </label>

        </div>

        <div class="catalog-form__section">

            <header class="catalog-form__section-header">

                <h2>Situação</h2>

                <p>
                    Defina se este tipo de vínculo poderá ser usado
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
                        Tipo de vínculo ativo
                    </strong>

                    <small>
                        Vínculos ativos poderão ser selecionados no
                        cadastro dos colaboradores.
                    </small>

                </span>

            </label>

        </div>

        <footer class="catalog-form__actions">

            <a
                href="<?= escapar(
                    appUrl('tipos-vinculo')
                ) ?>"
                class="catalog-secondary-button"
            >
                Cancelar
            </a>

            <button
                type="submit"
                class="catalog-primary-button"
            >
                Salvar tipo de vínculo
            </button>

        </footer>

    </form>

</section>