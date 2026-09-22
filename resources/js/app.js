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
            try {
                const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                this.$refs.content.innerHTML = await res.text();
                window.history.replaceState({}, '', url);
            } finally {
                this.loading = false;
            }
        },
    };
};

Alpine.start();
