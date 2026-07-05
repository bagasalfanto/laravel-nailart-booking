<x-app-layout>
    <x-slot name="header">Activity Log</x-slot>

    <div class="max-w-7xl mx-auto">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between mb-6">
            <div>
                <h1 class="text-3xl font-semibold text-slate-900">Activity Log</h1>
                <p class="text-slate-500 mt-1">Audit trail seluruh aktivitas user & sistem.</p>
            </div>
            <x-export-dropdown
                csv-url="{{ route('dashboard.activity-log.export', request()->query()) }}"
            />
        </div>

        {{-- Filter toolbar --}}
        <div class="rounded-2xl bg-white border border-[#eedde2] p-4 mb-4">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                <div class="md:col-span-3">
                    <label class="block text-xs text-slate-500 mb-1">User (nama / email)</label>
                    <input type="text" id="f_user" placeholder="Cari user…"
                           class="w-full rounded-lg border-[#ece4e8] text-sm focus:border-[#a23f66] focus:ring-[#a23f66]">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs text-slate-500 mb-1">Action</label>
                    <select id="f_event" class="w-full rounded-lg border-[#ece4e8] text-sm focus:border-[#a23f66] focus:ring-[#a23f66]">
                        <option value="">Semua</option>
                        @foreach ($events as $ev)
                            <option value="{{ $ev }}">{{ $ev }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs text-slate-500 mb-1">Subject</label>
                    <select id="f_subject_type" class="w-full rounded-lg border-[#ece4e8] text-sm focus:border-[#a23f66] focus:ring-[#a23f66]">
                        <option value="">Semua</option>
                        @foreach ($subjectTypes as $st)
                            <option value="{{ $st['value'] }}">{{ $st['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs text-slate-500 mb-1">Dari Tanggal</label>
                    <input type="date" id="f_date_from" class="w-full rounded-lg border-[#ece4e8] text-sm focus:border-[#a23f66] focus:ring-[#a23f66]">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs text-slate-500 mb-1">Sampai Tanggal</label>
                    <input type="date" id="f_date_to" class="w-full rounded-lg border-[#ece4e8] text-sm focus:border-[#a23f66] focus:ring-[#a23f66]">
                </div>
                <div class="md:col-span-1 flex items-end">
                    <button type="button" id="resetFilter"
                            class="w-full rounded-lg border border-[#eedde2] px-3 py-2 text-sm text-slate-600 hover:bg-[#fdf2f5]">Reset</button>
                </div>
            </div>
            <div class="mt-3">
                <input type="text" id="f_description" placeholder="Cari description…"
                       class="w-full rounded-lg border-[#ece4e8] text-sm focus:border-[#a23f66] focus:ring-[#a23f66]">
            </div>
        </div>

        {{-- Tabel --}}
        <div class="rounded-2xl bg-white border border-[#eedde2] overflow-hidden">
            <div class="overflow-x-auto dt-yajra-wrap">
                <table id="activityTable" class="min-w-full text-sm">
                    <thead class="bg-[#fdf2f5]">
                        <tr class="text-left text-slate-700">
                            <th class="px-4 py-3 font-semibold">Waktu</th>
                            <th class="px-4 py-3 font-semibold">User</th>
                            <th class="px-4 py-3 font-semibold">Action</th>
                            <th class="px-4 py-3 font-semibold">Subject</th>
                            <th class="px-4 py-3 font-semibold">Description</th>
                            <th class="px-4 py-3 font-semibold">IP</th>
                            <th class="px-4 py-3 font-semibold text-right">&nbsp;</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    {{-- Modal Detail --}}
    <div id="detailModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-hidden flex flex-col">
            <div class="flex items-center justify-between px-6 py-4 border-b border-[#f1e4e8]">
                <h3 class="text-lg font-semibold text-slate-900">Detail Activity</h3>
                <button type="button" id="closeDetail" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div id="detailBody" class="px-6 py-5 overflow-y-auto text-sm space-y-4">
                <div class="text-center text-slate-400 py-8">Memuat…</div>
            </div>
        </div>
    </div>

    @include('partials.yajra-assets')

    <style>

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
        $(function () {
            const table = $('#activityTable').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100],
                order: [[0, 'desc']],
                ajax: {
                    url: @json(route('dashboard.activity-log.data')),
                    data: function (d) {
                        d.f_user         = $('#f_user').val();
                        d.f_event        = $('#f_event').val();
                        d.f_subject_type = $('#f_subject_type').val();
                        d.f_description  = $('#f_description').val();
                        d.f_date_from    = $('#f_date_from').val();
                        d.f_date_to      = $('#f_date_to').val();
                    },
                    error: function (xhr) {
                        const table = $(this).DataTable();
                        table.processing(false);
                        if (xhr.status === 401) { window.location.reload(); return; }
                        window.toast?.('error', 'Gagal Memuat', 'Server error — coba refresh halaman.');
                    },
                },
                columns: [
                    { data: 'time_label',    name: 'created_at' },
                    { data: 'causer_label',  name: 'causer_id', orderable: false },
                    { data: 'event_badge',   name: 'event' },
                    { data: 'subject_label', name: 'subject_type', orderable: false },
                    { data: 'description',   name: 'description' },
                    { data: 'ip_address',    name: 'ip_address', orderable: false, searchable: false },
                    { data: 'actions',       name: 'actions', orderable: false, searchable: false, className: 'text-right whitespace-nowrap' },
                ],
                language: {
                    search: 'Cari global:',
                    lengthMenu: 'Tampilkan _MENU_ entri',
                    info: 'Menampilkan _START_–_END_ dari _TOTAL_ entri',
                    infoEmpty: 'Tidak ada data',
                    infoFiltered: '(difilter dari _MAX_ total)',
                    zeroRecords: 'Tidak ada activity yang cocok dengan filter.',
                    processing: '<span class="text-slate-500">Memuat…</span>',
                    paginate: { first: '«', previous: '‹', next: '›', last: '»' },
                },
            });

            // Re-draw saat filter berubah (debounced).
            let timer;
            const triggerReload = () => { clearTimeout(timer); timer = setTimeout(() => table.ajax.reload(), 300); };

            $('#f_user, #f_description').on('keyup', triggerReload);
            $('#f_event, #f_subject_type, #f_date_from, #f_date_to').on('change', () => table.ajax.reload());

            $('#resetFilter').on('click', () => {
                $('#f_user, #f_description, #f_date_from, #f_date_to').val('');
                $('#f_event, #f_subject_type').val('');
                table.ajax.reload();
            });

            // Detail modal.
            const modal     = document.getElementById('detailModal');
            const modalBody = document.getElementById('detailBody');
            const showModal = () => { modal.classList.remove('hidden'); modal.classList.add('flex'); };
            const hideModal = () => { modal.classList.add('hidden'); modal.classList.remove('flex'); };

            document.getElementById('closeDetail').addEventListener('click', hideModal);
            modal.addEventListener('click', (e) => { if (e.target === modal) hideModal(); });

            $('#activityTable tbody').on('click', '[data-detail]', async (e) => {
                const id = e.currentTarget.dataset.detail;
                showModal();
                modalBody.innerHTML = '<div class="text-center text-slate-400 py-8">Memuat…</div>';

                try {
                    const res = await fetch(@json(url('dashboard/activity-log')) + '/' + id);
                    const a = await res.json();

                    const props = a.properties || {};
                    const renderProps = Object.keys(props).length === 0
                        ? '<p class="text-slate-400 italic">Tidak ada properti tambahan.</p>'
                        : '<pre class="text-xs bg-slate-50 border border-slate-200 rounded-lg p-3 overflow-x-auto">'+
                          escapeHtml(JSON.stringify(props, null, 2))+
                          '</pre>';

                    modalBody.innerHTML = `
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs text-slate-400 uppercase">Waktu</p>
                                <p class="text-slate-800">${a.created_at} <span class="text-slate-400">(${a.created_diff})</span></p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-400 uppercase">Action</p>
                                <p class="text-slate-800 font-mono">${a.event || '—'}</p>
                            </div>
                            <div class="col-span-2">
                                <p class="text-xs text-slate-400 uppercase">Description</p>
                                <p class="text-slate-800">${escapeHtml(a.description || '—')}</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-400 uppercase">User</p>
                                ${a.causer
                                    ? `<p class="text-slate-800">${escapeHtml(a.causer.name)}<br><span class="text-xs text-slate-500">${escapeHtml(a.causer.email)}${a.causer.role ? ' · '+a.causer.role : ''}</span></p>`
                                    : '<p class="text-slate-400 italic">system</p>'}
                            </div>
                            <div>
                                <p class="text-xs text-slate-400 uppercase">Subject</p>
                                ${a.subject
                                    ? `<p class="text-slate-800">${escapeHtml(a.subject.type)} <code class="text-xs text-slate-400">#${escapeHtml(a.subject.id || '')}</code></p>`
                                    : '<p class="text-slate-400">—</p>'}
                            </div>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400 uppercase mb-1">Properties</p>
                            ${renderProps}
                        </div>
                    `;
                } catch (err) {
                    modalBody.innerHTML = '<p class="text-rose-600">Gagal memuat detail.</p>';
                }
            });

            function escapeHtml(s) {
                return String(s)
                    .replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;')
                    .replaceAll('"', '&quot;').replaceAll("'", '&#039;');
            }
        });
    </script>
</x-app-layout>
