<?php

declare(strict_types=1);

/**
 * Variáveis fornecidas pelo controller.
 *
 * @var array<string, mixed> $colaborador
 * @var array<string, mixed> $dadosFormulario
 * @var array<string, string> $errosFormulario
 * @var array<int, array<string, mixed>> $tiposDocumento
 * @var int $tamanhoMaximoBytes
 * @var string|null $erro
 */

$colaboradorId = (int) (
    $colaborador['id']
    ?? 0
);

$nomeColaborador = trim(
    (string) (
        $colaborador['nome_completo']
        ?? 'Colaborador'
    )
);

$dadosFormulario =
    is_array($dadosFormulario ?? null)
    ? $dadosFormulario
    : [];

$errosFormulario =
    is_array($errosFormulario ?? null)
    ? $errosFormulario
    : [];

$tiposDocumento =
    is_array($tiposDocumento ?? null)
    ? $tiposDocumento
    : [];

$tamanhoMaximoBytes = (int) (
    $tamanhoMaximoBytes
    ?? 10485760
);

$valorCampo = static function (
    string $campo,
    mixed $padrao = ''
) use (
    $dadosFormulario
): mixed {
    return $dadosFormulario[$campo]
        ?? $padrao;
};

$erroCampo = static function (
    string $campo
) use (
    $errosFormulario
): string {
    return trim(
        (string) (
            $errosFormulario[$campo]
            ?? ''
        )
    );
};

$classeCampo = static function (
    string $campo
) use (
    $errosFormulario
): string {
    return isset(
        $errosFormulario[$campo]
    )
        ? ' document-form-field--error'
        : '';
};

$valorBooleano = static function (
    mixed $valor
): bool {
    if (is_bool($valor)) {
        return $valor;
    }

    if (is_int($valor)) {
        return $valor === 1;
    }

    return in_array(
        mb_strtolower(
            trim((string) $valor)
        ),
        [
            '1',
            'true',
            't',
            'yes',
            'sim',
            'on',
        ],
        true
    );
};

$formatarBytes = static function (
    int $bytes
): string {
    if ($bytes >= 1048576) {
        return number_format(
            $bytes / 1048576,
            2,
            ',',
            '.'
        ) . ' MB';
    }

    if ($bytes >= 1024) {
        return number_format(
            $bytes / 1024,
            2,
            ',',
            '.'
        ) . ' KB';
    }

    return $bytes . ' bytes';
};

$tipoDocumentoSelecionadoId = (int) (
    $valorCampo(
        'tipo_documento_id',
        0
    )
);

$tipoSelecionadoExigeValidade = false;

foreach (
    $tiposDocumento as $tipoDocumento
) {
    if (
        (int) (
            $tipoDocumento['id']
            ?? 0
        ) === $tipoDocumentoSelecionadoId
    ) {
        $tipoSelecionadoExigeValidade =
            $valorBooleano(
                $tipoDocumento['exige_validade']
                    ?? false
            );

        break;
    }
}

$semTiposDocumento =
    $tiposDocumento === [];
?>

<section class="documents-page">

    <header class="documents-header">

        <div class="documents-header__content">

            <a
                href="<?= escapar(
                            appUrl(
                                'colaboradores/documentos?colaborador_id='
                                    . $colaboradorId
                            )
                        ) ?>"
                class="documents-back">
                <span aria-hidden="true">←</span>
                Voltar aos documentos
            </a>

            <span class="documents-eyebrow">
                Arquivo funcional
            </span>

            <h1 class="documents-title">
                Anexar documento
            </h1>

            <p class="documents-subtitle">
                Adicione um arquivo pessoal ou funcional de
                <strong>
                    <?= escapar(
                        $nomeColaborador
                    ) ?>
                </strong>.
            </p>

        </div>

    </header>

    <?php if (!empty($erro)): ?>

        <div
            class="documents-alert documents-alert--error"
            role="alert">
            <?= escapar(
                (string) $erro
            ) ?>
        </div>

    <?php endif; ?>

    <?php if ($semTiposDocumento): ?>

        <div
            class="documents-alert documents-alert--error"
            role="alert">
            Nenhum tipo de documento ativo está disponível.
            Cadastre ou ative um tipo antes de realizar o envio.
        </div>

    <?php endif; ?>

    <form
        method="post"
        action="<?= escapar(
                    appUrl('documentos/criar')
                ) ?>"
        enctype="multipart/form-data"
        class="document-form"
        data-document-upload-form
        data-max-bytes="<?= $tamanhoMaximoBytes ?>"
        novalidate>

        <?= campoCsrf() ?>

        <input
            type="hidden"
            name="colaborador_id"
            value="<?= $colaboradorId ?>">

        <section class="document-form__panel">

            <div class="document-form__section-header">

                <div class="document-form__section-icon">
                    <span aria-hidden="true">▤</span>
                </div>

                <div>
                    <h2>
                        Informações do documento
                    </h2>

                    <p>
                        Informe os dados utilizados para
                        identificar e organizar o arquivo.
                    </p>
                </div>

            </div>

            <div class="document-form__grid">

                <div
                    class="document-form-field document-form-field--full<?= escapar(
                                                                            $classeCampo(
                                                                                'tipo_documento_id'
                                                                            )
                                                                        ) ?>">

                    <label for="tipo_documento_id">
                        Tipo de documento
                        <span aria-hidden="true">*</span>
                    </label>

                    <select
                        id="tipo_documento_id"
                        name="tipo_documento_id"
                        required
                        data-document-type
                        aria-invalid="<?= $erroCampo(
                                            'tipo_documento_id'
                                        ) !== ''
                                            ? 'true'
                                            : 'false' ?>">
                        <option value="">
                            Selecione
                        </option>

                        <?php foreach (
                            $tiposDocumento
                            as $tipoDocumento
                        ): ?>

                            <?php
                            $tipoId = (int) (
                                $tipoDocumento['id']
                                ?? 0
                            );

                            $tipoNome = trim(
                                (string) (
                                    $tipoDocumento['nome']
                                    ?? ''
                                )
                            );

                            $tipoExigeValidade =
                                $valorBooleano(
                                    $tipoDocumento['exige_validade']
                                        ?? false
                                );
                            ?>

                            <option
                                value="<?= $tipoId ?>"
                                data-requires-validity="<?= $tipoExigeValidade
                                                            ? '1'
                                                            : '0' ?>"
                                <?= $tipoDocumentoSelecionadoId === $tipoId
                                    ? 'selected'
                                    : '' ?>>
                                <?= escapar(
                                    $tipoNome
                                ) ?>

                                <?= $tipoExigeValidade
                                    ? ' — exige validade'
                                    : '' ?>
                            </option>

                        <?php endforeach; ?>
                    </select>

                    <?php if (
                        $erroCampo(
                            'tipo_documento_id'
                        ) !== ''
                    ): ?>

                        <small
                            class="document-form-field__error">
                            <?= escapar(
                                $erroCampo(
                                    'tipo_documento_id'
                                )
                            ) ?>
                        </small>

                    <?php endif; ?>

                </div>

                <div
                    class="document-form-field document-form-field--full<?= escapar(
                                                                            $classeCampo('titulo')
                                                                        ) ?>">

                    <label for="titulo">
                        Identificação complementar
                    </label>

                    <input
                        type="text"
                        id="titulo"
                        name="titulo"
                        value="<?= escapar(
                                    (string) $valorCampo(
                                        'titulo'
                                    )
                                ) ?>"
                        maxlength="180"
                        autocomplete="off"
                        placeholder="COREN-MA, CRM-MA ou Graduação em Enfermagem"
                        aria-invalid="<?= $erroCampo(
                                            'titulo'
                                        ) !== ''
                                            ? 'true'
                                            : 'false' ?>">

                    <small class="document-form-field__hint">
                        Campo opcional. Use para diferenciar documentos do mesmo tipo.
                    </small>

                    <?php if (
                        $erroCampo('titulo') !== ''
                    ): ?>

                        <small
                            class="document-form-field__error">
                            <?= escapar(
                                $erroCampo('titulo')
                            ) ?>
                        </small>

                    <?php endif; ?>

                </div>

                <div
                    class="document-form-field<?= escapar(
                                                    $classeCampo(
                                                        'numero_documento'
                                                    )
                                                ) ?>">

                    <label for="numero_documento">
                        Número do documento
                    </label>

                    <input
                        type="text"
                        id="numero_documento"
                        name="numero_documento"
                        value="<?= escapar(
                                    (string) $valorCampo(
                                        'numero_documento'
                                    )
                                ) ?>"
                        maxlength="100"
                        autocomplete="off"
                        placeholder="Opcional"
                        aria-invalid="<?= $erroCampo(
                                            'numero_documento'
                                        ) !== ''
                                            ? 'true'
                                            : 'false' ?>">

                    <?php if (
                        $erroCampo(
                            'numero_documento'
                        ) !== ''
                    ): ?>

                        <small
                            class="document-form-field__error">
                            <?= escapar(
                                $erroCampo(
                                    'numero_documento'
                                )
                            ) ?>
                        </small>

                    <?php endif; ?>

                </div>

                <div
                    class="document-form-field<?= escapar(
                                                    $classeCampo(
                                                        'data_emissao'
                                                    )
                                                ) ?>">

                    <label for="data_emissao">
                        Data de emissão
                    </label>

                    <input
                        type="date"
                        id="data_emissao"
                        name="data_emissao"
                        value="<?= escapar(
                                    (string) $valorCampo(
                                        'data_emissao'
                                    )
                                ) ?>"
                        aria-invalid="<?= $erroCampo(
                                            'data_emissao'
                                        ) !== ''
                                            ? 'true'
                                            : 'false' ?>">

                    <?php if (
                        $erroCampo(
                            'data_emissao'
                        ) !== ''
                    ): ?>

                        <small
                            class="document-form-field__error">
                            <?= escapar(
                                $erroCampo(
                                    'data_emissao'
                                )
                            ) ?>
                        </small>

                    <?php endif; ?>

                </div>

                <div
                    class="document-form-field<?= escapar(
                                                    $classeCampo(
                                                        'data_validade'
                                                    )
                                                ) ?>"
                    data-validity-field>

                    <label for="data_validade">
                        Data de validade

                        <span
                            data-validity-required-marker
                            <?= $tipoSelecionadoExigeValidade
                                ? ''
                                : 'hidden' ?>
                            aria-hidden="true">
                            *
                        </span>
                    </label>

                    <input
                        type="date"
                        id="data_validade"
                        name="data_validade"
                        value="<?= escapar(
                                    (string) $valorCampo(
                                        'data_validade'
                                    )
                                ) ?>"
                        data-document-validity
                        <?= $tipoSelecionadoExigeValidade
                            ? 'required'
                            : '' ?>
                        aria-invalid="<?= $erroCampo(
                                            'data_validade'
                                        ) !== ''
                                            ? 'true'
                                            : 'false' ?>">

                    <small
                        class="document-form-field__hint"
                        data-validity-hint>
                        <?= $tipoSelecionadoExigeValidade
                            ? 'Obrigatória para o tipo selecionado.'
                            : 'Informe somente quando o documento possuir validade.' ?>
                    </small>

                    <?php if (
                        $erroCampo(
                            'data_validade'
                        ) !== ''
                    ): ?>

                        <small
                            class="document-form-field__error">
                            <?= escapar(
                                $erroCampo(
                                    'data_validade'
                                )
                            ) ?>
                        </small>

                    <?php endif; ?>

                </div>

                <div
                    class="document-form-field document-form-field--full<?= escapar(
                                                                            $classeCampo(
                                                                                'descricao'
                                                                            )
                                                                        ) ?>">

                    <label for="descricao">
                        Observações
                    </label>

                    <textarea
                        id="descricao"
                        name="descricao"
                        rows="5"
                        maxlength="5000"
                        placeholder="Inclua observações relevantes sobre o documento"
                        aria-invalid="<?= $erroCampo(
                                            'descricao'
                                        ) !== ''
                                            ? 'true'
                                            : 'false' ?>"><?= escapar(
                                                                (string) $valorCampo(
                                                                    'descricao'
                                                                )
                                                            ) ?></textarea>

                    <?php if (
                        $erroCampo(
                            'descricao'
                        ) !== ''
                    ): ?>

                        <small
                            class="document-form-field__error">
                            <?= escapar(
                                $erroCampo(
                                    'descricao'
                                )
                            ) ?>
                        </small>

                    <?php endif; ?>

                </div>

            </div>

        </section>

        <section class="document-form__panel">

            <div class="document-form__section-header">

                <div class="document-form__section-icon">
                    <span aria-hidden="true">↑</span>
                </div>

                <div>
                    <h2>
                        Arquivo
                    </h2>

                    <p>
                        Selecione um PDF ou uma imagem legível
                        do documento.
                    </p>
                </div>

            </div>

            <div
                class="document-upload<?= escapar(
                                            $classeCampo('arquivo')
                                        ) ?>"
                data-document-dropzone>

                <input
                    type="file"
                    id="arquivo"
                    name="arquivo"
                    accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png"
                    required
                    data-document-file
                    aria-invalid="<?= $erroCampo(
                                        'arquivo'
                                    ) !== ''
                                        ? 'true'
                                        : 'false' ?>">

                <label
                    for="arquivo"
                    class="document-upload__label">

                    <span
                        class="document-upload__icon"
                        aria-hidden="true">
                        ↑
                    </span>

                    <strong>
                        Selecione o arquivo
                    </strong>

                    <span>
                        PDF, JPG, JPEG ou PNG
                    </span>

                    <small>
                        Tamanho máximo:
                        <?= escapar(
                            $formatarBytes(
                                $tamanhoMaximoBytes
                            )
                        ) ?>
                    </small>

                </label>

                <div
                    class="document-upload__selected"
                    data-document-file-info
                    hidden
                    aria-live="polite">
                    <strong
                        data-document-file-name></strong>

                    <span
                        data-document-file-size></span>
                </div>

            </div>

            <?php if (
                $erroCampo('arquivo') !== ''
            ): ?>

                <small
                    class="document-form-field__error">
                    <?= escapar(
                        $erroCampo('arquivo')
                    ) ?>
                </small>

                <p class="document-upload__notice">
                    Por segurança, selecione o arquivo novamente.
                </p>

            <?php endif; ?>

            <div class="document-security-note">

                <span
                    class="document-security-note__icon"
                    aria-hidden="true">
                    ✓
                </span>

                <div>
                    <strong>
                        Armazenamento protegido
                    </strong>

                    <p>
                        O arquivo será validado pelo conteúdo real,
                        receberá um nome aleatório e ficará fora da
                        área pública do sistema.
                    </p>
                </div>

            </div>

        </section>

        <footer class="document-form__actions">

            <a
                href="<?= escapar(
                            appUrl(
                                'colaboradores/documentos?colaborador_id='
                                    . $colaboradorId
                            )
                        ) ?>"
                class="documents-clear-button">
                Cancelar
            </a>

            <button
                type="submit"
                class="documents-primary-button"
                data-document-submit
                <?= $semTiposDocumento
                    ? 'disabled'
                    : '' ?>>
                Anexar documento
            </button>

        </footer>

    </form>

</section>

<!--
Essa tela já possui:

multipart/form-data, necessário para uploads;
token CSRF;
vínculo interno com o colaborador;
tipos carregados da organização autenticada;
campo de validade adaptado ao tipo selecionado;
formatos permitidos informados ao navegador;
limite de tamanho exibido;
mensagens de validação por campo;
aviso para selecionar novamente o arquivo após erro;
nenhuma exposição do caminho físico do storage.
-->