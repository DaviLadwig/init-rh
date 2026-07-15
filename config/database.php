<?php

declare(strict_types=1);

require_once __DIR__ . '/env.php';

/**
 * Cria e retorna uma conexão PDO com o PostgreSQL.
 */
function conectarBanco(): PDO
{
    $driver = (string) env('DB_DRIVER', 'pgsql');
    $host = (string) env('DB_HOST', 'localhost');
    $porta = (string) env('DB_PORT', '5432');
    $banco = (string) env('DB_DATABASE');
    $usuario = (string) env('DB_USERNAME');
    $senha = (string) env('DB_PASSWORD');

    if ($banco === '') {
        throw new RuntimeException(
            'A variável DB_DATABASE não foi definida no arquivo .env.'
        );
    }

    if ($usuario === '') {
        throw new RuntimeException(
            'A variável DB_USERNAME não foi definida no arquivo .env.'
        );
    }

    $dsn = sprintf(
        '%s:host=%s;port=%s;dbname=%s',
        $driver,
        $host,
        $porta,
        $banco
    );

    try {
        $pdo = new PDO(
            $dsn,
            $usuario,
            $senha,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,

                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

                PDO::ATTR_EMULATE_PREPARES => false,

                PDO::ATTR_STRINGIFY_FETCHES => false,
            ]
        );

        /*
         * Define o fuso horário usado pela conexão.
         * Ajustaremos isso futuramente por organização.
         */
        $pdo->exec("SET TIME ZONE 'America/Sao_Paulo'");

        return $pdo;
    } catch (PDOException $erro) {
        $debug = env('APP_DEBUG', false);

        if ($debug === true) {
            throw new RuntimeException(
                'Erro ao conectar com o PostgreSQL: ' .
                $erro->getMessage()
            );
        }

        throw new RuntimeException(
            'Não foi possível conectar ao banco de dados.'
        );
    }
}   