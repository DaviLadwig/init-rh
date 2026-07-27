<?php

declare(strict_types=1);

require_once BASE_PATH . '/config/documentos.php';

class DocumentoColaboradorService
{
    public function __construct(
        private DocumentoColaboradorRepository $documentoRepository,
        private TipoDocumentoRepository $tipoDocumentoRepository,
        private ColaboradorRepository $colaboradorRepository
    ) {
    }

    /**
     * Lista os documentos de um colaborador pertencente
     * à organização autenticada.
     */
    public function listarPorColaborador(
        int $organizacaoId,
        int $colaboradorId,
        string $busca = '',
        string $situacao = 'TODOS'
    ): array {
        if (
            $organizacaoId <= 0
            || $colaboradorId <= 0
        ) {
            return [];
        }

        $colaborador =
            $this->colaboradorRepository->buscarPorId(
                $colaboradorId,
                $organizacaoId
            );

        if (!$colaborador) {
            return [];
        }

        return $this->documentoRepository
            ->listarPorColaborador(
                $organizacaoId,
                $colaboradorId,
                trim($busca),
                $this->normalizarSituacao(
                    $situacao
                )
            );
    }

    /**
     * Busca um documento pelo ID, sempre limitado
     * pela organização.
     */
    public function buscarPorId(
        int $documentoId,
        int $organizacaoId
    ): ?array {
        if (
            $documentoId <= 0
            || $organizacaoId <= 0
        ) {
            return null;
        }

        return $this->documentoRepository
            ->buscarPorId(
                $documentoId,
                $organizacaoId
            );
    }

    /**
     * Valida, move para o storage privado e registra
     * um novo documento.
     *
     * $arquivoRecebido deve receber:
     *
     * $_FILES['arquivo'] ?? []
     */
    public function criar(
        int $organizacaoId,
        int $colaboradorId,
        ?int $usuarioId,
        array $dadosRecebidos,
        array $arquivoRecebido
    ): array {
        $dados = $this->normalizarDados(
            $dadosRecebidos
        );

        $erros = $this->validarDadosBasicos(
            $dados
        );

        if ($organizacaoId <= 0) {
            $erros['organizacao'] =
                'A organização não foi identificada.';
        }

        if ($colaboradorId <= 0) {
            $erros['colaborador'] =
                'O colaborador informado é inválido.';
        }

        $colaborador = null;

        if (
            $organizacaoId > 0
            && $colaboradorId > 0
        ) {
            $colaborador =
                $this->colaboradorRepository
                    ->buscarPorId(
                        $colaboradorId,
                        $organizacaoId
                    );

            if (!$colaborador) {
                $erros['colaborador'] =
                    'O colaborador não foi encontrado.';
            }
        }

        $tipoDocumento =
            $this->validarTipoDocumento(
                $organizacaoId,
                $dados['tipo_documento_id'],
                null,
                $erros
            );

        $this->validarDatasDocumento(
            $dados,
            $tipoDocumento,
            $erros
        );

        $arquivo =
            $this->validarArquivoEnviado(
                $arquivoRecebido,
                $erros
            );

        if ($erros !== []) {
            return $this->falha(
                'Verifique os campos e o arquivo informados.',
                $erros,
                $dados
            );
        }

        if (
            !$colaborador
            || !$tipoDocumento
            || !$arquivo
        ) {
            return $this->falha(
                'Não foi possível validar o documento.',
                [],
                $dados
            );
        }

        /*
         * Calcula o hash usando o conteúdo real do arquivo.
         * Não utilizamos o nome enviado pelo navegador.
         */
        $hash = hash_file(
            'sha256',
            $arquivo['caminho_temporario']
        );

        if (!is_string($hash)) {
            return $this->falha(
                'Não foi possível verificar a integridade do arquivo.',
                [
                    'arquivo' =>
                        'Não foi possível calcular a assinatura do arquivo.',
                ],
                $dados
            );
        }

        $hash = mb_strtolower(
            trim($hash)
        );

        /*
         * Impede anexar o mesmo conteúdo duas vezes
         * ao mesmo colaborador.
         */
        if (
            $this->documentoRepository
                ->existeHashNoColaborador(
                    $organizacaoId,
                    $colaboradorId,
                    $hash
                )
        ) {
            return $this->falha(
                'Este arquivo já foi anexado ao colaborador.',
                [
                    'arquivo' =>
                        'Selecione um arquivo diferente.',
                ],
                $dados
            );
        }

        try {
            $destino =
                $this->gerarDestinoSeguro(
                    $organizacaoId,
                    $colaboradorId,
                    $arquivo['extensao']
                );
        } catch (Throwable) {
            return $this->falha(
                'O armazenamento privado de documentos não está disponível.',
                [
                    'arquivo' =>
                        'Não foi possível preparar o diretório privado.',
                ],
                $dados
            );
        }

        /*
         * move_uploaded_file() garante que a origem realmente
         * pertence ao mecanismo de upload HTTP do PHP.
         */
        $movido = move_uploaded_file(
            $arquivo['caminho_temporario'],
            $destino['caminho_absoluto']
        );

        if (!$movido) {
            return $this->falha(
                'Não foi possível armazenar o arquivo enviado.',
                [
                    'arquivo' =>
                        'O upload não pôde ser concluído.',
                ],
                $dados
            );
        }

        /*
         * No Linux, limita a leitura e escrita ao usuário
         * responsável pelo processo PHP.
         *
         * No Windows, o chmod pode não alterar as ACLs,
         * mas não prejudica o funcionamento.
         */
        @chmod(
            $destino['caminho_absoluto'],
            0600
        );

        try {
            $documentoId =
                $this->documentoRepository->criar(
                    [
                        'organizacao_id' =>
                            $organizacaoId,

                        'colaborador_id' =>
                            $colaboradorId,

                        'tipo_documento_id' =>
                            $dados[
                                'tipo_documento_id'
                            ],

                        'enviado_por_usuario_id' =>
                            $usuarioId !== null
                            && $usuarioId > 0
                                ? $usuarioId
                                : null,

                        'titulo' =>
                            $dados['titulo'],

                        'numero_documento' =>
                            $dados[
                                'numero_documento'
                            ],

                        'data_emissao' =>
                            $dados['data_emissao'],

                        'data_validade' =>
                            $dados['data_validade'],

                        'descricao' =>
                            $dados['descricao'],

                        'arquivo_nome_original' =>
                            $arquivo[
                                'nome_original'
                            ],

                        'arquivo_nome_armazenado' =>
                            $destino[
                                'nome_armazenado'
                            ],

                        'arquivo_caminho_relativo' =>
                            $destino[
                                'caminho_relativo'
                            ],

                        'arquivo_extensao' =>
                            $arquivo['extensao'],

                        'arquivo_mime' =>
                            $arquivo['mime'],

                        'arquivo_tamanho_bytes' =>
                            $arquivo[
                                'tamanho_bytes'
                            ],

                        'arquivo_hash_sha256' =>
                            $hash,

                        'ativo' => true,
                    ]
                );
        } catch (Throwable $erro) {
            /*
             * Caso o banco rejeite o registro, removemos
             * o arquivo recém-movido para não deixar um
             * arquivo órfão no servidor.
             */
            if (
                is_file(
                    $destino[
                        'caminho_absoluto'
                    ]
                )
            ) {
                @unlink(
                    $destino[
                        'caminho_absoluto'
                    ]
                );
            }

            if (
                $erro instanceof PDOException
                && $erro->getCode() === '23503'
            ) {
                return $this->falha(
                    'O colaborador ou o tipo de documento não está mais disponível.',
                    [],
                    $dados
                );
            }

            if (
                $erro instanceof PDOException
                && $erro->getCode() === '23505'
            ) {
                return $this->falha(
                    'Não foi possível registrar o documento porque houve uma duplicidade.',
                    [],
                    $dados
                );
            }

            throw $erro;
        }

        return [
            'sucesso' => true,
            'mensagem' =>
                'Documento anexado com segurança.',
            'documento_id' =>
                $documentoId,
        ];
    }

    /**
     * Atualiza somente os dados descritivos.
     *
     * O arquivo físico não será substituído nesta
     * primeira versão.
     */
    public function atualizarMetadados(
        int $documentoId,
        int $organizacaoId,
        array $dadosRecebidos
    ): array {
        if (
            $documentoId <= 0
            || $organizacaoId <= 0
        ) {
            return $this->falha(
                'O documento informado é inválido.'
            );
        }

        $documentoAtual =
            $this->documentoRepository
                ->buscarPorId(
                    $documentoId,
                    $organizacaoId
                );

        if (!$documentoAtual) {
            return $this->falha(
                'O documento não foi encontrado.'
            );
        }

        $dados = $this->normalizarDados(
            $dadosRecebidos
        );

        $erros = $this->validarDadosBasicos(
            $dados
        );

        $tipoDocumento =
            $this->validarTipoDocumento(
                $organizacaoId,
                $dados['tipo_documento_id'],
                (int) (
                    $documentoAtual[
                        'tipo_documento_id'
                    ]
                    ?? 0
                ),
                $erros
            );

        $this->validarDatasDocumento(
            $dados,
            $tipoDocumento,
            $erros
        );

        if ($erros !== []) {
            return $this->falha(
                'Verifique os campos informados.',
                $erros,
                $dados
            );
        }

        try {
            $atualizado =
                $this->documentoRepository
                    ->atualizarMetadados(
                        $documentoId,
                        $organizacaoId,
                        $dados
                    );
        } catch (PDOException $erro) {
            if ($erro->getCode() === '23503') {
                return $this->falha(
                    'O tipo de documento selecionado não está mais disponível.',
                    [],
                    $dados
                );
            }

            throw $erro;
        }

        return [
            'sucesso' => true,
            'mensagem' => $atualizado
                ? 'Documento atualizado com sucesso.'
                : 'Nenhuma alteração foi necessária.',
        ];
    }

    /**
     * Ativa ou desativa um documento.
     */
    public function alterarStatus(
        int $documentoId,
        int $organizacaoId
    ): array {
        if (
            $documentoId <= 0
            || $organizacaoId <= 0
        ) {
            return $this->falha(
                'O documento informado é inválido.'
            );
        }

        $documento =
            $this->documentoRepository
                ->buscarPorId(
                    $documentoId,
                    $organizacaoId
                );

        if (!$documento) {
            return $this->falha(
                'O documento não foi encontrado.'
            );
        }

        $novoStatus =
            !$this->valorBooleano(
                $documento['ativo']
                ?? false
            );

        /*
         * Para reativar, o arquivo físico precisa
         * continuar disponível no storage privado.
         */
        if ($novoStatus) {
            $arquivo =
                $this->resolverArquivoSeguro(
                    $documento,
                    false
                );

            if (
                !($arquivo['sucesso'] ?? false)
            ) {
                return $this->falha(
                    'O documento não pode ser ativado porque o arquivo físico não está disponível.'
                );
            }
        }

        $alterado =
            $this->documentoRepository
                ->alterarStatus(
                    $documentoId,
                    $organizacaoId,
                    $novoStatus
                );

        if (!$alterado) {
            return $this->falha(
                'Não foi possível alterar a situação do documento.'
            );
        }

        return [
            'sucesso' => true,
            'mensagem' => $novoStatus
                ? 'Documento ativado com sucesso.'
                : 'Documento desativado com sucesso.',
            'ativo' => $novoStatus,
        ];
    }

    /**
     * Prepara o download protegido.
     *
     * Além da organização, valida:
     *
     * - situação do documento;
     * - caminho relativo;
     * - path traversal;
     * - existência do arquivo;
     * - permissão de leitura;
     * - hash SHA-256.
     *
     * O controller ainda deverá validar autenticação
     * e permissão antes de chamar este método.
     */
    public function prepararDownload(
        int $documentoId,
        int $organizacaoId
    ): array {
        if (
            $documentoId <= 0
            || $organizacaoId <= 0
        ) {
            return $this->falha(
                'O documento informado é inválido.'
            );
        }

        $documento =
            $this->documentoRepository
                ->buscarPorId(
                    $documentoId,
                    $organizacaoId
                );

        if (!$documento) {
            return $this->falha(
                'O documento não foi encontrado.'
            );
        }

        if (
            !$this->valorBooleano(
                $documento['ativo']
                ?? false
            )
        ) {
            return $this->falha(
                'O documento está inativo e não pode ser baixado.'
            );
        }

        $arquivo =
            $this->resolverArquivoSeguro(
                $documento,
                true
            );

        if (
            !($arquivo['sucesso'] ?? false)
        ) {
            return $arquivo;
        }

        return [
            'sucesso' => true,

            'caminho_absoluto' =>
                $arquivo[
                    'caminho_absoluto'
                ],

            'nome_download' =>
                $this->nomeDownloadSeguro(
                    (string) (
                        $documento[
                            'arquivo_nome_original'
                        ]
                        ?? 'documento'
                    ),
                    (string) (
                        $documento[
                            'arquivo_extensao'
                        ]
                        ?? ''
                    )
                ),

            'mime' =>
                (string) (
                    $documento[
                        'arquivo_mime'
                    ]
                    ?? 'application/octet-stream'
                ),

            'tamanho_bytes' =>
                (int) filesize(
                    $arquivo[
                        'caminho_absoluto'
                    ]
                ),

            'documento' =>
                $documento,
        ];
    }

    /**
     * Realiza a exclusão lógica.
     *
     * O arquivo físico permanece no storage privado
     * para recuperação administrativa até existir
     * uma política formal de retenção e descarte.
     */
    public function excluirLogicamente(
        int $documentoId,
        int $organizacaoId
    ): array {
        if (
            $documentoId <= 0
            || $organizacaoId <= 0
        ) {
            return $this->falha(
                'O documento informado é inválido.'
            );
        }

        $documento =
            $this->documentoRepository
                ->buscarPorId(
                    $documentoId,
                    $organizacaoId
                );

        if (!$documento) {
            return $this->falha(
                'O documento não foi encontrado.'
            );
        }

        $excluido =
            $this->documentoRepository
                ->marcarComoExcluido(
                    $documentoId,
                    $organizacaoId
                );

        if (!$excluido) {
            return $this->falha(
                'Não foi possível excluir o documento.'
            );
        }

        return [
            'sucesso' => true,
            'mensagem' =>
                'Documento removido da listagem com segurança.',
        ];
    }

    /**
     * Normaliza os dados do formulário.
     */
    private function normalizarDados(
        array $dados
    ): array {
        $titulo = preg_replace(
            '/\s+/u',
            ' ',
            trim(
                (string) (
                    $dados['titulo']
                    ?? ''
                )
            )
        ) ?? '';

        $numeroDocumento = trim(
            (string) (
                $dados['numero_documento']
                ?? ''
            )
        );

        $dataEmissao = trim(
            (string) (
                $dados['data_emissao']
                ?? ''
            )
        );

        $dataValidade = trim(
            (string) (
                $dados['data_validade']
                ?? ''
            )
        );

        $descricao = trim(
            (string) (
                $dados['descricao']
                ?? ''
            )
        );

        return [
            'tipo_documento_id' =>
                (int) (
                    $dados['tipo_documento_id']
                    ?? 0
                ),

            'titulo' =>
                $titulo,

            'numero_documento' =>
                $numeroDocumento !== ''
                    ? $numeroDocumento
                    : null,

            'data_emissao' =>
                $dataEmissao !== ''
                    ? $dataEmissao
                    : null,

            'data_validade' =>
                $dataValidade !== ''
                    ? $dataValidade
                    : null,

            'descricao' =>
                $descricao !== ''
                    ? $descricao
                    : null,
        ];
    }

    /**
     * Valida os campos descritivos.
     */
    private function validarDadosBasicos(
        array $dados
    ): array {
        $erros = [];

        if (
            $dados['tipo_documento_id']
            <= 0
        ) {
            $erros['tipo_documento_id'] =
                'Selecione o tipo de documento.';
        }

        $titulo = (string) $dados['titulo'];

        if ($titulo === '') {
            $erros['titulo'] =
                'Informe o título do documento.';
        } elseif (
            mb_strlen($titulo) < 2
        ) {
            $erros['titulo'] =
                'O título deve possuir pelo menos 2 caracteres.';
        } elseif (
            mb_strlen($titulo) > 180
        ) {
            $erros['titulo'] =
                'O título deve possuir no máximo 180 caracteres.';
        }

        if (
            $dados['numero_documento']
            !== null
            && mb_strlen(
                (string) $dados[
                    'numero_documento'
                ]
            ) > 100
        ) {
            $erros['numero_documento'] =
                'O número do documento deve possuir no máximo 100 caracteres.';
        }

        if (
            $dados['data_emissao']
            !== null
            && !$this->dataValida(
                (string) $dados[
                    'data_emissao'
                ]
            )
        ) {
            $erros['data_emissao'] =
                'Informe uma data de emissão válida.';
        }

        if (
            $dados['data_validade']
            !== null
            && !$this->dataValida(
                (string) $dados[
                    'data_validade'
                ]
            )
        ) {
            $erros['data_validade'] =
                'Informe uma data de validade válida.';
        }

        if (
            $dados['descricao'] !== null
            && mb_strlen(
                (string) $dados['descricao']
            ) > 5000
        ) {
            $erros['descricao'] =
                'A descrição deve possuir no máximo 5.000 caracteres.';
        }

        return $erros;
    }

    /**
     * Valida o tipo do documento.
     *
     * Na edição, permite manter o tipo atual caso ele
     * tenha sido desativado posteriormente.
     */
    private function validarTipoDocumento(
        int $organizacaoId,
        int $tipoDocumentoId,
        ?int $tipoDocumentoAtualId,
        array &$erros
    ): ?array {
        if (
            $organizacaoId <= 0
            || $tipoDocumentoId <= 0
        ) {
            return null;
        }

        $tipoDocumento =
            $this->tipoDocumentoRepository
                ->buscarPorId(
                    $tipoDocumentoId,
                    $organizacaoId
                );

        if (!$tipoDocumento) {
            $erros['tipo_documento_id'] =
                'O tipo de documento selecionado não foi encontrado.';

            return null;
        }

        $ativo =
            $this->valorBooleano(
                $tipoDocumento['ativo']
                ?? false
            );

        if (
            !$ativo
            && $tipoDocumentoAtualId
                !== $tipoDocumentoId
        ) {
            $erros['tipo_documento_id'] =
                'O tipo de documento selecionado está inativo.';
        }

        return $tipoDocumento;
    }

    /**
     * Valida a emissão e validade conforme o tipo.
     */
    private function validarDatasDocumento(
        array $dados,
        ?array $tipoDocumento,
        array &$erros
    ): void {
        $dataEmissao =
            $dados['data_emissao'];

        $dataValidade =
            $dados['data_validade'];

        if (
            $tipoDocumento
            && $this->valorBooleano(
                $tipoDocumento[
                    'exige_validade'
                ]
                ?? false
            )
            && $dataValidade === null
        ) {
            $erros['data_validade'] =
                'Este tipo de documento exige uma data de validade.';
        }

        if (
            $dataEmissao !== null
            && $dataValidade !== null
            && $this->dataValida(
                (string) $dataEmissao
            )
            && $this->dataValida(
                (string) $dataValidade
            )
            && $dataValidade < $dataEmissao
        ) {
            $erros['data_validade'] =
                'A data de validade não pode ser anterior à data de emissão.';
        }
    }

    /**
     * Valida:
     *
     * - erro de upload;
     * - origem HTTP;
     * - tamanho real;
     * - MIME real;
     * - assinatura PDF;
     * - estrutura JPEG/PNG.
     */
    private function validarArquivoEnviado(
        array $arquivo,
        array &$erros
    ): ?array {
        if ($arquivo === []) {
            $erros['arquivo'] =
                'Selecione um arquivo para anexar.';

            return null;
        }

        $erroUpload = (int) (
            $arquivo['error']
            ?? UPLOAD_ERR_NO_FILE
        );

        if (
            $erroUpload !== UPLOAD_ERR_OK
        ) {
            $erros['arquivo'] =
                $this->mensagemErroUpload(
                    $erroUpload
                );

            return null;
        }

        $caminhoTemporario =
            (string) (
                $arquivo['tmp_name']
                ?? ''
            );

        if (
            $caminhoTemporario === ''
            || !is_file(
                $caminhoTemporario
            )
            || !is_uploaded_file(
                $caminhoTemporario
            )
        ) {
            $erros['arquivo'] =
                'O arquivo enviado não é um upload válido.';

            return null;
        }

        /*
         * O tamanho é consultado no arquivo temporário.
         * Não confiamos no valor size enviado pelo navegador.
         */
        $tamanhoReal = filesize(
            $caminhoTemporario
        );

        if (
            $tamanhoReal === false
            || $tamanhoReal <= 0
        ) {
            $erros['arquivo'] =
                'O arquivo enviado está vazio ou não pôde ser lido.';

            return null;
        }

        $limite =
            tamanhoMaximoDocumento();

        if ($tamanhoReal > $limite) {
            $erros['arquivo'] =
                'O arquivo ultrapassa o limite permitido de '
                . $this->formatarBytes(
                    $limite
                )
                . '.';

            return null;
        }

        if (!class_exists('finfo')) {
            $erros['arquivo'] =
                'O servidor não possui o recurso necessário para validar o tipo real do arquivo.';

            return null;
        }

        /*
         * Detecta o MIME pelo conteúdo real.
         *
         * Nunca utilizamos diretamente:
         *
         * $_FILES['arquivo']['type']
         */
        $finfo = new finfo(
            FILEINFO_MIME_TYPE
        );

        $mimeReal = $finfo->file(
            $caminhoTemporario
        );

        if (!is_string($mimeReal)) {
            $erros['arquivo'] =
                'Não foi possível identificar o tipo real do arquivo.';

            return null;
        }

        $mimeReal = mb_strtolower(
            trim($mimeReal)
        );

        $tiposPermitidos =
            tiposMimeDocumentosPermitidos();

        if (
            !array_key_exists(
                $mimeReal,
                $tiposPermitidos
            )
        ) {
            $erros['arquivo'] =
                'Formato não permitido. Envie somente PDF, JPG ou PNG.';

            return null;
        }

        $extensao =
            (string) $tiposPermitidos[
                $mimeReal
            ];

        if (
            !$this->conteudoCompativelComMime(
                $caminhoTemporario,
                $mimeReal
            )
        ) {
            $erros['arquivo'] =
                'O conteúdo do arquivo não corresponde ao formato identificado.';

            return null;
        }

        /*
         * O nome original é guardado apenas para exibição.
         * Ele nunca será usado como caminho físico.
         */
        $nomeOriginal =
            $this->normalizarNomeOriginal(
                (string) (
                    $arquivo['name']
                    ?? 'documento.'
                        . $extensao
                )
            );

        if (
            mb_strlen($nomeOriginal) > 255
        ) {
            $erros['arquivo'] =
                'O nome original do arquivo é muito longo.';

            return null;
        }

        return [
            'caminho_temporario' =>
                $caminhoTemporario,

            'nome_original' =>
                $nomeOriginal,

            'mime' =>
                $mimeReal,

            'extensao' =>
                $extensao,

            'tamanho_bytes' =>
                (int) $tamanhoReal,
        ];
    }

    /**
     * Confere assinatura PDF e estrutura real de
     * imagens JPEG ou PNG.
     */
    private function conteudoCompativelComMime(
        string $caminho,
        string $mime
    ): bool {
        if ($mime === 'application/pdf') {
            $arquivo = fopen(
                $caminho,
                'rb'
            );

            if ($arquivo === false) {
                return false;
            }

            $assinatura = fread(
                $arquivo,
                5
            );

            fclose($arquivo);

            return $assinatura === '%PDF-';
        }

        if (
            $mime === 'image/jpeg'
            || $mime === 'image/png'
        ) {
            $informacoes =
                @getimagesize($caminho);

            if (!is_array($informacoes)) {
                return false;
            }

            $tipoImagem = (int) (
                $informacoes[2]
                ?? 0
            );

            if ($mime === 'image/jpeg') {
                return $tipoImagem
                    === IMAGETYPE_JPEG;
            }

            return $tipoImagem
                === IMAGETYPE_PNG;
        }

        return false;
    }

    /**
     * Gera:
     *
     * organizacao_ID/colaborador_ID/nome-aleatorio.ext
     */
    private function gerarDestinoSeguro(
        int $organizacaoId,
        int $colaboradorId,
        string $extensao
    ): array {
        $base =
            caminhoStorageDocumentos();

        $diretorioRelativo =
            'organizacao_'
            . $organizacaoId
            . DIRECTORY_SEPARATOR
            . 'colaborador_'
            . $colaboradorId;

        $diretorioAbsoluto =
            $base
            . DIRECTORY_SEPARATOR
            . $diretorioRelativo;

        if (
            !is_dir($diretorioAbsoluto)
            && !mkdir(
                $diretorioAbsoluto,
                0700,
                true
            )
            && !is_dir($diretorioAbsoluto)
        ) {
            throw new RuntimeException(
                'Não foi possível criar o diretório privado.'
            );
        }

        if (
            !is_writable(
                $diretorioAbsoluto
            )
        ) {
            throw new RuntimeException(
                'O diretório privado não possui permissão de escrita.'
            );
        }

        $baseReal = realpath($base);

        $diretorioReal = realpath(
            $diretorioAbsoluto
        );

        if (
            $baseReal === false
            || $diretorioReal === false
            || !$this->caminhoDentroDaBase(
                $diretorioReal,
                $baseReal
            )
        ) {
            throw new RuntimeException(
                'O diretório calculado não é seguro.'
            );
        }

        /*
         * Tenta gerar um nome sem colisões.
         *
         * random_bytes(32) produz 64 caracteres
         * hexadecimais após bin2hex().
         */
        for (
            $tentativa = 0;
            $tentativa < 10;
            $tentativa++
        ) {
            $nomeArmazenado =
                bin2hex(
                    random_bytes(32)
                )
                . '.'
                . $extensao;

            $caminhoRelativoSistema =
                $diretorioRelativo
                . DIRECTORY_SEPARATOR
                . $nomeArmazenado;

            /*
             * No banco, utilizamos barra normal para que
             * o caminho continue portável entre Windows
             * e Linux.
             */
            $caminhoRelativoBanco =
                str_replace(
                    DIRECTORY_SEPARATOR,
                    '/',
                    $caminhoRelativoSistema
                );

            $caminhoAbsoluto =
                $diretorioReal
                . DIRECTORY_SEPARATOR
                . $nomeArmazenado;

            if (
                !file_exists(
                    $caminhoAbsoluto
                )
                && !$this
                    ->documentoRepository
                    ->existeCaminho(
                        $organizacaoId,
                        $caminhoRelativoBanco
                    )
            ) {
                return [
                    'nome_armazenado' =>
                        $nomeArmazenado,

                    'caminho_relativo' =>
                        $caminhoRelativoBanco,

                    'caminho_absoluto' =>
                        $caminhoAbsoluto,
                ];
            }
        }

        throw new RuntimeException(
            'Não foi possível gerar um nome seguro.'
        );
    }

    /**
     * Resolve um caminho registrado no banco sem
     * aceitar path traversal ou saída do storage.
     */
    private function resolverArquivoSeguro(
        array $documento,
        bool $verificarHash
    ): array {
        try {
            $base =
                caminhoStorageDocumentos();
        } catch (Throwable) {
            return $this->falha(
                'O armazenamento privado não está disponível.'
            );
        }

        $caminhoRelativo = trim(
            (string) (
                $documento[
                    'arquivo_caminho_relativo'
                ]
                ?? ''
            )
        );

        if (
            $caminhoRelativo === ''
            || str_contains(
                $caminhoRelativo,
                "\0"
            )
            || preg_match(
                '#(^|[\\/])\.\.([\\/]|$)#',
                $caminhoRelativo
            )
        ) {
            return $this->falha(
                'O caminho do documento é inválido.'
            );
        }

        $caminhoNormalizado =
            str_replace(
                [
                    '/',
                    '\\',
                ],
                DIRECTORY_SEPARATOR,
                $caminhoRelativo
            );

        $caminhoAbsoluto =
            $base
            . DIRECTORY_SEPARATOR
            . ltrim(
                $caminhoNormalizado,
                DIRECTORY_SEPARATOR
            );

        $baseReal = realpath($base);

        $arquivoReal = realpath(
            $caminhoAbsoluto
        );

        if (
            $baseReal === false
            || $arquivoReal === false
            || !$this->caminhoDentroDaBase(
                $arquivoReal,
                $baseReal
            )
            || !is_file($arquivoReal)
            || !is_readable($arquivoReal)
        ) {
            return $this->falha(
                'O arquivo do documento não está disponível.'
            );
        }

        if ($verificarHash) {
            $hashEsperado =
                mb_strtolower(
                    trim(
                        (string) (
                            $documento[
                                'arquivo_hash_sha256'
                            ]
                            ?? ''
                        )
                    )
                );

            $hashAtual = hash_file(
                'sha256',
                $arquivoReal
            );

            if (
                $hashEsperado === ''
                || !is_string($hashAtual)
                || !hash_equals(
                    $hashEsperado,
                    mb_strtolower(
                        $hashAtual
                    )
                )
            ) {
                return $this->falha(
                    'A integridade do arquivo não pôde ser confirmada.'
                );
            }
        }

        return [
            'sucesso' => true,
            'caminho_absoluto' =>
                $arquivoReal,
        ];
    }

    /**
     * Confirma que o caminho pertence ao storage privado.
     */
    private function caminhoDentroDaBase(
        string $caminho,
        string $base
    ): bool {
        $caminhoNormalizado = rtrim(
            str_replace(
                '\\',
                '/',
                $caminho
            ),
            '/'
        );

        $baseNormalizada = rtrim(
            str_replace(
                '\\',
                '/',
                $base
            ),
            '/'
        );

        /*
         * Caminhos do Windows não diferenciam
         * letras maiúsculas e minúsculas.
         */
        if (
            PHP_OS_FAMILY === 'Windows'
        ) {
            $caminhoNormalizado =
                mb_strtolower(
                    $caminhoNormalizado
                );

            $baseNormalizada =
                mb_strtolower(
                    $baseNormalizada
                );
        }

        return $caminhoNormalizado
            === $baseNormalizada
            || str_starts_with(
                $caminhoNormalizado,
                $baseNormalizada . '/'
            );
    }

    /**
     * Normaliza o nome original.
     *
     * Esse nome será usado apenas para exibição e
     * download, nunca para salvar o arquivo físico.
     */
    private function normalizarNomeOriginal(
        string $nome
    ): string {
        $nome = str_replace(
            '\\',
            '/',
            $nome
        );

        $nome = basename($nome);

        $nome = preg_replace(
            '/[\x00-\x1F\x7F]/u',
            '',
            $nome
        ) ?? '';

        $nome = trim($nome);

        return $nome !== ''
            ? $nome
            : 'documento';
    }

    /**
     * Remove caracteres perigosos para o cabeçalho
     * Content-Disposition.
     */
    private function nomeDownloadSeguro(
        string $nomeOriginal,
        string $extensao
    ): string {
        $nome =
            $this->normalizarNomeOriginal(
                $nomeOriginal
            );

        $nome = str_replace(
            [
                '"',
                "'",
                "\r",
                "\n",
                ';',
            ],
            '',
            $nome
        );

        $extensao = mb_strtolower(
            trim($extensao)
        );

        if (
            $extensao !== ''
            && !str_ends_with(
                mb_strtolower($nome),
                '.' . $extensao
            )
        ) {
            $nome .=
                '.' . $extensao;
        }

        return mb_substr(
            $nome,
            0,
            255
        );
    }

    /**
     * Traduz os códigos de erro de upload do PHP.
     */
    private function mensagemErroUpload(
        int $codigo
    ): string {
        return match ($codigo) {
            UPLOAD_ERR_INI_SIZE,
            UPLOAD_ERR_FORM_SIZE =>
                'O arquivo ultrapassa o tamanho máximo permitido.',

            UPLOAD_ERR_PARTIAL =>
                'O arquivo foi enviado apenas parcialmente.',

            UPLOAD_ERR_NO_FILE =>
                'Selecione um arquivo para anexar.',

            UPLOAD_ERR_NO_TMP_DIR =>
                'O servidor está sem diretório temporário para uploads.',

            UPLOAD_ERR_CANT_WRITE =>
                'O servidor não conseguiu gravar o arquivo temporário.',

            UPLOAD_ERR_EXTENSION =>
                'Uma extensão do servidor bloqueou o upload.',

            default =>
                'Ocorreu uma falha durante o envio do arquivo.',
        };
    }

    /**
     * Valida uma data no formato YYYY-MM-DD.
     */
    private function dataValida(
        string $data
    ): bool {
        if ($data === '') {
            return false;
        }

        $dataConvertida =
            DateTimeImmutable::createFromFormat(
                '!Y-m-d',
                $data
            );

        return $dataConvertida
            instanceof DateTimeImmutable
            && $dataConvertida->format(
                'Y-m-d'
            ) === $data;
    }

    /**
     * Converte formatos booleanos do PostgreSQL
     * e do formulário.
     */
    private function valorBooleano(
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
    }

    /**
     * Normaliza o filtro da listagem.
     */
    private function normalizarSituacao(
        string $situacao
    ): string {
        $situacao = strtoupper(
            trim($situacao)
        );

        return in_array(
            $situacao,
            [
                'TODOS',
                'ATIVOS',
                'INATIVOS',
            ],
            true
        )
            ? $situacao
            : 'TODOS';
    }

    /**
     * Formata bytes para mensagens amigáveis.
     */
    private function formatarBytes(
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
    }

    /**
     * Padroniza respostas de falha do service.
     */
    private function falha(
        string $mensagem,
        array $erros = [],
        array $dados = []
    ): array {
        return [
            'sucesso' => false,
            'mensagem' => $mensagem,
            'erros' => $erros,
            'dados' => $dados,
        ];
    }
}

/*
Esse service agora protege o fluxo com:

armazenamento fora de public;
validação por is_uploaded_file();
movimentação por move_uploaded_file();
tamanho obtido do arquivo real;
MIME detectado por finfo;
confirmação adicional da estrutura PDF, JPG ou PNG;
nome físico aleatório com random_bytes(32);
prevenção de path traversal;
conferência de que o caminho permanece dentro do storage;
hash SHA-256 no cadastro e no download;
remoção do arquivo caso o banco rejeite o registro;
isolamento por organização e colaborador;
bloqueio de arquivos duplicados;
download somente de documentos ativos;
exclusão lógica, preservando o arquivo para recuperação administrativa.

*/