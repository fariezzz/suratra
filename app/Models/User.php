<?php

namespace App\Models;

use App\Enums\UserRole;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'managed_rt',
        'resident_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class);
    }

    public function archivedLetters(): HasMany
    {
        return $this->hasMany(LetterArchive::class, 'archived_by');
    }

    public function hasRole(UserRole $role): bool
    {
        return $this->role === $role;
    }

    public function isWarga(): bool
    {
        return $this->hasRole(UserRole::WARGA);
    }

    public function isRt(): bool
    {
        return $this->hasRole(UserRole::RT);
    }

    public function isRw(): bool
    {
        return $this->hasRole(UserRole::RW);
    }

    public function canAccessRt(string $rt): bool
    {
        if ($this->isRw()) {
            return true;
        }

        return $this->isRt() && $this->managed_rt === $rt;
    }
}
