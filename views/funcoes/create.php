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

$erro = isset($erro) && is_string($erro)
    ? $erro
    : null;

$nome = trim((string) ($dadosFormulario['nome'] ?? ''));
$codigo = trim((string) ($dadosFormulario['codigo'] ?? ''));
$descricao = trim((string) ($dadosFormulario['descricao'] ?? ''));

$ativo = array_key_exists('ativo', $dadosFormulario)
    ? filter_var(
        $dadosFormulario['ativo'],
        FILTER_VALIDATE_BOOL
    )
    : true;
?>

<?php if ($erro !== null): ?>
    <div class="alert alert--error" role="alert">
        <?= escapar($erro) ?>
    </div>
<?php endif; ?>

<section class="catalog-page-header">

    <div class="catalog-page-header__content">

        <a
            href="<?= escapar(appUrl('funcoes')) ?>"
            class="catalog-back-link"
        >
            <svg viewBox="0 0 24 24">
                <path d="m15 18-6-6 6-6" />
            </svg>

            <span>Voltar para funções</span>
        </a>

        <span class="catalog-eyebrow">
            Estrutura organizacional
        </span>

        <h1>Nova função</h1>

        <p>
            Cadastre uma responsabilidade ou atribuição adicional.
        </p>

    </div>

</section>

<section class="catalog-form-card">

    <form
        method="POST"
        action="<?= escapar(appUrl('funcoes/criar')) ?>"
        class="catalog-form"
        novalidate
    >

        <?= campoCsrf() ?>

        <div class="catalog-form__section">

            <header class="catalog-form__section-header">
                <h2>Informações da função</h2>

                <p>
                    Informe os dados utilizados para identificar a função.
                </p>
            </header>

            <div class="catalog-form__grid">

                <div class="catalog-field">

                    <label for="nome">
                        Nome da função
                        <span>*</span>
                    </label>

                    <input
                        type="text"
                        id="nome"
                        name="nome"
                        value="<?= escapar($nome) ?>"
                        maxlength="120"
                        placeholder="Ex.: Coordenador de Transporte"
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
                        placeholder="Ex.: COORD_TRANSP"
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
                        placeholder="Descreva as responsabilidades desta função."
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
                    Defina se a função ficará disponível imediatamente.
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
                    <strong>Função ativa</strong>

                    <small>
                        Funções ativas poderão ser vinculadas aos colaboradores.
                    </small>
                </span>

            </label>

        </div>

        <footer class="catalog-form__actions">

            <a
                href="<?= escapar(appUrl('funcoes')) ?>"
                class="catalog-secondary-button"
            >
                Cancelar
            </a>

            <button
                type="submit"
                class="catalog-primary-button"
            >
                Salvar função
            </button>

        </footer>

    </form>

</section>