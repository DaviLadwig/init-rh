<?php

declare(strict_types=1);

/** @var array<string, mixed> $setor */
/** @var array<string, string> $errosFormulario */
/** @var string|null $erro */

$setor = isset($setor) && is_array($setor)
    ? $setor
    : [];

$errosFormulario =
    isset($errosFormulario)
    && is_array($errosFormulario)
        ? $errosFormulario
        : [];

$erro = isset($erro) && is_string($erro)
    ? $erro
    : null;

$setorId = (int) ($setor['id'] ?? 0);
$ativo = (bool) ($setor['ativo'] ?? false);
?>

<?php if ($erro !== null): ?>

    <div
        class="alert alert--error"
        role="alert"
    >
        <?= escapar($erro) ?>
    </div>

<?php endif; ?>

<section class="page-header">

    <div class="page-header__content">

        <a
            href="<?= escapar(appUrl('setores')) ?>"
            class="page-back-link"
        >
            <svg viewBox="0 0 24 24">
                <path d="m15 18-6-6 6-6" />
            </svg>

            <span>Voltar para setores</span>
        </a>

        <span class="page-eyebrow">
            Estrutura organizacional
        </span>

        <h1>Editar setor</h1>

        <p>
            Atualize as informações do setor selecionado.
        </p>

    </div>

</section>

<section class="sector-form-card">

    <form
        method="POST"
        action="<?= escapar(appUrl('setores/editar')) ?>"
        class="sector-form"
        novalidate
    >

        <?= campoCsrf() ?>

        <input
            type="hidden"
            name="setor_id"
            value="<?= $setorId ?>"
        >

        <div class="sector-form__section">

            <header class="sector-form__section-header">
                <div>
                    <h2>Informações do setor</h2>

                    <p>
                        Altere os dados de identificação e contato.
                    </p>
                </div>
            </header>

            <div class="sector-form__grid">

                <div class="form-field form-field--large">

                    <label for="nome">
                        Nome do setor
                        <span>*</span>
                    </label>

                    <input
                        type="text"
                        id="nome"
                        name="nome"
                        value="<?= escapar(
                            (string) (
                                $setor['nome']
                                ?? ''
                            )
                        ) ?>"
                        maxlength="120"
                        placeholder="Ex.: Recursos Humanos"
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

                        <small class="form-error">
                            <?= escapar(
                                $errosFormulario['nome']
                            ) ?>
                        </small>

                    <?php endif; ?>

                </div>

                <div class="form-field">

                    <label for="sigla">
                        Sigla
                    </label>

                    <input
                        type="text"
                        id="sigla"
                        name="sigla"
                        value="<?= escapar(
                            (string) (
                                $setor['sigla']
                                ?? ''
                            )
                        ) ?>"
                        maxlength="30"
                        placeholder="Ex.: RH"
                        class="<?= isset(
                            $errosFormulario['sigla']
                        )
                            ? 'is-invalid'
                            : '' ?>"
                        data-uppercase
                    >

                    <?php if (
                        isset($errosFormulario['sigla'])
                    ): ?>

                        <small class="form-error">
                            <?= escapar(
                                $errosFormulario['sigla']
                            ) ?>
                        </small>

                    <?php else: ?>

                        <small class="form-help">
                            Letras, números, hífen ou sublinhado.
                        </small>

                    <?php endif; ?>

                </div>

                <div class="form-field">

                    <label for="email">
                        E-mail
                    </label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="<?= escapar(
                            (string) (
                                $setor['email']
                                ?? ''
                            )
                        ) ?>"
                        maxlength="150"
                        placeholder="setor@exemplo.com"
                        class="<?= isset(
                            $errosFormulario['email']
                        )
                            ? 'is-invalid'
                            : '' ?>"
                    >

                    <?php if (
                        isset($errosFormulario['email'])
                    ): ?>

                        <small class="form-error">
                            <?= escapar(
                                $errosFormulario['email']
                            ) ?>
                        </small>

                    <?php endif; ?>

                </div>

                <div class="form-field">

                    <label for="telefone">
                        Telefone
                    </label>

                    <input
                        type="text"
                        id="telefone"
                        name="telefone"
                        value="<?= escapar(
                            (string) (
                                $setor['telefone']
                                ?? ''
                            )
                        ) ?>"
                        maxlength="20"
                        placeholder="(98) 99999-9999"
                        class="<?= isset(
                            $errosFormulario['telefone']
                        )
                            ? 'is-invalid'
                            : '' ?>"
                        data-phone
                    >

                    <?php if (
                        isset($errosFormulario['telefone'])
                    ): ?>

                        <small class="form-error">
                            <?= escapar(
                                $errosFormulario['telefone']
                            ) ?>
                        </small>

                    <?php endif; ?>

                </div>

                <div class="form-field form-field--full">

                    <label for="descricao">
                        Descrição
                    </label>

                    <textarea
                        id="descricao"
                        name="descricao"
                        maxlength="2000"
                        rows="5"
                        placeholder="Descreva as principais responsabilidades do setor."
                        class="<?= isset(
                            $errosFormulario['descricao']
                        )
                            ? 'is-invalid'
                            : '' ?>"
                    ><?= escapar(
                        (string) (
                            $setor['descricao']
                            ?? ''
                        )
                    ) ?></textarea>

                    <?php if (
                        isset($errosFormulario['descricao'])
                    ): ?>

                        <small class="form-error">
                            <?= escapar(
                                $errosFormulario['descricao']
                            ) ?>
                        </small>

                    <?php endif; ?>

                </div>

            </div>

        </div>

        <div class="sector-form__section">

            <header class="sector-form__section-header">
                <div>
                    <h2>Situação</h2>

                    <p>
                        Controle a disponibilidade deste setor.
                    </p>
                </div>
            </header>

            <label class="status-switch">

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

                <span class="status-switch__control"></span>

                <span class="status-switch__content">
                    <strong>Setor ativo</strong>

                    <small>
                        Setores inativos permanecem no histórico,
                        mas deixam de ser utilizados.
                    </small>
                </span>

            </label>

        </div>

        <footer class="sector-form__actions">

            <a
                href="<?= escapar(appUrl('setores')) ?>"
                class="form-secondary-button"
            >
                Cancelar
            </a>

            <button
                type="submit"
                class="page-primary-button"
            >
                Salvar alterações
            </button>

        </footer>

    </form>

</section>