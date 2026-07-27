<?php

declare(strict_types=1);

$colaboradorId = (int) (
    $colaborador['id']
    ?? 0
);

/**
 * Variáveis fornecidas pelo controller.
 *
 * @var array<string, mixed> $colaborador
 * @var array<int, array<string, mixed>> $documentos
 * @var string $busca
 * @var string $situacao
 * @var string|null $sucesso
 * @var string|null $erro
 */

$nomeColaborador = trim(
    (string) (
        $colaborador['nome_completo']
        ?? 'Colaborador'
    )
);

$buscaAtual = trim(
    (string) ($busca ?? '')
);

$situacaoAtual = strtoupper(
    trim(
        (string) (
            $situacao
            ?? 'TODOS'
        )
    )
);

if (
    !in_array(
        $situacaoAtual,
        [
            'TODOS',
            'ATIVOS',
            'INATIVOS',
        ],
        true
    )
) {
    $situacaoAtual = 'TODOS';
}

$formatarData = static function (
    mixed $data
): string {
    $data = trim((string) $data);

    if ($data === '') {
        return 'Não informada';
    }

    $dataConvertida =
        DateTimeImmutable::createFromFormat(
            '!Y-m-d',
            mb_substr($data, 0, 10)
        );

    if (
        !$dataConvertida
        instanceof DateTimeImmutable
    ) {
        return 'Não informada';
    }

    return $dataConvertida->format(
        'd/m/Y'
    );
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

$analisarValidade = static function (
    mixed $dataValidade
): array {
    $dataValidade = trim(
        (string) $dataValidade
    );

    if ($dataValidade === '') {
        return [
            'classe' => '',
            'texto' => '',
        ];
    }

    $validade =
        DateTimeImmutable::createFromFormat(
            '!Y-m-d',
            mb_substr(
                $dataValidade,
                0,
                10
            )
        );

    if (
        !$validade
        instanceof DateTimeImmutable
    ) {
        return [
            'classe' => '',
            'texto' => '',
        ];
    }

    $hoje = new DateTimeImmutable('today');

    if ($validade < $hoje) {
        return [
            'classe' =>
                'document-validity--expired',

            'texto' =>
                'Vencido',
        ];
    }

    $limite = $hoje->modify('+30 days');

    if ($validade <= $limite) {
        return [
            'classe' =>
                'document-validity--warning',

            'texto' =>
                'Vence em breve',
        ];
    }

    return [
        'classe' =>
            'document-validity--valid',

        'texto' =>
            'Válido',
    ];
};

$podeGerenciar =
    temPermissao(
        'documentos.gerenciar'
    );

$podeBaixar =
    temPermissao(
        'documentos.baixar'
    );

$podeExcluir =
    temPermissao(
        'documentos.excluir'
    );

$totalDocumentos = count(
    $documentos ?? []
);
?>

<section class="documents-page">

    <header class="documents-header">

        <div class="documents-header__content">

            <a
                href="<?= escapar(
                    appUrl(
                        'colaboradores/visualizar?id='
                        . $colaboradorId
                    )
                ) ?>"
                class="documents-back"
            >
                <span aria-hidden="true">←</span>
                Voltar ao colaborador
            </a>

            <span class="documents-eyebrow">
                Arquivo funcional
            </span>

            <h1 class="documents-title">
                Documentos
            </h1>

            <p class="documents-subtitle">
                Arquivos pessoais e funcionais de
                <strong>
                    <?= escapar(
                        $nomeColaborador
                    ) ?>
                </strong>.
            </p>

        </div>

        <?php if ($podeGerenciar): ?>

            <a
                href="<?= escapar(
                    appUrl(
                        'documentos/criar?colaborador_id='
                        . $colaboradorId
                    )
                ) ?>"
                class="documents-primary-button"
            >
                <span aria-hidden="true">＋</span>
                Anexar documento
            </a>

        <?php endif; ?>

    </header>

    <?php if (!empty($sucesso)): ?>

        <div
            class="documents-alert documents-alert--success"
            role="status"
        >
            <?= escapar(
                (string) $sucesso
            ) ?>
        </div>

    <?php endif; ?>

    <?php if (!empty($erro)): ?>

        <div
            class="documents-alert documents-alert--error"
            role="alert"
        >
            <?= escapar(
                (string) $erro
            ) ?>
        </div>

    <?php endif; ?>

    <section class="documents-summary">

        <div class="documents-summary__icon">
            <span aria-hidden="true">▤</span>
        </div>

        <div>
            <span class="documents-summary__label">
                Total exibido
            </span>

            <strong class="documents-summary__value">
                <?= $totalDocumentos ?>
            </strong>
        </div>

        <p class="documents-summary__text">
            Os arquivos físicos estão armazenados
            em área privada e não possuem acesso
            direto por URL.
        </p>

    </section>

    <section class="documents-panel">

        <form
            method="get"
            action="<?= escapar(
                appUrl(
                    'colaboradores/documentos'
                )
            ) ?>"
            class="documents-filters"
        >

            <input
                type="hidden"
                name="colaborador_id"
                value="<?= $colaboradorId ?>"
            >

            <div class="documents-field documents-field--search">

                <label for="documents-search">
                    Buscar documento
                </label>

                <div class="documents-search">

                    <span
                        class="documents-search__icon"
                        aria-hidden="true"
                    >
                        ⌕
                    </span>

                    <input
                        type="search"
                        id="documents-search"
                        name="busca"
                        value="<?= escapar(
                            $buscaAtual
                        ) ?>"
                        maxlength="180"
                        placeholder="Título, tipo, número ou arquivo"
                        autocomplete="off"
                    >

                </div>

            </div>

            <div class="documents-field">

                <label for="documents-status">
                    Situação
                </label>

                <select
                    id="documents-status"
                    name="situacao"
                >
                    <option
                        value="TODOS"
                        <?= $situacaoAtual === 'TODOS'
                            ? 'selected'
                            : '' ?>
                    >
                        Todos
                    </option>

                    <option
                        value="ATIVOS"
                        <?= $situacaoAtual === 'ATIVOS'
                            ? 'selected'
                            : '' ?>
                    >
                        Ativos
                    </option>

                    <option
                        value="INATIVOS"
                        <?= $situacaoAtual === 'INATIVOS'
                            ? 'selected'
                            : '' ?>
                    >
                        Inativos
                    </option>
                </select>

            </div>

            <div class="documents-filters__actions">

                <button
                    type="submit"
                    class="documents-filter-button"
                >
                    Filtrar
                </button>

                <?php if (
                    $buscaAtual !== ''
                    || $situacaoAtual !== 'TODOS'
                ): ?>

                    <a
                        href="<?= escapar(
                            appUrl(
                                'colaboradores/documentos?colaborador_id='
                                . $colaboradorId
                            )
                        ) ?>"
                        class="documents-clear-button"
                    >
                        Limpar
                    </a>

                <?php endif; ?>

            </div>

        </form>

        <?php if ($totalDocumentos === 0): ?>

            <div class="documents-empty">

                <div class="documents-empty__icon">
                    <span aria-hidden="true">▤</span>
                </div>

                <h2>
                    Nenhum documento encontrado
                </h2>

                <p>
                    <?php if (
                        $buscaAtual !== ''
                        || $situacaoAtual !== 'TODOS'
                    ): ?>
                        Nenhum arquivo corresponde aos
                        filtros informados.
                    <?php else: ?>
                        Este colaborador ainda não possui
                        documentos anexados.
                    <?php endif; ?>
                </p>

                <?php if (
                    $podeGerenciar
                    && $buscaAtual === ''
                    && $situacaoAtual === 'TODOS'
                ): ?>

                    <a
                        href="<?= escapar(
                            appUrl(
                                'documentos/criar?colaborador_id='
                                . $colaboradorId
                            )
                        ) ?>"
                        class="documents-primary-button"
                    >
                        Anexar primeiro documento
                    </a>

                <?php endif; ?>

            </div>

        <?php else: ?>

            <div class="documents-list">

                <?php foreach (
                    $documentos as $documento
                ): ?>

                    <?php
                    $documentoId = (int) (
                        $documento['id']
                        ?? 0
                    );

                    $documentoAtivo =
                        $valorBooleano(
                            $documento['ativo']
                            ?? false
                        );

                    $validade =
                        $analisarValidade(
                            $documento[
                                'data_validade'
                            ]
                            ?? null
                        );

                    $extensao = mb_strtoupper(
                        trim(
                            (string) (
                                $documento[
                                    'arquivo_extensao'
                                ]
                                ?? 'ARQUIVO'
                            )
                        )
                    );
                    ?>

                    <article
                        class="document-card <?= $documentoAtivo
                            ? ''
                            : 'document-card--inactive' ?>"
                    >

                        <div class="document-card__file">

                            <div
                                class="document-file-icon"
                                aria-hidden="true"
                            >
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
                                            <?= escapar(
                                                (string) (
                                                    $documento[
                                                        'tipo_documento_nome'
                                                    ]
                                                    ?? 'Documento'
                                                )
                                            ) ?>
                                        </span>

                                        <h2 class="document-card__title">
                                            <?= escapar(
                                                (string) (
                                                    $documento[
                                                        'titulo'
                                                    ]
                                                    ?? 'Documento'
                                                )
                                            ) ?>
                                        </h2>

                                    </div>

                                    <span
                                        class="document-status <?= $documentoAtivo
                                            ? 'document-status--active'
                                            : 'document-status--inactive' ?>"
                                    >
                                        <?= $documentoAtivo
                                            ? 'Ativo'
                                            : 'Inativo' ?>
                                    </span>

                                </div>

                                <div class="document-card__details">

                                    <?php if (
                                        !empty(
                                            $documento[
                                                'numero_documento'
                                            ]
                                        )
                                    ): ?>

                                        <div class="document-detail">

                                            <span>
                                                Número
                                            </span>

                                            <strong>
                                                <?= escapar(
                                                    (string) $documento[
                                                        'numero_documento'
                                                    ]
                                                ) ?>
                                            </strong>

                                        </div>

                                    <?php endif; ?>

                                    <div class="document-detail">

                                        <span>
                                            Emissão
                                        </span>

                                        <strong>
                                            <?= escapar(
                                                $formatarData(
                                                    $documento[
                                                        'data_emissao'
                                                    ]
                                                    ?? null
                                                )
                                            ) ?>
                                        </strong>

                                    </div>

                                    <div class="document-detail">

                                        <span>
                                            Validade
                                        </span>

                                        <strong>
                                            <?= escapar(
                                                $formatarData(
                                                    $documento[
                                                        'data_validade'
                                                    ]
                                                    ?? null
                                                )
                                            ) ?>
                                        </strong>

                                        <?php if (
                                            $validade['texto'] !== ''
                                        ): ?>

                                            <small
                                                class="document-validity <?= escapar(
                                                    $validade[
                                                        'classe'
                                                    ]
                                                ) ?>"
                                            >
                                                <?= escapar(
                                                    $validade[
                                                        'texto'
                                                    ]
                                                ) ?>
                                            </small>

                                        <?php endif; ?>

                                    </div>

                                </div>

                                <div class="document-card__metadata">

                                    <span>
                                        <strong>Arquivo:</strong>

                                        <?= escapar(
                                            (string) (
                                                $documento[
                                                    'arquivo_nome_original'
                                                ]
                                                ?? 'Não informado'
                                            )
                                        ) ?>
                                    </span>

                                    <span>
                                        <strong>Tamanho:</strong>

                                        <?= escapar(
                                            $formatarTamanho(
                                                $documento[
                                                    'arquivo_tamanho_bytes'
                                                ]
                                                ?? 0
                                            )
                                        ) ?>
                                    </span>

                                    <span>
                                        <strong>Enviado em:</strong>

                                        <?= escapar(
                                            $formatarDataHora(
                                                $documento[
                                                    'criado_em'
                                                ]
                                                ?? null
                                            )
                                        ) ?>
                                    </span>

                                    <span>
                                        <strong>Enviado por:</strong>

                                        <?= escapar(
                                            (string) (
                                                $documento[
                                                    'enviado_por_nome'
                                                ]
                                                ?? 'Usuário não identificado'
                                            )
                                        ) ?>
                                    </span>

                                </div>

                                <?php if (
                                    !empty(
                                        $documento[
                                            'descricao'
                                        ]
                                    )
                                ): ?>

                                    <p class="document-card__description">
                                        <?= nl2br(
                                            escapar(
                                                (string) $documento[
                                                    'descricao'
                                                ]
                                            )
                                        ) ?>
                                    </p>

                                <?php endif; ?>

                            </div>

                        </div>

                        <div class="document-card__actions">

                            <?php if (
                                $podeBaixar
                                && $documentoAtivo
                            ): ?>

                                <a
                                    href="<?= escapar(
                                        appUrl(
                                            'documentos/baixar?id='
                                            . $documentoId
                                        )
                                    ) ?>"
                                    class="document-action document-action--download"
                                >
                                    Baixar
                                </a>

                            <?php endif; ?>

                            <?php if ($podeGerenciar): ?>

                                <a
                                    href="<?= escapar(
                                        appUrl(
                                            'documentos/editar?id='
                                            . $documentoId
                                        )
                                    ) ?>"
                                    class="document-action"
                                >
                                    Editar
                                </a>

                                <form
                                    method="post"
                                    action="<?= escapar(
                                        appUrl(
                                            'documentos/alterar-status'
                                        )
                                    ) ?>"
                                    class="document-action-form"
                                    data-document-confirm-form
                                    data-confirm-message="<?= escapar(
                                        $documentoAtivo
                                            ? 'Deseja desativar este documento?'
                                            : 'Deseja ativar este documento?'
                                    ) ?>"
                                >
                                    <?= campoCsrf() ?>

                                    <input
                                        type="hidden"
                                        name="documento_id"
                                        value="<?= $documentoId ?>"
                                    >

                                    <button
                                        type="submit"
                                        class="document-action"
                                    >
                                        <?= $documentoAtivo
                                            ? 'Desativar'
                                            : 'Ativar' ?>
                                    </button>
                                </form>

                            <?php endif; ?>

                            <?php if ($podeExcluir): ?>

                                <form
                                    method="post"
                                    action="<?= escapar(
                                        appUrl(
                                            'documentos/excluir'
                                        )
                                    ) ?>"
                                    class="document-action-form"
                                    data-document-confirm-form
                                    data-confirm-message="Deseja remover este documento da listagem? O arquivo será preservado no armazenamento privado."
                                >
                                    <?= campoCsrf() ?>

                                    <input
                                        type="hidden"
                                        name="documento_id"
                                        value="<?= $documentoId ?>"
                                    >

                                    <button
                                        type="submit"
                                        class="document-action document-action--danger"
                                    >
                                        Excluir
                                    </button>
                                </form>

                            <?php endif; ?>

                        </div>

                    </article>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </section>

</section>