const root = document.documentElement;
const colorScheme = document.querySelector('meta[name="color-scheme"]');
const toggle = document.querySelector('[data-theme-toggle]');
const systemTheme = window.matchMedia('(prefers-color-scheme: dark)');
const storageKey = 'devstudio-color-scheme';

if (toggle && colorScheme) {
    const resolvedTheme = () => root.dataset.theme === 'system'
        ? (systemTheme.matches ? 'dark' : 'light')
        : root.dataset.theme;

    const updateLabel = () => {
        toggle.setAttribute('aria-label', `Ativar tema ${resolvedTheme() === 'dark' ? 'claro' : 'escuro'}`);
    };

    const applyTheme = (theme) => {
        root.dataset.theme = theme;
        colorScheme.content = theme === 'system' ? 'light dark' : theme;
        updateLabel();
    };

    toggle.addEventListener('click', () => {
        const nextTheme = resolvedTheme() === 'dark' ? 'light' : 'dark';
        const theme = nextTheme === (systemTheme.matches ? 'dark' : 'light') ? 'system' : nextTheme;

        applyTheme(theme);

        try {
            if (theme === 'system') {
                localStorage.removeItem(storageKey);
            } else {
                localStorage.setItem(storageKey, theme);
            }
        } catch {}
    });

    window.addEventListener('storage', (event) => {
        if (event.key !== storageKey && event.key !== null) return;
        applyTheme(event.newValue === 'light' || event.newValue === 'dark' ? event.newValue : 'system');
    });

    systemTheme.addEventListener('change', updateLabel);
    toggle.hidden = false;
    updateLabel();
}

if ('IntersectionObserver' in window && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    const revealSections = document.querySelectorAll([
        '.public-layout main .section-block',
        '.public-layout main .listing-hero',
        '.public-layout main .public-listing-grid > *',
        '.public-layout main .listing-empty-box',
        '.public-layout main .listing-pagination',
        '.public-layout main .public-detail > header',
        '.public-layout main .public-detail > div',
        '.public-layout main .public-detail > section',
        '.public-layout main .contact-layout > *',
        '.public-layout main > header',
        '.public-layout main > header + div > .ui-card',
        '.public-layout main > header + div > div > .ui-card',
        '.public-layout main > header + div > div > .ui-cta-banner',
    ].join(', '));
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.dataset.reveal = 'visible';
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

    revealSections.forEach((section) => {
        if (section.getBoundingClientRect().top >= window.innerHeight) {
            section.dataset.reveal = 'hidden';
            observer.observe(section);
        }
    });
}
