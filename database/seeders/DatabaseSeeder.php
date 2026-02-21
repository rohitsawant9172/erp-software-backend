<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $shop = \App\Models\Shop::create([
            'name' => 'Bharat Fertilizers',
            'owner_name' => 'Rajesh Kumar',
            'email' => 'rajesh@bharat.com',
            'phone' => '9876543210',
            'gstin' => '27AAAAA0000A1Z5',
            'trial_ends_at' => now()->addDays(30),
        ]);

        $user = \App\Models\User::create([
            'name' => 'Rajesh Kumar',
            'email' => 'admin@fertibill.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'tenant_id' => $shop->id,
            'role' => 'admin',
        ]);

        \App\Models\Farmer::create([
            'tenant_id' => $shop->id,
            'name' => 'Suresh Patil',
            'phone' => '9988776655',
            'land_size' => 15.5,
            'crops_grown' => 'Wheat, Sugarcane',
            'credit_limit' => 100000,
            'current_usage' => 25000,
        ]);

        \App\Models\Product::create([
            'tenant_id' => $shop->id,
            'name' => 'DAP Fertilizer',
            'sku' => 'DAP-50',
            'price' => 1350.00,
            'gst_rate' => 5.00,
            'stock_level' => 100,
        ]);
        
        \App\Models\Product::create([
            'tenant_id' => $shop->id,
            'name' => 'Urea 46%',
            'sku' => 'UREA-45',
            'price' => 266.50,
            'gst_rate' => 5.00,
            'stock_level' => 500,
        ]);
    }
}
