<!-- KOMPONEN MODAL KONFIRMASI GLOBAL ELEGAN (PENGGANTI POPUP BROWSER DEFAULT ALERT) -->
<div
    x-data="{
        open: false,
        title: 'Konfirmasi Tindakan',
        message: 'Apakah Anda yakin ingin melanjutkan tindakan ini?',
        confirmText: 'Ya, Lanjutkan',
        cancelText: 'Batal',
        type: 'danger', // 'danger', 'success', 'warning', 'primary'
        targetForm: null,
        callback: null,

        show(detail) {
            this.title = detail.title || 'Konfirmasi Tindakan';
            this.message = detail.message || 'Apakah Anda yakin ingin melanjutkan tindakan ini?';
            this.confirmText = detail.confirmText || 'Ya, Lanjutkan';
            this.cancelText = detail.cancelText || 'Batal';
            this.type = detail.type || 'danger';
            this.targetForm = detail.form || null;
            this.callback = detail.onConfirm || null;
            this.open = true;
        },
        doConfirm() {
            this.open = false;
            if (this.targetForm) {
                this.targetForm.dataset.confirmed = 'true';
                this.targetForm.submit();
            } else if (typeof this.callback === 'function') {
                this.callback();
            }
        }
    }"
    @open-confirm-modal.window="show($event.detail)"
>
    <template x-teleport="body">
        <div
            x-show="open"
            class="fixed inset-0 z-[9999] overflow-y-auto"
            x-cloak
        >
            <!-- Backdrop Full Viewport Gelap Halus -->
            <div
                x-show="open"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-slate-900/60 transition-opacity"
                @click="open = false"
            ></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div
                    x-show="open"
                    x-transition:enter="ease-out duration-200"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="relative w-full max-w-md transform overflow-hidden rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 p-5 sm:p-6 text-left shadow-2xl transition-all z-10"
                    @click.stop
                >
            <div class="flex items-start gap-4">
                <!-- Icon Sesuai Type -->
                <div class="size-11 rounded-xl shrink-0 flex items-center justify-center shadow-xs"
                     :class="{
                         'bg-rose-50 dark:bg-slate-800 text-rose-600 dark:text-rose-400 border border-rose-200/80 dark:border-slate-700': type === 'danger',
                         'bg-emerald-50 dark:bg-slate-800 text-emerald-600 dark:text-emerald-400 border border-emerald-200/80 dark:border-slate-700': type === 'success',
                         'bg-amber-50 dark:bg-slate-800 text-amber-600 dark:text-amber-400 border border-amber-200/80 dark:border-slate-700': type === 'warning',
                         'bg-blue-50 dark:bg-slate-800 text-blue-600 dark:text-blue-400 border border-blue-200/80 dark:border-slate-700': type === 'primary'
                     }"
                >
                    <!-- Danger Icon -->
                    <template x-if="type === 'danger'">
                        <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </template>
                    <!-- Success / Sync Icon -->
                    <template x-if="type === 'success'">
                        <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </template>
                    <!-- Warning Icon -->
                    <template x-if="type === 'warning'">
                        <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </template>
                    <!-- Primary / Info Icon -->
                    <template x-if="type === 'primary'">
                        <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </template>
                </div>

                <div class="flex-1">
                    <h3 class="text-base font-bold text-slate-900 dark:text-slate-100" x-text="title"></h3>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400 leading-relaxed" x-text="message"></p>
                </div>
            </div>

            <!-- Footer Action Buttons -->
            <div class="mt-5 sm:mt-6 flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100 dark:border-slate-800">
                <button
                    type="button"
                    @click="open = false"
                    class="px-4 py-2 rounded-xl text-xs font-semibold border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition cursor-pointer"
                    x-text="cancelText"
                >
                </button>
                <button
                    type="button"
                    @click="doConfirm()"
                    class="px-4 py-2 rounded-xl text-xs font-bold text-white shadow-xs transition cursor-pointer"
                    :class="{
                        'bg-rose-600 hover:bg-rose-700': type === 'danger',
                        'bg-emerald-600 hover:bg-emerald-700': type === 'success',
                        'bg-amber-600 hover:bg-amber-700': type === 'warning',
                        'bg-blue-600 hover:bg-blue-700': type === 'primary'
                    }"
                    x-text="confirmText"
                >
                </button>
            </div>
        </div>
    </div>
    </template>
</div>

<script>
    (function() {
        // Intercept form submit dengan data-confirm
        document.addEventListener('submit', function(e) {
            const form = e.target;
            if (form && form.getAttribute && form.hasAttribute('data-confirm') && !form.dataset.confirmed) {
                e.preventDefault();
                e.stopImmediatePropagation();
                window.dispatchEvent(new CustomEvent('open-confirm-modal', {
                    detail: {
                        title: form.getAttribute('data-confirm-title') || 'Konfirmasi Tindakan',
                        message: form.getAttribute('data-confirm'),
                        confirmText: form.getAttribute('data-confirm-btn') || 'Ya, Lanjutkan',
                        type: form.getAttribute('data-confirm-type') || 'danger',
                        form: form
                    }
                }));
            }
        }, true);

        // Intercept click pada tombol / link yang memiliki data-confirm (bukan form)
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('[data-confirm]:not(form)');
            if (!btn) return;
            e.preventDefault();
            e.stopPropagation();

            const parentForm = btn.form || btn.closest('form');
            const action = btn.getAttribute('data-action') || btn.getAttribute('href') || parentForm?.action;
            const method = (btn.getAttribute('data-method') || parentForm?.querySelector('input[name="_method"]')?.value || parentForm?.method || 'POST').toUpperCase();
            const message = btn.getAttribute('data-confirm');
            const title = btn.getAttribute('data-confirm-title') || 'Konfirmasi Tindakan';
            const confirmBtn = btn.getAttribute('data-confirm-btn') || (method === 'DELETE' ? 'Ya, Hapus' : 'Ya, Lanjutkan');
            const type = btn.getAttribute('data-confirm-type') || (method === 'DELETE' ? 'danger' : 'primary');

            // Jika tombol berada di dalam form (misal: tombol hapus dengan method DELETE)
            if (parentForm) {
                window.confirmAction({
                    title: title,
                    message: message,
                    confirmText: confirmBtn,
                    type: type,
                    form: parentForm
                });
                return;
            }

            window.confirmAction({
                title: title,
                message: message,
                confirmText: confirmBtn,
                type: type,
                onConfirm: function() {
                    if (action) {
                        const tempForm = document.createElement('form');
                        tempForm.method = 'POST';
                        tempForm.action = action;
                        tempForm.style.display = 'none';

                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                        if (csrfToken) {
                            const csrfInput = document.createElement('input');
                            csrfInput.type = 'hidden';
                            csrfInput.name = '_token';
                            csrfInput.value = csrfToken;
                            tempForm.appendChild(csrfInput);
                        }

                        if (method !== 'POST' && method !== 'GET') {
                            const methodInput = document.createElement('input');
                            methodInput.type = 'hidden';
                            methodInput.name = '_method';
                            methodInput.value = method;
                            tempForm.appendChild(methodInput);
                        }

                        document.body.appendChild(tempForm);
                        tempForm.submit();
                    } else if (btn.tagName === 'A' && btn.href) {
                        window.location.href = btn.href;
                    }
                }
            });
        });

        window.confirmAction = function(options) {
            window.dispatchEvent(new CustomEvent('open-confirm-modal', { detail: options }));
        };
    })();
</script>
