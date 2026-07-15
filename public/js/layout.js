//main.php 

'use strict';

document.addEventListener('DOMContentLoaded', function () {
    const sidebar = document.getElementById('appSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const menuToggle = document.getElementById('menuToggle');
    const sidebarClose = document.getElementById('sidebarClose');

    if (!sidebar || !overlay || !menuToggle) {
        return;
    }

    function abrirMenu() {
        sidebar.classList.add('is-open');
        overlay.classList.add('is-visible');

        menuToggle.setAttribute(
            'aria-expanded',
            'true'
        );

        document.body.classList.add(
            'sidebar-mobile-open'
        );
    }

    function fecharMenu() {
        sidebar.classList.remove('is-open');
        overlay.classList.remove('is-visible');

        menuToggle.setAttribute(
            'aria-expanded',
            'false'
        );

        document.body.classList.remove(
            'sidebar-mobile-open'
        );
    }

    menuToggle.addEventListener(
        'click',
        abrirMenu
    );

    overlay.addEventListener(
        'click',
        fecharMenu
    );

    if (sidebarClose) {
        sidebarClose.addEventListener(
            'click',
            fecharMenu
        );
    }

    document.addEventListener(
        'keydown',
        function (event) {
            if (event.key === 'Escape') {
                fecharMenu();
            }
        }
    );

    window.addEventListener(
        'resize',
        function () {
            if (window.innerWidth > 980) {
                fecharMenu();
            }
        }
    );
});