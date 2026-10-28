@php
    $user = auth()->user();
    $isAdmin = $user?->isAdmin() ?? false;
    $store = \App\Models\Setting::current();

    $mainLinks = [
        ['route' => 'pos', 'pattern' => 'pos', 'label' => 'Kasir', 'icon' => 'pos'],
        ['route' => 'catalog', 'pattern' => 'catalog', 'label' => 'Katalog Produk', 'icon' => 'catalog'],
        ['route' => 'sales.index', 'pattern' => 'sales.*', 'label' => 'Riwayat Transaksi', 'icon' => 'history'],
        ['route' => 'customers.index', 'pattern' => 'customers.*', 'label' => 'Pelanggan', 'icon' => 'users'],
    ];

    $adminLinks = [
        ['route' => 'admin.dashboard', 'pattern' => 'admin.dashboard', 'label' => 'Dashboard', 'icon' => 'dashboard'],
        ['route' => 'admin.products', 'pattern' => 'admin.products', 'label' => 'Produk', 'icon' => 'products'],
        ['route' => 'admin.categories', 'pattern' => 'admin.categories', 'label' => 'Kategori', 'icon' => 'categories'],
        ['route' => 'admin.stock', 'pattern' => 'admin.stock', 'label' => 'Manajemen Stok', 'icon' => 'stock'],
        ['route' => 'admin.users', 'pattern' => 'admin.users', 'label' => 'Pengguna', 'icon' => 'users'],
        ['route' => 'admin.reports', 'pattern' => 'admin.reports*', 'label' => 'Laporan', 'icon' => 'report'],
        ['route' => 'admin.settings', 'pattern' => 'admin.settings', 'label' => 'Pengaturan Toko', 'icon' => 'settings'],
    ];
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Aplikasi' }} · {{ $store->store_name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-slate-100">
    <div x-data="{ sidebarOpen: false }" class="min-h-screen lg:flex">
        <div
            x-show="sidebarOpen"
            x-cloak
            x-transition.opacity
            class="fixed inset-0 z-30 bg-slate-900/50 backdrop-blur-sm lg:hidden"
            @click="sidebarOpen = false"
        ></div>

        <aside
            class="fixed inset-y-0 left-0 z-40 flex w-72 shrink-0 transform flex-col border-r border-slate-200 bg-white shadow-xl transition-transform duration-200 ease-out lg:sticky lg:top-0 lg:h-screen lg:translate-x-0 lg:shadow-none"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        >
            <div class="flex items-center gap-3 border-b border-slate-100 px-5 py-4">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-700 text-base font-black text-white shadow-sm">
                    {{ mb_substr($store->store_name, 0, 1) }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-bold text-slate-900">{{ $store->store_name }}</p>
                    <p class="text-xs text-slate-500">Sistem Penjualan</p>
                </div>
                <button type="button" class="btn-ghost p-2 lg:hidden" @click="sidebarOpen = false" aria-label="Tutup menu">
                    <x-icon name="x" class="h-5 w-5" />
                </button>
            </div>

            <nav class="flex-1 overflow-y-auto px-3 pb-4">
                <p class="nav-section">Operasional</p>
                @foreach ($mainLinks as $link)
                    <a
                        href="{{ route($link['route']) }}"
                        wire:navigate
                        class="{{ request()->routeIs($link['pattern']) ? 'nav-active' : 'nav-item' }} mb-1"
                    >
                        <x-icon :name="$link['icon']" class="h-5 w-5 shrink-0" />
                        <span>{{ $link['label'] }}</span>
                    </a>
                @endforeach

                @if ($isAdmin)
                    <p class="nav-section">Administrasi</p>
                    @foreach ($adminLinks as $link)
                        <a
                            href="{{ route($link['route']) }}"
                            wire:navigate
                            class="{{ request()->routeIs($link['pattern']) ? 'nav-active' : 'nav-item' }} mb-1"
                        >
                            <x-icon :name="$link['icon']" class="h-5 w-5 shrink-0" />
                            <span>{{ $link['label'] }}</span>
                        </a>
                    @endforeach
                @endif
            </nav>

            <div class="border-t border-slate-100 p-3">
                <div class="flex items-center gap-3 rounded-xl bg-slate-50 px-3 py-2.5">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-slate-900 text-xs font-bold uppercase text-white">
                        {{ mb_substr($user->name ?? '?', 0, 2) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold text-slate-900">{{ $user->name }}</p>
                        <p class="text-xs capitalize text-slate-500">{{ $user->role->label() }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn-ghost p-2 text-slate-500 hover:text-rose-600" title="Keluar">
                            <x-icon name="logout" class="h-5 w-5" />
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col">
            <header class="sticky top-0 z-20 flex items-center gap-3 border-b border-slate-200 bg-white/90 px-4 py-3 backdrop-blur sm:px-6 lg:hidden">
                <button type="button" class="btn-ghost p-2" @click="sidebarOpen = true" aria-label="Buka menu">
                    <x-icon name="menu" class="h-6 w-6" />
                </button>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-bold text-slate-900">{{ $store->store_name }}</p>
                    <p class="text-xs text-slate-500">{{ $user->name }} · {{ $user->role->label() }}</p>
                </div>
                <a href="{{ route('pos') }}" wire:navigate class="btn-primary btn-sm">
                    <x-icon name="pos" class="h-4 w-4" />
                    Kasir
                </a>
            </header>

            <main class="flex-1 px-4 py-5 sm:px-6 sm:py-6">
                @if (session('success'))
                    <div class="mb-4 flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                        <x-icon name="check" class="mt-0.5 h-5 w-5 shrink-0" />
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                {{ $slot }}
            </main>

            <footer class="px-4 pb-6 text-center text-xs text-slate-400 sm:px-6">
                {{ $store->store_name }} · Sistem Penjualan
            </footer>
        </div>

        <div
            x-data="{ open: false, message: '', type: 'success' }"
            x-on:toast.window="
                message = $event.detail.message ?? $event.detail[0]?.message;
                type = $event.detail.type ?? $event.detail[0]?.type ?? 'success';
                open = true;
                clearTimeout($data.timer);
                $data.timer = setTimeout(() => open = false, 3500);
            "
            x-show="open"
            x-cloak
            x-transition
            class="fixed bottom-5 right-5 z-[60] w-[calc(100%-2.5rem)] max-w-sm"
        >
            <div
                class="flex items-start gap-3 rounded-2xl border px-4 py-3 text-sm font-semibold shadow-xl"
                :class="type === 'error' ? 'border-rose-200 bg-rose-50 text-rose-800' : 'border-emerald-200 bg-emerald-50 text-emerald-800'"
            >
                <x-icon name="check" x-show="type !== 'error'" class="mt-0.5 h-5 w-5 shrink-0" />
                <x-icon name="warning" x-show="type === 'error'" class="mt-0.5 h-5 w-5 shrink-0" x-cloak />
                <p class="flex-1" x-text="message"></p>
                <button type="button" class="opacity-60 hover:opacity-100" @click="open = false" aria-label="Tutup notifikasi">
                    <x-icon name="x" class="h-4 w-4" />
                </button>
            </div>
        </div>
    </div>

    @livewireScripts
</body>
</html>
