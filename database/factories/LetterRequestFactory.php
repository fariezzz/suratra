<?php

namespace Database\Factories;

use App\Enums\LetterRequestStatus;
use App\Enums\LetterType;
use App\Models\Resident;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LetterRequest>
 */
class LetterRequestFactory extends Factory
{
    public function definition(): array
    {
        return [
            'resident_id' => Resident::factory(),
            'reference_number' => 'REQ-'.now()->format('Ymd').'-'.fake()->unique()->numerify('####'),
            'letter_type' => LetterType::GENERAL->value,
            'purpose' => fake()->sentence(),
            'status' => LetterRequestStatus::PENDING_RT->value,
            'rt_notes' => null,
            'rw_notes' => null,
            'documents' => [
                [
                    'key' => 'ktp',
                    'label' => 'Scan KTP',
                    'path' => 'letter-documents/ktp.pdf',
                    'original_name' => 'ktp.pdf',
                    'mime_type' => 'application/pdf',
                    'size' => 1024,
                ],
            ],
            'letter_number' => null,
            'generated_content' => null,
            'generated_pdf_path' => null,
            'generated_docx_path' => null,
            'issued_at' => null,
        ];
    }

    public function pendingRw(): static
    {
        return $this->state(fn (): array => ['status' => LetterRequestStatus::PENDING_RW->value]);
    }

    public function completed(): static
    {
        return $this->state(fn (): array => [
            'status' => LetterRequestStatus::COMPLETED->value,
            'generated_content' => 'Surat selesai',
            'issued_at' => now(),
        ]);
    }

    public function rejectedRt(): static
    {
        return $this->state(fn (): array => ['status' => LetterRequestStatus::REJECTED_RT->value]);
    }

    public function rejectedRw(): static
    {
        return $this->state(fn (): array => ['status' => LetterRequestStatus::REJECTED_RW->value]);
    }

    public function type(LetterType $letterType): static
    {
        return $this->state(fn (): array => [
            'letter_type' => $letterType->value,
            'documents' => collect($letterType->requirements())
                ->map(fn (string $label, string $key): array => [
                    'key' => $key,
                    'label' => $label,
                    'path' => "letter-documents/{$key}.pdf",
                    'original_name' => "{$key}.pdf",
                    'mime_type' => 'application/pdf',
                    'size' => 1024,
                ])
                ->values()
                ->all(),
        ]);
    }
}