<div>
    <x-page-header title="Pengguna" subtitle="Kelola akun admin dan kasir. Riwayat transaksi tetap tersimpan saat akun dinonaktifkan.">
        <x-slot:actions>
            <button type="button" wire:click="openCreate" class="btn-primary">
                <x-icon name="plus" class="h-4 w-4" />
                Tambah Pengguna
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
                    <input type="text" wire:model.live.debounce.300ms="search" class="input pl-11" placeholder="Cari nama atau email...">
                </div>
            </div>
            <span class="text-xs font-medium text-slate-500">{{ $users->total() }} pengguna</span>
        </div>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Peran</th>
                        <th>Status</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr wire:key="user-{{ $user->id }}">
                            <td>
                                <div class="flex items-center gap-2.5">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-900 text-[10px] font-bold uppercase text-white">
                                        {{ mb_substr($user->name, 0, 2) }}
                                    </div>
                                    <div>
                                        <p class="font-semibold text-slate-800">{{ $user->name }}</p>
                                        @if ($user->id === auth()->id())
                                            <p class="text-[11px] text-emerald-600">Akun Anda</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="text-slate-600">{{ $user->email }}</td>
                            <td>
                                <span class="{{ $user->isAdmin() ? 'badge-sky' : 'badge-slate' }}">{{ $user->role->label() }}</span>
                            </td>
                            <td>
                                <span class="{{ $user->is_active ? 'badge-green' : 'badge-rose' }}">
                                    {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="text-right">
                                <div class="flex justify-end gap-1">
                                    <button type="button" wire:click="openEdit({{ $user->id }})" class="btn-ghost btn-sm">
                                        <x-icon name="pencil" class="h-4 w-4" />
                                        Ubah
                                    </button>
                                    @if ($user->id !== auth()->id())
                                        <button
                                            type="button"
                                            wire:click="toggleActive({{ $user->id }})"
                                            wire:confirm="{{ $user->is_active ? 'Nonaktifkan akun ini?' : 'Aktifkan akun ini?' }}"
                                            class="btn-ghost btn-sm {{ $user->is_active ? 'text-rose-600 hover:bg-rose-50' : 'text-emerald-600 hover:bg-emerald-50' }}"
                                        >
                                            {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <p class="text-sm font-semibold text-slate-700">Tidak ada pengguna ditemukan</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
            <div class="border-t border-slate-100 px-4 py-3">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    @if ($showModal)
        <div class="modal-backdrop" wire:click.self="closeModal">
            <div class="modal-panel max-w-lg" role="dialog" aria-modal="true">
                <div class="card-header">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">{{ $editingId ? 'Ubah Pengguna' : 'Tambah Pengguna' }}</h2>
                        <p class="text-xs text-slate-500">Admin memiliki akses penuh; kasir hanya akses kasir dan riwayatnya.</p>
                    </div>
                    <button type="button" wire:click="closeModal" class="btn-ghost p-2" aria-label="Tutup">
                        <x-icon name="x" class="h-5 w-5" />
                    </button>
                </div>
                <form wire:submit="save" class="space-y-4 p-5">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="user-name" class="label">Nama <span class="text-rose-500">*</span></label>
                            <input id="user-name" type="text" wire:model="name" class="input" autofocus>
                            @error('name')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="user-role" class="label">Peran <span class="text-rose-500">*</span></label>
                            <select id="user-role" wire:model="role" class="select">
                                @foreach ($roles as $roleOption)
                                    <option value="{{ $roleOption->value }}">{{ $roleOption->label() }}</option>
                                @endforeach
                            </select>
                            @error('role')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div>
                        <label for="user-email" class="label">Email <span class="text-rose-500">*</span></label>
                        <input id="user-email" type="email" wire:model="email" class="input" autocomplete="off">
                        @error('email')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="user-password" class="label">
                            Kata Sandi
                            @unless ($editingId)
                                <span class="text-rose-500">*</span>
                            @endunless
                        </label>
                        <input id="user-password" type="password" wire:model="password" class="input" autocomplete="new-password" placeholder="{{ $editingId ? 'Kosongkan jika tidak diubah' : 'Minimal 8 karakter' }}">
                        @error('password')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <label class="flex cursor-pointer items-center gap-2.5 text-sm text-slate-700">
                        <input type="checkbox" wire:model="isActive" class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                        Akun aktif dan dapat masuk
                    </label>
                    @error('isActive')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
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
