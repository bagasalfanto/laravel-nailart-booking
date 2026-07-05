<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('web_settings', function (Blueprint $table) {
            $table->string('group')->default('general')->index()->after('key');
            $table->string('label')->nullable()->after('group');
            $table->string('type')->default('text')->after('value');
            $table->integer('sort_order')->default(0)->after('type');
        });
    }

    public function down(): void
    {
        Schema::table('web_settings', function (Blueprint $table) {
            $table->dropColumn(['group', 'label', 'type', 'sort_order']);
        });
    }
};
