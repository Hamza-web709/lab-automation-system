(function () {
    document.addEventListener('DOMContentLoaded', () => {
        if (!window.gsap) return;

        gsap.from('.stat-card', {
            opacity: 0,
            y: 18,
            duration: 0.48,
            stagger: 0.055,
            ease: 'power2.out',
        });

        document.querySelectorAll('[data-count]').forEach((node) => {
            const target = Number(node.dataset.count || 0);
            const counter = { value: 0 };
            gsap.to(counter, {
                value: target,
                duration: 0.9,
                ease: 'power1.out',
                onUpdate: () => {
                    node.textContent = Math.round(counter.value).toLocaleString();
                },
            });
        });
    });
})();
