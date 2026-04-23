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
        Schema::create('pembayarans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('reservasi_id')->unique()->constrained('reservasis')->cascadeOnDelete();
            $table->string('order_id')->unique();

            // Midtrans
            $table->string('payment_url')->nullable();
            $table->string('payment_token')->nullable();
            $table->string('gateway_transaction_id')->nullable()->unique();
            $table->string('jenis_pembayaran')->nullable();
            $table->string('bank')->nullable();
            $table->json('raw_response')->nullable();

            $table->decimal('nominal', 12, 2);
            $table->string('status_pembayaran')->default('pending')->index();

            $table->timestamp('batas_waktu_bayar')->nullable();
            $table->timestamp('waktu_pembayaran')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayarans');
    }
};
