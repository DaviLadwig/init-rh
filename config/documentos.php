<?php

declare(strict_types=1);

/**
 * Retorna o diretório privado onde os documentos
 * dos colaboradores serão armazenados.
 *
 * O caminho deve estar configurado no arquivo .env
 * e deve ficar fora do diretório público do Apache.
 */
function caminhoStorageDocumentos(): string
{
    $caminho = trim(
        (string) env(
            'DOCUMENTOS_STORAGE_PATH',
            ''
        )
    );

    if ($caminho === '') {
        throw new RuntimeException(
            'O diretório privado de documentos não foi configurado.'
        );
    }

    /*
     * Normaliza as barras para o sistema operacional.
     */
    $caminho = str_replace(
        [
            '\\',
            '/',
        ],
        DIRECTORY_SEPARATOR,
        $caminho
    );

    $caminho = rtrim(
        $caminho,
        DIRECTORY_SEPARATOR
    );

    /*
     * Cria o diretório automaticamente caso ainda
     * não exista.
     *
     * No Linux, 0700 permite acesso somente ao usuário
     * responsável pelo processo.
     */
    if (
        !is_dir($caminho)
        && !mkdir(
            $caminho,
            0700,
            true
        )
        && !is_dir($caminho)
    ) {
        throw new RuntimeException(
            'Não foi possível criar o diretório privado de documentos.'
        );
    }

    if (!is_writable($caminho)) {
        throw new RuntimeException(
            'O diretório privado de documentos não possui permissão de escrita.'
        );
    }

    return $caminho;
}

/**
 * Retorna o tamanho máximo permitido para cada arquivo.
 *
 * O padrão é 10 MB.
 */
function tamanhoMaximoDocumento(): int
{
    $tamanho = filter_var(
        env(
            'DOCUMENTOS_MAX_BYTES',
            10485760
        ),
        FILTER_VALIDATE_INT
    );

    if (
        $tamanho === false
        || $tamanho <= 0
    ) {
        return 10485760;
    }

    return $tamanho;
}

/**
 * Tipos MIME permitidos.
 *
 * O sistema nunca confiará apenas na extensão ou no
 * valor enviado pelo navegador em $_FILES['type'].
 *
 * O MIME real será identificado posteriormente
 * utilizando finfo_file().
 */
function tiposMimeDocumentosPermitidos(): array
{
    return [
        'application/pdf' => 'pdf',
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
    ];
}

/**
 * Retorna as extensões permitidas.
 */
function extensoesDocumentosPermitidas(): array
{
    return array_values(
        tiposMimeDocumentosPermitidos()
    );
}