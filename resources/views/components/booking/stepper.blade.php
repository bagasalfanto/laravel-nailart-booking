@props(['current' => 1])

@php
    $steps = [
        ['n' => 1, 'label' => 'Select Date & Time'],
        ['n' => 2, 'label' => 'Review Order'],
        ['n' => 3, 'label' => 'Payment Details'],
    ];
@endphp

<div class="flex items-start justify-center max-w-2xl mx-auto">
    @foreach ($steps as $idx => $s)
        @php
            $isCurrent = $current === $s['n'];
            $isDone    = $current > $s['n'];
        @endphp

        <div class="flex flex-col items-center text-center" style="flex: 0 0 auto; width: 130px;">
            @if ($isCurrent || $isDone)
                <div class="w-12 h-12 rounded-full bg-[#a23f66] text-white flex items-center justify-center font-semibold text-base shadow-md">
                    {{ $isDone ? '✓' : $s['n'] }}
                </div>
                <p class="text-xs mt-2 {{ $isCurrent ? 'text-[#a23f66] font-semibold' : 'text-slate-600' }} leading-tight">{{ $s['label'] }}</p>
            @else
                <div class="w-12 h-12 rounded-full bg-white border-2 border-[#eedde2] text-slate-400 flex items-center justify-center font-semibold text-base">
                    {{ $s['n'] }}
                </div>
                <p class="text-xs mt-2 text-slate-400 leading-tight">{{ $s['label'] }}</p>
            @endif
        </div>

        @if ($idx < count($steps) - 1)
            <div class="flex-1 h-0.5 mt-6 {{ $current > $s['n'] ? 'bg-[#a23f66]' : 'bg-[#eedde2]' }}"></div>
        @endif
    @endforeach
</div>
