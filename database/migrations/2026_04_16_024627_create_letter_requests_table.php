<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('letter_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resident_id')->constrained()->cascadeOnDelete();
            $table->string('reference_number')->unique();
            $table->string('letter_type');
            $table->text('purpose');
            $table->string('status')->default('menunggu_verifikasi_rt');
            $table->text('rt_notes')->nullable();
            $table->text('rw_notes')->nullable();
            $table->json('documents')->nullable();
            $table->string('letter_number')->nullable()->unique();
            $table->longText('generated_content')->nullable();
            $table->timestamp('issued_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('letter_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('letter_requests');
    }
};
