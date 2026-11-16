@props(['daily'])

@php
    // $daily: koleksi ['date' => Carbon, 'revenue' => int, 'orders' => int] berurutan per hari.
    $max = max(1, $daily->max('revenue'));
    $peakIndex = $daily->search(fn ($day) => $day['revenue'] === $daily->max('revenue') && $day['revenue'] > 0);
    $count = $daily->count();
@endphp

<div {{ $attributes }}>
    <div class="bars" role="img" aria-label="Grafik omzet per hari, {{ $count }} hari terakhir. Tertinggi {{ rupiah($daily->max('revenue')) }}.">
        @foreach ($daily as $index => $day)
            @php $height = round($day['revenue'] / $max * 100, 1); @endphp
            <div @class(['bars__col', 'is-zero' => $day['revenue'] === 0, 'is-today' => $day['date']->isToday()])
                 style="--h: {{ $height }}%" tabindex="0">
                @if ($index === $peakIndex)
                    <span class="bars__peak">{{ rupiah($day['revenue']) }}</span>
                @endif
                <span class="bars__tip">
                    <small>{{ $day['date']->translatedFormat('l, d M') }}</small>
                    <b>{{ rupiah($day['revenue']) }}</b>
                    {{ $day['orders'] }} pesanan
                </span>
                <span class="bars__bar" style="height: var(--h)"></span>
            </div>
        @endforeach
    </div>

    <div class="bars-axis" aria-hidden="true">
        @foreach ($daily as $index => $day)
            <span @class(['is-today' => $day['date']->isToday()])>
                @if ($count <= 7)
                    {{ $day['date']->isToday() ? 'Hari ini' : $day['date']->translatedFormat('D d') }}
                @elseif ($count <= 14 || $index % 3 === ($count - 1) % 3)
                    {{ $day['date']->format('d/m') }}
                @endif
            </span>
        @endforeach
    </div>
</div>
