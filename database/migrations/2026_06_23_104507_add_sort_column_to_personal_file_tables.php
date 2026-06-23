<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * @var list<string>
     */
    private array $tables = [
        'academic_positions',
        'educations',
        'academic_degrees',
        'work_experiences',
        'scientific_projects',
        'trainings_seminars',
        'publications',
        'textbooks',
        'scientific_forums',
        'scholarships_awards',
        'foreign_languages',
        'computer_skills',
        'other_documents',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $table): void {
                $table->unsignedInteger('sort')->default(0)->after('employee_id');
            });

            $this->backfillSortColumn($table);
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $table): void {
                $table->dropColumn('sort');
            });
        }
    }

    private function backfillSortColumn(string $table): void
    {
        $employeeIds = DB::table($table)->distinct()->pluck('employee_id');

        foreach ($employeeIds as $employeeId) {
            $ids = DB::table($table)
                ->where('employee_id', $employeeId)
                ->orderBy('id')
                ->pluck('id');

            foreach ($ids->values() as $sort => $id) {
                DB::table($table)->where('id', $id)->update(['sort' => $sort]);
            }
        }
    }
};
