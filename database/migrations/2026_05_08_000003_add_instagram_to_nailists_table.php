<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nailists', function (Blueprint $table) {
            if (! Schema::hasColumn('nailists', 'instagram')) {
                $table->string('instagram', 100)->nullable()->after('bio');
            }
        });
    }

    public function down(): void
    {
        Schema::table('nailists', function (Blueprint $table) {
            if (Schema::hasColumn('nailists', 'instagram')) {
                $table->dropColumn('instagram');
            }
        });
    }
};
