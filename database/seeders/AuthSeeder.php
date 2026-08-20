<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Customer;
use Illuminate\Database\Seeder;

class AuthSeeder extends Seeder
{
    public function run(): void
    {
        if (! Account::query()->where('username', 'admin')->exists()) {
            Account::query()->create([
                'username' => 'admin',
                'password' => md5('admin123'),
                'email' => 'admin@tsscreen.local',
                'phone_number' => '',
                'user_type' => '1',
                'deleted' => 'n',
            ]);
        }

        if (! Customer::query()->where('email', 'customer@tsscreen.local')->exists()) {
            Customer::query()->create([
                'customer_name' => 'Demo Customer',
                'email' => 'customer@tsscreen.local',
                'phone_number' => '0900000000',
                'password' => '123456',
                'login_with' => 'email',
                'status' => 'y',
                'deleted' => 'n',
            ]);
        }
    }
}
