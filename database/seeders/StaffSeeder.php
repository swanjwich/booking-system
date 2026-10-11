<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Service;
use App\Models\StaffProfile;
use App\Models\User;
use Illuminate\Database\Seeder;

class StaffSeeder extends Seeder
{
    public function run(): void
    {
        $services = Service::all();

        $people = [
            ['Staff Member', 'staff@booking.test', 'Senior Stylist'],
            ['Maria Santos', 'maria@booking.test', 'Colour Specialist'],
            ['Jun Reyes', 'jun@booking.test', 'Barber'],
        ];

        foreach ($people as [$name, $email, $title]) {
            $user = User::factory()->create([
                'name' => $name,
                'email' => $email,
                'role' => UserRole::Staff,
            ]);

            $profile = $user->staffProfile()->create([
                'title' => $title,
                'is_bookable' => true,
            ]);

            $profile->services()->attach($services->random(4)->pluck('id'));

            foreach ([1, 2, 3, 4, 5] as $weekday) {
                $profile->workingHours()->create([
                    'day_of_week' => $weekday,
                    'start_time' => '09:00',
                    'end_time' => '17:00',
                ]);
            }

            $profile->workingHours()->create([
                'day_of_week' => 6,
                'start_time' => '09:00',
                'end_time' => '13:00',
            ]);
        }

        StaffProfile::first()->timeOffs()->create([
            'starts_at' => now()->addWeek()->setTime(0, 0),
            'ends_at' => now()->addWeek()->setTime(23, 59),
            'reason' => 'Annual leave',
        ]);
    }
}
