<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salon_off_dates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->date('tanggal')->unique();
            $table->string('alasan', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salon_off_dates');
    }
};
