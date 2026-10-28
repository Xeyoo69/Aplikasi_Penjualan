<?php

namespace App\Actions;

use App\Enums\StockMovementType;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdjustStockAction
{
    public function execute(
        Product $product,
        User $user,
        StockMovementType $type,
        int $qtyChange,
        ?string $reason
    ): Product {
        if (! in_array($type, [StockMovementType::Restock, StockMovementType::Adjustment], true)) {
            throw ValidationException::withMessages([
                'type' => 'Jenis penyesuaian stok tidak valid.',
            ]);
        }

        if ($type === StockMovementType::Restock && $qtyChange <= 0) {
            throw ValidationException::withMessages([
                'qty_change' => 'Jumlah restock harus lebih dari 0.',
            ]);
        }

        if ($qtyChange === 0) {
            throw ValidationException::withMessages([
                'qty_change' => 'Jumlah perubahan stok tidak boleh 0.',
            ]);
        }

        $reason = trim((string) $reason);

        if (mb_strlen($reason) < 3) {
            throw ValidationException::withMessages([
                'reason' => 'Alasan penyesuaian minimal 3 karakter.',
            ]);
        }

        return DB::transaction(function () use ($product, $user, $type, $qtyChange, $reason) {
            /** @var Product $locked */
            $locked = Product::query()->whereKey($product->id)->lockForUpdate()->firstOrFail();

            $stockBefore = $locked->stock;
            $stockAfter = $stockBefore + $qtyChange;

            if ($stockAfter < 0) {
                throw ValidationException::withMessages([
                    'qty_change' => "Stok tidak boleh minus. Stok saat ini {$stockBefore}.",
                ]);
            }

            $locked->stock = $stockAfter;
            $locked->save();

            StockMovement::query()->create([
                'product_id' => $locked->id,
                'user_id' => $user->id,
                'sale_id' => null,
                'type' => $type,
                'qty_change' => $qtyChange,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'reason' => $reason,
            ]);

            return $locked;
        }, 3);
    }
}
