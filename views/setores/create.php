<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Variáveis recebidas pelo SetorController
|--------------------------------------------------------------------------
*/

/** @var array<string, mixed> $dadosFormulario */
/** @var array<string, string> $errosFormulario */
/** @var string|null $erro */

$dadosFormulario =
    isset($dadosFormulario)
    && is_array($dadosFormulario)
        ? $dadosFormulario
        : [];

$errosFormulario =
    isset($errosFormulario)
    && is_array($errosFormulario)
        ? $errosFormulario
        : [];

$erro =
    isset($erro)
    && is_string($erro)
        ? $erro
        : null;

/*
|--------------------------------------------------------------------------
| Valores do formulário
|--------------------------------------------------------------------------
*/

$nome = trim(
    (string) (
        $dadosFormulario['nome']
        ?? ''
    )
);

$sigla = trim(
    (string) (
        $dadosFormulario['sigla']
        ?? ''
    )
);

$email = trim(
    (string) (
        $dadosFormulario['email']
        ?? ''
    )
);

$telefone = trim(
    (string) (
        $dadosFormulario['telefone']
        ?? ''
    )
);

$descricao = trim(
    (string) (
        $dadosFormulario['descricao']
        ?? ''
    )
);

/*
 * Um novo setor começa ativo por padrão.
 */
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

<section class="page-header">

    <div class="page-header__content">

        <a
            href="<?= escapar(appUrl('setores')) ?>"
            class="page-back-link"
        >
            <svg
                viewBox="0 0 24 24"
                aria-hidden="true"
            >
                <path d="m15 18-6-6 6-6" />
            </svg>

            <span>Voltar para setores</span>
        </a>

        <span class="page-eyebrow">
            Estrutura organizacional
        </span>

        <h1>Novo setor</h1>

        <p>
            Cadastre um departamento interno do prédio da
            Secretaria Municipal de Saúde.
        </p>

    </div>

</section>

<section class="sector-form-card">

    <form
        method="POST"
        action="<?= escapar(appUrl('setores/criar')) ?>"
        class="sector-form"
        autocomplete="off"
        novalidate
    >

        <?= campoCsrf() ?>

        <div class="sector-form__section">

            <header class="sector-form__section-header">

                <div>
                    <h2>Informações do setor</h2>

                    <p>
                        Informe os dados utilizados para identificar
                        e organizar o setor.
                    </p>
                </div>

            </header>

            <div class="sector-form__grid">

                <div class="form-field">

                    <label for="nome">
                        Nome do setor
                        <span aria-hidden="true">*</span>
                    </label>

                    <input
                        type="text"
                        id="nome"
                        name="nome"
                        value="<?= escapar($nome) ?>"
                        maxlength="120"
                        placeholder="Ex.: Recursos Humanos"
                        autocomplete="organization-title"
                        class="<?= isset(
                            $errosFormulario['nome']
                        )
                            ? 'is-invalid'
                            : '' ?>"
                        aria-invalid="<?= isset(
                            $errosFormulario['nome']
                        )
                            ? 'true'
                            : 'false' ?>"
                        <?= isset($errosFormulario['nome'])
                            ? 'aria-describedby="erro-nome"'
                            : '' ?>
                        autofocus
                        required
                    >

                    <?php if (
                        isset($errosFormulario['nome'])
                    ): ?>

                        <small
                            class="form-error"
                            id="erro-nome"
                        >
                            <?= escapar(
                                (string) $errosFormulario['nome']
                            ) ?>
                        </small>

                    <?php else: ?>

                        <small class="form-help">
                            Campo obrigatório.
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
                        value="<?= escapar($sigla) ?>"
                        maxlength="30"
                        placeholder="Ex.: RH"
                        class="<?= isset(
                            $errosFormulario['sigla']
                        )
                            ? 'is-invalid'
                            : '' ?>"
                        aria-invalid="<?= isset(
                            $errosFormulario['sigla']
                        )
                            ? 'true'
                            : 'false' ?>"
                        <?= isset($errosFormulario['sigla'])
                            ? 'aria-describedby="erro-sigla"'
                            : 'aria-describedby="ajuda-sigla"' ?>
                        data-uppercase
                    >

                    <?php if (
                        isset($errosFormulario['sigla'])
                    ): ?>

                        <small
                            class="form-error"
                            id="erro-sigla"
                        >
                            <?= escapar(
                                (string) $errosFormulario['sigla']
                            ) ?>
                        </small>

                    <?php else: ?>

                        <small
                            class="form-help"
                            id="ajuda-sigla"
                        >
                            Utilize letras, números, hífen ou sublinhado.
                        </small>

                    <?php endif; ?>

                </div>

                <div class="form-field">

                    <label for="email">
                        E-mail do setor
                    </label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="<?= escapar($email) ?>"
                        maxlength="150"
                        placeholder="Ex.: rh@saude.com"
                        autocomplete="email"
                        class="<?= isset(
                            $errosFormulario['email']
                        )
                            ? 'is-invalid'
                            : '' ?>"
                        aria-invalid="<?= isset(
                            $errosFormulario['email']
                        )
                            ? 'true'
                            : 'false' ?>"
                        <?= isset($errosFormulario['email'])
                            ? 'aria-describedby="erro-email"'
                            : '' ?>
                    >

                    <?php if (
                        isset($errosFormulario['email'])
                    ): ?>

                        <small
                            class="form-error"
                            id="erro-email"
                        >
                            <?= escapar(
                                (string) $errosFormulario['email']
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
                        value="<?= escapar($telefone) ?>"
                        maxlength="20"
                        placeholder="Ex.: (98) 99999-9999"
                        autocomplete="tel"
                        inputmode="tel"
                        class="<?= isset(
                            $errosFormulario['telefone']
                        )
                            ? 'is-invalid'
                            : '' ?>"
                        aria-invalid="<?= isset(
                            $errosFormulario['telefone']
                        )
                            ? 'true'
                            : 'false' ?>"
                        <?= isset($errosFormulario['telefone'])
                            ? 'aria-describedby="erro-telefone"'
                            : '' ?>
                        data-phone
                    >

                    <?php if (
                        isset($errosFormulario['telefone'])
                    ): ?>

                        <small
                            class="form-error"
                            id="erro-telefone"
                        >
                            <?= escapar(
                                (string) $errosFormulario['telefone']
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
                        placeholder="Descreva as principais responsabilidades e atividades do setor."
                        class="<?= isset(
                            $errosFormulario['descricao']
                        )
                            ? 'is-invalid'
                            : '' ?>"
                        aria-invalid="<?= isset(
                            $errosFormulario['descricao']
                        )
                            ? 'true'
                            : 'false' ?>"
                        <?= isset($errosFormulario['descricao'])
                            ? 'aria-describedby="erro-descricao"'
                            : 'aria-describedby="ajuda-descricao"' ?>
                    ><?= escapar($descricao) ?></textarea>

                    <?php if (
                        isset($errosFormulario['descricao'])
                    ): ?>

                        <small
                            class="form-error"
                            id="erro-descricao"
                        >
                            <?= escapar(
                                (string) $errosFormulario['descricao']
                            ) ?>
                        </small>

                    <?php else: ?>

                        <small
                            class="form-help"
                            id="ajuda-descricao"
                        >
                            Campo opcional, com até 2.000 caracteres.
                        </small>

                    <?php endif; ?>

                </div>

            </div>

        </div>

        <div class="sector-form__section">

            <header class="sector-form__section-header">

                <div>
                    <h2>Situação do setor</h2>

                    <p>
                        Defina se o setor ficará disponível imediatamente
                        para os próximos cadastros.
                    </p>
                </div>

            </header>

            <label class="status-switch">

                <!--
                    Este campo será enviado quando o checkbox
                    estiver desmarcado.
                -->
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
                    class="status-switch__control"
                    aria-hidden="true"
                ></span>

                <span class="status-switch__content">

                    <strong>Setor ativo</strong>

                    <small>
                        Setores ativos poderão ser associados aos
                        colaboradores futuramente.
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
                <svg
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                >
                    <path
                        d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"
                    />

                    <path d="M17 21v-8H7v8" />
                    <path d="M7 3v5h8" />
                </svg>

                <span>Salvar setor</span>
            </button>

        </footer>

    </form>

</section>