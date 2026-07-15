'use strict';

document.addEventListener('DOMContentLoaded', function () {
    const passwordInput = document.getElementById('senha');
    const passwordToggle = document.getElementById('passwordToggle');

    if (!passwordInput || !passwordToggle) {
        return;
    }

    passwordToggle.addEventListener('click', function () {
        const senhaEstaOculta =
            passwordInput.type === 'password';

        passwordInput.type =
            senhaEstaOculta ? 'text' : 'password';

        passwordToggle.textContent =
            senhaEstaOculta
                ? 'Ocultar senha'
                : 'Mostrar senha';

        passwordToggle.setAttribute(
            'aria-label',
            senhaEstaOculta
                ? 'Ocultar senha'
                : 'Mostrar senha'
        );

        passwordInput.focus();
    });
});