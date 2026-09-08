<?php

declare(strict_types=1);

/** @var array<int, array<string, mixed>> $documentos */
/** @var array<string, int> $indicadores */
/** @var array<string, mixed> $filtros */
/** @var array<string, mixed> $paginacao */
/** @var array<int, array<string, mixed>> $tiposDocumento */
/** @var string|null $sucesso */
/** @var string|null $erro */

$documentos = isset($documentos) && is_array($documentos)
    ? $documentos
    : [];

$indicadores = isset($indicadores) && is_array($indicadores)
    ? $indicadores
    : [];

$filtros = isset($filtros) && is_array($filtros)
    ? $filtros
    : [];

$paginacao = isset($paginacao) && is_array($paginacao)
    ? $paginacao
    : [];

$tiposDocumento = isset($tiposDocumento) && is_array($tiposDocumento)
    ? $tiposDocumento
    : [];

$sucesso = isset($sucesso) && is_string($sucesso)
    ? $sucesso
    : null;

$erro = isset($erro) && is_string($erro)
    ? $erro
    : null;

$busca = trim((string) ($filtros['busca'] ?? ''));
$situacao = strtoupper(trim((string) ($filtros['situacao'] ?? 'TODOS')));
$validade = strtoupper(trim((string) ($filtros['validade'] ?? 'TODOS')));
$tipoDocumentoId = (int) ($filtros['tipo_documento_id'] ?? 0);
$porPagina = (int) ($paginacao['por_pagina'] ?? $filtros['por_pagina'] ?? 25);
$diasParaVencer = max(1, (int) ($filtros['dias_para_vencer'] ?? 30));

$paginaAtual = max(1, (int) ($paginacao['pagina_atual'] ?? 1));
$totalPaginas = max(1, (int) ($paginacao['total_paginas'] ?? 1));
$totalRegistros = max(0, (int) ($paginacao['total_registros'] ?? 0));
$registroInicial = max(0, (int) ($paginacao['registro_inicial'] ?? 0));
$registroFinal = max(0, (int) ($paginacao['registro_final'] ?? 0));

$podeBaixar = temPermissao('documentos.baixar');
$podeGerenciar = temPermissao('documentos.gerenciar');
$podeExcluir = temPermissao('documentos.excluir');

$temFiltrosAtivos =
    $busca !== ''
    || $situacao !== 'TODOS'
    || $validade !== 'TODOS'
    || $tipoDocumentoId > 0
    || $porPagina !== 25;

$valorBooleano = static function (mixed $valor): bool {
    if (is_bool($valor)) {
        return $valor;
    }

    if (is_int($valor)) {
        return $valor === 1;
    }

    return in_array(
        mb_strtolower(trim((string) $valor)),
        ['1', 'true', 't', 'sim', 'yes', 'on'],
        true
    );
};

$formatarData = static function (mixed $valor): string {
    $data = trim((string) $valor);

    if ($data === '') {
        return 'Não informada';
    }

    $convertida = DateTimeImmutable::createFromFormat('!Y-m-d', $data);

    return $convertida
        ? $convertida->format('d/m/Y')
        : $data;
};

$formatarDataHora = static function (mixed $valor): string {
    $data = trim((string) $valor);

    if ($data === '') {
        return 'Não informado';
    }

    try {
        return (new DateTimeImmutable($data))->format('d/m/Y \à\s H:i');
    } catch (Throwable) {
        return $data;
    }
};

$formatarBytes = static function (mixed $valor): string {
    $bytes = max(0, (int) $valor);

    if ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2, ',', '.') . ' MB';
    }

    if ($bytes >= 1024) {
        return number_format($bytes / 1024, 2, ',', '.') . ' KB';
    }

    return $bytes . ' bytes';
};

$obterIniciais = static function (string $nome): string {
    $partes = preg_split('/\s+/u', trim($nome));

    if (!is_array($partes)) {
        return 'C';
    }

    $partes = array_values(array_filter(
        $partes,
        static fn(string $parte): bool => $parte !== ''
    ));

    if ($partes === []) {
        return 'C';
    }

    $primeira = mb_strtoupper(mb_substr($partes[0], 0, 1));

    if (count($partes) === 1) {
        return $primeira;
    }

    $ultima = mb_strtoupper(
        mb_substr($partes[count($partes) - 1], 0, 1)
    );

    return $primeira . $ultima;
};

$montarUrl = static function (array $alteracoes = []) use (
    $busca,
    $situacao,
    $validade,
    $tipoDocumentoId,
    $porPagina,
    $diasParaVencer
): string {
    $parametros = [
        'busca' => $busca,
        'situacao' => $situacao,
        'validade' => $validade,
        'tipo_documento_id' => $tipoDocumentoId,
        'por_pagina' => $porPagina,
        'dias_para_vencer' => $diasParaVencer,
        'pagina' => 1,
    ];

    foreach ($alteracoes as $chave => $valor) {
        $parametros[$chave] = $valor;
    }

    $parametros = array_filter(
        $parametros,
        static fn(mixed $valor): bool =>
        $valor !== ''
            && $valor !== null
            && $valor !== 0
            && $valor !== 'TODOS'
    );

    $query = http_build_query($parametros);

    return appUrl('documentos')
        . ($query !== '' ? '?' . $query : '');
};

$obterValidade = static function (
    string $situacaoValidade,
    mixed $diasRestantes
): array {
    $situacaoValidade = strtoupper(trim($situacaoValidade));
    $dias = $diasRestantes !== null && $diasRestantes !== ''
        ? (int) $diasRestantes
        : null;

    return match ($situacaoValidade) {
        'VENCIDO' => [
            'texto' => $dias !== null
                ? 'Vencido há ' . abs($dias) . (abs($dias) === 1 ? ' dia' : ' dias')
                : 'Vencido',
            'classe' => 'doc-validity--expired',
        ],
        'VENCENDO' => [
            'texto' => $dias === 0
                ? 'Vence hoje'
                : 'Vence em ' . max(0, (int) $dias) . ((int) $dias === 1 ? ' dia' : ' dias'),
            'classe' => 'doc-validity--warning',
        ],
        'VALIDO' => [
            'texto' => 'Válido',
            'classe' => 'doc-validity--valid',
        ],
        default => [
            'texto' => 'Sem validade',
            'classe' => 'doc-validity--neutral',
        ],
    };
};
?>

<section class="doc-center">

    <?php if ($sucesso !== null): ?>
        <div class="doc-alert doc-alert--success" role="status">
            <?= escapar($sucesso) ?>
        </div>
    <?php endif; ?>

    <?php if ($erro !== null): ?>
        <div class="doc-alert doc-alert--error" role="alert">
            <?= escapar($erro) ?>
        </div>
    <?php endif; ?>

    <header class="doc-center__header">
        <div>
            <span class="doc-center__eyebrow">Gestão documental</span>

            <h1>Documentos</h1>

            <p>
                Consulte arquivos, acompanhe validades e acesse os documentos
                dos colaboradores em um só lugar.
            </p>
        </div>

        <a
            href="<?= escapar(appUrl('colaboradores')) ?>"
            class="doc-center__new-button">
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                <circle cx="9" cy="7" r="4"></circle>
                <path d="M19 8v6"></path>
                <path d="M22 11h-6"></path>
            </svg>

            <span>Selecionar colaborador</span>
        </a>
    </header>

    <div class="doc-stats">
        <a
            href="<?= escapar($montarUrl([
                        'busca' => '',
                        'situacao' => 'TODOS',
                        'validade' => 'TODOS',
                        'tipo_documento_id' => 0,
                        'pagina' => 1,
                    ])) ?>"
            class="doc-stat">
            <span>Total</span>
            <strong><?= (int) ($indicadores['total'] ?? 0) ?></strong>
            <small>documentos cadastrados</small>
        </a>

        <a
            href="<?= escapar($montarUrl([
                        'busca' => '',
                        'situacao' => 'ATIVOS',
                        'validade' => 'TODOS',
                        'tipo_documento_id' => 0,
                        'pagina' => 1,
                    ])) ?>"
            class="doc-stat doc-stat--success">
            <span>Ativos</span>
            <strong><?= (int) ($indicadores['ativos'] ?? 0) ?></strong>
            <small>disponíveis para uso</small>
        </a>

        <a
            href="<?= escapar($montarUrl([
                        'busca' => '',
                        'situacao' => 'ATIVOS',
                        'validade' => 'VENCENDO',
                        'tipo_documento_id' => 0,
                        'pagina' => 1,
                    ])) ?>"
            class="doc-stat doc-stat--warning">
            <span>Vencendo</span>
            <strong><?= (int) ($indicadores['vencendo'] ?? 0) ?></strong>
            <small>nos próximos <?= $diasParaVencer ?> dias</small>
        </a>

        <a
            href="<?= escapar($montarUrl([
                        'busca' => '',
                        'situacao' => 'ATIVOS',
                        'validade' => 'VENCIDOS',
                        'tipo_documento_id' => 0,
                        'pagina' => 1,
                    ])) ?>"
            class="doc-stat doc-stat--danger">
            <span>Vencidos</span>
            <strong><?= (int) ($indicadores['vencidos'] ?? 0) ?></strong>
            <small>necessitam de atenção</small>
        </a>

        <a
            href="<?= escapar($montarUrl([
                        'busca' => '',
                        'situacao' => 'ATIVOS',
                        'validade' => 'SEM_VALIDADE',
                        'tipo_documento_id' => 0,
                        'pagina' => 1,
                    ])) ?>"
            class="doc-stat doc-stat--info">
            <span>Sem validade</span>
            <strong><?= (int) ($indicadores['sem_validade'] ?? 0) ?></strong>
            <small>sem vencimento informado</small>
        </a>

        <a
            href="<?= escapar($montarUrl([
                        'busca' => '',
                        'situacao' => 'INATIVOS',
                        'validade' => 'TODOS',
                        'tipo_documento_id' => 0,
                        'pagina' => 1,
                    ])) ?>"
            class="doc-stat doc-stat--neutral">
            <span>Inativos</span>
            <strong><?= (int) ($indicadores['inativos'] ?? 0) ?></strong>
            <small>fora de utilização</small>
        </a>
    </div>

    <section class="doc-panel">
        <form
            method="GET"
            action="<?= escapar(appUrl('documentos')) ?>"
            class="doc-filters">
            <div class="doc-field doc-field--search">
                <label for="busca">Buscar documento</label>

                <div class="doc-search-input">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <circle cx="11" cy="11" r="7"></circle>
                        <path d="m20 20-3.5-3.5"></path>
                    </svg>

                    <input
                        type="search"
                        id="busca"
                        name="busca"
                        value="<?= escapar($busca) ?>"
                        placeholder="Colaborador, matrícula, tipo, número ou arquivo">
                </div>
            </div>

            <div class="doc-field">
                <label for="situacao">Situação</label>
                <select id="situacao" name="situacao">
                    <option value="TODOS" <?= $situacao === 'TODOS' ? 'selected' : '' ?>>Todos</option>
                    <option value="ATIVOS" <?= $situacao === 'ATIVOS' ? 'selected' : '' ?>>Ativos</option>
                    <option value="INATIVOS" <?= $situacao === 'INATIVOS' ? 'selected' : '' ?>>Inativos</option>
                </select>
            </div>

            <div class="doc-field">
                <label for="validade">Validade</label>
                <select id="validade" name="validade">
                    <option value="TODOS" <?= $validade === 'TODOS' ? 'selected' : '' ?>>Todas</option>
                    <option value="VALIDOS" <?= $validade === 'VALIDOS' ? 'selected' : '' ?>>Válidos</option>
                    <option value="VENCENDO" <?= $validade === 'VENCENDO' ? 'selected' : '' ?>>Vencendo</option>
                    <option value="VENCIDOS" <?= $validade === 'VENCIDOS' ? 'selected' : '' ?>>Vencidos</option>
                    <option value="SEM_VALIDADE" <?= $validade === 'SEM_VALIDADE' ? 'selected' : '' ?>>Sem validade</option>
                </select>
            </div>

            <div class="doc-field">
                <label for="tipo_documento_id">Tipo</label>
                <select id="tipo_documento_id" name="tipo_documento_id">
                    <option value="0">Todos os tipos</option>

                    <?php foreach ($tiposDocumento as $tipo): ?>
                        <?php $tipoId = (int) ($tipo['id'] ?? 0); ?>

                        <option
                            value="<?= $tipoId ?>"
                            <?= $tipoDocumentoId === $tipoId ? 'selected' : '' ?>>
                            <?= escapar((string) ($tipo['nome'] ?? 'Tipo de documento')) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="doc-field">
                <label for="por_pagina">Exibir</label>
                <select id="por_pagina" name="por_pagina">
                    <?php foreach ([10, 25, 50, 100] as $quantidade): ?>
                        <option
                            value="<?= $quantidade ?>"
                            <?= $porPagina === $quantidade ? 'selected' : '' ?>>
                            <?= $quantidade ?> por página
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <input
                type="hidden"
                name="dias_para_vencer"
                value="<?= $diasParaVencer ?>">

            <div class="doc-filters__actions">
                <?php if ($temFiltrosAtivos): ?>
                    <a
                        href="<?= escapar(appUrl('documentos')) ?>"
                        class="doc-button doc-button--secondary">
                        Limpar filtros
                    </a>
                <?php endif; ?>

                <button type="submit" class="doc-button doc-button--primary">
                    Filtrar
                </button>
            </div>
        </form>

        <div class="doc-result-header">
            <div>
                <h2>Arquivos encontrados</h2>

                <p>
                    <?php if ($totalRegistros > 0): ?>
                        Exibindo <?= $registroInicial ?>–<?= $registroFinal ?> de <?= $totalRegistros ?> documentos.
                    <?php else: ?>
                        Nenhum documento encontrado com os filtros atuais.
                    <?php endif; ?>
                </p>
            </div>

            <span>Alerta de vencimento: <?= $diasParaVencer ?> dias</span>
        </div>

        <?php if ($documentos === []): ?>
            <div class="doc-empty">
                <span class="doc-empty__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <path d="M14 2v6h6"></path>
                        <path d="M9 13h6"></path>
                        <path d="M9 17h4"></path>
                    </svg>
                </span>

                <h3>Nenhum documento encontrado</h3>
                <p>Altere os filtros ou selecione um colaborador para anexar um novo arquivo.</p>

                <a href="<?= escapar(appUrl('colaboradores')) ?>" class="doc-button doc-button--primary">
                    Selecionar colaborador
                </a>
            </div>
        <?php else: ?>
            <div class="doc-table-wrapper">
                <table class="doc-table">
                    <thead>
                        <tr>
                            <th>Colaborador</th>
                            <th>Documento</th>
                            <th>Número</th>
                            <th>Validade</th>
                            <th>Situação</th>
                            <th>Arquivo</th>
                            <th class="doc-table__actions-title">Ações</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($documentos as $documento): ?>
                            <?php
                            $documentoId = (int) ($documento['id'] ?? 0);
                            $colaboradorId = (int) ($documento['colaborador_id'] ?? 0);
                            $colaboradorNome = trim((string) ($documento['colaborador_nome'] ?? 'Colaborador'));
                            $matricula = trim((string) ($documento['colaborador_matricula'] ?? ''));
                            $tipoNome = trim((string) ($documento['tipo_documento_nome'] ?? 'Documento'));
                            $titulo = trim((string) ($documento['titulo'] ?? ''));
                            $numero = trim((string) ($documento['numero_documento'] ?? ''));
                            $nomeOriginal = trim((string) ($documento['arquivo_nome_original'] ?? 'Arquivo'));
                            $extensao = strtoupper(trim((string) ($documento['arquivo_extensao'] ?? '')));
                            $ativo = $valorBooleano($documento['ativo'] ?? false);
                            $validadeDocumento = $obterValidade(
                                (string) ($documento['situacao_validade'] ?? 'SEM_VALIDADE'),
                                $documento['dias_para_vencer'] ?? null
                            );
                            ?>

                            <tr class="<?= $ativo ? '' : 'doc-table__row--inactive' ?>">
                                <td>
                                    <div class="doc-person">
                                        <span class="doc-person__avatar">
                                            <?= escapar($obterIniciais($colaboradorNome)) ?>
                                        </span>

                                        <div>
                                            <a
                                                href="<?= escapar(appUrl('colaboradores/visualizar?id=' . $colaboradorId)) ?>">
                                                <?= escapar($colaboradorNome) ?>
                                            </a>

                                            <span>
                                                <?= $matricula !== ''
                                                    ? 'Matrícula ' . escapar($matricula)
                                                    : 'Sem matrícula' ?>
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <div class="doc-document-cell">
                                        <span><?= escapar($tipoNome) ?></span>
                                        <strong>
                                            <?= escapar($titulo !== '' ? $titulo : $tipoNome) ?>
                                        </strong>
                                        <small>
                                            Enviado em <?= escapar($formatarDataHora($documento['criado_em'] ?? '')) ?>
                                        </small>
                                    </div>
                                </td>

                                <td>
                                    <span class="doc-muted">
                                        <?= escapar($numero !== '' ? $numero : 'Não informado') ?>
                                    </span>
                                </td>

                                <td>
                                    <div class="doc-validity-cell">
                                        <strong><?= escapar($formatarData($documento['data_validade'] ?? '')) ?></strong>
                                        <span class="doc-validity <?= escapar($validadeDocumento['classe']) ?>">
                                            <?= escapar($validadeDocumento['texto']) ?>
                                        </span>
                                    </div>
                                </td>

                                <td>
                                    <span class="doc-status <?= $ativo ? 'doc-status--active' : 'doc-status--inactive' ?>">
                                        <?= $ativo ? 'Ativo' : 'Inativo' ?>
                                    </span>
                                </td>

                                <td>
                                    <div class="doc-file-cell" title="<?= escapar($nomeOriginal) ?>">
                                        <span><?= escapar($extensao !== '' ? $extensao : 'ARQ') ?></span>
                                        <div>
                                            <strong><?= escapar($nomeOriginal) ?></strong>
                                            <small><?= escapar($formatarBytes($documento['arquivo_tamanho_bytes'] ?? 0)) ?></small>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <div class="doc-actions">
                                        <a
                                            href="<?= escapar(appUrl('colaboradores/documentos?colaborador_id=' . $colaboradorId)) ?>"
                                            class="doc-action"
                                            title="Ver documentos do colaborador"
                                            aria-label="Ver documentos do colaborador">
                                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                                <path d="M14 2v6h6"></path>
                                            </svg>
                                        </a>

                                        <?php if ($ativo): ?>
                                            <a
                                                href="<?= escapar(appUrl('documentos/visualizar?id=' . $documentoId . '&origem=central')) ?>"
                                                class="doc-action doc-action--view"
                                                title="Visualizar documento"
                                                aria-label="Visualizar documento"
                                                target="_blank"
                                                rel="noopener noreferrer">
                                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                                    <path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z"></path>
                                                    <circle cx="12" cy="12" r="2.5"></circle>
                                                </svg>
                                            </a>
                                        <?php endif; ?>

                                        <?php if ($podeBaixar && $ativo): ?>
                                            <a
                                                href="<?= escapar(appUrl('documentos/baixar?id=' . $documentoId . '&origem=central')) ?>"
                                                class="doc-action doc-action--download"
                                                title="Baixar documento"
                                                aria-label="Baixar documento">
                                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                                    <path d="M12 3v12"></path>
                                                    <path d="m7 10 5 5 5-5"></path>
                                                    <path d="M5 21h14"></path>
                                                </svg>
                                            </a>
                                        <?php endif; ?>

                                        <?php if ($podeGerenciar): ?>
                                            <a
                                                href="<?= escapar(appUrl('documentos/editar?id=' . $documentoId . '&origem=central')) ?>"
                                                class="doc-action"
                                                title="Editar documento"
                                                aria-label="Editar documento">
                                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                                    <path d="M12 20h9"></path>
                                                    <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L8 18l-4 1 1-4z"></path>
                                                </svg>
                                            </a>

                                            <form
                                                method="POST"
                                                action="<?= escapar(appUrl('documentos/alterar-status')) ?>"
                                                data-document-confirm-form
                                                data-confirm-message="<?= escapar(
                                                                            $ativo
                                                                                ? 'Deseja desativar este documento?'
                                                                                : 'Deseja ativar este documento?'
                                                                        ) ?>">
                                                <?= campoCsrf() ?>

                                                <input type="hidden" name="documento_id" value="<?= $documentoId ?>">
                                                <input type="hidden" name="origem" value="central">

                                                <button
                                                    type="submit"
                                                    class="doc-action <?= $ativo ? 'doc-action--warning' : 'doc-action--success' ?>"
                                                    title="<?= $ativo ? 'Desativar documento' : 'Ativar documento' ?>"
                                                    aria-label="<?= $ativo ? 'Desativar documento' : 'Ativar documento' ?>">
                                                    <?php if ($ativo): ?>
                                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                                            <circle cx="12" cy="12" r="9"></circle>
                                                            <path d="M8 12h8"></path>
                                                        </svg>
                                                    <?php else: ?>
                                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                                            <circle cx="12" cy="12" r="9"></circle>
                                                            <path d="m8 12 3 3 5-6"></path>
                                                        </svg>
                                                    <?php endif; ?>
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                        <?php if ($podeExcluir): ?>
                                            <form
                                                method="POST"
                                                action="<?= escapar(appUrl('documentos/excluir')) ?>"
                                                data-document-confirm-form
                                                data-confirm-message="Deseja remover este documento da listagem?">
                                                <?= campoCsrf() ?>

                                                <input type="hidden" name="documento_id" value="<?= $documentoId ?>">
                                                <input type="hidden" name="origem" value="central">

                                                <button
                                                    type="submit"
                                                    class="doc-action doc-action--danger"
                                                    title="Excluir documento"
                                                    aria-label="Excluir documento">
                                                    <svg viewBox="0 0 24 24" aria-hidden="true">
                                                        <path d="M3 6h18"></path>
                                                        <path d="M8 6V4h8v2"></path>
                                                        <path d="M19 6l-1 14H6L5 6"></path>
                                                    </svg>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <?php if ($totalPaginas > 1): ?>
            <nav class="doc-pagination" aria-label="Paginação dos documentos">
                <span>
                    Página <?= $paginaAtual ?> de <?= $totalPaginas ?>
                </span>

                <div>
                    <a
                        href="<?= escapar($montarUrl(['pagina' => max(1, $paginaAtual - 1)])) ?>"
                        class="doc-pagination__button <?= $paginaAtual <= 1 ? 'is-disabled' : '' ?>"
                        aria-label="Página anterior">
                        Anterior
                    </a>

                    <?php
                    $inicio = max(1, $paginaAtual - 2);
                    $fim = min($totalPaginas, $paginaAtual + 2);
                    ?>

                    <?php for ($pagina = $inicio; $pagina <= $fim; $pagina++): ?>
                        <a
                            href="<?= escapar($montarUrl(['pagina' => $pagina])) ?>"
                            class="doc-pagination__button <?= $pagina === $paginaAtual ? 'is-active' : '' ?>"
                            <?= $pagina === $paginaAtual ? 'aria-current="page"' : '' ?>>
                            <?= $pagina ?>
                        </a>
                    <?php endfor; ?>

                    <a
                        href="<?= escapar($montarUrl(['pagina' => min($totalPaginas, $paginaAtual + 1)])) ?>"
                        class="doc-pagination__button <?= $paginaAtual >= $totalPaginas ? 'is-disabled' : '' ?>"
                        aria-label="Próxima página">
                        Próxima
                    </a>
                </div>
            </nav>
        <?php endif; ?>
    </section>
</section>