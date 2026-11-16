{{-- Field kategori; dipakai di form tambah dan edit. --}}
@php $tone = old('tone', $category->tone); $art = old('art', $category->art); @endphp

<div class="mb-3">
    <label class="form-label" for="name">Nama kategori</label>
    <input type="text" id="name" name="name" value="{{ old('name', $category->name) }}" required
           class="form-control @error('name') is-invalid @enderror" placeholder="Contoh: Nugget">
    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="mb-3">
    <label class="form-label" for="description">Deskripsi singkat <span class="muted fw-normal">(opsional)</span></label>
    <input type="text" id="description" name="description" value="{{ old('description', $category->description) }}" maxlength="160"
           class="form-control @error('description') is-invalid @enderror" placeholder="Satu kalimat tentang kategori ini">
    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="mb-3">
    <span class="form-label d-block">Warna kemasan</span>
    <div class="swatch-pick" data-tone-preview="#catPreview">
        @foreach (config('frozemate.tones') as $key => $label)
            <input type="radio" name="tone" id="tone-{{ $key }}" value="{{ $key }}" @checked($tone === $key)>
            <label for="tone-{{ $key }}" class="tone-{{ $key }}">{{ $label }}</label>
        @endforeach
    </div>
    @error('tone') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
</div>

<div class="mb-3">
    <label class="form-label" for="art">Ilustrasi kemasan</label>
    <select id="art" name="art" class="form-select @error('art') is-invalid @enderror">
        @foreach (config('frozemate.arts') as $key => $label)
            <option value="{{ $key }}" @selected($art === $key)>{{ $label }}</option>
        @endforeach
    </select>
    @error('art') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="form-check form-switch mb-3">
    <input class="form-check-input" type="checkbox" role="switch" name="is_side" value="1" id="is_side" @checked(old('is_side', $category->is_side))>
    <label class="form-check-label" for="is_side">
        <strong>Pendamping / camilan</strong><br>
        <small class="muted">Bukan lauk utama. Smart Bundle menghitung porsinya setengah.</small>
    </label>
</div>

<div class="preview-frame mb-3" style="max-width:220px">
    <x-pack :tone="$tone" :art="$art" id="catPreview" />
</div>
