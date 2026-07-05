<div>
    {{-- ============== FOOTER / LOCATED @ ============== --}}
        @php
            // Ambil data khusus untuk footer langsung dari database agar aman dari error scope
            $footerData = \App\Models\WebSetting::whereIn('key', [
                'location_address', 'location_hours', 'contact_whatsapp', 
                'whatsapp_url', 'instagram_handle', 'instagram_url', 'location_map_embed'
            ])->pluck('value', 'key')->toArray();

            $fGet = fn($key, $default = '') => $footerData[$key] ?? $default;
        @endphp

        <footer class="max-w-6xl mx-auto px-4 py-16 md:py-24 border-t border-[#FFE5DD] mt-10">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
                
                {{-- KIRI: Informasi Lokasi & Kontak --}}
                <div class="space-y-8">
                    {{-- Alamat --}}
                    <div>
                        <h2 class="font-serif-eb text-4xl md:text-5xl text-[#DB7094] mb-4">
                            We're Located at
                        </h2>
                        <p class="text-lg text-[#202020] uppercase tracking-wide">
                            {{ $fGet('location_address', 'GRIYA MULAWARMAN INDAH, BLOK G12-A') }}
                        </p>
                    </div>

                    {{-- Jam Operasional --}}
                    <div>
                        <h3 class="text-sm font-bold text-[#DB7094] tracking-widest uppercase mb-2">
                            HOURS:
                        </h3>
                        <p class="text-lg text-[#202020] uppercase">
                            {{ $fGet('location_hours', 'EVERYDAY 09.00 - 20.00') }}
                        </p>
                    </div>

                    {{-- Kontak --}}
                    <div>
                        <h3 class="text-sm font-bold text-[#DB7094] tracking-widest uppercase mb-3">
                            CONTACTS:
                        </h3>
                        <div class="space-y-4">
                            {{-- WhatsApp --}}
                            <a href="{{ $fGet('whatsapp_url', '#') }}" target="_blank" class="flex items-center gap-3 text-[#202020] hover:text-[#DB7094] transition w-max">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                                </svg>
                                <span class="text-lg tracking-wide">{{ $fGet('contact_whatsapp', '0822-2395-8248') }}</span>
                            </a>
                            
                            {{-- Instagram / TikTok --}}
                            <a href="{{ $fGet('instagram_url', '#') }}" target="_blank" class="flex items-center gap-3 text-[#202020] hover:text-[#DB7094] transition w-max">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect>
                                    <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path>
                                    <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line>
                                </svg>
                                <span class="text-lg tracking-wide">{{ $fGet('instagram_handle', '@nailby.hilda') }}</span>
                            </a>
                        </div>
                    </div>
                </div>

                {{-- KANAN: Google Maps --}}
                <div class="w-full h-[300px] md:h-[400px] rounded-[2rem] overflow-hidden shadow-coquette-lg bg-[#FFE5DD] border-4 border-white">
                    <iframe 
                        src="{{ $fGet('location_map_embed', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3956.273767851949!2d109.255555!3d-7.434444!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zN8KwMjYnMDQuMCJTIDEwOcKwMTUnMjAuMCJF!5e0!3m2!1sen!2sid') }}" 
                        width="100%" 
                        height="100%" 
                        style="border:0;" 
                        allowfullscreen="" 
                        loading="lazy" 
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
            </div>

            {{-- Copyright --}}
            <div class="mt-16 text-center text-sm text-slate-400">
                &copy; {{ date('Y') }} nailby.hilda. All rights reserved.
            </div>
        </footer>
</div>
