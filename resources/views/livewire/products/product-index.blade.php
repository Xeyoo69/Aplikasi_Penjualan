<div>
    <x-page-header title="Produk" subtitle="Kelola katalog, harga, dan status produk.">
        <x-slot:actions>
            <a href="{{ route('admin.stock') }}" wire:navigate class="btn-secondary">
                <x-icon name="stock" class="h-4 w-4" />
                Manajemen Stok
            </a>
            <button type="button" wire:click="openCreate" class="btn-primary">
                <x-icon name="plus" class="h-4 w-4" />
                Tambah Produk
            </button>
        </x-slot:actions>
    </x-page-header>

    @if ($lowStockCount > 0)
        <div class="mb-4 flex flex-wrap items-center gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            <x-icon name="warning" class="h-5 w-5 shrink-0" />
            <p class="flex-1"><span class="font-bold">{{ $lowStockCount }} produk</span> memiliki stok di bawah atau sama dengan batas minimum.</p>
            <button type="button" wire:click="$set('lowOnly', true)" class="rounded-lg bg-amber-100 px-3 py-1.5 text-xs font-bold text-amber-800 hover:bg-amber-200">
                Tampilkan
            </button>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <div class="flex w-full flex-wrap items-center gap-2 sm:w-auto">
                <div class="min-w-0 flex-1 sm:w-64">
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                            <x-icon name="search" class="h-5 w-5" />
                        </span>
                        <input type="text" wire:model.live.debounce.300ms="search" class="input pl-11" placeholder="Cari nama, SKU, barcode...">
                    </div>
                </div>
                <select wire:model.live="categoryFilter" class="select w-auto">
                    <option value="">Semua kategori</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
                <label class="flex cursor-pointer items-center gap-2 rounded-xl border border-slate-200 px-3 py-2.5 text-xs font-semibold text-slate-600">
                    <input type="checkbox" wire:model.live="lowOnly" class="h-4 w-4 rounded border-slate-300 text-amber-600 focus:ring-amber-500">
                    Stok menipis
                </label>
            </div>
            <span class="text-xs font-medium text-slate-500">{{ $products->total() }} produk</span>
        </div>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Kategori</th>
                        <th class="text-right">Harga Beli</th>
                        <th class="text-right">Harga Jual</th>
                        <th class="text-center">Stok</th>
                        <th class="text-center">Status</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr wire:key="product-{{ $product->id }}">
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-10 shrink-0 overflow-hidden rounded-xl bg-slate-100">
                                        @if ($product->image_url)
                                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                                        @else
                                            <div class="flex h-full w-full items-center justify-center text-sm font-bold text-slate-400">
                                                {{ mb_substr($product->name, 0, 1) }}
                                            </div>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <p class="max-w-[220px] truncate font-semibold text-slate-800">{{ $product->name }}</p>
                                        <p class="text-[11px] text-slate-500">{{ $product->sku }}{{ $product->barcode ? ' · '.$product->barcode : '' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="text-slate-600">{{ $product->category->name }}</td>
                            <td class="text-right text-slate-600">{{ rupiah($product->purchase_price) }}</td>
                            <td class="text-right font-semibold text-slate-800">{{ rupiah($product->selling_price) }}</td>
                            <td class="text-center">
                                <div class="flex flex-col items-center gap-1">
                                    <span class="badge {{ $product->stock_badge_class }}">{{ $product->stock }}</span>
                                    <span class="text-[10px] text-slate-400">min. {{ $product->min_stock }}</span>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="{{ $product->is_active ? 'badge-green' : 'badge-slate' }}">
                                    {{ $product->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="text-right">
                                <div class="flex justify-end gap-1">
                                    <button type="button" wire:click="openEdit({{ $product->id }})" class="btn-ghost btn-sm">
                                        <x-icon name="pencil" class="h-4 w-4" />
                                        Ubah
                                    </button>
                                    <button
                                        type="button"
                                        wire:click="delete({{ $product->id }})"
                                        wire:confirm="Hapus produk {{ $product->name }}? Riwayat transaksi Lama tetap tersimpan."
                                        class="btn-ghost btn-sm text-rose-600 hover:bg-rose-50 hover:text-rose-700"
                                    >
                                        <x-icon name="trash" class="h-4 w-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                                        <x-icon name="products" class="h-6 w-6" />
                                    </div>
                                    <p class="mt-3 text-sm font-semibold text-slate-700">Tidak ada produk</p>
                                    <p class="mt-1 text-sm text-slate-500">Ubah filter atau tambahkan produk baru.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($products->hasPages())
            <div class="border-t border-slate-100 px-4 py-3">
                {{ $products->links() }}
            </div>
        @endif
    </div>

    @if ($showModal)
        <div class="modal-backdrop" wire:click.self="closeModal">
            <div class="modal-panel max-w-3xl" role="dialog" aria-modal="true">
                <div class="card-header">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">{{ $editingId ? 'Ubah Produk' : 'Tambah Produk' }}</h2>
                        <p class="text-xs text-slate-500">Harga disimpan sebagai rupiah tanpa desimal.</p>
                    </div>
                    <button type="button" wire:click="closeModal" class="btn-ghost p-2" aria-label="Tutup">
                        <x-icon name="x" class="h-5 w-5" />
                    </button>
                </div>

                <form wire:submit="save" class="space-y-5 p-5">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="product-name" class="label">Nama Produk <span class="text-rose-500">*</span></label>
                            <input id="product-name" type="text" wire:model="name" class="input" placeholder="Contoh: Kopi Arabika 250g" autofocus>
                            @error('name')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="product-category" class="label">Kategori <span class="text-rose-500">*</span></label>
                            <select id="product-category" wire:model="categoryId" class="select">
                                <option value="">Pilih kategori</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                            @error('categoryId')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="product-sku" class="label">SKU <span class="text-rose-500">*</span></label>
                            <input id="product-sku" type="text" wire:model="sku" class="input uppercase" placeholder="KOPI-001">
                            @error('sku')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="product-barcode" class="label">Barcode</label>
                            <input id="product-barcode" type="text" wire:model="barcode" class="input" placeholder="Opsional, untuk scanner">
                            @error('barcode')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="product-purchase" class="label">Harga Beli <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-sm font-medium text-slate-500">Rp</span>
                                <input id="product-purchase" type="number" min="0" wire:model="purchasePrice" class="input pl-10">
                            </div>
                            @error('purchasePrice')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="product-selling" class="label">Harga Jual <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-sm font-medium text-slate-500">Rp</span>
                                <input id="product-selling" type="number" min="0" wire:model="sellingPrice" class="input pl-10">
                            </div>
                            @error('sellingPrice')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="product-stock" class="label">Stok</label>
                            @if ($editingId)
                                <input id="product-stock" type="text" value="{{ $stock }}" class="input" disabled>
                                <p class="mt-1 text-[11px] text-slate-500">
                                    Stok hanya berubah lewat <a href="{{ route('admin.stock') }}" class="font-semibold text-emerald-600 hover:underline">Manajemen Stok</a> agar tercatat.
                                </p>
                            @else
                                <input id="product-stock" type="number" min="0" wire:model="stock" class="input">
                                <p class="mt-1 text-[11px] text-slate-500">Stok awal dicatat sebagai pergerakan restock.</p>
                                @error('stock')
                                    <p class="field-error">{{ $message }}</p>
                                @enderror
                            @endif
                        </div>
                        <div>
                            <label for="product-min-stock" class="label">Stok Minimum <span class="text-rose-500">*</span></label>
                            <input id="product-min-stock" type="number" min="0" wire:model="minStock" class="input">
                            <p class="mt-1 text-[11px] text-slate-500">Peringatan muncul saat stok ≤ nilai ini.</p>
                            @error('minStock')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="label">Foto Produk</label>
                            <div class="flex items-start gap-3">
                                <div class="h-20 w-20 shrink-0 overflow-hidden rounded-xl border border-slate-200 bg-slate-100">
                                    @if ($image)
                                        <img src="{{ $image->temporaryUrl() }}" alt="Pratinjau" class="h-full w-full object-cover">
                                    @elseif ($existingImage)
                                        <img src="{{ asset('storage/'.$existingImage) }}" alt="Foto produk" class="h-full w-full object-cover">
                                    @else
                                        <div class="flex h-full w-full items-center justify-center text-slate-400">
                                            <x-icon name="products" class="h-7 w-7" />
                                        </div>
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1">
                                    <input type="file" wire:model.live="image" accept="image/jpeg,image/png,image/webp" class="block w-full text-xs text-slate-500 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-slate-700 hover:file:bg-slate-200">
                                    <p class="mt-1 text-[11px] text-slate-500">JPG/PNG/WEBP, maksimal 2 MB.</p>
                                    <div wire:loading wire:target="image" class="mt-1 text-[11px] font-medium text-emerald-600">Mengunggah...</div>
                                    @error('image')
                                        <p class="field-error">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="flex items-end">
                            <label class="flex w-full cursor-pointer items-center justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50 px-3 py-3">
                                <span>
                                    <span class="block text-sm font-semibold text-slate-800">Produk aktif</span>
                                    <span class="block text-[11px] text-slate-500">Produk nonaktif tidak muncul di kasir.</span>
                                </span>
                                <input type="checkbox" wire:model="isActive" class="h-5 w-5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 border-t border-slate-100 pt-4">
                        <button type="button" wire:click="closeModal" class="btn-secondary">Batal</button>
                        <button type="submit" class="btn-primary" wire:loading.attr="disabled" wire:target="save">
                            <x-icon name="check" class="h-4 w-4" />
                            Simpan Produk
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
