<?php

namespace App\Livewire\Pos;

use App\Actions\CreateSaleAction;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Setting;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Kasir')]
class PosIndex extends Component
{
    public string $search = '';

    /** @var array<int, array{id: int, name: string, sku: string, price: int, qty: int, stock: int, image: string|null}> */
    public array $cart = [];

    public $customerId = '';

    public string $discountType = 'nominal';

    public $discountValue = 0;

    public bool $applyTax = true;

    public string $paymentMethod = 'cash';

    public $paidAmount = 0;

    public ?int $lastSaleId = null;

    public bool $showReceiptModal = false;

    public int $receiptWidth = 58;

    public bool $showCustomerModal = false;

    public string $newCustomerName = '';

    public string $newCustomerPhone = '';

    public string $newCustomerAddress = '';

    public function mount(): void
    {
        $this->applyTax = (float) Setting::current()->tax_percent > 0;
    }

    #[Computed]
    public function settings(): Setting
    {
        return Setting::current();
    }

    #[Computed]
    public function productResults()
    {
        return Product::query()
            ->active()
            ->with('category:id,name')
            ->search($this->search)
            ->orderBy('name')
            ->limit(24)
            ->get();
    }

    #[Computed]
    public function customers()
    {
        return Customer::query()
            ->orderBy('name')
            ->limit(500)
            ->get(['id', 'name', 'phone']);
    }

    #[Computed]
    public function subtotal(): int
    {
        return (int) collect($this->cart)->sum(fn (array $item) => $item['price'] * $item['qty']);
    }

    #[Computed]
    public function discountAmount(): int
    {
        $value = max(0, (int) $this->discountValue);

        $amount = $this->discountType === 'percent'
            ? (int) floor($this->subtotal() * min(100, $value) / 100)
            : $value;

        return min($amount, $this->subtotal());
    }

    #[Computed]
    public function taxPercent(): float
    {
        return $this->applyTax ? (float) $this->settings()->tax_percent : 0.0;
    }

    #[Computed]
    public function taxAmount(): int
    {
        return (int) round(($this->subtotal() - $this->discountAmount()) * $this->taxPercent() / 100);
    }

    #[Computed]
    public function total(): int
    {
        return $this->subtotal() - $this->discountAmount() + $this->taxAmount();
    }

    #[Computed]
    public function changeAmount(): int
    {
        return $this->paymentMethod === 'cash'
            ? max(0, (int) $this->paidAmount - $this->total())
            : 0;
    }

    #[Computed]
    public function cartCount(): int
    {
        return (int) collect($this->cart)->sum('qty');
    }

    #[Computed]
    public function receiptSale(): ?Sale
    {
        if (! $this->lastSaleId) {
            return null;
        }

        return Sale::query()
            ->with(['items', 'customer', 'user'])
            ->find($this->lastSaleId);
    }

    public function addProduct(int $productId): void
    {
        $product = Product::query()->active()->find($productId);

        if (! $product) {
            $this->notify('Produk tidak ditemukan atau sudah nonaktif.', 'error');

            return;
        }

        $currentQty = (int) ($this->cart[$productId]['qty'] ?? 0);

        if ($currentQty + 1 > $product->stock) {
            $this->notify("Stok {$product->name} hanya {$product->stock}.", 'error');

            return;
        }

        if (isset($this->cart[$productId])) {
            $this->cart[$productId]['qty']++;
            $this->cart[$productId]['stock'] = $product->stock;
            $this->cart[$productId]['price'] = $product->selling_price;
            $this->cart[$productId]['name'] = $product->name;
        } else {
            $this->cart[$productId] = [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'price' => $product->selling_price,
                'qty' => 1,
                'stock' => $product->stock,
                'image' => $product->image_url,
            ];
        }

        $this->search = '';
        $this->dispatch('focus-search');
    }

    public function scanBarcode(?string $term = null): void
    {
        $term = trim((string) ($term ?? $this->search));

        if ($term === '') {
            return;
        }

        $product = Product::query()
            ->active()
            ->where(function ($query) use ($term) {
                $query->where('barcode', $term)->orWhere('sku', $term);
            })
            ->first();

        if (! $product) {
            $matches = Product::query()->active()->search($term)->limit(2)->get();

            if ($matches->count() === 1) {
                $product = $matches->first();
            }
        }

        if (! $product) {
            $this->notify("Produk dengan kode \"{$term}\" tidak ditemukan.", 'error');

            return;
        }

        $this->addProduct($product->id);
    }

    public function increment(int $productId): void
    {
        $product = Product::query()->active()->find($productId);

        if (! $product) {
            $this->removeItem($productId);
            $this->notify('Produk sudah tidak tersedia.', 'error');

            return;
        }

        $qty = (int) ($this->cart[$productId]['qty'] ?? 0);

        if ($qty + 1 > $product->stock) {
            $this->notify("Stok {$product->name} hanya {$product->stock}.", 'error');

            return;
        }

        $this->cart[$productId]['qty'] = $qty + 1;
        $this->cart[$productId]['stock'] = $product->stock;
    }

    public function decrement(int $productId): void
    {
        if (! isset($this->cart[$productId])) {
            return;
        }

        if ($this->cart[$productId]['qty'] <= 1) {
            $this->removeItem($productId);

            return;
        }

        $this->cart[$productId]['qty']--;
    }

    public function updateQty(int $productId, mixed $qty): void
    {
        if (! isset($this->cart[$productId])) {
            return;
        }

        $qty = (int) $qty;

        if ($qty < 1) {
            $this->removeItem($productId);

            return;
        }

        $stock = (int) $this->cart[$productId]['stock'];

        if ($qty > $stock) {
            $this->cart[$productId]['qty'] = $stock;
            $this->notify("Jumlah disesuaikan dengan stok tersedia ({$stock}).", 'error');

            return;
        }

        $this->cart[$productId]['qty'] = $qty;
    }

    public function removeItem(int $productId): void
    {
        unset($this->cart[$productId]);
    }

    public function clearCart(): void
    {
        $this->cart = [];
        $this->discountValue = 0;
        $this->paidAmount = 0;
    }

    public function updatedPaymentMethod(): void
    {
        $this->paidAmount = $this->paymentMethod === 'cash' ? 0 : $this->total();
    }

    public function updatedDiscountType(): void
    {
        $this->discountValue = 0;
    }

    public function setPaymentMethod(string $method): void
    {
        if (! in_array($method, ['cash', 'transfer', 'qris'], true)) {
            return;
        }

        $this->paymentMethod = $method;
        $this->updatedPaymentMethod();
    }

    public function fillCash(int $amount = 0): void
    {
        $this->paymentMethod = 'cash';
        $this->paidAmount = $amount > 0 ? $amount : $this->total();
    }

    public function openCustomerModal(): void
    {
        Gate::authorize('create', Customer::class);
        $this->resetValidation();
        $this->newCustomerName = '';
        $this->newCustomerPhone = '';
        $this->newCustomerAddress = '';
        $this->showCustomerModal = true;
    }

    public function quickAddCustomer(): void
    {
        Gate::authorize('create', Customer::class);

        $data = $this->validate([
            'newCustomerName' => ['required', 'string', 'max:255'],
            'newCustomerPhone' => ['nullable', 'string', 'max:30'],
            'newCustomerAddress' => ['nullable', 'string', 'max:500'],
        ], [
            'newCustomerName.required' => 'Nama pelanggan wajib diisi.',
            'newCustomerName.max' => 'Nama pelanggan maksimal 255 karakter.',
            'newCustomerPhone.max' => 'Nomor telepon maksimal 30 karakter.',
            'newCustomerAddress.max' => 'Alamat maksimal 500 karakter.',
        ]);

        $customer = Customer::query()->create([
            'name' => $data['newCustomerName'],
            'phone' => $data['newCustomerPhone'] ?: null,
            'address' => $data['newCustomerAddress'] ?: null,
        ]);

        $this->customerId = $customer->id;
        $this->showCustomerModal = false;
        $this->newCustomerName = '';
        $this->newCustomerPhone = '';
        $this->newCustomerAddress = '';
        $this->notify('Pelanggan berhasil ditambahkan.', 'success');
    }

    public function checkout(CreateSaleAction $createSale): void
    {
        Gate::authorize('create', Sale::class);

        if (empty($this->cart)) {
            $this->addError('cart', 'Keranjang masih kosong.');
            $this->notify('Keranjang masih kosong.', 'error');

            return;
        }

        $this->customerId = $this->customerId ?: null;

        $this->validate([
            'customerId' => ['nullable', 'integer', 'exists:customers,id'],
            'discountType' => ['required', 'in:nominal,percent'],
            'discountValue' => [
                'required',
                'integer',
                'min:0',
                $this->discountType === 'percent' ? 'max:100' : 'max:999999999',
            ],
            'paymentMethod' => ['required', 'in:cash,transfer,qris'],
            'paidAmount' => $this->paymentMethod === 'cash'
                ? ['required', 'integer', 'min:0']
                : ['nullable', 'integer', 'min:0'],
        ], [
            'customerId.exists' => 'Pelanggan yang dipilih tidak valid.',
            'discountValue.required' => 'Nilai diskon wajib diisi.',
            'discountValue.integer' => 'Nilai diskon harus berupa angka.',
            'discountValue.min' => 'Nilai diskon tidak boleh negatif.',
            'discountValue.max' => $this->discountType === 'percent'
                ? 'Diskon persen maksimal 100%.'
                : 'Nilai diskon terlalu besar.',
            'paymentMethod.required' => 'Pilih metode pembayaran.',
            'paidAmount.required' => 'Nominal bayar wajib diisi.',
            'paidAmount.integer' => 'Nominal bayar harus berupa angka.',
            'paidAmount.min' => 'Nominal bayar tidak boleh negatif.',
        ]);

        try {
            $sale = $createSale->execute([
                'items' => collect($this->cart)
                    ->map(fn (array $item) => [
                        'product_id' => $item['id'],
                        'qty' => $item['qty'],
                    ])
                    ->values()
                    ->all(),
                'customer_id' => $this->customerId,
                'discount_type' => $this->discountType,
                'discount_value' => (int) $this->discountValue,
                'apply_tax' => $this->applyTax,
                'payment_method' => $this->paymentMethod,
                'paid_amount' => (int) $this->paidAmount,
            ], auth()->user());
        } catch (ValidationException $exception) {
            foreach ($exception->errors() as $field => $messages) {
                $this->addError($field, $messages[0] ?? 'Terjadi kesalahan.');
            }

            $this->notify(
                collect($exception->errors())->flatten()->first() ?? 'Transaksi gagal diproses.',
                'error'
            );

            return;
        }

        $this->lastSaleId = $sale->id;
        $this->showReceiptModal = true;

        $settings = Setting::current();
        $this->cart = [];
        $this->customerId = '';
        $this->discountType = 'nominal';
        $this->discountValue = 0;
        $this->paymentMethod = 'cash';
        $this->paidAmount = 0;
        $this->applyTax = (float) $settings->tax_percent > 0;
        $this->resetValidation();

        $this->notify("Transaksi {$sale->invoice_no} berhasil disimpan.", 'success');
    }

    public function closeReceipt(): void
    {
        $this->showReceiptModal = false;
        $this->lastSaleId = null;
    }

    private function notify(string $message, string $type = 'success'): void
    {
        $this->dispatch('toast', message: $message, type: $type);
    }

    public function render()
    {
        return view('livewire.pos.pos-index');
    }
}
