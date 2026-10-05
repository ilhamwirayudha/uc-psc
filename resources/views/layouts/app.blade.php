<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UC PSC Management System</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-ucpsc.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>[x-cloak] { display: none !important; }</style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F9F7FD]">
    {{-- Splash Screen on Refresh / Load --}}
    <x-splash-screen />

    <main>
        @yield('content')
    </main>

    {{-- Prevent bfcache from displaying stale login or guest state and lock Back button --}}
    <script>
        window.addEventListener('pageshow', function (event) {
            if (event.persisted || (window.performance && window.performance.getEntriesByType && window.performance.getEntriesByType("navigation")[0]?.type === "back_forward")) {
                window.location.reload();
            }
        });

        // Kunci tombol Back pada halaman Login agar pengguna tidak bisa kembali ke riwayat sebelumnya
        if (window.history && window.history.pushState) {
            history.pushState(null, '', window.location.href);
            window.addEventListener('popstate', function () {
                history.pushState(null, '', window.location.href);
            });
        }
    </script>
</body>
</html>