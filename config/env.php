<?php

declare(strict_types=1);

/**
 * Carrega as variáveis armazenadas no arquivo .env.
 *
 * Exemplo:
 * DB_HOST=localhost
 *
 * Depois do carregamento, o valor poderá ser acessado com:
 * env('DB_HOST')
 */

function carregarEnv(string $caminhoArquivo): void
{
    if (!file_exists($caminhoArquivo)) {
        throw new RuntimeException(
            'O arquivo .env não foi encontrado em: ' . $caminhoArquivo
        );
    }

    if (!is_readable($caminhoArquivo)) {
        throw new RuntimeException(
            'O arquivo .env não possui permissão de leitura.'
        );
    }

    $linhas = file(
        $caminhoArquivo,
        FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES
    );

    if ($linhas === false) {
        throw new RuntimeException(
            'Não foi possível ler o arquivo .env.'
        );
    }

    foreach ($linhas as $linha) {
        $linha = trim($linha);

        // Ignora linhas vazias e comentários.
        if (
            $linha === '' ||
            str_starts_with($linha, '#')
        ) {
            continue;
        }

        // Ignora linhas que não possuem "=".
        if (!str_contains($linha, '=')) {
            continue;
        }

        [$chave, $valor] = explode('=', $linha, 2);

        $chave = trim($chave);
        $valor = trim($valor);

        if ($chave === '') {
            continue;
        }

        /*
         * Remove aspas simples ou duplas envolvendo o valor.
         *
         * Exemplo:
         * APP_NAME="Sistema de RH"
         */
        if (strlen($valor) >= 2) {
            $primeiroCaractere = $valor[0];
            $ultimoCaractere = $valor[strlen($valor) - 1];

            $possuiAspasDuplas =
                $primeiroCaractere === '"' &&
                $ultimoCaractere === '"';

            $possuiAspasSimples =
                $primeiroCaractere === "'" &&
                $ultimoCaractere === "'";

            if ($possuiAspasDuplas || $possuiAspasSimples) {
                $valor = substr($valor, 1, -1);
            }
        }

        $_ENV[$chave] = $valor;
        $_SERVER[$chave] = $valor;

        putenv($chave . '=' . $valor);
    }
}

/**
 * Retorna uma variável carregada do arquivo .env.
 */
function env(string $chave, mixed $padrao = null): mixed
{
    $valor = $_ENV[$chave]
        ?? $_SERVER[$chave]
        ?? getenv($chave);

    if ($valor === false || $valor === null) {
        return $padrao;
    }

    return match (strtolower((string) $valor)) {
        'true' => true,
        'false' => false,
        'null' => null,
        default => $valor,
    };
}

// Localiza o .env na raiz do projeto.
$caminhoEnv = dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env';

carregarEnv($caminhoEnv);