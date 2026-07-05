<x-layouts.landing title="Book — Review Order">
    @php
        $rupiah = fn ($n) => 'Rp '.number_format((float) $n, 0, ',', '.');

        // Meta untuk perhitungan "Your Order" reaktif di Alpine.
        $treatmentMeta = $categories
            ->flatMap->activeTreatments
            ->mapWithKeys(fn ($t) => [$t->id => ['name' => $t->nama_jasa, 'price' => (float) $t->price_min]]);
        $charmMeta = $charms
            ->mapWithKeys(fn ($c) => [$c->id => ['name' => $c->nama_charm, 'price' => (float) $c->harga]]);

        $initialTreatments = $draft['treatments'] ?? [];
        $draftCharms = collect($draft['charms'] ?? []);
        $initialCharms = $charms->mapWithKeys(fn ($c) => [
            $c->id => (int) ($draftCharms->firstWhere('id', $c->id)['qty'] ?? 0),
        ]);
    @endphp

    <style>
        body { background-color: #FFF4F2 !important; }
    </style>

    <main class=" min-h-screen pb-20">
        <div class="max-w-6xl mx-auto px-4 pt-10">

            {{-- Title --}}
            <h1 class="text-center text-3xl md:text-4xl text-slate-900" style="font-family: 'Cormorant Garamond', serif;">
                Review Order
            </h1>

            {{-- Stepper --}}
            <div class="mt-6 mb-10">
                <x-booking.stepper :current="2" />
            </div>

            @if ($errors->any())
                <div class="mb-5 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('booking.step2.submit') }}" enctype="multipart/form-data"
                  class="grid grid-cols-1 lg:grid-cols-3 gap-6"
                  x-data="bookingReview({
                      treatmentMeta:     @js($treatmentMeta),
                      charmMeta:         @js($charmMeta),
                      initialTreatments: @js($initialTreatments),
                      initialCharms:     @js($initialCharms),
                  })">
                @csrf

                {{-- LEFT: Form sections (no card wrapper, lebih flat per design) --}}
                <div class="lg:col-span-2 space-y-8">
                    {{-- Categories --}}
                    @foreach ($categories as $cat)
                        <div>
                            <p class="font-semibold text-slate-900 mb-3" style="font-family: 'Cormorant Garamond', serif;">
                                {{ $cat->name }} <span class="text-rose-500">*</span>
                            </p>
                            <div class="space-y-2">
                                @foreach ($cat->activeTreatments as $t)
                                    <label class="flex items-center justify-between cursor-pointer py-1.5">
                                        <div class="flex items-center gap-3">
                                            <input type="checkbox" name="treatments[]" value="{{ $t->id }}"
                                                   x-model="selectedTreatments"
                                                   class="rounded border-slate-300 text-[#a23f66] focus:ring-[#a23f66]">
                                            <span class="text-sm text-slate-700">{{ $t->nama_jasa }}</span>
                                        </div>
                                        <span class="text-sm text-slate-700 whitespace-nowrap">{{ $t->price_label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- RIGHT: Sidebar --}}
                <div class="lg:col-span-1 space-y-5">
                    {{-- Insert your inspo --}}
                    <div class="bg-white rounded-2xl p-5 shadow-[0_4px_15px_rgba(205,163,173,0.1)]">
                        <p class="font-semibold text-slate-900 mb-1">Insert your inspo</p>
                        <p class="text-xs text-slate-500 mb-3">Upload referensi desain (opsional)</p>
                        <label class="relative flex flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed border-[#eedde2] bg-[#fdf8fa] py-8 cursor-pointer hover:bg-[#fdf2f5] overflow-hidden">
                            {{-- Preview gambar --}}
                            <template x-if="preview">
                                <img :src="preview" alt="Preview" class="absolute inset-0 w-full h-full object-cover">
                            </template>
                            <div x-show="!preview" class="flex flex-col items-center gap-2">
                                <svg class="w-10 h-10 text-[#d8b8c2]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4-4 4 4M12 12v8M20 16V8a4 4 0 00-4-4H8a4 4 0 00-4 4v8"/>
                                </svg>
                                <p class="text-xs text-slate-600">Choose a file or drag &amp; drop it here</p>
                                <p class="text-[10px] text-slate-400">JPG, PNG up to 5 MB</p>
                            </div>
                            <input type="file" name="referensi_desain" accept="image/*" class="hidden" @change="onFile($event)">
                        </label>
                        <p x-show="filename" x-text="filename" class="mt-2 text-xs text-slate-500 truncate"></p>
                        @error('referensi_desain')
                            <p class="mt-2 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Charms (optional) --}}
                    @if ($charms->isNotEmpty())
                        <div class="bg-white rounded-2xl p-5 shadow-[0_4px_15px_rgba(205,163,173,0.1)]">
                            <p class="font-semibold text-slate-900 mb-2" style="font-family: 'Cormorant Garamond', serif;">Add Charms (optional)</p>
                            <p class="text-xs text-slate-500 mb-3">Tambah aksesoris untuk nail art kamu.</p>
                            <div class="space-y-2 max-h-56 overflow-y-auto pr-1">
                                @foreach ($charms as $i => $charm)
                                    <div class="flex items-center justify-between gap-2">
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm text-slate-700 truncate">{{ $charm->nama_charm }}</p>
                                            <p class="text-xs text-slate-500">Rp {{ number_format((float) $charm->harga, 0, ',', '.') }}
                                                · <span class="{{ $charm->stok === 0 ? 'text-rose-500' : ($charm->stok <= 5 ? 'text-amber-600' : 'text-slate-400') }}">
                                                    {{ $charm->stok === 0 ? 'Stok habis' : 'Stok: '.$charm->stok }}
                                                </span>
                                            </p>
                                        </div>
                                        <input type="hidden" name="charms[{{ $i }}][id]" value="{{ $charm->id }}">
                                        <input type="number" name="charms[{{ $i }}][qty]" min="0" max="{{ max(0, $charm->stok) }}"
                                               x-model.number="qtys['{{ $charm->id }}']"
                                               class="w-16 rounded border-[#eedde2] text-sm text-center"
                                               {{ $charm->stok === 0 ? 'disabled' : '' }}>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Your Order --}}
                    <div class="bg-white rounded-2xl p-5 shadow-[0_4px_15px_rgba(205,163,173,0.1)] sticky top-6">
                        <p class="font-semibold text-slate-900 mb-4" style="font-family: 'Cormorant Garamond', serif;">Your Order</p>

                        <div class="space-y-2 text-sm">
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500">Selected Date</span>
                                <span class="text-slate-800 font-medium">
                                    {{ $summary['tanggal'] ? \Carbon\Carbon::parse($summary['tanggal'])->translatedFormat('d/m/Y') : '—' }}
                                    {{ $summary['jam'] ? '· '.str_replace(':', '.', $summary['jam']) : '' }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500">Nail Artist</span>
                                <span class="text-slate-800 font-medium">{{ $summary['nailist']?->user?->full_name ?? '—' }}</span>
                            </div>
                        </div>

                        <div class="border-t border-[#f5e7ec] mt-4 pt-3 space-y-1.5 text-sm">
                            <template x-if="items.length === 0">
                                <p class="text-slate-400 italic text-xs">No items selected yet.</p>
                            </template>
                            <template x-for="(it, idx) in items" :key="idx">
                                <div class="flex items-center justify-between"
                                     :class="it.charm ? 'text-xs text-slate-500' : 'text-slate-700'">
                                    <span x-text="it.label"></span>
                                    <span x-text="rupiah(it.amount)"></span>
                                </div>
                            </template>
                        </div>

                        <div class="border-t border-[#f5e7ec] mt-4 pt-4 flex items-center justify-between">
                            <span class="text-sm font-semibold text-slate-700">Estimated Total Price</span>
                            <span class="font-bold text-[#a23f66]" x-text="rupiah(total)"></span>
                        </div>

                        <button type="submit" class="w-full mt-4 rounded-lg bg-[#DF7D9E] text-white font-semibold px-4 py-3 hover:bg-[#df8da0] transition shadow-sm"
                                style="font-family: 'Cormorant Garamond', serif;">
                            Next
                        </button>

                        <a href="{{ route('booking.step1') }}"
                           class="mt-2 flex items-center justify-center gap-1.5 w-full rounded-lg border border-[#eedde2] bg-white px-4 py-2.5 text-sm font-medium text-slate-600 hover:bg-[#fdf2f5] transition">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
                            Back
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </main>

    <script>
        function bookingReview(cfg) {
            return {
                treatmentMeta:      cfg.treatmentMeta,
                charmMeta:          cfg.charmMeta,
                selectedTreatments: cfg.initialTreatments,
                qtys:               cfg.initialCharms,
                filename: '',
                preview: '',

                rupiah(n) {
                    return 'Rp ' + Number(n || 0).toLocaleString('id-ID');
                },

                get items() {
                    const out = [];
                    this.selectedTreatments.forEach((id) => {
                        const m = this.treatmentMeta[id];
                        if (m) out.push({ label: m.name, amount: m.price, charm: false });
                    });
                    Object.entries(this.qtys).forEach(([id, qty]) => {
                        const q = Number(qty) || 0;
                        const m = this.charmMeta[id];
                        if (m && q > 0) out.push({ label: `${m.name} (${q}x)`, amount: m.price * q, charm: true });
                    });
                    return out;
                },

                get total() {
                    return this.items.reduce((sum, it) => sum + it.amount, 0);
                },

                onFile(e) {
                    const file = e.target.files[0];
                    if (this.preview) URL.revokeObjectURL(this.preview);
                    this.filename = file?.name || '';
                    this.preview = file ? URL.createObjectURL(file) : '';
                },
            }
        }
    </script>
</x-layouts.landing>
