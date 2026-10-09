<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scientific_forums', function (Blueprint $table) {
            $table->boolean('international_forum_publication')->default(false)->after('title');
        });
    }

    public function down(): void
    {
        Schema::table('scientific_forums', function (Blueprint $table) {
            $table->dropColumn('international_forum_publication');
        });
    }
};
