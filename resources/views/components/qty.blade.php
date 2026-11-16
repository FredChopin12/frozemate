@props(['name' => 'qty', 'value' => 1, 'min' => 1, 'max' => 50, 'small' => false])

<div {{ $attributes->class(['qty', 'qty--sm' => $small]) }}>
    <button type="button" data-step="-1" aria-label="Kurangi"><i class="ph-bold ph-minus"></i></button>
    <input type="number" inputmode="numeric" name="{{ $name }}" value="{{ $value }}" min="{{ $min }}" max="{{ $max }}" aria-label="Jumlah">
    <button type="button" data-step="1" aria-label="Tambah"><i class="ph-bold ph-plus"></i></button>
</div>
