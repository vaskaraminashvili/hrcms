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
        Schema::dropIfExists('position_position_type');
        Schema::dropIfExists('position_types');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('position_types', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['name']);
        });

        Schema::create('position_position_type', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('position_id')->constrained('positions')->cascadeOnDelete();
            $table->foreignId('position_type_id')->constrained('position_types')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['position_id', 'position_type_id']);
        });
    }
};
