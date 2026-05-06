<?php

namespace Database\Factories;

use App\Enums\LetterRequestStatus;
use App\Enums\LetterType;
use App\Models\LetterRequest;
use App\Models\Resident;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LetterArchive>
 */
class LetterArchiveFactory extends Factory
{
    public function definition(): array
    {
        $resident = Resident::factory();

        return [
            'letter_request_id' => LetterRequest::factory(),
            'resident_id' => $resident,
            'archived_by' => null,
            'archive_number' => 'ARS-'.now()->format('Ymd').'-'.fake()->unique()->numerify('####'),
            'reference_number' => 'REQ-'.now()->format('Ymd').'-'.fake()->unique()->numerify('####'),
            'letter_number' => '470/'.now()->format('m').'/RT-RW/001/'.now()->format('Y'),
            'letter_type' => LetterType::GENERAL->value,
            'request_status' => LetterRequestStatus::COMPLETED->value,
            'resident_nik' => fake()->numerify('################'),
            'resident_name' => fake()->name(),
            'purpose' => fake()->sentence(),
            'documents' => [],
            'generated_content' => 'Surat selesai',
            'generated_pdf_path' => 'generated-letters/sample.pdf',
            'generated_docx_path' => 'generated-letters/sample.docx',
            'issued_at' => now(),
            'archived_at' => now(),
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (): array => ['request_status' => LetterRequestStatus::COMPLETED->value]);
    }
}