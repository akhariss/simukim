/**
 * Ma'had Al-Jami'ah — High-Performance Instant SPA Navigator
 * In-memory SWR caching + Hover Pre-fetching for 0ms Instant Page Transitions.
 */
let progressBar = null, progressTimer = null, activeAbortController = null, pendingNavUrl = null;
const pageCache = new Map(), CACHE_TTL = 90000;
const isAuth = p => !!p && (p.startsWith('/dashboard') || p.startsWith('/profile'));

function isSameOrigin(u) {
    if (u.origin === window.location.origin) return true;
    const isL = h => h === 'localhost' || h === '127.0.0.1';
    return isL(u.hostname) && isL(window.location.hostname) && (u.port || '') === (window.location.port || '');
}

function getProgressBar() {
    if (!progressBar) {
        progressBar = document.createElement('div');
        progressBar.id = 'spa-progress-bar';
        progressBar.className = 'fixed top-0 left-0 h-[2.5px] bg-gradient-to-r from-blue-500 via-blue-600 to-emerald-400 z-[9999] transition-all duration-150 pointer-events-none opacity-0 shadow-sm';
        progressBar.style.width = '0%';
        document.body.appendChild(progressBar);
    }
    return progressBar;
}

function startProgress() {
    const bar = getProgressBar();
    clearTimeout(progressTimer);
    bar.style.transition = 'width 150ms ease, opacity 80ms ease';
    bar.style.opacity = '1';
    bar.style.width = '45%';
    progressTimer = setTimeout(() => { bar.style.width = '80%'; }, 80);
}

function completeProgress() {
    const bar = getProgressBar();
    clearTimeout(progressTimer);
    bar.style.width = '100%';
    setTimeout(() => { bar.style.opacity = '0'; setTimeout(() => { bar.style.width = '0%'; }, 100); }, 80);
}

function shouldInterceptLink(anchor, event = {}) {
    if (!anchor?.href || event.defaultPrevented || (event.button !== undefined && event.button !== 0)) return false;
    if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return false;
    if ((anchor.target && anchor.target !== '_self') || anchor.hasAttribute('download')) return false;

    const href = anchor.getAttribute('href');
    if (!href || href.startsWith('#') || href.startsWith('javascript:') || anchor.hasAttribute('data-no-spa') || anchor.hasAttribute('data-confirm')) return false;

    let url;
    try { url = new URL(anchor.href, window.location.origin); } catch { return false; }
    if (!isSameOrigin(url)) return false;

    const bypass = ['/login', '/logout', '/password', '/export', '/template', '/secure-file', '/cetak'];
    if (bypass.some(t => url.pathname.includes(t))) return false;

    return isAuth(window.location.pathname) && isAuth(url.pathname);
}

function isLinkActive(linkHref, destUrl) {
    if (!linkHref || linkHref.startsWith('#') || linkHref.startsWith('javascript:')) return false;
    let lUrl;
    try { lUrl = new URL(linkHref, window.location.origin); } catch { return false; }

    const lp = lUrl.pathname.replace(/\/$/, ''), dp = destUrl.pathname.replace(/\/$/, '');
    if (lp !== dp && (lp === '/dashboard' || !dp.startsWith(lp + '/'))) return false;

    return (lUrl.searchParams.get('mode') || '') === (destUrl.searchParams.get('mode') || '') &&
           (lUrl.searchParams.get('tab') || '') === (destUrl.searchParams.get('tab') || '');
}

function syncNavLinks(destinationUrl) {
    try {
        const destUrl = new URL(destinationUrl, window.location.origin);
        document.querySelectorAll('#sidebar-nav a[href]').forEach(a => {
            const active = isLinkActive(a.getAttribute('href'), destUrl);
            a.className = active
                ? 'flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition-colors duration-100 bg-blue-50 text-blue-700 dark:bg-slate-800 dark:text-white dark:border dark:border-slate-700/80 shadow-2xs group'
                : 'flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-medium transition-colors duration-100 text-slate-600 hover:text-slate-900 hover:bg-slate-100/70 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800/50 group';
            const svg = a.querySelector('svg');
            if (svg) svg.setAttribute('class', active ? 'size-4 shrink-0 text-blue-600 dark:text-blue-400' : 'size-4 shrink-0 text-slate-400 dark:text-slate-500 group-hover:text-slate-600 dark:group-hover:text-slate-300 transition-colors');
        });

        const bNav = document.getElementById('bottom-nav') || document.querySelector('nav.fixed.bottom-0');
        if (bNav) {
            bNav.querySelectorAll('a[href]').forEach(a => {
                const active = isLinkActive(a.getAttribute('href'), destUrl);
                a.className = 'flex-1 flex flex-col items-center justify-center py-1 transition-colors ' +
                    (active ? 'font-bold text-blue-600 dark:text-blue-400' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200');
            });
        }
    } catch (e) {}
}

function applyHtmlToDom(htmlText, destinationUrl, currentMain, pushState = true) {
    const doc = new DOMParser().parseFromString(htmlText, 'text/html'), newMain = doc.querySelector('main');
    if (!currentMain || !newMain) { window.location.href = destinationUrl; return false; }
    if (doc.title) document.title = doc.title;

    document.querySelectorAll('body > [data-teleport-clone], body > [data-modal-portal]').forEach(el => el.remove());
    if (window.Alpine?.destroyTree) { try { window.Alpine.destroyTree(currentMain); } catch (e) {} }
    currentMain.innerHTML = newMain.innerHTML;
    currentMain.scrollTop = 0;

    currentMain.querySelectorAll('script').forEach((oldScript) => {
        try {
            const newScript = document.createElement('script');
            Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
            newScript.textContent = oldScript.textContent;
            document.head.appendChild(newScript);
            newScript.remove();
        } catch (err) {}
    });

    if (window.Alpine?.initTree) { try { window.Alpine.initTree(currentMain); } catch (e) {} }
    syncNavLinks(destinationUrl);
    if (window.innerWidth < 1024) window.dispatchEvent(new CustomEvent('close-sidebar'));
    if (pushState) { try { window.history.pushState({ spaUrl: destinationUrl }, '', destinationUrl); } catch (e) {} }
    return true;
}

async function navigateTo(url, pushState = true) {
    if (pendingNavUrl === url) return;
    pendingNavUrl = url;

    if (activeAbortController) activeAbortController.abort();
    activeAbortController = new AbortController();
    const signal = activeAbortController.signal;

    let timedOut = false;
    const timeoutId = setTimeout(() => { timedOut = true; if (activeAbortController) activeAbortController.abort(); }, 5000);

    const currentMain = document.querySelector('main');
    if (currentMain) currentMain.style.opacity = '0.7';
    startProgress();

    const targetObj = new URL(url, window.location.origin), fetchPath = targetObj.pathname + targetObj.search;
    const cached = pageCache.get(fetchPath);
    if (cached && (Date.now() - cached.time < CACHE_TTL)) {
        clearTimeout(timeoutId);
        applyHtmlToDom(cached.html, targetObj.href, currentMain, pushState);
        completeProgress();
        if (currentMain) currentMain.style.opacity = '1';
        pendingNavUrl = null;
        return;
    }

    try {
        const response = await fetch(fetchPath, { signal, headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-SPA-Navigation': 'true' } });
        if (!response.ok) { window.location.href = fetchPath; return; }

        let destinationUrl = targetObj.href;
        if (response.redirected) {
            const redirectedUrl = new URL(response.url);
            if (redirectedUrl.pathname.includes('/login') || !isSameOrigin(redirectedUrl)) { window.location.href = response.url; return; }
            destinationUrl = redirectedUrl.href;
        }

        const htmlText = await response.text();
        pageCache.set(fetchPath, { html: htmlText, time: Date.now() });
        applyHtmlToDom(htmlText, destinationUrl, currentMain, pushState);
    } catch (networkError) {
        if (timedOut || networkError.name !== 'AbortError') window.location.href = fetchPath;
    } finally {
        clearTimeout(timeoutId);
        completeProgress();
        if (currentMain) currentMain.style.opacity = '1';
        pendingNavUrl = null;
    }
}

export function initSpaNavigator() {
    if (window._spaNavigatorInitialized) return;
    window._spaNavigatorInitialized = true;

    document.addEventListener('click', (event) => {
        const anchor = event.target.closest('a');
        if (shouldInterceptLink(anchor, event)) {
            event.preventDefault();
            syncNavLinks(anchor.href);
            navigateTo(anchor.href, true);
        }
    });

    document.addEventListener('change', (event) => {
        const target = event.target;
        if (!target?.form) return;
        const form = target.form;
        if (form.method?.toUpperCase() === 'GET' && !form.hasAttribute('data-no-spa')) {
            if (target.matches('select, input[type="radio"], input[type="checkbox"]')) {
                if (typeof form.requestSubmit === 'function') form.requestSubmit();
                else form.dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
            }
        }
    });

    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (!form) return;
        if (form.method?.toUpperCase() !== 'GET') {
            pageCache.clear();
            return;
        }
        const formAction = form.action || window.location.href;
        let targetUrl;
        try { targetUrl = new URL(formAction, window.location.origin); } catch { return; }

        if (form.hasAttribute('data-no-spa') || form.hasAttribute('data-confirm') || form.target === '_blank' || targetUrl.pathname.includes('/export')) return;
        if (!isSameOrigin(targetUrl) || !isAuth(window.location.pathname) || !isAuth(targetUrl.pathname)) return;

        event.preventDefault();
        const formData = new FormData(form, event.submitter || undefined), searchParams = new URLSearchParams();
        for (const [key, value] of formData.entries()) {
            if (value !== '' && value !== null && value !== undefined) searchParams.append(key, value);
        }
        const qs = searchParams.toString();
        navigateTo(targetUrl.pathname + (qs ? '?' + qs : ''), true);
    });

    window.addEventListener('popstate', () => { navigateTo(window.location.href, false); });
}
