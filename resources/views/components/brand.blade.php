@props(['href' => null, 'light' => false])

<a href="{{ $href ?? route('home') }}" {{ $attributes->class(['brand', 'brand--light' => $light]) }} aria-label="FrozeMate — beranda">
    <svg class="brand__mark" viewBox="0 0 34 34" aria-hidden="true">
        <rect width="34" height="34" rx="10" fill="{{ $light ? '#ffd24a' : '#0c2531' }}"/>
        <g stroke="{{ $light ? '#0c2531' : '#ffd24a' }}" stroke-width="2.6" stroke-linecap="round" fill="none">
            <path d="M17 6.5v21M7.9 11.75l18.2 10.5M26.1 11.75L7.9 22.25"/>
            <path d="M14 8.6l3 2.6 3-2.6M14 25.4l3-2.6 3 2.6" stroke-linejoin="round"/>
        </g>
        <circle cx="17" cy="17" r="3.2" fill="#f2501c"/>
    </svg>
    <span class="brand__name">Froze<span>Mate</span></span>
</a>
