@php
    $flashes = collect([
        'success' => 'ph-fill ph-check-circle',
        'error' => 'ph-fill ph-warning-circle',
        'info' => 'ph-fill ph-info',
    ])->filter(fn ($icon, $type) => session()->has($type));
@endphp

@if ($flashes->isNotEmpty())
    <div class="toast-stack" role="status" aria-live="polite">
        @foreach ($flashes as $type => $icon)
            <div class="toast-fm toast-fm--{{ $type }}">
                <i class="ico {{ $icon }}"></i>
                <span>{{ session($type) }}</span>
                <button type="button" aria-label="Tutup"><i class="ph-bold ph-x"></i></button>
            </div>
        @endforeach
    </div>
@endif
