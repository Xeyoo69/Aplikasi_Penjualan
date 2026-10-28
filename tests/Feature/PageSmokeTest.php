<?php

namespace Tests\Feature;

use App\Actions\CreateSaleAction;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Smoke test: memastikan semua halaman utama benar-benar bisa dirender
 * (bukan hanya lolos otorisasi) tanpa error 500.
 */
class PageSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::query()->create([
            'store_name' => 'Toko Uji',
            'address' => 'Jl. Uji No. 1',
            'phone' => '021-000000',
            'tax_percent' => 0,
            'receipt_footer' => 'Terima kasih',
        ]);
    }

    public function test_semua_halaman_admin_dan_transaksi_dapat_dibuka(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create([
            'category_id' => Category::factory()->create()->id,
            'selling_price' => 15000,
            'stock' => 10,
        ]);
        Customer::factory()->create();

        $sale = app(CreateSaleAction::class)->execute([
            'items' => [['product_id' => $product->id, 'qty' => 1]],
            'payment_method' => 'cash',
            'paid_amount' => 20000,
        ], $admin);

        $pages = [
            'admin.dashboard',
            'admin.products',
            'admin.categories',
            'admin.stock',
            'admin.users',
            'admin.reports',
            'admin.settings',
            'pos',
            'catalog',
            'customers.index',
            'sales.index',
        ];

        foreach ($pages as $route) {
            $this->actingAs($admin)->get(route($route))->assertOk();
        }

        $this->actingAs($admin)->get(route('sales.show', $sale))->assertOk();
        $this->actingAs($admin)->get(route('sales.receipt', $sale))->assertOk();
        $this->actingAs($admin)->get(route('sales.receipt', ['sale' => $sale, 'width' => 80]))->assertOk();
    }

    public function test_semua_halaman_kasir_dapat_dibuka(): void
    {
        $kasir = User::factory()->kasir()->create();

        foreach (['pos', 'catalog', 'customers.index', 'sales.index'] as $route) {
            $this->actingAs($kasir)->get(route($route))->assertOk();
        }
    }
}
