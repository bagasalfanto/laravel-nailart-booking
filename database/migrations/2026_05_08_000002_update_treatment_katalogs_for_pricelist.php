<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('treatment_katalogs', function (Blueprint $table) {
            if (! Schema::hasColumn('treatment_katalogs', 'category_id')) {
                $table->foreignUuid('category_id')->nullable()->after('id')
                    ->constrained('treatment_categories')->nullOnDelete();
            }
            if (! Schema::hasColumn('treatment_katalogs', 'price_type')) {
                $table->enum('price_type', ['fixed', 'range'])->default('fixed')->after('deskripsi');
            }
            if (! Schema::hasColumn('treatment_katalogs', 'price_min')) {
                $table->decimal('price_min', 12, 2)->default(0)->after('price_type');
            }
            if (! Schema::hasColumn('treatment_katalogs', 'price_max')) {
                $table->decimal('price_max', 12, 2)->nullable()->after('price_min');
            }
            if (! Schema::hasColumn('treatment_katalogs', 'sort_order')) {
                $table->unsignedInteger('sort_order')->default(0)->after('price_max');
            }
            if (! Schema::hasColumn('treatment_katalogs', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('sort_order');
            }
        });

        // Migrate existing estimasi_harga ke price_min (price_type tetap 'fixed').
        if (Schema::hasColumn('treatment_katalogs', 'estimasi_harga')) {
            DB::statement('UPDATE treatment_katalogs SET price_min = estimasi_harga WHERE price_min = 0 OR price_min IS NULL');

            Schema::table('treatment_katalogs', function (Blueprint $table) {
                $table->dropColumn('estimasi_harga');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('treatment_katalogs', 'estimasi_harga')) {
            Schema::table('treatment_katalogs', function (Blueprint $table) {
                $table->decimal('estimasi_harga', 12, 2)->default(0)->after('deskripsi');
            });
            DB::statement('UPDATE treatment_katalogs SET estimasi_harga = price_min');
        }

        Schema::table('treatment_katalogs', function (Blueprint $table) {
            foreach (['is_active', 'sort_order', 'price_max', 'price_min', 'price_type'] as $col) {
                if (Schema::hasColumn('treatment_katalogs', $col)) {
                    $table->dropColumn($col);
                }
            }
            if (Schema::hasColumn('treatment_katalogs', 'category_id')) {
                $table->dropConstrainedForeignId('category_id');
            }
        });
    }
};
