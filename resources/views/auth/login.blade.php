@extends('layouts.auth')

@section('title', 'Masuk')

@section('content')
    <h1 style="font-size:2rem">Masuk</h1>
    <p class="muted mb-4">Belum punya akun? <a href="{{ route('register') }}">Daftar dulu</a>, cuma semenit.</p>

    <form method="POST" action="{{ route('login') }}" novalidate data-login-form>
        @csrf

        <div class="mb-3">
            <label class="form-label" for="email">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" autocomplete="email" autofocus required
                   class="form-control @error('email') is-invalid @enderror">
            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label" for="password">Kata sandi</label>
            <input type="password" id="password" name="password" autocomplete="current-password" required
                   class="form-control @error('password') is-invalid @enderror">
            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="form-check mb-4">
            <input class="form-check-input" type="checkbox" name="remember" value="1" id="remember">
            <label class="form-check-label" for="remember">Ingat saya di perangkat ini</label>
        </div>

        <button class="btn-fm btn-fm--primary btn-fm--lg btn-fm--block" type="submit">Masuk <i class="ph-bold ph-arrow-right"></i></button>
    </form>

    @env('local')
        <div class="demo-box mt-4">
            <div class="mb-1" style="letter-spacing:.06em;text-transform:uppercase;font-size:.68rem;color:var(--mute)">Akun demo (mode lokal)</div>
            Admin — <button type="button" data-fill-email="admin@frozemate.test" data-fill-password="password">admin@frozemate.test</button><br>
            Customer — <button type="button" data-fill-email="dinda@frozemate.test" data-fill-password="password">dinda@frozemate.test</button>
            <div class="mt-1 muted">Klik salah satu untuk mengisi form.</div>
        </div>
    @endenv

    <a class="link-arrow mt-4" style="font-size:.9rem" href="{{ route('home') }}"><i class="ph-bold ph-arrow-left"></i> Kembali ke toko</a>
@endsection
