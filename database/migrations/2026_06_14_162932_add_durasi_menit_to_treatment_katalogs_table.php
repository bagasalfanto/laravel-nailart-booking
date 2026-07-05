<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('treatment_katalogs', function (Blueprint $table) {
            $table->integer('durasi_menit')->default(120)->after('price_max');
        });
    }

    public function down(): void
    {
        Schema::table('treatment_katalogs', function (Blueprint $table) {
            $table->dropColumn('durasi_menit');
        });
    }
};
