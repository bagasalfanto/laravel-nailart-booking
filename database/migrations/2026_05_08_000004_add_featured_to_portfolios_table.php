<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('portfolios', function (Blueprint $table) {
            if (! Schema::hasColumn('portfolios', 'is_featured')) {
                $table->boolean('is_featured')->default(false)->after('deskripsi');
            }
            if (! Schema::hasColumn('portfolios', 'featured_at')) {
                $table->timestamp('featured_at')->nullable()->after('is_featured');
            }

            $table->index(['is_featured', 'featured_at']);
        });
    }

    public function down(): void
    {
        Schema::table('portfolios', function (Blueprint $table) {
            $table->dropIndex(['is_featured', 'featured_at']);
            foreach (['is_featured', 'featured_at'] as $col) {
                if (Schema::hasColumn('portfolios', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
