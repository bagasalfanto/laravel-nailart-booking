<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('activity_log')) {
            return;
        }

        $hasSubjectId = Schema::hasColumn('activity_log', 'subject_id');
        $hasSubjectType = Schema::hasColumn('activity_log', 'subject_type');
        $hasCauserId = Schema::hasColumn('activity_log', 'causer_id');
        $hasCauserType = Schema::hasColumn('activity_log', 'causer_type');

        $hasSubjectIndex = $this->indexExists('activity_log', 'subject');
        $hasCauserIndex = $this->indexExists('activity_log', 'causer');

        $subjectIdType = DB::table('information_schema.columns')
            ->where('table_schema', DB::getDatabaseName())
            ->where('table_name', 'activity_log')
            ->where('column_name', 'subject_id')
            ->value('data_type');

        $causerIdType = DB::table('information_schema.columns')
            ->where('table_schema', DB::getDatabaseName())
            ->where('table_name', 'activity_log')
            ->where('column_name', 'causer_id')
            ->value('data_type');

        $subjectIsUuidCompatible = in_array(strtolower((string) $subjectIdType), ['char', 'varchar'], true);
        $causerIsUuidCompatible = in_array(strtolower((string) $causerIdType), ['char', 'varchar'], true);

        if ($subjectIsUuidCompatible && $causerIsUuidCompatible) {
            return;
        }

        Schema::table('activity_log', function (Blueprint $table) use ($hasSubjectId, $hasSubjectType, $hasCauserId, $hasCauserType, $hasSubjectIndex, $hasCauserIndex) {
            if ($hasSubjectIndex) {
                $table->dropIndex('subject');
            }

            if ($hasSubjectId) {
                $table->dropColumn('subject_id');
            }

            if ($hasSubjectType) {
                $table->dropColumn('subject_type');
            }

            if ($hasCauserIndex) {
                $table->dropIndex('causer');
            }

            if ($hasCauserId) {
                $table->dropColumn('causer_id');
            }

            if ($hasCauserType) {
                $table->dropColumn('causer_type');
            }
        });

        Schema::table('activity_log', function (Blueprint $table) {
            if (!Schema::hasColumn('activity_log', 'subject_id') && !Schema::hasColumn('activity_log', 'subject_type')) {
                $table->nullableUuidMorphs('subject', 'subject');
            }

            if (!Schema::hasColumn('activity_log', 'causer_id') && !Schema::hasColumn('activity_log', 'causer_type')) {
                $table->nullableUuidMorphs('causer', 'causer');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No down migration, this is a data-safe schema synchronization.
    }

    private function indexExists(string $tableName, string $indexName): bool
    {
        return DB::table('information_schema.statistics')
            ->where('table_schema', DB::getDatabaseName())
            ->where('table_name', $tableName)
            ->where('index_name', $indexName)
            ->exists();
    }
};
