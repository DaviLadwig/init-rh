'use strict';

document.addEventListener('DOMContentLoaded', function () {
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

    const statusForms = document.querySelectorAll(
        '[data-catalog-status-form]'
    );

    statusForms.forEach(function (form) {
        form.addEventListener('submit', function (event) {
            const itemName =
                form.dataset.itemName || 'este item';

            const itemType =
                form.dataset.itemType || 'item';

            const itemActive =
                form.dataset.itemActive === 'true';

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
        });
    });
});