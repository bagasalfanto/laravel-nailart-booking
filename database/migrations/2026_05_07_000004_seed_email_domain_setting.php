<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('web_settings')->updateOrInsert(
            ['key' => 'email_domain'],
            [
                'id'         => (string) Str::uuid(),
                'value'      => 'nailart.com',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        DB::table('web_settings')->where('key', 'email_domain')->delete();
    }
};
