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
        Schema::table('letter_requests', function (Blueprint $table) {
            $table->string('generated_pdf_path')->nullable()->after('generated_content');
        });

        Schema::table('letter_archives', function (Blueprint $table) {
            $table->string('generated_pdf_path')->nullable()->after('generated_content');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('letter_requests', function (Blueprint $table) {
            $table->dropColumn('generated_pdf_path');
        });

        Schema::table('letter_archives', function (Blueprint $table) {
            $table->dropColumn('generated_pdf_path');
        });
    }
};
