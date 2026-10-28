<?php

namespace Tests\Feature\Sale;

use App\Actions\CreateSaleAction;
use App\Enums\StockMovementType;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Setting;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CreateSaleActionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::query()->create([
            'store_name' => 'Toko Uji',
            'tax_percent' => 10,
            'receipt_footer' => 'Terima kasih',
        ]);
    }

    public function test_transaksi_sukses_menghitung_total_diskon_pajak_kembalian_dan_stok(): void
    {
        $kasir = User::factory()->kasir()->create();
        $product = Product::factory()->create([
            'selling_price' => 25000,
            'purchase_price' => 20000,
            'stock' => 10,
        ]);

        $sale = app(CreateSaleAction::class)->execute([
            'items' => [
                ['product_id' => $product->id, 'qty' => 2],
            ],
            'discount_type' => 'nominal',
            'discount_value' => 5000,
            'apply_tax' => true,
            'payment_method' => 'cash',
            'paid_amount' => 100000,
        ], $kasir);

        $this->assertSame(50000, $sale->subtotal);
        $this->assertSame(5000, $sale->discount_amount);
        $this->assertSame('10.00', $sale->tax_percent);
        $this->assertSame(4500, $sale->tax_amount);
        $this->assertSame(49500, $sale->total);
        $this->assertSame(100000, $sale->paid_amount);
        $this->assertSame(50500, $sale->change_amount);
        $this->assertMatchesRegularExpression('/^INV-\d{8}-0001$/', $sale->invoice_no);

        $this->assertSame(8, $product->refresh()->stock);

        $movement = StockMovement::query()->where('sale_id', $sale->id)->firstOrFail();
        $this->assertSame(StockMovementType::Sale, $movement->type);
        $this->assertSame(-2, $movement->qty_change);
        $this->assertSame(10, $movement->stock_before);
        $this->assertSame(8, $movement->stock_after);

        $item = $sale->items()->firstOrFail();
        $this->assertSame($product->name, $item->product_name);
        $this->assertSame(25000, $item->unit_price);
        $this->assertSame(2, $item->qty);

        // Snapshot harga tetap walau produk diubah setelah transaksi.
        $product->update(['name' => 'Nama Baru', 'selling_price' => 99000]);
        $this->assertSame(25000, $item->refresh()->unit_price);
        $this->assertNotSame('Nama Baru', $item->product_name);
    }

    public function test_diskon_persen_dan_pajak_dari_pengaturan_dihitung_benar(): void
    {
        Setting::query()->firstOrFail()->update(['tax_percent' => 11]);

        $kasir = User::factory()->kasir()->create();
        $product = Product::factory()->create([
            'selling_price' => 100000,
            'stock' => 5,
        ]);

        $sale = app(CreateSaleAction::class)->execute([
            'items' => [['product_id' => $product->id, 'qty' => 1]],
            'discount_type' => 'percent',
            'discount_value' => 10,
            'apply_tax' => true,
            'payment_method' => 'cash',
            'paid_amount' => 100000,
        ], $kasir);

        $this->assertSame(100000, $sale->subtotal);
        $this->assertSame(10000, $sale->discount_amount);
        $this->assertSame(9900, $sale->tax_amount);
        $this->assertSame(99900, $sale->total);
        $this->assertSame(100, $sale->change_amount);
    }

    public function test_transaksi_ditolak_jika_qty_melebihi_stok(): void
    {
        $kasir = User::factory()->kasir()->create();
        $product = Product::factory()->create([
            'selling_price' => 10000,
            'stock' => 1,
        ]);

        try {
            app(CreateSaleAction::class)->execute([
                'items' => [['product_id' => $product->id, 'qty' => 2]],
                'payment_method' => 'cash',
                'paid_amount' => 50000,
            ], $kasir);

            $this->fail('Transaksi seharusnya ditolak karena stok tidak cukup.');
        } catch (ValidationException $exception) {
            $this->assertStringContainsString('tidak mencukupi', $exception->errors()['cart'][0]);
        }

        $this->assertDatabaseCount('sales', 0);
        $this->assertDatabaseCount('sale_items', 0);
        $this->assertDatabaseCount('stock_movements', 0);
        $this->assertSame(1, $product->refresh()->stock);
    }

    public function test_pembayaran_tunai_kurang_ditolak_dan_tidak_mengubah_stok(): void
    {
        $kasir = User::factory()->kasir()->create();
        $product = Product::factory()->create(['selling_price' => 50000, 'stock' => 5]);

        $this->expectException(ValidationException::class);

        try {
            app(CreateSaleAction::class)->execute([
                'items' => [['product_id' => $product->id, 'qty' => 1]],
                'payment_method' => 'cash',
                'paid_amount' => 20000,
            ], $kasir);
        } finally {
            $this->assertDatabaseCount('sales', 0);
            $this->assertSame(5, $product->refresh()->stock);
        }
    }

    public function test_nomor_invoice_unik_dan_berurutan_per_hari(): void
    {
        $kasir = User::factory()->kasir()->create();
        $product = Product::factory()->create(['selling_price' => 10000, 'stock' => 10]);

        $action = app(CreateSaleAction::class);

        $first = $action->execute([
            'items' => [['product_id' => $product->id, 'qty' => 1]],
            'apply_tax' => false,
            'payment_method' => 'cash',
            'paid_amount' => 10000,
        ], $kasir);

        $second = $action->execute([
            'items' => [['product_id' => $product->id, 'qty' => 1]],
            'apply_tax' => false,
            'payment_method' => 'qris',
            'paid_amount' => 10000,
        ], $kasir);

        $prefix = 'INV-'.now()->format('Ymd').'-';

        $this->assertSame($prefix.'0001', $first->invoice_no);
        $this->assertSame($prefix.'0002', $second->invoice_no);
        $this->assertNotSame($first->invoice_no, $second->invoice_no);
        $this->assertSame(2, Sale::query()->distinct()->count('invoice_no'));
        $this->assertDatabaseCount('invoice_sequences', 1);
    }

    public function test_produk_nonaktif_tidak_bisa_dijual(): void
    {
        $kasir = User::factory()->kasir()->create();
        $product = Product::factory()->inactive()->create(['stock' => 10]);

        $this->expectException(ValidationException::class);

        app(CreateSaleAction::class)->execute([
            'items' => [['product_id' => $product->id, 'qty' => 1]],
            'payment_method' => 'cash',
            'paid_amount' => 50000,
        ], $kasir);
    }
}
