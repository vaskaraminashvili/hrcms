<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('positions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            $table->foreignId('place_id')->constrained('places');
            $table->foreignId('vacation_policy_id')->constrained('vacation_policies');

            $table->string('position_type')->nullable();
            $table->date('date_start')->nullable();
            $table->date('date_end')->nullable();
            $table->string('status')->nullable();
            $table->string('act_number')->nullable();
            $table->date('act_date')->nullable();
            $table->string('staff_type')->nullable();
            $table->boolean('clinical')->default(false)->nullable();
            $table->string('clinical_text')->nullable();
            $table->boolean('automative_renewal')->nullable();
            $table->integer('salary')->nullable();
            $table->string('comment')->nullable();

            $table->timestamps();

            // One position per employee per department
            $table->unique(['employee_id', 'department_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('positions');
    }
};
