<?php

namespace App\Models;

use App\Enums\LetterRequestStatus;
use App\Enums\LetterType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LetterArchive extends Model
{
    use HasFactory;

    protected $fillable = [
        'letter_request_id',
        'resident_id',
        'archived_by',
        'archive_number',
        'reference_number',
        'letter_number',
        'letter_type',
        'request_status',
        'resident_nik',
        'resident_name',
        'purpose',
        'documents',
        'generated_content',
        'issued_at',
        'archived_at',
    ];

    protected $casts = [
        'documents' => 'array',
        'issued_at' => 'datetime',
        'archived_at' => 'datetime',
    ];

    public function letterRequest(): BelongsTo
    {
        return $this->belongsTo(LetterRequest::class);
    }

    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class);
    }

    public function archivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'archived_by');
    }

    public function getLetterTypeLabelAttribute(): string
    {
        return LetterType::tryFrom($this->letter_type)?->label() ?? $this->letter_type;
    }

    public function getStatusLabelAttribute(): string
    {
        return LetterRequestStatus::tryFrom($this->request_status)?->label() ?? $this->request_status;
    }
}
