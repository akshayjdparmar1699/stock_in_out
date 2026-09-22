<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Item;
use App\Models\ItemStock;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $mainBranch = Branch::create([
            'name' => 'Main Branch',
            'code' => 'BR1',
            'address' => 'Shop No. 1, Wholesale Market',
            'phone' => '9000000001',
        ]);

        $secondBranch = Branch::create([
            'name' => 'Branch 2',
            'code' => 'BR2',
            'address' => 'Shop No. 2, City Market',
            'phone' => '9000000002',
        ]);

        User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_ADMIN,
            'branch_id' => null,
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Main Branch Staff',
            'email' => 'staff1@example.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_STAFF,
            'branch_id' => $mainBranch->id,
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Branch 2 Staff',
            'email' => 'staff2@example.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_STAFF,
            'branch_id' => $secondBranch->id,
            'email_verified_at' => now(),
        ]);

        $items = [
            ['name' => 'Basmati Rice 25kg Bag', 'sku' => 'RICE-25', 'unit' => 'bag', 'purchase_price' => 1400, 'selling_price' => 1550, 'low_stock_threshold' => 10, 'qty' => 60],
            ['name' => 'Refined Sunflower Oil 15L', 'sku' => 'OIL-15L', 'unit' => 'tin', 'purchase_price' => 1800, 'selling_price' => 1950, 'low_stock_threshold' => 8, 'qty' => 40],
            ['name' => 'Sugar 50kg Bag', 'sku' => 'SUGAR-50', 'unit' => 'bag', 'purchase_price' => 2100, 'selling_price' => 2250, 'low_stock_threshold' => 5, 'qty' => 25],
            ['name' => 'Wheat Flour 25kg Bag', 'sku' => 'ATTA-25', 'unit' => 'bag', 'purchase_price' => 750, 'selling_price' => 830, 'low_stock_threshold' => 10, 'qty' => 50],
            ['name' => 'Toor Dal 30kg Bag', 'sku' => 'DAL-30', 'unit' => 'bag', 'purchase_price' => 3200, 'selling_price' => 3450, 'low_stock_threshold' => 5, 'qty' => 15],
        ];

        foreach ($items as $data) {
            $item = Item::create([
                'name' => $data['name'],
                'sku' => $data['sku'],
                'unit' => $data['unit'],
                'purchase_price' => $data['purchase_price'],
                'selling_price' => $data['selling_price'],
                'low_stock_threshold' => $data['low_stock_threshold'],
            ]);

            ItemStock::create(['branch_id' => $mainBranch->id, 'item_id' => $item->id, 'quantity' => $data['qty']]);
            ItemStock::create(['branch_id' => $secondBranch->id, 'item_id' => $item->id, 'quantity' => intdiv($data['qty'], 2)]);
        }

        $sharma = Customer::create(['name' => 'Sharma General Store', 'phone' => '9811111111', 'address' => 'MG Road']);
        $sharma->branches()->attach($mainBranch->id);

        $patel = Customer::create(['name' => 'Patel Kirana', 'phone' => '9822222222', 'address' => 'Station Road']);
        $patel->branches()->attach($secondBranch->id);

        $common = Customer::create(['name' => 'Gupta Wholesale Distributors', 'phone' => '9833333333', 'address' => 'Bypass Road']);
        $common->branches()->attach([$mainBranch->id, $secondBranch->id]);
    }
}
