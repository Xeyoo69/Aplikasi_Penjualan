<?php

namespace Tests\Feature\Sale;

use App\Actions\CreateSaleAction;
use App\Actions\VoidSaleAction;
use App\Enums\SaleStatus;
use App\Enums\StockMovementType;
use App\Models\Product;
use App\Models\Setting;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class VoidSaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_void_mengembalikan_stok_dan_mencatat_pergerakan(): void
    {
        Setting::query()->create(['store_name' => 'Toko Uji', 'tax_percent' => 0]);

        $kasir = User::factory()->kasir()->create();
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create(['selling_price' => 25000, 'stock' => 10]);

        $sale = app(CreateSaleAction::class)->execute([
            'items' => [['product_id' => $product->id, 'qty' => 2]],
            'payment_method' => 'cash',
            'paid_amount' => 50000,
        ], $kasir);

        $this->assertSame(8, $product->refresh()->stock);

        $voided = app(VoidSaleAction::class)->execute($sale, $admin, 'Salah input produk');

        $this->assertSame(SaleStatus::Void, $voided->status);
        $this->assertNotNull($voided->voided_at);
        $this->assertSame($admin->id, $voided->voided_by);
        $this->assertSame('Salah input produk', $voided->void_reason);
        $this->assertSame(10, $product->refresh()->stock);

        $voidMovement = StockMovement::query()
            ->where('sale_id', $sale->id)
            ->where('type', StockMovementType::Void->value)
            ->firstOrFail();

        $this->assertSame(2, $voidMovement->qty_change);
        $this->assertSame(8, $voidMovement->stock_before);
        $this->assertSame(10, $voidMovement->stock_after);
        $this->assertStringContainsString('Salah input produk', $voidMovement->reason);

        // Transaksi tetap ada (tidak dihapus).
        $this->assertDatabaseHas('sales', [
            'id' => $sale->id,
            'status' => 'void',
            'voided_by' => $admin->id,
        ]);
        $this->assertDatabaseCount('sales', 1);
    }

    public function test_void_kedua_kali_ditolak(): void
    {
        Setting::query()->create(['store_name' => 'Toko Uji', 'tax_percent' => 0]);

        $kasir = User::factory()->kasir()->create();
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create(['selling_price' => 25000, 'stock' => 10]);

        $sale = app(CreateSaleAction::class)->execute([
            'items' => [['product_id' => $product->id, 'qty' => 1]],
            'payment_method' => 'cash',
            'paid_amount' => 25000,
        ], $kasir);

        $void = app(VoidSaleAction::class);
        $void->execute($sale, $admin, 'Pelanggan membatalkan');

        $this->expectException(ValidationException::class);

        try {
            $void->execute($sale->refresh(), $admin, 'Pelanggan membatalkan lagi');
        } finally {
            $this->assertSame(10, $product->refresh()->stock);
        }
    }

    public function test_kasir_tidak_boleh_void_transaksi(): void
    {
        Setting::query()->create(['store_name' => 'Toko Uji', 'tax_percent' => 0]);

        $kasir = User::factory()->kasir()->create();
        $product = Product::factory()->create(['selling_price' => 25000, 'stock' => 10]);

        $sale = app(CreateSaleAction::class)->execute([
            'items' => [['product_id' => $product->id, 'qty' => 1]],
            'payment_method' => 'cash',
            'paid_amount' => 25000,
        ], $kasir);

        $this->expectException(AuthorizationException::class);

        app(VoidSaleAction::class)->execute($sale, $kasir, 'Mencoba void sendiri');
    }
}
