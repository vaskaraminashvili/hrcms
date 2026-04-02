<?php

use App\Enums\EmployeeStatusEnum;
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
        Schema::table('employees', function (Blueprint $table) {
            $table->string('mobile_number')->after('citizenship')->nullable();
            $table->string('account_number')->after('mobile_number')->nullable();
            $table->json('address_details')->after('account_number')->nullable();
            $table->string('status')->after('address_details')->default(EmployeeStatusEnum::ACTIVE->value);
            $table->dropColumn('address')->nullable();
            $table->dropColumn('pysical_address')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('mobile_number');
            $table->dropColumn('account_number');
            $table->string('address')->nullable();
            $table->string('pysical_address')->nullable();
            $table->dropColumn('address_details');
        });
    }
};
