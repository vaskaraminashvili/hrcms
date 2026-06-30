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
        if (! $this->departmentsHasParentIdColumn()) {
            return;
        }

        $this->dropDepartmentsParentForeignKeys();

        Schema::table('departments', function (Blueprint $table) {
            $table->foreign('parent_id')
                ->references('id')
                ->on('departments')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! $this->departmentsHasParentIdColumn()) {
            return;
        }

        $this->dropDepartmentsParentForeignKeys();

        Schema::table('departments', function (Blueprint $table) {
            $table->foreign('parent_id')
                ->references('id')
                ->on('departments');
        });
    }

    private function departmentsHasParentIdColumn(): bool
    {
        return Schema::hasTable('departments') && Schema::hasColumn('departments', 'parent_id');
    }

    /**
     * Drop any foreign key on `departments.parent_id` without assuming Laravel's default constraint name.
     */
    private function dropDepartmentsParentForeignKeys(): void
    {
        if (! Schema::hasTable('departments') || ! Schema::hasColumn('departments', 'parent_id')) {
            return;
        }

        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();
        $tableName = $connection->getTablePrefix().'departments';

        if ($driver === 'mysql') {
            $database = $connection->getDatabaseName();
            $constraintNames = DB::table('information_schema.KEY_COLUMN_USAGE')
                ->where('TABLE_SCHEMA', $database)
                ->where('TABLE_NAME', $tableName)
                ->where('COLUMN_NAME', 'parent_id')
                ->whereNotNull('REFERENCED_TABLE_NAME')
                ->distinct()
                ->pluck('CONSTRAINT_NAME');

            foreach ($constraintNames as $name) {
                DB::statement('ALTER TABLE `'.$tableName.'` DROP FOREIGN KEY `'.$name.'`');
            }

            return;
        }

        try {
            Schema::table('departments', function (Blueprint $table) {
                $table->dropForeign(['parent_id']);
            });
        } catch (Throwable) {
            // Constraint missing or non-standard name (e.g. partial migration / migrate:refresh).
        }
    }
};
