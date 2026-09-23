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

// Same bar for ordinary link-driven navigation (sidebar links, "Edit",
// "View", etc.) — but not for links another handler already
// intercepted (AJAX pagination, the eye-toggle button, etc.), which
// we detect via e.defaultPrevented since those run first on the way
// up to this document-level listener.
document.addEventListener('click', (e) => {
    if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;

    const link = e.target.closest('a[href]');
    if (!link || link.target === '_blank' || link.hasAttribute('download')) return;

    const href = link.getAttribute('href');
    if (!href || href.startsWith('#') || href.startsWith('javascript:')) return;
    if (link.dataset.noProgress !== undefined) return;

    showPageProgress();
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

Alpine.start();
