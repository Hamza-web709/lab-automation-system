(function () {
    const initIcons = () => {
        if (window.lucide) {
            window.lucide.createIcons();
        }
    };

    const initSidebar = () => {
        document.querySelectorAll('[data-sidebar-toggle]').forEach((button) => {
            button.addEventListener('click', () => document.body.classList.add('sidebar-open'));
        });

        document.querySelectorAll('[data-sidebar-close]').forEach((button) => {
            button.addEventListener('click', () => document.body.classList.remove('sidebar-open'));
        });
    };

    const initFlash = () => {
        document.querySelectorAll('[data-dismiss-flash]').forEach((button) => {
            button.addEventListener('click', () => {
                const flash = button.closest('.flash');
                if (!flash) return;
                if (window.gsap) {
                    gsap.to(flash, { opacity: 0, y: -8, duration: 0.2, onComplete: () => flash.remove() });
                } else {
                    flash.remove();
                }
            });
        });
    };

    const initLoadingButtons = () => {
        document.querySelectorAll('form').forEach((form) => {
            form.addEventListener('submit', () => {
                const submitter = form.querySelector('[type="submit"]');
                if (submitter) {
                    submitter.classList.add('is-loading');
                    submitter.setAttribute('disabled', 'disabled');
                }
            });
        });
    };

    const initLottie = () => {
        if (!window.lottie) return;
        document.querySelectorAll('[data-lottie]').forEach((container) => {
            window.lottie.loadAnimation({
                container,
                renderer: 'svg',
                loop: container.dataset.lottieLoop !== 'false',
                autoplay: true,
                path: container.dataset.lottie,
            });
        });
    };

    const initThemeToggle = () => {
        const toggles = document.querySelectorAll('[data-theme-toggle]');
        const labels = document.querySelectorAll('[data-theme-label]');
        if (!toggles.length) return;

        const getStoredTheme = () => {
            try {
                return localStorage.getItem('labflow-theme') === 'dark' ? 'dark' : 'light';
            } catch (error) {
                return 'light';
            }
        };

        const setTheme = (theme) => {
            const isDark = theme === 'dark';
            document.documentElement.dataset.theme = isDark ? 'dark' : 'light';
            document.body.classList.toggle('theme-is-dark', isDark);
            toggles.forEach((toggle) => {
                toggle.setAttribute('aria-pressed', isDark ? 'true' : 'false');
                toggle.setAttribute('aria-label', isDark ? 'Switch to light theme' : 'Switch to dark theme');
            });
            labels.forEach((label) => {
                label.textContent = isDark ? 'Dark' : 'Light';
            });
        };

        setTheme(getStoredTheme());

        toggles.forEach((toggle) => {
            toggle.addEventListener('click', () => {
                const nextTheme = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
                try {
                    localStorage.setItem('labflow-theme', nextTheme);
                } catch (error) {
                    // Theme preference is nice-to-have; the UI still switches for this page view.
                }
                setTheme(nextTheme);
            });
        });
    };

    const initUserDropdown = () => {
        const dropdowns = document.querySelectorAll('.user-dropdown');
        if (!dropdowns.length) return;

        const closeDropdown = (dropdown) => {
            const toggle = dropdown.querySelector('.user-dropdown-toggle');
            const menu = dropdown.querySelector('.user-dropdown-menu');
            dropdown.classList.remove('show');
            if (menu) menu.classList.remove('show');
            if (toggle) toggle.setAttribute('aria-expanded', 'false');
        };

        const closeAllDropdowns = (except = null) => {
            dropdowns.forEach((dropdown) => {
                if (dropdown !== except) closeDropdown(dropdown);
            });
        };

        dropdowns.forEach((dropdown) => {
            const toggle = dropdown.querySelector('.user-dropdown-toggle');
            const menu = dropdown.querySelector('.user-dropdown-menu');
            if (!toggle || !menu) return;

            toggle.addEventListener('click', (event) => {
                event.stopPropagation();
                const willOpen = !dropdown.classList.contains('show');
                closeAllDropdowns(dropdown);
                dropdown.classList.toggle('show', willOpen);
                menu.classList.toggle('show', willOpen);
                toggle.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
            });

            menu.addEventListener('click', (event) => {
                event.stopPropagation();
            });
        });

        document.addEventListener('click', () => closeAllDropdowns());
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeAllDropdowns();
            }
        });
    };

    const boot = () => {
        initThemeToggle();
        initUserDropdown();
        initIcons();
        initSidebar();
        initFlash();
        initLoadingButtons();
        initLottie();
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
