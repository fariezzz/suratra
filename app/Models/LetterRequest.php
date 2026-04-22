<?php

namespace App\Models;

use App\Enums\LetterRequestStatus;
use App\Enums\LetterType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LetterRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'resident_id',
        'reference_number',
        'letter_type',
        'purpose',
        'status',
        'rt_notes',
        'rw_notes',
        'documents',
        'letter_number',
        'generated_content',
        'issued_at',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'documents' => 'array',
    ];

    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class);
    }

    public function getLetterTypeLabelAttribute(): string
    {
        return LetterType::tryFrom($this->letter_type)?->label() ?? $this->letter_type;
    }

    public function getStatusLabelAttribute(): string
    {
        return LetterRequestStatus::tryFrom($this->status)?->label() ?? $this->status;
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return LetterRequestStatus::tryFrom($this->status)?->badgeClass() ?? 'text-bg-secondary';
    }
}
