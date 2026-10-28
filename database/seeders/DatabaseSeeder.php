<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@txeyoo.test'],
            [
                'name' => 'Admin TXeyoo',
                'password' => Hash::make('password'),
                'role' => UserRole::Admin,
                'is_active' => true,
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'kasir@txeyoo.test'],
            [
                'name' => 'Kasir TXeyoo',
                'password' => Hash::make('password'),
                'role' => UserRole::Kasir,
                'is_active' => true,
            ]
        );

        $categories = [
            'Makanan' => 'Makanan instan, roti, dan camilan.',
            'Minuman' => 'Air mineral, teh, kopi, dan susu.',
            'Sembako' => 'Kebutuhan pokok sehari-hari.',
            'Perawatan Diri' => 'Sabun, shampo, dan pasta gigi.',
            'Perlengkapan Rumah' => 'Kebutuhan kebersihan dan rumah tangga.',
        ];

        foreach ($categories as $name => $description) {
            Category::query()->updateOrCreate(
                ['name' => $name],
                ['description' => $description]
            );
        }

        $products = [
            ['sku' => 'SMB-001', 'barcode' => '8991001000011', 'name' => 'Beras Pandan Wangi 5 kg', 'category' => 'Sembako', 'purchase' => 62000, 'selling' => 72000, 'stock' => 40, 'min' => 10],
            ['sku' => 'SMB-002', 'barcode' => '8991001000028', 'name' => 'Minyak Goreng Sania 2 L', 'category' => 'Sembako', 'purchase' => 34000, 'selling' => 39500, 'stock' => 35, 'min' => 10],
            ['sku' => 'SMB-003', 'barcode' => '8991001000035', 'name' => 'Gula Pasir Gulaku 1 kg', 'category' => 'Sembako', 'purchase' => 15500, 'selling' => 18500, 'stock' => 30, 'min' => 8],
            ['sku' => 'SMB-004', 'barcode' => '8991001000042', 'name' => 'Telur Ayam Negeri 1 kg', 'category' => 'Sembako', 'purchase' => 26000, 'selling' => 30000, 'stock' => 8, 'min' => 10],
            ['sku' => 'SMB-005', 'barcode' => '8991001000059', 'name' => 'Mi Instan Indomie Goreng', 'category' => 'Makanan', 'purchase' => 2800, 'selling' => 3500, 'stock' => 80, 'min' => 20],
            ['sku' => 'MNM-001', 'barcode' => '8991001000066', 'name' => 'Air Mineral Aqua 600 ml', 'category' => 'Minuman', 'purchase' => 3000, 'selling' => 4000, 'stock' => 60, 'min' => 15],
            ['sku' => 'MNM-002', 'barcode' => '8991001000073', 'name' => 'Teh Botol Sosro 350 ml', 'category' => 'Minuman', 'purchase' => 4000, 'selling' => 5000, 'stock' => 48, 'min' => 12],
            ['sku' => 'MNM-003', 'barcode' => '8991001000080', 'name' => 'Kopi Kapal Api Special 165 g', 'category' => 'Minuman', 'purchase' => 12000, 'selling' => 15000, 'stock' => 4, 'min' => 5],
            ['sku' => 'MNM-004', 'barcode' => '8991001000097', 'name' => 'Susu UHT Ultra Milk 250 ml', 'category' => 'Minuman', 'purchase' => 5500, 'selling' => 7000, 'stock' => 6, 'min' => 8],
            ['sku' => 'MKN-001', 'barcode' => '8991001000103', 'name' => 'Roti Tawar Sari Roti', 'category' => 'Makanan', 'purchase' => 14000, 'selling' => 17000, 'stock' => 12, 'min' => 6],
            ['sku' => 'PRW-001', 'barcode' => '8991001000110', 'name' => 'Sabun Mandi Lifebuoy 75 g', 'category' => 'Perawatan Diri', 'purchase' => 4000, 'selling' => 5500, 'stock' => 40, 'min' => 10],
            ['sku' => 'PRW-002', 'barcode' => '8991001000127', 'name' => 'Shampo Pantene 170 ml', 'category' => 'Perawatan Diri', 'purchase' => 22000, 'selling' => 27000, 'stock' => 18, 'min' => 6],
            ['sku' => 'PRW-003', 'barcode' => '8991001000134', 'name' => 'Pasta Gigi Pepsodent 190 g', 'category' => 'Perawatan Diri', 'purchase' => 12000, 'selling' => 15500, 'stock' => 22, 'min' => 8],
            ['sku' => 'RMT-001', 'barcode' => '8991001000141', 'name' => 'Detergen Rinso 770 g', 'category' => 'Perlengkapan Rumah', 'purchase' => 18000, 'selling' => 22000, 'stock' => 20, 'min' => 8],
            ['sku' => 'RMT-002', 'barcode' => '8991001000158', 'name' => 'Tisu Paseo 250 sheets', 'category' => 'Perlengkapan Rumah', 'purchase' => 14000, 'selling' => 17500, 'stock' => 16, 'min' => 6],
            ['sku' => 'RMT-003', 'barcode' => '8991001000165', 'name' => 'Gas LPG 3 kg (isi ulang)', 'category' => 'Perlengkapan Rumah', 'purchase' => 20000, 'selling' => 25000, 'stock' => 25, 'min' => 10],
            ['sku' => 'MKN-002', 'barcode' => '8991001000172', 'name' => 'Keripik Chitato 68 g', 'category' => 'Makanan', 'purchase' => 9500, 'selling' => 12000, 'stock' => 30, 'min' => 10],
            ['sku' => 'MKN-003', 'barcode' => '8991001000189', 'name' => 'Biskuit Oreo 133 g', 'category' => 'Makanan', 'purchase' => 8000, 'selling' => 10000, 'stock' => 28, 'min' => 10],
            ['sku' => 'MKN-004', 'barcode' => '8991001000196', 'name' => 'Cokelat SilverQueen 58 g', 'category' => 'Makanan', 'purchase' => 12000, 'selling' => 15000, 'stock' => 24, 'min' => 8],
            ['sku' => 'MKN-005', 'barcode' => '8991001000202', 'name' => 'Permen Kopiko', 'category' => 'Makanan', 'purchase' => 4500, 'selling' => 6000, 'stock' => 50, 'min' => 15],
            ['sku' => 'SMB-006', 'barcode' => '8991001000219', 'name' => 'Sarden ABC 155 g', 'category' => 'Sembako', 'purchase' => 8500, 'selling' => 11000, 'stock' => 26, 'min' => 10],
            ['sku' => 'SMB-007', 'barcode' => '8991001000226', 'name' => 'Kecap Manis Bango 275 ml', 'category' => 'Sembako', 'purchase' => 11000, 'selling' => 14000, 'stock' => 18, 'min' => 6],
            ['sku' => 'SMB-008', 'barcode' => '8991001000233', 'name' => 'Saus Sambal ABC 135 ml', 'category' => 'Sembako', 'purchase' => 7000, 'selling' => 9000, 'stock' => 3, 'min' => 5],
            ['sku' => 'MNM-005', 'barcode' => '8991001000240', 'name' => 'Kopi Torabika Duo sachet', 'category' => 'Minuman', 'purchase' => 1500, 'selling' => 2000, 'stock' => 72, 'min' => 20],
            ['sku' => 'MNM-006', 'barcode' => '8991001000257', 'name' => 'Air Galon Isi Ulang', 'category' => 'Minuman', 'purchase' => 6000, 'selling' => 8000, 'stock' => 20, 'min' => 5],
        ];

        foreach ($products as $product) {
            $categoryId = Category::query()->where('name', $product['category'])->value('id');

            Product::query()->updateOrCreate(
                ['sku' => $product['sku']],
                [
                    'category_id' => $categoryId,
                    'barcode' => $product['barcode'],
                    'name' => $product['name'],
                    'purchase_price' => $product['purchase'],
                    'selling_price' => $product['selling'],
                    'stock' => $product['stock'],
                    'min_stock' => $product['min'],
                    'is_active' => true,
                ]
            );
        }

        $customers = [
            ['name' => 'Budi Santoso', 'phone' => '081234567801', 'address' => 'Jl. Merdeka No. 12, Jakarta'],
            ['name' => 'Siti Aminah', 'phone' => '081234567802', 'address' => 'Jl. Melati No. 5, Bandung'],
            ['name' => 'Andi Wijaya', 'phone' => '081234567803', 'address' => 'Jl. Sudirman No. 88, Surabaya'],
            ['name' => 'Rina Lestari', 'phone' => '081234567804', 'address' => 'Jl. Kenanga No. 21, Yogyakarta'],
            ['name' => 'Dewi Anggraini', 'phone' => '081234567805', 'address' => 'Jl. Ahmad Yani No. 34, Semarang'],
        ];

        foreach ($customers as $customer) {
            Customer::query()->updateOrCreate(
                ['phone' => $customer['phone']],
                $customer
            );
        }

        $setting = Setting::query()->firstOrNew(['id' => 1]);
        $setting->fill([
            'store_name' => 'TXeyoo',
            'address' => 'Jl. Raya Pasar Baru No. 10, Jakarta',
            'phone' => '021-5550100',
            'tax_percent' => 11,
            'receipt_footer' => 'Terima kasih telah berbelanja. Barang yang sudah dibeli tidak dapat ditukar.',
        ])->save();
    }
}
