<?php

declare(strict_types=1);

/**
 * Variáveis fornecidas pelo controller.
 *
 * @var array<string, mixed> $colaborador
 * @var array<string, mixed> $documento
 * @var array<string, string> $errosFormulario
 * @var array<int, array<string, mixed>> $tiposDocumento
 * @var string|null $erro
 */

$colaboradorId = (int) (
    $colaborador['id']
    ?? 0
);

$documentoId = (int) (
    $documento['id']
    ?? 0
);

$nomeColaborador = trim(
    (string) (
        $colaborador['nome_completo']
        ?? 'Colaborador'
    )
);

$errosFormulario =
    is_array($errosFormulario ?? null)
    ? $errosFormulario
    : [];

$tiposDocumento =
    is_array($tiposDocumento ?? null)
    ? $tiposDocumento
    : [];

$valorCampo = static function (
    string $campo,
    mixed $padrao = ''
) use (
    $documento
): mixed {
    return $documento[$campo]
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

$formatarTamanho = static function (
    mixed $bytes
): string {
    $bytes = (int) $bytes;

    if ($bytes <= 0) {
        return '0 bytes';
    }

    if ($bytes >= 1073741824) {
        return number_format(
            $bytes / 1073741824,
            2,
            ',',
            '.'
        ) . ' GB';
    }

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

$formatarDataHora = static function (
    mixed $data
): string {
    $data = trim((string) $data);

    if ($data === '') {
        return 'Não informada';
    }

    try {
        return (
            new DateTimeImmutable($data)
        )->format('d/m/Y \à\s H:i');
    } catch (Throwable) {
        return 'Não informada';
    }
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

$extensao = mb_strtoupper(
    trim(
        (string) (
            $documento['arquivo_extensao']
            ?? 'ARQUIVO'
        )
    )
);

$documentoAtivo = $valorBooleano(
    $documento['ativo']
        ?? false
);
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
                Editar documento
            </h1>

            <p class="documents-subtitle">
                Atualize as informações do documento de
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

    <form
        method="post"
        action="<?= escapar(
                    appUrl('documentos/editar')
                ) ?>"
        class="document-form"
        data-document-upload-form
        novalidate>

        <?= campoCsrf() ?>

        <input
            type="hidden"
            name="documento_id"
            value="<?= $documentoId ?>">

        <section class="document-form__panel">

            <div class="document-form__section-header">

                <div class="document-form__section-icon">
                    <span aria-hidden="true">▤</span>
                </div>

                <div>
                    <h2>
                        Arquivo armazenado
                    </h2>

                    <p>
                        O arquivo físico não será alterado
                        durante esta edição.
                    </p>
                </div>

            </div>

            <div class="document-card__file">

                <div
                    class="document-file-icon"
                    aria-hidden="true">
                    <?= escapar(
                        $extensao === 'PDF'
                            ? 'PDF'
                            : 'IMG'
                    ) ?>
                </div>

                <div class="document-card__main">

                    <div class="document-card__heading">

                        <div>

                            <span class="document-card__type">
                                Arquivo original
                            </span>

                            <h2 class="document-card__title">
                                <?= escapar(
                                    (string) (
                                        $documento['arquivo_nome_original']
                                        ?? 'Documento'
                                    )
                                ) ?>
                            </h2>

                        </div>

                        <span
                            class="document-status <?= $documentoAtivo
                                                        ? 'document-status--active'
                                                        : 'document-status--inactive' ?>">
                            <?= $documentoAtivo
                                ? 'Ativo'
                                : 'Inativo' ?>
                        </span>

                    </div>

                    <div class="document-card__metadata">

                        <span>
                            <strong>Formato:</strong>

                            <?= escapar(
                                $extensao
                            ) ?>
                        </span>

                        <span>
                            <strong>Tamanho:</strong>

                            <?= escapar(
                                $formatarTamanho(
                                    $documento['arquivo_tamanho_bytes']
                                        ?? 0
                                )
                            ) ?>
                        </span>

                        <span>
                            <strong>Enviado em:</strong>

                            <?= escapar(
                                $formatarDataHora(
                                    $documento['criado_em']
                                        ?? null
                                )
                            ) ?>
                        </span>

                        <span>
                            <strong>Enviado por:</strong>

                            <?= escapar(
                                (string) (
                                    $documento['enviado_por_nome']
                                    ?? 'Usuário não identificado'
                                )
                            ) ?>
                        </span>

                    </div>

                </div>

            </div>

            <div class="document-security-note">

                <span
                    class="document-security-note__icon"
                    aria-hidden="true">
                    ✓
                </span>

                <div>
                    <strong>
                        Integridade preservada
                    </strong>

                    <p>
                        Para trocar o conteúdo físico, será
                        necessário anexar um novo documento.
                        Esta tela altera somente os dados
                        descritivos.
                    </p>
                </div>

            </div>

        </section>

        <section class="document-form__panel">

            <div class="document-form__section-header">

                <div class="document-form__section-icon">
                    <span aria-hidden="true">✎</span>
                </div>

                <div>
                    <h2>
                        Informações do documento
                    </h2>

                    <p>
                        Atualize os dados usados para
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

                            $tipoAtivo = $valorBooleano(
                                $tipoDocumento['ativo']
                                    ?? false
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

                                <?= !$tipoAtivo
                                    ? ' — inativo'
                                    : '' ?>

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

                        <small class="document-form-field__error">
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
                        placeholder="Ex.: COREN-MA, CRM-MA ou Graduação em Enfermagem"
                        aria-invalid="<?= $erroCampo(
                                            'titulo'
                                        ) !== ''
                                            ? 'true'
                                            : 'false' ?>">

                    <?php if (
                        $erroCampo('titulo') !== ''
                    ): ?>

                        <small class="document-form-field__error">
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

                        <small class="document-form-field__error">
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

                        <small class="document-form-field__error">
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

                        <small class="document-form-field__error">
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

                        <small class="document-form-field__error">
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
                data-document-submit>
                Salvar alterações
            </button>

        </footer>

    </form>

</section>