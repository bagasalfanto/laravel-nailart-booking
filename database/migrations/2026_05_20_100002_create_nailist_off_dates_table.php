<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nailist_off_dates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('nailist_id')->constrained('nailists')->cascadeOnDelete();
            $table->date('tanggal');
            $table->string('alasan', 255)->nullable();
            $table->timestamps();
            $table->unique(['nailist_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nailist_off_dates');
    }
};
