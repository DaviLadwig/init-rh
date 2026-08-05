<?php

declare(strict_types=1);

require_once BASE_PATH . '/config/documentos.php';

class DocumentoColaboradorController
{
    public function __construct(
        private DocumentoColaboradorService $documentoService,
        private TipoDocumentoRepository $tipoDocumentoRepository,
        private ColaboradorService $colaboradorService
    ) {}


    /**
     * Exibe a tela central com os documentos de todos
     * os colaboradores da organização autenticada.
     */
    public function geral(): void
    {
        $organizacaoId =
            $this->obterOrganizacaoId();

        if ($organizacaoId <= 0) {
            definirFlash(
                'erro',
                'A organização do usuário não foi identificada.'
            );

            redirecionar('dashboard');

            return;
        }

        $tipoDocumentoId = filter_input(
            INPUT_GET,
            'tipo_documento_id',
            FILTER_VALIDATE_INT
        );

        $pagina = filter_input(
            INPUT_GET,
            'pagina',
            FILTER_VALIDATE_INT
        );

        $porPagina = filter_input(
            INPUT_GET,
            'por_pagina',
            FILTER_VALIDATE_INT
        );

        $diasParaVencer = filter_input(
            INPUT_GET,
            'dias_para_vencer',
            FILTER_VALIDATE_INT
        );

        $resultado =
            $this->documentoService
            ->listarGeral(
                $organizacaoId,
                [
                    'busca' =>
                        (string) (
                            $_GET['busca']
                            ?? ''
                        ),

                    'situacao' =>
                        (string) (
                            $_GET['situacao']
                            ?? 'TODOS'
                        ),

                    'validade' =>
                        (string) (
                            $_GET['validade']
                            ?? 'TODOS'
                        ),

                    'tipo_documento_id' =>
                        $tipoDocumentoId
                            ? (int) $tipoDocumentoId
                            : 0,

                    'pagina' =>
                        $pagina
                            ? (int) $pagina
                            : 1,

                    'por_pagina' =>
                        $porPagina
                            ? (int) $porPagina
                            : 25,

                    'dias_para_vencer' =>
                        $diasParaVencer
                            ? (int) $diasParaVencer
                            : 30,
                ]
            );

        $tiposDocumento =
            $this->tipoDocumentoRepository
            ->listarAtivos(
                $organizacaoId
            );

        renderizarView(
            'documentos/geral',
            [
                'tituloPagina' =>
                    'Documentos',

                'documentos' =>
                    $resultado['documentos']
                    ?? [],

                'indicadores' =>
                    $resultado['indicadores']
                    ?? [],

                'filtros' =>
                    $resultado['filtros']
                    ?? [],

                'paginacao' =>
                    $resultado['paginacao']
                    ?? [],

                'tiposDocumento' =>
                    $tiposDocumento,

                'sucesso' =>
                    obterFlash('sucesso'),

                'erro' =>
                    obterFlash('erro'),
            ],
            'layouts/main'
        );
    }

    /**
     * Lista os documentos anexados a um colaborador.
     */
    public function index(): void
    {
        $organizacaoId =
            $this->obterOrganizacaoId();

        $colaboradorId = filter_input(
            INPUT_GET,
            'colaborador_id',
            FILTER_VALIDATE_INT
        );

        if (
            $organizacaoId <= 0
            || !$colaboradorId
        ) {
            definirFlash(
                'erro',
                'O colaborador informado é inválido.'
            );

            redirecionar('colaboradores');

            return;
        }

        $colaborador =
            $this->colaboradorService->buscarPorId(
                (int) $colaboradorId,
                $organizacaoId
            );

        if (!$colaborador) {
            definirFlash(
                'erro',
                'O colaborador não foi encontrado.'
            );

            redirecionar('colaboradores');

            return;
        }

        $busca = trim(
            (string) ($_GET['busca'] ?? '')
        );

        $situacao = strtoupper(
            trim(
                (string) (
                    $_GET['situacao']
                    ?? 'TODOS'
                )
            )
        );

        $documentos =
            $this->documentoService
            ->listarPorColaborador(
                $organizacaoId,
                (int) $colaboradorId,
                $busca,
                $situacao
            );

        renderizarView(
            'documentos/index',
            [
                'tituloPagina' =>
                'Documentos do colaborador',

                'colaborador' =>
                $colaborador,

                'documentos' =>
                $documentos,

                'busca' =>
                $busca,

                'situacao' =>
                $situacao,

                'sucesso' =>
                obterFlash('sucesso'),

                'erro' =>
                obterFlash('erro'),
            ],
            'layouts/main'
        );
    }

    /**
     * Exibe o formulário para anexar um documento.
     */
    public function criar(): void
    {
        $organizacaoId =
            $this->obterOrganizacaoId();

        $colaboradorId = filter_input(
            INPUT_GET,
            'colaborador_id',
            FILTER_VALIDATE_INT
        );

        if (
            $organizacaoId <= 0
            || !$colaboradorId
        ) {
            definirFlash(
                'erro',
                'O colaborador informado é inválido.'
            );

            redirecionar('colaboradores');

            return;
        }

        $colaborador =
            $this->colaboradorService->buscarPorId(
                (int) $colaboradorId,
                $organizacaoId
            );

        if (!$colaborador) {
            definirFlash(
                'erro',
                'O colaborador não foi encontrado.'
            );

            redirecionar('colaboradores');

            return;
        }

        $formulario =
            $this->recuperarFormularioDaSessao();

        $tiposDocumento =
            $this->tipoDocumentoRepository
            ->listarAtivos(
                $organizacaoId
            );

        renderizarView(
            'documentos/create',
            [
                'tituloPagina' =>
                'Anexar documento',

                'colaborador' =>
                $colaborador,

                'dadosFormulario' =>
                $formulario['dados'],

                'errosFormulario' =>
                $formulario['erros'],

                'tiposDocumento' =>
                $tiposDocumento,

                'tamanhoMaximoBytes' =>
                tamanhoMaximoDocumento(),

                'erro' =>
                obterFlash('erro'),
            ],
            'layouts/main'
        );
    }

    /**
     * Valida o upload e salva o novo documento.
     */
    public function salvar(): void
    {
        $colaboradorId = filter_input(
            INPUT_POST,
            'colaborador_id',
            FILTER_VALIDATE_INT
        );

        if (!$colaboradorId) {
            definirFlash(
                'erro',
                'O colaborador informado é inválido.'
            );

            redirecionar('colaboradores');

            return;
        }

        if (!$this->csrfDaRequisicaoValido()) {
            definirFlash(
                'erro',
                'A sessão expirou. Atualize a página e tente novamente.'
            );

            redirecionar(
                'documentos/criar?colaborador_id='
                    . (int) $colaboradorId
            );

            return;
        }

        $organizacaoId =
            $this->obterOrganizacaoId();

        $usuarioId =
            $this->obterUsuarioId();

        $arquivoRecebido =
            isset($_FILES['arquivo'])
            && is_array($_FILES['arquivo'])
            ? $_FILES['arquivo']
            : [];

        $resultado =
            $this->documentoService->criar(
                $organizacaoId,
                (int) $colaboradorId,
                $usuarioId > 0
                    ? $usuarioId
                    : null,
                $_POST,
                $arquivoRecebido
            );

        if (
            !($resultado['sucesso'] ?? false)
        ) {
            /*
             * O arquivo não é guardado na sessão.
             * O navegador exigirá que ele seja selecionado
             * novamente por segurança.
             */
            $_SESSION['documento_formulario'] = $resultado['dados'] ?? $_POST;

            $_SESSION['documento_erros'] = $resultado['erros'] ?? [];

            definirFlash(
                'erro',
                (string) (
                    $resultado['mensagem']
                    ?? 'Não foi possível anexar o documento.'
                )
            );

            redirecionar(
                'documentos/criar?colaborador_id='
                    . (int) $colaboradorId
            );

            return;
        }

        definirFlash(
            'sucesso',
            (string) (
                $resultado['mensagem']
                ?? 'Documento anexado com sucesso.'
            )
        );

        redirecionar(
            'colaboradores/documentos?colaborador_id='
                . (int) $colaboradorId
        );
    }

    /**
     * Exibe o formulário de edição dos metadados.
     *
     * O arquivo físico não será substituído nessa tela.
     */
    public function editar(): void
    {
        $organizacaoId =
            $this->obterOrganizacaoId();

        $origem =
            $this->obterOrigemListagem();

        $documentoId = filter_input(
            INPUT_GET,
            'id',
            FILTER_VALIDATE_INT
        );

        if (
            $organizacaoId <= 0
            || !$documentoId
        ) {
            definirFlash(
                'erro',
                'O documento informado é inválido.'
            );

            redirecionar(
                $origem === 'central'
                    ? 'documentos'
                    : 'colaboradores'
            );

            return;
        }

        $documentoAtual =
            $this->documentoService->buscarPorId(
                (int) $documentoId,
                $organizacaoId
            );

        if (!$documentoAtual) {
            definirFlash(
                'erro',
                'O documento não foi encontrado.'
            );

            redirecionar(
                $origem === 'central'
                    ? 'documentos'
                    : 'colaboradores'
            );

            return;
        }

        $colaboradorId = (int) (
            $documentoAtual['colaborador_id']
            ?? 0
        );

        $colaborador =
            $this->colaboradorService->buscarPorId(
                $colaboradorId,
                $organizacaoId
            );

        if (!$colaborador) {
            definirFlash(
                'erro',
                'O colaborador relacionado ao documento não foi encontrado.'
            );

            redirecionar(
                $origem === 'central'
                    ? 'documentos'
                    : 'colaboradores'
            );

            return;
        }

        $formulario =
            $this->recuperarFormularioDaSessao();

        $documento =
            $documentoAtual;

        /*
         * Mantém os valores digitados caso a validação
         * tenha devolvido o usuário para o formulário.
         */
        if ($formulario['dados'] !== []) {
            $documento = array_merge(
                $documentoAtual,
                $formulario['dados']
            );

            $documento['id'] =
                $documentoAtual['id'];

            $documento['colaborador_id'] =
                $documentoAtual['colaborador_id'];
        }

        $tiposDocumento =
            $this->carregarTiposDocumentoParaEdicao(
                $organizacaoId,
                (int) (
                    $documentoAtual['tipo_documento_id']
                    ?? 0
                )
            );

        renderizarView(
            'documentos/edit',
            [
                'tituloPagina' =>
                'Editar documento',

                'colaborador' =>
                $colaborador,

                'documento' =>
                $documento,

                'errosFormulario' =>
                $formulario['erros'],

                'tiposDocumento' =>
                $tiposDocumento,

                'origem' =>
                $origem,

                'erro' =>
                obterFlash('erro'),
            ],
            'layouts/main'
        );
    }

    /**
     * Atualiza somente as informações descritivas.
     */
    public function atualizar(): void
    {
        $origem =
            $this->obterOrigemListagem();

        $documentoId = filter_input(
            INPUT_POST,
            'documento_id',
            FILTER_VALIDATE_INT
        );

        if (!$documentoId) {
            definirFlash(
                'erro',
                'O documento informado é inválido.'
            );

            redirecionar(
                $origem === 'central'
                    ? 'documentos'
                    : 'colaboradores'
            );

            return;
        }

        $organizacaoId =
            $this->obterOrganizacaoId();

        $documentoAtual =
            $this->documentoService->buscarPorId(
                (int) $documentoId,
                $organizacaoId
            );

        if (!$documentoAtual) {
            definirFlash(
                'erro',
                'O documento não foi encontrado.'
            );

            redirecionar(
                $origem === 'central'
                    ? 'documentos'
                    : 'colaboradores'
            );

            return;
        }

        $colaboradorId = (int) (
            $documentoAtual['colaborador_id']
            ?? 0
        );

        if (!$this->csrfDaRequisicaoValido()) {
            definirFlash(
                'erro',
                'A sessão expirou. Atualize a página e tente novamente.'
            );

            redirecionar(
                $this->destinoEdicao(
                    (int) $documentoId,
                    $origem
                )
            );

            return;
        }

        $resultado =
            $this->documentoService
            ->atualizarMetadados(
                (int) $documentoId,
                $organizacaoId,
                $_POST
            );

        if (
            !($resultado['sucesso'] ?? false)
        ) {
            $_SESSION['documento_formulario'] = $resultado['dados'] ?? $_POST;

            $_SESSION['documento_erros'] = $resultado['erros'] ?? [];

            definirFlash(
                'erro',
                (string) (
                    $resultado['mensagem']
                    ?? 'Não foi possível atualizar o documento.'
                )
            );

            redirecionar(
                $this->destinoEdicao(
                    (int) $documentoId,
                    $origem
                )
            );

            return;
        }

        definirFlash(
            'sucesso',
            (string) (
                $resultado['mensagem']
                ?? 'Documento atualizado com sucesso.'
            )
        );

        redirecionar(
            $this->destinoListagem(
                $colaboradorId,
                $origem
            )
        );
    }

    /**
     * Ativa ou desativa um documento.
     */
    public function alterarStatus(): void
    {
        $origem =
            $this->obterOrigemListagem();

        $documentoId = filter_input(
            INPUT_POST,
            'documento_id',
            FILTER_VALIDATE_INT
        );

        if (!$documentoId) {
            definirFlash(
                'erro',
                'O documento informado é inválido.'
            );

            redirecionar(
                $origem === 'central'
                    ? 'documentos'
                    : 'colaboradores'
            );

            return;
        }

        $organizacaoId =
            $this->obterOrganizacaoId();

        $documento =
            $this->documentoService->buscarPorId(
                (int) $documentoId,
                $organizacaoId
            );

        if (!$documento) {
            definirFlash(
                'erro',
                'O documento não foi encontrado.'
            );

            redirecionar(
                $origem === 'central'
                    ? 'documentos'
                    : 'colaboradores'
            );

            return;
        }

        $colaboradorId = (int) (
            $documento['colaborador_id']
            ?? 0
        );

        if (!$this->csrfDaRequisicaoValido()) {
            definirFlash(
                'erro',
                'A sessão expirou. Atualize a página e tente novamente.'
            );

            redirecionar(
                $this->destinoListagem(
                    $colaboradorId,
                    $origem
                )
            );

            return;
        }

        $resultado =
            $this->documentoService
            ->alterarStatus(
                (int) $documentoId,
                $organizacaoId
            );

        $tipoFlash =
            ($resultado['sucesso'] ?? false)
            ? 'sucesso'
            : 'erro';

        definirFlash(
            $tipoFlash,
            (string) (
                $resultado['mensagem']
                ?? 'Não foi possível alterar a situação do documento.'
            )
        );

        redirecionar(
            $this->destinoListagem(
                $colaboradorId,
                $origem
            )
        );
    }

    /**
     * Realiza a exclusão lógica.
     *
     * O arquivo físico continua no storage privado
     * enquanto não existir uma política formal de descarte.
     */
    public function excluir(): void
    {
        $origem =
            $this->obterOrigemListagem();

        $documentoId = filter_input(
            INPUT_POST,
            'documento_id',
            FILTER_VALIDATE_INT
        );

        if (!$documentoId) {
            definirFlash(
                'erro',
                'O documento informado é inválido.'
            );

            redirecionar(
                $origem === 'central'
                    ? 'documentos'
                    : 'colaboradores'
            );

            return;
        }

        $organizacaoId =
            $this->obterOrganizacaoId();

        $documento =
            $this->documentoService->buscarPorId(
                (int) $documentoId,
                $organizacaoId
            );

        if (!$documento) {
            definirFlash(
                'erro',
                'O documento não foi encontrado.'
            );

            redirecionar(
                $origem === 'central'
                    ? 'documentos'
                    : 'colaboradores'
            );

            return;
        }

        $colaboradorId = (int) (
            $documento['colaborador_id']
            ?? 0
        );

        if (!$this->csrfDaRequisicaoValido()) {
            definirFlash(
                'erro',
                'A sessão expirou. Atualize a página e tente novamente.'
            );

            redirecionar(
                $this->destinoListagem(
                    $colaboradorId,
                    $origem
                )
            );

            return;
        }

        $resultado =
            $this->documentoService
            ->excluirLogicamente(
                (int) $documentoId,
                $organizacaoId
            );

        $tipoFlash =
            ($resultado['sucesso'] ?? false)
            ? 'sucesso'
            : 'erro';

        definirFlash(
            $tipoFlash,
            (string) (
                $resultado['mensagem']
                ?? 'Não foi possível remover o documento.'
            )
        );

        redirecionar(
            $this->destinoListagem(
                $colaboradorId,
                $origem
            )
        );
    }

    /**
     * Exibe o arquivo diretamente no navegador por uma
     * rota protegida.
     *
     * São permitidos somente PDF, JPG e PNG já validados
     * pelo service. O caminho físico nunca é exposto.
     */
    public function visualizar(): void
    {
        $this->entregarArquivoProtegido(
            'inline'
        );
    }

    /**
     * Entrega o arquivo como download por uma rota
     * protegida.
     *
     * O caminho físico nunca é enviado ao navegador.
     */
    public function baixar(): void
    {
        $this->entregarArquivoProtegido(
            'attachment'
        );
    }

    /**
     * Centraliza a preparação e transmissão protegida
     * usada tanto na visualização quanto no download.
     *
     * Modos aceitos:
     *
     * - inline: abre no navegador;
     * - attachment: força o download.
     */
    private function entregarArquivoProtegido(
        string $disposicao
    ): void {
        $disposicao = $disposicao === 'inline'
            ? 'inline'
            : 'attachment';

        $origem =
            $this->obterOrigemListagem();

        $organizacaoId =
            $this->obterOrganizacaoId();

        $documentoId = filter_input(
            INPUT_GET,
            'id',
            FILTER_VALIDATE_INT
        );

        if (
            $organizacaoId <= 0
            || !$documentoId
        ) {
            definirFlash(
                'erro',
                'O documento informado é inválido.'
            );

            redirecionar(
                $origem === 'central'
                    ? 'documentos'
                    : 'colaboradores'
            );

            return;
        }

        /*
         * Buscamos primeiro para conhecer o colaborador
         * e retornar à tela correta em caso de falha.
         */
        $documento =
            $this->documentoService->buscarPorId(
                (int) $documentoId,
                $organizacaoId
            );

        if (!$documento) {
            definirFlash(
                'erro',
                'O documento não foi encontrado.'
            );

            redirecionar(
                $origem === 'central'
                    ? 'documentos'
                    : 'colaboradores'
            );

            return;
        }

        $colaboradorId = (int) (
            $documento['colaborador_id']
            ?? 0
        );

        /*
         * prepararDownload() também confirma:
         *
         * - organização;
         * - documento ativo;
         * - caminho seguro;
         * - existência do arquivo;
         * - hash SHA-256.
         */
        $resultado =
            $this->documentoService
            ->prepararDownload(
                (int) $documentoId,
                $organizacaoId
            );

        if (
            !($resultado['sucesso'] ?? false)
        ) {
            definirFlash(
                'erro',
                (string) (
                    $resultado['mensagem']
                    ?? 'O arquivo não está disponível.'
                )
            );

            redirecionar(
                $this->destinoListagem(
                    $colaboradorId,
                    $origem
                )
            );

            return;
        }

        $caminho = (string) (
            $resultado['caminho_absoluto']
            ?? ''
        );

        if (
            $caminho === ''
            || !is_file($caminho)
            || !is_readable($caminho)
        ) {
            definirFlash(
                'erro',
                'O arquivo não está disponível para leitura.'
            );

            redirecionar(
                $this->destinoListagem(
                    $colaboradorId,
                    $origem
                )
            );

            return;
        }

        if (headers_sent()) {
            definirFlash(
                'erro',
                $disposicao === 'inline'
                    ? 'Não foi possível abrir o documento.'
                    : 'Não foi possível iniciar o download do documento.'
            );

            redirecionar(
                $this->destinoListagem(
                    $colaboradorId,
                    $origem
                )
            );

            return;
        }

        $nomeDownload =
            $this->normalizarNomeDownload(
                (string) (
                    $resultado['nome_download']
                    ?? 'documento'
                )
            );

        $mime =
            $this->normalizarMimeDownload(
                (string) (
                    $resultado['mime']
                    ?? ''
                )
            );

        if (
            $disposicao === 'inline'
            && !$this->mimePodeSerVisualizado(
                $mime
            )
        ) {
            definirFlash(
                'erro',
                'Este formato não pode ser visualizado no navegador. Faça o download do arquivo.'
            );

            redirecionar(
                $this->destinoListagem(
                    $colaboradorId,
                    $origem
                )
            );

            return;
        }

        $tamanho = filesize($caminho);

        if ($tamanho === false) {
            definirFlash(
                'erro',
                'Não foi possível identificar o tamanho do arquivo.'
            );

            redirecionar(
                $this->destinoListagem(
                    $colaboradorId,
                    $origem
                )
            );

            return;
        }

        /*
         * Fecha a sessão antes de transmitir o arquivo.
         * Isso evita bloquear outras requisições do mesmo
         * usuário durante visualizações ou downloads.
         */
        if (
            session_status()
            === PHP_SESSION_ACTIVE
        ) {
            session_write_close();
        }

        /*
         * Remove qualquer saída anterior para impedir
         * corrupção do PDF ou da imagem.
         */
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $nomeAscii =
            $this->criarNomeAscii(
                $nomeDownload
            );

        header(
            'Content-Type: ' . $mime
        );

        header(
            'Content-Length: '
                . (string) $tamanho
        );

        header(
            'Content-Disposition: '
                . $disposicao
                . '; filename="'
                . $nomeAscii
                . '"; '
                . "filename*=UTF-8''"
                . rawurlencode($nomeDownload)
        );

        header(
            'X-Content-Type-Options: nosniff'
        );

        header(
            $disposicao === 'inline'
                ? 'X-Frame-Options: SAMEORIGIN'
                : 'X-Frame-Options: DENY'
        );

        header(
            'Referrer-Policy: no-referrer'
        );

        header(
            "Content-Security-Policy: default-src 'none'; frame-ancestors 'self'; sandbox"
        );

        header(
            'Cache-Control: private, no-store, no-cache, '
                . 'must-revalidate, max-age=0'
        );

        header(
            'Pragma: no-cache'
        );

        header(
            'Expires: 0'
        );

        $arquivo = fopen(
            $caminho,
            'rb'
        );

        if ($arquivo === false) {
            http_response_code(500);

            exit;
        }

        /*
         * Transmite em blocos para não carregar o arquivo
         * completo na memória.
         */
        while (!feof($arquivo)) {
            $bloco = fread(
                $arquivo,
                8192
            );

            if ($bloco === false) {
                break;
            }

            echo $bloco;

            flush();

            if (connection_aborted()) {
                break;
            }
        }

        fclose($arquivo);

        exit;
    }

    /**
     * Limita a visualização direta aos formatos que o
     * navegador consegue apresentar com segurança.
     */
    private function mimePodeSerVisualizado(
        string $mime
    ): bool {
        return in_array(
            $mime,
            [
                'application/pdf',
                'image/jpeg',
                'image/png',
            ],
            true
        );
    }


    /**
     * Identifica de qual listagem a ação foi iniciada.
     *
     * Valores aceitos:
     *
     * - central: tela geral de documentos;
     * - colaborador: documentos de uma pessoa.
     */
    private function obterOrigemListagem(): string
    {
        $origem =
            $_POST['origem']
            ?? $_GET['origem']
            ?? 'colaborador';

        return is_string($origem)
            && $origem === 'central'
            ? 'central'
            : 'colaborador';
    }

    /**
     * Define para qual listagem o usuário será enviado
     * depois de editar, desativar ou excluir.
     */
    private function destinoListagem(
        int $colaboradorId,
        string $origem
    ): string {
        if ($origem === 'central') {
            return 'documentos';
        }

        return 'colaboradores/documentos?colaborador_id='
            . max(0, $colaboradorId);
    }

    /**
     * Preserva a origem ao retornar ao formulário de edição.
     */
    private function destinoEdicao(
        int $documentoId,
        string $origem
    ): string {
        $destino =
            'documentos/editar?id='
            . max(0, $documentoId);

        if ($origem === 'central') {
            $destino .= '&origem=central';
        }

        return $destino;
    }

    /**
     * Retorna o ID da organização autenticada.
     */
    private function obterOrganizacaoId(): int
    {
        $usuario =
            usuarioAutenticado();

        if (!is_array($usuario)) {
            return 0;
        }

        return (int) (
            $usuario['organizacao_id']
            ?? 0
        );
    }

    /**
     * Retorna o ID do usuário que realizou o upload.
     */
    private function obterUsuarioId(): int
    {
        $usuario =
            usuarioAutenticado();

        if (!is_array($usuario)) {
            return 0;
        }

        return (int) (
            $usuario['id']
            ?? $usuario['usuario_id']
            ?? 0
        );
    }

    /**
     * Valida o token CSRF recebido por POST.
     */
    private function csrfDaRequisicaoValido(): bool
    {
        $token =
            $_POST['csrf_token']
            ?? null;

        return csrfValido(
            is_string($token)
                ? $token
                : null
        );
    }

    /**
     * Lista os tipos ativos e mantém disponível
     * o tipo atual caso tenha sido desativado.
     */
    private function carregarTiposDocumentoParaEdicao(
        int $organizacaoId,
        int $tipoAtualId
    ): array {
        $tipos =
            $this->tipoDocumentoRepository
            ->listarAtivos(
                $organizacaoId
            );

        if ($tipoAtualId <= 0) {
            return $tipos;
        }

        foreach ($tipos as $tipo) {
            if (
                (int) ($tipo['id'] ?? 0)
                === $tipoAtualId
            ) {
                return $tipos;
            }
        }

        $tipoAtual =
            $this->tipoDocumentoRepository
            ->buscarPorId(
                $tipoAtualId,
                $organizacaoId
            );

        if ($tipoAtual) {
            $tipos[] = $tipoAtual;

            usort(
                $tipos,
                static function (
                    array $primeiro,
                    array $segundo
                ): int {
                    return strcasecmp(
                        (string) (
                            $primeiro['nome']
                            ?? ''
                        ),
                        (string) (
                            $segundo['nome']
                            ?? ''
                        )
                    );
                }
            );
        }

        return $tipos;
    }

    /**
     * Recupera e remove os dados temporários da sessão.
     */
    private function recuperarFormularioDaSessao(): array
    {
        $dados =
            $_SESSION['documento_formulario'] ?? [];

        $erros =
            $_SESSION['documento_erros'] ?? [];

        unset(
            $_SESSION['documento_formulario'],
            $_SESSION['documento_erros']
        );

        return [
            'dados' =>
            is_array($dados)
                ? $dados
                : [],

            'erros' =>
            is_array($erros)
                ? $erros
                : [],
        ];
    }

    /**
     * Permite somente os MIME configurados.
     */
    private function normalizarMimeDownload(
        string $mime
    ): string {
        $mime = mb_strtolower(
            trim($mime)
        );

        $permitidos = array_keys(
            tiposMimeDocumentosPermitidos()
        );

        return in_array(
            $mime,
            $permitidos,
            true
        )
            ? $mime
            : 'application/octet-stream';
    }

    /**
     * Remove caracteres perigosos do nome usado
     * no cabeçalho Content-Disposition.
     */
    private function normalizarNomeDownload(
        string $nome
    ): string {
        $nome = str_replace(
            [
                "\r",
                "\n",
                "\0",
                '"',
                '\\',
                ';',
            ],
            '',
            $nome
        );

        $nome = basename(
            str_replace(
                '\\',
                '/',
                $nome
            )
        );

        $nome = trim($nome);

        return $nome !== ''
            ? mb_substr(
                $nome,
                0,
                255
            )
            : 'documento';
    }

    /**
     * Gera um nome ASCII compatível com navegadores
     * antigos. O filename* mantém o nome UTF-8.
     */
    private function criarNomeAscii(
        string $nome
    ): string {
        $convertido = iconv(
            'UTF-8',
            'ASCII//TRANSLIT//IGNORE',
            $nome
        );

        if (!is_string($convertido)) {
            $convertido = 'documento';
        }

        $convertido = preg_replace(
            '/[^A-Za-z0-9._ -]/',
            '_',
            $convertido
        ) ?? 'documento';

        $convertido = trim(
            $convertido,
            " .\t\n\r\0\x0B"
        );

        return $convertido !== ''
            ? substr(
                $convertido,
                0,
                180
            )
            : 'documento';
    }
}

/*
Esse controller protege a visualização e o download contra:

acesso cruzado entre organizações;
exposição do caminho físico;
documentos inativos;
arquivos ausentes ou alterados;
corrupção por saída anterior;
cache em navegador ou proxy;
interpretação incorreta do MIME;
injeção no Content-Disposition;
carregamento completo do arquivo na memória;
path traversal, validado novamente pelo service.

*/