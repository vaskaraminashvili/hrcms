<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('position_histories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('position_id')->constrained('positions')->cascadeOnDelete();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();

            // What kind of save triggered this entry: created, updated, deleted
            $table->string('event_type')->default('updated');

            // Full snapshot of the position at this point in time
            $table->json('snapshot');

            // Diff: which fields changed and their before/after values
            // e.g. {"salary": {"from": 1200, "to": 1500}, "status": {"from": "active", "to": "suspended"}}
            $table->json('changed_fields')->nullable();

            // Boolean flags for fast reporting queries — one per tracked field
            $table->boolean('affects_salary')->default(false);
            $table->boolean('affects_status')->default(false);
            $table->boolean('affects_position_type')->default(false);
            $table->boolean('affects_staff_type')->default(false);
            $table->boolean('affects_date_start')->default(false);
            $table->boolean('affects_date_end')->default(false);
            $table->boolean('affects_clinical')->default(false);
            $table->boolean('affects_vacation_policy')->default(false);
            $table->boolean('affects_place')->default(false);
            $table->boolean('affects_act_number')->default(false);

            $table->timestamps(); // created_at = exact moment the change happened

            // Indexes for common report queries
            $table->index('event_type');
            $table->index('affects_salary');
            $table->index('affects_status');
            $table->index('affects_position_type');
            $table->index(['position_id', 'event_type']);
            $table->index(['position_id', 'affects_salary']);
            $table->index(['position_id', 'affects_status']);
            $table->index(['position_id', 'affects_position_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('position_histories');
    }
};
