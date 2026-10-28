<?php

namespace App\Livewire\Products;

use App\Actions\AdjustStockAction;
use App\Enums\StockMovementType;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Produk')]
class ProductIndex extends Component
{
    use WithFileUploads, WithPagination;

    public string $search = '';

    public $categoryFilter = '';

    public bool $lowOnly = false;

    public bool $showModal = false;

    public ?int $editingId = null;

    public $categoryId = '';

    public string $name = '';

    public string $sku = '';

    public string $barcode = '';

    public $purchasePrice = 0;

    public $sellingPrice = 0;

    public $stock = 0;

    public $minStock = 0;

    public bool $isActive = true;

    public $image = null;

    public ?string $existingImage = null;

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

    public function updatedLowOnly(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        Gate::authorize('create', Product::class);

        $this->resetForm();
        $this->categoryId = Category::query()->orderBy('name')->value('id') ?? '';
        $this->stock = 0;
        $this->minStock = 5;
        $this->isActive = true;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $product = Product::query()->findOrFail($id);
        Gate::authorize('update', $product);

        $this->editingId = $product->id;
        $this->categoryId = $product->category_id;
        $this->name = $product->name;
        $this->sku = $product->sku;
        $this->barcode = (string) $product->barcode;
        $this->purchasePrice = $product->purchase_price;
        $this->sellingPrice = $product->selling_price;
        $this->stock = $product->stock;
        $this->minStock = $product->min_stock;
        $this->isActive = $product->is_active;
        $this->existingImage = $product->image;
        $this->image = null;
        $this->resetValidation();
        $this->showModal = true;
    }

    public function save(AdjustStockAction $adjustStock): void
    {
        $product = $this->editingId ? Product::query()->findOrFail($this->editingId) : null;

        $product
            ? Gate::authorize('update', $product)
            : Gate::authorize('create', Product::class);

        $data = $this->validate([
            'categoryId' => ['required', 'integer', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'sku' => [
                'required',
                'string',
                'max:64',
                Rule::unique('products', 'sku')->ignore($this->editingId),
            ],
            'barcode' => [
                'nullable',
                'string',
                'max:64',
                Rule::unique('products', 'barcode')->ignore($this->editingId),
            ],
            'purchasePrice' => ['required', 'integer', 'min:0', 'max:999999999999'],
            'sellingPrice' => ['required', 'integer', 'min:0', 'max:999999999999'],
            'stock' => ['required', 'integer', 'min:0', 'max:999999999'],
            'minStock' => ['required', 'integer', 'min:0', 'max:999999999'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'isActive' => ['boolean'],
        ], [
            'categoryId.required' => 'Kategori wajib dipilih.',
            'categoryId.exists' => 'Kategori tidak valid.',
            'name.required' => 'Nama produk wajib diisi.',
            'name.max' => 'Nama produk maksimal 255 karakter.',
            'sku.required' => 'SKU wajib diisi.',
            'sku.max' => 'SKU maksimal 64 karakter.',
            'sku.unique' => 'SKU sudah digunakan produk lain.',
            'barcode.max' => 'Barcode maksimal 64 karakter.',
            'barcode.unique' => 'Barcode sudah digunakan produk lain.',
            'purchasePrice.required' => 'Harga beli wajib diisi.',
            'purchasePrice.integer' => 'Harga beli harus berupa angka bulat.',
            'purchasePrice.min' => 'Harga beli tidak boleh negatif.',
            'sellingPrice.required' => 'Harga jual wajib diisi.',
            'sellingPrice.integer' => 'Harga jual harus berupa angka bulat.',
            'sellingPrice.min' => 'Harga jual tidak boleh negatif.',
            'stock.required' => 'Stok awal wajib diisi.',
            'stock.integer' => 'Stok harus berupa angka bulat.',
            'stock.min' => 'Stok tidak boleh negatif.',
            'minStock.required' => 'Stok minimum wajib diisi.',
            'minStock.integer' => 'Stok minimum harus berupa angka bulat.',
            'minStock.min' => 'Stok minimum tidak boleh negatif.',
            'image.image' => 'Berkas harus berupa gambar.',
            'image.mimes' => 'Format gambar harus JPG, JPEG, PNG, atau WEBP.',
            'image.max' => 'Ukuran gambar maksimal 2 MB.',
        ]);

        $newImage = $this->image ? $this->image->store('products', 'public') : null;

        $payload = [
            'category_id' => $data['categoryId'],
            'name' => $data['name'],
            'sku' => strtoupper($data['sku']),
            'barcode' => $data['barcode'] ?: null,
            'purchase_price' => (int) $data['purchasePrice'],
            'selling_price' => (int) $data['sellingPrice'],
            'min_stock' => (int) $data['minStock'],
            'is_active' => (bool) $data['isActive'],
        ];

        if ($newImage) {
            $payload['image'] = $newImage;
        }

        if ($product) {
            $oldImage = $product->image;
            $product->update($payload);

            if ($newImage && $oldImage) {
                Storage::disk('public')->delete($oldImage);
            }

            $this->dispatch('toast', message: 'Produk berhasil diperbarui.', type: 'success');
        } else {
            $dataStock = (int) $data['stock'];

            DB::transaction(function () use ($payload, $dataStock, $adjustStock) {
                $newProduct = Product::query()->create($payload + ['stock' => 0]);

                if ($dataStock > 0) {
                    $adjustStock->execute(
                        $newProduct,
                        auth()->user(),
                        StockMovementType::Restock,
                        $dataStock,
                        'Stok awal produk'
                    );
                }
            });

            $this->dispatch('toast', message: 'Produk berhasil ditambahkan.', type: 'success');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        $product = Product::query()->findOrFail($id);
        Gate::authorize('delete', $product);

        $product->delete();
        $this->dispatch('toast', message: 'Produk berhasil dihapus (data lama tetap tersimpan di riwayat).', type: 'success');
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->categoryId = '';
        $this->name = '';
        $this->sku = '';
        $this->barcode = '';
        $this->purchasePrice = 0;
        $this->sellingPrice = 0;
        $this->stock = 0;
        $this->minStock = 0;
        $this->isActive = true;
        $this->image = null;
        $this->existingImage = null;
        $this->resetValidation();
    }

    public function render()
    {
        $products = Product::query()
            ->with('category:id,name')
            ->search($this->search)
            ->when($this->categoryFilter !== '', fn ($query) => $query->where('category_id', $this->categoryFilter))
            ->when($this->lowOnly, fn ($query) => $query->lowStock())
            ->latest('id')
            ->paginate(10);

        return view('livewire.products.product-index', [
            'products' => $products,
            'categories' => Category::query()->orderBy('name')->get(['id', 'name']),
            'lowStockCount' => Product::query()->active()->lowStock()->count(),
        ]);
    }
}
