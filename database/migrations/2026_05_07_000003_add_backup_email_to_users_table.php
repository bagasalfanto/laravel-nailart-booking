<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'backup_email')) {
                $table->string('backup_email')->nullable()->after('email');
            }
            if (! Schema::hasColumn('users', 'backup_email_verified_at')) {
                $table->timestamp('backup_email_verified_at')->nullable()->after('backup_email');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            foreach (['backup_email', 'backup_email_verified_at'] as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
