<div>
    <x-page-header title="Pelanggan" subtitle="Data pelanggan dapat digunakan saat transaksi di kasir.">
        <x-slot:actions>
            <button type="button" wire:click="openCreate" class="btn-primary">
                <x-icon name="plus" class="h-4 w-4" />
                Tambah Pelanggan
            </button>
        </x-slot:actions>
    </x-page-header>

    <div class="card">
        <div class="card-header">
            <div class="w-full max-w-sm">
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                        <x-icon name="search" class="h-5 w-5" />
                    </span>
                    <input type="text" wire:model.live.debounce.300ms="search" class="input pl-11" placeholder="Cari nama atau telepon...">
                </div>
            </div>
            <span class="text-xs font-medium text-slate-500">{{ $customers->total() }} pelanggan</span>
        </div>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Telepon</th>
                        <th>Alamat</th>
                        <th class="text-center">Transaksi</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($customers as $customer)
                        <tr wire:key="customer-{{ $customer->id }}">
                            <td class="font-semibold text-slate-800">{{ $customer->name }}</td>
                            <td class="text-slate-600">{{ $customer->phone ?: '-' }}</td>
                            <td class="max-w-xs truncate text-slate-500">{{ $customer->address ?: '-' }}</td>
                            <td class="text-center"><span class="badge-slate">{{ $customer->sales_count }}</span></td>
                            <td class="text-right">
                                <div class="flex justify-end gap-1">
                                    <button type="button" wire:click="openEdit({{ $customer->id }})" class="btn-ghost btn-sm">
                                        <x-icon name="pencil" class="h-4 w-4" />
                                        Ubah
                                    </button>
                                    <button
                                        type="button"
                                        wire:click="delete({{ $customer->id }})"
                                        wire:confirm="Hapus pelanggan {{ $customer->name }}? Data transaksi lama tetap tersimpan."
                                        class="btn-ghost btn-sm text-rose-600 hover:bg-rose-50 hover:text-rose-700"
                                    >
                                        <x-icon name="trash" class="h-4 w-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                                        <x-icon name="users" class="h-6 w-6" />
                                    </div>
                                    <p class="mt-3 text-sm font-semibold text-slate-700">Belum ada pelanggan</p>
                                    <p class="mt-1 text-sm text-slate-500">Tambahkan pelanggan untuk menyimpan riwayat pembeliannya.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($customers->hasPages())
            <div class="border-t border-slate-100 px-4 py-3">
                {{ $customers->links() }}
            </div>
        @endif
    </div>

    @if ($showModal)
        <div class="modal-backdrop" wire:click.self="closeModal">
            <div class="modal-panel max-w-md" role="dialog" aria-modal="true">
                <div class="card-header">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">{{ $editingId ? 'Ubah Pelanggan' : 'Tambah Pelanggan' }}</h2>
                        <p class="text-xs text-slate-500">Nama wajib, telepon dan alamat opsional.</p>
                    </div>
                    <button type="button" wire:click="closeModal" class="btn-ghost p-2" aria-label="Tutup">
                        <x-icon name="x" class="h-5 w-5" />
                    </button>
                </div>
                <form wire:submit="save" class="space-y-4 p-5">
                    <div>
                        <label for="customer-name" class="label">Nama <span class="text-rose-500">*</span></label>
                        <input id="customer-name" type="text" wire:model="name" class="input" placeholder="Nama pelanggan" autofocus>
                        @error('name')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="customer-phone" class="label">Telepon</label>
                        <input id="customer-phone" type="text" wire:model="phone" class="input" placeholder="08xxxxxxxxxx">
                        @error('phone')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="customer-address" class="label">Alamat</label>
                        <textarea id="customer-address" wire:model="address" rows="3" class="textarea" placeholder="Alamat pelanggan (opsional)"></textarea>
                        @error('address')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="flex justify-end gap-2 pt-1">
                        <button type="button" wire:click="closeModal" class="btn-secondary">Batal</button>
                        <button type="submit" class="btn-primary" wire:loading.attr="disabled">
                            <x-icon name="check" class="h-4 w-4" />
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
