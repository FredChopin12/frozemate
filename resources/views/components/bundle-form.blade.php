@props(['prefill' => [], 'sticker' => null, 'id' => 'bf'])

@php
    $needs = config('frozemate.needs');
    $budgets = config('frozemate.budgets');
    $chosen = fn (string $key, $default = null) => old($key, $prefill[$key] ?? $default);
@endphp

{{-- Panel Smart Bundle: empat pilihan → rekomendasi. --}}
<form method="GET" action="{{ route('bundle.result') }}" class="panel" data-bundle-form>
    @if ($sticker)
        <span class="sticker panel__sticker">{{ $sticker }}</span>
    @endif

    <div class="panel__head">
        <span class="name">Panel Smart Bundle</span>
        <span class="panel__temp"><span class="dot"></span>−18°C</span>
    </div>

    <fieldset>
        <legend>01 <b>Buat siapa?</b></legend>
        <div class="seg">
            @foreach ($needs as $key => $need)
                <input type="radio" name="need" id="{{ $id }}-need-{{ $key }}" value="{{ $key }}" @checked($chosen('need', 'kos') === $key) required>
                <label for="{{ $id }}-need-{{ $key }}"><i class="ph {{ $need['icon'] }}"></i>{{ $need['label'] }}</label>
            @endforeach
        </div>
        @error('need') <div class="invalid-note">{{ $message }}</div> @enderror
    </fieldset>

    <div class="row g-3">
        <div class="col-sm-6">
            <fieldset>
                <legend>02 <b>Berapa orang?</b></legend>
                <div class="seg seg--even">
                    @foreach (config('frozemate.people') as $value => $label)
                        <input type="radio" name="people" id="{{ $id }}-people-{{ $value }}" value="{{ $value }}" @checked((int) $chosen('people', 1) === $value) required>
                        <label for="{{ $id }}-people-{{ $value }}">{{ $label }}</label>
                    @endforeach
                </div>
                @error('people') <div class="invalid-note">{{ $message }}</div> @enderror
            </fieldset>
        </div>
        <div class="col-sm-6">
            <fieldset>
                <legend>03 <b>Berapa kali makan?</b></legend>
                <div class="seg seg--even">
                    @foreach (config('frozemate.meals') as $value)
                        <input type="radio" name="meals" id="{{ $id }}-meals-{{ $value }}" value="{{ $value }}" @checked((int) $chosen('meals', 5) === $value) required>
                        <label for="{{ $id }}-meals-{{ $value }}">{{ $value }}×</label>
                    @endforeach
                </div>
                @error('meals') <div class="invalid-note">{{ $message }}</div> @enderror
            </fieldset>
        </div>
    </div>

    <fieldset class="mb-0">
        <legend>04 <b>Budget</b></legend>
        <div class="seg">
            @foreach ($budgets as $key => $budget)
                <input type="radio" name="budget" id="{{ $id }}-budget-{{ $key }}" value="{{ $key }}" data-long="{{ $budget['long'] }}" @checked($chosen('budget', 'b2') === $key) required>
                <label for="{{ $id }}-budget-{{ $key }}">{{ $budget['label'] }}</label>
            @endforeach
        </div>
        @error('budget') <div class="invalid-note">{{ $message }}</div> @enderror
    </fieldset>

    <div class="panel__foot">
        <div class="panel__readout" data-readout aria-live="polite"></div>
        <button type="submit" class="btn-fm btn-fm--primary btn-fm--lg">
            Racik bundelnya <i class="ph-bold ph-arrow-right"></i>
        </button>
    </div>
</form>
