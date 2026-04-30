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
        // need to chagne naame on existing table exsiting field
        Schema::table('scientific_forums', function (Blueprint $table) {
            $table->renameColumn('held_at', 'start_date');
            $table->date('end_date')->after('start_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scientific_forums', function (Blueprint $table) {
            $table->renameColumn('start_date', 'held_at');
            $table->dropColumn('end_date');
        });

    }
};
