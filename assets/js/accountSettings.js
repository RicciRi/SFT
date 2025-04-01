export function initAccountSettings() {
    const form = document.querySelector('form');
    const password = document.getElementById('account_settings_plainPassword_first');
    const confirmPassword = document.getElementById('account_settings_plainPassword_second');
    const errorContainer = document.getElementById('password-mismatch-error');

    if (!form || !password || !confirmPassword || !errorContainer) {
        return;
    }

    form.addEventListener('submit', function (e) {
        const pass = password.value.trim();
        const confirm = confirmPassword.value.trim();

        errorContainer.innerHTML = '';

        if (pass || confirm) {
            // Проверка длины
            if (pass.length < 8) {
                e.preventDefault();

                const errorMessage = document.createElement('div');
                errorMessage.classList.add('alert', 'alert-danger', 'mt-2');
                errorMessage.innerText = 'Password must be at least 8 characters.';
                errorContainer.appendChild(errorMessage);

                return false;
            }

            // Проверка совпадения
            if (pass !== confirm) {
                e.preventDefault();

                const errorMessage = document.createElement('div');
                errorMessage.classList.add('alert', 'alert-danger', 'mt-2');
                errorMessage.innerText = 'Passwords do not match!';
                errorContainer.appendChild(errorMessage);

                return false;
            }
        }
    });
}
