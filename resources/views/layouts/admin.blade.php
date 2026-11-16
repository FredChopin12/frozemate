<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head')
</head>
<body>
    <div class="admin-mobile-top">
        <x-brand light :href="route('admin.dashboard')" />
        <button class="icon-btn" type="button" data-bs-toggle="offcanvas" data-bs-target="#adminDrawer" aria-controls="adminDrawer" aria-label="Buka menu">
            <i class="ph-bold ph-list"></i>
        </button>
    </div>

    <div class="offcanvas offcanvas-start admin-drawer" tabindex="-1" id="adminDrawer" aria-label="Menu admin">
        <div class="offcanvas-header">
            <x-brand light :href="route('admin.dashboard')" />
            <button type="button" class="icon-btn" data-bs-dismiss="offcanvas" aria-label="Tutup"><i class="ph-bold ph-x"></i></button>
        </div>
        <div class="offcanvas-body d-flex flex-column">
            @include('partials.admin-nav')
        </div>
    </div>

    <div class="admin-shell">
        <aside class="admin-side">
            <x-brand light :href="route('admin.dashboard')" />
            @include('partials.admin-nav')
        </aside>

        <div class="admin-main">
            <header class="admin-top">
                <div>
                    <span class="eyebrow eyebrow--plain">@yield('eyebrow', 'Admin')</span>
                    <h1>@yield('heading')</h1>
                </div>
                <div class="d-flex gap-2 flex-wrap">@yield('actions')</div>
            </header>

            <main class="admin-content">
                @yield('content')
            </main>
        </div>
    </div>

    @include('partials.flash')
</body>
</html>
