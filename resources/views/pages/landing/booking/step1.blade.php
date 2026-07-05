<x-layouts.landing title="Book — Select Date & Time">
    <style>
        body { background-color: #FFF4F2 !important; }
    </style>
    <main class="min-h-screen pb-20">
        <div class="max-w-4xl mx-auto px-4 pt-10">

            {{-- Title --}}
            <h1 class="text-center text-3xl md:text-4xl text-slate-900" style="font-family: 'Cormorant Garamond', serif;">
                Select Date &amp; Time
            </h1>

            {{-- Stepper --}}
            <div class="mt-6 mb-10">
                <x-booking.stepper :current="1" />
            </div>

            @if ($errors->any())
                <div class="mb-5 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('booking.step1.submit') }}" class="grid grid-cols-1 md:grid-cols-2 gap-6"
                  x-data="bookingStep1(@js($draft ?? []))">
                @csrf
                <input type="hidden" name="tanggal" :value="selectedDate">
                <input type="hidden" name="jam" :value="jam">

                {{-- Calendar card --}}
                <div class="rounded-2xl bg-white p-5 md:p-6 shadow-[0_4px_15px_rgba(205,163,173,0.12)]">
                    <div class="flex items-center justify-between mb-4">
                        <p class="font-semibold text-slate-900" x-text="monthLabel"></p>
                        <div class="flex items-center gap-1">
                            <button type="button" @click="prevMonth()" class="w-7 h-7 inline-flex items-center justify-center rounded-full text-slate-500 hover:bg-[#fdf2f5]">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
                            </button>
                            <button type="button" @click="nextMonth()" class="w-7 h-7 inline-flex items-center justify-center rounded-full text-slate-500 hover:bg-[#fdf2f5]">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-7 gap-1 text-center">
                        @foreach (['Mo','Tu','We','Th','Fr','Sa','Su'] as $d)
                            <p class="text-xs text-slate-400 font-medium py-1.5">{{ $d }}</p>
                        @endforeach
                        <template x-for="day in days" :key="day.key">
                            <button type="button"
                                    @click="day.isCurrent && !day.isPast && (selectedDate = day.iso)"
                                    :disabled="!day.isCurrent || day.isPast"
                                    :class="[
                                        'aspect-square text-sm rounded-full inline-flex items-center justify-center transition w-9 h-9',
                                        !day.isCurrent ? 'text-slate-300 cursor-default' : '',
                                        day.isPast && day.isCurrent ? 'text-slate-300 cursor-not-allowed' : '',
                                        selectedDate === day.iso ? 'bg-[#a23f66] text-white font-semibold' :
                                            (day.isCurrent && !day.isPast ? 'text-slate-700 hover:bg-[#fdf2f5]' : '')
                                    ]"
                                    x-text="day.day"></button>
                        </template>
                    </div>
                </div>

                {{-- Right column: Nail Artists + Times --}}
                <div class="space-y-6">
                    {{-- Available Nail Artists --}}
                    <div>
                        <p class="font-semibold text-slate-900 mb-3">Available Nail Artists</p>
                        <div class="flex flex-wrap gap-2">
                            @forelse ($nailists as $n)
                                <label class="cursor-pointer">
                                    <input type="radio" name="nailist_id" value="{{ $n['id'] }}" class="sr-only peer" x-model="nailistId" required>
                                    <div class="inline-flex items-center gap-2 rounded-full bg-white border border-[#eedde2] pl-1 pr-4 py-1 text-sm text-slate-700
                                                peer-checked:border-[#a23f66] peer-checked:text-[#a23f66] peer-checked:font-medium transition">
                                        <img src="{{ $n['avatar'] }}" alt="" class="w-7 h-7 rounded-full object-cover">
                                        {{ $n['name'] }}
                                    </div>
                                </label>
                            @empty
                                <p class="text-sm text-slate-500 italic">Belum ada nailist.</p>
                            @endforelse
                        </div>
                    </div>

                    {{-- Available Times --}}
                    <div>
                        <p class="font-semibold text-slate-900 mb-3">Available Times</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach (['10:00','12:00','14:00','16:00'] as $t)
                                <label class="cursor-pointer">
                                    <input type="radio" value="{{ $t }}" class="sr-only peer" x-model="jam">
                                    <div class="rounded-full bg-white border border-[#eedde2] px-5 py-2 text-sm text-slate-700
                                                peer-checked:border-[#a23f66] peer-checked:bg-[#fdf2f5] peer-checked:text-[#a23f66] peer-checked:font-medium transition">
                                        {{ str_replace(':', '.', $t) }}
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Submit --}}
                    <button type="submit"
                            :disabled="!selectedDate || !nailistId || !jam"
                            class="w-full mt-2 rounded-lg bg-[#DF7D9E] text-white font-semibold px-4 py-3 disabled:opacity-50 disabled:cursor-not-allowed hover:bg-[#df8da0] transition shadow-sm"
                            style="font-family: 'Cormorant Garamond', serif;">
                        Next
                    </button>
                </div>
            </form>
        </div>
    </main>

    <script>
        function bookingStep1(draft) {
            return {
                cursor: new Date(),
                selectedDate: draft.tanggal || '',
                nailistId: draft.nailist_id || '',
                jam: draft.jam || '',

                get monthLabel() {
                    return new Intl.DateTimeFormat('id-ID', { month: 'long', year: 'numeric' }).format(this.cursor);
                },

                get days() {
                    const year = this.cursor.getFullYear();
                    const month = this.cursor.getMonth();
                    const firstDay = new Date(year, month, 1);
                    let startDow = firstDay.getDay();
                    startDow = startDow === 0 ? 6 : startDow - 1;
                    const startCal = new Date(year, month, 1 - startDow);
                    const today = new Date(); today.setHours(0,0,0,0);

                    const pad = (n) => String(n).padStart(2, '0');
                    const arr = [];
                    for (let i = 0; i < 42; i++) {
                        const d = new Date(startCal);
                        d.setDate(startCal.getDate() + i);
                        const isCurrent = d.getMonth() === month;
                        const isPast = d < today;
                        const iso = `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
                        arr.push({ key: iso+'-'+i, day: d.getDate(), iso, isCurrent, isPast });
                    }
                    return arr;
                },

                prevMonth() { this.cursor = new Date(this.cursor.getFullYear(), this.cursor.getMonth() - 1, 1); },
                nextMonth() { this.cursor = new Date(this.cursor.getFullYear(), this.cursor.getMonth() + 1, 1); },
            }
        }
    </script>
</x-layouts.landing>
