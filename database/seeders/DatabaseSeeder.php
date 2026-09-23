<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with the real production baseline:
     * one branch, one admin. No demo data.
     */
    public function run(): void
    {
        Branch::create([
            'name' => 'Om Sai Aallubhandar (Sugar Factory)',
        ]);

        User::create([
            'name' => 'Jaydip Barad',
            'email' => 'jaydip.barad@gmail.com',
            'password' => bcrypt('Jaydip@1234'),
            'role' => User::ROLE_ADMIN,
            'branch_id' => null,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
    }
}
