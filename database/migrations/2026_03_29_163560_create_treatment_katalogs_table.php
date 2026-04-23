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
        Schema::create('treatment_katalogs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama_jasa');
            $table->string('kode_jasa')->unique();
            $table->text('deskripsi')->nullable();
            $table->decimal('estimasi_harga', 12, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('treatment_katalogs');
    }
};
