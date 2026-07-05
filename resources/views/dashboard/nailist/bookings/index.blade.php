<x-app-layout>
    <x-slot name="header">Booking</x-slot>

    <div class="max-w-7xl mx-auto">
        {{-- Header --}}
        <div class="mb-8">
            <h1 class="text-3xl md:text-4xl font-semibold text-slate-900">Booking Management</h1>
            <p class="text-slate-500 mt-1">Review and manage your upcoming client sessions.</p>
        </div>

        {{-- Stats cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            {{-- Pending --}}
            <article class="rounded-2xl bg-white border border-[#eedde2] p-5 relative overflow-hidden">
                <div class="absolute -top-4 -right-4 w-20 h-20 rounded-full bg-amber-100/40"></div>
                <div class="relative flex items-start justify-between gap-3">
                    <div class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-amber-50">
                        <svg class="w-5 h-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <rect x="3" y="5" width="18" height="16" rx="2"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M16 3v4M8 3v4"/>
                        </svg>
                    </div>
                    <p class="text-xs uppercase tracking-wider text-slate-400">Pending</p>
                </div>
                <p class="relative mt-4 text-4xl font-semibold text-slate-900">{{ $stats['pending'] }}</p>
                <p class="relative mt-1 text-sm text-slate-500">Requires your attention</p>
            </article>

            {{-- Confirmed --}}
            <article class="rounded-2xl bg-white border border-[#eedde2] p-5 relative overflow-hidden">
                <div class="absolute -top-4 -right-4 w-20 h-20 rounded-full bg-emerald-100/40"></div>
                <div class="relative flex items-start justify-between gap-3">
                    <div class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-emerald-50">
                        <svg class="w-5 h-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <rect x="3" y="5" width="18" height="16" rx="2"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 14l2 2 4-4M16 3v4M8 3v4M3 10h18"/>
                        </svg>
                    </div>
                    <p class="text-xs uppercase tracking-wider text-slate-400">Confirmed</p>
                </div>
                <p class="relative mt-4 text-4xl font-semibold text-slate-900">{{ $stats['confirmed'] }}</p>
                <p class="relative mt-1 text-sm text-slate-500">Upcoming this week</p>
            </article>

            {{-- Completed --}}
            <article class="rounded-2xl bg-white border border-[#eedde2] p-5 relative overflow-hidden bg-gradient-to-br from-[#fdf2f5] to-white">
                <div class="absolute -top-4 -right-4 w-20 h-20 rounded-full bg-[#fdf2f5]"></div>
                <div class="relative flex items-start justify-between gap-3">
                    <div class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-[#fdf2f5]">
                        <svg class="w-5 h-5 text-[#a23f66]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2 4 4 8-8 4 4"/>
                        </svg>
                    </div>
                    <p class="text-xs uppercase tracking-wider text-slate-400">Completed</p>
                </div>
                <p class="relative mt-4 text-4xl font-semibold text-slate-900">{{ $stats['completed'] }}</p>
                <p class="relative mt-1 text-sm text-slate-500">Sessions this month</p>
            </article>
        </div>

        {{-- DataTable --}}
        <div class="rounded-2xl bg-white border border-[#eedde2] overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-[#f1e4e8]">
                <h2 class="text-lg font-semibold text-slate-900">All Bookings</h2>
                <select id="f_status" class="rounded-lg border border-[#eedde2] bg-white pl-3 pr-10 py-1.5 text-sm text-slate-600 focus:border-[#a23f66] focus:ring-[#a23f66]">                    <option value="">Semua Status</option>
                    <option value="pending">Pending</option>
                    <option value="dikonfirmasi">Dikonfirmasi</option>
                    <option value="diproses">Diproses</option>
                    <option value="sukses">Sukses</option>
                    <option value="dibatalkan">Dibatalkan</option>
                </select>
            </div>
            <div class="overflow-x-auto dt-yajra-wrap">
                <table id="bookingsTable" class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left">
                            <th>Customer</th>
                            <th>Treatment</th>
                            <th>Date & Time</th>
                            <th>Status</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    {{-- Delete form (hidden, set action via JS) --}}
    <form id="delete-booking-form" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>

    @include('partials.yajra-assets')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        [x-cloak] { display: none !important; }

        /* 1. Mencegat DataTables versi 1.x (Select di dalam Label) */
        .dataTables_length label {
            display: flex !important;
            flex-direction: row !important;
            align-items: center !important;
            gap: 8px !important;
            white-space: nowrap !important;
            width: max-content !important; /* Paksa agar tidak menyusut turun */
        }

        /* 2. Mencegat DataTables versi 2.x (Select dan Label bersebelahan) */
        .dt-length {
            display: flex !important;
            flex-direction: row !important;
            align-items: center !important;
            gap: 8px !important;
            white-space: nowrap !important;
            width: max-content !important;
        }
        .dt-length label { margin: 0 !important; }

        /* 3. Paksa ukuran kotak dropdown dan ikon panahnya agar lega */
        .dataTables_length select,
        .dt-length select {
            width: 75px !important;
            min-width: 75px !important;
            padding: 4px 30px 4px 12px !important; /* Padding kanan 30px khusus untuk panah */
            background-position: right 8px center !important; /* Dorong panah ke ujung kanan */
            border-radius: 6px !important;
            box-sizing: border-box !important;
        }
    </style>

    <script>
        const csrf = document.querySelector('meta[name="csrf-token"]').content;

        $(function () {
            const table = $('#bookingsTable').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100],
                order: [[2, 'desc']],
                ajax: {
                    url: @json(route('dashboard.nailist-bookings.data')),
                    data: function (d) {
                        d.f_status = $('#f_status').val();
                    },
                    error: function (xhr) {
                        table.processing(false);
                        if (xhr.status === 401) { window.location.reload(); return; }
                        window.toast?.('error', 'Gagal Memuat', 'Server error — coba refresh halaman.');
                    },
                },
                columns: [
                    { data: 'customer_label', name: 'customer.user.full_name', orderable: false },
                    { data: 'treatment_label', name: 'treatment_id' },
                    { data: 'schedule_label',  name: 'waktu_mulai' },
                    { data: 'status_badge',    name: 'status_id' },
                    { data: 'actions',         name: 'actions', orderable: false, searchable: false, className: 'text-right whitespace-nowrap' },
                ],
                language: {
                    search: 'Cari global:',
                    lengthMenu: 'Tampilkan _MENU_ entri',
                    info: 'Menampilkan _START_-_END_ dari _TOTAL_ entri',
                    infoEmpty: 'Tidak ada data',
                    zeroRecords: 'Tidak ada booking.',
                    processing: '<span class="text-slate-500">Memuat…</span>',
                    paginate: { first: '«', previous: '<', next: '>', last: '»' },
                },
            });

            $('#f_status').on('change', () => table.ajax.reload(null, false));

            // Delete handler
            $('#bookingsTable tbody').on('click', '[data-delete-booking]', async function () {
                const result = await Swal.fire({
                    title: 'Hapus Booking?',
                    html: `Booking <b>${this.dataset.name}</b> akan dihapus permanen.`,
                    icon: 'warning',
                    showCancelButton: true,
                    cancelButtonText: 'Batal',
                    confirmButtonText: 'Ya, Hapus',
                    confirmButtonColor: '#e11d48',
                    cancelButtonColor: '#94a3b8',
                    reverseButtons: true,
                });

                if (result.isConfirmed) {
                    const form = document.getElementById('delete-booking-form');
                    form.action = this.dataset.action;
                    form.submit();
                }
            });
        });
    </script>
</x-app-layout>
