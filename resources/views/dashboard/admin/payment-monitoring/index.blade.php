<x-app-layout>
    <x-slot name="header">Payment Monitoring</x-slot>

    <div class="max-w-7xl mx-auto">
        {{-- Header --}}
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between mb-6">
            <div>
                <h1 class="text-3xl font-semibold text-slate-900">Payment Monitoring</h1>
                <p class="text-slate-500 mt-1">Pantau dan konfirmasi pembayaran dari customer.</p>
            </div>
            <div class="flex items-center gap-3">
                <x-export-dropdown
                    csv-url="{{ route('dashboard.payment-monitoring.export', request()->query()) }}"
                    pdf-url="{{ route('dashboard.payment-monitoring.export', array_merge(request()->query(), ['format' => 'pdf'])) }}"
                />
            </div>
        </div>

        {{-- Stats grid --}}
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
            <article class="rounded-2xl bg-white border border-[#eedde2] p-5">
                <p class="text-xs uppercase text-slate-400 tracking-wide">Total</p>
                <p class="text-2xl font-semibold text-slate-900 mt-2">{{ number_format($stats['total']) }}</p>
            </article>
            <article class="rounded-2xl bg-emerald-50 border border-emerald-200 p-5">
                <p class="text-xs uppercase text-emerald-600 tracking-wide">Paid</p>
                <p class="text-2xl font-semibold text-emerald-700 mt-2">{{ number_format($stats['paid']) }}</p>
            </article>
            <article class="rounded-2xl bg-amber-50 border border-amber-200 p-5">
                <p class="text-xs uppercase text-amber-600 tracking-wide">Pending</p>
                <p class="text-2xl font-semibold text-amber-700 mt-2">{{ number_format($stats['pending']) }}</p>
            </article>
            <article class="rounded-2xl bg-rose-50 border border-rose-200 p-5">
                <p class="text-xs uppercase text-rose-600 tracking-wide">Expired</p>
                <p class="text-2xl font-semibold text-rose-700 mt-2">{{ number_format($stats['expired']) }}</p>
            </article>
            <article class="rounded-2xl bg-[#fdf2f5] border border-[#eedde2] p-5 col-span-2 md:col-span-1">
                <p class="text-xs uppercase text-[#a23f66] tracking-wide">Revenue</p>
                <p class="text-xl md:text-lg font-bold text-[#a23f66] mt-2">Rp {{ number_format($stats['revenue'], 0, ',', '.') }}</p>
            </article>
        </div>

        {{-- Filter --}}
        <div class="rounded-2xl bg-white border border-[#eedde2] p-4 mb-4 grid grid-cols-1 md:grid-cols-3 gap-3">
            <div class="md:col-span-2">
                <label class="block text-xs text-slate-500 mb-1">Cari (Order ID atau Customer)</label>
                <input type="text" id="f_search" placeholder="Cari…"
                       class="w-full rounded-lg border-[#ece4e8] text-sm focus:border-[#a23f66] focus:ring-[#a23f66]">
            </div>
            <div>
                <label class="block text-xs text-slate-500 mb-1">Status</label>
                <select id="f_status" class="w-full rounded-lg border-[#ece4e8] text-sm focus:border-[#a23f66] focus:ring-[#a23f66]">
                    <option value="">Semua</option>
                    <option value="pending">Pending</option>
                    <option value="settlement">Paid</option>
                    <option value="expire">Expired</option>
                    <option value="cancel">Cancelled</option>
                </select>
            </div>
        </div>

        {{-- Tabel --}}
        <div class="rounded-2xl bg-white border border-[#eedde2] overflow-hidden">
            <div class="overflow-x-auto dt-yajra-wrap">
                <table id="paymentsTable" class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left">
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Nailist</th>
                            <th>Amount</th>
                            <th>Sisa</th>
                            <th>Status</th>
                            <th>Time</th>
                            <th class="text-right">Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

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
            const table = $('#paymentsTable').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100],
                order: [[6, 'desc']],
                ajax: {
                    url: @json(route('dashboard.payment-monitoring.data')),
                    data: function (d) {
                        d.f_search = $('#f_search').val();
                        d.f_status = $('#f_status').val();
                    },
                    error: function (xhr) {
                        table.processing(false);
                        if (xhr.status === 401) { window.location.reload(); return; }
                        window.toast?.('error', 'Gagal Memuat', 'Server error — coba refresh halaman.');
                    },
                },
                columns: [
                    { data: 'order_label',    name: 'order_id' },
                    { data: 'customer_label', name: 'reservasi.customer.user.full_name', orderable: false },
                    { data: 'nailist_label',  name: 'reservasi.nailist.user.full_name', orderable: false },
                    { data: 'amount_label',   name: 'nominal' },
                    { data: 'remaining_label',name: 'remaining', orderable: false, searchable: false },
                    { data: 'status_badge',   name: 'status_pembayaran' },
                    { data: 'time_label',     name: 'created_at' },
                    { data: 'actions',        name: 'actions', orderable: false, searchable: false, className: 'text-right whitespace-nowrap' },
                ],
                language: {
                    search: 'Cari global:',
                    lengthMenu: 'Tampilkan _MENU_ entri',
                    info: 'Menampilkan _START_–_END_ dari _TOTAL_ entri',
                    infoEmpty: 'Tidak ada data',
                    zeroRecords: 'Tidak ada pembayaran yang cocok.',
                    processing: '<span class="text-slate-500">Memuat…</span>',
                    paginate: { first: '«', previous: '‹', next: '›', last: '»' },
                },
            });

            let timer;
            $('#f_search').on('keyup', () => { clearTimeout(timer); timer = setTimeout(() => table.ajax.reload(null, false), 300); });
            $('#f_status').on('change', () => table.ajax.reload(null, false));

            $('#paymentsTable tbody').on('click', '[data-confirm-id]', async function () {
                const id = this.dataset.confirmId;
                const order = this.dataset.order;

                const result = await Swal.fire({
                    title: 'Konfirmasi Pembayaran?',
                    html: `Order <b>${order}</b> akan ditandai sebagai <b>Paid</b>. Reservasi terkait akan otomatis berubah ke status Confirmed.`,
                    icon: 'question',
                    showCancelButton: true,
                    cancelButtonText: 'Batal',
                    confirmButtonText: 'Ya, Konfirmasi',
                    confirmButtonColor: '#10b981',
                    cancelButtonColor: '#94a3b8',
                    reverseButtons: true,
                });
                if (! result.isConfirmed) return;

                try {
                    const url = @json(url('dashboard/payment-monitoring')) + '/' + id + '/confirm';
                    const res = await fetch(url, {
                        method: 'PATCH',
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
                    });
                    const json = await res.json();
                    if (! res.ok || ! json.success) {
                        window.toast?.('error', 'Gagal', json.message || 'Terjadi kesalahan.');
                        return;
                    }
                    table.ajax.reload(null, false);
                    window.toast?.('success', 'Pembayaran Dikonfirmasi', json.message);
                } catch (e) {
                    window.toast?.('error', 'Network Error', e.message);
                }
            });

            // Pelunasan handler
            $('#paymentsTable tbody').on('click', '[data-pelunasan-id]', async function () {
                const id = this.dataset.pelunasanId;
                const order = this.dataset.order;
                const customer = this.dataset.customer;
                const treatment = this.dataset.treatment;
                const sisa = parseInt(this.dataset.sisa) || 0;

                const result = await Swal.fire({
                    title: '<span class="text-slate-800">Input Pelunasan</span>',
                    html: `
                        <div class="text-left space-y-4">
                            <div class="rounded-xl bg-gradient-to-br from-[#fdf2f5] to-white border border-[#eedde2] p-4">
                                <div class="flex items-center gap-3 mb-3">
                                    <div class="flex-shrink-0 w-10 h-10 rounded-full bg-[#a23f66] flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-slate-800 text-sm">${customer}</p>
                                        <p class="text-xs text-slate-500">${treatment}</p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-2 text-xs">
                                    <div class="rounded-lg bg-white px-3 py-2 border border-[#f1e4e8]">
                                        <span class="text-slate-400">Order</span>
                                        <p class="font-mono text-slate-700 mt-0.5">${order}</p>
                                    </div>
                                    <div class="rounded-lg bg-white px-3 py-2 border border-[#f1e4e8]">
                                        <span class="text-slate-400">Sisa Tagihan</span>
                                        <p class="font-bold text-[#a23f66] mt-0.5">Rp ${sisa.toLocaleString('id-ID')}</p>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Nominal Pelunasan</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm font-medium">Rp</span>
                                    <input type="number" id="swal-nominal" style="padding-left: 2.5rem !important;" class="block w-full rounded-lg border border-[#eedde2] bg-white py-2.5 pr-3 text-sm text-slate-800 placeholder-slate-400 focus:border-[#a23f66] focus:ring-2 focus:ring-[#fdf2f5] outline-none" placeholder="Masukkan nominal" value="${sisa}" min="1" autofocus>                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Metode Pembayaran</label>
                                <div class="grid grid-cols-2 gap-2">
                                    ${['Cash','Debit','QRIS','Transfer'].map((label, i) => {
                                        const val = label.toLowerCase();
                                        const icons = {
                                            cash: '<path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>',
                                            debit: '<rect x="2" y="3" width="20" height="14" rx="2"/><path stroke-linecap="round" stroke-linejoin="round" d="M2 10h20"/>',
                                            qris: '<path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2.48a2.5 2.5 0 00-4.52 0H5m14-4h2m-6 0H9m10-4h2m-6 0h-2"/>',
                                            transfer: '<path stroke-linecap="round" stroke-linejoin="round" d="M8 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2v-2m-5-9l3-3m0 0l3 3m-3-3v12"/>',
                                        };
                                        const isFirst = i === 0;
                                        return `<label class="flex items-center gap-2 rounded-lg border px-3 py-2.5 cursor-pointer transition text-sm ${isFirst ? 'border-[#a23f66] bg-[#fdf2f5] text-[#a23f66]' : 'border-[#eedde2] bg-white text-slate-600 hover:border-slate-300'}">
                                            <input type="radio" name="swal-jenis" value="${val}" ${isFirst ? 'checked' : ''} class="sr-only pelunasan-radio">
                                            <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">${icons[val]}</svg>
                                            <span class="font-medium">${label}</span>
                                        </label>`;
                                    }).join('')}
                                </div>
                            </div>
                        </div>
                    `,
                    icon: null,
                    showCancelButton: true,
                    cancelButtonText: 'Batal',
                    confirmButtonText: '<span class="flex items-center gap-1.5"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg> Simpan Pelunasan</span>',
                    confirmButtonColor: '#10b981',
                    cancelButtonColor: '#94a3b8',
                    reverseButtons: true,
                    customClass: {
                        popup: 'rounded-2xl',
                        confirmButton: 'rounded-lg px-5 py-2.5 text-sm font-medium',
                        cancelButton: 'rounded-lg px-5 py-2.5 text-sm font-medium',
                    },
                    width: '28rem',
                    didOpen: () => {
                        document.getElementById('swal-nominal').focus();
                        // Highlight radio selection
                        document.querySelectorAll('.pelunasan-radio').forEach(radio => {
                            radio.addEventListener('change', function () {
                                document.querySelectorAll('.pelunasan-radio').forEach(r => {
                                    const label = r.closest('label');
                                    if (r.checked) {
                                        label.classList.add('border-[#a23f66]', 'bg-[#fdf2f5]', 'text-[#a23f66]');
                                        label.classList.remove('border-[#eedde2]', 'bg-white', 'text-slate-600', 'hover:border-slate-300');
                                    } else {
                                        label.classList.remove('border-[#a23f66]', 'bg-[#fdf2f5]', 'text-[#a23f66]');
                                        label.classList.add('border-[#eedde2]', 'bg-white', 'text-slate-600', 'hover:border-slate-300');
                                    }
                                });
                            });
                        });
                    },
                    preConfirm: () => {
                        const nominal = document.getElementById('swal-nominal').value;
                        const jenisRadio = document.querySelector('input[name="swal-jenis"]:checked');
                        const jenis = jenisRadio ? jenisRadio.value : null;
                        if (!nominal || parseFloat(nominal) <= 0) {
                            Swal.showValidationMessage('Nominal harus lebih dari 0');
                            return false;
                        }
                        if (!jenis) {
                            Swal.showValidationMessage('Pilih metode pembayaran');
                            return false;
                        }
                        return { nominal, jenis };
                    },
                });

                if (!result.isConfirmed || !result.value) return;

                try {
                    const url = @json(url('dashboard/payment-monitoring')) + '/' + id + '/pelunasan';
                    const res = await fetch(url, {
                        method: 'PATCH',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            pelunasan_nominal: result.value.nominal,
                            pelunasan_jenis: result.value.jenis,
                        }),
                    });
                    const json = await res.json();
                    if (!res.ok || !json.success) {
                        window.toast?.('error', 'Gagal', json.message || 'Terjadi kesalahan.');
                        return;
                    }
                    table.ajax.reload(null, false);
                    window.toast?.('success', 'Pelunasan Tercatat', json.message);
                } catch (e) {
                    window.toast?.('error', 'Network Error', e.message);
                }
            });
        });
    </script>
</x-app-layout>
