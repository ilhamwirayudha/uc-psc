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
        Schema::table('clients', function (Blueprint $table) {
            $table->string('occupation')->nullable()->after('dob');
            $table->string('education')->nullable()->after('occupation');
            $table->string('marital_status')->nullable()->after('education');
            $table->string('country')->default('Indonesia')->nullable()->after('marital_status');
            $table->string('province')->nullable()->after('country');
            $table->string('city')->nullable()->after('province');
            $table->text('address')->nullable()->after('city');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn([
                'occupation',
                'education',
                'marital_status',
                'country',
                'province',
                'city',
                'address',
            ]);
        });
    }
};
