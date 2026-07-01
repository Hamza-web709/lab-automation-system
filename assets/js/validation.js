(function () {
    const markField = (input, isValid) => {
        const field = input.closest('.field');
        if (!field) return;
        field.classList.toggle('has-error', !isValid);
    };

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('form[data-validate]').forEach((form) => {
            form.addEventListener('submit', (event) => {
                let valid = true;
                form.querySelectorAll('[required]').forEach((input) => {
                    const ok = String(input.value || '').trim().length > 0;
                    markField(input, ok);
                    valid = valid && ok;
                });

                if (!valid) {
                    event.preventDefault();
                    const firstError = form.querySelector('.has-error input, .has-error select, .has-error textarea');
                    if (firstError) firstError.focus();
                }
            });
        });

        document.querySelectorAll('[required]').forEach((input) => {
            input.addEventListener('input', () => markField(input, String(input.value || '').trim().length > 0));
            input.addEventListener('change', () => markField(input, String(input.value || '').trim().length > 0));
        });
    });
})();
