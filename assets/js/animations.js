(function () {
    document.addEventListener('DOMContentLoaded', () => {
        if (!window.gsap) return;
        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        gsap.from('[data-animate="fade-up"]', {
            opacity: 0,
            y: 18,
            duration: 0.55,
            stagger: 0.06,
            ease: 'power2.out',
        });

        gsap.from('.nav-link', {
            opacity: 0,
            x: -12,
            duration: 0.42,
            stagger: 0.035,
            ease: 'power2.out',
        });

        if (window.ScrollTrigger) {
            gsap.registerPlugin(ScrollTrigger);
            gsap.utils.toArray('[data-scroll-reveal]').forEach((node) => {
                gsap.from(node, {
                    opacity: 0,
                    y: 34,
                    rotateX: 5,
                    duration: 0.78,
                    ease: 'power3.out',
                    scrollTrigger: {
                        trigger: node,
                        start: 'top 84%',
                    },
                });
            });
        }

        document.querySelectorAll('.btn, .quick-action, .stat-card').forEach((node) => {
            node.addEventListener('mouseenter', () => gsap.to(node, { y: -2, duration: 0.16 }));
            node.addEventListener('mouseleave', () => gsap.to(node, { y: 0, duration: 0.16 }));
        });

        const initLandingInteractions = () => {
            if (!document.body.classList.contains('landing-body') || prefersReducedMotion) return;

            const progress = document.querySelector('.scroll-progress');
            if (progress) {
                const updateProgress = () => {
                    const max = document.documentElement.scrollHeight - window.innerHeight;
                    const value = max > 0 ? window.scrollY / max : 0;
                    gsap.to(progress, { scaleX: value, duration: 0.12, ease: 'none' });
                };
                updateProgress();
                window.addEventListener('scroll', updateProgress, { passive: true });
                window.addEventListener('resize', updateProgress);
            }

            const ring = document.querySelector('.landing-cursor-ring');
            const dot = document.querySelector('.landing-cursor-dot');
            if (ring && dot && window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
                const ringX = gsap.quickTo(ring, 'x', { duration: 0.28, ease: 'power3.out' });
                const ringY = gsap.quickTo(ring, 'y', { duration: 0.28, ease: 'power3.out' });
                const dotX = gsap.quickTo(dot, 'x', { duration: 0.08, ease: 'power2.out' });
                const dotY = gsap.quickTo(dot, 'y', { duration: 0.08, ease: 'power2.out' });

                window.addEventListener('pointermove', (event) => {
                    document.body.classList.add('cursor-active');
                    ringX(event.clientX);
                    ringY(event.clientY);
                    dotX(event.clientX);
                    dotY(event.clientY);
                }, { passive: true });

                document.querySelectorAll('a, button, [data-tilt], [data-magnetic]').forEach((node) => {
                    node.addEventListener('mouseenter', () => document.body.classList.add('cursor-hover'));
                    node.addEventListener('mouseleave', () => document.body.classList.remove('cursor-hover'));
                });
            }

            document.querySelectorAll('[data-magnetic]').forEach((node) => {
                node.addEventListener('mousemove', (event) => {
                    const rect = node.getBoundingClientRect();
                    const x = event.clientX - rect.left - rect.width / 2;
                    const y = event.clientY - rect.top - rect.height / 2;
                    gsap.to(node, { x: x * 0.18, y: y * 0.26, duration: 0.28, ease: 'power3.out' });
                });
                node.addEventListener('mouseleave', () => {
                    gsap.to(node, { x: 0, y: 0, duration: 0.34, ease: 'elastic.out(1, 0.45)' });
                });
            });

            document.querySelectorAll('[data-tilt]').forEach((node) => {
                node.addEventListener('mousemove', (event) => {
                    const rect = node.getBoundingClientRect();
                    const x = (event.clientX - rect.left) / rect.width - 0.5;
                    const y = (event.clientY - rect.top) / rect.height - 0.5;
                    gsap.to(node, {
                        rotateY: x * 8,
                        rotateX: y * -7,
                        y: -6,
                        duration: 0.28,
                        ease: 'power2.out',
                    });
                });
                node.addEventListener('mouseleave', () => {
                    gsap.to(node, {
                        rotateY: 0,
                        rotateX: 0,
                        y: 0,
                        duration: 0.34,
                        ease: 'power2.out',
                    });
                });
            });

            gsap.from('.hero-copy h1', {
                opacity: 0,
                y: 26,
                duration: 0.82,
                delay: 0.08,
                ease: 'power3.out',
            });

            gsap.from('.lab-console', {
                opacity: 0,
                y: 28,
                rotate: -3,
                duration: 0.9,
                delay: 0.18,
                ease: 'power3.out',
            });

            gsap.to('.lab-console', {
                y: -10,
                duration: 3.6,
                repeat: -1,
                yoyo: true,
                ease: 'sine.inOut',
            });

            if (window.ScrollTrigger) {
                gsap.utils.toArray('.landing-section').forEach((section) => {
                    gsap.from(section.querySelectorAll('.section-title, .solution-showcase, .feature-card, .problem-card, .workflow-step'), {
                        opacity: 0,
                        y: 22,
                        duration: 0.55,
                        stagger: 0.05,
                        ease: 'power2.out',
                        scrollTrigger: {
                            trigger: section,
                            start: 'top 78%',
                        },
                    });
                });
            }
        };

        initLandingInteractions();
    });
})();
