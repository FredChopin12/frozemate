@props(['art' => 'box'])

{{-- Ilustrasi isi kemasan. Warna mengikuti kelas .tone-* di elemen induk. --}}
<svg {{ $attributes->merge(['class' => 'pack__glyph']) }} viewBox="0 0 120 120" aria-hidden="true" focusable="false">
    @switch($art)
        @case('nugget')
            <rect class="fill outline" x="14" y="46" width="48" height="34" rx="15" transform="rotate(-14 38 63)"/>
            <rect class="fill outline" x="58" y="26" width="46" height="32" rx="14" transform="rotate(12 81 42)"/>
            <rect class="fill outline" x="50" y="66" width="52" height="34" rx="16" transform="rotate(5 76 83)"/>
            <path class="stroke" d="M28 60l6 2M40 68l5-3M72 38l6 1M86 46l4-4M66 80l6 2M82 88l6-3"/>
            @break

        @case('sosis')
            <g transform="rotate(-28 60 60)">
                <rect class="fill outline" x="8" y="34" width="104" height="24" rx="12"/>
                <rect class="fill outline" x="8" y="64" width="104" height="24" rx="12"/>
                <path class="stroke" d="M34 40l-6 12M52 40l-6 12M70 40l-6 12M88 40l-6 12M34 70l-6 12M52 70l-6 12M70 70l-6 12M88 70l-6 12"/>
            </g>
            @break

        @case('dimsum')
            <circle class="fill outline" cx="60" cy="60" r="40"/>
            <path class="stroke" d="M60 20c6 12 6 22 0 30M88 32c-4 12-10 19-20 22M100 60c-12 6-22 6-30 0M88 88c-12-4-19-10-22-20M60 100c-6-12-6-22 0-30M32 88c4-12 10-19 20-22M20 60c12-6 22-6 30 0M32 32c12 4 19 10 22 20"/>
            <circle class="outline" fill="#f58a3c" cx="60" cy="60" r="8"/>
            @break

        @case('bakso')
            <path class="stroke" d="M14 106L106 14"/>
            <circle class="fill outline" cx="38" cy="82" r="17"/>
            <circle class="fill outline" cx="60" cy="60" r="17"/>
            <circle class="fill outline" cx="82" cy="38" r="17"/>
            <path class="stroke" d="M30 77c2-4 5-6 9-6M52 55c2-4 5-6 9-6M74 33c2-4 5-6 9-6"/>
            @break

        @case('kentang')
            <rect class="fill outline" x="34" y="22" width="11" height="52" rx="3" transform="rotate(-10 39 48)"/>
            <rect class="fill outline" x="48" y="13" width="11" height="60" rx="3" transform="rotate(-3 53 44)"/>
            <rect class="fill outline" x="62" y="17" width="11" height="56" rx="3" transform="rotate(5 67 46)"/>
            <rect class="fill outline" x="76" y="26" width="11" height="48" rx="3" transform="rotate(12 81 50)"/>
            <path class="outline" fill="#e4573d" d="M26 58h68l-8 46a6 6 0 0 1-6 5H40a6 6 0 0 1-6-5z"/>
            <path fill="#fff" opacity=".85" d="M38 74h44l-1.4 8H39.4z"/>
            @break

        @case('ayam')
            <g transform="rotate(45 60 60)">
                <rect class="outline" fill="#fff" x="70" y="54" width="36" height="12" rx="4"/>
                <circle class="outline" fill="#fff" cx="108" cy="53" r="7"/>
                <circle class="outline" fill="#fff" cx="108" cy="67" r="7"/>
                <rect fill="#fff" x="96" y="55.5" width="12" height="9"/>
                <ellipse class="fill outline" cx="46" cy="60" rx="36" ry="27"/>
                <path class="stroke" d="M22 52c6-9 17-13 28-11"/>
            </g>
            @break

        @case('kudapan')
            <path class="fill outline" d="M14 84a46 46 0 0 1 92 0z"/>
            <path class="stroke" d="M24 64l7 5M36 50l5 7M52 42l2 8M68 42l-2 8M84 50l-5 7M96 64l-7 5"/>
            <path class="stroke" d="M22 96h76"/>
            @break

        @default
            <path class="fill outline" d="M20 44l40-18 40 18v40l-40 18-40-18z"/>
            <path class="stroke" d="M20 44l40 18 40-18M60 62v40M40 35l40 18"/>
    @endswitch
</svg>
