@props(['product'])

<article class="product-card">
    @if ($product->isSoldOut())
        <span class="product-card__badge sticker sticker--mute">Habis</span>
    @elseif ($product->isLowStock())
        <span class="product-card__badge sticker sticker--ember">Sisa {{ $product->stock }}</span>
    @elseif ($product->is_featured)
        <span class="product-card__badge sticker">Laris</span>
    @endif

    <x-pack :product="$product" />

    <div class="product-card__body">
        <span class="product-card__cat">{{ $product->category->name }}</span>
        <a class="product-card__name" href="{{ route('products.show', $product) }}">{{ $product->name }}</a>

        <ul class="spec">
            <li>{{ $product->weightLabel() }}</li>
            <li>{{ $product->servings }} porsi</li>
            @if ($product->cook_method)
                <li class="spec__wide">{{ $product->cook_method }}</li>
            @endif
        </ul>

        <div class="product-card__foot">
            <div>
                <div class="price product-card__price">{{ rupiah($product->price) }}</div>
                <div class="product-card__per">≈ {{ rupiah($product->pricePerServing()) }} / porsi</div>
            </div>

            <form method="POST" action="{{ route('cart.store') }}">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <button class="add-btn" type="submit" @disabled($product->isSoldOut())
                        aria-label="Tambah {{ $product->name }} ke keranjang" title="Tambah ke keranjang">
                    <i class="ph-bold ph-plus"></i>
                </button>
            </form>
        </div>
    </div>
</article>
