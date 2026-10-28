<div>
    <x-page-header title="Riwayat Transaksi" subtitle="Cari transaksi, cetak ulang struk, dan pantau status penjualan.">
        <x-slot:actions>
            <a href="{{ route('pos') }}" wire:navigate class="btn-primary">
                <x-icon name="pos" class="h-4 w-4" />
                Buka Kasir
            </a>
        </x-slot:actions>
    </x-page-header>

    <div class="card mb-5">
        <div class="card-header">
            <div>
                <h2 class="text-base font-bold text-slate-900">Filter</h2>
                <p class="text-xs text-slate-500">{{ tanggal_indonesia($from) }} – {{ tanggal_indonesia($to) }}</p>
            </div>
            <button type="button" wire:click="resetFilters" class="btn-ghost btn-sm">Reset filter</button>
        </div>
        <div class="grid gap-3 p-4 sm:grid-cols-2 lg:grid-cols-6">
            <div>
                <label for="date-from" class="label">Dari tanggal</label>
                <input id="date-from" type="date" wire:model.live="dateFrom" class="input">
            </div>
            <div>
                <label for="date-to" class="label">Sampai tanggal</label>
                <input id="date-to" type="date" wire:model.live="dateTo" class="input">
            </div>
            <div>
                <label for="payment-filter" class="label">Metode bayar</label>
                <select id="payment-filter" wire:model.live="paymentFilter" class="select">
                    <option value="">Semua metode</option>
                    @foreach ($paymentMethods as $method)
                        <option value="{{ $method->value }}">{{ $method->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="status-filter" class="label">Status</label>
                <select id="status-filter" wire:model.live="statusFilter" class="select">
                    <option value="">Semua status</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}">{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>
            @if (auth()->user()->isAdmin())
                <div>
                    <label for="cashier-filter" class="label">Kasir</label>
                    <select id="cashier-filter" wire:model.live="cashierId" class="select">
                        <option value="">Semua kasir</option>
                        @foreach ($cashiers as $cashier)
                            <option value="{{ $cashier->id }}">{{ $cashier->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div>
                <label for="invoice-search" class="label">Cari invoice</label>
                <input id="invoice-search" type="text" wire:model.live.debounce.300ms="search" class="input" placeholder="INV-...">
            </div>
        </div>
    </div>

    <div class="mb-5 grid gap-3 sm:grid-cols-3">
        <div class="stat-card">
            <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Transaksi selesai</p>
            <p class="mt-1 text-2xl font-black text-slate-900">{{ number_format($summaryCount, 0, ',', '.') }}</p>
        </div>
        <div class="stat-card">
            <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Omzet (tanpa void)</p>
            <p class="mt-1 text-2xl font-black text-emerald-700">{{ rupiah($summaryTotal) }}</p>
        </div>
        <div class="stat-card">
            <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Rata-rata / transaksi</p>
            <p class="mt-1 text-2xl font-black text-slate-900">{{ rupiah($summaryCount > 0 ? (int) round($summaryTotal / $summaryCount) : 0) }}</p>
        </div>
    </div>

    <div class="card">
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Invoice</th>
                        <th>Waktu</th>
                        <th>Kasir</th>
                        <th>Pelanggan</th>
                        <th>Pembayaran</th>
                        <th class="text-right">Total</th>
                        <th class="text-center">Status</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sales as $sale)
                        <tr wire:key="sale-{{ $sale->id }}" class="{{ $sale->isVoid() ? 'opacity-60' : '' }}">
                            <td>
                                <a href="{{ route('sales.show', $sale) }}" wire:navigate class="font-bold text-emerald-700 hover:underline">{{ $sale->invoice_no }}</a>
                                <p class="text-[11px] text-slate-500">{{ $sale->items_count }} item</p>
                            </td>
                            <td class="text-slate-500">{{ tanggal_indonesia($sale->created_at, true) }}</td>
                            <td class="text-slate-600">{{ $sale->user->name }}</td>
                            <td class="text-slate-600">{{ $sale->customer->name ?? 'Umum' }}</td>
                            <td>
                                <span class="badge-slate">{{ $sale->payment_method->label() }}</span>
                            </td>
                            <td class="text-right font-bold text-slate-900">{{ rupiah($sale->total) }}</td>
                            <td class="text-center">
                                <span class="{{ $sale->isVoid() ? 'badge-rose' : 'badge-green' }}">{{ $sale->status->label() }}</span>
                            </td>
                            <td class="text-right">
                                <div class="flex justify-end gap-1">
                                    <a href="{{ route('sales.show', $sale) }}" wire:navigate class="btn-ghost btn-sm">Detail</a>
                                    <a href="{{ route('sales.receipt', ['sale' => $sale, 'width' => 58]) }}" target="_blank" class="btn-ghost btn-sm" title="Cetak struk">
                                        <x-icon name="printer" class="h-4 w-4" />
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                                        <x-icon name="history" class="h-6 w-6" />
                                    </div>
                                    <p class="mt-3 text-sm font-semibold text-slate-700">Tidak ada transaksi</p>
                                    <p class="mt-1 text-sm text-slate-500">Coba ubah filter tanggal atau mulai transaksi baru di kasir.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($sales->hasPages())
            <div class="border-t border-slate-100 px-4 py-3">
                {{ $sales->links() }}
            </div>
        @endif
    </div>
</div>
