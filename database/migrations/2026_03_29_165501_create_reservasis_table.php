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
        Schema::create('reservasis', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignUuid('nailist_id')->constrained('nailists')->cascadeOnDelete();
            $table->foreignUuid('treatment_id')->constrained('treatment_katalogs');
            $table->foreignUuid('status_id')->constrained('status_bookings');
            $table->date('tanggal')->index();
            $table->time('jam');
            $table->dateTime('waktu_mulai')->nullable()->index();
            $table->dateTime('waktu_selesai')->nullable()->index();
            $table->string('referensi_desain')->nullable();
            $table->decimal('total_harga_final', 12, 2)->nullable();
            $table->timestamp('booking_notified_at')->nullable();
            $table->timestamps();

            $table->unique(['nailist_id', 'tanggal', 'jam'], 'reservasi_nailist_tanggal_jam_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservasis');
    }
};
