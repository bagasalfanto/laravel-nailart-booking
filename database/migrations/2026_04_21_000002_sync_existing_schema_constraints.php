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
        $this->syncUsersTable();
        $this->syncSessionsTable();
        $this->syncProfileUniqueConstraints();
        $this->syncReservasiTable();
        $this->syncPembayaranTable();
        $this->syncSpatieModelPivotTables();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration synchronizes existing schema state and is intentionally not reversible.
    }

    private function syncUsersTable(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        if (!$this->indexExists('users', 'users_username_unique')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unique('username');
            });
        }
    }

    private function syncSessionsTable(): void
    {
        if (!Schema::hasTable('sessions')) {
            $this->createSessionsTable();
            return;
        }

        if (!Schema::hasColumn('sessions', 'user_id')) {
            Schema::drop('sessions');
            $this->createSessionsTable();
            return;
        }

        $dataType = DB::table('information_schema.columns')
            ->where('table_schema', DB::getDatabaseName())
            ->where('table_name', 'sessions')
            ->where('column_name', 'user_id')
            ->value('data_type');

        if (!in_array(strtolower((string) $dataType), ['char', 'varchar'], true)) {
            Schema::drop('sessions');
            $this->createSessionsTable();
        }
    }

    private function createSessionsTable(): void
    {
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignUuid('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    private function syncProfileUniqueConstraints(): void
    {
        if (Schema::hasTable('admins') && !$this->indexExists('admins', 'admins_user_id_unique')) {
            Schema::table('admins', function (Blueprint $table) {
                $table->unique('user_id');
            });
        }

        if (Schema::hasTable('nailists') && !$this->indexExists('nailists', 'nailists_user_id_unique')) {
            Schema::table('nailists', function (Blueprint $table) {
                $table->unique('user_id');
            });
        }

        if (Schema::hasTable('customers') && !$this->indexExists('customers', 'customers_user_id_unique')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->unique('user_id');
            });
        }
    }

    private function syncReservasiTable(): void
    {
        if (!Schema::hasTable('reservasis')) {
            return;
        }

        if (!Schema::hasColumn('reservasis', 'waktu_mulai')) {
            Schema::table('reservasis', function (Blueprint $table) {
                $table->dateTime('waktu_mulai')->nullable()->index();
            });
        }

        if (!Schema::hasColumn('reservasis', 'waktu_selesai')) {
            Schema::table('reservasis', function (Blueprint $table) {
                $table->dateTime('waktu_selesai')->nullable()->index();
            });
        }

        if (!Schema::hasColumn('reservasis', 'booking_notified_at')) {
            Schema::table('reservasis', function (Blueprint $table) {
                $table->timestamp('booking_notified_at')->nullable();
            });
        }

        if (!$this->indexExists('reservasis', 'reservasis_tanggal_index')) {
            Schema::table('reservasis', function (Blueprint $table) {
                $table->index('tanggal');
            });
        }

        if (!$this->indexExists('reservasis', 'reservasi_nailist_tanggal_jam_unique')) {
            Schema::table('reservasis', function (Blueprint $table) {
                $table->unique(['nailist_id', 'tanggal', 'jam'], 'reservasi_nailist_tanggal_jam_unique');
            });
        }
    }

    private function syncPembayaranTable(): void
    {
        if (!Schema::hasTable('pembayarans')) {
            return;
        }

        if (!Schema::hasColumn('pembayarans', 'order_id')) {
            Schema::table('pembayarans', function (Blueprint $table) {
                $table->string('order_id')->nullable()->after('reservasi_id');
            });

            $rows = DB::table('pembayarans')->whereNull('order_id')->get();

            foreach ($rows as $row) {
                DB::table('pembayarans')
                    ->where('id', $row->id)
                    ->update(['order_id' => 'ORDER-' . strtoupper(substr((string) $row->reservasi_id, 0, 8))]);
            }
        }

        if (!Schema::hasColumn('pembayarans', 'bank')) {
            Schema::table('pembayarans', function (Blueprint $table) {
                $table->string('bank')->nullable()->after('jenis_pembayaran');
            });
        }

        if (!Schema::hasColumn('pembayarans', 'raw_response')) {
            Schema::table('pembayarans', function (Blueprint $table) {
                $table->json('raw_response')->nullable()->after('bank');
            });
        }

        if (!$this->indexExists('pembayarans', 'pembayarans_reservasi_id_unique')) {
            Schema::table('pembayarans', function (Blueprint $table) {
                $table->unique('reservasi_id');
            });
        }

        if (!$this->indexExists('pembayarans', 'pembayarans_order_id_unique')) {
            Schema::table('pembayarans', function (Blueprint $table) {
                $table->unique('order_id');
            });
        }

        if (!$this->indexExists('pembayarans', 'pembayarans_gateway_transaction_id_unique') && Schema::hasColumn('pembayarans', 'gateway_transaction_id')) {
            Schema::table('pembayarans', function (Blueprint $table) {
                $table->unique('gateway_transaction_id');
            });
        }

        if (!$this->indexExists('pembayarans', 'pembayarans_status_pembayaran_index')) {
            Schema::table('pembayarans', function (Blueprint $table) {
                $table->index('status_pembayaran');
            });
        }
    }

    private function syncSpatieModelPivotTables(): void
    {
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $teams = config('permission.teams');

        if (empty($tableNames) || empty($columnNames)) {
            return;
        }

        $modelTableRoles = $tableNames['model_has_roles'] ?? null;
        $modelTablePermissions = $tableNames['model_has_permissions'] ?? null;
        $modelMorphKey = $columnNames['model_morph_key'] ?? 'model_uuid';

        if (!$modelTableRoles || !$modelTablePermissions) {
            return;
        }

        if (!Schema::hasTable($modelTableRoles) || !Schema::hasTable($modelTablePermissions)) {
            return;
        }

        $hasNewMorphKey = Schema::hasColumn($modelTableRoles, $modelMorphKey)
            && Schema::hasColumn($modelTablePermissions, $modelMorphKey);

        if ($hasNewMorphKey) {
            return;
        }

        $pivotRole = $columnNames['role_pivot_key'] ?? 'role_id';
        $pivotPermission = $columnNames['permission_pivot_key'] ?? 'permission_id';

        // Rebuild model pivot tables to support UUID model keys.
        Schema::dropIfExists($modelTableRoles);
        Schema::dropIfExists($modelTablePermissions);

        Schema::create($modelTablePermissions, static function (Blueprint $table) use ($tableNames, $columnNames, $pivotPermission, $teams, $modelMorphKey) {
            $table->unsignedBigInteger($pivotPermission);
            $table->string('model_type');
            $table->uuid($modelMorphKey);
            $table->index([$modelMorphKey, 'model_type'], 'model_has_permissions_model_id_model_type_index');

            $table->foreign($pivotPermission)
                ->references('id')
                ->on($tableNames['permissions'])
                ->cascadeOnDelete();

            if ($teams) {
                $table->unsignedBigInteger($columnNames['team_foreign_key']);
                $table->index($columnNames['team_foreign_key'], 'model_has_permissions_team_foreign_key_index');

                $table->primary([
                    $columnNames['team_foreign_key'],
                    $pivotPermission,
                    $modelMorphKey,
                    'model_type',
                ], 'model_has_permissions_permission_model_type_primary');
            } else {
                $table->primary([
                    $pivotPermission,
                    $modelMorphKey,
                    'model_type',
                ], 'model_has_permissions_permission_model_type_primary');
            }
        });

        Schema::create($modelTableRoles, static function (Blueprint $table) use ($tableNames, $columnNames, $pivotRole, $teams, $modelMorphKey) {
            $table->unsignedBigInteger($pivotRole);
            $table->string('model_type');
            $table->uuid($modelMorphKey);
            $table->index([$modelMorphKey, 'model_type'], 'model_has_roles_model_id_model_type_index');

            $table->foreign($pivotRole)
                ->references('id')
                ->on($tableNames['roles'])
                ->cascadeOnDelete();

            if ($teams) {
                $table->unsignedBigInteger($columnNames['team_foreign_key']);
                $table->index($columnNames['team_foreign_key'], 'model_has_roles_team_foreign_key_index');

                $table->primary([
                    $columnNames['team_foreign_key'],
                    $pivotRole,
                    $modelMorphKey,
                    'model_type',
                ], 'model_has_roles_role_model_type_primary');
            } else {
                $table->primary([
                    $pivotRole,
                    $modelMorphKey,
                    'model_type',
                ], 'model_has_roles_role_model_type_primary');
            }
        });
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
