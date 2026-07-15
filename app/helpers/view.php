<?php

declare(strict_types=1);

/**
 * Renderiza uma view, com ou sem um layout principal.
 *
 * Exemplo sem layout:
 *
 * renderizarView('auth/login', $dados);
 *
 * Exemplo com layout:
 *
 * renderizarView(
 *     'dashboard/index',
 *     $dados,
 *     'layouts/main'
 * );
 */
function renderizarView(
    string $view,
    array $dados = [],
    ?string $layout = null
): void {
    $view = trim($view, '/');

    if (
        $view === ''
        || str_contains($view, '..')
    ) {
        throw new InvalidArgumentException(
            'O caminho informado para a view é inválido.'
        );
    }

    $arquivoView =
        BASE_PATH
        . '/views/'
        . $view
        . '.php';

    if (!is_file($arquivoView)) {
        throw new RuntimeException(
            'A view não foi encontrada: ' . $view
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Disponibiliza os dados como variáveis
    |--------------------------------------------------------------------------
    |
    | Exemplo:
    |
    | ['tituloPagina' => 'Dashboard']
    |
    | torna-se:
    |
    | $tituloPagina = 'Dashboard';
    |
    */

    extract(
        $dados,
        EXTR_SKIP
    );

    /*
    |--------------------------------------------------------------------------
    | Renderização sem layout
    |--------------------------------------------------------------------------
    |
    | Usada em páginas como login e alteração de senha.
    |
    */

    if ($layout === null) {
        require $arquivoView;
        return;
    }

    $layout = trim($layout, '/');

    if (
        $layout === ''
        || str_contains($layout, '..')
    ) {
        throw new InvalidArgumentException(
            'O caminho informado para o layout é inválido.'
        );
    }

    $arquivoLayout =
        BASE_PATH
        . '/views/'
        . $layout
        . '.php';

    if (!is_file($arquivoLayout)) {
        throw new RuntimeException(
            'O layout não foi encontrado: ' . $layout
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Captura o conteúdo da view
    |--------------------------------------------------------------------------
    |
    | O conteúdo gerado pelo dashboard será armazenado
    | na variável $conteudo e inserido no layouts/main.php.
    |
    */

    ob_start();

    require $arquivoView;

    $conteudo = ob_get_clean();

    if ($conteudo === false) {
        throw new RuntimeException(
            'Não foi possível renderizar o conteúdo da página.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Carrega o layout principal
    |--------------------------------------------------------------------------
    */

    require $arquivoLayout;
}