'use strict';

document.addEventListener('DOMContentLoaded', () => {
    configurarConfirmacoes();
    configurarFormularioDeUpload();
});

/**
 * Confirma ações como desativar e excluir documentos.
 */
function configurarConfirmacoes() {
    const formularios = document.querySelectorAll(
        '[data-document-confirm-form]'
    );

    formularios.forEach((formulario) => {
        formulario.addEventListener(
            'submit',
            (evento) => {
                const mensagem =
                    formulario.dataset.confirmMessage
                    || 'Confirma esta operação?';

                if (!window.confirm(mensagem)) {
                    evento.preventDefault();
                }
            }
        );
    });
}

/**
 * Controla a tela de upload de documentos.
 */
function configurarFormularioDeUpload() {
    const formulario = document.querySelector(
        '[data-document-upload-form]'
    );

    if (
        !(
            formulario
            instanceof HTMLFormElement
        )
    ) {
        return;
    }

    const tipoDocumento =
        formulario.querySelector(
            '[data-document-type]'
        );

    const campoValidade =
        formulario.querySelector(
            '[data-document-validity]'
        );

    const marcadorValidade =
        formulario.querySelector(
            '[data-validity-required-marker]'
        );

    const textoValidade =
        formulario.querySelector(
            '[data-validity-hint]'
        );

    const dataEmissao =
        formulario.querySelector(
            '#data_emissao'
        );

    const arquivo =
        formulario.querySelector(
            '[data-document-file]'
        );

    const areaUpload =
        formulario.querySelector(
            '[data-document-dropzone]'
        );

    const informacoesArquivo =
        formulario.querySelector(
            '[data-document-file-info]'
        );

    const nomeArquivo =
        formulario.querySelector(
            '[data-document-file-name]'
        );

    const tamanhoArquivo =
        formulario.querySelector(
            '[data-document-file-size]'
        );

    const botaoEnviar =
        formulario.querySelector(
            '[data-document-submit]'
        );

    const limiteBytes = Number.parseInt(
        formulario.dataset.maxBytes
        || '10485760',
        10
    );

    const tiposPermitidos = new Set([
        'application/pdf',
        'image/jpeg',
        'image/png',
    ]);

    const extensoesPermitidas = new Set([
        'pdf',
        'jpg',
        'jpeg',
        'png',
    ]);

    /**
     * Confere se a validade é anterior à emissão.
     */
    const validarDatas = () => {
        if (
            !(
                campoValidade
                instanceof HTMLInputElement
            )
        ) {
            return;
        }

        const emissao =
            dataEmissao
            instanceof HTMLInputElement
                ? dataEmissao.value
                : '';

        campoValidade.min = emissao;

        if (
            emissao !== ''
            && campoValidade.value !== ''
            && campoValidade.value < emissao
        ) {
            campoValidade.setCustomValidity(
                'A data de validade não pode ser anterior à data de emissão.'
            );

            return;
        }

        campoValidade.setCustomValidity('');
    };

    /**
     * Torna a validade obrigatória conforme
     * o tipo de documento selecionado.
     */
    const atualizarObrigatoriedadeDaValidade =
        () => {
            if (
                !(
                    tipoDocumento
                    instanceof HTMLSelectElement
                )
                || !(
                    campoValidade
                    instanceof HTMLInputElement
                )
            ) {
                return;
            }

            const opcaoSelecionada =
                tipoDocumento.options[
                    tipoDocumento.selectedIndex
                ];

            const exigeValidade =
                opcaoSelecionada
                    ?.dataset
                    .requiresValidity
                === '1';

            campoValidade.required =
                exigeValidade;

            if (
                marcadorValidade
                instanceof HTMLElement
            ) {
                marcadorValidade.hidden =
                    !exigeValidade;
            }

            if (
                textoValidade
                instanceof HTMLElement
            ) {
                textoValidade.textContent =
                    exigeValidade
                        ? 'Obrigatória para o tipo selecionado.'
                        : 'Informe somente quando o documento possuir validade.';
            }

            validarDatas();
        };

    /**
     * Obtém a extensão do arquivo.
     */
    const obterExtensao = (nome) => {
        const partes = nome
            .toLowerCase()
            .split('.');

        return partes.length > 1
            ? partes.pop()
            : '';
    };

    /**
     * Exibe nome e tamanho do arquivo selecionado.
     */
    const mostrarArquivo = (
        arquivoSelecionado
    ) => {
        if (
            !(
                informacoesArquivo
                instanceof HTMLElement
            )
            || !(
                nomeArquivo
                instanceof HTMLElement
            )
            || !(
                tamanhoArquivo
                instanceof HTMLElement
            )
        ) {
            return;
        }

        if (
            !(
                arquivoSelecionado
                instanceof File
            )
        ) {
            informacoesArquivo.hidden = true;

            informacoesArquivo.classList.remove(
                'document-upload__selected--error'
            );

            return;
        }

        nomeArquivo.textContent =
            arquivoSelecionado.name;

        tamanhoArquivo.textContent =
            formatarBytes(
                arquivoSelecionado.size
            );

        informacoesArquivo.hidden = false;
    };

    /**
     * Aplica o estado visual de arquivo inválido.
     */
    const marcarArquivoInvalido = () => {
        if (
            areaUpload
            instanceof HTMLElement
        ) {
            areaUpload.classList.add(
                'document-upload--invalid'
            );
        }

        if (
            informacoesArquivo
            instanceof HTMLElement
        ) {
            informacoesArquivo.classList.add(
                'document-upload__selected--error'
            );
        }
    };

    /**
     * Valida extensão, MIME e tamanho.
     *
     * Esta validação é apenas uma ajuda visual.
     * O PHP continua sendo responsável pela
     * validação definitiva.
     */
    const validarArquivo = () => {
        if (
            !(
                arquivo
                instanceof HTMLInputElement
            )
        ) {
            return true;
        }

        const arquivoSelecionado =
            arquivo.files?.[0];

        arquivo.setCustomValidity('');

        if (
            areaUpload
            instanceof HTMLElement
        ) {
            areaUpload.classList.remove(
                'document-upload--invalid'
            );
        }

        if (
            informacoesArquivo
            instanceof HTMLElement
        ) {
            informacoesArquivo.classList.remove(
                'document-upload__selected--error'
            );
        }

        mostrarArquivo(
            arquivoSelecionado
        );

        if (
            !(
                arquivoSelecionado
                instanceof File
            )
        ) {
            return true;
        }

        const extensao = obterExtensao(
            arquivoSelecionado.name
        );

        const mimeAceito =
            arquivoSelecionado.type === ''
            || tiposPermitidos.has(
                arquivoSelecionado.type
            );

        if (
            !extensoesPermitidas.has(
                extensao
            )
            || !mimeAceito
        ) {
            arquivo.setCustomValidity(
                'Selecione somente um arquivo PDF, JPG, JPEG ou PNG.'
            );

            marcarArquivoInvalido();

            return false;
        }

        if (
            Number.isFinite(
                limiteBytes
            )
            && limiteBytes > 0
            && arquivoSelecionado.size
                > limiteBytes
        ) {
            arquivo.setCustomValidity(
                'O arquivo ultrapassa o limite de '
                + formatarBytes(
                    limiteBytes
                )
                + '.'
            );

            marcarArquivoInvalido();

            return false;
        }

        if (
            arquivoSelecionado.size <= 0
        ) {
            arquivo.setCustomValidity(
                'O arquivo selecionado está vazio.'
            );

            marcarArquivoInvalido();

            return false;
        }

        return true;
    };

    if (
        tipoDocumento
        instanceof HTMLSelectElement
    ) {
        tipoDocumento.addEventListener(
            'change',
            atualizarObrigatoriedadeDaValidade
        );
    }

    if (
        dataEmissao
        instanceof HTMLInputElement
    ) {
        dataEmissao.addEventListener(
            'change',
            validarDatas
        );
    }

    if (
        campoValidade
        instanceof HTMLInputElement
    ) {
        campoValidade.addEventListener(
            'change',
            validarDatas
        );
    }

    if (
        arquivo
        instanceof HTMLInputElement
    ) {
        arquivo.addEventListener(
            'change',
            validarArquivo
        );
    }

    /**
     * Arrastar e soltar arquivo.
     */
    if (
        areaUpload
        instanceof HTMLElement
        && arquivo
        instanceof HTMLInputElement
    ) {
        [
            'dragenter',
            'dragover',
        ].forEach(
            (nomeEvento) => {
                areaUpload.addEventListener(
                    nomeEvento,
                    (evento) => {
                        evento.preventDefault();

                        areaUpload.classList.add(
                            'document-upload--dragging'
                        );
                    }
                );
            }
        );

        [
            'dragleave',
            'drop',
        ].forEach(
            (nomeEvento) => {
                areaUpload.addEventListener(
                    nomeEvento,
                    (evento) => {
                        evento.preventDefault();

                        areaUpload.classList.remove(
                            'document-upload--dragging'
                        );
                    }
                );
            }
        );

        areaUpload.addEventListener(
            'drop',
            (evento) => {
                const arquivos =
                    evento
                        .dataTransfer
                        ?.files;

                if (
                    !arquivos
                    || arquivos.length === 0
                ) {
                    return;
                }

                try {
                    const transferencia =
                        new DataTransfer();

                    transferencia
                        .items
                        .add(
                            arquivos[0]
                        );

                    arquivo.files =
                        transferencia.files;

                    arquivo.dispatchEvent(
                        new Event(
                            'change',
                            {
                                bubbles: true,
                            }
                        )
                    );
                } catch (erro) {
                    /*
                     * Alguns navegadores não permitem
                     * alterar o FileList por JavaScript.
                     */
                    console.warn(
                        'O navegador não permitiu adicionar o arquivo arrastado.',
                        erro
                    );
                }
            }
        );
    }

    /**
     * Validação final antes do envio.
     */
    formulario.addEventListener(
        'submit',
        (evento) => {
            atualizarObrigatoriedadeDaValidade();
            validarDatas();
            validarArquivo();

            if (
                !formulario.checkValidity()
            ) {
                evento.preventDefault();

                formulario.reportValidity();

                return;
            }

            if (
                botaoEnviar
                instanceof HTMLButtonElement
            ) {
                botaoEnviar.disabled = true;

                botaoEnviar.textContent =
                    'Enviando...';
            }
        }
    );

    atualizarObrigatoriedadeDaValidade();
    validarDatas();
    validarArquivo();
}

/**
 * Formata o tamanho do arquivo.
 */
function formatarBytes(bytes) {
    const valor = Number(bytes);

    if (
        !Number.isFinite(valor)
        || valor <= 0
    ) {
        return '0 bytes';
    }

    if (valor >= 1073741824) {
        return (
            valor / 1073741824
        ).toLocaleString(
            'pt-BR',
            {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            }
        ) + ' GB';
    }

    if (valor >= 1048576) {
        return (
            valor / 1048576
        ).toLocaleString(
            'pt-BR',
            {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            }
        ) + ' MB';
    }

    if (valor >= 1024) {
        return (
            valor / 1024
        ).toLocaleString(
            'pt-BR',
            {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            }
        ) + ' KB';
    }

    return valor + ' bytes';
}