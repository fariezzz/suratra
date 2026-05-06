<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Resident>
 */
class ResidentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nik' => fake()->unique()->numerify('################'),
            'name' => fake()->name(),
            'gender' => fake()->randomElement(['L', 'P']),
            'birth_place' => fake()->city(),
            'birth_date' => fake()->date(),
            'ktp_address' => fake()->address(),
            'status_kawin' => fake()->randomElement(['Belum Kawin', 'Kawin']),
            'agama' => fake()->randomElement(['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha']),
            'address' => fake()->address(),
            'resident_status' => fake()->randomElement(['warga_asli', 'pendatang']),
            'rt' => fake()->numerify('###'),
            'rw' => fake()->numerify('###'),
            'phone' => '08'.fake()->numerify('##########'),
            'occupation' => fake()->jobTitle(),
        ];
    }

    public function rt(string $rt = '001'): static
    {
        return $this->state(fn (): array => ['rt' => $rt]);
    }

    public function rw(string $rw = '001'): static
    {
        return $this->state(fn (): array => ['rw' => $rw]);
    }

    public function wargaAsli(): static
    {
        return $this->state(fn (): array => ['resident_status' => 'warga_asli']);
    }

    public function pendatang(): static
    {
        return $this->state(fn (): array => ['resident_status' => 'pendatang']);
    }
}