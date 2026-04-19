<?php

namespace Database\Seeders;

use App\Models\MembershipPlan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MembershipPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        MembershipPlan::create([
            'name' => 'basic',
            'display_name' => 'Basic',
            'price' => 0,
            'discount_percentage' => 0,
            'description' => 'Paket gratis untuk semua pengguna',
            'features' => [
                'Browse & Search Event',
                'Lihat Detail Event',
                'Checkout Tiket',
                'Riwayat Pembelian',
                'E-Ticket Digital',
            ],
            'is_active' => true,
            'order' => 1,
        ]);

        MembershipPlan::create([
            'name' => 'regular',
            'display_name' => 'Regular',
            'price' => 50000,
            'discount_percentage' => 15,
            'description' => 'Paket regular dengan diskon 15% untuk semua tiket',
            'features' => [
                'Browse & Search Event',
                'Lihat Detail Event',
                'Checkout Tiket',
                'Riwayat Pembelian',
                'E-Ticket Digital',
                'Diskon 15% Semua Tiket',
                'Priority Support',
            ],
            'is_active' => true,
            'order' => 2,
        ]);

        MembershipPlan::create([
            'name' => 'premium',
            'display_name' => 'Premium',
            'price' => 100000,
            'discount_percentage' => 30,
            'description' => 'Paket premium dengan diskon 30% dan akses eksklusif',
            'features' => [
                'Browse & Search Event',
                'Lihat Detail Event',
                'Checkout Tiket',
                'Riwayat Pembelian',
                'E-Ticket Digital',
                'Diskon 30% Semua Tiket',
                'Priority Support',
                'Early Bird Access',
                'Exclusive Events',
                'VIP Lounge Access (Venue Selected)',
            ],
            'is_active' => true,
            'order' => 3,
        ]);
    }
}
