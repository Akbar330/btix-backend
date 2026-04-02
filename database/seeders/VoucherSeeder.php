<?php

namespace Database\Seeders;

use App\Models\Voucher;
use Illuminate\Database\Seeder;

class VoucherSeeder extends Seeder
{
    public function run(): void
    {
        $vouchers = [
            [
                'code' => 'HEMAT10',
                'description' => 'Diskon 10% untuk semua event dengan minimum pembelian Rp200.000.',
                'discount_type' => 'percent',
                'value' => 10,
                'min_purchase' => 200000,
                'max_discount' => 75000,
                'starts_at' => now()->subDays(7),
                'ends_at' => now()->addMonths(3),
                'max_uses' => 500,
                'used_count' => 0,
                'is_active' => true,
            ],
            [
                'code' => 'WELCOME50K',
                'description' => 'Potongan Rp50.000 untuk user baru dengan minimum transaksi Rp300.000.',
                'discount_type' => 'flat',
                'value' => 50000,
                'min_purchase' => 300000,
                'max_discount' => null,
                'starts_at' => now()->subDays(3),
                'ends_at' => now()->addMonths(2),
                'max_uses' => 250,
                'used_count' => 0,
                'is_active' => true,
            ],
            [
                'code' => 'WEEKEND25',
                'description' => 'Diskon 25% untuk promo akhir pekan, maksimal potongan Rp100.000.',
                'discount_type' => 'percent',
                'value' => 25,
                'min_purchase' => 400000,
                'max_discount' => 100000,
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addWeeks(2),
                'max_uses' => 100,
                'used_count' => 0,
                'is_active' => true,
            ],
            [
                'code' => 'FLASH30K',
                'description' => 'Potongan cepat Rp30.000 untuk transaksi minimum Rp150.000.',
                'discount_type' => 'flat',
                'value' => 30000,
                'min_purchase' => 150000,
                'max_discount' => null,
                'starts_at' => now()->subHours(6),
                'ends_at' => now()->addDays(10),
                'max_uses' => 80,
                'used_count' => 0,
                'is_active' => true,
            ],
            [
                'code' => 'EXPIREDDEAL',
                'description' => 'Voucher contoh yang sudah lewat masa berlakunya untuk testing validasi.',
                'discount_type' => 'percent',
                'value' => 15,
                'min_purchase' => 100000,
                'max_discount' => 50000,
                'starts_at' => now()->subMonths(2),
                'ends_at' => now()->subMonth(),
                'max_uses' => 50,
                'used_count' => 12,
                'is_active' => true,
            ],
            [
                'code' => 'PAUSEDPROMO',
                'description' => 'Voucher nonaktif untuk kebutuhan testing admin toggle dan validasi frontend.',
                'discount_type' => 'flat',
                'value' => 20000,
                'min_purchase' => 100000,
                'max_discount' => null,
                'starts_at' => now()->subDays(5),
                'ends_at' => now()->addMonth(),
                'max_uses' => 100,
                'used_count' => 0,
                'is_active' => false,
            ],
        ];

        foreach ($vouchers as $voucher) {
            Voucher::updateOrCreate(
                ['code' => $voucher['code']],
                $voucher
            );
        }
    }
}
