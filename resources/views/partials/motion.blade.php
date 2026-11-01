{{-- Top Navigation Loading Bar (GPU-accelerated scaleX transform) --}}
<div id="kn-progress-wrap" aria-hidden="true" class="no-print fixed top-0 left-0 right-0 h-[3px] z-[100] pointer-events-none opacity-0 transition-opacity duration-200">
    <div id="kn-progress-bar"
         class="h-full w-full origin-left scale-x-0 transition-transform duration-300 ease-out"
         style="background: linear-gradient(90deg, #B85D3B 0%, #C69C6D 55%, #B85D3B 100%); box-shadow: 0 0 10px rgba(184, 93, 59, 0.45);">
    </div>
</div>

<script>
    (function () {
        const wrap = document.getElementById('kn-progress-wrap');
        const bar = document.getElementById('kn-progress-bar');
        let trickleTimer = null;
        let currentProgress = 0;

        function setProgress(pct) {
            currentProgress = pct;
            if (bar) bar.style.transform = `scaleX(${pct / 100})`;
        }

        function startLoadingBar() {
            if (!wrap || !bar) return;
            clearInterval(trickleTimer);
            wrap.classList.remove('opacity-0');
            setProgress(18);

            trickleTimer = setInterval(() => {
                if (currentProgress < 84) {
                    setProgress(currentProgress + Math.max(2, (84 - currentProgress) * 0.14));
                }
            }, 180);
        }

        function finishLoadingBar() {
            clearInterval(trickleTimer);
            if (!wrap || !bar) return;
            setProgress(100);
            setTimeout(() => {
                wrap.classList.add('opacity-0');
                setTimeout(() => setProgress(0), 220);
            }, 160);
        }

        // Trigger top loading bar on internal page links
        document.addEventListener('click', function (e) {
            const link = e.target.closest('a[href]');
            if (!link) return;
            if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
            if (link.target && link.target !== '_self') return;
            if (link.hasAttribute('download')) return;

            const href = link.getAttribute('href') || '';
            if (!href || href.startsWith('#') || href.startsWith('javascript:') || href.includes('/export')) return;

            try {
                const url = new URL(link.href, window.location.origin);
                if (url.origin !== window.location.origin) return;
                if (url.pathname === window.location.pathname && url.search === window.location.search) return;
                startLoadingBar();
            } catch (_) {}
        });

        // Trigger button spinner + loading bar on form submissions (after confirm() checks run)
        window.addEventListener('submit', function (e) {
            if (e.defaultPrevented) return;
            const form = e.target;
            if (!(form instanceof HTMLFormElement)) return;

            const action = form.getAttribute('action') || '';
            if (!action.includes('/export')) {
                startLoadingBar();
            }

            const submitter = e.submitter || form.querySelector('button[type="submit"]');
            if (submitter && !submitter.dataset.knLoading) {
                submitter.dataset.knLoading = '1';
                submitter.dataset.knOriginalHtml = submitter.innerHTML;
                const width = submitter.offsetWidth;
                if (width > 64) {
                    submitter.style.minWidth = width + 'px';
                }
                submitter.disabled = true;
                submitter.classList.add('opacity-85', 'cursor-wait');

                const labelText = (submitter.textContent || '').trim();
                const showLabel = labelText.length > 3 ? 'Memproses...' : '';
                submitter.innerHTML = `
                    <svg class="w-4 h-4 kn-spinner inline-block shrink-0" viewBox="0 0 24 24" fill="none">
                        <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-opacity="0.28" stroke-width="2.5"></circle>
                        <path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"></path>
                    </svg>
                    ${showLabel ? `<span>${showLabel}</span>` : ''}
                `;

                // Safety reset in case navigation is cancelled or stays on page
                setTimeout(() => resetSubmitter(submitter), 8000);
            }
        });

        function resetSubmitter(btn) {
            if (!btn || !btn.dataset.knLoading) return;
            btn.disabled = false;
            btn.classList.remove('opacity-85', 'cursor-wait');
            btn.style.minWidth = '';
            if (btn.dataset.knOriginalHtml) {
                btn.innerHTML = btn.dataset.knOriginalHtml;
            }
            delete btn.dataset.knLoading;
        }

        // Reset cleanly if user navigates back via browser bfcache
        window.addEventListener('pageshow', function () {
            finishLoadingBar();
            document.querySelectorAll('[data-kn-loading]').forEach(resetSubmitter);
        });

        // Close Staff Switcher popover when clicking outside
        document.addEventListener('click', function (e) {
            const menu = document.getElementById('staff-switcher-menu');
            const trigger = document.getElementById('staff-switcher-trigger');
            if (!menu || menu.classList.contains('hidden')) return;
            if (!menu.contains(e.target) && (!trigger || !trigger.contains(e.target))) {
                menu.classList.add('hidden');
            }
        });

        // Auto-dismiss success flash banners gently after 5.5s (pauses on hover)
        document.querySelectorAll('[data-kn-autodismiss]').forEach(toast => {
            let timer = setTimeout(() => dismissToast(toast), 5500);
            toast.addEventListener('mouseenter', () => clearTimeout(timer));
            toast.addEventListener('mouseleave', () => {
                timer = setTimeout(() => dismissToast(toast), 2500);
            });
        });

        window.knDismissToast = dismissToast;
        function dismissToast(el) {
            if (!el) return;
            el.style.opacity = '0';
            el.style.transform = 'translate3d(0, -6px, 0)';
            setTimeout(() => el.remove(), 220);
        }
    })();
</script>
