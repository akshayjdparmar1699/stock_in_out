import Alpine from 'alpinejs';
import {
    Chart,
    BarController,
    DoughnutController,
    LineController,
    BarElement,
    ArcElement,
    LineElement,
    PointElement,
    CategoryScale,
    LinearScale,
    Tooltip,
    Filler,
} from 'chart.js';

Chart.register(
    BarController, DoughnutController, LineController,
    BarElement, ArcElement, LineElement, PointElement,
    CategoryScale, LinearScale, Tooltip, Filler
);

window.Alpine = Alpine;

/**
 * Small, server-data-driven chart components for the dashboard — kept
 * generic (labels/values/colors in, a Chart.js instance out) rather than
 * dashboard-specific, so any future chart just reuses one of these two
 * instead of writing fresh Chart.js config each time.
 */
window.barChart = function ({ labels, values, colors }) {
    return {
        chart: null,
        render(canvas) {
            this.chart = new Chart(canvas, {
                type: 'bar',
                data: {
                    labels,
                    datasets: [{ data: values, backgroundColor: colors, borderRadius: 6, maxBarThickness: 56 }],
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { ticks: { callback: (v) => '₹' + Number(v).toLocaleString('en-IN') } },
                    },
                },
            });
        },
    };
};

window.donutChart = function ({ labels, values, colors }) {
    return {
        chart: null,
        render(canvas) {
            this.chart = new Chart(canvas, {
                type: 'doughnut',
                data: {
                    labels,
                    datasets: [{ data: values, backgroundColor: colors, borderWidth: 2, borderColor: '#fff' }],
                },
                options: {
                    responsive: true,
                    cutout: '62%',
                    plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, padding: 12, font: { size: 11 } } } },
                },
            });
        },
    };
};

window.lineChart = function ({ labels, sales, profit }) {
    return {
        chart: null,
        render(canvas) {
            this.chart = new Chart(canvas, {
                type: 'line',
                data: {
                    labels,
                    datasets: [
                        {
                            label: 'Sales',
                            data: sales,
                            borderColor: '#6a00d9',
                            backgroundColor: 'rgba(106, 0, 217, 0.1)',
                            fill: true,
                            tension: 0.35,
                            pointRadius: 3,
                            pointBackgroundColor: '#6a00d9',
                        },
                        {
                            label: 'Profit',
                            data: profit,
                            borderColor: '#16a34a',
                            backgroundColor: 'transparent',
                            fill: false,
                            tension: 0.35,
                            pointRadius: 3,
                            pointBackgroundColor: '#16a34a',
                        },
                    ],
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { ticks: { callback: (v) => '₹' + (Number(v) / 1000).toLocaleString('en-IN') + 'k' } },
                    },
                },
            });
        },
    };
};

/**
 * A mixed bar (sales) + line (profit) chart that reports which day the
 * viewer tapped via a plain DOM CustomEvent ("chart-bar-click", detail:
 * {index}), so the host page binds it with a plain `@chart-bar-click`
 * listener instead of a JS callback.
 *
 * The chart instance itself is kept in a closure variable, NOT an x-data
 * property (`this.chart = ...`) — Alpine's reactivity proxies an x-data
 * object's properties lazily, deep-converting a nested value to reactive
 * the first time something reads through it. Chart.js's internal data
 * structures are circular (dataset <-> controller <-> chart back-refs),
 * so the moment `highlight()` below both reads and writes through
 * `this.chart.data...`, Alpine's lazy conversion walks that circular
 * graph and recurses forever. A plain closure variable is never wrapped,
 * so reading/writing it is just normal Chart.js API use.
 */
window.barLineClickChart = function ({ labels, sales, profit }) {
    let chart = null;

    return {
        render(canvas) {
            const barColor = (i) => (i === sales.length - 1 ? '#6a00d9' : '#c38aff');

            chart = new Chart(canvas, {
                type: 'bar',
                data: {
                    labels,
                    datasets: [
                        {
                            type: 'bar',
                            label: 'Sales',
                            data: sales,
                            backgroundColor: sales.map((_, i) => barColor(i)),
                            borderRadius: 4,
                            maxBarThickness: 48,
                            order: 2,
                        },
                        {
                            type: 'line',
                            label: 'Profit',
                            data: profit,
                            borderColor: '#16a34a',
                            backgroundColor: 'transparent',
                            tension: 0.35,
                            pointRadius: 3,
                            pointBackgroundColor: '#16a34a',
                            yAxisID: 'y1',
                            order: 1,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    onClick: (event, elements) => {
                        if (!elements.length) return;
                        const index = elements[0].index;
                        this.highlight(index);
                        canvas.dispatchEvent(new CustomEvent('chart-bar-click', { detail: { index }, bubbles: true }));
                    },
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { ticks: { callback: (v) => '₹' + (Number(v) / 1000).toLocaleString('en-IN') + 'k' } },
                        y1: { display: false, position: 'right' },
                    },
                },
            });

            canvas.style.cursor = 'pointer';
        },
        highlight(index) {
            if (!chart) return;
            chart.data.datasets[0].backgroundColor = chart.data.labels.map((_, i) => (i === index ? '#6a00d9' : '#c38aff'));
            chart.update();
        },
    };
};

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

window.isPwaStandalone = window.navigator.standalone === true
    || window.matchMedia('(display-mode: standalone)').matches;

/**
 * Real push notifications (low stock / over credit limit — the two
 * conditions a save can detect the instant it happens) on top of the
 * in-app bell. Browser permission is asked for explicitly via a button
 * tap, never on page load, and subscribing is a no-op if the browser
 * doesn't support it (desktop Safari, very old browsers) or the server
 * hasn't configured VAPID keys yet (no public key in the meta tag).
 */
function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    const rawData = window.atob(base64);
    return Uint8Array.from([...rawData].map((char) => char.charCodeAt(0)));
}

window.canUsePushNotifications = 'serviceWorker' in navigator && 'PushManager' in window
    && !!document.querySelector('meta[name="vapid-public-key"]')?.content;

window.pushPermissionState = window.canUsePushNotifications ? Notification.permission : 'unsupported';

window.subscribeToPushNotifications = async function () {
    if (!window.canUsePushNotifications) return false;

    const permission = await Notification.requestPermission();
    window.pushPermissionState = permission;
    if (permission !== 'granted') return false;

    const registration = await navigator.serviceWorker.ready;
    const vapidPublicKey = document.querySelector('meta[name="vapid-public-key"]').content;

    const subscription = await registration.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: urlBase64ToUint8Array(vapidPublicKey),
    });

    await fetch('/push-subscriptions', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            Accept: 'application/json',
        },
        body: JSON.stringify(subscription.toJSON()),
    });

    return true;
};

// iOS Safari never fires beforeinstallprompt — there is no programmatic
// install API there at all, only the manual Share > Add to Home Screen
// flow — so the "Install App" button above can never appear on an
// iPhone/iPad no matter how it's styled. This flags that case so the
// nav can show instructions instead of a button that will just never
// light up.
window.isIosInstallable = (() => {
    const isIos = /iphone|ipad|ipod/i.test(navigator.userAgent) && !window.MSStream;

    return isIos && !window.isPwaStandalone;
})();

/**
 * A proactive "install this app" popup — the nav's own Install button/hint
 * is easy to miss tucked in a menu, so this surfaces it the moment the
 * browser signals installability (or, on iOS, on load) and stays gone for
 * good once dismissed or once the app is actually installed/opened
 * standalone.
 */
window.pwaInstallBanner = function () {
    return {
        show: false,
        isIos: window.isIosInstallable,

        init() {
            if (window.isPwaStandalone || localStorage.getItem('pwaInstallBannerDismissed')) {
                return;
            }

            window.addEventListener('pwa-installable', () => { this.show = true; });
            window.addEventListener('pwa-installed', () => { this.show = false; });

            if (window.deferredInstallPrompt) {
                this.show = true;
            } else if (this.isIos) {
                setTimeout(() => { this.show = true; }, 1500);
            }
        },

        async install() {
            await window.promptPwaInstall();
            this.show = false;
        },

        dismiss() {
            this.show = false;
            localStorage.setItem('pwaInstallBannerDismissed', '1');
        },
    };
};

/**
 * The notification bell's dropdown — fetches the current branch's unread
 * count on load (so the badge is right without opening anything) and the
 * full list only once the dropdown is actually opened, since the list
 * itself also re-syncs server-side on every fetch.
 */
window.notificationBell = function () {
    return {
        open: false,
        loading: false,
        unreadCount: 0,
        notifications: [],
        canUsePush: window.canUsePushNotifications,
        pushPermission: window.pushPermissionState,

        init() {
            this.refreshCount();
            window.addEventListener('push-received', () => this.refreshCount());
        },

        async enablePush() {
            const granted = await window.subscribeToPushNotifications();
            this.pushPermission = window.pushPermissionState;
            return granted;
        },

        async refreshCount() {
            try {
                const res = await fetch('/notifications', { headers: { Accept: 'application/json' } });
                const data = await res.json();
                this.unreadCount = data.unread_count;
                this.notifications = data.notifications;
            } catch (e) {
                // Silently skip — a failed background refresh shouldn't show an error to the user.
            }
        },

        async toggle() {
            this.open = !this.open;
            if (this.open) {
                this.loading = true;
                await this.refreshCount();
                this.loading = false;
            }
        },

        async markAllRead() {
            await fetch('/notifications/read-all', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    Accept: 'application/json',
                },
            });
            await this.refreshCount();
        },
    };
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
            + 'background:#6a00d9;z-index:9999;box-shadow:0 0 8px rgba(106, 0, 217,.6);'
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
        #page-loading-overlay .cube-loader { width: 32px; height: 32px; background: #6a00d9;
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
    overlay.innerHTML = spinnerSvg('w-8 h-8 text-brand-600');
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

/**
 * In-app banner for a push notification that arrives while a tab is
 * already open — some browsers suppress the OS-level banner for the
 * focused tab, so this is the thing that actually guarantees the person
 * sees it without having to background the app first. Built with
 * textContent (not innerHTML) since title/body can contain whatever a
 * customer or item was named.
 */
function showPushToast({ title, body, url }) {
    const toast = document.createElement('div');
    toast.style.cssText = 'position:fixed;top:1rem;left:1rem;right:1rem;max-width:20rem;margin-left:auto;'
        + 'z-index:9999;background:#fff;border:1px solid #e5e7eb;border-radius:0.5rem;'
        + 'box-shadow:0 10px 15px -3px rgba(0,0,0,.1),0 4px 6px -4px rgba(0,0,0,.1);'
        + 'padding:1rem;cursor:pointer;display:flex;align-items:flex-start;gap:0.75rem;'
        + 'font-family:inherit;';

    const iconWrap = document.createElement('div');
    iconWrap.style.cssText = 'width:2rem;height:2rem;border-radius:9999px;background:#e0e7ff;'
        + 'display:flex;align-items:center;justify-content:center;flex-shrink:0;';
    iconWrap.innerHTML = '<svg style="width:1.25rem;height:1.25rem;color:#6a00d9" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" /></svg>';

    const textWrap = document.createElement('div');
    textWrap.style.cssText = 'min-width:0;flex:1;';

    const titleEl = document.createElement('p');
    titleEl.style.cssText = 'font-size:0.875rem;font-weight:600;color:#1f2937;margin:0;'
        + 'white-space:nowrap;overflow:hidden;text-overflow:ellipsis;';
    titleEl.textContent = title;

    const bodyEl = document.createElement('p');
    bodyEl.style.cssText = 'font-size:0.75rem;color:#6b7280;margin:0.125rem 0 0 0;';
    bodyEl.textContent = body;

    textWrap.append(titleEl, bodyEl);
    toast.append(iconWrap, textWrap);

    toast.addEventListener('click', () => { window.location.href = url; });
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 6000);
}

if ('serviceWorker' in navigator) {
    navigator.serviceWorker.addEventListener('message', (event) => {
        if (event.data?.type === 'push-received') {
            showPushToast(event.data);
            window.dispatchEvent(new Event('push-received'));
        }
    });
}

Alpine.start();
