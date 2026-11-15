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
        Schema::table('users', function (Blueprint $table) {
            $table->string('employee_id', 60)->nullable()->after('role');
            $table->string('position', 120)->nullable()->after('employee_id');
            $table->string('phone', 30)->nullable()->after('position');
            $table->string('gender', 10)->nullable()->after('phone');
            $table->text('address')->nullable()->after('gender');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['employee_id', 'position', 'phone', 'gender', 'address']);
        });
    }
};
