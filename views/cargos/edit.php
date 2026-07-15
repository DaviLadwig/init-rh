<?php

declare(strict_types=1);

/** @var array<string, mixed> $cargo */
/** @var array<string, string> $errosFormulario */
/** @var string|null $erro */

$cargo = isset($cargo) && is_array($cargo)
    ? $cargo
    : [];

$errosFormulario =
    isset($errosFormulario) && is_array($errosFormulario)
        ? $errosFormulario
        : [];

$erro = isset($erro) && is_string($erro)
    ? $erro
    : null;

$cargoId = (int) ($cargo['id'] ?? 0);
$nome = trim((string) ($cargo['nome'] ?? ''));
$codigo = trim((string) ($cargo['codigo'] ?? ''));
$descricao = trim((string) ($cargo['descricao'] ?? ''));
$ativo = (bool) ($cargo['ativo'] ?? false);
?>

<?php if ($erro !== null): ?>
    <div class="alert alert--error" role="alert">
        <?= escapar($erro) ?>
    </div>
<?php endif; ?>

<section class="catalog-page-header">

    <div class="catalog-page-header__content">

        <a
            href="<?= escapar(appUrl('cargos')) ?>"
            class="catalog-back-link"
        >
            <svg viewBox="0 0 24 24">
                <path d="m15 18-6-6 6-6" />
            </svg>

            <span>Voltar para cargos</span>
        </a>

        <span class="catalog-eyebrow">
            Estrutura organizacional
        </span>

        <h1>Editar cargo</h1>

        <p>
            Atualize as informações do cargo selecionado.
        </p>

    </div>

</section>

<section class="catalog-form-card">

    <form
        method="POST"
        action="<?= escapar(appUrl('cargos/editar')) ?>"
        class="catalog-form"
        novalidate
    >

        <?= campoCsrf() ?>

        <input
            type="hidden"
            name="cargo_id"
            value="<?= $cargoId ?>"
        >

        <div class="catalog-form__section">

            <header class="catalog-form__section-header">
                <h2>Informações do cargo</h2>

                <p>
                    Altere os dados de identificação do cargo.
                </p>
            </header>

            <div class="catalog-form__grid">

                <div class="catalog-field">

                    <label for="nome">
                        Nome do cargo
                        <span>*</span>
                    </label>

                    <input
                        type="text"
                        id="nome"
                        name="nome"
                        value="<?= escapar($nome) ?>"
                        maxlength="120"
                        placeholder="Ex.: Assistente Administrativo"
                        class="<?= isset($errosFormulario['nome'])
                            ? 'is-invalid'
                            : '' ?>"
                        autofocus
                        required
                    >

                    <?php if (isset($errosFormulario['nome'])): ?>
                        <small class="catalog-form-error">
                            <?= escapar($errosFormulario['nome']) ?>
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
                        placeholder="Ex.: ASSIST_ADMIN"
                        class="<?= isset($errosFormulario['codigo'])
                            ? 'is-invalid'
                            : '' ?>"
                        data-uppercase
                    >

                    <?php if (isset($errosFormulario['codigo'])): ?>
                        <small class="catalog-form-error">
                            <?= escapar($errosFormulario['codigo']) ?>
                        </small>
                    <?php else: ?>
                        <small class="catalog-form-help">
                            Letras, números, hífen ou sublinhado.
                        </small>
                    <?php endif; ?>

                </div>

                <div class="catalog-field catalog-field--full">

                    <label for="descricao">
                        Descrição
                    </label>

                    <textarea
                        id="descricao"
                        name="descricao"
                        maxlength="2000"
                        rows="5"
                        placeholder="Descreva as principais atribuições deste cargo."
                        class="<?= isset($errosFormulario['descricao'])
                            ? 'is-invalid'
                            : '' ?>"
                    ><?= escapar($descricao) ?></textarea>

                    <?php if (isset($errosFormulario['descricao'])): ?>
                        <small class="catalog-form-error">
                            <?= escapar($errosFormulario['descricao']) ?>
                        </small>
                    <?php endif; ?>

                </div>

            </div>

        </div>

        <div class="catalog-form__section">

            <header class="catalog-form__section-header">
                <h2>Situação</h2>

                <p>
                    Controle a disponibilidade deste cargo.
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
                    <?= $ativo ? 'checked' : '' ?>
                >

                <span class="catalog-status-switch__control"></span>

                <span class="catalog-status-switch__content">
                    <strong>Cargo ativo</strong>

                    <small>
                        Cargos inativos permanecem no histórico.
                    </small>
                </span>

            </label>

        </div>

        <footer class="catalog-form__actions">

            <a
                href="<?= escapar(appUrl('cargos')) ?>"
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