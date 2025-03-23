import './bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */

// import './styles/app.scss';
// import './styles/_variables.scss';

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.flash-message__close').forEach((btn) => {
        btn.addEventListener('click', () => {
            const flash = btn.closest('.flash-message');
            flash.classList.add('is-hidden');
            setTimeout(() => flash.remove(), 300); // дождаться анимации
        });
    });
});
