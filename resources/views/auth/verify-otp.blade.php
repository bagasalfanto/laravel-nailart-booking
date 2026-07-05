<x-guest-layout>
    @php
        // Mask email: joh**@gmail.com
        $maskedEmail = (function ($e) {
            if (! str_contains($e, '@')) return $e;
            [$local, $domain] = explode('@', $e, 2);
            $visible = mb_substr($local, 0, 3);
            return $visible.str_repeat('*', max(2, mb_strlen($local) - 3)).'@'.$domain;
        })($email);
    @endphp

    <div class="space-y-6 sm:space-y-8">
        {{-- Header --}}
        <div class="space-y-3 text-center">
            <h1 class="text-4xl sm:text-5xl text-slate-900" style="font-family: 'Cormorant Garamond', serif;">
                Code Verification
            </h1>
            <p class="text-base sm:text-lg text-slate-700">
                We have sent code to your email
                <span class="text-[#a8526b] font-medium">{{ $maskedEmail }}</span>
            </p>
        </div>

        {{-- Status messages --}}
        <div class="max-w-2xl mx-auto">
            @if (session('status'))
                <div class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('status') }}
                </div>
            @endif
            @if ($errors->any())
                <div class="mb-5 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- OTP form --}}
        <form method="POST" action="{{ route('verification.otp.verify') }}" class="space-y-6"
              x-data="otpForm()" @submit.prevent="onSubmit($event)">
            @csrf
            {{-- Hidden input pakai x-ref + tulis manual ke DOM untuk hindari race condition x-model. --}}
            <input type="hidden" name="code" x-ref="codeInput" value="">

            {{-- 6 separate input boxes --}}
            <div class="flex justify-center gap-2 sm:gap-3 max-w-2xl mx-auto" x-ref="container">
                @for ($i = 0; $i < 6; $i++)
                    <input type="text" inputmode="numeric" maxlength="1"
                           autocomplete="off"
                           x-on:input="onInput($event, {{ $i }})"
                           x-on:keydown="onKeydown($event, {{ $i }})"
                           x-on:paste="onPaste($event)"
                           x-ref="box{{ $i }}"
                           {{ $i === 0 ? 'autofocus' : '' }}
                           class="w-12 h-14 sm:w-16 sm:h-20 text-center text-2xl sm:text-3xl font-semibold text-slate-900 rounded-xl bg-white border border-[#efd1d6] outline-none transition focus:border-[#e4a9ba] focus:ring-2 focus:ring-[#f0c5d3]/70" />
                @endfor
            </div>

            <div class="max-w-2xl mx-auto">
                <button type="submit" id="verifyButton"
                        class="flex w-full items-center justify-center rounded-md bg-[#e8a9c2] px-4 py-3.5 text-base font-semibold text-white shadow-[0_10px_24px_rgba(232,169,194,0.35)] transition hover:bg-[#df97b3] focus:outline-none focus:ring-2 focus:ring-[#e8a9c2]/70 focus:ring-offset-2 focus:ring-offset-white"
                        style="font-family: 'Cormorant Garamond', serif;">
                    Verify Account
                </button>
            </div>

            <div class="text-center text-sm text-slate-700"
                 x-data="{ cooldown: {{ $cooldownSeconds }} }"
                 x-init="setInterval(() => { if (cooldown > 0) cooldown-- }, 1000)">
                Didn't receive code?
                <button type="button"
                        @click="document.getElementById('resendForm').submit()"
                        :disabled="cooldown > 0"
                        :class="cooldown > 0 ? 'text-slate-400 cursor-not-allowed' : 'text-[#ea7c9d] hover:underline font-medium'"
                        class="ml-1">
                    <span x-show="cooldown > 0">Resend in <span x-text="cooldown"></span>s</span>
                    <span x-show="cooldown === 0">Resend</span>
                </button>
            </div>
        </form>

        {{-- Hidden resend form --}}
        <form id="resendForm" method="POST" action="{{ route('verification.otp.resend') }}" class="hidden">
            @csrf
        </form>

        {{-- Logout link --}}
        <div class="text-center">
            <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button type="submit" class="text-xs text-slate-400 hover:text-slate-600">Bukan Anda? Keluar</button>
            </form>
        </div>
    </div>

    <script>
        function otpForm() {
            return {
                getValue() {
                    return Array.from(this.$refs.container.querySelectorAll('input')).map(i => i.value || '').join('');
                },
                /** Tulis langsung ke hidden input.value — synchronous, bypass Alpine reactivity. */
                syncToHidden() {
                    this.$refs.codeInput.value = this.getValue();
                },
                onSubmit(e) {
                    // Sinkronkan dulu, lalu submit native (lewati handler ini supaya tidak loop).
                    this.syncToHidden();
                    e.target.submit();
                },
                onInput(e, idx) {
                    e.target.value = e.target.value.replace(/[^0-9]/g, '').slice(0, 1);

                    if (e.target.value && idx < 5) {
                        this.$refs['box' + (idx + 1)].focus();
                    }
                    this.syncToHidden();

                    // Auto-submit saat 6 digit kelar.
                    if (this.$refs.codeInput.value.length === 6) {
                        this.$refs.codeInput.form.submit();
                    }
                },
                onKeydown(e, idx) {
                    if (e.key === 'Backspace' && ! e.target.value && idx > 0) {
                        this.$refs['box' + (idx - 1)].focus();
                    } else if (e.key === 'ArrowLeft' && idx > 0) {
                        this.$refs['box' + (idx - 1)].focus();
                    } else if (e.key === 'ArrowRight' && idx < 5) {
                        this.$refs['box' + (idx + 1)].focus();
                    }
                },
                onPaste(e) {
                    e.preventDefault();
                    const txt = (e.clipboardData || window.clipboardData).getData('text').replace(/[^0-9]/g, '').slice(0, 6);
                    if (! txt) return;
                    const inputs = this.$refs.container.querySelectorAll('input');
                    txt.split('').forEach((ch, i) => { if (inputs[i]) inputs[i].value = ch; });
                    this.syncToHidden();
                    inputs[Math.min(txt.length, 5)]?.focus();
                    if (this.$refs.codeInput.value.length === 6) {
                        this.$refs.codeInput.form.submit();
                    }
                },
            }
        }
    </script>
</x-guest-layout>
