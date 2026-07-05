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
        Schema::table('pembayarans', function (Blueprint $table) {
            $table->decimal('pelunasan_nominal', 15, 2)->nullable()->after('nominal');
            $table->string('pelunasan_jenis')->nullable()->after('pelunasan_nominal');
            $table->dateTime('pelunasan_waktu')->nullable()->after('pelunasan_jenis');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembayarans', function (Blueprint $table) {
            $table->dropColumn(['pelunasan_nominal', 'pelunasan_jenis', 'pelunasan_waktu']);
        });
    }
};
