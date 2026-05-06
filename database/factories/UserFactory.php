<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\Resident;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'role' => UserRole::WARGA->value,
            'managed_rt' => null,
            'resident_id' => null,
        ];
    }

    public function warga(): static
    {
        return $this->state(fn (): array => [
            'role' => UserRole::WARGA->value,
            'managed_rt' => null,
        ]);
    }

    public function rt(string $managedRt = '001'): static
    {
        return $this->state(fn (): array => [
            'role' => UserRole::RT->value,
            'managed_rt' => $managedRt,
        ]);
    }

    public function rw(): static
    {
        return $this->state(fn (): array => [
            'role' => UserRole::RW->value,
            'managed_rt' => null,
        ]);
    }

    public function linkedToResident(?Resident $resident = null): static
    {
        return $this->state(fn () => [
            'resident_id' => $resident?->id,
            'name' => $resident?->name ?? fake()->name(),
        ]);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
