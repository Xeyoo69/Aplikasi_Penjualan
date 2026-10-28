<?php

namespace App\Livewire\Stock;

use App\Actions\AdjustStockAction;
use App\Enums\StockMovementType;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Manajemen Stok')]
class StockIndex extends Component
{
    use WithPagination;

    public $productId = '';

    public string $adjustType = 'restock';

    public $qtyChange = '';

    public string $reason = '';

    public $filterProductId = '';

    public string $filterType = '';

    public string $movementSearch = '';

    public function mount(): void
    {
        Gate::authorize('viewAny', StockMovement::class);
    }

    public function updatedFilterProductId(): void
    {
        $this->resetPage();
    }

    public function updatedFilterType(): void
    {
        $this->resetPage();
    }

    public function updatedMovementSearch(): void
    {
        $this->resetPage();
    }

    public function save(AdjustStockAction $adjustStock): void
    {
        Gate::authorize('create', StockMovement::class);

        $qtyRules = ['required', 'integer', 'not_in:0'];

        if ($this->adjustType === 'restock') {
            $qtyRules[] = 'min:1';
        }

        $data = $this->validate([
            'productId' => ['required', 'integer', 'exists:products,id'],
            'adjustType' => ['required', 'in:restock,adjustment'],
            'qtyChange' => $qtyRules,
            'reason' => ['required', 'string', 'min:3', 'max:255'],
        ], [
            'productId.required' => 'Pilih produk terlebih dahulu.',
            'productId.exists' => 'Produk tidak ditemukan.',
            'adjustType.required' => 'Pilih jenis penyesuaian.',
            'adjustType.in' => 'Jenis penyesuaian tidak valid.',
            'qtyChange.required' => 'Jumlah perubahan wajib diisi.',
            'qtyChange.integer' => 'Jumlah harus berupa angka bulat.',
            'qtyChange.not_in' => 'Jumlah perubahan tidak boleh 0.',
            'qtyChange.min' => 'Restock harus menambah stok (minimal 1).',
            'reason.required' => 'Alasan wajib diisi.',
            'reason.min' => 'Alasan minimal 3 karakter.',
            'reason.max' => 'Alasan maksimal 255 karakter.',
        ]);

        $product = Product::query()->findOrFail($data['productId']);

        try {
            $product = $adjustStock->execute(
                $product,
                auth()->user(),
                StockMovementType::from($data['adjustType']),
                (int) $data['qtyChange'],
                $data['reason']
            );
        } catch (ValidationException $exception) {
            foreach ($exception->errors() as $field => $messages) {
                $this->addError($field, $messages[0] ?? 'Terjadi kesalahan.');
            }

            return;
        }

        $this->qtyChange = '';
        $this->reason = '';
        $this->dispatch(
            'toast',
            message: "Stok {$product->name} diperbarui menjadi {$product->stock}.",
            type: 'success'
        );
    }

    public function render()
    {
        $products = Product::query()->active()->orderBy('name')->get(['id', 'name', 'sku', 'stock']);

        $movements = StockMovement::query()
            ->with(['product:id,name,sku,deleted_at', 'user:id,name', 'sale:id,invoice_no'])
            ->when($this->filterProductId !== '', fn ($query) => $query->where('product_id', $this->filterProductId))
            ->when($this->filterType !== '', fn ($query) => $query->where('type', $this->filterType))
            ->when($this->movementSearch !== '', fn ($query) => $query->where('reason', 'like', '%'.$this->movementSearch.'%'))
            ->latest()
            ->paginate(15);

        $selectedProduct = $this->productId ? $products->firstWhere('id', (int) $this->productId) : null;

        return view('livewire.stock.stock-index', [
            'products' => $products,
            'selectedProduct' => $selectedProduct,
            'movements' => $movements,
            'lowStockProducts' => Product::query()->active()->lowStock()->orderBy('stock')->limit(6)->get(),
            'movementTypes' => StockMovementType::cases(),
            'totalProducts' => Product::query()->active()->count(),
            'lowStockCount' => Product::query()->active()->lowStock()->count(),
            'movementsToday' => StockMovement::query()->whereDate('created_at', today())->count(),
        ]);
    }
}
