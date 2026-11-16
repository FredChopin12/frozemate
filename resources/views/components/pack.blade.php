@props([
    'product' => null,
    'tone' => null,
    'art' => null,
    'size' => null,   // null (kartu) | thumb | mini | hero
])

@php
    $tone = $tone ?? $product?->tone() ?? 'blue';
    $art = $art ?? $product?->art() ?? 'box';
    $image = $product?->imageUrl();
@endphp

<div {{ $attributes->class(['pack', "tone-$tone", $size ? "pack--$size" : '', 'is-out' => $product?->exists && $product->isSoldOut()]) }}>
    <x-glyph :art="$art" />

    @if ($image)
        <img src="{{ $image }}" alt="{{ $product->name }}" loading="lazy">
    @elseif (! in_array($size, ['thumb', 'mini']))
        <div class="pack__meta">
            <span>FrozeMate</span>
            <span>{{ $product?->weightLabel() ?? '−18°C' }}</span>
        </div>
    @endif
</div>
