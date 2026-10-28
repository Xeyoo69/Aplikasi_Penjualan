<div>
    <x-page-header title="Katalog Produk" subtitle="Daftar produk aktif beserta harga dan stok terkini.">
        <x-slot:actions>
            <a href="{{ route('pos') }}" wire:navigate class="btn-primary">
                <x-icon name="pos" class="h-4 w-4" />
                Buka Kasir
            </a>
        </x-slot:actions>
    </x-page-header>

    <div class="card mb-5 p-4">
        <div class="flex flex-col gap-2 sm:flex-row">
            <div class="relative flex-1">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                    <x-icon name="search" class="h-5 w-5" />
                </span>
                <input type="text" wire:model.live.debounce.300ms="search" class="input pl-11" placeholder="Cari nama, SKU, atau barcode...">
            </div>
            <select wire:model.live="categoryFilter" class="select sm:w-56">
                <option value="">Semua kategori</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="mb-3 flex items-center justify-between text-xs text-slate-500">
        <span>{{ $products->total() }} produk aktif</span>
        <span>Harga sudah termasuk pajak hanya jika diaktifkan di kasir.</span>
    </div>

    @if ($products->isEmpty())
        <div class="card empty-state">
            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                <x-icon name="catalog" class="h-7 w-7" />
            </div>
            <p class="mt-4 text-sm font-semibold text-slate-800">Produk tidak ditemukan</p>
            <p class="mt-1 text-sm text-slate-500">Coba kata kunci atau kategori lain.</p>
        </div>
    @else
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-4">
            @foreach ($products as $product)
                <div wire:key="catalog-{{ $product->id }}" class="card flex flex-col overflow-hidden">
                    <div class="relative aspect-[4/3] bg-slate-100">
                        @if ($product->image_url)
                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="h-full w-full object-cover" loading="lazy">
                        @else
                            <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-slate-100 to-slate-200 text-2xl font-black text-slate-400">
                                {{ mb_substr($product->name, 0, 1) }}
                            </div>
                        @endif
                        <span class="badge {{ $product->stock_badge_class }} absolute left-2 top-2 shadow-sm">Stok {{ $product->stock }}</span>
                    </div>
                    <div class="flex flex-1 flex-col p-3">
                        <p class="line-clamp-2 text-sm font-semibold leading-snug text-slate-800">{{ $product->name }}</p>
                        <p class="mt-0.5 text-[11px] text-slate-500">{{ $product->category->name }} · {{ $product->sku }}</p>
                        <p class="mt-auto pt-3 text-base font-bold text-emerald-700">{{ rupiah($product->selling_price) }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        @if ($products->hasPages())
            <div class="mt-4">
                {{ $products->links() }}
            </div>
        @endif
    @endif
</div>
