<div>
    <div class="mb-5 flex flex-wrap items-end justify-between gap-3">
        <div>
            <a href="{{ route('sales.index') }}" wire:navigate class="mb-2 inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-emerald-700">
                <x-icon name="arrow-left" class="h-4 w-4" />
                Kembali ke riwayat
            </a>
            <div class="flex flex-wrap items-center gap-2">
                <h1 class="page-title">{{ $sale->invoice_no }}</h1>
                <span class="{{ $sale->isVoid() ? 'badge-rose' : 'badge-green' }}">{{ $sale->status->label() }}</span>
            </div>
            <p class="page-subtitle">{{ tanggal_indonesia($sale->created_at, true) }} · {{ $sale->user->name }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('sales.receipt', ['sale' => $sale, 'width' => 58]) }}" target="_blank" class="btn-secondary">
                <x-icon name="printer" class="h-4 w-4" />
                Struk 58 mm
            </a>
            <a href="{{ route('sales.receipt', ['sale' => $sale, 'width' => 80]) }}" target="_blank" class="btn-secondary">
                <x-icon name="printer" class="h-4 w-4" />
                Struk 80 mm
            </a>
            @can('void', $sale)
                <button type="button" wire:click="openVoidModal" class="btn-danger">
                    <x-icon name="warning" class="h-4 w-4" />
                    Void Transaksi
                </button>
            @endcan
        </div>
    </div>

    @if ($sale->isVoid())
        <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 p-4">
            <div class="flex items-start gap-3">
                <x-icon name="warning" class="mt-0.5 h-5 w-5 shrink-0 text-rose-600" />
                <div class="text-sm text-rose-800">
                    <p class="font-bold">Transaksi ini sudah di-void</p>
                    <p class="mt-0.5">
                        {{ tanggal_indonesia($sale->voided_at, true) }} oleh {{ $sale->voidedBy->name ?? 'sistem' }}.
                    </p>
                    <p class="mt-1"><span class="font-semibold">Alasan:</span> {{ $sale->void_reason }}</p>
                </div>
            </div>
        </div>
    @endif

    <div class="grid gap-5 lg:grid-cols-3">
        <div class="space-y-5 lg:col-span-2">
            <div class="card">
                <div class="card-header">
                    <h2 class="text-base font-bold text-slate-900">Item Transaksi</h2>
                    <span class="badge-slate">{{ $sale->items->count() }} item</span>
                </div>
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th class="text-right">Harga</th>
                                <th class="text-center">Qty</th>
                                <th class="text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($sale->items as $item)
                                <tr>
                                    <td>
                                        <p class="font-semibold text-slate-800">{{ $item->product_name }}</p>
                                        <p class="text-[11px] text-slate-500">{{ $item->sku ?: '-' }}</p>
                                    </td>
                                    <td class="text-right text-slate-600">{{ rupiah($item->unit_price) }}</td>
                                    <td class="text-center text-slate-700">{{ $item->qty }}</td>
                                    <td class="text-right font-semibold text-slate-800">{{ rupiah($item->subtotal) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card p-5">
                <h2 class="text-base font-bold text-slate-900">Informasi Transaksi</h2>
                <dl class="mt-4 grid gap-4 text-sm sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Pelanggan</dt>
                        <dd class="mt-1 font-semibold text-slate-800">{{ $sale->customer->name ?? 'Pelanggan umum' }}</dd>
                        @if ($sale->customer?->phone)
                            <dd class="text-xs text-slate-500">{{ $sale->customer->phone }}</dd>
                        @endif
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Metode Pembayaran</dt>
                        <dd class="mt-1 font-semibold text-slate-800">{{ $sale->payment_method->label() }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Kasir</dt>
                        <dd class="mt-1 font-semibold text-slate-800">{{ $sale->user->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Waktu Transaksi</dt>
                        <dd class="mt-1 font-semibold text-slate-800">{{ tanggal_indonesia($sale->created_at, true) }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <div class="space-y-5">
            <div class="card p-5">
                <h2 class="text-base font-bold text-slate-900">Ringkasan Pembayaran</h2>
                <dl class="mt-4 space-y-2.5 text-sm">
                    <div class="flex justify-between text-slate-600">
                        <dt>Subtotal</dt>
                        <dd class="font-semibold text-slate-800">{{ rupiah($sale->subtotal) }}</dd>
                    </div>
                    @if ($sale->discount_amount > 0)
                        <div class="flex justify-between text-rose-600">
                            <dt>
                                Diskon
                                @if ($sale->discount_type)
                                    ({{ $sale->discount_type->label() }} {{ $sale->discount_type === \App\Enums\DiscountType::Percent ? $sale->discount_value.'%' : rupiah($sale->discount_value) }})
                                @endif
                            </dt>
                            <dd class="font-semibold">- {{ rupiah($sale->discount_amount) }}</dd>
                        </div>
                    @endif
                    @if ($sale->tax_amount > 0)
                        <div class="flex justify-between text-slate-600">
                            <dt>Pajak ({{ rtrim(rtrim(number_format((float) $sale->tax_percent, 2, ',', '.'), '0'), ',') }}%)</dt>
                            <dd class="font-semibold text-slate-800">{{ rupiah($sale->tax_amount) }}</dd>
                        </div>
                    @endif
                    <div class="flex items-center justify-between border-t border-slate-200 pt-3">
                        <dt class="font-bold text-slate-900">Total</dt>
                        <dd class="text-xl font-black text-emerald-700">{{ rupiah($sale->total) }}</dd>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <dt>Dibayar</dt>
                        <dd class="font-semibold text-slate-800">{{ rupiah($sale->paid_amount) }}</dd>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <dt>Kembalian</dt>
                        <dd class="font-semibold text-slate-800">{{ rupiah($sale->change_amount) }}</dd>
                    </div>
                </dl>
            </div>

            <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-4 text-xs leading-relaxed text-slate-500">
                Data produk pada transaksi disimpan sebagai snapshot. Perubahan harga atau nama produk setelah transaksi tidak mengubah riwayat ini.
            </div>
        </div>
    </div>

    @if ($showVoidModal)
        <div class="modal-backdrop" wire:click.self="showVoidModal = false">
            <div class="modal-panel max-w-md" role="dialog" aria-modal="true">
                <div class="card-header">
                    <div>
                        <h2 class="text-base font-bold text-rose-700">Void Transaksi</h2>
                        <p class="text-xs text-slate-500">{{ $sale->invoice_no }} · {{ rupiah($sale->total) }}</p>
                    </div>
                    <button type="button" wire:click="showVoidModal = false" class="btn-ghost p-2" aria-label="Tutup">
                        <x-icon name="x" class="h-5 w-5" />
                    </button>
                </div>
                <form wire:submit="voidSale" class="space-y-4 p-5">
                    <div class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-2.5 text-xs text-amber-800">
                        Stok semua item akan dikembalikan dan transaksi tetap tersimpan sebagai void. Tindakan ini tidak dapat dibatalkan.
                    </div>
                    <div>
                        <label for="void-reason" class="label">Alasan void <span class="text-rose-500">*</span></label>
                        <textarea id="void-reason" wire:model="voidReason" rows="3" class="textarea" placeholder="Contoh: Salah input produk / pelanggan batal membeli"></textarea>
                        @error('voidReason')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                        @error('void_reason')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" wire:click="showVoidModal = false" class="btn-secondary">Batal</button>
                        <button type="submit" class="btn-danger" wire:loading.attr="disabled">
                            <x-icon name="warning" class="h-4 w-4" />
                            Void & Kembalikan Stok
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
