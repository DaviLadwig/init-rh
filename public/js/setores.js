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

    const phoneFields = document.querySelectorAll(
        '[data-phone]'
    );

    phoneFields.forEach(function (field) {
        field.addEventListener('input', function () {
            let value = field.value.replace(/\D/g, '');

            value = value.slice(0, 11);

            if (value.length <= 10) {
                value = value.replace(
                    /^(\d{0,2})(\d{0,4})(\d{0,4})$/,
                    function (_, area, first, last) {
                        let result = '';

                        if (area) {
                            result += '(' + area;

                            if (area.length === 2) {
                                result += ') ';
                            }
                        }

                        if (first) {
                            result += first;
                        }

                        if (last) {
                            result += '-' + last;
                        }

                        return result;
                    }
                );
            } else {
                value = value.replace(
                    /^(\d{2})(\d{5})(\d{4})$/,
                    '($1) $2-$3'
                );
            }

            field.value = value;
        });
    });

    const statusForms = document.querySelectorAll(
        '[data-sector-status-form]'
    );

    statusForms.forEach(function (form) {
        form.addEventListener('submit', function (event) {
            const sectorName =
                form.dataset.sectorName || 'este setor';

            const sectorActive =
                form.dataset.sectorActive === 'true';

            const action = sectorActive
                ? 'desativar'
                : 'ativar';

            const confirmed = window.confirm(
                'Deseja realmente '
                + action
                + ' o setor "'
                + sectorName
                + '"?'
            );

            if (!confirmed) {
                event.preventDefault();
            }
        });
    });
});