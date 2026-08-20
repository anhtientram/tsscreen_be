<?php

namespace Database\Seeders;

use App\Models\Packet;
use Illuminate\Database\Seeder;

class PacketSeeder extends Seeder
{
    public function run(): void
    {
        $packets = [
            [
                'name_packet' => 'Gói dùng thử',
                'price' => '0',
                'price_6_month' => '0',
                'price_12_month' => '0',
                'day_qty' => '7',
                'month_qty' => '0',
                'year_qty' => '0',
                'is_trial' => '1',
                'is_business' => '0',
                'detail' => 'Dùng thử 7 ngày, 1 TV',
                'description' => 'Gói dùng thử',
                'picture' => '',
                'limit_qty' => '1',
                'limit_capacity' => (string) (100 * 1024 * 1024),
            ],
            [
                'name_packet' => 'Gói cơ bản',
                'price' => '99000',
                'price_6_month' => '499000',
                'price_12_month' => '899000',
                'day_qty' => '0',
                'month_qty' => '1',
                'year_qty' => '0',
                'is_trial' => '0',
                'is_business' => '0',
                'detail' => '2 TV, 1GB media',
                'description' => 'Gói cơ bản',
                'picture' => '',
                'limit_qty' => '2',
                'limit_capacity' => (string) (1024 * 1024 * 1024),
            ],
            [
                'name_packet' => 'Gói doanh nghiệp',
                'price' => '299000',
                'price_6_month' => '1599000',
                'price_12_month' => '2999000',
                'day_qty' => '0',
                'month_qty' => '1',
                'year_qty' => '0',
                'is_trial' => '0',
                'is_business' => '1',
                'detail' => '10 TV, 10GB media',
                'description' => 'Gói doanh nghiệp',
                'picture' => '',
                'limit_qty' => '10',
                'limit_capacity' => (string) (10 * 1024 * 1024 * 1024),
            ],
        ];

        foreach ($packets as $row) {
            if (Packet::query()->where('name_packet', $row['name_packet'])->exists()) {
                continue;
            }

            Packet::query()->create($row + ['deleted' => 'n']);
        }
    }
}
