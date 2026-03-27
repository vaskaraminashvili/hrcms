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
        Schema::create('position_details', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('position_id')->constrained('positions')->cascadeOnDelete()->unique();
            $table->string('position_type')->nullable();
            $table->date('date_start')->nullable();
            $table->date('date_end')->nullable();
            $table->string('status')->nullable();
            $table->string('act_number')->nullable();
            $table->date('act_date')->nullable();
            $table->string('staff_type')->nullable();
            $table->boolean('clinical')->nullable()->default(false);
            $table->string('clinical_text')->nullable();
            $table->boolean('automative_renewal')->nullable();
            $table->integer('salary')->nullable();
            $table->foreignId('vacation_policy_id')->constrained('vacation_policies');
            $table->string('comment')->nullable();
            $table->timestamps();
            $table->index(['date_start', 'date_end']);
            $table->index(['status']);
            $table->index(['salary']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('position_details');
    }
};
