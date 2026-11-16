<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head')
</head>
<body>
    <div class="auth">
        <aside class="auth__aside">
            <x-brand light />

            <div>
                <span class="eyebrow" style="color:var(--butter)">Smart Frozen Food Bundle</span>
                <h2 class="mt-3">Bilang butuhnya apa.<br>Kami racik bundelnya.</h2>
                <p class="mb-0" style="max-width:36ch">Masuk untuk checkout dan memantau status pesananmu dari dapur sampai pintu rumah.</p>
            </div>

            <div class="receipt-wrap" style="max-width:300px;transform:rotate(-2deg)">
                <div class="receipt">
                    <div class="receipt__head">
                        <div class="title">Kos Saver Bundle</div>
                        <div class="sub">1 orang · 5× makan</div>
                    </div>
                    <div class="receipt__row"><span class="q">1×</span><span class="n">Nugget</span><span class="dots"></span><span>Rp25.000</span></div>
                    <div class="receipt__row"><span class="q">1×</span><span class="n">Sosis</span><span class="dots"></span><span>Rp18.000</span></div>
                    <div class="receipt__row"><span class="q">1×</span><span class="n">Dimsum</span><span class="dots"></span><span>Rp22.000</span></div>
                    <div class="receipt__rule"></div>
                    <div class="receipt__total"><span class="label">Total</span><span class="amount">Rp65.000</span></div>
                </div>
            </div>
        </aside>

        <main class="auth__main">
            <div class="auth__form">
                <div class="d-lg-none mb-4"><x-brand /></div>
                @yield('content')
            </div>
        </main>
    </div>

    @include('partials.flash')
</body>
</html>
