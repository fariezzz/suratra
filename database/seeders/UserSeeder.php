<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'rt@demo.local'],
            [
                'name' => 'Pengurus RT',
                'password' => '12345',
                'role' => UserRole::RT,
                'resident_id' => null,
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'rw@demo.local'],
            [
                'name' => 'Pengurus RW',
                'password' => '12345',
                'role' => UserRole::RW,
                'resident_id' => null,
            ]
        );

        $residents = Resident::query()->orderBy('id')->get();

        foreach ($residents as $index => $resident) {
            User::query()->updateOrCreate(
                ['resident_id' => $resident->id],
                [
                    'name' => $resident->name,
                    'email' => sprintf('warga%02d@demo.local', $index + 1),
                    'password' => 'password123',
                    'role' => UserRole::WARGA,
                ]
            );
        }
    }
}
