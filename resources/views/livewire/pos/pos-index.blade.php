<div class="space-y-5" x-data x-on:focus-search.window="$refs.search.focus()">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="page-title">Kasir</h1>
            <p class="page-subtitle">Scan barcode atau cari produk, lalu selesaikan pembayaran.</p>
        </div>
        <div class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-medium text-slate-500 shadow-sm">
            <span class="flex h-2 w-2 rounded-full bg-emerald-500"></span>
            {{ $this->settings->store_name }} · Pajak {{ number_format((float) $this->settings->tax_percent, 0, ',', '.') }}%
        </div>
    </div>

    @error('cart')
        <div class="flex items-start gap-2 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700">
            <x-icon name="warning" class="mt-0.5 h-5 w-5 shrink-0" />
            <span>{{ $message }}</span>
        </div>
    @enderror

    <div class="grid items-start gap-5 xl:grid-cols-[minmax(0,1fr)_400px]">
        <section class="space-y-4">
            <div class="card p-4">
                <label for="product-search" class="label">Cari produk</label>
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                        <x-icon name="search" class="h-5 w-5" />
                    </span>
                    <input
                        id="product-search"
                        x-ref="search"
                        type="text"
                        wire:model.live.debounce.250ms="search"
                        x-on:keydown.enter.prevent="$wire.scanBarcode($event.target.value)"
                        class="input py-3 pl-11 pr-24 text-base"
                        placeholder="Ketik nama, SKU, atau scan barcode..."
                        autocomplete="off"
                        autofocus
                    >
                    <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                        <span class="rounded-lg bg-slate-100 px-2 py-1 text-[11px] font-semibold text-slate-500">Enter ↵</span>
                    </span>
                </div>
                <div class="mt-2.5 flex flex-wrap items-center justify-between gap-2 text-xs text-slate-500">
                    <span>Barcode scanner: scan lalu tekan Enter.</span>
                    <span>{{ $this->productResults->count() }} produk ditampilkan</span>
                </div>
            </div>

            @if ($this->productResults->isEmpty())
                <div class="card empty-state">
                    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                        <x-icon name="search" class="h-7 w-7" />
                    </div>
                    <p class="mt-4 text-sm font-semibold text-slate-800">Produk tidak ditemukan</p>
                    <p class="mt-1 max-w-sm text-sm text-slate-500">
                        Coba kata kunci lain, periksa SKU/barcode, atau pastikan produk sudah aktif.
                    </p>
                </div>
            @else
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 2xl:grid-cols-4">
                    @foreach ($this->productResults as $product)
                        <button
                            type="button"
                            wire:key="pos-product-{{ $product->id }}"
                            wire:click="addProduct({{ $product->id }})"
                            @disabled($product->stock <= 0)
                            class="group flex h-full flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white text-left shadow-sm transition hover:-translate-y-0.5 hover:border-emerald-300 hover:shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:translate-y-0"
                        >
                            <div class="relative aspect-[4/3] w-full overflow-hidden bg-slate-100">
                                @if ($product->image_url)
                                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="h-full w-full object-cover transition duration-300 group-hover:scale-105" loading="lazy">
                                @else
                                    <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-slate-100 to-slate-200 text-2xl font-black text-slate-400">
                                        {{ mb_substr($product->name, 0, 1) }}
                                    </div>
                                @endif
                                <span class="badge {{ $product->stock_badge_class }} absolute left-2 top-2 shadow-sm">
                                    Stok {{ $product->stock }}
                                </span>
                            </div>
                            <div class="flex flex-1 flex-col p-3">
                                <p class="line-clamp-2 text-sm font-semibold leading-snug text-slate-800">{{ $product->name }}</p>
                                <p class="mt-0.5 text-[11px] text-slate-500">{{ $product->sku }}</p>
                                <div class="mt-auto flex items-center justify-between gap-2 pt-3">
                                    <span class="text-sm font-bold text-emerald-700">{{ rupiah($product->selling_price) }}</span>
                                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700 transition group-hover:bg-emerald-600 group-hover:text-white">
                                        <x-icon name="plus" class="h-4 w-4" />
                                    </span>
                                </div>
                            </div>
                        </button>
                    @endforeach
                </div>
            @endif
        </section>

        <aside class="space-y-4 xl:sticky xl:top-5">
            <div class="card overflow-hidden">
                <div class="card-header">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Keranjang</h2>
                        <p class="text-xs text-slate-500">{{ $this->cartCount }} item</p>
                    </div>
                    @if (count($cart) > 0)
                        <button type="button" wire:click="clearCart" wire:confirm="Kosongkan semua item di keranjang?" class="text-xs font-semibold text-rose-600 hover:text-rose-700">
                            Kosongkan
                        </button>
                    @endif
                </div>

                @if (empty($cart))
                    <div class="px-5 py-9 text-center">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                            <x-icon name="pos" class="h-6 w-6" />
                        </div>
                        <p class="mt-3 text-sm font-semibold text-slate-700">Belum ada item</p>
                        <p class="mt-1 text-xs text-slate-500">Pilih produk di sebelah kiri untuk mulai transaksi.</p>
                    </div>
                @else
                    <div class="max-h-[330px] divide-y divide-slate-100 overflow-y-auto">
                        @foreach ($cart as $item)
                            <div wire:key="cart-item-{{ $item['id'] }}" class="flex gap-3 p-3">
                                <div class="h-12 w-12 shrink-0 overflow-hidden rounded-xl bg-slate-100">
                                    @if (! empty($item['image']))
                                        <img src="{{ $item['image'] }}" alt="" class="h-full w-full object-cover">
                                    @else
                                        <div class="flex h-full w-full items-center justify-center text-sm font-bold text-slate-400">
                                            {{ mb_substr($item['name'], 0, 1) }}
                                        </div>
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-semibold text-slate-800">{{ $item['name'] }}</p>
                                            <p class="text-[11px] text-slate-500">{{ rupiah($item['price']) }} · stok {{ $item['stock'] }}</p>
                                        </div>
                                        <button type="button" wire:click="removeItem({{ $item['id'] }})" class="p-1 text-slate-400 transition hover:text-rose-600" title="Hapus item">
                                            <x-icon name="trash" class="h-4 w-4" />
                                        </button>
                                    </div>
                                    <div class="mt-2 flex items-center justify-between gap-2">
                                        <div class="flex items-center rounded-lg border border-slate-200 bg-white">
                                            <button type="button" wire:click="decrement({{ $item['id'] }})" class="flex h-8 w-8 items-center justify-center text-slate-500 transition hover:bg-slate-50 hover:text-slate-900" title="Kurangi">
                                                <x-icon name="minus" class="h-4 w-4" />
                                            </button>
                                            <input
                                                type="number"
                                                value="{{ $item['qty'] }}"
                                                min="1"
                                                max="{{ $item['stock'] }}"
                                                wire:change="updateQty({{ $item['id'] }}, $event.target.value)"
                                                class="h-8 w-12 border-x border-slate-200 bg-white text-center text-sm font-semibold text-slate-800 focus:outline-none"
                                            >
                                            <button type="button" wire:click="increment({{ $item['id'] }})" class="flex h-8 w-8 items-center justify-center text-slate-500 transition hover:bg-slate-50 hover:text-slate-900" title="Tambah">
                                                <x-icon name="plus" class="h-4 w-4" />
                                            </button>
                                        </div>
                                        <span class="text-sm font-bold text-slate-900">{{ rupiah($item['price'] * $item['qty']) }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="card p-4">
                <div class="flex items-center justify-between gap-2">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Pelanggan</h2>
                        <p class="text-xs text-slate-500">Opsional</p>
                    </div>
                    <button type="button" wire:click="openCustomerModal" class="btn-secondary btn-sm">
                        <x-icon name="plus" class="h-4 w-4" />
                        Pelanggan Baru
                    </button>
                </div>
                <select wire:model.live="customerId" class="select mt-3">
                    <option value="">Pelanggan umum (tanpa nama)</option>
                    @foreach ($this->customers as $customer)
                        <option value="{{ $customer->id }}">
                            {{ $customer->name }}{{ $customer->phone ? ' · '.$customer->phone : '' }}
                        </option>
                    @endforeach
                </select>
                @error('customerId')
                    <p class="field-error">{{ $message }}</p>
                @enderror

                <div class="mt-4 grid grid-cols-2 gap-3">
                    <div>
                        <label class="label">Diskon</label>
                        <select wire:model.live="discountType" class="select">
                            <option value="nominal">Nominal (Rp)</option>
                            <option value="percent">Persen (%)</option>
                        </select>
                    </div>
                    <div>
                        <label class="label">Nilai</label>
                        <input type="number" min="0" wire:model.live.debounce.300ms="discountValue" class="input" placeholder="0">
                    </div>
                </div>
                @error('discountValue')
                    <p class="field-error">{{ $message }}</p>
                @enderror

                @if ((float) $this->settings->tax_percent > 0)
                    <label class="mt-3 flex cursor-pointer items-center justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5">
                        <span class="text-sm text-slate-700">
                            Pajak {{ rtrim(rtrim(number_format((float) $this->settings->tax_percent, 2, ',', '.'), '0'), ',') }}%
                            <span class="block text-[11px] text-slate-500">Dari subtotal setelah diskon</span>
                        </span>
                        <input type="checkbox" wire:model.live="applyTax" class="h-5 w-5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                    </label>
                @endif

                <div class="mt-4 space-y-2 border-t border-dashed border-slate-200 pt-4 text-sm">
                    <div class="flex justify-between text-slate-600">
                        <span>Subtotal</span>
                        <span class="font-semibold text-slate-800">{{ rupiah($this->subtotal) }}</span>
                    </div>
                    @if ($this->discountAmount > 0)
                        <div class="flex justify-between text-rose-600">
                            <span>Diskon</span>
                            <span class="font-semibold">- {{ rupiah($this->discountAmount) }}</span>
                        </div>
                    @endif
                    @if ($this->taxAmount > 0)
                        <div class="flex justify-between text-slate-600">
                            <span>Pajak ({{ rtrim(rtrim(number_format($this->taxPercent, 2, ',', '.'), '0'), ',') }}%)</span>
                            <span class="font-semibold text-slate-800">{{ rupiah($this->taxAmount) }}</span>
                        </div>
                    @endif
                    <div class="flex items-center justify-between border-t border-slate-200 pt-3">
                        <span class="text-sm font-semibold text-slate-700">Total</span>
                        <span class="text-xl font-black text-emerald-700">{{ rupiah($this->total) }}</span>
                    </div>
                </div>

                <div class="mt-4">
                    <label class="label">Metode pembayaran</label>
                    <div class="grid grid-cols-3 gap-2">
                        @foreach (['cash' => 'Tunai', 'transfer' => 'Transfer', 'qris' => 'QRIS'] as $value => $label)
                            <button
                                type="button"
                                wire:click="setPaymentMethod('{{ $value }}')"
                                class="rounded-xl border px-2 py-2.5 text-xs font-bold transition {{ $paymentMethod === $value ? 'border-emerald-600 bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600' : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 hover:bg-slate-50' }}"
                            >
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>
                </div>

                @if ($paymentMethod === 'cash')
                    <div class="mt-3">
                        <label for="paid-amount" class="label">Uang diterima</label>
                        <input id="paid-amount" type="number" min="0" wire:model.live.debounce.200ms="paidAmount" class="input text-right text-lg font-bold" placeholder="0">
                        @error('paidAmount')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                        @error('paid_amount')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                        <div class="mt-2 grid grid-cols-4 gap-1.5">
                            <button type="button" wire:click="fillCash" class="rounded-lg bg-slate-100 px-2 py-1.5 text-[11px] font-semibold text-slate-600 hover:bg-slate-200">Uang pas</button>
                            <button type="button" wire:click="fillCash(50000)" class="rounded-lg bg-slate-100 px-2 py-1.5 text-[11px] font-semibold text-slate-600 hover:bg-slate-200">50rb</button>
                            <button type="button" wire:click="fillCash(100000)" class="rounded-lg bg-slate-100 px-2 py-1.5 text-[11px] font-semibold text-slate-600 hover:bg-slate-200">100rb</button>
                            <button type="button" wire:click="fillCash(200000)" class="rounded-lg bg-slate-100 px-2 py-1.5 text-[11px] font-semibold text-slate-600 hover:bg-slate-200">200rb</button>
                        </div>
                        <div class="mt-3 flex items-center justify-between rounded-xl bg-slate-900 px-3 py-2.5 text-white">
                            <span class="text-xs font-medium text-slate-300">Kembalian</span>
                            <span class="text-lg font-bold">{{ rupiah($this->changeAmount) }}</span>
                        </div>
                    </div>
                @else
                    <div class="mt-3 rounded-xl border border-sky-200 bg-sky-50 px-3 py-2.5 text-xs text-sky-700">
                        Pembayaran {{ $paymentMethod === 'qris' ? 'QRIS' : 'transfer' }} dianggap pas sebesar total tagihan.
                    </div>
                @endif

                <button
                    type="button"
                    wire:click="checkout"
                    wire:loading.attr="disabled"
                    wire:target="checkout"
                    @disabled(empty($cart))
                    class="btn-primary mt-4 w-full py-3 text-base"
                >
                    <span wire:loading.remove wire:target="checkout" class="inline-flex items-center gap-2">
                        <x-icon name="check" class="h-5 w-5" />
                        Bayar {{ rupiah($this->total) }}
                    </span>
                    <span wire:loading wire:target="checkout" class="inline-flex items-center gap-2">
                        <svg class="h-5 w-5 animate-spin" viewBox="0 0 24 24" fill="none">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4Z"></path>
                        </svg>
                        Menyimpan...
                    </span>
                </button>
            </div>
        </aside>
    </div>

    @if ($showCustomerModal)
        <div class="modal-backdrop" wire:click.self="showCustomerModal = false">
            <div class="modal-panel max-w-md" role="dialog" aria-modal="true" aria-labelledby="customer-modal-title">
                <div class="card-header">
                    <div>
                        <h2 id="customer-modal-title" class="text-base font-bold text-slate-900">Pelanggan Baru</h2>
                        <p class="text-xs text-slate-500">Simpan data pelanggan dan langsung pilih.</p>
                    </div>
                    <button type="button" wire:click="showCustomerModal = false" class="btn-ghost p-2">
                        <x-icon name="x" class="h-5 w-5" />
                    </button>
                </div>
                <form wire:submit="quickAddCustomer" class="space-y-4 p-5">
                    <div>
                        <label for="new-customer-name" class="label">Nama <span class="text-rose-500">*</span></label>
                        <input id="new-customer-name" type="text" wire:model="newCustomerName" class="input" placeholder="Nama pelanggan" autofocus>
                        @error('newCustomerName')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="new-customer-phone" class="label">Telepon</label>
                        <input id="new-customer-phone" type="text" wire:model="newCustomerPhone" class="input" placeholder="08xxxxxxxxxx">
                        @error('newCustomerPhone')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="new-customer-address" class="label">Alamat</label>
                        <textarea id="new-customer-address" wire:model="newCustomerAddress" rows="3" class="textarea" placeholder="Alamat pelanggan (opsional)"></textarea>
                        @error('newCustomerAddress')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="flex justify-end gap-2 pt-1">
                        <button type="button" wire:click="showCustomerModal = false" class="btn-secondary">Batal</button>
                        <button type="submit" class="btn-primary" wire:loading.attr="disabled">
                            <x-icon name="check" class="h-4 w-4" />
                            Simpan & Pilih
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if ($showReceiptModal && $this->receiptSale)
        @php($receipt = $this->receiptSale)
        <div class="modal-backdrop" wire:click.self="closeReceipt">
            <div class="modal-panel max-w-lg" role="dialog" aria-modal="true" aria-labelledby="receipt-modal-title">
                <div class="card-header">
                    <div>
                        <h2 id="receipt-modal-title" class="text-base font-bold text-emerald-700">Transaksi berhasil</h2>
                        <p class="text-xs text-slate-500">{{ $receipt->invoice_no }}</p>
                    </div>
                    <button type="button" wire:click="closeReceipt" class="btn-ghost p-2" aria-label="Tutup">
                        <x-icon name="x" class="h-5 w-5" />
                    </button>
                </div>

                <div class="p-5">
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <div class="space-y-1 border-b border-dashed border-slate-300 pb-3 text-center">
                            <p class="text-sm font-black text-slate-900">{{ $this->settings->store_name }}</p>
                            @if ($this->settings->address)
                                <p class="text-[11px] text-slate-500">{{ $this->settings->address }}</p>
                            @endif
                            @if ($this->settings->phone)
                                <p class="text-[11px] text-slate-500">{{ $this->settings->phone }}</p>
                            @endif
                        </div>
                        <div class="grid grid-cols-2 gap-1 py-3 text-[11px] text-slate-600">
                            <span>Invoice</span><span class="text-right font-semibold text-slate-800">{{ $receipt->invoice_no }}</span>
                            <span>Waktu</span><span class="text-right">{{ tanggal_indonesia($receipt->created_at, true) }}</span>
                            <span>Kasir</span><span class="text-right">{{ $receipt->user->name }}</span>
                            <span>Pelanggan</span><span class="text-right">{{ $receipt->customer->name ?? 'Umum' }}</span>
                        </div>
                        <div class="space-y-2 border-y border-dashed border-slate-300 py-3">
                            @foreach ($receipt->items as $line)
                                <div class="text-[11px]">
                                    <p class="font-semibold text-slate-800">{{ $line->product_name }}</p>
                                    <div class="flex justify-between text-slate-500">
                                        <span>{{ $line->qty }} x {{ rupiah($line->unit_price) }}</span>
                                        <span class="font-semibold text-slate-700">{{ rupiah($line->subtotal) }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="space-y-1 pt-3 text-[11px]">
                            <div class="flex justify-between text-slate-600"><span>Subtotal</span><span>{{ rupiah($receipt->subtotal) }}</span></div>
                            @if ($receipt->discount_amount > 0)
                                <div class="flex justify-between text-rose-600"><span>Diskon</span><span>- {{ rupiah($receipt->discount_amount) }}</span></div>
                            @endif
                            @if ($receipt->tax_amount > 0)
                                <div class="flex justify-between text-slate-600"><span>Pajak ({{ rtrim(rtrim(number_format((float) $receipt->tax_percent, 2, ',', '.'), '0'), ',') }}%)</span><span>{{ rupiah($receipt->tax_amount) }}</span></div>
                            @endif
                            <div class="flex justify-between border-t border-slate-300 pt-2 text-sm font-black text-slate-900"><span>Total</span><span>{{ rupiah($receipt->total) }}</span></div>
                            <div class="flex justify-between text-slate-600"><span>Bayar ({{ $receipt->payment_method->label() }})</span><span>{{ rupiah($receipt->paid_amount) }}</span></div>
                            <div class="flex justify-between font-semibold text-slate-800"><span>Kembalian</span><span>{{ rupiah($receipt->change_amount) }}</span></div>
                        </div>
                        @if ($this->settings->receipt_footer)
                            <p class="mt-3 border-t border-dashed border-slate-300 pt-3 text-center text-[10px] text-slate-500">{{ $this->settings->receipt_footer }}</p>
                        @endif
                    </div>

                    <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <p class="mb-1 text-[11px] font-semibold uppercase tracking-wide text-slate-400">Lebar struk</p>
                            <div class="flex gap-1.5">
                                @foreach ([58, 80] as $width)
                                    <button
                                        type="button"
                                        wire:click="$set('receiptWidth', {{ $width }})"
                                        class="rounded-lg px-3 py-1.5 text-xs font-bold transition {{ $receiptWidth === $width ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}"
                                    >
                                        {{ $width }} mm
                                    </button>
                                @endforeach
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button type="button" wire:click="closeReceipt" class="btn-secondary">Transaksi Baru</button>
                            <button
                                type="button"
                                onclick="window.open('{{ route('sales.receipt', ['sale' => $receipt, 'width' => $receiptWidth]) }}', '_blank', 'width=460,height=760')"
                                class="btn-primary"
                            >
                                <x-icon name="printer" class="h-4 w-4" />
                                Cetak Struk
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
