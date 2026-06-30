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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('surname');
            $table->string('name_eng')->nullable();
            $table->string('surrname_eng')->nullable();
            $table->string('personal_number')->unique()->length(11)->index('personal_number_index');
            $table->string('email')->nullable();
            $table->date('birth_date');
            $table->string('gender')->nullable();
            $table->string('citizenship')->nullable();
            $table->boolean('education')->nullable();
            $table->string('degree')->nullable();
            $table->string('address')->nullable();
            $table->string('pysical_address')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['name', 'surname', 'personal_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
