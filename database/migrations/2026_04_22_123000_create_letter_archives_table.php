<?php

use App\Enums\LetterRequestStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('letter_archives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('letter_request_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('resident_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('archived_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('archive_number')->nullable()->unique();
            $table->string('reference_number');
            $table->string('letter_number')->nullable();
            $table->string('letter_type');
            $table->string('request_status');
            $table->string('resident_nik', 16);
            $table->string('resident_name');
            $table->text('purpose');
            $table->json('documents')->nullable();
            $table->longText('generated_content');
            $table->dateTime('issued_at')->nullable();
            $table->dateTime('archived_at');
            $table->timestamps();

            $table->index('reference_number');
            $table->index('letter_number');
            $table->index('letter_type');
            $table->index('request_status');
            $table->index('resident_nik');
            $table->index('resident_name');
            $table->index('archived_at');
        });

        $now = now();

        $archives = DB::table('letter_requests')
            ->join('residents', 'residents.id', '=', 'letter_requests.resident_id')
            ->where('letter_requests.status', LetterRequestStatus::COMPLETED->value)
            ->whereNotNull('letter_requests.generated_content')
            ->select([
                'letter_requests.id as letter_request_id',
                'letter_requests.resident_id',
                'letter_requests.reference_number',
                'letter_requests.letter_number',
                'letter_requests.letter_type',
                'letter_requests.status as request_status',
                'residents.nik as resident_nik',
                'residents.name as resident_name',
                'letter_requests.purpose',
                'letter_requests.documents',
                'letter_requests.generated_content',
                'letter_requests.issued_at',
            ])
            ->get()
            ->map(function (object $archive) use ($now): array {
                return [
                    'letter_request_id' => $archive->letter_request_id,
                    'resident_id' => $archive->resident_id,
                    'archived_by' => null,
                    'archive_number' => null,
                    'reference_number' => $archive->reference_number,
                    'letter_number' => $archive->letter_number,
                    'letter_type' => $archive->letter_type,
                    'request_status' => $archive->request_status,
                    'resident_nik' => $archive->resident_nik,
                    'resident_name' => $archive->resident_name,
                    'purpose' => $archive->purpose,
                    'documents' => $archive->documents,
                    'generated_content' => $archive->generated_content,
                    'issued_at' => $archive->issued_at,
                    'archived_at' => $archive->issued_at ?? $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            })
            ->all();

        if ($archives !== []) {
            DB::table('letter_archives')->insert($archives);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('letter_archives');
    }
};
