<div>
    <x-page-header title="Laporan" subtitle="Analisis penjualan berdasarkan periode, produk, dan kasir.">
        <x-slot:actions>
            <a
                href="{{ route('admin.reports.export', ['type' => $activeTab, 'from' => $from, 'to' => $to]) }}"
                class="btn-primary"
            >
                <x-icon name="report" class="h-4 w-4" />
                Export CSV
            </a>
        </x-slot:actions>
    </x-page-header>

    <div class="card mb-5">
        <div class="grid gap-3 p-4 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <label for="report-from" class="label">Dari tanggal</label>
                <input id="report-from" type="date" wire:model.live="dateFrom" class="input">
            </div>
            <div>
                <label for="report-to" class="label">Sampai tanggal</label>
                <input id="report-to" type="date" wire:model.live="dateTo" class="input">
            </div>
            <div class="flex items-end sm:col-span-2">
                <p class="text-xs text-slate-500">
                    Periode aktif: <span class="font-semibold text-slate-800">{{ tanggal_indonesia($from) }} – {{ tanggal_indonesia($to) }}</span>.
                    Data void tidak dihitung.
                </p>
            </div>
        </div>
    </div>

    <div class="mb-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
        <div class="stat-card">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Transaksi</p>
            <p class="mt-1 text-xl font-black text-slate-900">{{ number_format($summary['transactions'], 0, ',', '.') }}</p>
        </div>
        <div class="stat-card">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Subtotal</p>
            <p class="mt-1 text-xl font-black text-slate-900">{{ rupiah($summary['subtotal']) }}</p>
        </div>
        <div class="stat-card">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Diskon</p>
            <p class="mt-1 text-xl font-black text-rose-600">{{ rupiah($summary['discount']) }}</p>
        </div>
        <div class="stat-card">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Pajak</p>
            <p class="mt-1 text-xl font-black text-slate-900">{{ rupiah($summary['tax']) }}</p>
        </div>
        <div class="stat-card">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total Omzet</p>
            <p class="mt-1 text-xl font-black text-emerald-700">{{ rupiah($summary['total']) }}</p>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="flex flex-wrap gap-2">
                @foreach (['sales' => 'Penjualan per Hari', 'products' => 'Per Produk', 'cashiers' => 'Per Kasir'] as $tab => $label)
                    <button
                        type="button"
                        wire:click="setTab('{{ $tab }}')"
                        class="rounded-xl px-3.5 py-2 text-sm font-semibold transition {{ $activeTab === $tab ? 'bg-slate-900 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}"
                    >
                        {{ $label }}
                    </button>
                @endforeach
            </div>
            <span class="text-xs text-slate-500">{{ $rows->total() }} baris</span>
        </div>

        <div class="table-wrap">
            @if ($activeTab === 'sales')
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th class="text-center">Transaksi</th>
                            <th class="text-right">Subtotal</th>
                            <th class="text-right">Diskon</th>
                            <th class="text-right">Pajak</th>
                            <th class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $row)
                            <tr wire:key="report-sale-{{ $row->sale_date }}">
                                <td class="font-semibold text-slate-800">{{ tanggal_indonesia($row->sale_date) }}</td>
                                <td class="text-center text-slate-600">{{ number_format((int) $row->transactions, 0, ',', '.') }}</td>
                                <td class="text-right text-slate-600">{{ rupiah((int) $row->subtotal) }}</td>
                                <td class="text-right text-rose-600">{{ rupiah((int) $row->discount) }}</td>
                                <td class="text-right text-slate-600">{{ rupiah((int) $row->tax) }}</td>
                                <td class="text-right font-bold text-slate-900">{{ rupiah((int) $row->total) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6"><div class="empty-state"><p class="text-sm text-slate-500">Tidak ada data pada periode ini.</p></div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            @elseif ($activeTab === 'products')
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>SKU</th>
                            <th class="text-center">Qty Terjual</th>
                            <th class="text-right">Total Penjualan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $row)
                            <tr wire:key="report-product-{{ md5($row->product_name.$row->sku) }}">
                                <td class="font-semibold text-slate-800">{{ $row->product_name }}</td>
                                <td class="text-slate-500">{{ $row->sku ?: '-' }}</td>
                                <td class="text-center font-bold text-slate-800">{{ number_format((int) $row->qty, 0, ',', '.') }}</td>
                                <td class="text-right font-semibold text-slate-800">{{ rupiah((int) $row->total) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4"><div class="empty-state"><p class="text-sm text-slate-500">Tidak ada produk terjual pada periode ini.</p></div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            @else
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Kasir</th>
                            <th class="text-center">Jumlah Transaksi</th>
                            <th class="text-right">Total Penjualan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $row)
                            <tr wire:key="report-cashier-{{ $row->id }}">
                                <td class="font-semibold text-slate-800">{{ $row->name }}</td>
                                <td class="text-center text-slate-600">{{ number_format((int) $row->transactions, 0, ',', '.') }}</td>
                                <td class="text-right font-bold text-slate-900">{{ rupiah((int) $row->total) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3"><div class="empty-state"><p class="text-sm text-slate-500">Tidak ada transaksi pada periode ini.</p></div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            @endif
        </div>

        @if ($rows->hasPages())
            <div class="border-t border-slate-100 px-4 py-3">
                {{ $rows->links() }}
            </div>
        @endif
    </div>
</div>
