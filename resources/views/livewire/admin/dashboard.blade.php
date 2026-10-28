<div>
    <x-page-header title="Dashboard" subtitle="Ringkasan penjualan {{ now()->translatedFormat('F Y') }}.">
        <x-slot:actions>
            <a href="{{ route('admin.reports') }}" wire:navigate class="btn-secondary">
                <x-icon name="report" class="h-4 w-4" />
                Lihat Laporan
            </a>
            <a href="{{ route('pos') }}" wire:navigate class="btn-primary">
                <x-icon name="pos" class="h-4 w-4" />
                Buka Kasir
            </a>
        </x-slot:actions>
    </x-page-header>

    <div class="mb-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <div class="stat-card">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Omzet hari ini</p>
                    <p class="mt-2 text-2xl font-black text-slate-900">{{ rupiah($revenueToday) }}</p>
                    <p class="mt-1 text-xs text-slate-500">{{ $transactionsToday }} transaksi selesai</p>
                </div>
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                    <x-icon name="cash" class="h-5 w-5" />
                </div>
            </div>
        </div>
        <div class="stat-card">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Omzet bulan ini</p>
                    <p class="mt-2 text-2xl font-black text-slate-900">{{ rupiah($revenueMonth) }}</p>
                    <p class="mt-1 text-xs text-slate-500">{{ $transactionsMonth }} transaksi selesai</p>
                </div>
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-sky-50 text-sky-600">
                    <x-icon name="report" class="h-5 w-5" />
                </div>
            </div>
        </div>
        <div class="stat-card">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Transaksi hari ini</p>
                    <p class="mt-2 text-2xl font-black text-slate-900">{{ number_format($transactionsToday, 0, ',', '.') }}</p>
                    <p class="mt-1 text-xs text-slate-500">Rata-rata {{ rupiah($transactionsToday > 0 ? (int) round($revenueToday / $transactionsToday) : 0) }}</p>
                </div>
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-violet-50 text-violet-600">
                    <x-icon name="history" class="h-5 w-5" />
                </div>
            </div>
        </div>
        <div class="stat-card">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Stok menipis</p>
                    <p class="mt-2 text-2xl font-black text-slate-900">{{ number_format($lowStockProducts->count(), 0, ',', '.') }}</p>
                    <p class="mt-1 text-xs text-slate-500">Produk perlu restock</p>
                </div>
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                    <x-icon name="warning" class="h-5 w-5" />
                </div>
            </div>
        </div>
    </div>

    <div class="grid gap-5 xl:grid-cols-2">
        <div class="card">
            <div class="card-header">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Produk Terlaris</h2>
                    <p class="text-xs text-slate-500">Berdasarkan qty terjual bulan ini.</p>
                </div>
                <a href="{{ route('admin.reports') }}" wire:navigate class="text-xs font-semibold text-emerald-700 hover:underline">Detail</a>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse ($bestSellers as $index => $product)
                    <div class="flex items-center gap-3 px-4 py-3">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-xs font-bold text-slate-500">{{ $index + 1 }}</span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold text-slate-800">{{ $product->product_name }}</p>
                            <p class="text-[11px] text-slate-500">{{ $product->sku ?: '-' }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-bold text-slate-900">{{ number_format((int) $product->qty, 0, ',', '.') }} pcs</p>
                            <p class="text-[11px] text-slate-500">{{ rupiah((int) $product->total) }}</p>
                        </div>
                    </div>
                @empty
                    <div class="empty-state py-10">
                        <p class="text-sm text-slate-500">Belum ada penjualan bulan ini.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Stok Menipis</h2>
                    <p class="text-xs text-slate-500">Stok ≤ batas minimum.</p>
                </div>
                <a href="{{ route('admin.stock') }}" wire:navigate class="text-xs font-semibold text-emerald-700 hover:underline">Kelola stok</a>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse ($lowStockProducts as $product)
                    <div class="flex items-center gap-3 px-4 py-3">
                        <div class="h-9 w-9 shrink-0 overflow-hidden rounded-lg bg-slate-100">
                            @if ($product->image_url)
                                <img src="{{ $product->image_url }}" alt="" class="h-full w-full object-cover">
                            @else
                                <div class="flex h-full w-full items-center justify-center text-xs font-bold text-slate-400">{{ mb_substr($product->name, 0, 1) }}</div>
                            @endif
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold text-slate-800">{{ $product->name }}</p>
                            <p class="text-[11px] text-slate-500">{{ $product->category->name }} · min. {{ $product->min_stock }}</p>
                        </div>
                        <span class="badge {{ $product->stock_badge_class }}">{{ $product->stock }}</span>
                    </div>
                @empty
                    <div class="empty-state py-10">
                        <p class="text-sm text-slate-500">Semua stok dalam kondisi aman.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="card mt-5">
        <div class="card-header">
            <div>
                <h2 class="text-base font-bold text-slate-900">Transaksi Terbaru</h2>
                <p class="text-xs text-slate-500">6 transaksi terakhir.</p>
            </div>
            <a href="{{ route('sales.index') }}" wire:navigate class="text-xs font-semibold text-emerald-700 hover:underline">Lihat semua</a>
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Invoice</th>
                        <th>Waktu</th>
                        <th>Kasir</th>
                        <th class="text-right">Total</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentSales as $sale)
                        <tr>
                            <td>
                                <a href="{{ route('sales.show', $sale) }}" wire:navigate class="font-bold text-emerald-700 hover:underline">{{ $sale->invoice_no }}</a>
                            </td>
                            <td class="text-slate-500">{{ tanggal_indonesia($sale->created_at, true) }}</td>
                            <td class="text-slate-600">{{ $sale->user->name }}</td>
                            <td class="text-right font-semibold text-slate-800">{{ rupiah($sale->total) }}</td>
                            <td class="text-center">
                                <span class="{{ $sale->isVoid() ? 'badge-rose' : 'badge-green' }}">{{ $sale->status->label() }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="empty-state py-10">
                                    <p class="text-sm text-slate-500">Belum ada transaksi.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
