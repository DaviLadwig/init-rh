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

/*
|--------------------------------------------------------------------------
| Valores do formulário
|--------------------------------------------------------------------------
*/

$nome = trim(
    (string) ($dadosFormulario['nome'] ?? '')
);

$sigla = trim(
    (string) ($dadosFormulario['sigla'] ?? '')
);

$email = trim(
    (string) ($dadosFormulario['email'] ?? '')
);

$telefone = trim(
    (string) ($dadosFormulario['telefone'] ?? '')
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


<section class="sector-page-header">

    <div class="sector-page-header__content">

        <a
            href="<?= escapar(appUrl('setores')) ?>"
            class="sector-back-link"
        >
            <svg
                viewBox="0 0 24 24"
                aria-hidden="true"
            >
                <path d="m15 18-6-6 6-6"></path>
            </svg>

            <span>Voltar para setores</span>
        </a>

        <span class="sector-eyebrow">
            Estrutura organizacional
        </span>

        <h1>Novo setor</h1>

        <p>
            Cadastre um setor interno da Secretaria Municipal
            de Saúde.
        </p>

    </div>

</section>

<section class="sector-form-card">

    <form
        method="POST"
        action="<?= escapar(appUrl('setores/criar')) ?>"
        class="sector-form"
        novalidate
    >

        <?= campoCsrf() ?>

        <div class="sector-form__section">

            <header class="sector-form__section-header">

                <h2>Identificação do setor</h2>

                <p>
                    Informe o nome e a sigla utilizados para
                    identificar o setor.
                </p>

            </header>

            <div class="sector-form__grid">

                <div class="sector-field">

                    <label for="nome">
                        Nome do setor
                        <span>*</span>
                    </label>

                    <input
                        type="text"
                        id="nome"
                        name="nome"
                        value="<?= escapar($nome) ?>"
                        maxlength="120"
                        placeholder="Ex.: Recursos Humanos"
                        class="<?= isset(
                            $errosFormulario['nome']
                        )
                            ? 'is-invalid'
                            : '' ?>"
                        autocomplete="organization-title"
                        autofocus
                        required
                    >

                    <?php if (
                        isset($errosFormulario['nome'])
                    ): ?>

                        <small class="sector-form-error">
                            <?= escapar(
                                $errosFormulario['nome']
                            ) ?>
                        </small>

                    <?php endif; ?>

                </div>

                <div class="sector-field">

                    <label for="sigla">
                        Sigla
                    </label>

                    <input
                        type="text"
                        id="sigla"
                        name="sigla"
                        value="<?= escapar($sigla) ?>"
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

                        <small class="sector-form-error">
                            <?= escapar(
                                $errosFormulario['sigla']
                            ) ?>
                        </small>

                    <?php else: ?>

                        <small class="sector-form-help">
                            Use uma identificação curta, como RH,
                            FIN ou ADM.
                        </small>

                    <?php endif; ?>

                </div>

            </div>

        </div>

        <div class="sector-form__section">

            <header class="sector-form__section-header">

                <h2>Contato</h2>

                <p>
                    Os dados de contato são opcionais e podem ser
                    atualizados posteriormente.
                </p>

            </header>

            <div class="sector-form__grid">

                <div class="sector-field">

                    <label for="email">
                        E-mail
                    </label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="<?= escapar($email) ?>"
                        maxlength="150"
                        placeholder="Ex.: rh@saude.com"
                        class="<?= isset(
                            $errosFormulario['email']
                        )
                            ? 'is-invalid'
                            : '' ?>"
                        autocomplete="email"
                    >

                    <?php if (
                        isset($errosFormulario['email'])
                    ): ?>

                        <small class="sector-form-error">
                            <?= escapar(
                                $errosFormulario['email']
                            ) ?>
                        </small>

                    <?php endif; ?>

                </div>

                <div class="sector-field">

                    <label for="telefone">
                        Telefone
                    </label>

                    <input
                        type="text"
                        id="telefone"
                        name="telefone"
                        value="<?= escapar($telefone) ?>"
                        maxlength="20"
                        placeholder="Ex.: (98) 99999-9999"
                        class="<?= isset(
                            $errosFormulario['telefone']
                        )
                            ? 'is-invalid'
                            : '' ?>"
                        inputmode="numeric"
                        autocomplete="tel"
                        data-phone-mask
                    >

                    <?php if (
                        isset($errosFormulario['telefone'])
                    ): ?>

                        <small class="sector-form-error">
                            <?= escapar(
                                $errosFormulario['telefone']
                            ) ?>
                        </small>

                    <?php endif; ?>

                </div>

            </div>

        </div>

        <div class="sector-form__section">

            <header class="sector-form__section-header">

                <h2>Descrição</h2>

                <p>
                    Registre resumidamente as responsabilidades
                    desse setor.
                </p>

            </header>

            <div class="sector-form__grid">

                <div
                    class="
                        sector-field
                        sector-field--full
                    "
                >

                    <label for="descricao">
                        Descrição do setor
                    </label>

                    <textarea
                        id="descricao"
                        name="descricao"
                        maxlength="2000"
                        rows="5"
                        placeholder="Descreva as principais atividades e responsabilidades do setor."
                        class="<?= isset(
                            $errosFormulario['descricao']
                        )
                            ? 'is-invalid'
                            : '' ?>"
                    ><?= escapar($descricao) ?></textarea>

                    <?php if (
                        isset($errosFormulario['descricao'])
                    ): ?>

                        <small class="sector-form-error">
                            <?= escapar(
                                $errosFormulario['descricao']
                            ) ?>
                        </small>

                    <?php else: ?>

                        <small class="sector-form-help">
                            Campo opcional, com até 2.000 caracteres.
                        </small>

                    <?php endif; ?>

                </div>

            </div>

        </div>

        <div class="sector-form__section">

            <header class="sector-form__section-header">

                <h2>Situação</h2>

                <p>
                    Defina se o setor ficará disponível para uso
                    imediatamente.
                </p>

            </header>

            <label class="sector-status-switch">

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

                <span
                    class="sector-status-switch__control"
                    aria-hidden="true"
                ></span>

                <span class="sector-status-switch__content">

                    <strong>Setor ativo</strong>

                    <small>
                        Setores ativos poderão ser vinculados aos
                        colaboradores.
                    </small>

                </span>

            </label>

        </div>

        <footer class="sector-form__actions">

            <a
                href="<?= escapar(appUrl('setores')) ?>"
                class="sector-secondary-button"
            >
                Cancelar
            </a>

            <button
                type="submit"
                class="sector-primary-button"
            >
                Salvar setor
            </button>

        </footer>

    </form>

</section>