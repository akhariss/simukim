/**
 * Theme Manager for Mahad App
 * Key: 'sahaja_theme'
 * Values: 'light' | 'dark'
 */

export function initTheme() {
    const saved = localStorage.getItem('sahaja_theme');
    const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    const initial = saved || (prefersDark ? 'dark' : 'light');

    applyTheme(initial);

    // Expose global helper for Alpine & buttons
    window.toggleTheme = function() {
        const current = document.documentElement.getAttribute('data-theme') || 'light';
        const next = current === 'dark' ? 'light' : 'dark';

        // Enable transitions only during user-initiated toggle
        document.documentElement.classList.add('theme-transitioning');
        applyTheme(next);
        setTimeout(() => {
            document.documentElement.classList.remove('theme-transitioning');
        }, 250);

        return next;
    };

    window.getCurrentTheme = function() {
        return document.documentElement.getAttribute('data-theme') || 'light';
    };
}

export function applyTheme(theme) {
    const root = document.documentElement;
    if (theme === 'dark') {
        if (root.getAttribute('data-theme') !== 'dark') root.setAttribute('data-theme', 'dark');
        if (!root.classList.contains('dark')) root.classList.add('dark');
    } else {
        if (root.getAttribute('data-theme') !== 'light') root.setAttribute('data-theme', 'light');
        if (root.classList.contains('dark')) root.classList.remove('dark');
    }

    try {
        localStorage.setItem('sahaja_theme', theme);
        document.cookie = `sahaja_theme=${theme};path=/;max-age=31536000;SameSite=Lax`;
    } catch(e) {}

    window.dispatchEvent(new CustomEvent('theme-changed', { detail: { theme } }));
}

// Auto-run on script load to guarantee theme sync
initTheme();
