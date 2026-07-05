{{-- Toast notifikasi global (top-right). Pakai SweetAlert2. --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    .toast-pop .swal2-popup { padding: 0.85rem 1rem !important; }
    .toast-pop .swal2-title { font-size: 0.95rem !important; font-weight: 600 !important; padding: 0 !important; margin: 0 !important; }
    .toast-pop .swal2-html-container { font-size: 0.825rem !important; color: #475569 !important; padding: 0 !important; margin: 0.25rem 0 0 0 !important; line-height: 1.35 !important; }
    .toast-pop .swal2-icon { width: 1.75rem !important; height: 1.75rem !important; margin: 0 0.625rem 0 0 !important; }
    .toast-pop.swal2-success-bordered { border-left: 4px solid #10b981 !important; }
    .toast-pop.swal2-error-bordered   { border-left: 4px solid #e11d48 !important; }
    .toast-pop.swal2-warning-bordered { border-left: 4px solid #f59e0b !important; }
    .toast-pop.swal2-info-bordered    { border-left: 4px solid #0ea5e9 !important; }
</style>

<script>
    (function () {
        // Default title per type.
        const DEFAULT_TITLES = {
            success: 'Berhasil',
            error:   'Gagal',
            warning: 'Perhatian',
            info:    'Informasi',
        };

        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 4000,
            timerProgressBar: true,
            showCloseButton: true,
            customClass: { popup: 'toast-pop' },
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            },
        });

        /**
         * Helper global: window.toast('success', 'Judul', 'Pesan detail.')
         * type: success | error | warning | info
         */
        window.toast = function (type, title, message) {
            const t = DEFAULT_TITLES[type] ? type : 'info';
            Toast.fire({
                icon: t,
                title: title || DEFAULT_TITLES[t],
                html: message ? `<div>${message}</div>` : undefined,
                customClass: { popup: 'toast-pop swal2-' + t + '-bordered' },
            });
        };

        // Auto-trigger dari flash session.
        @if (session('toast'))
            @php $t = session('toast'); @endphp
            window.toast(
                @json($t['type'] ?? 'success'),
                @json($t['title'] ?? null),
                @json($t['message'] ?? null)
            );
        @elseif (session('status'))
            window.toast('success', null, @json(session('status')));
        @elseif (session('error'))
            window.toast('error', null, @json(session('error')));
        @endif
    })();
</script>
