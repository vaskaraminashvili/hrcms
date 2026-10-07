<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scientific_forums', function (Blueprint $table) {
            $table->string('participation_role')->nullable()->after('participation_form');
            $table->string('scope')->nullable()->after('participation_role');
        });

        Schema::table('scientific_projects', function (Blueprint $table) {
            $table->dropColumn(['participation_role', 'scope']);
        });
    }

    public function down(): void
    {
        Schema::table('scientific_projects', function (Blueprint $table) {
            $table->string('participation_role')->nullable()->after('position');
            $table->string('scope')->nullable()->after('participation_role');
        });

        Schema::table('scientific_forums', function (Blueprint $table) {
            $table->dropColumn(['participation_role', 'scope']);
        });
    }
};
