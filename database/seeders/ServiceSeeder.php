<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            ['Haircut', 30, 0, 35000],
            ['Hair Colour', 90, 15, 180000],
            ['Blow Dry', 30, 0, 45000],
            ['Hair Treatment', 60, 10, 120000],
            ['Manicure', 45, 5, 40000],
            ['Pedicure', 60, 5, 55000],
        ];

        foreach ($services as [$name, $duration, $buffer, $price]) {
            Service::create([
                'name' => $name,
                'duration_minutes' => $duration,
                'buffer_minutes' => $buffer,
                'price_amount' => $price,
                'is_active' => true,
            ]);
        }
    }
}
