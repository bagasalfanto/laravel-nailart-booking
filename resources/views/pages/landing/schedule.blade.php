<x-layouts.landing title="Schedule">
    @php
        $canExport = auth()->check() && auth()->user()->hasAnyRole(['admin', 'superadmin', 'nailist']);
    @endphp

    {{-- Google Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
        .schedule-page              { font-family: 'Poppins', sans-serif; color: #1f2937; }
        .schedule-page .font-sans   { font-family: 'Poppins', sans-serif; }
        .schedule-page .font-serif  { font-family: 'Playfair Display', serif; }
        .schedule-page .bg-brand-bg { background-color: #FFF4F2; }
        .schedule-page .bg-card-bg  { background-color: #FFFFFF; }
        .schedule-page .text-brand-pink { color: #D56B8D; }
        body { background-color: #FFF4F2 !important; }
    </style>

    <section class="schedule-page font-sans text-gray-800 min-h-screen pb-20 px-4 md:px-8">

        {{-- Title --}}
        <header class="text-center pt-10 md:pt-14 mb-8">
            <h1 class="text-3xl md:text-4xl font-serif font-semibold text-gray-900">Schedule</h1>
        </header>

        {{-- Calendar --}}
        <div class="max-w-6xl mx-auto">
            <div x-data="scheduleApp()" @schedule:title.window="title = $event.detail">

                {{-- Toolbar --}}
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-5 px-1">
                    {{-- Month nav (kiri) --}}
                    <div class="flex items-center gap-3">
                        <button type="button" @click="prev()"
                                class="w-8 h-8 inline-flex items-center justify-center rounded-full bg-[#FBD6DF] text-[#D56B8D] hover:bg-[#f5bccb] transition shadow-sm">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
                        </button>
                        <h2 class="font-serif text-2xl md:text-3xl font-medium text-gray-900 min-w-[120px] text-center" x-text="title"></h2>
                        <button type="button" @click="next()"
                                class="w-8 h-8 inline-flex items-center justify-center rounded-full bg-[#FBD6DF] text-[#D56B8D] hover:bg-[#f5bccb] transition shadow-sm">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                        </button>
                    </div>

                    {{-- Filter + view (kanan) --}}
                    <div class="flex items-center gap-2 flex-wrap">
                        <select x-model="nailistId" @change="reload()"
                                class="rounded-full border border-[#eedde2] bg-white pl-4 pr-9 py-2 text-sm focus:border-[#D56B8D] focus:ring-[#D56B8D] cursor-pointer appearance-none bg-no-repeat bg-[length:14px] bg-[right_12px_center]"
                                style='background-image: url("data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%23D56B8D%22 stroke-width=%222.5%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22%3E%3Cpolyline points=%226 9 12 15 18 9%22/%3E%3C/svg%3E");'>
                            <option value="">All Nail Artists</option>
                            @foreach ($nailists as $n)
                                <option value="{{ $n['id'] }}">{{ $n['name'] }}</option>
                            @endforeach
                        </select>

                        <div class="inline-flex rounded-full bg-white border border-[#eedde2] p-1 text-xs font-medium">
                            <button type="button" @click="setView('dayGridMonth')"
                                    :class="view === 'dayGridMonth' ? 'bg-[#D56B8D] text-white shadow-sm' : 'text-gray-500'"
                                    class="px-3 py-1.5 rounded-full transition">Month</button>
                            <button type="button" @click="setView('timeGridWeek')"
                                    :class="view === 'timeGridWeek' ? 'bg-[#D56B8D] text-white shadow-sm' : 'text-gray-500'"
                                    class="px-3 py-1.5 rounded-full transition">Week</button>
                            <button type="button" @click="setView('listWeek')"
                                    :class="view === 'listWeek' ? 'bg-[#D56B8D] text-white shadow-sm' : 'text-gray-500'"
                                    class="px-3 py-1.5 rounded-full transition">List</button>
                        </div>

                        @if ($canExport)
                            <a id="exportBtn" href="#"
                               class="inline-flex items-center gap-1.5 rounded-full border border-[#D56B8D] bg-white px-4 py-2 text-sm font-medium text-[#D56B8D] hover:bg-[#FFF0F4]">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"/></svg>
                                Export CSV
                            </a>
                        @endif
                    </div>
                </div>

                {{-- Calendar container --}}
                <div class="rounded-3xl bg-card-bg border border-[#f5e0e6] overflow-hidden shadow-[0_8px_28px_-12px_rgba(213,107,141,0.20)]">
                    <div id="calendar" class="schedule-calendar"></div>
                </div>

                {{-- Legend --}}
                @if ($nailists->isNotEmpty())
                    <div class="flex flex-wrap items-center gap-2 mt-5 px-1">
                        <p class="text-xs text-gray-400 uppercase tracking-wide mr-1">Legend:</p>
                        @foreach ($nailists as $n)
                            <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium"
                                  style="background: {{ $n['color']['bg'] }}; color: {{ $n['color']['fg'] }};">
                                <span class="w-1.5 h-1.5 rounded-full" style="background: {{ $n['color']['fg'] }};"></span>
                                {{ $n['name'] }}
                            </span>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </section>

    {{-- Modal Detail --}}
    <div id="detailModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md max-h-[90vh] overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-[#f5e0e6] flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold tracking-widest text-[#D56B8D] uppercase">Detail Booking</p>
                    <h3 id="detailTitle" class="text-lg font-semibold text-gray-900 mt-0.5"></h3>
                </div>
                <button type="button" id="closeDetail" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div id="detailBody" class="px-6 py-5 overflow-y-auto text-sm space-y-4"></div>
        </div>
    </div>

    {{-- FullCalendar v6 --}}
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>

    <style>
        .schedule-calendar { font-family: 'Poppins', sans-serif; }
        .schedule-calendar .fc { --fc-border-color: #f5e0e6; --fc-today-bg-color: #FFF5F6; --fc-page-bg-color: #ffffff; }

        /* Hari header — pink bar */
        .schedule-calendar .fc-col-header { background: #FBD6DF; }
        .schedule-calendar .fc-col-header-cell { padding: 14px 0; border: none !important; }
        .schedule-calendar .fc-col-header-cell-cushion {
            color: #8b3a5a; font-weight: 500; text-decoration: none; font-size: 0.95rem;
            font-family: 'Playfair Display', serif; letter-spacing: 0.5px;
        }

        /* Day cells */
        .schedule-calendar .fc-daygrid-day { background: #ffffff; }
        .schedule-calendar .fc-daygrid-day-frame { padding: 4px; min-height: 110px; }
        .schedule-calendar .fc-daygrid-day-top { flex-direction: row; justify-content: flex-end; padding: 4px 8px 0; }
        .schedule-calendar .fc-daygrid-day-number {
            color: #94a3b8; font-weight: 400; font-size: 0.8rem; text-decoration: none; padding: 0;
        }
        .schedule-calendar .fc-day-today .fc-daygrid-day-number {
            color: #fff !important; background: #D56B8D; border-radius: 999px;
            width: 22px; height: 22px; display: inline-flex; align-items: center; justify-content: center;
            font-weight: 600;
        }
        .schedule-calendar .fc-day-other .fc-daygrid-day-number { color: #cbd5e1; }
        .schedule-calendar .fc-scrollgrid { border: none !important; }
        .schedule-calendar .fc-scrollgrid td, .schedule-calendar .fc-scrollgrid th { border-color: #f5e0e6 !important; }

        /* Events — horizontal pill */
        .schedule-calendar .fc-event {
            background: transparent !important; border: none !important;
            margin: 2px 4px !important; padding: 0 !important; cursor: pointer;
            box-shadow: none !important;
        }
        .schedule-calendar .fc-event-main { padding: 0 !important; }
        .schedule-calendar .fc-h-event .fc-event-main, .schedule-calendar .fc-event-time, .schedule-calendar .fc-event-title { color: inherit !important; }
        .schedule-calendar .fc-daygrid-event { white-space: normal !important; align-items: stretch !important; }
        .schedule-calendar .fc-daygrid-event-dot { display: none; }

        /* Custom event card */
        .ev-card {
            display: flex; align-items: stretch; gap: 0;
            border-radius: 6px; overflow: hidden; min-height: 28px;
            transition: opacity 0.15s, transform 0.15s;
        }
        .ev-card:hover { opacity: 0.88; transform: translateY(-1px); }
        .ev-card .ev-strip { width: 4px; flex-shrink: 0; }
        .ev-card .ev-body { padding: 3px 7px; flex: 1; min-width: 0; line-height: 1.2; }
        .ev-card .ev-name { font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.3px; opacity: 0.85; }
        .ev-card .ev-time { font-size: 11px; font-weight: 500; margin-top: 1px; }

        /* "+ N more" link */
        .schedule-calendar .fc-daygrid-more-link {
            font-size: 10px; color: #D56B8D; font-weight: 600; padding: 2px 6px;
            background: #FFF0F4; border-radius: 4px; margin: 2px 4px; display: block;
        }
        .schedule-calendar .fc-daygrid-more-link:hover { background: #FBD6DF; }

        /* Week / Time grid view */
        .schedule-calendar .fc-timegrid-event { border-radius: 6px !important; padding: 4px 6px; }
        .schedule-calendar .fc-timegrid-event .ev-card { min-height: 100%; }

        /* List view */
        .schedule-calendar .fc-list-day-cushion { background: #FFF0F4 !important; color: #D56B8D; font-weight: 600; }
        .schedule-calendar .fc-list-event:hover td { background: #FFF5F6 !important; }
        .schedule-calendar .fc-list-event-time, .schedule-calendar .fc-list-event-title { color: #475569; }
        .schedule-calendar .fc-list-empty-cushion { background: #FFF0F4 !important; color: #D56B8D; }

        .schedule-calendar .fc-view-harness { background: #ffffff; }
    </style>

    <script>
        function scheduleApp() {
            return {
                nailistId: '',
                view: 'dayGridMonth',
                title: '',
                prev() { window.scheduleCalendar?.prev(); },
                next() { window.scheduleCalendar?.next(); },
                setView(v) {
                    this.view = v;
                    window.scheduleCalendar?.changeView(v);
                },
                reload() {
                    window.scheduleCalendar?.refetchEvents();
                    updateExportLink();
                },
            }
        }

        function updateExportLink() {
            const btn = document.getElementById('exportBtn');
            if (!btn || !window.scheduleCalendar) return;
            const view = window.scheduleCalendar.view;
            const url = new URL(@json(route('schedule.export')));
            url.searchParams.set('start', view.activeStart.toISOString());
            url.searchParams.set('end', view.activeEnd.toISOString());
            const nailistId = document.querySelector('[x-model="nailistId"]')?.value || '';
            if (nailistId) url.searchParams.set('nailist_id', nailistId);
            btn.href = url.toString();
        }

        document.addEventListener('DOMContentLoaded', () => {
            const calendarEl = document.getElementById('calendar');
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'id',
                firstDay: 0,
                height: 'auto',
                fixedWeekCount: true,
                headerToolbar: false,
                dayMaxEvents: 4,
                allDaySlot: false,
                slotMinTime: '08:00:00',
                slotMaxTime: '22:00:00',
                noEventsText: 'Belum ada jadwal pada periode ini.',
                dayHeaderFormat: { weekday: 'short' },

                events: function (info, success, failure) {
                    const params = new URLSearchParams({ start: info.startStr, end: info.endStr });
                    const nid = document.querySelector('[x-model="nailistId"]')?.value;
                    if (nid) params.append('nailist_id', nid);
                    const url = @json(route('schedule.events')) + '?' + params.toString();
                    fetch(url)
                        .then(r => { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
                        .then(data => success(data))
                        .catch(err => {
                            console.error('[Schedule] Failed to load events:', err);
                            window.toast?.('error', 'Gagal Memuat Jadwal', err.message);
                            failure(err);
                        });
                },

                eventContent: function (arg) {
                    const p = arg.event.extendedProps;
                    const bg = arg.event.backgroundColor || '#FFF0F4';
                    const fg = arg.event.textColor || '#D56B8D';
                    const name = (p.nailist_name || '').split(' ').map(w => w[0]).join('').slice(0, 2).toUpperCase();
                    const time = p.time_label || '';
                    return {
                        html: `
                            <div class="ev-card" style="background:${bg}; color:${fg};">
                                <div class="ev-strip" style="background:${fg};"></div>
                                <div class="ev-body">
                                    <p class="ev-name">${name}</p>
                                    <p class="ev-time">${time}</p>
                                </div>
                            </div>
                        `
                    };
                },

                datesSet: (info) => {
                    const fmt = new Intl.DateTimeFormat('id-ID', { month: 'long', year: 'numeric' });
                    const newTitle = fmt.format(info.view.currentStart);
                    window.dispatchEvent(new CustomEvent('schedule:title', { detail: newTitle }));
                    updateExportLink();
                },

                eventClick: (info) => {
                    info.jsEvent.preventDefault();
                    showDetail(info.event);
                },
            });

            calendar.render();
            window.scheduleCalendar = calendar;

            const modal     = document.getElementById('detailModal');
            const titleEl   = document.getElementById('detailTitle');
            const bodyEl    = document.getElementById('detailBody');
            const showModal = () => { modal.classList.remove('hidden'); modal.classList.add('flex'); };
            const hideModal = () => { modal.classList.add('hidden'); modal.classList.remove('flex'); };
            document.getElementById('closeDetail').addEventListener('click', hideModal);
            modal.addEventListener('click', (e) => { if (e.target === modal) hideModal(); });
            document.addEventListener('keydown', (e) => { if (e.key === 'Escape') hideModal(); });

            function escapeHtml(s) {
                return String(s ?? '')
                    .replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;')
                    .replaceAll('"', '&quot;').replaceAll("'", '&#039;');
            }

            function statusBadge(status) {
                const palette = {
                    'pending'      : 'bg-amber-50 text-amber-700',
                    'dikonfirmasi' : 'bg-sky-50 text-sky-700',
                    'diproses'     : 'bg-indigo-50 text-indigo-700',
                    'sukses'       : 'bg-emerald-50 text-emerald-700',
                    'dibatalkan'   : 'bg-rose-50 text-rose-700',
                };
                const cls = palette[String(status).toLowerCase()] || 'bg-slate-100 text-slate-600';
                return `<span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ${cls}">${escapeHtml(status)}</span>`;
            }

            function showDetail(event) {
                const p = event.extendedProps;
                titleEl.textContent = `${p.nailist_name} · ${p.treatment_name}`;

                let html = `
                    <div class="grid grid-cols-2 gap-x-4 gap-y-3">
                        <div>
                            <p class="text-xs uppercase tracking-wide text-gray-400">Tanggal</p>
                            <p class="text-gray-800 mt-0.5">${escapeHtml(p.date_label)}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wide text-gray-400">Waktu</p>
                            <p class="text-gray-800 mt-0.5 font-mono">${escapeHtml(p.time_label)}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wide text-gray-400">Nailist</p>
                            <p class="text-gray-800 mt-0.5 font-medium">${escapeHtml(p.nailist_name)}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wide text-gray-400">Treatment</p>
                            <p class="text-gray-800 mt-0.5">${escapeHtml(p.treatment_name)}</p>
                        </div>
                        <div class="col-span-2">
                            <p class="text-xs uppercase tracking-wide text-gray-400">Status</p>
                            <div class="mt-0.5">${statusBadge(p.status)}</div>
                        </div>
                    </div>
                `;

                if (p.has_private) {
                    html += `
                        <div class="border-t border-[#f5e0e6] pt-4 mt-2">
                            <p class="text-xs font-semibold tracking-widest text-[#D56B8D] uppercase mb-3">Detail Privat</p>
                            <div class="grid grid-cols-2 gap-x-4 gap-y-3">
                                <div><p class="text-xs uppercase tracking-wide text-gray-400">Customer</p><p class="text-gray-800 mt-0.5">${escapeHtml(p.customer)}</p></div>
                                <div><p class="text-xs uppercase tracking-wide text-gray-400">No. HP</p><p class="text-gray-800 mt-0.5 font-mono">${escapeHtml(p.phone)}</p></div>
                                <div><p class="text-xs uppercase tracking-wide text-gray-400">Total</p><p class="text-[#D56B8D] mt-0.5 font-semibold">${escapeHtml(p.total)}</p></div>
                                ${p.referensi ? `<div class="col-span-2"><p class="text-xs uppercase tracking-wide text-gray-400">Referensi Desain</p><a href="${escapeHtml(p.referensi)}" target="_blank" rel="noopener" class="text-[#D56B8D] mt-0.5 break-all hover:underline">${escapeHtml(p.referensi)}</a></div>` : ''}
                            </div>
                        </div>
                    `;
                } else {
                    html += `
                        <div class="border-t border-[#f5e0e6] pt-3 mt-2">
                            <p class="text-xs text-gray-400 italic">Detail customer hanya bisa dilihat oleh staff. <a href="{{ route('login') }}" class="text-[#D56B8D] hover:underline">Login</a> jika Anda admin atau nailist.</p>
                        </div>
                    `;
                }

                bodyEl.innerHTML = html;
                showModal();
            }
        });
    </script>
</x-layouts.landing>
