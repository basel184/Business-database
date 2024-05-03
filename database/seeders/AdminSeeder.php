<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Abdelrahman Elalfee',
            'email' => 'elalfee@business.com',
            'password' => Hash::make('Admin!@#123'),
            'type' => 'admin'
        ]);
        Admin::create([
            'display_name' => 'Elalfee',
            'phone' => '010940706235',
            'user_id' => 1,
            'role_id' => 1
        ]);
    }
}
