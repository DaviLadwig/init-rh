'use strict';

document.addEventListener('DOMContentLoaded', function () {
    /*
    |--------------------------------------------------------------------------
    | Utilitários
    |--------------------------------------------------------------------------
    */

    /**
     * Mantém somente números.
     */
    function somenteNumeros(valor) {
        return String(valor || '').replace(/\D+/g, '');
    }

    /**
     * Formata o CPF no padrão 000.000.000-00.
     */
    function formatarCpf(valor) {
        const numeros = somenteNumeros(valor).slice(0, 11);

        if (numeros.length <= 3) {
            return numeros;
        }

        if (numeros.length <= 6) {
            return numeros.slice(0, 3)
                + '.'
                + numeros.slice(3);
        }

        if (numeros.length <= 9) {
            return numeros.slice(0, 3)
                + '.'
                + numeros.slice(3, 6)
                + '.'
                + numeros.slice(6);
        }

        return numeros.slice(0, 3)
            + '.'
            + numeros.slice(3, 6)
            + '.'
            + numeros.slice(6, 9)
            + '-'
            + numeros.slice(9, 11);
    }

    /**
     * Formata telefone fixo ou celular com DDD.
     */
    function formatarTelefone(valor) {
        const numeros = somenteNumeros(valor).slice(0, 11);

        if (numeros.length === 0) {
            return '';
        }

        if (numeros.length <= 2) {
            return '(' + numeros;
        }

        if (numeros.length <= 6) {
            return '('
                + numeros.slice(0, 2)
                + ') '
                + numeros.slice(2);
        }

        if (numeros.length <= 10) {
            return '('
                + numeros.slice(0, 2)
                + ') '
                + numeros.slice(2, 6)
                + '-'
                + numeros.slice(6, 10);
        }

        return '('
            + numeros.slice(0, 2)
            + ') '
            + numeros.slice(2, 7)
            + '-'
            + numeros.slice(7, 11);
    }

    /*
    |--------------------------------------------------------------------------
    | Máscara de CPF
    |--------------------------------------------------------------------------
    */

    const camposCpf = document.querySelectorAll(
        '[data-cpf]'
    );

    camposCpf.forEach(function (campo) {
        campo.value = formatarCpf(campo.value);

        campo.addEventListener(
            'input',
            function () {
                campo.value = formatarCpf(
                    campo.value
                );

                campo.setCustomValidity('');
            }
        );

        campo.addEventListener(
            'paste',
            function () {
                window.setTimeout(
                    function () {
                        campo.value = formatarCpf(
                            campo.value
                        );
                    },
                    0
                );
            }
        );
    });

    /*
    |--------------------------------------------------------------------------
    | Máscara de telefone
    |--------------------------------------------------------------------------
    */

    const camposTelefone = document.querySelectorAll(
        '[data-phone]'
    );

    camposTelefone.forEach(function (campo) {
        campo.value = formatarTelefone(
            campo.value
        );

        campo.addEventListener(
            'input',
            function () {
                campo.value = formatarTelefone(
                    campo.value
                );

                campo.setCustomValidity('');
            }
        );

        campo.addEventListener(
            'paste',
            function () {
                window.setTimeout(
                    function () {
                        campo.value = formatarTelefone(
                            campo.value
                        );
                    },
                    0
                );
            }
        );
    });

    /*
    |--------------------------------------------------------------------------
    | Regra dinâmica da data final do vínculo
    |--------------------------------------------------------------------------
    */

    const formulariosColaborador =
        document.querySelectorAll(
            '[data-employee-form]'
        );

    formulariosColaborador.forEach(
        function (formulario) {
            const campoTipoVinculo =
                formulario.querySelector(
                    '[data-contract-type]'
                );

            const campoDataAdmissao =
                formulario.querySelector(
                    '#data_admissao'
                );

            const campoDataFim =
                formulario.querySelector(
                    '[data-contract-end-date]'
                );

            const marcadorObrigatorio =
                formulario.querySelector(
                    '[data-end-date-required]'
                );

            const textoAjudaDataFim =
                formulario.querySelector(
                    '[data-end-date-help]'
                );

            /**
             * Retorna a opção atualmente selecionada
             * no tipo de vínculo.
             */
            function obterOpcaoSelecionada() {
                if (!campoTipoVinculo) {
                    return null;
                }

                return campoTipoVinculo.options[
                    campoTipoVinculo.selectedIndex
                ] || null;
            }

            /**
             * Verifica se o tipo de vínculo selecionado
             * exige uma data final.
             */
            function vinculoExigeDataFim() {
                const opcaoSelecionada =
                    obterOpcaoSelecionada();

                return opcaoSelecionada !== null
                    && opcaoSelecionada.dataset
                        .requiresEndDate === 'true';
            }

            /**
             * Atualiza a obrigatoriedade do campo
             * de data final.
             */
            function atualizarObrigatoriedadeDataFim() {
                if (!campoDataFim) {
                    return;
                }

                const obrigatoria =
                    vinculoExigeDataFim();

                campoDataFim.required =
                    obrigatoria;

                campoDataFim.setAttribute(
                    'aria-required',
                    obrigatoria
                        ? 'true'
                        : 'false'
                );

                if (marcadorObrigatorio) {
                    marcadorObrigatorio.hidden =
                        !obrigatoria;
                }

                if (textoAjudaDataFim) {
                    textoAjudaDataFim.textContent =
                        obrigatoria
                            ? 'Obrigatória para o tipo de vínculo selecionado.'
                            : 'Opcional para o tipo de vínculo selecionado.';
                }

                validarPeriodoVinculo();
            }

            /**
             * Mantém a data final igual ou posterior
             * à data de admissão.
             */
            function atualizarDataMinima() {
                if (
                    !campoDataFim
                    || !campoDataAdmissao
                ) {
                    return;
                }

                campoDataFim.min =
                    campoDataAdmissao.value || '';

                validarPeriodoVinculo();
            }

            /**
             * Valida o período do vínculo.
             */
            function validarPeriodoVinculo() {
                if (!campoDataFim) {
                    return true;
                }

                campoDataFim.setCustomValidity('');

                if (
                    campoDataFim.required
                    && campoDataFim.value === ''
                ) {
                    campoDataFim.setCustomValidity(
                        'Informe a data final deste vínculo.'
                    );

                    return false;
                }

                if (
                    campoDataAdmissao
                    && campoDataAdmissao.value !== ''
                    && campoDataFim.value !== ''
                    && campoDataFim.value
                        < campoDataAdmissao.value
                ) {
                    campoDataFim.setCustomValidity(
                        'A data final não pode ser anterior à data de admissão.'
                    );

                    return false;
                }

                return true;
            }

            if (campoTipoVinculo) {
                campoTipoVinculo.addEventListener(
                    'change',
                    atualizarObrigatoriedadeDataFim
                );
            }

            if (campoDataAdmissao) {
                campoDataAdmissao.addEventListener(
                    'change',
                    atualizarDataMinima
                );

                campoDataAdmissao.addEventListener(
                    'input',
                    atualizarDataMinima
                );
            }

            if (campoDataFim) {
                campoDataFim.addEventListener(
                    'change',
                    validarPeriodoVinculo
                );

                campoDataFim.addEventListener(
                    'input',
                    validarPeriodoVinculo
                );
            }

            /*
             * Executa ao abrir a tela de cadastro
             * ou edição.
             */
            atualizarObrigatoriedadeDataFim();
            atualizarDataMinima();

            /*
             * Verifica os campos antes do envio.
             */
            formulario.addEventListener(
                'submit',
                function (event) {
                    atualizarObrigatoriedadeDataFim();
                    atualizarDataMinima();
                    validarPeriodoVinculo();

                    if (
                        !formulario.checkValidity()
                    ) {
                        event.preventDefault();

                        formulario.reportValidity();

                        const primeiroInvalido =
                            formulario.querySelector(
                                ':invalid'
                            );

                        if (primeiroInvalido) {
                            primeiroInvalido.focus();
                        }
                    }
                }
            );
        }
    );

    /*
    |--------------------------------------------------------------------------
    | Confirmação ao ativar ou desativar colaborador
    |--------------------------------------------------------------------------
    */

    const formulariosStatus =
        document.querySelectorAll(
            '[data-employee-status-form]'
        );

    formulariosStatus.forEach(
        function (formulario) {
            formulario.addEventListener(
                'submit',
                function (event) {
                    const nome =
                        formulario.dataset
                            .employeeName
                        || 'este colaborador';

                    const estaAtivo =
                        formulario.dataset
                            .employeeActive
                        === 'true';

                    const acao = estaAtivo
                        ? 'desativar'
                        : 'ativar';

                    const mensagem =
                        'Deseja realmente '
                        + acao
                        + ' o colaborador "'
                        + nome
                        + '"?';

                    if (
                        !window.confirm(mensagem)
                    ) {
                        event.preventDefault();
                    }
                }
            );
        }
    );
});