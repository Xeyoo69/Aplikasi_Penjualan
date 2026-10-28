<?php

namespace Tests\Feature\Pos;

use App\Livewire\Pos\PosIndex;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PosIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_dari_halaman_kasir_membuat_transaksi_dan_mengurangi_stok(): void
    {
        Setting::query()->create(['store_name' => 'Toko Uji', 'tax_percent' => 0]);

        $kasir = User::factory()->kasir()->create();
        $product = Product::factory()->create(['selling_price' => 25000, 'stock' => 5]);

        Livewire::actingAs($kasir)
            ->test(PosIndex::class)
            ->call('addProduct', $product->id)
            ->assertSet('cart.'.$product->id.'.qty', 1)
            ->call('increment', $product->id)
            ->assertSet('cart.'.$product->id.'.qty', 2)
            ->set('paidAmount', 100000)
            ->call('checkout')
            ->assertHasNoErrors()
            ->assertSet('showReceiptModal', true)
            ->assertSet('cart', []);

        $this->assertDatabaseHas('sales', [
            'user_id' => $kasir->id,
            'subtotal' => 50000,
            'total' => 50000,
            'paid_amount' => 100000,
            'change_amount' => 50000,
            'payment_method' => 'cash',
            'status' => 'completed',
        ]);

        $this->assertSame(3, $product->refresh()->stock);
        $this->assertDatabaseCount('sale_items', 1);
        $this->assertDatabaseCount('stock_movements', 1);
    }

    public function test_komponen_menolak_menambah_melebihi_stok(): void
    {
        Setting::query()->create(['store_name' => 'Toko Uji', 'tax_percent' => 0]);

        $kasir = User::factory()->kasir()->create();
        $product = Product::factory()->create(['selling_price' => 25000, 'stock' => 1]);

        Livewire::actingAs($kasir)
            ->test(PosIndex::class)
            ->call('addProduct', $product->id)
            ->call('addProduct', $product->id)
            ->assertSet('cart.'.$product->id.'.qty', 1)
            ->assertDispatched('toast');

        $this->assertDatabaseCount('sales', 0);
        $this->assertSame(1, $product->refresh()->stock);
    }

    public function test_scan_barcode_menambahkan_produk_ke_keranjang(): void
    {
        Setting::query()->create(['store_name' => 'Toko Uji', 'tax_percent' => 0]);

        $kasir = User::factory()->kasir()->create();
        $product = Product::factory()->create([
            'barcode' => '8999999999999',
            'selling_price' => 15000,
            'stock' => 5,
        ]);

        Livewire::actingAs($kasir)
            ->test(PosIndex::class)
            ->call('scanBarcode', '8999999999999')
            ->assertSet('cart.'.$product->id.'.qty', 1);
    }
}
