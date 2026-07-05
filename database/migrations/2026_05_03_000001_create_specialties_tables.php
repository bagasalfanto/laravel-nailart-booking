<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('specialties', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('nailist_specialty', function (Blueprint $table) {
            $table->foreignUuid('nailist_id')->constrained('nailists')->cascadeOnDelete();
            $table->foreignUuid('specialty_id')->constrained('specialties')->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['nailist_id', 'specialty_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nailist_specialty');
        Schema::dropIfExists('specialties');
    }
};
