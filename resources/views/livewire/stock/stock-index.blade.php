<div>
    <x-page-header title="Manajemen Stok" subtitle="Setiap perubahan stok tercatat lengkap dengan alasan dan pelakunya.">
        <x-slot:actions>
            <a href="{{ route('admin.products') }}" wire:navigate class="btn-secondary">
                <x-icon name="products" class="h-4 w-4" />
                Daftar Produk
            </a>
        </x-slot:actions>
    </x-page-header>

    <div class="mb-5 grid gap-3 sm:grid-cols-3">
        <div class="stat-card flex items-center gap-3">
            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-sky-50 text-sky-600">
                <x-icon name="products" class="h-5 w-5" />
            </div>
            <div>
                <p class="text-xs font-medium text-slate-500">Produk aktif</p>
                <p class="text-xl font-black text-slate-900">{{ number_format($totalProducts, 0, ',', '.') }}</p>
            </div>
        </div>
        <div class="stat-card flex items-center gap-3">
            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                <x-icon name="warning" class="h-5 w-5" />
            </div>
            <div>
                <p class="text-xs font-medium text-slate-500">Stok menipis</p>
                <p class="text-xl font-black text-slate-900">{{ number_format($lowStockCount, 0, ',', '.') }}</p>
            </div>
        </div>
        <div class="stat-card flex items-center gap-3">
            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                <x-icon name="stock" class="h-5 w-5" />
            </div>
            <div>
                <p class="text-xs font-medium text-slate-500">Pergerakan hari ini</p>
                <p class="text-xl font-black text-slate-900">{{ number_format($movementsToday, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    <div class="mb-5 grid gap-5 lg:grid-cols-3">
        <div class="card lg:col-span-2">
            <div class="card-header">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Penyesuaian Stok</h2>
                    <p class="text-xs text-slate-500">Gunakan restock untuk barang masuk, penyesuaian untuk koreksi (bisa negatif).</p>
                </div>
            </div>
            <form wire:submit="save" class="grid gap-4 p-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="stock-product" class="label">Produk <span class="text-rose-500">*</span></label>
                    <select id="stock-product" wire:model.live="productId" class="select">
                        <option value="">Pilih produk</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku }}) · stok {{ $product->stock }}</option>
                        @endforeach
                    </select>
                    @error('productId')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                    @if ($selectedProduct)
                        <p class="mt-1.5 text-xs font-medium text-slate-500">
                            Stok saat ini: <span class="font-bold text-slate-800">{{ $selectedProduct->stock }}</span>
                        </p>
                    @endif
                </div>
                <div>
                    <label for="stock-type" class="label">Jenis <span class="text-rose-500">*</span></label>
                    <select id="stock-type" wire:model.live="adjustType" class="select">
                        <option value="restock">Restock (barang masuk)</option>
                        <option value="adjustment">Penyesuaian (koreksi)</option>
                    </select>
                    @error('adjustType')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="stock-qty" class="label">Jumlah Perubahan <span class="text-rose-500">*</span></label>
                    <input id="stock-qty" type="number" wire:model="qtyChange" class="input" placeholder="{{ $adjustType === 'restock' ? 'Contoh: 20' : 'Contoh: -3 atau 5' }}">
                    <p class="mt-1 text-[11px] text-slate-500">
                        {{ $adjustType === 'restock' ? 'Nilai positif untuk menambah stok.' : 'Gunakan tanda minus untuk mengurangi stok.' }}
                    </p>
                    @error('qtyChange')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>
                <div class="sm:col-span-2">
                    <label for="stock-reason" class="label">Alasan <span class="text-rose-500">*</span></label>
                    <input id="stock-reason" type="text" wire:model="reason" class="input" placeholder="Contoh: Pembelian dari supplier, barang rusak, stok opname">
                    @error('reason')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>
                <div class="flex justify-end sm:col-span-2">
                    <button type="submit" class="btn-primary" wire:loading.attr="disabled">
                        <x-icon name="check" class="h-4 w-4" />
                        Simpan Penyesuaian
                    </button>
                </div>
            </form>
        </div>

        <div class="card">
            <div class="card-header">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Stok Menipis</h2>
                    <p class="text-xs text-slate-500">Segera lakukan restock.</p>
                </div>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse ($lowStockProducts as $product)
                    <div class="flex items-center justify-between gap-3 px-4 py-3">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-slate-800">{{ $product->name }}</p>
                            <p class="text-[11px] text-slate-500">Minimum {{ $product->min_stock }}</p>
                        </div>
                        <span class="badge {{ $product->stock_badge_class }}">{{ $product->stock }}</span>
                    </div>
                @empty
                    <div class="px-4 py-8 text-center text-sm text-slate-500">
                        Semua stok aman.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div>
                <h2 class="text-base font-bold text-slate-900">Riwayat Pergerakan Stok</h2>
                <p class="text-xs text-slate-500">Penjualan, restock, penyesuaian, dan void.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <select wire:model.live="filterProductId" class="select w-auto text-xs">
                    <option value="">Semua produk</option>
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                    @endforeach
                </select>
                <select wire:model.live="filterType" class="select w-auto text-xs">
                    <option value="">Semua jenis</option>
                    @foreach ($movementTypes as $type)
                        <option value="{{ $type->value }}">{{ $type->label() }}</option>
                    @endforeach
                </select>
                <input type="text" wire:model.live.debounce.300ms="movementSearch" class="input w-44 text-xs" placeholder="Cari alasan...">
            </div>
        </div>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Produk</th>
                        <th>Jenis</th>
                        <th class="text-center">Perubahan</th>
                        <th class="text-center">Stok</th>
                        <th>Alasan</th>
                        <th>Oleh</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($movements as $movement)
                        <tr wire:key="movement-{{ $movement->id }}">
                            <td class="text-slate-500">{{ tanggal_indonesia($movement->created_at, true) }}</td>
                            <td>
                                <p class="font-semibold text-slate-800">{{ $movement->product->name ?? 'Produk dihapus' }}</p>
                                <p class="text-[11px] text-slate-500">{{ $movement->product->sku ?? '-' }}</p>
                            </td>
                            <td><span class="badge {{ $movement->type->badgeClass() }}">{{ $movement->type->label() }}</span></td>
                            <td class="text-center font-bold {{ $movement->qty_change > 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ $movement->qty_change > 0 ? '+' : '' }}{{ $movement->qty_change }}
                            </td>
                            <td class="text-center text-slate-600">{{ $movement->stock_before }} → <span class="font-semibold text-slate-800">{{ $movement->stock_after }}</span></td>
                            <td class="max-w-xs truncate text-slate-600" title="{{ $movement->reason }}">{{ $movement->reason ?: '-' }}</td>
                            <td class="text-slate-600">{{ $movement->user->name ?? 'Sistem' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <p class="text-sm font-semibold text-slate-700">Belum ada pergerakan stok</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($movements->hasPages())
            <div class="border-t border-slate-100 px-4 py-3">
                {{ $movements->links() }}
            </div>
        @endif
    </div>
</div>
