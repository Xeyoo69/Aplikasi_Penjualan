<?php

namespace App\Actions;

use App\Enums\SaleStatus;
use App\Enums\StockMovementType;
use App\Models\Product;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VoidSaleAction
{
    public function execute(Sale $sale, User $admin, string $reason): Sale
    {
        if (! $admin->isAdmin()) {
            throw new AuthorizationException('Hanya admin yang dapat membatalkan transaksi.');
        }

        $reason = trim($reason);

        if (mb_strlen($reason) < 5) {
            throw ValidationException::withMessages([
                'void_reason' => 'Alasan void minimal 5 karakter.',
            ]);
        }

        return DB::transaction(function () use ($sale, $admin, $reason) {
            /** @var Sale $lockedSale */
            $lockedSale = Sale::query()->whereKey($sale->id)->lockForUpdate()->firstOrFail();

            if ($lockedSale->status === SaleStatus::Void) {
                throw ValidationException::withMessages([
                    'void_reason' => 'Transaksi ini sudah di-void sebelumnya.',
                ]);
            }

            $items = $lockedSale->items()->orderBy('id')->get();
            $products = Product::withTrashed()
                ->whereIn('id', $items->pluck('product_id')->filter())
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($items as $item) {
                /** @var Product|null $product */
                $product = $products->get($item->product_id);

                if (! $product) {
                    continue;
                }

                $stockBefore = $product->stock;
                $stockAfter = $stockBefore + $item->qty;

                $product->stock = $stockAfter;
                $product->save();

                StockMovement::query()->create([
                    'product_id' => $product->id,
                    'user_id' => $admin->id,
                    'sale_id' => $lockedSale->id,
                    'type' => StockMovementType::Void,
                    'qty_change' => $item->qty,
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                    'reason' => 'Void '.$lockedSale->invoice_no.': '.$reason,
                ]);
            }

            $lockedSale->update([
                'status' => SaleStatus::Void,
                'voided_at' => now(),
                'voided_by' => $admin->id,
                'void_reason' => $reason,
            ]);

            return $lockedSale->load(['items', 'customer', 'user', 'voidedBy']);
        }, 3);
    }
}
