<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Akses Ditolak · {{ \App\Models\Setting::current()->store_name }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-slate-950">
    <div class="flex min-h-screen items-center justify-center px-4 py-10">
        <div class="w-full max-w-md rounded-2xl border border-slate-800 bg-slate-900 p-8 text-center shadow-2xl">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-rose-500/15 text-rose-400">
                <x-icon name="warning" class="h-7 w-7" />
            </div>

            <p class="mt-6 text-sm font-semibold uppercase tracking-wider text-rose-400">403 · Akses Ditolak</p>
            <h1 class="mt-2 text-2xl font-bold text-white">Halaman ini khusus admin</h1>
            <p class="mt-3 text-sm leading-relaxed text-slate-400">
                Akun Anda tidak memiliki izin untuk membuka halaman tersebut.
                Silakan kembali ke halaman kasir atau hubungi admin toko.
            </p>

            <div class="mt-7 flex flex-col gap-3 sm:flex-row sm:justify-center">
                <a href="{{ auth()->check() ? route('home') : route('login') }}" class="btn-primary">
                    <x-icon name="pos" class="h-4 w-4" />
                    Halaman Utama
                </a>
            </div>
        </div>
    </div>
</body>
</html>
