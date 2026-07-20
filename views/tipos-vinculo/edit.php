<?php

declare(strict_types=1);

/** @var array<string, mixed> $tipoVinculo */
/** @var array<string, string> $errosFormulario */
/** @var string|null $erro */

$tipoVinculo =
    isset($tipoVinculo) && is_array($tipoVinculo)
        ? $tipoVinculo
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
| Valores do registro
|--------------------------------------------------------------------------
*/

$tipoVinculoId = (int) (
    $tipoVinculo['id']
    ?? 0
);

$nome = trim(
    (string) ($tipoVinculo['nome'] ?? '')
);

$codigo = trim(
    (string) ($tipoVinculo['codigo'] ?? '')
);

$descricao = trim(
    (string) ($tipoVinculo['descricao'] ?? '')
);

$exigeDataFim = filter_var(
    $tipoVinculo['exige_data_fim'] ?? false,
    FILTER_VALIDATE_BOOL
);

$ativo = filter_var(
    $tipoVinculo['ativo'] ?? false,
    FILTER_VALIDATE_BOOL
);
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

        <h1>Editar tipo de vínculo</h1>

        <p>
            Atualize as informações da forma de contratação
            selecionada.
        </p>

    </div>

</section>

<section class="catalog-form-card">

    <form
        method="POST"
        action="<?= escapar(
            appUrl('tipos-vinculo/editar')
        ) ?>"
        class="catalog-form"
        novalidate
    >

        <?= campoCsrf() ?>

        <input
            type="hidden"
            name="tipo_vinculo_id"
            value="<?= $tipoVinculoId ?>"
        >

        <div class="catalog-form__section">

            <header class="catalog-form__section-header">

                <h2>Identificação do vínculo</h2>

                <p>
                    Atualize o nome, código ou descrição desta
                    forma de contratação.
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
                    Controle se este vínculo deve exigir uma data
                    prevista para encerramento.
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
                        Ao selecionar este vínculo no colaborador,
                        a data final deverá ser informada.
                    </small>

                </span>

            </label>

        </div>

        <div class="catalog-form__section">

            <header class="catalog-form__section-header">

                <h2>Situação</h2>

                <p>
                    Controle a disponibilidade deste tipo de
                    vínculo.
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
                        Vínculos inativos permanecem no histórico,
                        mas não ficam disponíveis para novos
                        colaboradores.
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
                Salvar alterações
            </button>

        </footer>

    </form>

</section>