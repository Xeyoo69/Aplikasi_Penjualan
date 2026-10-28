<?php

namespace Tests\Feature\Stock;

use App\Actions\AdjustStockAction;
use App\Enums\StockMovementType;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AdjustStockActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_restock_menambah_stok_dan_mencatat_pergerakan(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create(['stock' => 5]);

        $updated = app(AdjustStockAction::class)->execute(
            $product,
            $admin,
            StockMovementType::Restock,
            10,
            'Pembelian dari supplier'
        );

        $this->assertSame(15, $updated->stock);

        $movement = StockMovement::query()->firstOrFail();
        $this->assertSame(StockMovementType::Restock, $movement->type);
        $this->assertSame(10, $movement->qty_change);
        $this->assertSame(5, $movement->stock_before);
        $this->assertSame(15, $movement->stock_after);
        $this->assertSame('Pembelian dari supplier', $movement->reason);
        $this->assertSame($admin->id, $movement->user_id);
    }

    public function test_penyesuaian_tidak_boleh_membuat_stok_minus(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create(['stock' => 3]);

        $this->expectException(ValidationException::class);

        try {
            app(AdjustStockAction::class)->execute(
                $product,
                $admin,
                StockMovementType::Adjustment,
                -5,
                'Stok opname'
            );
        } finally {
            $this->assertSame(3, $product->refresh()->stock);
            $this->assertDatabaseCount('stock_movements', 0);
        }
    }
}
