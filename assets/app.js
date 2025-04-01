import './bootstrap.js';
import './js/home.js';
import './js/send.js';

import { initSafeDial } from './js/components/safeDial.js';
import { initMatrix } from "./js/components/matrix.js";
import { initRegister } from "./js/register.js";

document.addEventListener('turbo:load', () => {
    document.querySelectorAll('[data-component="safe_dial"]').forEach(initSafeDial);
    document.querySelectorAll('[data-component="matrix"]').forEach(initMatrix);
    document.querySelectorAll('[data-component="register"]').forEach(initRegister);




    document.querySelectorAll('.flash-message__close').forEach((btn) => {
        btn.addEventListener('click', () => {
            console.log('clicked')
            const flash = btn.closest('.flash-message');
            flash.classList.add('is-hidden');
            setTimeout(() => flash.remove(), 300);
        });
    });
});
