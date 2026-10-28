<div>
    <x-page-header title="Pengaturan Toko" subtitle="Informasi ini tampil di struk dan digunakan untuk perhitungan pajak." />

    <form wire:submit="save" class="grid gap-5 lg:grid-cols-3">
        <div class="space-y-5 lg:col-span-2">
            <div class="card">
                <div class="card-header">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Informasi Toko</h2>
                        <p class="text-xs text-slate-500">Nama, alamat, dan telepon untuk struk.</p>
                    </div>
                </div>
                <div class="space-y-4 p-5">
                    <div>
                        <label for="store-name" class="label">Nama Toko <span class="text-rose-500">*</span></label>
                        <input id="store-name" type="text" wire:model="storeName" class="input" placeholder="Nama toko">
                        @error('storeName')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="store-address" class="label">Alamat</label>
                        <textarea id="store-address" wire:model="address" rows="2" class="textarea" placeholder="Alamat toko"></textarea>
                        @error('address')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="sm:w-1/2">
                        <label for="store-phone" class="label">Telepon</label>
                        <input id="store-phone" type="text" wire:model="phone" class="input" placeholder="08xxxxxxxxxx">
                        @error('phone')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Pajak & Struk</h2>
                        <p class="text-xs text-slate-500">Pajak dihitung dari subtotal setelah diskon.</p>
                    </div>
                </div>
                <div class="space-y-4 p-5">
                    <div class="sm:w-1/3">
                        <label for="tax-percent" class="label">Persen Pajak</label>
                        <div class="relative">
                            <input id="tax-percent" type="number" step="0.01" min="0" max="100" wire:model="taxPercent" class="input pr-10">
                            <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3.5 text-sm font-semibold text-slate-500">%</span>
                        </div>
                        <p class="mt-1 text-[11px] text-slate-500">Isi 0 jika toko tidak memungut pajak.</p>
                        @error('taxPercent')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="receipt-footer" class="label">Footer Struk</label>
                        <textarea id="receipt-footer" wire:model="receiptFooter" rows="2" class="textarea" placeholder="Terima kasih telah berbelanja."></textarea>
                        <p class="mt-1 text-[11px] text-slate-500">Teks tambahan di bagian bawah struk.</p>
                        @error('receiptFooter')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="btn-primary" wire:loading.attr="disabled">
                    <x-icon name="check" class="h-4 w-4" />
                    Simpan Pengaturan
                </button>
            </div>
        </div>

        <div class="space-y-5">
            <div class="card p-5">
                <h2 class="text-sm font-bold text-slate-900">Pratinjau Struk</h2>
                <div class="mt-3 rounded-xl border border-dashed border-slate-300 bg-slate-50 p-3 font-mono text-[10px] leading-relaxed text-slate-700">
                    <p class="text-center text-xs font-black">{{ $storeName ?: 'Nama Toko' }}</p>
                    @if ($address)
                        <p class="text-center">{{ $address }}</p>
                    @endif
                    @if ($phone)
                        <p class="text-center">{{ $phone }}</p>
                    @endif
                    <div class="my-2 border-t border-dashed border-slate-300"></div>
                    <div class="flex justify-between"><span>Contoh Produk</span><span>{{ rupiah(25000) }}</span></div>
                    <div class="flex justify-between text-slate-500"><span>1 x {{ rupiah(25000) }}</span><span>{{ rupiah(25000) }}</span></div>
                    <div class="my-2 border-t border-dashed border-slate-300"></div>
                    <div class="flex justify-between"><span>Subtotal</span><span>{{ rupiah(25000) }}</span></div>
                    <div class="flex justify-between"><span>Pajak ({{ rtrim(rtrim(number_format((float) $taxPercent, 2, ',', '.'), '0'), ',') }}%)</span><span>{{ rupiah(25000 * (float) $taxPercent / 100) }}</span></div>
                    <div class="flex justify-between font-bold"><span>Total</span><span>{{ rupiah(25000 + (25000 * (float) $taxPercent / 100)) }}</span></div>
                    <div class="my-2 border-t border-dashed border-slate-300"></div>
                    <p class="text-center">{{ $receiptFooter ?: 'Terima kasih telah berbelanja.' }}</p>
                </div>
            </div>

            <div class="rounded-2xl border border-sky-200 bg-sky-50 p-4 text-xs leading-relaxed text-sky-800">
                Perubahan nama toko langsung terlihat di sidebar dan struk baru. Transaksi lama tetap menyimpan snapshot produk, bukan data toko.
            </div>
        </div>
    </form>
</div>
