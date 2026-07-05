<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nailist_working_hours', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('nailist_id')->constrained('nailists')->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week'); // 0=Min, 1=Sen, ..., 6=Sab (Carbon::dayOfWeek)
            $table->time('jam_buka')->nullable();
            $table->time('jam_tutup')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['nailist_id', 'day_of_week']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nailist_working_hours');
    }
};
