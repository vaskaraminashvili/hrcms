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
        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('place_id')->constrained('places');
            $table->foreignId('employee_id')->constrained('employees');
            $table->foreignId('department_id')->constrained('departments');
            $table->date('date_start')->nullable();
            $table->date('date_end')->nullable();
            $table->string('status')->nullable();
            $table->string('act_number')->nullable();
            $table->date('act_date')->nullable();
            $table->boolean('staff_type')->nullable()->default(false);
            $table->boolean('clinical')->nullable()->default(false);
            $table->string('clinical_text')->nullable();
            $table->boolean('automative_renewal')->nullable();
            $table->integer('salary')->nullable();
            $table->string('comment')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['date_start', 'date_end']);
            $table->index(['status']);
            $table->index(['salary']);
            $table->index(['department_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('positions');
    }
};
