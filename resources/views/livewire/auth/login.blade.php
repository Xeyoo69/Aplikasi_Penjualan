<div class="grid min-h-screen lg:grid-cols-2">
    <div class="relative hidden overflow-hidden bg-gradient-to-br from-emerald-700 via-emerald-800 to-teal-950 p-12 lg:flex lg:flex-col lg:justify-between">
        <div class="absolute -right-24 -top-24 h-80 w-80 rounded-full bg-emerald-400/20 blur-3xl"></div>
        <div class="absolute -bottom-32 -left-20 h-96 w-96 rounded-full bg-teal-300/10 blur-3xl"></div>

        <div class="relative">
            <div class="flex items-center gap-3">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/15 text-lg font-black text-white ring-1 ring-white/20 backdrop-blur">
                    {{ mb_substr($store->store_name, 0, 1) }}
                </div>
                <div>
                    <p class="text-lg font-bold text-white">{{ $store->store_name }}</p>
                    <p class="text-sm text-emerald-100/80">Sistem Penjualan & Kasir</p>
                </div>
            </div>
        </div>

        <div class="relative max-w-lg">
            <p class="inline-flex items-center rounded-full bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-emerald-100 ring-1 ring-white/15">
                Siap melayani
            </p>
            <h1 class="mt-5 text-4xl font-black leading-tight text-white">
                Transaksi cepat,<br>stok tetap akurat.
            </h1>
            <p class="mt-4 text-base leading-relaxed text-emerald-100/80">
                Kelola penjualan, pelanggan, stok, dan laporan toko dalam satu tempat.
                Dirancang nyaman dipakai di komputer maupun tablet kasir.
            </p>
            <ul class="mt-8 space-y-3 text-sm text-emerald-50">
                <li class="flex items-center gap-3">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-white/15"><x-icon name="check" class="h-4 w-4" /></span>
                    Pencarian produk via nama, SKU, atau barcode
                </li>
                <li class="flex items-center gap-3">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-white/15"><x-icon name="check" class="h-4 w-4" /></span>
                    Stok terkunci aman saat transaksi bersamaan
                </li>
                <li class="flex items-center gap-3">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-white/15"><x-icon name="check" class="h-4 w-4" /></span>
                    Struk siap cetak 58 mm / 80 mm
                </li>
            </ul>
        </div>

        <p class="relative text-xs text-emerald-200/60">&copy; {{ now()->year }} {{ $store->store_name }}. Semua data tersimpan aman.</p>
    </div>

    <div class="flex items-center justify-center bg-slate-100 px-4 py-10 sm:px-8">
        <div class="w-full max-w-md">
            <div class="mb-8 flex items-center gap-3 lg:hidden">
                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-600 text-base font-black text-white">
                    {{ mb_substr($store->store_name, 0, 1) }}
                </div>
                <div>
                    <p class="text-base font-bold text-slate-900">{{ $store->store_name }}</p>
                    <p class="text-xs text-slate-500">Sistem Penjualan</p>
                </div>
            </div>

            <div class="card p-6 sm:p-8">
                <h2 class="text-2xl font-bold tracking-tight text-slate-900">Masuk ke akun</h2>
                <p class="mt-1.5 text-sm text-slate-500">Gunakan email dan kata sandi yang diberikan admin toko.</p>

                <form wire:submit="authenticate" class="mt-7 space-y-5">
                    <div>
                        <label for="email" class="label">Email</label>
                        <input
                            id="email"
                            type="email"
                            wire:model="email"
                            class="input"
                            placeholder="nama@toko.com"
                            autocomplete="username"
                            autofocus
                        >
                        @error('email')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div x-data="{ show: false }">
                        <label for="password" class="label">Kata Sandi</label>
                        <div class="relative">
                            <input
                                id="password"
                                :type="show ? 'text' : 'password'"
                                wire:model="password"
                                class="input pr-12"
                                placeholder="••••••••"
                                autocomplete="current-password"
                            >
                            <button
                                type="button"
                                class="absolute inset-y-0 right-0 flex items-center px-3.5 text-xs font-semibold text-slate-500 hover:text-slate-800"
                                @click="show = !show"
                                tabindex="-1"
                            >
                                <span x-text="show ? 'Sembunyikan' : 'Lihat'"></span>
                            </button>
                        </div>
                        @error('password')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <label class="flex cursor-pointer items-center gap-2.5 text-sm text-slate-600">
                        <input type="checkbox" wire:model="remember" class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                        Ingat saya di perangkat ini
                    </label>

                    <button type="submit" class="btn-primary w-full py-3" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="authenticate" class="inline-flex items-center gap-2">
                            <x-icon name="logout" class="h-5 w-5 rotate-180" />
                            Masuk
                        </span>
                        <span wire:loading wire:target="authenticate" class="inline-flex items-center gap-2">
                            <svg class="h-5 w-5 animate-spin" viewBox="0 0 24 24" fill="none">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4Z"></path>
                            </svg>
                            Memproses...
                        </span>
                    </button>
                </form>
            </div>

            <p class="mt-6 text-center text-xs text-slate-500">
                Butuh bantuan? Hubungi admin toko untuk reset kata sandi.
            </p>
        </div>
    </div>
</div>
