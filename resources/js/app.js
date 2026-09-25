import Alpine from 'alpinejs';

window.Alpine = Alpine;

/**
 * Capture the browser's install prompt so we can trigger it from our own
 * "Install App" button instead of relying on the user finding the
 * browser's menu option themselves.
 */
window.deferredInstallPrompt = null;

window.addEventListener('beforeinstallprompt', (event) => {
    event.preventDefault();
    window.deferredInstallPrompt = event;
    window.dispatchEvent(new Event('pwa-installable'));
});

window.addEventListener('appinstalled', () => {
    window.deferredInstallPrompt = null;
    window.dispatchEvent(new Event('pwa-installed'));
});

window.promptPwaInstall = async function () {
    if (!window.deferredInstallPrompt) return;
    window.deferredInstallPrompt.prompt();
    await window.deferredInstallPrompt.userChoice;
    window.deferredInstallPrompt = null;
    window.dispatchEvent(new Event('pwa-installed'));
};

// iOS Safari never fires beforeinstallprompt — there is no programmatic
// install API there at all, only the manual Share > Add to Home Screen
// flow — so the "Install App" button above can never appear on an
// iPhone/iPad no matter how it's styled. This flags that case so the
// nav can show instructions instead of a button that will just never
// light up.
window.isIosInstallable = (() => {
    const isIos = /iphone|ipad|ipod/i.test(navigator.userAgent) && !window.MSStream;
    const isStandalone = window.navigator.standalone === true
        || window.matchMedia('(display-mode: standalone)').matches;

    return isIos && !isStandalone;
})();

/**
 * ---------------------------------------------------------------------
 * Global "something is happening" feedback, so a slow request (cold
 * Render instance, cross-region DB) never looks like a dead click —
 * this is what stops people from mashing "Save" and creating duplicate
 * bills.
 * ---------------------------------------------------------------------
 */

// A thin progress bar across the very top of the viewport, shown the
// instant a real navigation or form submit starts. We never need to
// hide it on success: the browser is about to replace the whole page
// anyway, which takes it (and its state) with it.
function showPageProgress() {
    let bar = document.getElementById('page-progress-bar');
    if (!bar) {
        bar = document.createElement('div');
        bar.id = 'page-progress-bar';
        bar.style.cssText = 'position:fixed;top:0;left:0;height:3px;width:0%;'
            + 'background:#4f46e5;z-index:9999;box-shadow:0 0 8px rgba(79,70,229,.6);'
            + 'transition:width .3s ease-out,opacity .2s ease-in;';
        document.body.appendChild(bar);
    }
    bar.style.opacity = '1';
    bar.style.width = '15%';
    requestAnimationFrame(() => {
        requestAnimationFrame(() => { bar.style.width = '75%'; });
    });
}

// Bfcache/back-button: the page (and our bar) can reappear without a
// fresh load, so make sure it isn't left stuck mid-progress.
window.addEventListener('pageshow', () => {
    const bar = document.getElementById('page-progress-bar');
    if (bar) { bar.style.transition = 'none'; bar.style.width = '0%'; bar.style.opacity = '0'; }
});

const spinnerSvg = (size) => `
    <svg class="animate-spin ${size}" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
    </svg>`;

// Disable + spin a submit button the instant its form is on its way
// out, so a slow response can't be mistaken for a missed click.
document.addEventListener('submit', (e) => {
    if (e.defaultPrevented || e.target.dataset.noProgress !== undefined) return;

    showPageProgress();

    const form = e.target;
    const btn = form.querySelector('button[type="submit"]') || form.querySelector('button:not([type="button"])');
    if (btn && !btn.disabled) {
        btn.disabled = true;
        btn.classList.add('opacity-75', 'cursor-wait');
        btn.insertAdjacentHTML('afterbegin', spinnerSvg('w-4 h-4 inline -ml-1 mr-2'));
    }
});

// A small rotating/morphing square, centered over the whole screen —
// used for ordinary link-driven navigation (sidebar links, period
// filter tabs like Today/This Week, "Edit", "View", etc.) instead of
// the top bar, which is easy to miss on a phone. Injected once.
let cubeLoaderStyleInjected = false;
function ensureCubeLoaderStyles() {
    if (cubeLoaderStyleInjected) return;
    cubeLoaderStyleInjected = true;
    const style = document.createElement('style');
    style.textContent = `
        #page-loading-overlay { position: fixed; inset: 0; z-index: 9998; display: none;
            align-items: center; justify-content: center; background: rgba(255,255,255,.55); }
        #page-loading-overlay .cube-loader { width: 32px; height: 32px; background: #4f46e5;
            animation: cube-loader-spin 1.1s infinite cubic-bezier(.6,.2,.4,.8); }
        @keyframes cube-loader-spin {
            0%   { transform: rotate(0deg) scale(1); border-radius: 15%; }
            50%  { transform: rotate(180deg) scale(.75); border-radius: 50%; }
            100% { transform: rotate(360deg) scale(1); border-radius: 15%; }
        }`;
    document.head.appendChild(style);
}

function showCubeLoader() {
    ensureCubeLoaderStyles();
    let overlay = document.getElementById('page-loading-overlay');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.id = 'page-loading-overlay';
        overlay.innerHTML = '<div class="cube-loader"></div>';
        document.body.appendChild(overlay);
    }
    overlay.style.display = 'flex';
}

function hideCubeLoader() {
    const overlay = document.getElementById('page-loading-overlay');
    if (overlay) overlay.style.display = 'none';
}

window.showCubeLoader = showCubeLoader;
window.hideCubeLoader = hideCubeLoader;

window.addEventListener('pageshow', () => {
    const overlay = document.getElementById('page-loading-overlay');
    if (overlay) overlay.style.display = 'none';
});

document.addEventListener('click', (e) => {
    if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;

    const link = e.target.closest('a[href]');
    if (!link || link.target === '_blank' || link.hasAttribute('download')) return;

    const href = link.getAttribute('href');
    if (!href || href.startsWith('#') || href.startsWith('javascript:')) return;
    if (link.dataset.noProgress !== undefined) return;

    showCubeLoader();
});

/**
 * Shows a centered spinner over an AJAX-loading region without every
 * page needing its own markup for it — used by listTable()/
 * dashboardWidget() below.
 */
function showAjaxSpinner(el) {
    if (!el || el.querySelector(':scope > .ajax-spinner-overlay')) return;
    if (getComputedStyle(el).position === 'static') el.style.position = 'relative';

    const overlay = document.createElement('div');
    overlay.className = 'ajax-spinner-overlay';
    overlay.style.cssText = 'position:absolute;inset:0;display:flex;align-items:center;justify-content:center;'
        + 'background:rgba(255,255,255,.5);min-height:4rem;';
    overlay.innerHTML = spinnerSvg('w-8 h-8 text-indigo-600');
    el.appendChild(overlay);
}

function hideAjaxSpinner(el) {
    el?.querySelector(':scope > .ajax-spinner-overlay')?.remove();
}

/**
 * Shared Alpine component for list pages (Invoices, Customers, Suppliers,
 * Items, Stock, Purchases). Intercepts pagination link clicks and the
 * "rows per page" dropdown so only the table region re-fetches, instead of
 * a full page reload.
 */
window.listTable = function () {
    return {
        loading: false,

        onClick(e) {
            const link = e.target.closest('a[href]');
            if (!link) return;
            e.preventDefault();
            this.load(link.href);
        },

        async onChange(e) {
            const select = e.target.closest('select[name="value"]');
            if (!select) return;

            await fetch(select.form.action, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ value: select.value }),
            });

            const url = new URL(window.location.href);
            url.searchParams.delete('page');
            this.load(url.toString());
        },

        async load(url) {
            this.loading = true;
            showAjaxSpinner(this.$refs.content);
            try {
                const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                this.$refs.content.innerHTML = await res.text();
                window.history.replaceState({}, '', url);
            } finally {
                this.loading = false;
                hideAjaxSpinner(this.$refs.content);
            }
        },
    };
};

window.showAjaxSpinner = showAjaxSpinner;
window.hideAjaxSpinner = hideAjaxSpinner;

// Fetches a PDF route as a real File object (not a navigation), which is
// what lets us hand it to the share sheet or trigger a download without
// ever leaving the page — inside an installed iPhone PWA, navigating the
// tab straight to a PDF URL renders a blank screen (the standalone
// WKWebView has no built-in PDF viewer the way Safari itself does).
//
// Cached per URL and kicked off as soon as the page loads (see
// window.prefetchPdf), rather than only when Share/Download is tapped.
// navigator.share() only counts as "triggered by the user" if it runs
// with no real async gap after the click — awaiting a slow network
// fetch first (Render's free tier is not fast) burns through that
// window, so the very first tap silently does nothing and only the
// second tap (now warmed up) shows the share sheet. Prefetching means
// the file is usually already sitting in the cache by the time someone
// taps Share, so the await afterwards resolves immediately instead of
// waiting on the network.
const pdfFileCache = new Map();
function fetchPdfFile(url, filename) {
    if (!pdfFileCache.has(url)) {
        const promise = (async () => {
            const res = await fetch(url, { credentials: 'same-origin' });
            if (!res.ok) throw new Error(`Failed to fetch PDF (${res.status})`);
            return new File([await res.blob()], filename, { type: 'application/pdf' });
        })();
        promise.catch(() => pdfFileCache.delete(url));
        pdfFileCache.set(url, promise);
    }
    return pdfFileCache.get(url);
}

window.prefetchPdf = function (url, filename) {
    fetchPdfFile(url, filename).catch(() => {});
};

// Saves the PDF on the device. Prefers handing the file straight to the
// native share sheet's "Save to Files" (avoids iOS Safari just navigating
// to the raw blob: URL and showing that in the address bar instead of
// actually saving anything); falls back to a blob-URL download link,
// which desktop browsers save silently without any of that showing.
window.downloadPdf = async function (url, filename) {
    showCubeLoader();
    try {
        const file = await fetchPdfFile(url, filename);

        if (navigator.canShare && navigator.canShare({ files: [file] })) {
            await navigator.share({ files: [file] });
            return;
        }

        const objectUrl = URL.createObjectURL(file);
        const link = document.createElement('a');
        link.href = objectUrl;
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        link.remove();
        setTimeout(() => URL.revokeObjectURL(objectUrl), 10000);
    } catch (err) {
        if (err.name !== 'AbortError') window.open(url, '_blank');
    } finally {
        hideCubeLoader();
    }
};

/**
 * Shares the actual PDF file through the device's native share sheet
 * (WhatsApp, Mail, Files, AirDrop, etc. on mobile; whatever the OS offers
 * on desktop) — this is what lets an iPhone user pick WhatsApp themselves
 * and actually attach the bill, instead of just a downloaded file with
 * nowhere to send it from. Falls back to a text-only share, then to a
 * generic (no-recipient) WhatsApp Web compose, if file-sharing or the Web
 * Share API isn't available in the browser.
 */
window.sharePdf = async function ({ url, filename, title = '', text = '' }) {
    showCubeLoader();
    try {
        const file = await fetchPdfFile(url, filename);
        if (navigator.canShare && navigator.canShare({ files: [file] })) {
            await navigator.share({ files: [file], title, text });
            return;
        }
    } catch (err) {
        if (err.name === 'AbortError') return;
    } finally {
        hideCubeLoader();
    }

    if (navigator.share) {
        try {
            await navigator.share({ title, text });
            return;
        } catch (err) {
            if (err.name === 'AbortError') return;
        }
    }

    window.open(`https://wa.me/?text=${encodeURIComponent(text)}`, '_blank');
};

Alpine.start();
