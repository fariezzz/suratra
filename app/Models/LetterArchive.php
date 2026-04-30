<?php

namespace App\Models;

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
        'generated_pdf_path',
        'generated_docx_path',
        'issued_at',
        'archived_at',
    ];

    protected $casts = [
        'documents' => 'array',
        'issued_at' => 'datetime',
        'archived_at' => 'datetime',
    ];

    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class);
    }

    public function getLetterTypeLabelAttribute(): string
    {
        return LetterType::tryFrom($this->letter_type)?->label() ?? $this->letter_type;
    }
}
