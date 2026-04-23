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
        Schema::create('penggunaan_charms', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('reservasi_id')->constrained('reservasis')->cascadeOnDelete();
            $table->foreignUuid('charm_id')->constrained('data_charms');
            $table->integer('jumlah_dipakai');
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penggunaan_charms');
    }
};
