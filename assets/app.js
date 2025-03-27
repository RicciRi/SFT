import './bootstrap.js';

document.addEventListener('turbo:load', () => {
    document.querySelectorAll('.flash-message__close').forEach((btn) => {
        btn.addEventListener('click', () => {
            console.log('clicked')
            const flash = btn.closest('.flash-message');
            flash.classList.add('is-hidden');
            setTimeout(() => flash.remove(), 300);
        });
    });
});
