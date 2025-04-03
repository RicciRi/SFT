import './bootstrap.js';
import './js/home.js';
import './js/send.js';

import { initSafeDial } from './js/components/safeDial.js';
import { initMatrix } from "./js/components/matrix.js";
import { initRegister } from "./js/register.js";
import {initAccountSettings} from "./js/accountSettings.js";

document.addEventListener('turbo:load', () => {
    document.querySelectorAll('[data-component="safe_dial"]').forEach(initSafeDial);
    document.querySelectorAll('[data-component="matrix"]').forEach(initMatrix);
    document.querySelectorAll('[data-component="register"]').forEach(initRegister);
    document.querySelectorAll('[data-component="account-settings"]').forEach(initAccountSettings);
    document.querySelectorAll('.flash-message__close').forEach((btn) => {
        btn.addEventListener('click', () => {
            console.log('clicked')
            const flash = btn.closest('.flash-message');
            flash.classList.add('is-hidden');
            setTimeout(() => flash.remove(), 300);
        });
    });

    handleFlashes();
});

function handleFlashes() {
    const flashElements = document.querySelectorAll('.flash-message');

    flashElements.forEach((element) => {
        const label = element.className.match(/flash-message--(\w+)/)[1];
        const message = element.querySelector('.subtext')?.innerText.trim();

        if (message) {
            showFlash(label, message);
        }

        element.remove();
    });
}

