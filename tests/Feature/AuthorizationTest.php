<?php

namespace Tests\Feature;

use App\Actions\CreateSaleAction;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_kasir_tidak_bisa_mengakses_halaman_admin(): void
    {
        $kasir = User::factory()->kasir()->create();
        $admin = User::factory()->admin()->create();

        $this->actingAs($kasir)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($kasir)->get(route('admin.products'))->assertForbidden();
        $this->actingAs($kasir)->get(route('admin.users'))->assertForbidden();
        $this->actingAs($kasir)->get(route('admin.reports'))->assertForbidden();
        $this->actingAs($kasir)->get(route('admin.settings'))->assertForbidden();

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
        $this->actingAs($admin)->get(route('admin.products'))->assertOk();
    }

    public function test_kasir_bisa_akses_kasir_katalog_pelanggan_dan_riwayat_sendiri(): void
    {
        Setting::query()->create(['store_name' => 'Toko Uji', 'tax_percent' => 0]);

        $kasir = User::factory()->kasir()->create();

        $this->actingAs($kasir)->get(route('pos'))->assertOk();
        $this->actingAs($kasir)->get(route('catalog'))->assertOk();
        $this->actingAs($kasir)->get(route('customers.index'))->assertOk();
        $this->actingAs($kasir)->get(route('sales.index'))->assertOk();
    }

    public function test_kasir_hanya_melihat_transaksi_miliknya_sendiri(): void
    {
        Setting::query()->create(['store_name' => 'Toko Uji', 'tax_percent' => 0]);

        $kasirA = User::factory()->kasir()->create();
        $kasirB = User::factory()->kasir()->create();
        $product = Product::factory()->create(['selling_price' => 25000, 'stock' => 20]);

        $action = app(CreateSaleAction::class);

        $saleA = $action->execute([
            'items' => [['product_id' => $product->id, 'qty' => 1]],
            'payment_method' => 'cash',
            'paid_amount' => 25000,
        ], $kasirA);

        $saleB = $action->execute([
            'items' => [['product_id' => $product->id, 'qty' => 1]],
            'payment_method' => 'cash',
            'paid_amount' => 25000,
        ], $kasirB);

        $this->actingAs($kasirA)
            ->get(route('sales.index'))
            ->assertOk()
            ->assertSee($saleA->invoice_no)
            ->assertDontSee($saleB->invoice_no);

        $this->actingAs($kasirA)->get(route('sales.show', $saleA))->assertOk();
        $this->actingAs($kasirA)->get(route('sales.show', $saleB))->assertForbidden();

        $this->actingAs($kasirA)->get(route('sales.receipt', $saleA))->assertOk();
        $this->actingAs($kasirA)->get(route('sales.receipt', $saleB))->assertForbidden();
    }

    public function test_admin_bisa_export_csv_dan_kasir_tidak(): void
    {
        Setting::query()->create(['store_name' => 'Toko Uji', 'tax_percent' => 0]);

        $admin = User::factory()->admin()->create();
        $kasir = User::factory()->kasir()->create();

        $query = [
            'type' => 'sales',
            'from' => now()->startOfMonth()->toDateString(),
            'to' => now()->toDateString(),
        ];

        $this->actingAs($admin)
            ->get(route('admin.reports.export', $query))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $this->actingAs($kasir)
            ->get(route('admin.reports.export', $query))
            ->assertForbidden();
    }

    public function test_guest_diarahkan_ke_login_dan_home_redirect_sesuai_role(): void
    {
        Setting::query()->create(['store_name' => 'Toko Uji', 'tax_percent' => 0]);

        $this->get('/')->assertRedirect(route('login'));
        $this->get(route('pos'))->assertRedirect(route('login'));
        $this->get(route('login'))->assertOk()->assertSee('Masuk ke akun');

        $admin = User::factory()->admin()->create();
        $kasir = User::factory()->kasir()->create();

        $this->actingAs($admin)->get('/')->assertRedirect(route('admin.dashboard'));
        $this->actingAs($kasir)->get('/')->assertRedirect(route('pos'));
    }
}
