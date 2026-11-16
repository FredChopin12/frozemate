@if ($paginator->hasPages())
    <nav class="pager" aria-label="Halaman">
        @if ($paginator->onFirstPage())
            <span class="is-disabled" aria-hidden="true"><i class="ph-bold ph-caret-left"></i></span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Sebelumnya"><i class="ph-bold ph-caret-left"></i></a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="gap">…</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="is-current" aria-current="page">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Berikutnya"><i class="ph-bold ph-caret-right"></i></a>
        @else
            <span class="is-disabled" aria-hidden="true"><i class="ph-bold ph-caret-right"></i></span>
        @endif
    </nav>
@endif
