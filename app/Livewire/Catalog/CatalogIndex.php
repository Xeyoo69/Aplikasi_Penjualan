<?php

namespace App\Livewire\Catalog;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Katalog Produk')]
class CatalogIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public $categoryFilter = '';

    public function mount(): void
    {
        Gate::authorize('viewAny', Product::class);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCategoryFilter(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $products = Product::query()
            ->active()
            ->with('category:id,name')
            ->search($this->search)
            ->when($this->categoryFilter !== '', fn ($query) => $query->where('category_id', $this->categoryFilter))
            ->orderBy('name')
            ->paginate(12);

        return view('livewire.catalog.catalog-index', [
            'products' => $products,
            'categories' => Category::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }
}
