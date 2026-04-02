<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Check if admin already exists to prevent duplication
        if (!User::where('email', 'admin@tiket.com')->exists()) {
            User::create([
                'name' => 'Super Admin',
                'email' => 'admin@tiket.com',
                'role' => 'admin',
                'password' => Hash::make('password123'),
            ]);
        }

        $this->call([
            TicketSeeder::class,
            VoucherSeeder::class,
        ]);
    }
}
