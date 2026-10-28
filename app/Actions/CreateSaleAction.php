<?php

namespace App\Actions;

use App\Enums\DiscountType;
use App\Enums\PaymentMethod;
use App\Enums\SaleStatus;
use App\Enums\StockMovementType;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Setting;
use App\Models\StockMovement;
use App\Models\User;
use App\Services\InvoiceNumberGenerator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CreateSaleAction
{
    public function __construct(private readonly InvoiceNumberGenerator $invoiceNumbers) {}

    /**
     * @param  array{
     *     items: array<int, array{product_id: int|string, qty: int|string}>,
     *     customer_id?: int|string|null,
     *     discount_type?: string|null,
     *     discount_value?: int|string|null,
     *     apply_tax?: bool,
     *     payment_method: string,
     *     paid_amount?: int|string|null
     * }  $data
     */
    public function execute(array $data, User $cashier): Sale
    {
        $validated = Validator::make($data, [
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
            'discount_type' => ['nullable', Rule::enum(DiscountType::class)],
            'discount_value' => ['nullable', 'integer', 'min:0'],
            'apply_tax' => ['boolean'],
            'payment_method' => ['required', Rule::enum(PaymentMethod::class)],
            'paid_amount' => ['nullable', 'integer', 'min:0'],
        ], [
            'items.required' => 'Keranjang masih kosong.',
            'items.min' => 'Keranjang masih kosong.',
            'items.*.qty.min' => 'Jumlah produk minimal 1.',
            'payment_method.required' => 'Pilih metode pembayaran.',
            'payment_method.enum' => 'Metode pembayaran tidak valid.',
        ])->validate();

        // Gabungkan baris duplikat agar pengecekan stok tidak bisa dilewati.
        $lines = collect($validated['items'])
            ->groupBy(fn (array $item) => (int) $item['product_id'])
            ->map(fn ($group, $productId) => [
                'product_id' => (int) $productId,
                'qty' => (int) $group->sum('qty'),
            ])
            ->sortBy('product_id')
            ->values();

        $discountType = filled($validated['discount_type'] ?? null)
            ? DiscountType::from($validated['discount_type'])
            : null;
        $discountValue = (int) ($validated['discount_value'] ?? 0);

        if ($discountType === DiscountType::Percent && $discountValue > 100) {
            throw ValidationException::withMessages([
                'discount_value' => 'Diskon persen tidak boleh lebih dari 100%.',
            ]);
        }

        $paymentMethod = PaymentMethod::from($validated['payment_method']);

        return DB::transaction(function () use (
            $lines,
            $validated,
            $cashier,
            $discountType,
            $discountValue,
            $paymentMethod
        ) {
            // Kunci semua produk yang terlibat dalam urutan tetap untuk menghindari deadlock.
            $products = Product::query()
                ->whereIn('id', $lines->pluck('product_id'))
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $subtotal = 0;
            $saleItems = [];

            foreach ($lines as $line) {
                /** @var Product|null $product */
                $product = $products->get($line['product_id']);

                if (! $product || ! $product->is_active) {
                    throw ValidationException::withMessages([
                        'cart' => 'Ada produk yang tidak ditemukan atau sudah nonaktif.',
                    ]);
                }

                if ($line['qty'] > $product->stock) {
                    throw ValidationException::withMessages([
                        'cart' => "Stok {$product->name} tidak mencukupi. Tersedia {$product->stock}, diminta {$line['qty']}.",
                    ]);
                }

                $lineSubtotal = $product->selling_price * $line['qty'];
                $subtotal += $lineSubtotal;

                $saleItems[] = [
                    'product' => $product,
                    'qty' => $line['qty'],
                    'unit_price' => $product->selling_price,
                    'subtotal' => $lineSubtotal,
                ];
            }

            $discountAmount = match ($discountType) {
                DiscountType::Percent => (int) floor($subtotal * $discountValue / 100),
                DiscountType::Nominal => $discountValue,
                default => 0,
            };
            $discountAmount = min($discountAmount, $subtotal);

            $settings = Setting::current();
            $taxPercent = ($validated['apply_tax'] ?? true)
                ? (float) $settings->tax_percent
                : 0.0;
            $taxAmount = (int) round(($subtotal - $discountAmount) * $taxPercent / 100);
            $total = $subtotal - $discountAmount + $taxAmount;

            $paidAmount = (int) ($validated['paid_amount'] ?? 0);

            if ($paymentMethod === PaymentMethod::Cash) {
                if ($paidAmount < $total) {
                    throw ValidationException::withMessages([
                        'paid_amount' => 'Nominal bayar kurang dari total tagihan.',
                    ]);
                }
            } else {
                $paidAmount = $total;
            }

            $changeAmount = max(0, $paidAmount - $total);

            $sale = Sale::query()->create([
                'invoice_no' => $this->invoiceNumbers->generate(),
                'user_id' => $cashier->id,
                'customer_id' => $validated['customer_id'] ?? null,
                'subtotal' => $subtotal,
                'discount_type' => $discountType,
                'discount_value' => $discountValue,
                'discount_amount' => $discountAmount,
                'tax_percent' => $taxPercent,
                'tax_amount' => $taxAmount,
                'total' => $total,
                'paid_amount' => $paidAmount,
                'change_amount' => $changeAmount,
                'payment_method' => $paymentMethod,
                'status' => SaleStatus::Completed,
            ]);

            foreach ($saleItems as $item) {
                /** @var Product $product */
                $product = $item['product'];
                $stockBefore = $product->stock;
                $stockAfter = $stockBefore - $item['qty'];

                $sale->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'unit_price' => $item['unit_price'],
                    'qty' => $item['qty'],
                    'subtotal' => $item['subtotal'],
                ]);

                $product->stock = $stockAfter;
                $product->save();

                StockMovement::query()->create([
                    'product_id' => $product->id,
                    'user_id' => $cashier->id,
                    'sale_id' => $sale->id,
                    'type' => StockMovementType::Sale,
                    'qty_change' => -$item['qty'],
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                    'reason' => 'Penjualan '.$sale->invoice_no,
                ]);
            }

            return $sale->load(['items', 'customer', 'user']);
        }, 3);
    }
}
