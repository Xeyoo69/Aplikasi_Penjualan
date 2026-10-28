<?php

use App\Http\Controllers\ReportExportController;
use App\Http\Controllers\SaleReceiptController;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Auth\Login;
use App\Livewire\Catalog\CatalogIndex;
use App\Livewire\Categories\CategoryIndex;
use App\Livewire\Customers\CustomerIndex;
use App\Livewire\Pos\PosIndex;
use App\Livewire\Products\ProductIndex;
use App\Livewire\Reports\ReportIndex;
use App\Livewire\Sales\SaleIndex;
use App\Livewire\Sales\SaleShow;
use App\Livewire\Settings\SettingForm;
use App\Livewire\Stock\StockIndex;
use App\Livewire\Users\UserIndex;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (! Auth::check()) {
        return redirect()->route('login');
    }

    return redirect()->route(auth()->user()->isAdmin() ? 'admin.dashboard' : 'pos');
})->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
});

Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect()->route('login');
})->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/pos', PosIndex::class)->name('pos');
    Route::get('/katalog', CatalogIndex::class)->name('catalog');

    Route::get('/pelanggan', CustomerIndex::class)->name('customers.index');

    Route::get('/transaksi', SaleIndex::class)->name('sales.index');
    Route::get('/transaksi/{sale}', SaleShow::class)->name('sales.show');
    Route::get('/transaksi/{sale}/struk', SaleReceiptController::class)->name('sales.receipt');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', Dashboard::class)->name('dashboard');
    Route::get('/produk', ProductIndex::class)->name('products');
    Route::get('/kategori', CategoryIndex::class)->name('categories');
    Route::get('/stok', StockIndex::class)->name('stock');
    Route::get('/pengguna', UserIndex::class)->name('users');
    Route::get('/laporan', ReportIndex::class)->name('reports');
    Route::get('/laporan/export', ReportExportController::class)->name('reports.export');
    Route::get('/pengaturan', SettingForm::class)->name('settings');
});
