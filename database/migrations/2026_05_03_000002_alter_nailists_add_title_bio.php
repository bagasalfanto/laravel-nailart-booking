<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nailists', function (Blueprint $table) {
            $table->string('title')->nullable()->after('user_id');
            $table->text('bio')->nullable()->after('title');
        });

        // Pindahkan nilai lama dari kolom specialty ke title (best-effort migrasi data).
        if (Schema::hasColumn('nailists', 'specialty')) {
            \DB::statement('UPDATE nailists SET title = specialty WHERE title IS NULL AND specialty IS NOT NULL');

            Schema::table('nailists', function (Blueprint $table) {
                $table->dropColumn('specialty');
            });
        }
    }

    public function down(): void
    {
        Schema::table('nailists', function (Blueprint $table) {
            $table->string('specialty')->nullable();
            $table->dropColumn(['title', 'bio']);
        });
    }
};
