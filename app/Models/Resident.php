<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Resident extends Model
{
    use HasFactory;

    protected $fillable = [
        'nik',
        'name',
        'gender',
        'birth_place',
        'birth_date',
        'ktp_address',
        'address',
        'resident_status',
        'rt',
        'rw',
        'phone',
        'occupation',
    ];

    protected $casts = [
        'birth_date' => 'date',
    ];

    public function letterRequests(): HasMany
    {
        return $this->hasMany(LetterRequest::class);
    }

    public function letterArchives(): HasMany
    {
        return $this->hasMany(LetterArchive::class);
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }
}
