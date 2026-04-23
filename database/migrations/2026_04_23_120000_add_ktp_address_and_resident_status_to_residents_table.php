<?php

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
        Schema::table('residents', function (Blueprint $table) {
            $table->text('ktp_address')->nullable()->after('birth_date');
            $table->string('resident_status', 20)->default('warga_asli')->after('address');
        });

        DB::table('residents')->update([
            'ktp_address' => DB::raw('address'),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('residents', function (Blueprint $table) {
            $table->dropColumn(['ktp_address', 'resident_status']);
        });
    }
};
