@extends('layouts.auth')

@section('title', 'Daftar')

@section('content')
    <h1 style="font-size:2rem">Buat akun</h1>
    <p class="muted mb-4">Sudah punya akun? <a href="{{ route('login') }}">Masuk di sini</a>.</p>

    <form method="POST" action="{{ route('register') }}" novalidate>
        @csrf

        <div class="mb-3">
            <label class="form-label" for="name">Nama</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" autocomplete="name" autofocus required
                   class="form-control @error('name') is-invalid @enderror">
            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label" for="email">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" autocomplete="email" required
                   class="form-control @error('email') is-invalid @enderror">
            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label" for="phone">Nomor WhatsApp <span class="muted fw-normal">(opsional)</span></label>
            <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" autocomplete="tel" inputmode="tel" placeholder="0812-3456-7890"
                   class="form-control @error('phone') is-invalid @enderror">
            @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="row g-3 mb-4">
            <div class="col-sm-6">
                <label class="form-label" for="password">Kata sandi</label>
                <input type="password" id="password" name="password" autocomplete="new-password" required
                       class="form-control @error('password') is-invalid @enderror">
                @error('password') <div class="invalid-feedback">{{ $message }}</div> @else <div class="form-text">Minimal 8 karakter.</div> @enderror
            </div>
            <div class="col-sm-6">
                <label class="form-label" for="password_confirmation">Ulangi kata sandi</label>
                <input type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password" required class="form-control">
            </div>
        </div>

        <button class="btn-fm btn-fm--primary btn-fm--lg btn-fm--block" type="submit">Daftar <i class="ph-bold ph-arrow-right"></i></button>
    </form>

    <a class="link-arrow mt-4" style="font-size:.9rem" href="{{ route('home') }}"><i class="ph-bold ph-arrow-left"></i> Kembali ke toko</a>
@endsection
