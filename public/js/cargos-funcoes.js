'use strict';

document.addEventListener('DOMContentLoaded', function () {
    /*
    |--------------------------------------------------------------------------
    | Campos de código em letras maiúsculas
    |--------------------------------------------------------------------------
    */

    const uppercaseFields = document.querySelectorAll(
        '[data-uppercase]'
    );

    uppercaseFields.forEach(function (field) {
        field.addEventListener('input', function () {
            field.value = field.value
                .toUpperCase()
                .replace(/\s+/g, '');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Campo de carga horária
    |--------------------------------------------------------------------------
    */

    const workloadFields = document.querySelectorAll(
        '[data-workload]'
    );

    workloadFields.forEach(function (field) {
        field.addEventListener('input', function () {
            let value = field.value;

            value = value.replace(
                /[^0-9,.]/g,
                ''
            );

            const separatorPosition = value.search(
                /[,.]/
            );

            if (separatorPosition !== -1) {
                const integerPart = value
                    .slice(0, separatorPosition)
                    .replace(/[,.]/g, '')
                    .slice(0, 3);

                const decimalPart = value
                    .slice(separatorPosition + 1)
                    .replace(/[,.]/g, '')
                    .slice(0, 2);

                value = integerPart
                    + ','
                    + decimalPart;
            } else {
                value = value
                    .replace(/[,.]/g, '')
                    .slice(0, 3);
            }

            field.value = value;
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Confirmação de ativação e desativação
    |--------------------------------------------------------------------------
    */

    const statusForms = document.querySelectorAll(
        '[data-catalog-status-form]'
    );

    statusForms.forEach(function (form) {
        form.addEventListener(
            'submit',
            function (event) {
                const itemName =
                    form.dataset.itemName
                    || 'este item';

                const itemType =
                    form.dataset.itemType
                    || 'item';

                const itemActive =
                    form.dataset.itemActive
                    === 'true';

                const action = itemActive
                    ? 'desativar'
                    : 'ativar';

                const message =
                    'Deseja realmente '
                    + action
                    + ' '
                    + itemType
                    + ' "'
                    + itemName
                    + '"?';

                if (!window.confirm(message)) {
                    event.preventDefault();
                }
            }
        );
    });
});