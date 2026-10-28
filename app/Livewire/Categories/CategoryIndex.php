<?php

namespace App\Livewire\Categories;

use App\Models\Category;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Kategori')]
class CategoryIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $showModal = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $description = '';

    public function mount(): void
    {
        Gate::authorize('viewAny', Category::class);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        Gate::authorize('create', Category::class);
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $category = Category::query()->findOrFail($id);
        Gate::authorize('update', $category);

        $this->editingId = $category->id;
        $this->name = $category->name;
        $this->description = (string) $category->description;
        $this->resetValidation();
        $this->showModal = true;
    }

    public function save(): void
    {
        $category = $this->editingId ? Category::query()->findOrFail($this->editingId) : null;

        $category
            ? Gate::authorize('update', $category)
            : Gate::authorize('create', Category::class);

        $data = $this->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('categories', 'name')->ignore($this->editingId),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
        ], [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.max' => 'Nama kategori maksimal 100 karakter.',
            'name.unique' => 'Nama kategori sudah digunakan.',
            'description.max' => 'Deskripsi maksimal 1000 karakter.',
        ]);

        if ($category) {
            $category->update([
                'name' => $data['name'],
                'description' => $data['description'] ?: null,
            ]);
            $this->dispatch('toast', message: 'Kategori berhasil diperbarui.', type: 'success');
        } else {
            Category::query()->create([
                'name' => $data['name'],
                'description' => $data['description'] ?: null,
            ]);
            $this->dispatch('toast', message: 'Kategori berhasil ditambahkan.', type: 'success');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        $category = Category::query()->withCount(['products' => fn ($query) => $query->withTrashed()])->findOrFail($id);
        Gate::authorize('delete', $category);

        if ($category->products_count > 0) {
            $this->dispatch('toast', message: 'Kategori tidak bisa dihapus karena masih memiliki produk.', type: 'error');

            return;
        }

        $category->delete();
        $this->dispatch('toast', message: 'Kategori berhasil dihapus.', type: 'success');
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->description = '';
        $this->resetValidation();
    }

    public function render()
    {
        $categories = Category::query()
            ->withCount('products')
            ->when($this->search !== '', fn ($query) => $query->where('name', 'like', '%'.$this->search.'%'))
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.categories.category-index', [
            'categories' => $categories,
        ]);
    }
}
