<div>
    <x-page-header title="Kategori" subtitle="Kelompokkan produk agar lebih mudah dicari di kasir.">
        <x-slot:actions>
            <button type="button" wire:click="openCreate" class="btn-primary">
                <x-icon name="plus" class="h-4 w-4" />
                Tambah Kategori
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
                    <input type="text" wire:model.live.debounce.300ms="search" class="input pl-11" placeholder="Cari kategori...">
                </div>
            </div>
            <span class="text-xs font-medium text-slate-500">{{ $categories->total() }} kategori</span>
        </div>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nama Kategori</th>
                        <th>Deskripsi</th>
                        <th class="text-center">Produk</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categories as $category)
                        <tr wire:key="category-{{ $category->id }}">
                            <td class="font-semibold text-slate-800">{{ $category->name }}</td>
                            <td class="max-w-xs truncate text-slate-500">{{ $category->description ?: '-' }}</td>
                            <td class="text-center">
                                <span class="badge-slate">{{ $category->products_count }}</span>
                            </td>
                            <td class="text-right">
                                <div class="flex justify-end gap-1">
                                    <button type="button" wire:click="openEdit({{ $category->id }})" class="btn-ghost btn-sm" title="Ubah">
                                        <x-icon name="pencil" class="h-4 w-4" />
                                        Ubah
                                    </button>
                                    <button
                                        type="button"
                                        wire:click="delete({{ $category->id }})"
                                        wire:confirm="Hapus kategori {{ $category->name }}?"
                                        class="btn-ghost btn-sm text-rose-600 hover:bg-rose-50 hover:text-rose-700"
                                        title="Hapus"
                                    >
                                        <x-icon name="trash" class="h-4 w-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <div class="empty-state">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                                        <x-icon name="categories" class="h-6 w-6" />
                                    </div>
                                    <p class="mt-3 text-sm font-semibold text-slate-700">Belum ada kategori</p>
                                    <p class="mt-1 text-sm text-slate-500">Tambahkan kategori untuk mulai mengelompokkan produk.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($categories->hasPages())
            <div class="border-t border-slate-100 px-4 py-3">
                {{ $categories->links() }}
            </div>
        @endif
    </div>

    @if ($showModal)
        <div class="modal-backdrop" wire:click.self="closeModal">
            <div class="modal-panel max-w-md" role="dialog" aria-modal="true">
                <div class="card-header">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">{{ $editingId ? 'Ubah Kategori' : 'Tambah Kategori' }}</h2>
                        <p class="text-xs text-slate-500">Nama kategori harus unik.</p>
                    </div>
                    <button type="button" wire:click="closeModal" class="btn-ghost p-2" aria-label="Tutup">
                        <x-icon name="x" class="h-5 w-5" />
                    </button>
                </div>
                <form wire:submit="save" class="space-y-4 p-5">
                    <div>
                        <label for="category-name" class="label">Nama <span class="text-rose-500">*</span></label>
                        <input id="category-name" type="text" wire:model="name" class="input" placeholder="Contoh: Minuman" autofocus>
                        @error('name')
                            <p class="field-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="category-description" class="label">Deskripsi</label>
                        <textarea id="category-description" wire:model="description" rows="3" class="textarea" placeholder="Deskripsi singkat (opsional)"></textarea>
                        @error('description')
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
