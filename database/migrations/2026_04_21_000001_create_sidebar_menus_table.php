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
        $tableNames = config('permission.table_names');

        Schema::create('sidebar_menus', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('parent_id')->nullable()->constrained('sidebar_menus')->nullOnDelete();
            $table->string('title');
            $table->string('icon')->nullable();
            $table->string('route_name')->nullable();
            $table->string('url')->nullable();
            $table->string('permission_name')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });

        Schema::create('sidebar_menu_role', function (Blueprint $table) use ($tableNames) {
            $table->foreignUuid('menu_id')->constrained('sidebar_menus')->cascadeOnDelete();
            $table->unsignedBigInteger('role_id');

            $table->foreign('role_id')
                ->references('id')
                ->on($tableNames['roles'])
                ->cascadeOnDelete();

            $table->primary(['menu_id', 'role_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sidebar_menu_role');
        Schema::dropIfExists('sidebar_menus');
    }
};
